<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['nombre', 'activo', 'plan', 'plan_expira_en', 'notas', 'configuracion'];

    protected $casts = [
        'activo'        => 'boolean',
        'plan_expira_en'=> 'datetime',
        'configuracion' => 'array',
    ];

    public function users()    { return $this->hasMany(User::class); }
    public function anuncios() { return $this->hasMany(\App\Models\AuditLog::class); }

    public function planLabel(): string
    {
        return match($this->plan) {
            'basico' => 'Básico',
            'pro'    => 'Pro',
            default  => 'Trial',
        };
    }

    public function diasRestantesTrial(): ?int
    {
        if ($this->plan !== 'trial' || ! $this->plan_expira_en) return null;
        return max(0, (int) now()->diffInDays($this->plan_expira_en, false));
    }
}
