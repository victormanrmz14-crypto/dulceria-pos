<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'avatar_path',
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

    public function ownsAvatarPath(?string $path): bool
    {
        return $path !== null && preg_match(
            '~\Aavatars/'.preg_quote((string) $this->getKey(), '~').'/[a-zA-Z0-9]{40}\.(?:jpg|jpeg|png|webp)\z~',
            $path
        ) === 1;
    }

    public function avatarUrl(): ?string
    {
        $path = $this->getAttribute('avatar_path');

        return $this->ownsAvatarPath($path) ? Storage::disk('public')->url($path) : null;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
