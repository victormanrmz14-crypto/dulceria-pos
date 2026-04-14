<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'marca_id',
        'nombre',
        'precio',
        'stock',
        'stock_minimo',
        'unidad_medida',
        'activo',
    ];

    protected $casts = [
        'precio'       => 'decimal:2',
        'stock'        => 'decimal:3',
        'stock_minimo' => 'decimal:3',
        'activo'       => 'boolean',
    ];

    // Relación con Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con Marca
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    // Verificar si el stock está bajo
    public function stockBajo()
    {
        return $this->stock <= $this->stock_minimo;
    }
}