<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Explicit local demo, never invoked by DatabaseSeeder:
 * docker compose -f docker-compose.yml exec app php artisan db:seed --class=SuperAdminDemoSeeder
 *
 * Creates 20 marked businesses and more than two months of historical operations.
 * Preserves existing accounts, prints a generated demo password once and skips
 * subsequent runs. It neither resets the database nor changes existing demo data.
 * Requires APP_ENV=local or testing; even --force cannot enable it in production.
 */
class SuperAdminDemoSeeder extends Seeder
{
    public const MARKER = '[DEMO-OPERACIONES-v1]';

    private const NAMES = [
        'Dulcería La Tradición', 'Dulces del Centro', 'El Rincón del Chocolate', 'Gomitas y Sonrisas',
        'Dulcería Los Arcos', 'La Piñata Feliz', 'Caramelos de la Abuela', 'Dulcería Mercado del Sol',
        'Dulces La Estación', 'La Casita de Mazapán', 'Dulcería Nube de Azúcar',
        'Dulces El Puente', 'La Esquina del Caramelo', 'Dulcería Las Flores',
        'Dulces Coyoacán', 'Dulcería Primer Sueño', 'La Nueva Paletería', 'Dulces La Bienvenida',
        'Dulcería La Tradición de México y sus Sabores Artesanales del Centro Histórico', 'Dulcería El Descanso',
    ];

    // Integer cents throughout generation; the existing POS uses final prices without extra tax.
    private const PRODUCTS = [
        ['Paleta de cereza', 550], ['Mazapán de cacahuate', 700], ['Gomitas de frutas 100 g', 1850],
        ['Chocolate con leche', 2200], ['Tamarindo enchilado', 1250], ['Bombones 100 g', 1600],
        ['Caramelos surtidos 250 g', 3450], ['Chicle de hierbabuena', 350], ['Palanqueta de cacahuate', 1400],
        ['Cacahuates japoneses 100 g', 1900], ['Obleas con cajeta', 1750], ['Pulpa de tamarindo', 850],
        ['Gomitas de mango enchilado', 2350], ['Chocolate amargo', 2950], ['Paleta de sandía', 650],
        ['Cocada artesanal', 1500], ['Chocolates surtidos 250 g', 6800], ['Botana de maíz', 1800],
        ['Dulce de leche', 1150], ['Gomitas de ositos 250 g', 4200], ['Chiclosos de cajeta', 950],
        ['Bolsa para fiesta', 9500], ['Chocolate con almendras', 3250], ['Caramelo de café', 450],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \LogicException('La demo solo se permite en entornos local y testing.');
        }
        $existing = Tenant::where('notas', 'like', self::MARKER.'%')->count();
        if ($existing === 20) {
            $this->command?->info('La simulación ya existe. Se conservan los datos y las contraseñas.');

            return;
        }
        if ($existing !== 0) {
            throw new \LogicException('Existe una demo incompleta; no se sobrescribirán datos.');
        }

        $now = CarbonImmutable::now();
        $start = $now->subMonthsNoOverflow(2)->subDays(7)->startOfDay();
        $password = Str::password(20);
        $hash = Hash::make($password);

        DB::transaction(function () use ($now, $start, $hash) {
            $this->account(null, 'demo_superadmin', 'superadmin@dulceria-demo.test', 'SuperAdmin Demo', 'admin', true, $hash, $start->subDays(15), true);
            foreach (self::NAMES as $offset => $name) {
                $this->business($offset + 1, $name, $now, $start, $hash);
            }
        });

        $this->command?->info('Demo creada: 20 dulcerías. Historial: '.$start->toDateString().' a '.$now->toDateString().'.');
        $this->command?->info('SuperAdmin demo: superadmin@dulceria-demo.test');
        $this->command?->info('Contraseña de las cuentas demo: '.$password);
        $this->command?->info('Las cuentas y los datos anteriores se conservaron.');
    }

    private function account(?int $tenantId, string $username, string $email, string $name, string $role, bool $enabled, string $hash, CarbonImmutable $created, bool $super = false): int
    {
        return DB::table('users')->insertGetId([
            'tenant_id' => $tenantId, 'username' => $username, 'email' => $email, 'nombre' => $name,
            'apellido' => 'Simulación', 'rol' => $role, 'activo' => $enabled, 'es_super_admin' => $super,
            'password' => $hash, 'email_verified_at' => $created, 'created_at' => $created, 'updated_at' => $created,
        ]);
    }

    private function business(int $index, string $name, CarbonImmutable $now, CarbonImmutable $start, string $hash): void
    {
        $age = match ($index) {
            9 => 45, 10 => 25, 11 => 12, 15 => 18, 16 => 16, 17 => 1, 18 => 3, default => null
        };
        $registered = $age !== null ? $now->subDays($age)->startOfDay() : $start->subDays(14 + $index);
        $profile = match (true) {
            $index <= 8 => 'Operación estable con ventas frecuentes',
            $index <= 11 => 'Incorporación reciente con ventas',
            $index <= 14 => 'Vendió anteriormente; sin ventas recientes',
            $index === 15 => 'Productos registrados; pendiente de primera venta',
            $index === 16 => 'Registro de más de 7 días; sin productos ni ventas',
            $index === 17 => 'Registro nuevo; sin productos ni ventas',
            $index === 18 => 'Registro nuevo con productos; pendiente de primera venta',
            $index === 19 => 'Con ventas; administrador deshabilitado',
            default => 'Negocio deshabilitado con historial comercial',
        };
        $tenantId = DB::table('tenants')->insertGetId([
            'nombre' => '[Demo] '.$name, 'activo' => $index !== 20,
            'plan' => $index >= 15 && $index <= 18 ? 'trial' : ($index % 3 === 0 ? 'pro' : 'basico'),
            'plan_expira_en' => null, 'notas' => self::MARKER.' Datos ficticios. '.$profile.'.',
            'created_at' => $registered, 'updated_at' => $now,
        ]);
        $suffix = str_pad((string) $index, 2, '0', STR_PAD_LEFT);
        $this->account($tenantId, 'demo_admin_'.$suffix, 'admin.'.$suffix.'@dulceria-demo.test', 'Administración '.$suffix, 'admin', $index !== 19, $hash, $registered);
        $cashier = $this->account($tenantId, 'demo_cajero_'.$suffix, 'caja.'.$suffix.'@dulceria-demo.test', 'Caja '.$suffix, 'cajero', $index !== 20, $hash, $registered->addHours(1));

        if (in_array($index, [16, 17], true)) {
            return;
        }
        $productDate = $registered->addDay();
        $categoryIds = [];
        foreach (['Dulces y paletas', 'Chocolates', 'Gomitas y tamarindos', 'Botanas y fiesta'] as $category) {
            $categoryIds[] = DB::table('categorias')->insertGetId(['tenant_id' => $tenantId, 'nombre' => $category, 'activo' => true, 'created_at' => $productDate, 'updated_at' => $productDate]);
        }
        $brandId = DB::table('marcas')->insertGetId(['tenant_id' => $tenantId, 'nombre' => 'Selección demo', 'activo' => true, 'created_at' => $productDate, 'updated_at' => $productDate]);
        $supplierId = DB::table('proveedores')->insertGetId(['tenant_id' => $tenantId, 'nombre' => 'Distribuidora de prueba '.$suffix, 'email' => 'proveedor.'.$suffix.'@dulceria-demo.test', 'activo' => true, 'created_at' => $productDate, 'updated_at' => $productDate]);
        $productCount = match (true) {
            $index === 15 => 8, $index === 18 => 6, $index >= 9 && $index <= 11 => 16, default => 24
        };
        $products = [];
        foreach (array_slice(self::PRODUCTS, 0, $productCount) as $p => [$productName, $cents]) {
            $id = DB::table('productos')->insertGetId([
                'tenant_id' => $tenantId, 'categoria_id' => $categoryIds[$p % 4], 'marca_id' => $brandId, 'proveedor_id' => $supplierId,
                'nombre' => $productName, 'precio' => $this->money($cents), 'stock' => 0, 'stock_minimo' => 10,
                'unidad_medida' => 'pieza', 'activo' => true, 'created_at' => $productDate, 'updated_at' => $productDate,
            ]);
            $products[] = ['id' => $id, 'cents' => $cents];
        }

        if (! in_array($index, [15, 18], true)) {
            $salesStart = $registered->addDays(2)->max($start);
            $salesEnd = match ($index) {
                12 => $now->subDays(36), 13 => $now->subDays(43), 14 => $now->subDays(50), 20 => $now->subDays(8), default => $now
            };
            $this->operations($tenantId, $cashier, $index, $salesStart, $salesEnd, $now, $products);
        }
        // Current demo stock; no existing inventory is touched.
        foreach ($products as $p => $product) {
            DB::table('productos')->where('id', $product['id'])->update(['stock' => $p % 9 === 0 ? 4 : 75 + ($p * 7 + $index) % 90, 'updated_at' => $now]);
        }
    }

    private function operations(int $tenantId, int $cashier, int $index, CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $now, array $products): void
    {
        $lastCut = null;
        $fund = 0;
        $dayIndex = 0;
        for ($day = $start->startOfDay(); $day->lte($end->startOfDay()); $day = $day->addDay(), $dayIndex++) {
            if ($dayIndex > 0 && $day->isSunday() && $index % 3 === 0) {
                continue;
            }
            $tickets = ($index >= 9 && $index <= 11 ? 2 + $index % 3 : 4 + $index % 6) + ($day->isSaturday() ? 3 : 0);
            $cash = 0;
            $card = 0;
            $count = 0;
            for ($ticket = 0; $ticket < $tickets; $ticket++) {
                $date = $day->setTime(9, $index % 11)->addMinutes($ticket * 38);
                if ($date->gt($now)) {
                    continue;
                }
                $lines = [];
                $total = 0;
                for ($line = 0; $line < 1 + ($dayIndex + $ticket + $index) % 4; $line++) {
                    $p = ($dayIndex * 3 + $ticket + $line * 5) % count($products);
                    $quantity = 1 + ($ticket + $line + $index) % 5;
                    $amount = $products[$p]['cents'] * $quantity;
                    $lines[] = ['tenant_id' => $tenantId, 'producto_id' => $products[$p]['id'], 'cantidad' => $quantity,
                        'precio_unitario' => $this->money($products[$p]['cents']), 'importe' => $this->money($amount), 'created_at' => $date, 'updated_at' => $date];
                    $total += $amount;
                }
                $isCash = ($ticket + $dayIndex + $index) % 4 !== 0;
                $received = $isCash ? (int) (ceil($total / 2000) * 2000) : null;
                $saleId = DB::table('ventas')->insertGetId([
                    'tenant_id' => $tenantId, 'user_id' => $cashier, 'folio' => sprintf('DM%02d-%s-%02d', $index, $day->format('ymd'), $ticket + 1),
                    'metodo_pago' => $isCash ? 'efectivo' : 'tarjeta', 'subtotal' => $this->money($total), 'impuestos' => '0.00', 'total' => $this->money($total),
                    'monto_recibido' => $received !== null ? $this->money($received) : null, 'cambio' => $received !== null ? $this->money($received - $total) : null,
                    'created_at' => $date, 'updated_at' => $date,
                ]);
                DB::table('detalle_ventas')->insert(array_map(fn ($line) => ['venta_id' => $saleId, ...$line], $lines));
                $isCash ? $cash += $total : $card += $total;
                $count++;
            }
            if ($count === 0) {
                continue;
            }
            $income = $lastCut === null ? 20000 : 0;
            $withdrawal = $cash >= 50000 ? 10000 : 0;
            foreach (['ingreso' => $income, 'retiro' => $withdrawal] as $type => $amount) {
                $movementDate = $day->setTime($type === 'ingreso' ? 8 : 19, 0);
                if ($amount > 0 && $movementDate->lte($now)) {
                    DB::table('movimientos_caja')->insert(['tenant_id' => $tenantId, 'user_id' => $cashier, 'tipo' => $type,
                        'monto' => $this->money($amount), 'motivo' => self::MARKER.($type === 'ingreso' ? ' Fondo inicial de caja.' : ' Retiro de efectivo de prueba.'),
                        'created_at' => $movementDate, 'updated_at' => $movementDate]);
                }
            }
            $close = $day->setTime(20, 0);
            if ($close->gt($now)) {
                continue;
            }
            $expected = $fund + $cash + $income - $withdrawal;
            DB::table('cortes_caja')->insert([
                'tenant_id' => $tenantId, 'user_id' => $cashier, 'fecha_inicio' => $lastCut ?? $day->startOfDay(), 'fecha_corte' => $close,
                'num_transacciones' => $count, 'total_efectivo' => $this->money($expected), 'total_tarjeta' => $this->money($card),
                'total_general' => $this->money($expected + $card), 'efectivo_contado' => $this->money($expected), 'dinero_en_caja' => '200.00',
                'notas' => self::MARKER.' Corte simulado; conserva $200 de fondo para el siguiente período.', 'created_at' => $close, 'updated_at' => $close,
            ]);
            $lastCut = $close;
            $fund = 20000;
        }
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
