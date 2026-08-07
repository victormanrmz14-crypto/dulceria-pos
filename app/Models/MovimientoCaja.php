<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasTenant;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'tenant_id',
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
