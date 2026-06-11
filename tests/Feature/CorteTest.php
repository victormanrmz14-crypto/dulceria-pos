<?php

namespace Tests\Feature;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorteTest extends TestCase
{
    use RefreshDatabase;

    private function venta(User $user, float $total, string $metodo = 'efectivo'): Venta
    {
        return Venta::create([
            'user_id'     => $user->id,
            'folio'       => 'VTA-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'metodo_pago' => $metodo,
            'subtotal'    => $total,
            'impuestos'   => 0,
            'total'       => $total,
        ]);
    }

    public function test_el_corte_incluye_el_fondo_del_corte_anterior(): void
    {
        $user = User::factory()->create();

        // Corte previo que dejó $500 de fondo en el cajón
        CorteCaja::create([
            'user_id'           => $user->id,
            'fecha_inicio'      => now()->subDay(),
            'fecha_corte'       => now()->subHours(2),
            'num_transacciones' => 1,
            'total_efectivo'    => 500,
            'total_tarjeta'     => 0,
            'total_general'     => 500,
            'dinero_en_caja'    => 500,
        ]);

        // Venta en efectivo de $100 en el período actual
        $this->venta($user, 100);

        // El cajero cuenta el cajón completo: fondo (500) + venta (100)
        $response = $this->actingAs($user)->post('/cortes', [
            'efectivo_contado' => 600,
            'dinero_en_caja'   => 500,
        ]);

        $corte = CorteCaja::latest('id')->first();
        $response->assertRedirect(route('cortes.show', $corte));

        // El efectivo esperado incluye el fondo, así el conteo cuadra exacto
        $this->assertEquals(600.00, (float) $corte->total_efectivo);
        $this->assertEquals(600.00, (float) $corte->total_general);
        $this->assertEquals(0.00, round((float) $corte->efectivo_contado - (float) $corte->total_efectivo, 2));
    }

    public function test_el_corte_considera_ingresos_y_retiros_manuales(): void
    {
        $user = User::factory()->create();

        $this->venta($user, 200);
        $this->venta($user, 150, 'tarjeta');
        MovimientoCaja::create(['user_id' => $user->id, 'tipo' => 'ingreso', 'monto' => 50, 'motivo' => 'fondo para cambio']);
        MovimientoCaja::create(['user_id' => $user->id, 'tipo' => 'retiro',  'monto' => 30, 'motivo' => 'pago a proveedor']);

        $this->actingAs($user)->post('/cortes', []);

        $corte = CorteCaja::latest('id')->first();

        $this->assertNotNull($corte);
        $this->assertEquals(220.00, (float) $corte->total_efectivo); // 200 + 50 - 30
        $this->assertEquals(150.00, (float) $corte->total_tarjeta);
        $this->assertEquals(370.00, (float) $corte->total_general);
        $this->assertEquals(2, $corte->num_transacciones);
    }

    public function test_no_se_genera_un_corte_sin_ventas_ni_movimientos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/caja')->post('/cortes', []);

        $response->assertRedirect('/caja');
        $response->assertSessionHas('error');
        $this->assertSame(0, CorteCaja::count());
    }

    public function test_un_segundo_corte_inmediato_no_duplica_el_periodo(): void
    {
        $user = User::factory()->create();

        $this->venta($user, 100);

        $this->actingAs($user)->post('/cortes', []);
        // Doble submit: el segundo corte no encuentra nada que cortar
        $respuesta2 = $this->actingAs($user)->post('/cortes', []);

        $respuesta2->assertSessionHas('error');
        $this->assertSame(1, CorteCaja::count());
    }
}
