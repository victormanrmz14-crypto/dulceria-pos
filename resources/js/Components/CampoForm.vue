<script setup>
// Campo de formulario Bootstrap con etiqueta y error de validación
defineProps({
    etiqueta: String,
    error: { type: String, default: null },
    requerido: { type: Boolean, default: false },
});
</script>

<template>
    <div>
        <label class="form-label fw-semibold small text-secondary">
            {{ etiqueta }} <span v-if="requerido" class="text-danger">*</span>
        </label>
        <slot :clase="error ? 'is-invalid' : ''" />
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>
