<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'user_id',
        'tipo',
        'monto',
        'motivo',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'tipo'  => 'string',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
