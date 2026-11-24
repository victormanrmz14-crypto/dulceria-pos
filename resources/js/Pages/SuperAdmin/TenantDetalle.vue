<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaMetric from "../../Components/SuperAdmin/SaMetric.vue";
import SaTable from "../../Components/SuperAdmin/SaTable.vue";
import SaChart from "../../Components/SuperAdmin/SaChart.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useTemplateRef } from "vue";
import { focusInvalid } from "../../Components/SuperAdmin/focusInvalid";
const notesElement = useTemplateRef("notesElement");
const props = defineProps({
    tenant: Object,
    admin: Object,
    stats: Object,
    ultimasVentas: Array,
    grafica: Array,
    periodo: Object,
});
const page = usePage();
const notes = useForm({ notas: props.tenant.notas || "" });
const save = () =>
    notes.post(`/superadmin/dulcerias/${props.tenant.id}/notas`, {
        preserveScroll: true,
        onError: () => focusInvalid(notesElement.value),
    });
const toggle = (event) => {
    if (
        !confirm(
            `¿${props.tenant.activo ? "Desactivar" : "Activar"} esta dulcería?`,
        )
    )
        event.preventDefault();
};
const money = (n) =>
    new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(n);
</script>
<template>
    <SuperAdminLayout>
        <Head :title="`${tenant.nombre} · Superadministración`" />
        <Link href="/superadmin/dulcerias" class="sa-link sa-section-gap">
            Volver a Dulcerías
        </Link>
        <SaHeading
            :title="tenant.nombre"
            :description="`Registrada el ${tenant.creado_en}`"
        >
            <form
                v-if="admin"
                method="POST"
                :action="`/superadmin/dulcerias/${tenant.id}/impersonar`"
            >
                <input type="hidden" name="_token" :value="page.props.csrf" />
                <button class="sa-button">Impersonar administrador</button>
            </form>
            <form
                method="POST"
                :action="`/superadmin/dulcerias/${tenant.id}/toggle`"
                @submit="toggle"
            >
                <input type="hidden" name="_token" :value="page.props.csrf" />
                <button
                    class="sa-button"
                    :class="tenant.activo ? 'sa-button--danger' : ''"
                >
                    {{
                        tenant.activo
                            ? "Desactivar dulcería"
                            : "Activar dulcería"
                    }}
                </button>
            </form>
        </SaHeading>
        <div class="sa-form-actions sa-section-gap">
            <span
                class="sa-status"
                :class="tenant.activo ? 'sa-status--ok' : ''"
            >
                {{ tenant.activo ? "Habilitada" : "Deshabilitada" }}
            </span>
            <span class="sa-status">Plan {{ tenant.plan_label }}</span>
        </div>
        <div class="sa-metrics">
            <SaMetric
                label="Usuarios asociados"
                :value="stats.usuarios"
                detail="Administradores y cajeros"
            />
            <SaMetric
                label="Productos"
                :value="stats.productos"
                detail="Registros del negocio"
            />
            <SaMetric
                label="Ventas en 30 días"
                :value="stats.ventas_30"
                :detail="periodo.label"
            />
            <SaMetric
                label="Ventas históricas"
                :value="stats.ventas_total"
                detail="Todo el historial hasta ahora"
            />
        </div>
        <div class="sa-two sa-section-gap">
            <section class="sa-panel" style="margin: 0">
                <header><h2>Actividad comercial del negocio</h2></header>
                <div class="sa-panel-body">
                    <dl class="sa-detail-list">
                        <dt>Importe del período</dt>
                        <dd class="sa-money">
                            <strong>{{ money(stats.importe_30) }}</strong>
                        </dd>
                        <dt>Última venta histórica</dt>
                        <dd>
                            {{
                                stats.ultima_venta ||
                                "Nunca ha registrado ventas"
                            }}
                        </dd>
                        <dt>Período</dt>
                        <dd>{{ periodo.label }} · {{ periodo.zona }}</dd>
                    </dl>
                    <p class="sa-note" style="margin: 16px 0 0">
                        Importe comercial de esta dulcería. No representa
                        ingresos de la plataforma.
                    </p>
                </div>
            </section>
            <section class="sa-panel" style="margin: 0">
                <header><h2>Administrador y plan</h2></header>
                <div class="sa-panel-body">
                    <dl class="sa-detail-list">
                        <dt>Administrador</dt>
                        <dd>
                            {{ admin?.nombre || "Sin administrador asignado" }}
                        </dd>
                        <dt>Correo</dt>
                        <dd>{{ admin?.email || "Sin correo asociado" }}</dd>
                        <dt>Estado de cuenta</dt>
                        <dd>
                            {{
                                admin?.activo
                                    ? "Habilitado"
                                    : "Sin administrador habilitado"
                            }}
                        </dd>
                        <dt>Plan</dt>
                        <dd>{{ tenant.plan_label }}</dd>
                        <dt>Expiración</dt>
                        <dd>
                            {{
                                tenant.plan_expira_en || "Sin fecha configurada"
                            }}
                        </dd>
                        <template v-if="tenant.dias_trial !== null">
                            <dt>Trial</dt>
                            <dd>{{ tenant.dias_trial }} días restantes</dd>
                        </template>
                    </dl>
                    <Link href="/superadmin/saas" class="sa-link">
                        Gestionar planes
                    </Link>
                </div>
            </section>
        </div>
        <div class="sa-two sa-section-gap">
            <SaChart
                title="Evolución de ventas"
                :description="periodo.label"
                :rows="grafica"
                field="ventas"
                label="Ventas"
            />
            <SaChart
                title="Importe comercial registrado"
                :description="periodo.label"
                :rows="grafica"
                field="importe"
                label="Importe"
                currency
            />
        </div>
        <section class="sa-panel">
            <header>
                <div>
                    <h2>Últimas operaciones</h2>
                    <p>Hasta ocho ventas del historial completo.</p>
                </div>
            </header>
            <SaTable
                :rows="ultimasVentas"
                :columns="[
                    { key: 'folio', label: 'Folio' },
                    { key: 'total', label: 'Importe' },
                    { key: 'metodo_pago', label: 'Forma de pago' },
                    { key: 'cajero', label: 'Usuario' },
                    { key: 'fecha', label: 'Fecha' },
                ]"
                caption="Últimas ventas del negocio"
                empty="Esta dulcería aún no registra ventas."
            >
                <template #cell-folio="{ row }">
                    {{ row.folio || "#" + row.id }}
                </template>
                <template #cell-total="{ row }">
                    <span class="sa-money">{{ money(row.total) }}</span>
                </template>
            </SaTable>
        </section>
        <section class="sa-panel">
            <header><h2>Notas administrativas</h2></header>
            <form
                ref="notesElement"
                class="sa-panel-body sa-form"
                @submit.prevent="save"
            >
                <div class="sa-field">
                    <label for="tenant-notes">
                        Notas internas de esta dulcería
                    </label>
                    <textarea
                        id="tenant-notes"
                        v-model="notes.notas"
                        rows="5"
                        maxlength="5000"
                        :aria-invalid="!!notes.errors.notas"
                        aria-describedby="notes-help notes-error"
                    />
                    <span id="notes-help" class="sa-muted">
                        Visibles para superadministradores. Máximo 5000
                        caracteres.
                    </span>
                    <span id="notes-error" class="sa-error" role="status">
                        {{ notes.errors.notas }}
                    </span>
                </div>
                <div>
                    <button
                        class="sa-button sa-button--primary"
                        :disabled="notes.processing"
                        :aria-busy="notes.processing"
                    >
                        {{ notes.processing ? "Guardando…" : "Guardar notas" }}
                    </button>
                </div>
            </form>
        </section>
    </SuperAdminLayout>
</template>
