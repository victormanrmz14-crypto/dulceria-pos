<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Paginacion from '../../Components/Paginacion.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import { moneda } from '../../utils';

defineProps({
    cortes: Object,
});
</script>

<template>
    <AppLayout premium>
        <Head title="Cortes de caja" />
        <FinanceWorkspace>
            <div class="p-page-heading">
                <div><h1 class="p-page-title">Historial de cortes</h1><p class="finance-description">Consulta los cierres de caja y sus importes.</p></div>
                <UiButton href="/caja"><UiIcon name="cash" />Ir a caja</UiButton>
            </div>

            <section class="p-panel" aria-labelledby="cuts-title">
                <div class="p-panel__heading"><div><h2 id="cuts-title" class="p-panel__title">Cortes de caja</h2><p class="p-panel__description">Responsables, transacciones y totales por cierre</p></div><UiIcon name="receipt" class="finance-muted" /></div>
                <template v-if="cortes.data.length">
                    <table class="finance-table cuts-table">
                        <caption class="finance-table-caption">Historial de cortes de caja</caption>
                        <thead><tr><th scope="col">Fecha del corte</th><th scope="col">Cajero</th><th scope="col" class="finance-number">Transacciones</th><th scope="col" class="finance-number">Efectivo</th><th scope="col" class="finance-number">Tarjeta</th><th scope="col" class="finance-number">Total general</th><th scope="col"><span class="visually-hidden">Acciones</span></th></tr></thead>
                        <tbody>
                            <tr v-for="c in cortes.data" :key="c.id">
                                <td>{{ c.fecha }}</td><td>{{ c.cajero }}</td><td class="finance-number">{{ c.num_transacciones }}</td>
                                <td class="finance-number finance-money">{{ moneda(c.total_efectivo) }}</td><td class="finance-number finance-money">{{ moneda(c.total_tarjeta) }}</td><td class="finance-number finance-money cuts-total">{{ moneda(c.total_general) }}</td>
                                <td><UiButton :href="`/cortes/${c.id}`" variant="secondary" :aria-label="`Ver corte de ${c.cajero}, ${c.fecha}`">Ver<UiIcon name="arrow" :size="16" /></UiButton></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="finance-cards cuts-cards">
                        <article v-for="c in cortes.data" :key="c.id" class="finance-record">
                            <div class="finance-record__heading"><div><h3 class="finance-record__title">{{ c.cajero }}</h3><p class="finance-record__meta">{{ c.fecha }}</p></div><UiIcon name="receipt" class="finance-muted" /></div>
                            <dl class="finance-pairs">
                                <div class="finance-pair"><dt>Transacciones</dt><dd>{{ c.num_transacciones }}</dd></div>
                                <div class="finance-pair"><dt>Efectivo</dt><dd class="finance-money">{{ moneda(c.total_efectivo) }}</dd></div>
                                <div class="finance-pair"><dt>Tarjeta</dt><dd class="finance-money">{{ moneda(c.total_tarjeta) }}</dd></div>
                                <div class="finance-pair finance-pair--total"><dt>Total general</dt><dd class="finance-money cuts-total">{{ moneda(c.total_general) }}</dd></div>
                            </dl>
                            <UiButton :href="`/cortes/${c.id}`" variant="secondary" class="finance-record__action" :aria-label="`Ver corte de ${c.cajero}, ${c.fecha}`">Ver corte<UiIcon name="arrow" :size="16" /></UiButton>
                        </article>
                    </div>
                </template>
                <UiEmptyState v-else icon="receipt" title="Sin cortes registrados" description="Los cierres de caja aparecerán aquí con sus importes y responsable." />
                <div class="finance-pagination"><Paginacion :links="cortes.links" premium /></div>
            </section>
        </FinanceWorkspace>
    </AppLayout>
</template>

<style scoped>
.cuts-cards { padding: 16px; }
.cuts-total { font-weight: 700; }
.cuts-table :deep(td:first-child) { width: 140px; }
.cuts-table :deep(td:nth-child(2)) { max-width: 220px; }
@media (min-width: 1200px) and (max-width: 1399.98px) { .cuts-table :deep(th), .cuts-table :deep(td) { padding-inline: 12px; } }
</style>
