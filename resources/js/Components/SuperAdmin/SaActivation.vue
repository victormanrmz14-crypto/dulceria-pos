<script setup lang="ts">
import UiIcon from '../Ui/UiIcon.vue';
defineProps<{
    milestones: Array<{
        clave: string;
        label: string;
        definicion: string;
        estado: 'completado' | 'pendiente' | 'sin_datos';
        evidencia: string | null;
    }>;
}>();
const labels = { completado: 'Completado', pendiente: 'Pendiente', sin_datos: 'Datos insuficientes' };
</script>

<template>
    <section class="sa-panel">
        <header>
            <div>
                <h2>Estado de activación</h2>
                <p>Hitos comprobados con los registros del negocio.</p>
            </div>
        </header>
        <ul class="sa-activation-list">
            <li v-for="milestone in milestones" :key="milestone.clave" class="sa-activation-item">
                <UiIcon :name="milestone.estado === 'completado' ? 'success' : 'info'" :size="20" />
                <div class="sa-stack">
                    <strong>{{ milestone.label }}</strong>
                    <p class="sa-muted">{{ milestone.definicion }}</p>
                    <span>{{ milestone.evidencia || (milestone.estado === 'sin_datos' ? 'Fecha no disponible' : 'Sin registro hasta la consulta') }}</span>
                </div>
                <span class="sa-status" :class="{ 'sa-status--ok': milestone.estado === 'completado' }">{{ labels[milestone.estado] }}</span>
            </li>
        </ul>
        <div class="sa-panel-body sa-note">
            Configuración inicial: no verificable. Los valores guardados pueden proceder de valores predeterminados; no se consideran un hito completado.
            Los hitos son independientes: un corte no demuestra que haya ocurrido una venta.
        </div>
    </section>
</template>

<style scoped>
.sa-activation-list { margin: 0; padding: 0; list-style: none; }
.sa-activation-item { display: grid; grid-template-columns: 20px minmax(0, 1fr) auto; align-items: start; gap: 12px; padding: 20px; border-bottom: 1px solid var(--p-border); }
.sa-activation-item p { margin: 0; }
.sa-activation-item .sa-status { white-space: normal; }
@media (max-width: 479px) {
    .sa-activation-item { padding: 16px; grid-template-columns: 20px minmax(0, 1fr); }
    .sa-activation-item .sa-status { grid-column: 2; justify-self: start; }
}
</style>
