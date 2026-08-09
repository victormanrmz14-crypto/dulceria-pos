<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';
import Grafica from '../../Components/Grafica.vue';

const props = defineProps({
    tenant:        Object,
    admin:         Object,
    stats:         Object,
    ultimasVentas: Array,
    grafica:       Array,
});

const page = usePage();
const csrf = computed(() => page.props.csrf);

const notasForm = useForm({ notas: props.tenant.notas ?? '' });
const saveNotas = () => notasForm.post(`/superadmin/dulcerias/${props.tenant.id}/notas`);

const moneda = (n) => '$' + Number(n ?? 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const datosGrafica = computed(() => ({
    labels: props.grafica.map(d => d.fecha),
    datasets: [{
        label: 'Ventas',
        data: props.grafica.map(d => d.ventas),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,.08)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointBackgroundColor: '#3b82f6',
    }],
}));

const opcionesGrafica = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: 'rgba(0,0,0,.05)' } },
        x: { ticks: { color: '#94a3b8', maxTicksLimit: 7, maxRotation: 0 }, grid: { display: false } },
    },
};

const planClase = (p) => ({ trial: 'plan--trial', basico: 'plan--basico', pro: 'plan--pro' }[p] ?? 'plan--trial');
</script>

<template>
    <SuperAdminLayout>
        <Head :title="`${tenant.nombre} · Admin Panel`" />

        <!-- Breadcrumb -->
        <div class="breadcrumb mb-3">
            <a href="/superadmin/dulcerias" class="breadcrumb-link">← Dulcerías</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">{{ tenant.nombre }}</span>
        </div>

        <!-- Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="tenant-avatar-lg">{{ tenant.nombre.charAt(0).toUpperCase() }}</div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="page-title mb-0">{{ tenant.nombre }}</h1>
                        <span class="plan-badge" :class="planClase(tenant.plan)">{{ tenant.plan_label }}</span>
                        <span class="st-badge" :class="tenant.activo ? 'st-badge--ok' : 'st-badge--err'">
                            {{ tenant.activo ? 'Activa' : 'Desactivada' }}
                        </span>
                    </div>
                    <p class="page-sub">Registrada el {{ tenant.creado_en }}</p>
                </div>
            </div>
            <!-- Acciones -->
            <div class="header-actions">
                <!-- Impersonar -->
                <form v-if="admin" method="POST" :action="`/superadmin/dulcerias/${tenant.id}/impersonar`">
                    <input type="hidden" name="_token" :value="csrf">
                    <button type="submit" class="btn-impersonar">👁️ Impersonar admin</button>
                </form>
                <!-- Toggle activo -->
                <form method="POST" :action="`/superadmin/dulcerias/${tenant.id}/toggle`">
                    <input type="hidden" name="_token" :value="csrf">
                    <button type="submit" class="btn-toggle" :class="tenant.activo ? 'btn-toggle--des' : 'btn-toggle--act'"
                        :onclick="`return confirm('¿${tenant.activo ? 'Desactivar' : 'Activar'} esta dulcería?')`">
                        {{ tenant.activo ? '🔒 Desactivar' : '🔓 Activar' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl">
                <div class="kpi"><div class="kpi-val">{{ stats.usuarios }}</div><div class="kpi-lbl">Usuarios</div></div>
            </div>
            <div class="col-6 col-xl">
                <div class="kpi"><div class="kpi-val">{{ stats.productos }}</div><div class="kpi-lbl">Productos</div></div>
            </div>
            <div class="col-6 col-xl">
                <div class="kpi"><div class="kpi-val">{{ stats.ventas_mes }}</div><div class="kpi-lbl">Ventas este mes</div></div>
            </div>
            <div class="col-6 col-xl">
                <div class="kpi"><div class="kpi-val">{{ stats.ventas_total }}</div><div class="kpi-lbl">Ventas totales</div></div>
            </div>
            <div class="col-12 col-xl">
                <div class="kpi kpi--green"><div class="kpi-val">{{ moneda(stats.ingresos_mes) }}</div><div class="kpi-lbl">Ingresos este mes</div></div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna izquierda: gráfica + últimas ventas -->
            <div class="col-12 col-lg-8">
                <!-- Gráfica -->
                <div class="panel mb-4">
                    <div class="panel-header"><span class="panel-title">Ventas últimos 14 días</span></div>
                    <div style="padding: 1rem 1.25rem 1.25rem;">
                        <Grafica :datos="datosGrafica" :opciones="opcionesGrafica" :alto="180" />
                    </div>
                </div>

                <!-- Últimas ventas -->
                <div class="panel">
                    <div class="panel-header"><span class="panel-title">Últimas ventas</span></div>
                    <div v-if="ultimasVentas.length === 0" class="empty-state">Sin ventas registradas.</div>
                    <table v-else class="data-table">
                        <thead><tr><th>Folio</th><th>Total</th><th>Pago</th><th>Cajero</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <tr v-for="v in ultimasVentas" :key="v.id">
                                <td class="c-mono">{{ v.folio ?? `#${v.id}` }}</td>
                                <td class="c-num">{{ moneda(v.total) }}</td>
                                <td class="c-muted">{{ v.metodo_pago }}</td>
                                <td>{{ v.cajero }}</td>
                                <td class="c-muted">{{ v.fecha }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Columna derecha: admin + plan + notas -->
            <div class="col-12 col-lg-4">
                <!-- Info del admin -->
                <div class="panel mb-4">
                    <div class="panel-header"><span class="panel-title">Administrador</span></div>
                    <div v-if="!admin" class="empty-state text-danger">Sin administrador asignado.</div>
                    <div v-else class="panel-body">
                        <div class="admin-info">
                            <div class="admin-av">{{ admin.nombre.charAt(0).toUpperCase() }}</div>
                            <div>
                                <div class="admin-nombre">{{ admin.nombre }}</div>
                                <div class="admin-email">{{ admin.email }}</div>
                            </div>
                        </div>
                        <span class="st-badge mt-2 d-inline-flex" :class="admin.activo ? 'st-badge--ok' : 'st-badge--err'">
                            {{ admin.activo ? 'Activo' : 'Desactivado' }}
                        </span>
                    </div>
                </div>

                <!-- Plan -->
                <div class="panel mb-4">
                    <div class="panel-header"><span class="panel-title">Plan</span></div>
                    <div class="panel-body">
                        <div class="plan-info">
                            <span class="plan-badge plan-badge-lg" :class="planClase(tenant.plan)">{{ tenant.plan_label }}</span>
                            <div class="plan-meta">
                                <div v-if="tenant.plan_expira_en" class="plan-expira">Expira: {{ tenant.plan_expira_en }}</div>
                                <div v-if="tenant.dias_trial !== null" class="plan-trial">
                                    <span :class="tenant.dias_trial <= 3 ? 'text-danger' : ''">{{ tenant.dias_trial }} días restantes de trial</span>
                                </div>
                            </div>
                        </div>
                        <a href="/superadmin/saas" class="link-plan">Gestionar plan →</a>
                    </div>
                </div>

                <!-- Notas internas -->
                <div class="panel">
                    <div class="panel-header"><span class="panel-title">📝 Notas internas</span></div>
                    <form class="panel-body" @submit.prevent="saveNotas">
                        <textarea
                            v-model="notasForm.notas"
                            class="notas-input"
                            rows="6"
                            placeholder="Notas privadas sobre esta dulcería (solo visibles para super admins)..."
                        ></textarea>
                        <button type="submit" class="save-btn" :disabled="notasForm.processing">
                            {{ notasForm.processing ? 'Guardando...' : 'Guardar notas' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
.breadcrumb { display: flex; align-items: center; gap: .5rem; font-size: .78rem; }
.breadcrumb-link { color: #3b82f6; text-decoration: none; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-sep { color: #94a3b8; }
.breadcrumb-current { color: #64748b; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
.page-sub    { font-size: .75rem; color: #64748b; margin: .15rem 0 0; }
.tenant-avatar-lg { width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; flex-shrink: 0; }

.header-actions { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
.btn-impersonar { background: #7c3aed; color: #fff; border: none; border-radius: 8px; padding: .5rem 1rem; font-size: .8rem; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-impersonar:hover { background: #6d28d9; }
.btn-toggle { border: none; border-radius: 8px; padding: .5rem 1rem; font-size: .8rem; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-toggle--des { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }
.btn-toggle--des:hover { background: #fee2e2; }
.btn-toggle--act { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.btn-toggle--act:hover { background: #dcfce7; }

.kpi { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: .9rem 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.kpi--green { border-left: 3px solid #10b981; }
.kpi-val { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
.kpi-lbl { font-size: .7rem; color: #64748b; font-weight: 500; margin-top: .15rem; }

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.panel-title  { font-size: .88rem; font-weight: 700; color: #1e293b; }
.panel-body   { padding: 1.1rem 1.25rem; }

.data-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.data-table th { padding: .55rem 1rem; color: #64748b; font-size: .67rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; border-bottom: 1px solid #f1f5f9; text-align: left; background: #fafafa; }
.data-table td { padding: .65rem 1rem; border-bottom: 1px solid #f8fafc; color: #334155; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #f8fafc; }
.c-muted { color: #94a3b8; font-size: .73rem; }
.c-mono  { font-family: monospace; font-size: .78rem; color: #475569; }
.c-num   { font-weight: 700; color: #0f172a; }

.st-badge { display: inline-flex; padding: .2rem .65rem; border-radius: 6px; font-size: .68rem; font-weight: 600; }
.st-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.st-badge--err { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }

.plan-badge { display: inline-flex; padding: .18rem .6rem; border-radius: 6px; font-size: .68rem; font-weight: 700; text-transform: uppercase; }
.plan-badge-lg { font-size: .78rem; padding: .25rem .8rem; }
.plan--trial  { background: #f1f5f9; color: #64748b; }
.plan--basico { background: #eff6ff; color: #2563eb; }
.plan--pro    { background: #fffbeb; color: #b45309; }

.admin-info { display: flex; align-items: center; gap: .75rem; }
.admin-av   { width: 36px; height: 36px; border-radius: 9px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
.admin-nombre { font-size: .83rem; font-weight: 600; color: #1e293b; }
.admin-email  { font-size: .73rem; color: #64748b; }

.plan-info { display: flex; align-items: center; gap: 1rem; margin-bottom: .75rem; }
.plan-meta { font-size: .75rem; color: #64748b; }
.plan-expira { margin-bottom: .15rem; }
.link-plan { font-size: .75rem; color: #3b82f6; text-decoration: none; }
.link-plan:hover { text-decoration: underline; }

.notas-input { width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .6rem .85rem; font-size: .82rem; color: #1e293b; outline: none; resize: vertical; font-family: inherit; line-height: 1.55; margin-bottom: .75rem; }
.notas-input:focus { border-color: #3b82f6; background: #fff; }
.save-btn { background: #3b82f6; color: #fff; border: none; border-radius: 8px; padding: .5rem 1.1rem; font-size: .8rem; font-weight: 600; cursor: pointer; width: 100%; transition: background .15s; }
.save-btn:hover:not(:disabled) { background: #2563eb; }
.save-btn:disabled { opacity: .6; cursor: not-allowed; }

.empty-state { text-align: center; padding: 2rem 1rem; color: #94a3b8; font-size: .85rem; }
</style>
