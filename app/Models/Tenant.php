<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
