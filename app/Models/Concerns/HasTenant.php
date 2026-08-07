<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Aplica aislamiento por tenant a cualquier modelo.
 *
 * - Lectura: global scope filtra por tenant_id del usuario autenticado.
 * - Escritura: asigna tenant_id automáticamente al crear registros.
 * - Los super-admins de plataforma (es_super_admin=true) ven todos los tenants.
 */
trait HasTenant
{
    protected static function bootHasTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = auth()->user();

            if ($user && !$user->es_super_admin && $user->tenant_id) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    $user->tenant_id
                );
            }
        });

        static::creating(function ($model) {
            if (empty($model->tenant_id) && auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
