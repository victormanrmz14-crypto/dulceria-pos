<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anuncio extends Model
{
    protected $fillable = ['titulo', 'cuerpo', 'tipo', 'activo', 'expira_en', 'creado_por'];

    protected $casts = [
        'activo'    => 'boolean',
        'expira_en' => 'datetime',
    ];

    public function autor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function scopeVigentes($query)
    {
        return $query->where('activo', true)
                     ->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', now()));
    }
}
