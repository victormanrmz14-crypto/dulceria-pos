<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasTenant;

    protected $table = 'proveedores';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'email',
        'telefono',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
