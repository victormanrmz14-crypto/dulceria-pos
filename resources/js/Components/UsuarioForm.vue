<script setup>
import { useId } from 'vue';
import UiFormField from './Ui/UiFormField.vue';
import UiButton from './Ui/UiButton.vue';
import UiIcon from './Ui/UiIcon.vue';

// Formulario compartido de usuario (crear / editar)
defineProps({
    form: Object,
    esEdicion: { type: Boolean, default: false },
    textoBoton: { type: String, default: 'Guardar' },
});

defineEmits(['enviar']);
const roleErrorId = useId();
</script>

<template>
    <form @submit.prevent="$emit('enviar')" class="user-form">
        <section class="user-form-section" aria-labelledby="user-personal-title">
            <div class="user-form-intro"><h2 id="user-personal-title">Información personal</h2><p>Identifica a la persona que utilizará la cuenta.</p></div>
            <div class="user-form-fields">
                <UiFormField etiqueta="Nombre" :error="form.errors.nombre" requerido><template #default="{ control }"><input v-bind="control" v-model="form.nombre" type="text" name="nombre" autocomplete="off" maxlength="150" class="p-field-control" required></template></UiFormField>
                <UiFormField etiqueta="Apellido" :error="form.errors.apellido"><template #default="{ control }"><input v-bind="control" v-model="form.apellido" type="text" name="apellido" autocomplete="off" maxlength="150" class="p-field-control"></template></UiFormField>
            </div>
        </section>
        <section class="user-form-section" aria-labelledby="user-access-title">
            <div class="user-form-intro"><h2 id="user-access-title">Datos de acceso</h2><p v-if="esEdicion">Deja las contraseñas vacías para conservar la actual.</p><p v-else>Define el usuario, el correo y la contraseña de acceso.</p></div>
            <div class="user-form-fields">
                <UiFormField etiqueta="Nombre de usuario" :error="form.errors.username" requerido><template #default="{ control }"><input v-bind="control" v-model="form.username" type="text" name="username" autocomplete="off" :spellcheck="false" maxlength="100" class="p-field-control" required></template></UiFormField>
                <UiFormField etiqueta="Correo electrónico" :error="form.errors.email" requerido><template #default="{ control }"><input v-bind="control" v-model="form.email" type="email" name="email" autocomplete="off" :spellcheck="false" maxlength="255" class="p-field-control" required></template></UiFormField>
                <UiFormField :etiqueta="esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña'" :error="form.errors.password" :requerido="!esEdicion"><template #default="{ control }"><input v-bind="control" v-model="form.password" type="password" name="password" minlength="8" :required="!esEdicion" autocomplete="new-password" class="p-field-control"></template></UiFormField>
                <UiFormField etiqueta="Confirmar contraseña" :error="form.errors.password_confirmation" :requerido="!esEdicion || !!form.password"><template #default="{ control }"><input v-bind="control" v-model="form.password_confirmation" type="password" name="password_confirmation" minlength="8" :required="!esEdicion || !!form.password" autocomplete="new-password" class="p-field-control"></template></UiFormField>
            </div>
        </section>
        <section class="user-form-section" aria-labelledby="user-role-title">
            <div class="user-form-intro"><h2 id="user-role-title">Rol de la cuenta</h2><p>Selecciona uno de los roles disponibles.</p></div>
            <fieldset class="user-role-field" :aria-describedby="form.errors.rol ? roleErrorId : undefined" :aria-invalid="form.errors.rol ? 'true' : undefined">
                <legend class="user-role-label">Rol <span aria-hidden="true">*</span></legend>
                <div class="user-role-options">
                    <button type="button" class="user-role-option" :class="{ 'is-active': form.rol === 'cajero' }" :aria-pressed="form.rol === 'cajero'" @click="form.rol = 'cajero'"><UiIcon name="receipt" />Cajero<UiIcon v-if="form.rol === 'cajero'" name="success" :size="16" /></button>
                    <button type="button" class="user-role-option" :class="{ 'is-active': form.rol === 'admin' }" :aria-pressed="form.rol === 'admin'" @click="form.rol = 'admin'"><UiIcon name="shield" />Administrador<UiIcon v-if="form.rol === 'admin'" name="success" :size="16" /></button>
                </div>
                <p v-if="form.errors.rol" :id="roleErrorId" class="user-role-error" role="alert"><UiIcon name="warning" :size="14" />{{ form.errors.rol }}</p>
            </fieldset>
        </section>
        <div class="user-form-actions"><UiButton href="/usuarios" variant="secondary">Cancelar</UiButton><UiButton type="submit" :loading="form.processing">{{ form.processing ? 'Guardando…' : textoBoton }}</UiButton></div>
    </form>
</template>

<style scoped>
.user-form-section { display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px; padding: 20px 16px; }
.user-form-section + .user-form-section { border-top: 1px solid var(--p-border); }
.user-form-intro h2 { font: inherit; font-size: 14px; font-weight: 600; margin: 0; line-height: 1.5; }
.user-form-intro p { font-size: 12px; color: var(--p-muted); line-height: 1.6; margin: 6px 0 0; max-width: 36ch; }
.user-form-fields { display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px 16px; min-width: 0; }
.user-role-field { margin: 0; padding: 0; border: 0; min-width: 0; }
.user-role-label { float: none; width: auto; font-size: 13px; font-weight: 500; margin-bottom: 8px; }
.user-role-label span { color: var(--p-danger); }
.user-role-options { display: grid; grid-template-columns: minmax(0, 1fr); gap: 8px; }
.user-role-option { display: inline-flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 8px; min-height: 48px; padding: 12px; background: var(--p-surface); color: var(--p-text); border: 1px solid #858a94; border-radius: var(--p-radius-control); font: inherit; font-size: 13px; cursor: pointer; }
.user-role-option:hover { background: var(--p-canvas); }
.user-role-option.is-active { border-color: var(--p-brand-ink); color: var(--p-brand-ink); font-weight: 600; box-shadow: inset 0 0 0 1px var(--p-brand-ink); background: var(--p-surface); }
.user-role-error { display: flex; align-items: flex-start; gap: 6px; color: var(--p-danger); font-size: 12px; line-height: 1.5; margin: 8px 0 0; overflow-wrap: anywhere; }
.user-form-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; padding: 16px; border-top: 1px solid var(--p-border); }
.user-form-actions :deep(.p-button) { flex: 1 1 auto; }
.user-form-actions :deep(.p-button--primary:not(:disabled)) { border-color: var(--p-brand-ink); }
@media (min-width: 400px) { .user-role-options { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 600px) { .user-form-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } .user-form-section { padding: 24px; } .user-form-actions { padding: 20px 24px; } .user-form-actions :deep(.p-button) { flex: 0 1 auto; } }
@media (min-width: 1200px) { .user-form-section { grid-template-columns: 200px minmax(0, 1fr); padding: 28px; gap: 32px; } }
</style>
