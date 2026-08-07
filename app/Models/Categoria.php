<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasTenant;

    protected $table = 'categorias';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'activo',
    ];
}