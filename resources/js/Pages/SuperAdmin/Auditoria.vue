<script setup>
import { Head, router } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaTable from "../../Components/SuperAdmin/SaTable.vue";
import SaPagination from "../../Components/SuperAdmin/SaPagination.vue";
import { reactive } from "vue";
const props = defineProps({ logs: Object, filters: Object, acciones: Array });
const filters = reactive({
    q: props.filters.q || "",
    accion: props.filters.accion || "",
});
const search = () =>
    router.get("/superadmin/auditoria", filters, { preserveState: true });
</script>
<template>
    <SuperAdminLayout>
        <Head title="Auditoría · Superadministración" />
        <SaHeading
            title="Auditoría"
            description="Eventos administrativos conservados por la plataforma."
        />
        <section class="sa-panel">
            <form class="sa-filters" @submit.prevent="search">
                <div class="sa-field">
                    <label for="audit-search">
                        Buscar evento, usuario o negocio
                    </label>
                    <input
                        id="audit-search"
                        v-model="filters.q"
                        type="search"
                        maxlength="200"
                    />
                </div>
                <div class="sa-field">
                    <label for="audit-action">Acción</label>
                    <select id="audit-action" v-model="filters.accion">
                        <option value="">Todas las acciones</option>
                        <option v-for="action in acciones" :key="action">
                            {{ action }}
                        </option>
                    </select>
                </div>
                <button class="sa-button sa-button--primary">
                    Filtrar eventos
                </button>
            </form>
            <SaTable
                :rows="logs.data"
                :columns="[
                    { key: 'fecha', label: 'Fecha' },
                    { key: 'accion', label: 'Acción' },
                    { key: 'descripcion', label: 'Descripción y metadatos' },
                    { key: 'usuario', label: 'Usuario' },
                    { key: 'tenant', label: 'Negocio' },
                    { key: 'ip', label: 'Dirección IP' },
                ]"
                caption="Eventos administrativos"
            >
                <template #cell-accion="{ row }">
                    <span class="sa-status">{{ row.accion }}</span>
                </template>
                <template #cell-descripcion="{ row }">
                    <p>{{ row.descripcion }}</p>
                    <details
                        v-if="row.meta && Object.keys(row.meta).length"
                        class="sa-meta"
                    >
                        <summary>Ver metadatos</summary>
                        <pre>{{ JSON.stringify(row.meta, null, 2) }}</pre>
                    </details>
                </template>
            </SaTable>
            <SaPagination :paginator="logs" />
        </section>
        <p class="sa-note">
            Las fechas siguen la zona horaria del sistema, America/Mexico_City.
            La ausencia de un evento en este registro no demuestra que una
            operación nunca ocurrió.
        </p>
    </SuperAdminLayout>
</template>
