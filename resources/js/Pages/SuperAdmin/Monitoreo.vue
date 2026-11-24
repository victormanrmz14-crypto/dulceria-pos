<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaTable from "../../Components/SuperAdmin/SaTable.vue";
import SaPagination from "../../Components/SuperAdmin/SaPagination.vue";
defineProps({
    salud: Array,
    tenants: Object,
    failedJobs: Array,
    jobsFallidos: Number,
    comprobadoEn: String,
    zona: String,
    periodo: Object,
});
const columns = [
    { key: "nombre", label: "Dulcería" },
    { key: "activo", label: "Estado administrativo" },
    { key: "plan", label: "Plan" },
    { key: "ventas_30", label: "Ventas en 30 días" },
    { key: "ultima_venta", label: "Última venta histórica" },
    { key: "actividad", label: "Actividad comercial" },
];
</script>
<template>
    <SuperAdminLayout>
        <Head title="Monitoreo · Superadministración" />
        <SaHeading
            title="Monitoreo"
            description="Comprobaciones actuales del sistema y actividad comercial por negocio."
        >
            <button class="sa-button" @click="router.reload()">
                Actualizar comprobaciones
            </button>
        </SaHeading>
        <p class="sa-note sa-section-gap">
            Comprobado el {{ comprobadoEn }} · {{ zona }}. Son comprobaciones
            puntuales; no representan disponibilidad histórica.
        </p>
        <div class="sa-health">
            <article v-for="service in salud" :key="service.nombre">
                <h2>{{ service.nombre }}</h2>
                <span
                    class="sa-status"
                    :class="
                        service.ok === true
                            ? 'sa-status--ok'
                            : service.ok === false
                              ? 'sa-status--danger'
                              : ''
                    "
                >
                    {{
                        service.ok === true
                            ? "Comprobación correcta"
                            : service.ok === false
                              ? "Requiere revisión"
                              : "No disponible"
                    }}
                </span>
                <p>{{ service.detalle }}</p>
                <p v-if="service.alcance">{{ service.alcance }}</p>
            </article>
        </div>
        <section class="sa-panel">
            <header>
                <div>
                    <h2>Jobs fallidos conservados</h2>
                    <p>
                        {{
                            jobsFallidos === null
                                ? "Consulta no disponible"
                                : jobsFallidos +
                                  " registros en total. Hasta diez fallos recientes."
                        }}
                    </p>
                </div>
            </header>
            <SaTable
                :rows="failedJobs"
                :columns="[
                    { key: 'fallido_en', label: 'Fecha del fallo' },
                    { key: 'job', label: 'Proceso' },
                    { key: 'excepcion', label: 'Excepción' },
                ]"
                caption="Últimos jobs fallidos"
                :empty="
                    jobsFallidos === null
                        ? 'No se pudo consultar el registro de fallos.'
                        : 'No hay jobs fallidos conservados.'
                "
            />
        </section>
        <section class="sa-panel">
            <header>
                <div>
                    <h2>Actividad comercial por negocio</h2>
                    <p>
                        {{ periodo.label }} · La última venta considera todo el
                        historial hasta ahora.
                    </p>
                </div>
                <Link href="/superadmin/dulcerias" class="sa-link">
                    Filtrar negocios
                </Link>
            </header>
            <template v-if="tenants">
                <SaTable
                    :rows="tenants.data"
                    :columns="columns"
                    caption="Actividad de las dulcerías"
                >
                    <template #cell-nombre="{ row }">
                        <Link
                            :href="`/superadmin/dulcerias/${row.id}`"
                            class="sa-link"
                        >
                            {{ row.nombre }}
                        </Link>
                    </template>
                    <template #cell-activo="{ row }">
                        <span class="sa-status">
                            {{ row.activo ? "Habilitada" : "Deshabilitada" }}
                        </span>
                    </template>
                    <template #cell-ultima_venta="{ row }">
                        {{ row.ultima_venta || "Nunca ha vendido" }}
                    </template>
                </SaTable>
                <SaPagination :paginator="tenants" />
            </template>
            <p v-else class="sa-empty">
                No se pudo consultar la actividad comercial.
            </p>
        </section>
    </SuperAdminLayout>
</template>
