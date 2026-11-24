<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaMetric from "../../Components/SuperAdmin/SaMetric.vue";
import SaChart from "../../Components/SuperAdmin/SaChart.vue";
const props = defineProps({
    kpis: Object,
    periodo: Object,
    grafica: Array,
    alertas: Array,
});
const money = (n) =>
    new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(n);
</script>
<template>
    <SuperAdminLayout>
        <Head title="Vista general · Superadministración" />
        <SaHeading
            title="Vista general"
            description="Una mirada a los negocios y a su actividad comercial."
        >
            <span class="sa-status">
                <span>{{ periodo.label }}</span>
            </span>
        </SaHeading>
        <div class="sa-metrics">
            <SaMetric
                label="Dulcerías registradas"
                :value="kpis.registradas"
                detail="Total de negocios registrados"
                icon="store"
            />
            <SaMetric
                label="Habilitadas"
                :value="kpis.habilitadas"
                detail="Estado administrativo habilitado"
                icon="shield"
            />
            <SaMetric
                label="Con ventas en 30 días"
                :value="kpis.con_ventas"
                detail="Al menos una venta en el período"
                icon="receipt"
            />
            <SaMetric
                label="Nuevas en el período"
                :value="kpis.nuevas"
                detail="Registros en los últimos 30 días"
                icon="plus"
            />
        </div>
        <p class="sa-note sa-section-gap">
            Período: {{ periodo.label }} · {{ periodo.zona }}. Incluye hoy hasta
            la hora de consulta y los 29 días anteriores. La actividad incluye
            negocios habilitados y deshabilitados con ventas registradas.
        </p>
        <div class="sa-grid sa-section-gap">
            <SaChart
                title="Negocios con actividad comercial"
                description="Negocios distintos con al menos una venta cada día; no es una suma acumulada."
                :rows="grafica"
                field="negocios"
                label="Negocios con ventas"
            />
            <section class="sa-panel" style="margin-bottom: 0">
                <header><h2>Negocios que requieren atención</h2></header>
                <div class="sa-attention-summary">
                    <strong>{{ kpis.atencion }}</strong>
                    <Link
                        href="/superadmin/dulcerias?estado=atencion"
                        class="sa-link"
                    >
                        Revisar negocios
                    </Link>
                </div>
                <ul v-if="alertas.length" class="sa-alert-list">
                    <li v-for="tenant in alertas" :key="tenant.id">
                        <Link :href="`/superadmin/dulcerias/${tenant.id}`">
                            {{ tenant.nombre }}
                        </Link>
                        <p>{{ tenant.problemas.join(" · ") }}</p>
                    </li>
                </ul>
                <p v-else class="sa-empty">
                    Sin situaciones de atención según estos criterios.
                </p>
                <div class="sa-panel-body sa-note">
                    Sin administrador habilitado, plan vencido o negocio
                    habilitado registrado antes del período sin ventas
                    recientes. Un negocio se cuenta una sola vez. La ausencia de
                    ventas no indica un fallo técnico.
                    <br />
                    Se muestran hasta seis negocios.
                    <Link href="/superadmin/monitoreo" class="sa-link">
                        Consultar monitoreo técnico
                    </Link>
                </div>
            </section>
        </div>
        <div class="sa-two sa-section-gap">
            <SaChart
                title="Nuevas dulcerías"
                description="Registros diarios durante el período seleccionado."
                :rows="grafica"
                field="registros"
                label="Registros"
            />
            <SaChart
                title="Ventas registradas"
                description="Cantidad de operaciones comerciales por día."
                :rows="grafica"
                field="ventas"
                label="Ventas"
            />
        </div>
        <section class="sa-panel">
            <header>
                <div>
                    <h2>Volumen comercial registrado</h2>
                    <p>Importes de las dulcerías durante el período</p>
                </div>
                <strong class="sa-money">{{ money(kpis.importe) }}</strong>
            </header>
            <div class="sa-panel-body">
                <p>{{ kpis.ventas }} ventas registradas en el período.</p>
                <p class="sa-note" style="margin: 0">
                    Estos importes corresponden a los negocios. La facturación
                    de Dulcería POS y el MRR cobrado no están disponibles: no
                    existe un registro verificable de cobros de plataforma.
                </p>
            </div>
        </section>
    </SuperAdminLayout>
</template>
