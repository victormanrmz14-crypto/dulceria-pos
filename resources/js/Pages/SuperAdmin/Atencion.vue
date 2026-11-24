<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { shallowRef, watch } from 'vue';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';
import SaHeading from '../../Components/SuperAdmin/SaHeading.vue';
import SaTable from '../../Components/SuperAdmin/SaTable.vue';
import SaPagination from '../../Components/SuperAdmin/SaPagination.vue';
import SaQueryError from '../../Components/SuperAdmin/SaQueryError.vue';

type Criterion = { tipo: string; label: string; definicion: string };
type Business = { id: number; nombre: string; activo: boolean; creado_en: string | null; senales: Array<Criterion & { evidencia: string }> };
const props = defineProps<{
    tenants: { data: Business[]; total: number; links: Array<object> } | null;
    filters: { tipo?: string };
    criterios: Criterion[];
    periodo: { label: string; zona: string; fin: string };
}>();
const type = shallowRef(props.filters.tipo || '');
watch(() => props.filters.tipo, (value) => { type.value = value || ''; });
const loading = shallowRef(false);
const filter = () => router.get('/superadmin/atencion', { tipo: type.value }, {
    preserveScroll: true,
    onStart: () => { loading.value = true; },
    onFinish: () => { loading.value = false; },
});
const columns = [
    { key: 'nombre', label: 'Dulcería' },
    { key: 'activo', label: 'Estado administrativo' },
    { key: 'senales', label: 'Situación y evidencia' },
    { key: 'accion', label: 'Consultar' },
];
</script>

<template>
    <SuperAdminLayout>
        <Head title="Centro de atención · Superadministración" />
        <SaHeading title="Centro de atención" description="Señales concretas para dar seguimiento a las dulcerías." />
        <p class="sa-note sa-section-gap">Estas señales no prueban abandono, fraude ni fallos técnicos. Se incluyen negocios habilitados y deshabilitados; cada negocio aparece una sola vez.</p>
        <section class="sa-panel">
            <form class="sa-filters" @submit.prevent="filter">
                <div class="sa-field">
                    <label for="attention-type">Tipo de situación</label>
                    <select id="attention-type" v-model="type" name="tipo">
                        <option value="">Todas las situaciones</option>
                        <option v-for="criterion in criterios" :key="criterion.tipo" :value="criterion.tipo">{{ criterion.label }}</option>
                    </select>
                </div>
                <button class="sa-button sa-button--primary" :disabled="loading" :aria-busy="loading">{{ loading ? 'Filtrando…' : 'Filtrar situaciones' }}</button>
                <Link href="/superadmin/atencion" class="sa-link">Limpiar filtro</Link>
            </form>
            <template v-if="tenants">
                <p class="sa-panel-body sa-note" role="status">{{ tenants.total }} {{ tenants.total === 1 ? 'negocio' : 'negocios' }} con la situación seleccionada. Consulta: {{ periodo.fin }} · {{ periodo.zona }}.</p>
                <SaTable :rows="tenants.data" :columns="columns" caption="Seguimiento operativo de dulcerías" empty="No hay negocios con estas señales de seguimiento.">
                    <template #cell-nombre="{ row }">
                        <Link :href="`/superadmin/dulcerias/${row.id}`" class="sa-link">{{ row.nombre }}</Link>
                        <span class="sa-muted sa-stack">Registro: {{ row.creado_en || 'Fecha no disponible' }}</span>
                    </template>
                    <template #cell-activo="{ row }">
                        <span class="sa-status" :class="{ 'sa-status--ok': row.activo }">{{ row.activo ? 'Habilitada' : 'Deshabilitada' }}</span>
                    </template>
                    <template #cell-senales="{ row }">
                        <ul class="sa-signal-list">
                            <li v-for="signal in row.senales" :key="signal.tipo">
                                <strong>{{ signal.label }}</strong>
                                <p>{{ signal.evidencia }}</p>
                            </li>
                        </ul>
                    </template>
                    <template #cell-accion="{ row }"><Link :href="`/superadmin/dulcerias/${row.id}`" class="sa-link" :aria-label="`Ver detalle de ${row.nombre}`">Ver detalle</Link></template>
                </SaTable>
                <SaPagination :paginator="tenants" />
            </template>
            <SaQueryError v-else />
        </section>
        <section class="sa-panel">
            <header><h2>Criterios de seguimiento</h2></header>
            <dl class="sa-detail-list sa-panel-body">
                <template v-for="criterion in criterios" :key="criterion.tipo">
                    <dt>{{ criterion.label }}</dt><dd>{{ criterion.definicion }}</dd>
                </template>
            </dl>
            <p class="sa-panel-body sa-note">Actividad comercial: {{ periodo.label }} · {{ periodo.zona }}. Incluye hoy hasta la consulta y los 29 días anteriores. Los umbrales orientan una revisión operativa.</p>
        </section>
    </SuperAdminLayout>
</template>

<style scoped>
.sa-signal-list { margin: 0; padding-left: 16px; }
.sa-signal-list li + li { margin-top: 12px; }
.sa-signal-list p { margin: 4px 0 0; color: var(--p-muted); }
</style>
