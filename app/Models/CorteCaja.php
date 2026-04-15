<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    protected $table = 'cortes_caja';

    protected $fillable = [
        'user_id',
        'fecha_inicio',
        'fecha_corte',
        'num_transacciones',
        'total_efectivo',
        'total_tarjeta',
        'total_general',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio'      => 'datetime',
        'fecha_corte'       => 'datetime',
        'total_efectivo'    => 'decimal:2',
        'total_tarjeta'     => 'decimal:2',
        'total_general'     => 'decimal:2',
        'num_transacciones' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
