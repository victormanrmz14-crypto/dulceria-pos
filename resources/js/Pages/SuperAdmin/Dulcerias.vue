<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';

const props = defineProps({
    tenants:       Array,
    total:         Number,
    conProblemas:  Number,
});

const busqueda     = ref('');
const filtroEstado = ref('todas');

const lista = computed(() => {
    let r = props.tenants;
    if (filtroEstado.value === 'ok')       r = r.filter(t => t.estado === 'ok');
    if (filtroEstado.value === 'problema') r = r.filter(t => t.estado === 'problema');
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        r = r.filter(t =>
            t.nombre.toLowerCase().includes(q) ||
            t.admin_email?.toLowerCase().includes(q) ||
            t.admin_nombre?.toLowerCase().includes(q)
        );
    }
    return r;
});
</script>

<template>
    <SuperAdminLayout>
        <Head title="Dulcerías · Admin Panel" />

        <!-- Encabezado -->
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Dulcerías</h1>
                <p class="page-sub">{{ total }} registradas · {{ conProblemas }} con problemas</p>
            </div>
        </div>

        <!-- Resumen rápido -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--blue">
                    <span class="mini-val">{{ total }}</span>
                    <span class="mini-lbl">Total registradas</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--green">
                    <span class="mini-val">{{ total - conProblemas }}</span>
                    <span class="mini-lbl">Operando bien</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mini-stat" :class="conProblemas > 0 ? 'mini-stat--red' : 'mini-stat--gray'">
                    <span class="mini-val">{{ conProblemas }}</span>
                    <span class="mini-lbl">Con problemas</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--purple">
                    <span class="mini-val">{{ total > 0 ? Math.round(((total - conProblemas) / total) * 100) : 0 }}%</span>
                    <span class="mini-lbl">Tasa activación</span>
                </div>
            </div>
        </div>

        <!-- Panel tabla -->
        <div class="panel">
            <div class="panel-header panel-header--flex">
                <span class="panel-title">Listado completo</span>
                <div class="toolbar">
                    <div class="filter-tabs">
                        <button v-for="[v,l] in [['todas','Todas'],['ok','Correctas'],['problema','Con problemas']]"
                            :key="v" class="filter-tab" :class="{ 'filter-tab--active': filtroEstado === v }"
                            @click="filtroEstado = v">{{ l }}</button>
                    </div>
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input v-model="busqueda" type="text" placeholder="Buscar dulcería..." class="search-input" />
                    </div>
                </div>
            </div>

            <!-- Tabla escritorio -->
            <div class="table-responsive d-none d-md-block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Dulcería</th>
                            <th>Administrador</th>
                            <th>Email del admin</th>
                            <th>Usuarios</th>
                            <th>Estado</th>
                            <th>Problema detectado</th>
                            <th>Registrada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="lista.length === 0">
                            <td colspan="8" class="empty-row">Sin resultados para este filtro.</td>
                        </tr>
                        <tr v-for="t in lista" :key="t.id" :class="t.estado === 'problema' ? 'tr--problema' : ''">
                            <td class="c-muted">{{ t.id }}</td>
                            <td>
                                <div class="entity-cell">
                                    <div class="entity-av" :class="t.estado === 'problema' ? 'entity-av--red' : 'entity-av--blue'">
                                        {{ t.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="entity-name">{{ t.nombre }}</span>
                                </div>
                            </td>
                            <td>
                                <span v-if="t.admin_nombre">{{ t.admin_nombre }}</span>
                                <span v-else class="c-none">Sin admin</span>
                            </td>
                            <td class="c-email">{{ t.admin_email ?? '—' }}</td>
                            <td class="c-num">{{ t.usuarios }}</td>
                            <td>
                                <span class="st-badge" :class="t.estado === 'ok' ? 'st-badge--ok' : 'st-badge--err'">
                                    {{ t.estado === 'ok' ? 'Correcta' : 'Problema' }}
                                </span>
                            </td>
                            <td>
                                <ul v-if="t.problemas.length" class="problem-list">
                                    <li v-for="(p,i) in t.problemas" :key="i">{{ p }}</li>
                                </ul>
                                <span v-else class="c-muted">—</span>
                            </td>
                            <td class="c-muted" :title="t.creado_en">{{ t.creado_diff }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cards móvil -->
            <div class="d-md-none">
                <div v-if="lista.length === 0" class="empty-row">Sin resultados.</div>
                <div v-for="t in lista" :key="t.id" class="m-card" :class="t.estado === 'problema' ? 'm-card--prob' : ''">
                    <div class="m-card-head">
                        <div class="entity-cell">
                            <div class="entity-av" :class="t.estado === 'problema' ? 'entity-av--red' : 'entity-av--blue'">
                                {{ t.nombre.charAt(0).toUpperCase() }}
                            </div>
                            <span class="entity-name">{{ t.nombre }}</span>
                        </div>
                        <span class="st-badge" :class="t.estado === 'ok' ? 'st-badge--ok' : 'st-badge--err'">
                            {{ t.estado === 'ok' ? 'OK' : 'Problema' }}
                        </span>
                    </div>
                    <div class="m-rows">
                        <div class="m-row"><span class="m-lbl">Admin</span><span>{{ t.admin_nombre ?? '—' }}</span></div>
                        <div class="m-row"><span class="m-lbl">Email</span><span class="c-email">{{ t.admin_email ?? '—' }}</span></div>
                        <div class="m-row"><span class="m-lbl">Usuarios</span><span>{{ t.usuarios }}</span></div>
                        <div v-if="t.problemas.length" class="m-row">
                            <span class="m-lbl">Problema</span>
                            <span class="c-prob">{{ t.problemas.join(' · ') }}</span>
                        </div>
                        <div class="m-row"><span class="m-lbl">Registrada</span><span class="c-muted">{{ t.creado_en }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
.page-sub    { font-size: .78rem; color: #64748b; margin: .2rem 0 0; }

/* Mini stats */
.mini-stat {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: .9rem 1rem;
    display: flex; flex-direction: column; gap: .15rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.mini-val  { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; }
.mini-lbl  { font-size: .7rem; color: #64748b; font-weight: 500; }
.mini-stat--blue   { border-left: 3px solid #3b82f6; }
.mini-stat--green  { border-left: 3px solid #10b981; }
.mini-stat--red    { border-left: 3px solid #ef4444; }
.mini-stat--gray   { border-left: 3px solid #94a3b8; }
.mini-stat--purple { border-left: 3px solid #8b5cf6; }

/* Panel */
.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.panel-header--flex { justify-content: space-between; flex-wrap: wrap; gap: .6rem; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }

/* Toolbar */
.toolbar { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.filter-tabs { display: flex; gap: .2rem; background: #f1f5f9; border-radius: 8px; padding: .2rem; }
.filter-tab { font-size: .71rem; font-weight: 600; padding: .25rem .65rem; border: none; background: transparent; color: #64748b; border-radius: 6px; cursor: pointer; transition: background .15s, color .15s; }
.filter-tab--active { background: #fff; color: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.search-wrap { position: relative; }
.search-icon { position: absolute; left: .6rem; top: 50%; transform: translateY(-50%); font-size: .75rem; pointer-events: none; }
.search-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .35rem .75rem .35rem 2rem; color: #1e293b; font-size: .78rem; width: 200px; outline: none; transition: border-color .15s; }
.search-input:focus { border-color: #3b82f6; background: #fff; }
.search-input::placeholder { color: #94a3b8; }

/* Tabla */
.data-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.data-table thead tr { background: #fafafa; }
.data-table th { padding: .6rem 1rem; color: #64748b; font-weight: 600; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #f1f5f9; text-align: left; white-space: nowrap; }
.data-table td { padding: .7rem 1rem; border-bottom: 1px solid #f8fafc; color: #334155; vertical-align: middle; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #f8fafc; }
.tr--problema { background: #fffbfb; }
.tr--problema:hover { background: #fff5f5 !important; }

.c-muted { color: #94a3b8; font-size: .75rem; }
.c-email { color: #64748b; font-size: .78rem; }
.c-num   { font-weight: 700; color: #0f172a; }
.c-none  { color: #94a3b8; font-style: italic; font-size: .78rem; }
.c-prob  { color: #dc2626; font-size: .75rem; }

.entity-cell { display: flex; align-items: center; gap: .6rem; }
.entity-av { width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
.entity-av--blue { background: #eff6ff; color: #3b82f6; }
.entity-av--red  { background: #fee2e2; color: #dc2626; }
.entity-name { font-weight: 600; color: #1e293b; }

.st-badge { display: inline-flex; padding: .2rem .65rem; border-radius: 6px; font-size: .68rem; font-weight: 600; }
.st-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.st-badge--err { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }

.problem-list { margin: 0; padding: 0 0 0 1rem; color: #dc2626; font-size: .75rem; }
.problem-list li { line-height: 1.6; }

.empty-row { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }

/* Móvil */
.m-card { border-bottom: 1px solid #f1f5f9; padding: .85rem 1.1rem; }
.m-card--prob { background: #fffbfb; }
.m-card:last-child { border-bottom: none; }
.m-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .55rem; }
.m-rows { display: flex; flex-direction: column; gap: .3rem; }
.m-row  { display: flex; justify-content: space-between; font-size: .78rem; color: #475569; }
.m-lbl  { color: #94a3b8; font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>
