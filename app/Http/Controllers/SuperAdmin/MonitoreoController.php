<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\Venta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MonitoreoController extends Controller
{
    public function index()
    {
        // ── Salud del sistema ──────────────────────────────────────
        $jobsFallidos = DB::table('failed_jobs')->count();

        $discoLibre   = disk_free_space('/');
        $discoTotal   = disk_total_space('/');
        $discoPct     = $discoTotal > 0 ? round(($discoLibre / $discoTotal) * 100) : 0;

        $cacheOk = true;
        try {
            Cache::put('sa_health_check', 1, 5);
            $cacheOk = Cache::get('sa_health_check') === 1;
        } catch (\Throwable) {
            $cacheOk = false;
        }

        $salud = [
            ['nombre' => 'Base de datos',   'ok' => true,       'detalle' => 'Conexión activa'],
            ['nombre' => 'Caché / Redis',   'ok' => $cacheOk,   'detalle' => $cacheOk ? 'Respondiendo' : 'Sin respuesta'],
            ['nombre' => 'Jobs fallidos',   'ok' => $jobsFallidos === 0, 'detalle' => $jobsFallidos === 0 ? 'Sin errores' : "{$jobsFallidos} en cola de fallos"],
            ['nombre' => 'Espacio en disco','ok' => $discoPct > 15, 'detalle' => $this->formatBytes($discoLibre) . ' libres de ' . $this->formatBytes($discoTotal)],
        ];

        // ── Actividad por tenant ────────────────────────────────────
        $ventasPorTenant = Venta::selectRaw('tenant_id, COUNT(*) as total_mes, MAX(created_at) as ultima_venta')
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $actividadTotal = Venta::selectRaw('tenant_id, COUNT(*) as total_global')
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $tenants = Tenant::orderBy('nombre')->get()->map(function (Tenant $t) use ($ventasPorTenant, $actividadTotal) {
            $mes     = $ventasPorTenant->get($t->id);
            $global  = $actividadTotal->get($t->id);
            $ultima  = $mes?->ultima_venta ? now()->parse($mes->ultima_venta) : null;
            $dormida = ! $ultima || $ultima->lt(now()->subDays(30));

            return [
                'id'            => $t->id,
                'nombre'        => $t->nombre,
                'activo'        => $t->activo,
                'plan'          => $t->plan,
                'ventas_mes'    => (int) ($mes?->total_mes ?? 0),
                'ventas_total'  => (int) ($global?->total_global ?? 0),
                'ultima_venta'  => $ultima?->isoFormat('D MMM YYYY, HH:mm'),
                'ultima_diff'   => $ultima?->diffForHumans() ?? 'Sin ventas',
                'dormida'       => $dormida,
            ];
        });

        // ── Jobs fallidos (últimos 10) ──────────────────────────────
        $failedJobs = DB::table('failed_jobs')
            ->latest('failed_at')
            ->limit(10)
            ->get()
            ->map(fn ($j) => [
                'id'         => $j->id,
                'job'        => class_basename(json_decode($j->payload, true)['displayName'] ?? $j->queue),
                'excepcion'  => str($j->exception)->before("\n")->limit(120)->toString(),
                'fallido_en' => now()->parse($j->failed_at)->isoFormat('D MMM HH:mm'),
            ]);

        return Inertia::render('SuperAdmin/Monitoreo', [
            'salud'       => $salud,
            'tenants'     => $tenants->values(),
            'failedJobs'  => $failedJobs->values(),
            'jobsFallidos'=> $jobsFallidos,
            'discoPct'    => $discoPct,
        ]);
    }

    private function formatBytes(float $bytes): string
    {
        if ($bytes >= 1_073_741_824) return round($bytes / 1_073_741_824, 1) . ' GB';
        if ($bytes >= 1_048_576)     return round($bytes / 1_048_576, 1) . ' MB';
        return round($bytes / 1024, 1) . ' KB';
    }
}
