<?php

namespace App\Services\SuperAdmin;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HealthChecks
{
    public function snapshot(): array
    {
        $checks = [];
        try {
            DB::select('SELECT 1');
            $checks[] = ['nombre' => 'Base de datos', 'ok' => true, 'detalle' => 'Consulta SELECT 1 completada'];
        } catch (\Throwable) {
            $checks[] = ['nombre' => 'Base de datos', 'ok' => false, 'detalle' => 'No fue posible completar la consulta'];
        }
        $key = 'sa-health-'.Str::uuid();
        $store = config('cache.default');
        try {
            Cache::put($key, $key, 10);
            $ok = Cache::get($key) === $key;
            $checks[] = ['nombre' => 'Caché: '.$store, 'ok' => $ok, 'detalle' => $ok ? 'Escritura y lectura verificadas'.($store === 'array' ? ' · solo memoria de esta petición' : '') : 'La lectura no coincide con la escritura'];
        } catch (\Throwable) {
            $checks[] = ['nombre' => 'Caché: '.$store, 'ok' => false, 'detalle' => 'No se pudo verificar escritura y lectura'];
        } finally {
            try {
                Cache::forget($key);
            } catch (\Throwable) { /* Failure already reported. */
            }
        }
        $jobs = null;
        $failures = [];
        try {
            $jobs = DB::table('failed_jobs')->count();
            $failures = DB::table('failed_jobs')->latest('failed_at')->limit(10)->get()->map(fn ($j) => [
                'id' => $j->id, 'job' => class_basename(json_decode($j->payload, true)['displayName'] ?? $j->queue),
                'excepcion' => str($j->exception)->before("\n")->limit(200)->toString(),
                'fallido_en' => CarbonImmutable::parse($j->failed_at)->isoFormat('D MMM YYYY, HH:mm'),
            ])->all();
            $checks[] = ['nombre' => 'Jobs fallidos', 'ok' => $jobs === 0, 'detalle' => $jobs.' registros conservados en failed_jobs; no mide workers activos'];
        } catch (\Throwable) {
            $checks[] = ['nombre' => 'Jobs fallidos', 'ok' => null, 'detalle' => 'No fue posible consultar failed_jobs'];
        }
        $path = storage_path();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);
        $pct = $free !== false && $total !== false && $total > 0 ? round(100 * $free / $total, 1) : null;
        $checks[] = ['nombre' => 'Disco del proceso PHP', 'ok' => $pct === null ? null : $pct > 15,
            'detalle' => $pct === null ? 'Medición no disponible' : round($free / 1073741824, 1).' GB libres de '.round($total / 1073741824, 1).' GB ('.$pct.' % libre)',
            'alcance' => 'Sistema de archivos que contiene '.$path.'. En Docker corresponde al filesystem visible desde PHP, no al volumen MySQL ni a todo el servidor.'];

        return ['salud' => $checks, 'jobsFallidos' => $jobs, 'failedJobs' => $failures,
            'comprobadoEn' => now()->isoFormat('D MMM YYYY, HH:mm:ss'), 'zona' => config('app.timezone')];
    }
}
