<script setup>
// Modal con estilos Bootstrap controlado por Vue (sin instancia JS de Bootstrap,
// para que conviva sin fricción con el ciclo de vida de los componentes)
defineProps({
    abierto: { type: Boolean, default: false },
    titulo: { type: String, default: '' },
    ancho: { type: String, default: '440px' },
});

defineEmits(['cerrar']);
</script>

<template>
    <Teleport to="body">
        <div v-if="abierto">
            <div class="modal fade show d-block" tabindex="-1" @click.self="$emit('cerrar')">
                <div class="modal-dialog modal-dialog-centered" :style="{ maxWidth: ancho }">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title titulo-marca text-primary">{{ titulo }}</h5>
                            <button type="button" class="btn-close" aria-label="Cerrar" @click="$emit('cerrar')" />
                        </div>
                        <div class="modal-body">
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show" />
        </div>
    </Teleport>
</template>
