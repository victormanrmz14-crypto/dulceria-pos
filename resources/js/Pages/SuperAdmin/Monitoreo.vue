<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';
import Grafica from '../../Components/Grafica.vue';

const props = defineProps({
    salud:        Array,
    tenants:      Array,
    failedJobs:   Array,
    jobsFallidos: Number,
    discoPct:     Number,
});

const busqueda = ref('');
const soloDormidas = ref(false);

const tenantsFiltrados = computed(() => {
    let r = props.tenants;
    if (soloDormidas.value) r = r.filter(t => t.dormida);
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        r = r.filter(t => t.nombre.toLowerCase().includes(q));
    }
    return r;
});

const dormidas = computed(() => props.tenants.filter(t => t.dormida).length);
</script>

<template>
    <SuperAdminLayout>
        <Head title="Monitoreo · Admin Panel" />

        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Monitoreo</h1>
                <p class="page-sub">Estado del sistema y actividad de dulcerías</p>
            </div>
        </div>

        <!-- Salud del sistema -->
        <div class="panel mb-4">
            <div class="panel-header">
                <span class="panel-title">💾 Salud del Sistema</span>
            </div>
            <div class="health-grid">
                <div
                    v-for="item in salud"
                    :key="item.nombre"
                    class="health-card"
                    :class="item.ok ? 'health-card--ok' : 'health-card--err'"
                >
                    <div class="health-status">
                        <span class="health-dot" :class="item.ok ? 'health-dot--ok' : 'health-dot--err'"></span>
                        <span class="health-label">{{ item.nombre }}</span>
                    </div>
                    <div class="health-detalle">{{ item.detalle }}</div>
                    <div class="health-badge" :class="item.ok ? 'health-badge--ok' : 'health-badge--err'">
                        {{ item.ok ? 'Operando' : 'Con problemas' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Jobs fallidos -->
        <div v-if="failedJobs.length > 0" class="panel mb-4">
            <div class="panel-header panel-header--red">
                <span class="panel-title">❌ Jobs Fallidos</span>
                <span class="badge-err">{{ jobsFallidos }}</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Job</th>
                            <th>Error</th>
                            <th>Falló en</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="j in failedJobs" :key="j.id">
                            <td class="c-mono">{{ j.job }}</td>
                            <td class="c-err">{{ j.excepcion }}</td>
                            <td class="c-muted">{{ j.fallido_en }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actividad por dulcería -->
        <div class="panel">
            <div class="panel-header panel-header--flex">
                <div>
                    <span class="panel-title">📊 Actividad por Dulcería</span>
                    <span v-if="dormidas > 0" class="dormidas-badge">{{ dormidas }} sin actividad en 30 días</span>
                </div>
                <div class="toolbar">
                    <label class="toggle-label">
                        <input type="checkbox" v-model="soloDormidas" class="me-1" />
                        Solo dormidas
                    </label>
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input v-model="busqueda" type="text" placeholder="Buscar..." class="search-input" />
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Dulcería</th>
                            <th>Plan</th>
                            <th>Ventas este mes</th>
                            <th>Ventas totales</th>
                            <th>Última venta</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tenantsFiltrados.length === 0">
                            <td colspan="6" class="empty-row">Sin resultados.</td>
                        </tr>
                        <tr v-for="t in tenantsFiltrados" :key="t.id" :class="!t.activo ? 'tr--inactivo' : ''">
                            <td class="c-bold">{{ t.nombre }}</td>
                            <td>
                                <span class="plan-badge" :class="`plan-badge--${t.plan}`">{{ t.plan }}</span>
                            </td>
                            <td class="c-num">{{ t.ventas_mes }}</td>
                            <td class="c-num">{{ t.ventas_total }}</td>
                            <td class="c-muted">{{ t.ultima_diff }}</td>
                            <td>
                                <span v-if="!t.activo" class="st-badge st-badge--err">Desactivada</span>
                                <span v-else-if="t.dormida" class="st-badge st-badge--warn">Dormida</span>
                                <span v-else class="st-badge st-badge--ok">Activa</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
.page-sub    { font-size: .78rem; color: #64748b; margin: .2rem 0 0; }

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; flex-wrap: wrap; }
.panel-header--flex { justify-content: space-between; }
.panel-header--red { background: #fff5f5; border-bottom-color: #fee2e2; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }
.badge-err { background: #fee2e2; color: #dc2626; font-size: .68rem; font-weight: 700; padding: .15rem .55rem; border-radius: 999px; }

/* Health grid */
.health-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1px; background: #f1f5f9; }
.health-card { background: #fff; padding: 1.1rem 1.25rem; }
.health-card--err { background: #fffbfb; }
.health-status { display: flex; align-items: center; gap: .5rem; margin-bottom: .35rem; }
.health-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.health-dot--ok  { background: #10b981; box-shadow: 0 0 6px #10b981; animation: pulse 2s infinite; }
.health-dot--err { background: #ef4444; box-shadow: 0 0 6px #ef4444; }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.4; } }
.health-label  { font-size: .82rem; font-weight: 600; color: #1e293b; }
.health-detalle{ font-size: .72rem; color: #64748b; margin-bottom: .5rem; }
.health-badge  { display: inline-flex; font-size: .67rem; font-weight: 700; padding: .15rem .55rem; border-radius: 999px; }
.health-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.health-badge--err { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }

/* Toolbar */
.toolbar { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.toggle-label { font-size: .75rem; color: #64748b; display: flex; align-items: center; cursor: pointer; }
.search-wrap { position: relative; }
.search-icon { position: absolute; left: .6rem; top: 50%; transform: translateY(-50%); font-size: .75rem; pointer-events: none; }
.search-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .35rem .75rem .35rem 2rem; color: #1e293b; font-size: .78rem; width: 180px; outline: none; }
.search-input:focus { border-color: #3b82f6; }
.search-input::placeholder { color: #94a3b8; }
.dormidas-badge { font-size: .72rem; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: .15rem .55rem; border-radius: 999px; margin-left: .5rem; }

/* Tabla */
.data-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.data-table thead tr { background: #fafafa; }
.data-table th { padding: .6rem 1rem; color: #64748b; font-weight: 600; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #f1f5f9; text-align: left; white-space: nowrap; }
.data-table td { padding: .7rem 1rem; border-bottom: 1px solid #f8fafc; color: #334155; vertical-align: middle; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #f8fafc; }
.tr--inactivo { opacity: .55; }
.c-muted { color: #94a3b8; font-size: .75rem; }
.c-mono  { font-family: monospace; font-size: .78rem; color: #475569; }
.c-err   { color: #dc2626; font-size: .75rem; }
.c-num   { font-weight: 700; color: #0f172a; }
.c-bold  { font-weight: 600; color: #1e293b; }
.empty-row { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }

.plan-badge { display: inline-flex; padding: .15rem .5rem; border-radius: 6px; font-size: .68rem; font-weight: 700; text-transform: uppercase; }
.plan-badge--trial  { background: #f1f5f9; color: #64748b; }
.plan-badge--basico { background: #eff6ff; color: #2563eb; }
.plan-badge--pro    { background: #fffbeb; color: #b45309; }

.st-badge { display: inline-flex; padding: .2rem .65rem; border-radius: 6px; font-size: .68rem; font-weight: 600; }
.st-badge--ok   { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.st-badge--err  { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }
.st-badge--warn { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
</style>
