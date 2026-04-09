<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'username',
        'email',
        'password',
        'rol',
        'activo',
    ];
    

    protected $hidden = [
        'password',
        'remember_token',
    ];
}