<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaMetric from "../../Components/SuperAdmin/SaMetric.vue";
import SaTable from "../../Components/SuperAdmin/SaTable.vue";
import SaPagination from "../../Components/SuperAdmin/SaPagination.vue";
import { computed } from "vue";
import SaPlanForm from "../../Components/SuperAdmin/SaPlanForm.vue";
const props = defineProps({ tenants: Object, planes: Object, resumen: Object });
const plans = computed(() =>
    Object.entries(props.planes).map(([id, plan]) => ({ id, ...plan })),
);
const money = (n) =>
    new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(n);
</script>
<template>
    <SuperAdminLayout>
        <Head title="Planes · Superadministración" />
        <SaHeading
            title="Planes"
            description="Distribución de planes y configuración administrativa de cada negocio."
        />
        <div class="sa-metrics">
            <SaMetric
                label="Plan Trial"
                :value="resumen.trial"
                detail="Negocios con este plan asignado"
            />
            <SaMetric
                label="Plan Básico"
                :value="resumen.basico"
                detail="Negocios con este plan asignado"
            />
            <SaMetric
                label="Plan Pro"
                :value="resumen.pro"
                detail="Negocios con este plan asignado"
            />
            <SaMetric
                label="Planes vencidos"
                :value="resumen.vencidos"
                detail="Fecha de expiración alcanzada"
            />
        </div>
        <p class="sa-note sa-section-gap">
            La distribución incluye negocios habilitados, deshabilitados y
            planes vencidos. No hay registro de pagos de plataforma: facturación
            y MRR cobrado no disponibles.
        </p>
        <section class="sa-panel">
            <header>
                <div>
                    <h2>Planes configurados</h2>
                    <p>
                        Tarifas nominales del catálogo; no representan cobros
                        realizados.
                    </p>
                </div>
            </header>
            <SaTable
                :rows="plans"
                :columns="[
                    { key: 'label', label: 'Plan' },
                    { key: 'precio', label: 'Tarifa nominal / mes' },
                    { key: 'max_usuarios', label: 'Máximo de usuarios' },
                    { key: 'max_productos', label: 'Máximo de productos' },
                    { key: 'ventas', label: 'Ventas' },
                ]"
                caption="Catálogo existente de planes"
            >
                <template #cell-precio="{ row }">
                    {{ money(row.precio) }}
                </template>
                <template #cell-max_usuarios="{ row }">
                    {{
                        row.max_usuarios >= 999
                            ? "Ilimitados"
                            : row.max_usuarios
                    }}
                </template>
                <template #cell-max_productos="{ row }">
                    {{
                        row.max_productos >= 999
                            ? "Ilimitados"
                            : row.max_productos
                    }}
                </template>
            </SaTable>
        </section>
        <section class="sa-panel">
            <header><h2>Administración de planes</h2></header>
            <SaTable
                :rows="tenants.data"
                :columns="[
                    { key: 'nombre', label: 'Dulcería' },
                    { key: 'plan', label: 'Plan y vigencia' },
                    { key: 'usuarios', label: 'Usuarios' },
                    { key: 'creado_en', label: 'Registro' },
                    { key: 'form', label: 'Modificar plan' },
                ]"
                caption="Planes por negocio"
            >
                <template #cell-nombre="{ row }">
                    <Link
                        class="sa-link"
                        :href="`/superadmin/dulcerias/${row.id}`"
                    >
                        {{ row.nombre }}
                    </Link>
                    <p class="sa-muted">
                        {{ row.activo ? "Habilitada" : "Deshabilitada" }}
                    </p>
                </template>
                <template #cell-plan="{ row }">
                    <div class="sa-stack">
                        <strong>{{ row.plan_label }}</strong>
                        <span
                            class="sa-status"
                            :class="row.plan_vencido ? 'sa-status--warn' : ''"
                        >
                            {{
                                row.plan_vencido
                                    ? "Vencido"
                                    : row.plan_expira_en
                                      ? "Vigente"
                                      : "Sin expiración configurada"
                            }}
                        </span>
                        <span class="sa-muted">
                            Expira: {{ row.plan_expira_en || "Sin fecha" }}
                        </span>
                        <span
                            v-if="row.dias_restantes !== null"
                            class="sa-muted"
                        >
                            {{ row.dias_restantes }} días de trial restantes
                        </span>
                    </div>
                </template>
                <template #cell-form="{ row }">
                    <SaPlanForm :tenant="row" :planes="planes" />
                </template>
            </SaTable>
            <SaPagination :paginator="tenants" />
        </section>
    </SuperAdminLayout>
</template>
