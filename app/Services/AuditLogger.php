<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public static function log(string $accion, string $descripcion, ?int $tenantId = null, array $meta = []): void
    {
        AuditLog::create([
            'user_id'     => auth()->id(),
            'tenant_id'   => $tenantId,
            'accion'      => $accion,
            'descripcion' => $descripcion,
            'ip'          => request()->ip(),
            'meta'        => $meta ?: null,
        ]);

        Log::channel('audit')->info($accion, [
            'descripcion' => $descripcion,
            'user_id'     => auth()->id(),
            'tenant_id'   => $tenantId,
            'ip'          => request()->ip(),
            'meta'        => $meta ?: null,
        ]);
    }
}
