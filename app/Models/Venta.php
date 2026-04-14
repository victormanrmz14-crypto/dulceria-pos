<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'user_id',
        'folio',
        'metodo_pago',
        'subtotal',
        'impuestos',
        'total',
    ];

    protected $casts = [
        'subtotal'   => 'decimal:2',
        'impuestos'  => 'decimal:2',
        'total'      => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con detalles
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    // Generar folio a partir del id ya asignado por la BD
    public static function folioDesdeId(int $id): string
    {
        return 'VTA-' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }
}