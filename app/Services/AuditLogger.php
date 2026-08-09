<?php

namespace App\Services;

use App\Models\AuditLog;

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
    }
}
