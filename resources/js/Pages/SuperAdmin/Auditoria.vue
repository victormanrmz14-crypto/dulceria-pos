<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';

const props = defineProps({
    logs:    Array,
    tenants: Array,
});

const busqueda      = ref('');
const filtroAccion  = ref('');

const accionesUnicas = computed(() => {
    const set = new Set(props.logs.map(l => l.accion));
    return [...set].sort();
});

const lista = computed(() => {
    let r = props.logs;
    if (filtroAccion.value) r = r.filter(l => l.accion === filtroAccion.value);
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        r = r.filter(l =>
            l.descripcion.toLowerCase().includes(q) ||
            l.usuario.toLowerCase().includes(q) ||
            l.tenant.toLowerCase().includes(q) ||
            l.accion.toLowerCase().includes(q)
        );
    }
    return r;
});

const accionColor = (accion) => {
    if (accion.startsWith('impersonacion')) return 'ac--purple';
    if (['reset_password', 'reenvio_bienvenida'].includes(accion)) return 'ac--orange';
    if (['tenant_toggle', 'notas_tenant'].includes(accion))        return 'ac--blue';
    if (accion === 'plan_cambio')                                   return 'ac--gold';
    if (accion.startsWith('anuncio'))                               return 'ac--green';
    if (accion === 'email_masivo')                                  return 'ac--pink';
    return 'ac--gray';
};
</script>

<template>
    <SuperAdminLayout>
        <Head title="Auditoría · Admin Panel" />

        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Auditoría de Plataforma</h1>
                <p class="page-sub">Últimas 200 acciones registradas</p>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header panel-header--flex">
                <span class="panel-title">Log de actividad</span>
                <div class="toolbar">
                    <select v-model="filtroAccion" class="filter-select">
                        <option value="">Todas las acciones</option>
                        <option v-for="a in accionesUnicas" :key="a" :value="a">{{ a }}</option>
                    </select>
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input v-model="busqueda" type="text" placeholder="Buscar..." class="search-input" />
                    </div>
                </div>
            </div>

            <!-- Tabla escritorio -->
            <div class="table-responsive d-none d-md-block">
                <table class="dt">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th>Dulcería</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="lista.length === 0">
                            <td colspan="6" class="empty">Sin actividad registrada.</td>
                        </tr>
                        <tr v-for="l in lista" :key="l.id">
                            <td class="c-fecha" :title="l.diff">{{ l.fecha }}</td>
                            <td>
                                <span class="ac-badge" :class="accionColor(l.accion)">{{ l.accion }}</span>
                            </td>
                            <td class="c-desc">{{ l.descripcion }}</td>
                            <td class="c-user">{{ l.usuario }}</td>
                            <td class="c-tenant">{{ l.tenant }}</td>
                            <td class="c-ip">{{ l.ip ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cards móvil -->
            <div class="d-md-none">
                <div v-if="lista.length === 0" class="empty">Sin actividad registrada.</div>
                <div v-for="l in lista" :key="l.id" class="m-card">
                    <div class="m-card-head">
                        <span class="ac-badge" :class="accionColor(l.accion)">{{ l.accion }}</span>
                        <span class="c-fecha">{{ l.fecha }}</span>
                    </div>
                    <div class="m-desc">{{ l.descripcion }}</div>
                    <div class="m-rows mt-1">
                        <div class="m-row"><span class="m-lbl">Usuario</span><span class="c-user">{{ l.usuario }}</span></div>
                        <div class="m-row"><span class="m-lbl">Dulcería</span><span>{{ l.tenant }}</span></div>
                        <div class="m-row"><span class="m-lbl">IP</span><span class="c-ip">{{ l.ip ?? '—' }}</span></div>
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

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.panel-header--flex { justify-content: space-between; flex-wrap: wrap; gap: .6rem; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }

.toolbar { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.filter-select { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .35rem .65rem; font-size: .75rem; color: #334155; outline: none; cursor: pointer; }
.filter-select:focus { border-color: #3b82f6; }
.search-wrap { position: relative; }
.search-icon { position: absolute; left: .6rem; top: 50%; transform: translateY(-50%); font-size: .75rem; pointer-events: none; }
.search-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .35rem .75rem .35rem 2rem; color: #1e293b; font-size: .78rem; width: 200px; outline: none; transition: border-color .15s; }
.search-input:focus { border-color: #3b82f6; background: #fff; }
.search-input::placeholder { color: #94a3b8; }

.dt { width: 100%; border-collapse: collapse; }
.dt thead tr { background: #fafafa; }
.dt th { padding: .6rem 1rem; color: #64748b; font-weight: 600; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #f1f5f9; text-align: left; white-space: nowrap; }
.dt td { padding: .65rem 1rem; border-bottom: 1px solid #f8fafc; vertical-align: middle; font-size: .82rem; color: #334155; }
.dt tbody tr:last-child td { border-bottom: none; }
.dt tbody tr:hover { background: #f8fafc; }

.ac-badge { display: inline-flex; padding: .18rem .55rem; border-radius: 5px; font-size: .65rem; font-weight: 700; font-family: monospace; letter-spacing: .01em; white-space: nowrap; }
.ac--purple { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.ac--orange { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
.ac--blue   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.ac--gold   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.ac--green  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.ac--pink   { background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; }
.ac--gray   { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

.c-fecha  { color: #94a3b8; font-size: .72rem; white-space: nowrap; }
.c-desc   { color: #1e293b; font-size: .8rem; max-width: 280px; }
.c-user   { color: #64748b; font-size: .75rem; }
.c-tenant { color: #475569; font-size: .75rem; }
.c-ip     { color: #94a3b8; font-size: .72rem; font-family: monospace; }

.empty { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }

.m-card { border-bottom: 1px solid #f1f5f9; padding: .85rem 1.1rem; }
.m-card:last-child { border-bottom: none; }
.m-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .4rem; flex-wrap: wrap; gap: .4rem; }
.m-desc { font-size: .78rem; color: #334155; line-height: 1.4; }
.m-rows { display: flex; flex-direction: column; gap: .3rem; }
.m-row  { display: flex; justify-content: space-between; font-size: .75rem; color: #475569; }
.m-lbl  { color: #94a3b8; font-size: .67rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>
