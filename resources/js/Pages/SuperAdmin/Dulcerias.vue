<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaMetric from "../../Components/SuperAdmin/SaMetric.vue";
import SaTable from "../../Components/SuperAdmin/SaTable.vue";
import SaPagination from "../../Components/SuperAdmin/SaPagination.vue";
import { reactive } from "vue";
const props = defineProps({
    tenants: Object,
    filters: Object,
    total: Number,
    habilitadas: Number,
    conProblemas: Number,
    periodo: Object,
});
const filters = reactive({
    q: props.filters.q || "",
    estado: props.filters.estado || "",
    actividad: props.filters.actividad || "",
});
const search = () =>
    router.get("/superadmin/dulcerias", filters, {
        preserveState: true,
        preserveScroll: true,
    });
const columns = [
    { key: "nombre", label: "Dulcería" },
    { key: "activo", label: "Estado / plan" },
    { key: "admin", label: "Administrador" },
    { key: "usuarios", label: "Usuarios" },
    { key: "actividad", label: "Actividad comercial" },
    { key: "problemas", label: "Atención" },
    { key: "creado_en", label: "Registro" },
];
</script>
<template>
    <SuperAdminLayout>
        <Head title="Dulcerías · Superadministración" />
        <SaHeading
            title="Dulcerías"
            description="Estado administrativo, equipo y actividad de cada negocio."
        />
        <div class="sa-metrics">
            <SaMetric
                label="Registradas"
                :value="total"
                detail="Todos los negocios"
            />
            <SaMetric
                label="Habilitadas"
                :value="habilitadas"
                detail="Estado administrativo"
            />
            <SaMetric
                label="Deshabilitadas"
                :value="total - habilitadas"
                detail="Estado administrativo"
            />
            <SaMetric
                label="Requieren atención"
                :value="conProblemas"
                detail="Criterios del panel general"
            />
        </div>
        <section class="sa-panel">
            <form class="sa-filters" @submit.prevent="search">
                <div class="sa-field">
                    <label for="tenant-search">
                        Buscar negocio o administrador
                    </label>
                    <input
                        id="tenant-search"
                        v-model="filters.q"
                        type="search"
                        maxlength="200"
                        placeholder="Nombre o correo"
                    />
                </div>
                <div class="sa-field">
                    <label for="tenant-state">Estado administrativo</label>
                    <select id="tenant-state" v-model="filters.estado">
                        <option value="">Todos los estados</option>
                        <option value="habilitadas">Habilitadas</option>
                        <option value="deshabilitadas">Deshabilitadas</option>
                        <option value="atencion">Requieren atención</option>
                        <option value="ok">Sin alertas de atención</option>
                    </select>
                </div>
                <div class="sa-field">
                    <label for="tenant-activity">Actividad comercial</label>
                    <select id="tenant-activity" v-model="filters.actividad">
                        <option value="">Toda la actividad</option>
                        <option value="reciente">Con ventas en 30 días</option>
                        <option value="sin_recientes">
                            Vendieron antes del período
                        </option>
                        <option value="nunca">Nunca han vendido</option>
                    </select>
                </div>
                <button class="sa-button sa-button--primary">Filtrar</button>
            </form>
            <SaTable
                :rows="tenants.data"
                :columns="columns"
                caption="Dulcerías de la plataforma"
            >
                <template #cell-nombre="{ row }">
                    <Link
                        :href="`/superadmin/dulcerias/${row.id}`"
                        class="sa-link"
                    >
                        {{ row.nombre }}
                    </Link>
                    <span class="sa-muted sa-stack">#{{ row.id }}</span>
                </template>
                <template #cell-activo="{ row }">
                    <div class="sa-stack">
                        <span
                            class="sa-status"
                            :class="row.activo ? 'sa-status--ok' : ''"
                        >
                            {{ row.activo ? "Habilitada" : "Deshabilitada" }}
                        </span>
                        <span class="sa-muted">Plan {{ row.plan }}</span>
                    </div>
                </template>
                <template #cell-admin="{ row }">
                    <div class="sa-stack">
                        <strong>
                            {{ row.admin_nombre || "Sin administrador" }}
                        </strong>
                        <span>
                            {{ row.admin_email || "Sin correo asociado" }}
                        </span>
                        <span class="sa-muted">
                            {{
                                row.admin_activo
                                    ? "Administrador habilitado"
                                    : "Sin administrador habilitado"
                            }}
                        </span>
                    </div>
                </template>
                <template #cell-actividad="{ row }">
                    <div class="sa-stack">
                        <span>{{ row.actividad }}</span>
                        <span class="sa-muted">
                            Última venta: {{ row.ultima_venta || "Nunca" }}
                        </span>
                        <span class="sa-muted">
                            {{ row.ventas_30 }} ventas en 30 días
                        </span>
                    </div>
                </template>
                <template #cell-problemas="{ row }">
                    <ul v-if="row.problemas.length">
                        <li v-for="issue in row.problemas" :key="issue">
                            {{ issue }}
                        </li>
                    </ul>
                    <span v-else class="sa-muted">Sin alertas</span>
                </template>
            </SaTable>
            <SaPagination :paginator="tenants" />
        </section>
        <p class="sa-note">
            Actividad: {{ periodo.label }} · {{ periodo.zona }}. Usuarios
            incluye administradores y cajeros asociados, habilitados o
            deshabilitados.
        </p>
    </SuperAdminLayout>
</template>
