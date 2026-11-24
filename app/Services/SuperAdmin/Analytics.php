<?php

namespace App\Services\SuperAdmin;

use App\Models\Tenant;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/** Read-only aggregates. The existing tenant scope and route authorization remain in force. */
class Analytics
{
    public readonly CarbonImmutable $now;

    public readonly CarbonImmutable $start;

    public function __construct()
    {
        $this->now = CarbonImmutable::now();
        $this->start = $this->now->subDays(29)->startOfDay();
    }

    public function period(): array
    {
        return ['inicio' => $this->start->toDateTimeString(), 'fin' => $this->now->toDateTimeString(),
            'zona' => config('app.timezone'), 'label' => $this->start->isoFormat('D MMM').' – '.$this->now->isoFormat('D MMM YYYY')];
    }

    public function sales(): Builder
    {
        return Venta::query()->whereNotNull('tenant_id')
            ->whereIn('tenant_id', Tenant::select('id'))->where('created_at', '<=', $this->now);
    }

    public function tenants(): Builder
    {
        return Tenant::query()->withCount('users')->withCount(['users as admins_habilitados' => fn ($q) => $q->where('rol', 'admin')->where('activo', true)])
            ->with(['users' => fn ($q) => $q->select(['id', 'tenant_id', 'nombre', 'apellido', 'email', 'activo', 'created_at'])->where('rol', 'admin')->orderByDesc('activo')->orderBy('created_at')->orderBy('id')->limit(1)])
            ->addSelect(['ultima_venta' => $this->sales()->selectRaw('MAX(created_at)')->whereColumn('ventas.tenant_id', 'tenants.id')])
            ->addSelect(['ventas_30' => $this->sales()->selectRaw('COUNT(*)')->whereColumn('ventas.tenant_id', 'tenants.id')->where('created_at', '>=', $this->start)]);
    }

    public function attention(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('users', fn ($u) => $u->where('rol', 'admin')->where('activo', true))
                ->orWhere('plan_expira_en', '<=', $this->now)
                ->orWhere(function ($q) {
                    // Established, enabled businesses only; onboarding is not an incident.
                    $q->where('activo', true)->where('created_at', '<', $this->start)
                        ->whereNotIn('id', $this->sales()->select('tenant_id')->where('created_at', '>=', $this->start));
                });
        });
    }

    public function row(Tenant $t): array
    {
        $admin = $t->users->first();
        $last = $t->ultima_venta ? CarbonImmutable::parse($t->ultima_venta) : null;
        $issues = [];
        if (! $t->admins_habilitados) {
            $issues[] = 'Sin administrador habilitado';
        }
        if ($t->plan_expira_en && $t->plan_expira_en->lte($this->now)) {
            $issues[] = 'Plan vencido';
        }
        if ($t->activo && $t->created_at->lt($this->start) && ! $t->ventas_30) {
            $issues[] = $last ? 'Sin ventas en 30 días' : 'Nunca ha registrado ventas';
        }

        return [
            'id' => $t->id, 'nombre' => $t->nombre, 'activo' => $t->activo, 'plan' => $t->plan,
            'admin_nombre' => $admin ? trim($admin->nombre.' '.$admin->apellido) : null,
            'admin_email' => $admin?->email, 'admin_activo' => $admin?->activo,
            'usuarios' => $t->users_count, 'admins_habilitados' => (int) $t->admins_habilitados,
            'ventas_30' => (int) $t->ventas_30, 'ultima_venta' => $last?->isoFormat('D MMM YYYY, HH:mm'),
            'actividad' => ! $last ? 'Nunca ha vendido' : ($t->ventas_30 ? 'Con ventas recientes' : 'Sin ventas en 30 días'),
            'problemas' => $issues, 'estado' => $issues ? 'problema' : 'ok',
            'creado_en' => $t->created_at->isoFormat('D MMM YYYY, HH:mm'),
        ];
    }

    public function series(?int $tenantId = null): array
    {
        $sales = $this->sales()->where('created_at', '>=', $this->start);
        if ($tenantId !== null) {
            $sales->where('tenant_id', $tenantId);
        }
        $sales = $sales->selectRaw('DATE(created_at) as fecha, COUNT(*) as ventas, COUNT(DISTINCT tenant_id) as negocios, SUM(total) as importe')->groupBy('fecha')->get()->keyBy('fecha');
        $registrations = $tenantId === null
            ? Tenant::whereBetween('created_at', [$this->start, $this->now])
                ->selectRaw('DATE(created_at) as fecha, COUNT(*) as registros')->groupBy('fecha')->get()->keyBy('fecha')
            : collect();

        return collect(range(0, 29))->map(function ($i) use ($sales, $registrations) {
            $day = $this->start->addDays($i)->toDateString();
            $sale = $sales->get($day);

            return ['fecha' => $day, 'registros' => (int) ($registrations->get($day)?->registros ?? 0),
                'ventas' => (int) ($sale?->ventas ?? 0), 'negocios' => (int) ($sale?->negocios ?? 0), 'importe' => round((float) ($sale?->importe ?? 0), 2)];
        })->all();
    }
}
