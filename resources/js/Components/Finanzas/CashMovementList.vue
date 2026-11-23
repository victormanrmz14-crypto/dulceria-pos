<script setup lang="ts">
import { ArrowDownLeft, ArrowUpRight } from '@lucide/vue';
import UiEmptyState from '../Ui/UiEmptyState.vue';
import { moneda } from '../../utils';

defineProps<{ movements: { id: number; tipo: string; monto: number; motivo: string | null; fecha: string }[] }>();
</script>

<template>
    <ul v-if="movements.length" class="cash-movements">
        <li v-for="m in movements" :key="m.id" class="cash-movement">
            <span class="cash-movement__icon" :class="m.tipo === 'ingreso' ? 'cash-movement__icon--in' : 'cash-movement__icon--out'">
                <component :is="m.tipo === 'ingreso' ? ArrowDownLeft : ArrowUpRight" :size="18" :stroke-width="1.75" aria-hidden="true" />
            </span>
            <div class="cash-movement__body">
                <div class="cash-movement__top"><span class="cash-movement__type">{{ m.tipo === 'ingreso' ? 'Ingreso' : 'Retiro' }}</span><span class="cash-movement__amount">{{ m.tipo === 'ingreso' ? '+' : '−' }} {{ moneda(m.monto) }}</span></div>
                <p class="cash-movement__reason">{{ m.motivo || (m.tipo === 'ingreso' ? 'Ingreso' : 'Retiro') }}</p>
                <p class="cash-movement__date">{{ m.fecha }}</p>
            </div>
        </li>
    </ul>
    <UiEmptyState v-else icon="cash" title="Sin movimientos en este período" description="Los ingresos y retiros de efectivo aparecerán aquí." />
</template>

<style scoped>
.cash-movements { list-style: none; padding: 0; margin: 0; }
.cash-movement { display: flex; align-items: flex-start; gap: 12px; padding: 16px; border-bottom: 1px solid var(--p-border); }
.cash-movement:last-child { border-bottom: 0; }
.cash-movement__icon { width: 32px; height: 32px; border-radius: var(--p-radius-control); background: var(--p-muted-surface); display: grid; place-items: center; flex-shrink: 0; }
.cash-movement__icon--in { color: var(--p-success); }
.cash-movement__icon--out { color: var(--p-danger); }
.cash-movement__body { min-width: 0; flex: 1; }
.cash-movement__top { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 4px 12px; }
.cash-movement__type { font-size: 13px; font-weight: 600; }
.cash-movement__amount { margin-left: auto; white-space: nowrap; font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; }
.cash-movement__reason { margin: 4px 0; font-size: 13px; line-height: 1.6; overflow-wrap: anywhere; }
.cash-movement__date { margin: 0; font-size: 12px; color: var(--p-muted); }
@media (min-width: 600px) { .cash-movement { padding-inline: 20px; } }
</style>
