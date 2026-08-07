<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasTenant;

    protected $table = 'marcas';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'activo',
    ];
}