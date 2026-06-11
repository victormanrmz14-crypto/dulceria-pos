<script setup>
import CampoForm from './CampoForm.vue';

// Formulario compartido de usuario (crear / editar)
defineProps({
    form: Object,
    esEdicion: { type: Boolean, default: false },
    textoBoton: { type: String, default: 'Guardar' },
});

defineEmits(['enviar']);
</script>

<template>
    <form @submit.prevent="$emit('enviar')">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Nombre" :error="form.errors.nombre" requerido>
                    <template #default="{ clase }">
                        <input v-model="form.nombre" type="text" maxlength="150"
                               class="form-control" :class="clase" required>
                    </template>
                </CampoForm>
            </div>
            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Apellido" :error="form.errors.apellido">
                    <template #default="{ clase }">
                        <input v-model="form.apellido" type="text" maxlength="150"
                               class="form-control" :class="clase">
                    </template>
                </CampoForm>
            </div>

            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Nombre de usuario" :error="form.errors.username" requerido>
                    <template #default="{ clase }">
                        <input v-model="form.username" type="text" maxlength="100"
                               class="form-control" :class="clase" required>
                    </template>
                </CampoForm>
            </div>
            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Email" :error="form.errors.email" requerido>
                    <template #default="{ clase }">
                        <input v-model="form.email" type="email" maxlength="255"
                               class="form-control" :class="clase" required>
                    </template>
                </CampoForm>
            </div>

            <div class="col-12 col-md-6">
                <CampoForm
                    :etiqueta="esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña'"
                    :error="form.errors.password"
                    :requerido="!esEdicion"
                >
                    <template #default="{ clase }">
                        <input v-model="form.password" type="password" minlength="8"
                               class="form-control" :class="clase" :required="!esEdicion"
                               autocomplete="new-password">
                    </template>
                </CampoForm>
            </div>
            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Confirmar contraseña" :requerido="!esEdicion">
                    <template #default="{ clase }">
                        <input v-model="form.password_confirmation" type="password" minlength="8"
                               class="form-control" :class="clase" :required="!esEdicion || !!form.password"
                               autocomplete="new-password">
                    </template>
                </CampoForm>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold small text-secondary">
                    Rol <span class="text-danger">*</span>
                </label>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="metodo-pago w-100 py-2"
                                :class="{ activo: form.rol === 'cajero' }"
                                @click="form.rol = 'cajero'">
                            🧾 Cajero
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="metodo-pago w-100 py-2"
                                :class="{ activo: form.rol === 'admin' }"
                                @click="form.rol = 'admin'">
                            👑 Administrador
                        </button>
                    </div>
                </div>
                <div v-if="form.errors.rol" class="text-danger small mt-1">{{ form.errors.rol }}</div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary fw-bold px-4" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" />
                {{ textoBoton }}
            </button>
        </div>
    </form>
</template>
