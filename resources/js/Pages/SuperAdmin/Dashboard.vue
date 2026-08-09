<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';
import Grafica from '../../Components/Grafica.vue';

const props = defineProps({
    totalTenants:        Number,
    tenantsMes:          Number,
    totalUsuarios:       Number,
    tenantsActivos:      Number,
    tenantsConProblemas: Number,
    grafica:             Array,
    tenants:             Array,
    alertas:             Array,
});

// ── Filtros tabla ──────────────────────────────────────────────
const busqueda    = ref('');
const filtroEstado = ref('todas');

const tenantsFiltrados = computed(() => {
    let lista = props.tenants;
    if (filtroEstado.value === 'ok')       lista = lista.filter(t => t.estado === 'ok');
    if (filtroEstado.value === 'problema') lista = lista.filter(t => t.estado === 'problema');
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        lista = lista.filter(t =>
            t.nombre.toLowerCase().includes(q) ||
            t.admin_email?.toLowerCase().includes(q) ||
            t.admin_nombre?.toLowerCase().includes(q)
        );
    }
    return lista;
});

// ── Gráfica ────────────────────────────────────────────────────
const datosGrafica = computed(() => ({
    labels: props.grafica.map(d => d.fecha),
    datasets: [{
        label: 'Registros',
        data: props.grafica.map(d => d.total),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#3b82f6',
        pointRadius: 3,
    }],
}));

const opcionesGrafica = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: { label: ctx => ` ${ctx.raw} registro${ctx.raw !== 1 ? 's' : ''}` },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1, color: '#94a3b8' },
            grid: { color: 'rgba(0,0,0,.05)' },
        },
        x: {
            ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
            grid: { display: false },
        },
    },
};

const tasaActivacion = computed(() =>
    props.totalTenants ? Math.round((props.tenantsActivos / props.totalTenants) * 100) : 0
);
</script>

<template>
    <SuperAdminLayout>
        <Head title="Panel de Plataforma" />

        <!-- ── KPIs ─────────────────────────────────────────── -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="kpi-card kpi-card--blue">
                    <div class="kpi-icon">🏪</div>
                    <div>
                        <div class="kpi-value">{{ totalTenants }}</div>
                        <div class="kpi-label">Dulcerías registradas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="kpi-card kpi-card--green">
                    <div class="kpi-icon">✅</div>
                    <div>
                        <div class="kpi-value">{{ tenantsActivos }}</div>
                        <div class="kpi-label">Operando correctamente</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="kpi-card" :class="tenantsConProblemas > 0 ? 'kpi-card--red' : 'kpi-card--gray'">
                    <div class="kpi-icon">⚠️</div>
                    <div>
                        <div class="kpi-value">{{ tenantsConProblemas }}</div>
                        <div class="kpi-label">Requieren atención</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="kpi-card kpi-card--purple">
                    <div class="kpi-icon">👥</div>
                    <div>
                        <div class="kpi-value">{{ totalUsuarios }}</div>
                        <div class="kpi-label">Usuarios en plataforma</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Alertas ───────────────────────────────────────── -->
        <div v-if="alertas.length > 0" class="panel mb-4">
            <div class="panel-header panel-header--red">
                <span class="panel-title">⚠️ Dulcerías que requieren atención</span>
                <span class="badge-count badge-count--red">{{ alertas.length }}</span>
            </div>
            <div class="alerta-list">
                <div v-for="t in alertas" :key="t.id" class="alerta-item">
                    <div class="alerta-avatar">{{ t.nombre.charAt(0).toUpperCase() }}</div>
                    <div class="alerta-info">
                        <span class="alerta-nombre">{{ t.nombre }}</span>
                        <div class="alerta-problemas">
                            <span
                                v-for="(p, i) in t.problemas"
                                :key="i"
                                class="problema-tag"
                            >{{ p }}</span>
                        </div>
                    </div>
                    <span class="alerta-fecha">{{ t.creado_diff }}</span>
                </div>
            </div>
        </div>

        <!-- ── Gráfica ───────────────────────────────────────── -->
        <div class="panel mb-4">
            <div class="panel-header">
                <span class="panel-title">Registros de dulcerías — últimos 30 días</span>
                <span class="panel-sub">{{ tenantsMes }} este mes</span>
            </div>
            <div class="panel-body">
                <Grafica :datos="datosGrafica" :opciones="opcionesGrafica" :alto="200" />
            </div>
        </div>

        <!-- ── Tabla diagnóstico ─────────────────────────────── -->
        <div class="panel">
            <div class="panel-header panel-header--flex">
                <div>
                    <span class="panel-title">Diagnóstico de dulcerías</span>
                    <span class="panel-sub ms-2">{{ tenantsFiltrados.length }} de {{ totalTenants }}</span>
                </div>
                <div class="toolbar">
                    <div class="filter-tabs">
                        <button
                            v-for="[val, lbl] in [['todas','Todas'],['ok','Correctas'],['problema','Con problemas']]"
                            :key="val"
                            class="filter-tab"
                            :class="{ 'filter-tab--active': filtroEstado === val }"
                            @click="filtroEstado = val"
                        >{{ lbl }}</button>
                    </div>
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input
                            v-model="busqueda"
                            type="text"
                            placeholder="Buscar..."
                            class="search-input"
                        />
                    </div>
                </div>
            </div>

            <!-- Tabla escritorio -->
            <div class="table-responsive d-none d-md-block">
                <table class="diag-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Dulcería</th>
                            <th>Administrador</th>
                            <th>Email</th>
                            <th>Usuarios</th>
                            <th>Estado</th>
                            <th>Registrada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tenantsFiltrados.length === 0">
                            <td colspan="7" class="empty-cell">Sin resultados.</td>
                        </tr>
                        <tr
                            v-for="t in tenantsFiltrados"
                            :key="t.id"
                            :class="t.estado === 'problema' ? 'row--problema' : ''"
                        >
                            <td class="cell-id">{{ t.id }}</td>
                            <td>
                                <div class="tenant-cell">
                                    <div class="tenant-av" :class="t.estado === 'problema' ? 'tenant-av--red' : 'tenant-av--blue'">
                                        {{ t.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="tenant-nombre">{{ t.nombre }}</span>
                                </div>
                            </td>
                            <td>
                                <span v-if="t.admin_nombre">{{ t.admin_nombre }}</span>
                                <span v-else class="cell-none">Sin admin</span>
                            </td>
                            <td class="cell-email">
                                <span v-if="t.admin_email">{{ t.admin_email }}</span>
                                <span v-else class="cell-none">—</span>
                            </td>
                            <td class="cell-num">{{ t.usuarios }}</td>
                            <td>
                                <div v-if="t.estado === 'ok'" class="status-badge status-badge--ok">Correcta</div>
                                <div v-else class="status-badge status-badge--err">
                                    <span v-for="(p, i) in t.problemas" :key="i" class="d-block">{{ p }}</span>
                                </div>
                            </td>
                            <td class="cell-fecha" :title="t.creado_en">{{ t.creado_diff }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cards móvil -->
            <div class="d-md-none">
                <div v-if="tenantsFiltrados.length === 0" class="empty-cell">Sin resultados.</div>
                <div
                    v-for="t in tenantsFiltrados"
                    :key="t.id"
                    class="mobile-card"
                    :class="t.estado === 'problema' ? 'mobile-card--problema' : ''"
                >
                    <div class="mobile-card-head">
                        <div class="tenant-cell">
                            <div class="tenant-av" :class="t.estado === 'problema' ? 'tenant-av--red' : 'tenant-av--blue'">
                                {{ t.nombre.charAt(0).toUpperCase() }}
                            </div>
                            <span class="tenant-nombre">{{ t.nombre }}</span>
                        </div>
                        <div v-if="t.estado === 'ok'" class="status-badge status-badge--ok">Correcta</div>
                        <div v-else class="status-badge status-badge--err">Problema</div>
                    </div>
                    <div class="mobile-rows">
                        <div class="mobile-row"><span class="mobile-lbl">Admin</span><span>{{ t.admin_nombre ?? '—' }}</span></div>
                        <div class="mobile-row"><span class="mobile-lbl">Email</span><span class="cell-email">{{ t.admin_email ?? '—' }}</span></div>
                        <div class="mobile-row"><span class="mobile-lbl">Usuarios</span><span>{{ t.usuarios }}</span></div>
                        <div v-if="t.problemas.length" class="mobile-row">
                            <span class="mobile-lbl">Problema</span>
                            <span class="cell-problema">{{ t.problemas.join(', ') }}</span>
                        </div>
                        <div class="mobile-row"><span class="mobile-lbl">Registrada</span><span>{{ t.creado_en }}</span></div>
                    </div>
                </div>
            </div>
        </div>

    </SuperAdminLayout>
</template>

<style scoped>
/* ── KPI Cards ── */
.kpi-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 1.1rem 1rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    transition: box-shadow .15s;
}
.kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.07); }
.kpi-icon { font-size: 1.6rem; line-height: 1; flex-shrink: 0; }
.kpi-value {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1.1;
    color: #0f172a;
}
.kpi-label { font-size: .72rem; color: #64748b; font-weight: 500; margin-top: .1rem; }

.kpi-card--blue  { border-left: 3px solid #3b82f6; }
.kpi-card--green { border-left: 3px solid #10b981; }
.kpi-card--red   { border-left: 3px solid #ef4444; }
.kpi-card--gray  { border-left: 3px solid #94a3b8; }
.kpi-card--purple{ border-left: 3px solid #8b5cf6; }

/* ── Panel genérico ── */
.panel {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.panel-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fafafa;
}
.panel-header--flex {
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .6rem;
}
.panel-header--red { background: #fff5f5; border-bottom-color: #fee2e2; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }
.panel-sub { font-size: .75rem; color: #94a3b8; }
.panel-body { padding: 1rem 1.25rem 1.25rem; }

.badge-count {
    font-size: .68rem;
    font-weight: 700;
    padding: .15rem .55rem;
    border-radius: 999px;
    letter-spacing: .03em;
}
.badge-count--red { background: #fee2e2; color: #dc2626; }

/* ── Alertas ── */
.alerta-list { }
.alerta-item {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .75rem 1.25rem;
    border-bottom: 1px solid #fff5f5;
}
.alerta-item:last-child { border-bottom: none; }
.alerta-avatar {
    width: 36px; height: 36px;
    border-radius: 9px;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: .9rem;
    flex-shrink: 0;
}
.alerta-info { flex: 1; min-width: 0; }
.alerta-nombre { display: block; font-size: .83rem; font-weight: 600; color: #1e293b; }
.alerta-problemas { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .25rem; }
.problema-tag {
    font-size: .67rem;
    background: #fee2e2;
    color: #b91c1c;
    padding: .15rem .5rem;
    border-radius: 999px;
    font-weight: 600;
    border: 1px solid #fecaca;
}
.alerta-fecha { font-size: .72rem; color: #94a3b8; white-space: nowrap; }

/* ── Toolbar ── */
.toolbar { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.filter-tabs {
    display: flex;
    gap: .2rem;
    background: #f1f5f9;
    border-radius: 8px;
    padding: .2rem;
}
.filter-tab {
    font-size: .71rem;
    font-weight: 600;
    padding: .25rem .65rem;
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.filter-tab--active { background: #fff; color: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.search-wrap { position: relative; }
.search-icon { position: absolute; left: .6rem; top: 50%; transform: translateY(-50%); font-size: .75rem; pointer-events: none; }
.search-input {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .35rem .75rem .35rem 2rem;
    color: #1e293b;
    font-size: .78rem;
    width: 200px;
    outline: none;
    transition: border-color .15s;
}
.search-input:focus { border-color: #3b82f6; background: #fff; }
.search-input::placeholder { color: #94a3b8; }

/* ── Tabla diagnóstico ── */
.diag-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .82rem;
}
.diag-table thead tr { background: #fafafa; }
.diag-table th {
    padding: .65rem 1rem;
    color: #64748b;
    font-weight: 600;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
    white-space: nowrap;
}
.diag-table td {
    padding: .7rem 1rem;
    border-bottom: 1px solid #f8fafc;
    color: #334155;
    vertical-align: middle;
}
.diag-table tbody tr:last-child td { border-bottom: none; }
.diag-table tbody tr:hover { background: #f8fafc; }
.row--problema { background: #fffbfb; }
.row--problema:hover { background: #fff5f5 !important; }

.cell-id   { color: #94a3b8; font-size: .75rem; }
.cell-email{ color: #64748b; font-size: .78rem; }
.cell-fecha{ color: #94a3b8; font-size: .75rem; white-space: nowrap; }
.cell-num  { font-weight: 700; color: #1e293b; }
.cell-none { color: #94a3b8; font-style: italic; font-size: .78rem; }
.cell-problema { color: #dc2626; font-size: .75rem; }

.tenant-cell { display: flex; align-items: center; gap: .6rem; }
.tenant-av {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .8rem; flex-shrink: 0;
}
.tenant-av--blue { background: #eff6ff; color: #3b82f6; }
.tenant-av--red  { background: #fee2e2; color: #dc2626; }
.tenant-nombre   { font-weight: 600; color: #1e293b; }

.empty-cell { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }

/* Badges estado */
.status-badge {
    display: inline-flex;
    flex-direction: column;
    padding: .2rem .65rem;
    border-radius: 6px;
    font-size: .68rem;
    font-weight: 600;
    line-height: 1.5;
}
.status-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.status-badge--err { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }

/* ── Mobile cards ── */
.mobile-card {
    border-bottom: 1px solid #f1f5f9;
    padding: .85rem 1.1rem;
}
.mobile-card--problema { background: #fffbfb; }
.mobile-card:last-child { border-bottom: none; }
.mobile-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .55rem; }
.mobile-rows { display: flex; flex-direction: column; gap: .3rem; }
.mobile-row { display: flex; justify-content: space-between; font-size: .78rem; color: #475569; }
.mobile-lbl { color: #94a3b8; font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>
