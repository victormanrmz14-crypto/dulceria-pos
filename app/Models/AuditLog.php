<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'tenant_id', 'accion', 'descripcion', 'ip', 'meta'];

    protected $casts = ['meta' => 'array'];

    public $timestamps = true;
    const UPDATED_AT = null;

    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
    public function tenant()  { return $this->belongsTo(Tenant::class); }
}
