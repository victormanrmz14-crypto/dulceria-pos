<script setup lang="ts">
import { router } from '@inertiajs/vue3';
</script>

<template>
    <section class="sa-panel" role="alert">
        <div class="sa-panel-body">
            <h2>Información operativa no disponible</h2>
            <p>No se pudo consultar la información. Vuelve a intentarlo para ver las métricas y señales actuales.</p>
            <button type="button" class="sa-button" @click="router.reload()">Reintentar consulta</button>
        </div>
    </section>
</template>
