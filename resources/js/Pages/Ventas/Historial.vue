<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import AnalyticsSalesList from '../../Components/Analitica/AnalyticsSalesList.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';

defineProps({
    ventas: Object, // paginador de Laravel
});
</script>

<template>
    <AppLayout premium>
        <Head title="Historial de ventas" />
        <FinanceWorkspace>
            <div class="p-page-heading"><div><h1 class="p-page-title history-title"><UiIcon name="receipt" :size="23" />Historial de ventas</h1><p class="finance-description">Consulta las ventas registradas y accede a sus tickets.</p></div><UiButton href="/ventas"><UiIcon name="cart" />Ir al punto de venta</UiButton></div>
            <section class="p-panel" aria-labelledby="history-list-title">
                <div class="p-panel__heading"><div><h2 id="history-list-title" class="p-panel__title">Ventas registradas</h2><p class="p-panel__description">Folio, fecha, cajero y método de pago</p></div><UiIcon name="receipt" class="finance-muted" /></div>
                <div class="history-sales"><AnalyticsSalesList :sales="ventas.data" :links="ventas.links" ticket-action caption="Historial de ventas y acceso a sus tickets" empty-title="Sin ventas registradas" empty-description="Las ventas aparecerán aquí con su fecha, cajero e importe.">
                    <template #empty-action><UiButton href="/ventas" variant="secondary"><UiIcon name="cart" :size="16" />Ir al punto de venta</UiButton></template>
                </AnalyticsSalesList></div>
            </section>
        </FinanceWorkspace>
    </AppLayout>
</template>

<style scoped>
.history-title { display: flex; align-items: center; gap: 10px; }
.history-title .p-icon { color: var(--p-muted); }
.history-sales :deep(.p-empty) { flex-direction: column; text-align: center; min-height: 220px; gap: 12px; padding: 28px 16px; }
.history-sales :deep(.p-empty__copy) { max-width: 46ch; }
</style>
