<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';

const props = defineProps({
    usuarios:      Array,
    totalUsuarios: Number,
    totalActivos:  Number,
    totalAdmins:   Number,
});

const busqueda  = ref('');
const filtroRol = ref('todos');

const lista = computed(() => {
    let r = props.usuarios;
    if (filtroRol.value === 'admin')       r = r.filter(u => u.rol === 'admin' && !u.es_super_admin);
    if (filtroRol.value === 'cajero')      r = r.filter(u => u.rol === 'cajero');
    if (filtroRol.value === 'superadmin')  r = r.filter(u => u.es_super_admin);
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        r = r.filter(u =>
            u.nombre.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q) ||
            u.username.toLowerCase().includes(q) ||
            u.tenant_nombre.toLowerCase().includes(q)
        );
    }
    return r;
});

const page = usePage();
const csrf = computed(() => page.props.csrf);

// Reset password por usuario
const resetTarget = ref(null);
const resetForm   = useForm({ password: '', password_confirmation: '' });
const abrirReset  = (u) => { resetTarget.value = u; resetForm.reset(); };
const cerrarReset = () => { resetTarget.value = null; resetForm.reset(); };
const submitReset = () => {
    resetForm.post(`/superadmin/usuarios/${resetTarget.value.id}/reset-password`, {
        onSuccess: () => cerrarReset(),
    });
};

// Reenviar bienvenida
const reenviarBienvenida = (u) => {
    if (!confirm(`¿Generar nueva contraseña y reenviar credenciales a ${u.email}?`)) return;
    const form = useForm({});
    form.post(`/superadmin/usuarios/${u.id}/reenviar-bienvenida`);
};

const rolLabel = (u) => {
    if (u.es_super_admin) return 'Super Admin';
    if (u.rol === 'admin') return 'Admin';
    return 'Cajero';
};
const rolClase = (u) => {
    if (u.es_super_admin) return 'rol-badge--super';
    if (u.rol === 'admin') return 'rol-badge--admin';
    return 'rol-badge--cajero';
};
</script>

<template>
    <SuperAdminLayout>
        <Head title="Usuarios · Admin Panel" />

        <!-- Encabezado -->
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Usuarios</h1>
                <p class="page-sub">Todos los usuarios registrados en la plataforma</p>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--blue">
                    <span class="mini-val">{{ totalUsuarios }}</span>
                    <span class="mini-lbl">Total usuarios</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--green">
                    <span class="mini-val">{{ totalActivos }}</span>
                    <span class="mini-lbl">Activos</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--red">
                    <span class="mini-val">{{ totalUsuarios - totalActivos }}</span>
                    <span class="mini-lbl">Desactivados</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mini-stat mini-stat--purple">
                    <span class="mini-val">{{ totalAdmins }}</span>
                    <span class="mini-lbl">Administradores</span>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="panel">
            <div class="panel-header panel-header--flex">
                <span class="panel-title">Listado de usuarios</span>
                <div class="toolbar">
                    <div class="filter-tabs">
                        <button v-for="[v,l] in [['todos','Todos'],['admin','Admins'],['cajero','Cajeros'],['superadmin','Super']]"
                            :key="v" class="filter-tab" :class="{ 'filter-tab--active': filtroRol === v }"
                            @click="filtroRol = v">{{ l }}</button>
                    </div>
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input v-model="busqueda" type="text" placeholder="Buscar usuario..." class="search-input" />
                    </div>
                </div>
            </div>

            <!-- Tabla escritorio -->
            <div class="table-responsive d-none d-md-block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Dulcería</th>
                            <th>Estado</th>
                            <th>Registrado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="lista.length === 0">
                            <td colspan="9" class="empty-row">Sin resultados para este filtro.</td>
                        </tr>
                        <template v-for="u in lista" :key="u.id">
                        <tr :class="!u.activo ? 'tr--inactivo' : ''">
                            <td class="c-muted">{{ u.id }}</td>
                            <td>
                                <div class="entity-cell">
                                    <div class="entity-av" :class="u.es_super_admin ? 'entity-av--purple' : u.rol === 'admin' ? 'entity-av--blue' : 'entity-av--gray'">
                                        {{ u.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="entity-name">{{ u.nombre }}</span>
                                </div>
                            </td>
                            <td class="c-mono">{{ u.username }}</td>
                            <td class="c-email">{{ u.email }}</td>
                            <td><span class="rol-badge" :class="rolClase(u)">{{ rolLabel(u) }}</span></td>
                            <td class="c-tenant">{{ u.tenant_nombre }}</td>
                            <td>
                                <span class="st-badge" :class="u.activo ? 'st-badge--ok' : 'st-badge--err'">
                                    {{ u.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="c-muted" :title="u.creado_en">{{ u.creado_diff }}</td>
                            <td v-if="!u.es_super_admin">
                                <div class="row-actions">
                                    <button class="act-btn act-btn--blue" @click="abrirReset(u)">🔑 Reset</button>
                                    <form method="POST" :action="`/superadmin/usuarios/${u.id}/reenviar-bienvenida`" class="d-inline" @submit.prevent="reenviarBienvenida(u)">
                                        <button type="button" class="act-btn act-btn--gray" @click="reenviarBienvenida(u)">📧 Reenviar</button>
                                    </form>
                                </div>
                            </td>
                            <td v-else class="c-muted">—</td>
                        </tr>
                        <!-- Fila inline de reset password -->
                        <tr v-if="resetTarget?.id === u.id" class="reset-row">
                            <td colspan="9">
                                <form class="reset-inline" @submit.prevent="submitReset">
                                    <span class="reset-label">Nueva contraseña para <strong>{{ u.email }}</strong></span>
                                    <input v-model="resetForm.password" type="password" placeholder="Nueva contraseña (mín. 8 chars)" class="reset-input" required minlength="8" />
                                    <input v-model="resetForm.password_confirmation" type="password" placeholder="Confirmar contraseña" class="reset-input" required />
                                    <button type="submit" class="act-btn act-btn--blue" :disabled="resetForm.processing">Guardar</button>
                                    <button type="button" class="act-btn act-btn--gray" @click="cerrarReset">Cancelar</button>
                                </form>
                                <div v-if="resetForm.errors.password" class="reset-error">{{ resetForm.errors.password }}</div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Cards móvil -->
            <div class="d-md-none">
                <div v-if="lista.length === 0" class="empty-row">Sin resultados.</div>
                <template v-for="u in lista" :key="u.id">
                <div class="m-card" :class="!u.activo ? 'm-card--inact' : ''">
                    <div class="m-card-head">
                        <div class="entity-cell">
                            <div class="entity-av" :class="u.es_super_admin ? 'entity-av--purple' : u.rol === 'admin' ? 'entity-av--blue' : 'entity-av--gray'">
                                {{ u.nombre.charAt(0).toUpperCase() }}
                            </div>
                            <span class="entity-name">{{ u.nombre }}</span>
                        </div>
                        <span class="st-badge" :class="u.activo ? 'st-badge--ok' : 'st-badge--err'">
                            {{ u.activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="m-rows">
                        <div class="m-row"><span class="m-lbl">Rol</span><span class="rol-badge" :class="rolClase(u)">{{ rolLabel(u) }}</span></div>
                        <div class="m-row"><span class="m-lbl">Email</span><span class="c-email">{{ u.email }}</span></div>
                        <div class="m-row"><span class="m-lbl">Dulcería</span><span class="c-tenant">{{ u.tenant_nombre }}</span></div>
                        <div class="m-row"><span class="m-lbl">Registrado</span><span class="c-muted">{{ u.creado_en }}</span></div>
                    </div>
                    <div v-if="!u.es_super_admin" class="m-actions">
                        <button class="act-btn act-btn--blue" @click="abrirReset(u)">🔑 Resetear</button>
                        <button class="act-btn act-btn--gray" @click="reenviarBienvenida(u)">📧 Reenviar</button>
                    </div>
                    <!-- Reset inline móvil -->
                    <div v-if="resetTarget?.id === u.id" class="m-reset">
                        <form class="reset-inline reset-inline--vertical" @submit.prevent="submitReset">
                            <div class="reset-label">Nueva contraseña para <strong>{{ u.email }}</strong></div>
                            <input v-model="resetForm.password" type="password" placeholder="Nueva contraseña (mín. 8 chars)" class="reset-input" required minlength="8" />
                            <input v-model="resetForm.password_confirmation" type="password" placeholder="Confirmar contraseña" class="reset-input" required />
                            <div class="reset-btns">
                                <button type="submit" class="act-btn act-btn--blue" :disabled="resetForm.processing">Guardar</button>
                                <button type="button" class="act-btn act-btn--gray" @click="cerrarReset">Cancelar</button>
                            </div>
                        </form>
                        <div v-if="resetForm.errors.password" class="reset-error">{{ resetForm.errors.password }}</div>
                    </div>
                </div>
                </template>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
.page-sub    { font-size: .78rem; color: #64748b; margin: .2rem 0 0; }

.mini-stat { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; padding: .9rem 1rem; display: flex; flex-direction: column; gap: .15rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.mini-val  { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; }
.mini-lbl  { font-size: .7rem; color: #64748b; font-weight: 500; }
.mini-stat--blue   { border-left: 3px solid #3b82f6; }
.mini-stat--green  { border-left: 3px solid #10b981; }
.mini-stat--red    { border-left: 3px solid #ef4444; }
.mini-stat--purple { border-left: 3px solid #8b5cf6; }

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.panel-header--flex { justify-content: space-between; flex-wrap: wrap; gap: .6rem; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }

.toolbar { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.filter-tabs { display: flex; gap: .2rem; background: #f1f5f9; border-radius: 8px; padding: .2rem; }
.filter-tab { font-size: .71rem; font-weight: 600; padding: .25rem .65rem; border: none; background: transparent; color: #64748b; border-radius: 6px; cursor: pointer; transition: background .15s, color .15s; }
.filter-tab--active { background: #fff; color: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.search-wrap { position: relative; }
.search-icon { position: absolute; left: .6rem; top: 50%; transform: translateY(-50%); font-size: .75rem; pointer-events: none; }
.search-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .35rem .75rem .35rem 2rem; color: #1e293b; font-size: .78rem; width: 200px; outline: none; transition: border-color .15s; }
.search-input:focus { border-color: #3b82f6; background: #fff; }
.search-input::placeholder { color: #94a3b8; }

.data-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.data-table thead tr { background: #fafafa; }
.data-table th { padding: .6rem 1rem; color: #64748b; font-weight: 600; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #f1f5f9; text-align: left; white-space: nowrap; }
.data-table td { padding: .7rem 1rem; border-bottom: 1px solid #f8fafc; color: #334155; vertical-align: middle; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #f8fafc; }
.tr--inactivo { opacity: .65; }

.c-muted  { color: #94a3b8; font-size: .75rem; }
.c-email  { color: #64748b; font-size: .78rem; }
.c-mono   { font-family: monospace; font-size: .8rem; color: #475569; }
.c-tenant { color: #475569; font-size: .8rem; }

.entity-cell { display: flex; align-items: center; gap: .6rem; }
.entity-av { width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
.entity-av--blue   { background: #eff6ff; color: #3b82f6; }
.entity-av--purple { background: #f5f3ff; color: #7c3aed; }
.entity-av--gray   { background: #f8fafc; color: #94a3b8; }
.entity-name { font-weight: 600; color: #1e293b; }

.rol-badge { display: inline-flex; padding: .18rem .6rem; border-radius: 6px; font-size: .67rem; font-weight: 700; }
.rol-badge--super  { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.rol-badge--admin  { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.rol-badge--cajero { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

.st-badge { display: inline-flex; padding: .2rem .65rem; border-radius: 6px; font-size: .68rem; font-weight: 600; }
.st-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.st-badge--err { background: #fff5f5; color: #dc2626; border: 1px solid #fecaca; }

.empty-row { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }

.m-card { border-bottom: 1px solid #f1f5f9; padding: .85rem 1.1rem; }
.m-card--inact { opacity: .65; }
.m-card:last-child { border-bottom: none; }
.m-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .55rem; }
.m-rows { display: flex; flex-direction: column; gap: .3rem; }
.m-row  { display: flex; justify-content: space-between; align-items: center; font-size: .78rem; color: #475569; }
.m-lbl  { color: #94a3b8; font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.m-actions { display: flex; gap: .5rem; margin-top: .7rem; flex-wrap: wrap; }
.m-reset { margin-top: .7rem; padding-top: .7rem; border-top: 1px solid #f1f5f9; }

/* Botones de acción en filas */
.row-actions { display: flex; gap: .4rem; flex-wrap: wrap; }
.act-btn { font-size: .72rem; font-weight: 600; padding: .3rem .7rem; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: background .15s; }
.act-btn--blue { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.act-btn--blue:hover:not(:disabled) { background: #dbeafe; }
.act-btn--gray { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
.act-btn--gray:hover:not(:disabled) { background: #f1f5f9; }
.act-btn:disabled { opacity: .5; cursor: not-allowed; }

/* Fila de reset inline (escritorio) */
.reset-row { background: #f8fafc; }
.reset-row td { padding: .85rem 1.25rem; }
.reset-inline { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.reset-inline--vertical { flex-direction: column; align-items: stretch; }
.reset-label { font-size: .78rem; color: #475569; white-space: nowrap; }
.reset-input { background: #fff; border: 1px solid #e2e8f0; border-radius: 7px; padding: .35rem .75rem; font-size: .82rem; color: #1e293b; outline: none; min-width: 190px; transition: border-color .15s; }
.reset-input:focus { border-color: #3b82f6; }
.reset-btns { display: flex; gap: .5rem; }
.reset-error { font-size: .72rem; color: #dc2626; margin-top: .3rem; }
.d-inline { display: inline; }
</style>
