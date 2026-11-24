<script setup>
import { Head, router } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaMetric from "../../Components/SuperAdmin/SaMetric.vue";
import SaTable from "../../Components/SuperAdmin/SaTable.vue";
import SaPagination from "../../Components/SuperAdmin/SaPagination.vue";
import { reactive, shallowRef, useTemplateRef } from "vue";
import { useForm } from "@inertiajs/vue3";
import UiOverlay from "../../Components/Ui/UiOverlay.vue";
const props = defineProps({
    usuarios: Object,
    filters: Object,
    totalUsuarios: Number,
    totalActivos: Number,
    totalAdmins: Number,
});
const filters = reactive({
    q: props.filters.q || "",
    rol: props.filters.rol || "",
    estado: props.filters.estado || "",
});
const search = () =>
    router.get("/superadmin/usuarios", filters, { preserveState: true });
import { focusInvalid } from "../../Components/SuperAdmin/focusInvalid";
const resetElement = useTemplateRef("resetElement");
const target = shallowRef(null);
const form = useForm({ password: "", password_confirmation: "" });
const open = (user) => {
    form.reset();
    form.clearErrors();
    target.value = user;
};
const close = () => {
    target.value = null;
    form.reset();
    form.clearErrors();
};
const save = () =>
    form.post(`/superadmin/usuarios/${target.value.id}/reset-password`, {
        onSuccess: close,
        onError: () => focusInvalid(resetElement.value),
    });
const welcome = (user) => {
    if (
        confirm(
            `¿Generar nueva contraseña y reenviar credenciales a ${user.email}?`,
        )
    )
        router.post(`/superadmin/usuarios/${user.id}/reenviar-bienvenida`);
};
const columns = [
    { key: "nombre", label: "Cuenta", width: "24%" },
    { key: "rol", label: "Rol", width: "16%" },
    { key: "tenant_nombre", label: "Negocio", width: "18%" },
    { key: "activo", label: "Estado", width: "12%" },
    { key: "creado_en", label: "Registro", width: "13%" },
    { key: "acciones", label: "Acciones", width: "17%" },
];
</script>
<template>
    <SuperAdminLayout>
        <Head title="Usuarios · Superadministración" />
        <SaHeading
            title="Usuarios"
            description="Cuentas de todos los negocios y de la plataforma."
        />
        <div class="sa-metrics">
            <SaMetric
                label="Cuentas registradas"
                :value="totalUsuarios"
                detail="Incluye cuentas de plataforma"
            />
            <SaMetric
                label="Habilitadas"
                :value="totalActivos"
                detail="Estado de la cuenta"
            />
            <SaMetric
                label="Deshabilitadas"
                :value="totalUsuarios - totalActivos"
                detail="Estado de la cuenta"
            />
            <SaMetric
                label="Administradores"
                :value="totalAdmins"
                detail="Excluye superadministradores"
            />
        </div>
        <section class="sa-panel">
            <form class="sa-filters" @submit.prevent="search">
                <div class="sa-field">
                    <label for="user-search">Buscar cuenta o negocio</label>
                    <input
                        id="user-search"
                        v-model="filters.q"
                        type="search"
                        maxlength="200"
                        placeholder="Nombre, username o correo"
                    />
                </div>
                <div class="sa-field">
                    <label for="user-role">Rol</label>
                    <select id="user-role" v-model="filters.rol">
                        <option value="">Todos los roles</option>
                        <option value="admin">Administrador</option>
                        <option value="cajero">Cajero</option>
                        <option value="superadmin">Superadministrador</option>
                    </select>
                </div>
                <div class="sa-field">
                    <label for="user-state">Estado</label>
                    <select id="user-state" v-model="filters.estado">
                        <option value="">Todos los estados</option>
                        <option value="habilitado">Habilitado</option>
                        <option value="deshabilitado">Deshabilitado</option>
                    </select>
                </div>
                <button class="sa-button sa-button--primary">Filtrar</button>
            </form>
            <SaTable
                :rows="usuarios.data"
                :columns="columns"
                caption="Cuentas globales"
            >
                <template #cell-nombre="{ row }">
                    <div class="sa-stack">
                        <strong>{{ row.nombre }}</strong>
                        <span>{{ row.email }}</span>
                        <span class="sa-muted">
                            Username: {{ row.username }}
                        </span>
                    </div>
                </template>
                <template #cell-rol="{ row }">
                    {{
                        row.es_super_admin
                            ? "Superadministrador"
                            : row.rol === "admin"
                              ? "Administrador"
                              : row.rol
                    }}
                </template>
                <template #cell-activo="{ row }">
                    <span class="sa-status">
                        {{ row.activo ? "Habilitado" : "Deshabilitado" }}
                    </span>
                </template>
                <template #cell-acciones="{ row }">
                    <div class="sa-stack">
                        <button
                            class="sa-button"
                            :aria-label="`Restablecer contraseña de ${row.email}`"
                            @click="open(row)"
                        >
                            Restablecer contraseña
                        </button>
                        <button
                            class="sa-button"
                            :aria-label="`Reenviar bienvenida a ${row.email}`"
                            @click="welcome(row)"
                        >
                            Reenviar bienvenida
                        </button>
                    </div>
                </template>
            </SaTable>
            <SaPagination :paginator="usuarios" />
        </section>
        <UiOverlay
            :open="!!target"
            title="Restablecer contraseña"
            class="sa-overlay"
            @close="close"
        >
            <form ref="resetElement" class="sa-form" @submit.prevent="save">
                <p class="sa-note">
                    Cuenta: {{ target?.email }}. La nueva contraseña reemplazará
                    la actual.
                </p>
                <div class="sa-field">
                    <label for="reset-password">Nueva contraseña</label>
                    <input
                        id="reset-password"
                        v-model="form.password"
                        type="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                        :aria-invalid="!!form.errors.password"
                        aria-describedby="reset-error"
                    />
                    <span id="reset-error" class="sa-error">
                        {{ form.errors.password }}
                    </span>
                </div>
                <div class="sa-field">
                    <label for="reset-confirm">Confirmar contraseña</label>
                    <input
                        id="reset-confirm"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                    />
                </div>
                <div class="sa-form-actions">
                    <button
                        class="sa-button sa-button--primary"
                        :disabled="form.processing"
                        :aria-busy="form.processing"
                    >
                        {{
                            form.processing
                                ? "Guardando…"
                                : "Guardar contraseña"
                        }}
                    </button>
                    <button type="button" class="sa-button" @click="close">
                        Cancelar
                    </button>
                </div>
            </form>
        </UiOverlay>
    </SuperAdminLayout>
</template>
