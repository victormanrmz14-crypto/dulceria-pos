<script setup lang="ts">
import { useId } from 'vue';
import UiButton from '../Ui/UiButton.vue';
import UiEmptyState from '../Ui/UiEmptyState.vue';
import UiIcon from '../Ui/UiIcon.vue';
import { moneda } from '../../utils';

interface CartItem { id: number; nombre: string; precio: number; cantidad: number; stock: number; unidad: string }
defineProps<{
    items: CartItem[]; errors: Record<string, string>; total: number; change: number;
    payment: string; hasErrors: boolean; desktop: boolean;
}>();
const received = defineModel<number | string | null>('received', { required: true });
const emit = defineEmits<{
    remove: [id: number]; quantity: [item: CartItem, value: string]; clear: []; confirm: [];
    'update:payment': [value: string];
}>();
const fieldId = useId();
</script>

<template>
    <div class="pos-checkout">
        <header v-if="desktop" class="pos-checkout-heading">
            <div><UiIcon name="cart" :size="18" /><h2>Carrito</h2></div>
            <span class="pos-checkout-count">{{ items.length }} {{ items.length === 1 ? 'producto' : 'productos' }}</span>
        </header>
        <div v-if="items.length" class="pos-cart-items" aria-label="Productos en el carrito">
            <article v-for="item in items" :key="item.id" class="pos-cart-item">
                <div class="pos-cart-item__heading">
                    <h3>{{ item.nombre }}</h3>
                    <button type="button" class="p-icon-button pos-remove" :aria-label="`Quitar ${item.nombre}`" @click="emit('remove', item.id)"><UiIcon name="close" :size="16" /></button>
                </div>
                <p class="pos-unit-price">{{ moneda(item.precio) }} / {{ item.unidad }}</p>
                <div class="pos-cart-item__values">
                    <label :for="`${fieldId}-${item.id}`">Cantidad</label>
                    <input :id="`${fieldId}-${item.id}`" type="number" inputmode="numeric"
                        class="pos-input pos-quantity" :value="item.cantidad" min="1" :max="item.stock"
                        :aria-label="`Cantidad de ${item.nombre}`" :aria-invalid="!!errors[item.id]"
                        :aria-describedby="errors[item.id] ? `${fieldId}-error-${item.id}` : undefined"
                        @change="emit('quantity', item, ($event.target as HTMLInputElement).value)">
                    <strong>{{ moneda(item.precio * item.cantidad) }}</strong>
                </div>
                <p v-if="errors[item.id]" :id="`${fieldId}-error-${item.id}`" class="pos-field-error" role="alert"><UiIcon name="warning" :size="14" />{{ errors[item.id] }}</p>
            </article>
        </div>
        <UiEmptyState v-else icon="cart" title="Tu carrito está vacío" description="Selecciona un producto para empezar la venta." class="pos-cart-empty" />

        <div class="pos-payment" :class="{ 'pos-payment--empty': !items.length }">
            <div class="pos-total" aria-live="polite" aria-atomic="true"><span>Total a cobrar</span><strong>{{ moneda(total) }}</strong></div>
            <template v-if="items.length">
                <fieldset class="pos-methods">
                    <legend>Método de pago</legend>
                    <div>
                        <button v-for="method in [{value: 'efectivo', label: 'Efectivo', icon: 'cash'}, {value: 'tarjeta', label: 'Tarjeta', icon: 'card'}]"
                            :key="method.value" type="button" :aria-pressed="payment === method.value"
                            :class="{ 'is-selected': payment === method.value }"
                            @click="emit('update:payment', method.value)">
                            <UiIcon :name="method.icon" :size="18" />{{ method.label }}
                            <UiIcon v-if="payment === method.value" name="success" :size="14" />
                        </button>
                    </div>
                </fieldset>
                <div v-if="payment === 'efectivo'" class="pos-received">
                    <label :for="`${fieldId}-received`">Monto recibido</label>
                    <div class="pos-money-input"><span aria-hidden="true">$</span><input :id="`${fieldId}-received`"
                        v-model.number="received" type="number" inputmode="decimal" step="0.01" min="0" class="pos-input"
                        placeholder="0.00" :aria-invalid="!!errors.monto"
                        :aria-describedby="errors.monto ? `${fieldId}-amount-error` : undefined"
                        @keydown.enter.prevent="emit('confirm')"></div>
                    <p v-if="errors.monto" :id="`${fieldId}-amount-error`" class="pos-field-error" role="alert"><UiIcon name="warning" :size="14" />{{ errors.monto }}</p>
                    <div v-if="change > 0" class="pos-change"><span>Cambio</span><strong>{{ moneda(change) }}</strong></div>
                </div>
                <UiButton class="pos-charge" :disabled="hasErrors" @click="emit('confirm')">Cobrar {{ moneda(total) }}<UiIcon name="arrow" :size="18" /></UiButton>
                <button type="button" class="pos-clear" @click="emit('clear')">Limpiar carrito</button>
            </template>
            <UiButton v-else class="pos-charge" disabled>Cobrar {{ moneda(total) }}<UiIcon name="arrow" :size="18" /></UiButton>
        </div>
    </div>
</template>

<style scoped>
.pos-checkout { min-width: 0; }
.pos-checkout-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; padding: 18px 20px; border-bottom: 1px solid var(--p-border); }
.pos-checkout-heading > div { display: flex; align-items: center; gap: 8px; }
.pos-checkout-heading h2 { margin: 0; font: inherit; font-weight: 600; }
.pos-checkout-heading .p-icon { color: var(--p-muted); }
.pos-checkout-count { color: var(--p-muted); font-size: 12px; }
.pos-cart-items { padding-inline: 20px; max-height: min(360px, 42dvh); overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; }
.pos-cart-item { padding-block: 12px 16px; border-bottom: 1px solid var(--p-border); }
.pos-cart-item:last-child { border-bottom: 0; }
.pos-cart-item__heading { display: flex; align-items: flex-start; gap: 8px; }
.pos-cart-item h3 { flex: 1; min-width: 0; margin: 10px 0 0; font: inherit; font-size: 13px; font-weight: 600; line-height: 1.5; overflow-wrap: anywhere; }
.pos-remove { margin-right: -10px; }
.pos-remove:hover { color: var(--p-danger); }
.pos-unit-price { font-size: 12px; color: var(--p-muted); margin: -2px 0 12px; }
.pos-cart-item__values { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.pos-cart-item__values label { font-size: 12px; color: var(--p-muted); }
.pos-cart-item__values strong { margin-left: auto; font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.pos-input { width: 100%; min-width: 0; min-height: 44px; padding: 10px 12px; border: 1px solid #858a94; border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-text); font: inherit; font-size: 16px; font-variant-numeric: tabular-nums; }
.pos-input:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.pos-input[aria-invalid="true"] { border-color: var(--p-danger); }
.pos-quantity { width: 76px; text-align: center; }
.pos-field-error { display: flex; align-items: flex-start; gap: 6px; margin: 8px 0 0; font-size: 12px; color: var(--p-danger); }
.pos-field-error .p-icon { margin-top: 2px; }
.pos-cart-empty { min-height: 252px; }
.pos-payment { padding: 20px; border-top: 1px solid var(--p-border); background: var(--p-canvas); border-radius: 0 0 var(--p-radius-panel) var(--p-radius-panel); }
.pos-total { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 4px 12px; margin-bottom: 20px; }
.pos-total > span { font-size: 13px; font-weight: 500; }
.pos-total > strong { font-size: clamp(24px, 2vw, 30px); line-height: 1.3; font-weight: 600; letter-spacing: -.8px; font-variant-numeric: tabular-nums; white-space: nowrap; }
.pos-payment--empty .pos-total { color: var(--p-muted); }
.pos-methods { margin: 0 0 20px; min-width: 0; padding: 0; border: 0; }
.pos-methods legend, .pos-received > label { float: none; width: auto; padding: 0; margin: 0 0 8px; font: inherit; font-size: 12px; font-weight: 500; }
.pos-methods > div { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.pos-methods button { display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 48px; padding: 10px 8px; border: 1px solid #858a94; border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-muted); font: inherit; font-size: 13px; cursor: pointer; }
.pos-methods button:hover { background: var(--p-muted-surface); color: var(--p-text); }
.pos-methods button.is-selected { color: var(--p-brand-ink); border-color: var(--p-brand-ink); background: color-mix(in srgb, var(--p-brand) 5%, white); box-shadow: inset 0 0 0 1px var(--p-brand-ink); font-weight: 600; }
.pos-received { margin-bottom: 20px; }
.pos-money-input { position: relative; }
.pos-money-input > span { position: absolute; left: 12px; top: 12px; color: var(--p-muted); }
.pos-money-input input { text-align: right; padding-left: 32px; }
.pos-money-input input::placeholder { color: var(--p-muted); opacity: 1; }
.pos-change { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; color: var(--p-success); margin-top: 12px; font-variant-numeric: tabular-nums; }
.pos-charge { width: 100%; min-height: 48px; }
.pos-charge .p-icon { margin-left: auto; }
.pos-clear { display: block; width: 100%; min-height: 44px; margin-top: 8px; border: 0; border-radius: var(--p-radius-control); background: transparent; color: var(--p-muted); font: inherit; font-size: 12px; cursor: pointer; }
.pos-clear:hover { background: var(--p-muted-surface); color: var(--p-danger); }
@media (max-width: 991.98px) { .pos-cart-items { max-height: 34dvh; } }
@media (max-width: 767.98px) { .pos-payment { padding: 20px 16px 12px; } .pos-cart-items { padding-inline: 16px; } }
</style>
