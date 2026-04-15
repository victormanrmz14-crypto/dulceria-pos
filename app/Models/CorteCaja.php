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
        'efectivo_contado',
        'dinero_en_caja',
    ];

    protected $casts = [
        'fecha_inicio'      => 'datetime',
        'fecha_corte'       => 'datetime',
        'total_efectivo'    => 'decimal:2',
        'total_tarjeta'     => 'decimal:2',
        'total_general'     => 'decimal:2',
        'num_transacciones' => 'integer',
        'efectivo_contado'  => 'decimal:2',
        'dinero_en_caja'    => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
