<script setup>
import { computed, reactive } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';

const props = defineProps({
    tenants: Array,
    planes:  Object,
    resumen: Object,
});

const page = usePage();

// One form per tenant, keyed by tenant id
const forms = reactive({});
props.tenants.forEach(t => {
    forms[t.id] = useForm({
        plan:           t.plan,
        plan_expira_en: t.plan_expira_en ?? '',
    });
});

const guardarPlan = (tenant) => {
    forms[tenant.id].post(`/superadmin/saas/${tenant.id}/plan`, {
        preserveScroll: true,
    });
};

const planBadgeClass = (plan) => {
    if (plan === 'pro')    return 'badge-pro';
    if (plan === 'basico') return 'badge-basico';
    return 'badge-trial';
};

const planLabel = (plan) => {
    if (plan === 'pro')    return '⭐ Pro';
    if (plan === 'basico') return 'Básico';
    return 'Trial';
};

const moneda = (n) => '$' + Number(n).toLocaleString('es-MX');
</script>

<template>
    <SuperAdminLayout>
        <Head title="Planes & SaaS · Admin Panel" />

        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Planes & SaaS</h1>
                <p class="page-sub">Gestión de suscripciones y facturación estimada</p>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi kpi--gray">
                    <div class="kpi-val">{{ resumen.trial }}</div>
                    <div class="kpi-lbl">En período trial</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi kpi--blue">
                    <div class="kpi-val">{{ resumen.basico }}</div>
                    <div class="kpi-lbl">Plan Básico</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi kpi--gold">
                    <div class="kpi-val">{{ resumen.pro }}</div>
                    <div class="kpi-lbl">Plan Pro</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi kpi--green">
                    <div class="kpi-val">{{ moneda(resumen.mrr) }}</div>
                    <div class="kpi-lbl">MRR estimado</div>
                </div>
            </div>
        </div>

        <!-- Tabla de planes disponibles -->
        <div class="panel mb-4">
            <div class="panel-header">
                <span class="panel-title">Planes disponibles</span>
            </div>
            <div class="table-responsive">
                <table class="dt">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Precio/mes</th>
                            <th>Máx. usuarios</th>
                            <th>Máx. productos</th>
                            <th>Ventas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(info, key) in planes" :key="key">
                            <td>
                                <span class="plan-badge" :class="planBadgeClass(key)">{{ planLabel(key) }}</span>
                            </td>
                            <td class="fw-semibold">{{ info.precio === 0 ? 'Gratis' : moneda(info.precio) }}</td>
                            <td>{{ info.max_usuarios >= 999 ? 'Ilimitados' : info.max_usuarios }}</td>
                            <td>{{ info.max_productos >= 999 ? 'Ilimitados' : info.max_productos }}</td>
                            <td>{{ info.ventas }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla de tenants con cambio de plan -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Dulcerías y suscripciones</span>
                <span class="panel-sub">{{ tenants.length }} registradas</span>
            </div>
            <div class="table-responsive d-none d-lg-block">
                <table class="dt">
                    <thead>
                        <tr>
                            <th>Dulcería</th>
                            <th>Plan actual</th>
                            <th>Expira / Días restantes</th>
                            <th>Usuarios</th>
                            <th>Registrada</th>
                            <th>Cambiar plan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tenants.length === 0">
                            <td colspan="6" class="empty">Sin dulcerías registradas.</td>
                        </tr>
                        <tr v-for="t in tenants" :key="t.id" :class="!t.activo ? 'row-inactivo' : ''">
                            <td>
                                <div class="entity-cell">
                                    <div class="av av--blue">{{ t.nombre.charAt(0).toUpperCase() }}</div>
                                    <div>
                                        <div class="entity-name">{{ t.nombre }}</div>
                                        <div v-if="!t.activo" class="entity-sub text-danger">Desactivada</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="plan-badge" :class="planBadgeClass(t.plan)">{{ planLabel(t.plan) }}</span>
                                <span v-if="t.plan_vencido" class="vencido-tag">Vencido</span>
                            </td>
                            <td class="c-muted">
                                <span v-if="t.plan_expira_en">{{ t.plan_expira_en }}</span>
                                <span v-if="t.plan === 'trial' && t.dias_restantes !== null" class="dias-tag" :class="t.dias_restantes <= 3 ? 'dias-tag--red' : 'dias-tag--blue'">
                                    {{ t.dias_restantes }}d restantes
                                </span>
                                <span v-if="!t.plan_expira_en" class="c-none">Sin vencimiento</span>
                            </td>
                            <td class="c-num">{{ t.usuarios }}</td>
                            <td class="c-muted">{{ t.creado_en }}</td>
                            <td>
                                <form class="plan-form" @submit.prevent="guardarPlan(t)">
                                    <select v-model="forms[t.id].plan" class="plan-select">
                                        <option value="trial">Trial</option>
                                        <option value="basico">Básico</option>
                                        <option value="pro">Pro</option>
                                    </select>
                                    <input v-model="forms[t.id].plan_expira_en" type="date" class="plan-date" />
                                    <button type="submit" class="plan-btn" :disabled="forms[t.id].processing">
                                        {{ forms[t.id].processing ? '...' : 'Guardar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cards móvil -->
            <div class="d-lg-none">
                <div v-if="tenants.length === 0" class="empty">Sin dulcerías.</div>
                <div v-for="t in tenants" :key="t.id" class="m-card">
                    <div class="m-card-head">
                        <span class="entity-name">{{ t.nombre }}</span>
                        <span class="plan-badge" :class="planBadgeClass(t.plan)">{{ planLabel(t.plan) }}</span>
                    </div>
                    <div class="m-rows">
                        <div class="m-row"><span class="m-lbl">Usuarios</span><span>{{ t.usuarios }}</span></div>
                        <div class="m-row"><span class="m-lbl">Expira</span><span>{{ t.plan_expira_en ?? '—' }}</span></div>
                        <div class="m-row"><span class="m-lbl">Registrada</span><span class="c-muted">{{ t.creado_en }}</span></div>
                    </div>
                    <form class="plan-form mt-2" @submit.prevent="guardarPlan(t)">
                        <select v-model="forms[t.id].plan" class="plan-select">
                            <option value="trial">Trial</option>
                            <option value="basico">Básico</option>
                            <option value="pro">Pro</option>
                        </select>
                        <input v-model="forms[t.id].plan_expira_en" type="date" class="plan-date" />
                        <button type="submit" class="plan-btn" :disabled="forms[t.id].processing">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
.page-sub    { font-size: .78rem; color: #64748b; margin: .2rem 0 0; }

.kpi { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1rem 1.1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.kpi-val { font-size: 1.7rem; font-weight: 800; color: #0f172a; line-height: 1; }
.kpi-lbl { font-size: .7rem; color: #64748b; margin-top: .2rem; font-weight: 500; }
.kpi--gray  { border-left: 3px solid #94a3b8; }
.kpi--blue  { border-left: 3px solid #3b82f6; }
.kpi--gold  { border-left: 3px solid #d97706; }
.kpi--green { border-left: 3px solid #10b981; }

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }
.panel-sub { font-size: .75rem; color: #94a3b8; }

.dt { width: 100%; border-collapse: collapse; font-size: .82rem; }
.dt thead tr { background: #fafafa; }
.dt th { padding: .6rem 1rem; color: #64748b; font-weight: 600; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #f1f5f9; text-align: left; white-space: nowrap; }
.dt td { padding: .7rem 1rem; border-bottom: 1px solid #f8fafc; color: #334155; vertical-align: middle; }
.dt tbody tr:last-child td { border-bottom: none; }
.dt tbody tr:hover { background: #f8fafc; }
.row-inactivo { opacity: .6; }

.entity-cell { display: flex; align-items: center; gap: .6rem; }
.av { width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
.av--blue { background: #eff6ff; color: #3b82f6; }
.entity-name { font-weight: 600; color: #1e293b; font-size: .82rem; }
.entity-sub { font-size: .7rem; }

.plan-badge { display: inline-flex; align-items: center; padding: .18rem .6rem; border-radius: 6px; font-size: .7rem; font-weight: 700; letter-spacing: .02em; }
.badge-trial  { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.badge-basico { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.badge-pro    { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

.vencido-tag { display: inline-flex; margin-left: .35rem; background: #fee2e2; color: #dc2626; font-size: .65rem; font-weight: 700; padding: .1rem .4rem; border-radius: 4px; }
.dias-tag { display: inline-flex; margin-left: .35rem; font-size: .68rem; font-weight: 600; padding: .1rem .4rem; border-radius: 4px; }
.dias-tag--blue { background: #eff6ff; color: #2563eb; }
.dias-tag--red  { background: #fee2e2; color: #dc2626; }

.c-muted { color: #94a3b8; font-size: .75rem; }
.c-num   { font-weight: 700; color: #0f172a; }
.c-none  { color: #94a3b8; font-style: italic; font-size: .75rem; }

.plan-form { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
.plan-select { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: .28rem .5rem; font-size: .75rem; color: #334155; outline: none; cursor: pointer; }
.plan-date { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: .28rem .5rem; font-size: .75rem; color: #334155; outline: none; width: 130px; }
.plan-btn { background: #3b82f6; color: #fff; border: none; border-radius: 6px; padding: .3rem .7rem; font-size: .72rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .15s; }
.plan-btn:hover:not(:disabled) { background: #2563eb; }
.plan-btn:disabled { opacity: .5; cursor: not-allowed; }

.empty { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }

.m-card { border-bottom: 1px solid #f1f5f9; padding: .85rem 1.1rem; }
.m-card:last-child { border-bottom: none; }
.m-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; }
.m-rows { display: flex; flex-direction: column; gap: .3rem; }
.m-row  { display: flex; justify-content: space-between; font-size: .78rem; color: #475569; }
.m-lbl  { color: #94a3b8; font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>
