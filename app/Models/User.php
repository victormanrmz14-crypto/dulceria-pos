<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Tenant;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'nombre',
        'apellido',
        'username',
        'email',
        'password',
        'rol',
        'es_super_admin',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'es_super_admin' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function esAdmin(): bool
    {
        return $this->es_super_admin || $this->rol === 'admin';
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
