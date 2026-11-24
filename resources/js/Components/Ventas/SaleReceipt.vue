<script setup lang="ts">
import { moneda, numero } from '../../utils';
import type { TicketSale, TicketConfig } from './receipt';

defineProps<{ venta: TicketSale; config?: TicketConfig | null }>();
</script>

<template>
    <article class="sale-receipt ticket-imprimible" aria-label="Comprobante de venta">
        <header class="sale-receipt__business">
            <img v-if="config?.logo" :src="config.logo" alt="Logotipo del negocio" class="sale-receipt__logo" width="64" height="64" />
            <h2>{{ config?.nombre || 'Dulcería POS' }}</h2>
            <p v-if="config?.rfc">RFC: {{ config.rfc }}</p>
            <p v-if="config?.direccion">{{ config.direccion }}</p>
            <p v-if="config?.telefono">Tel: {{ config.telefono }}</p>
            <p v-if="config?.encabezado" class="sale-receipt__message">{{ config.encabezado }}</p>
        </header>
        <dl class="sale-receipt__meta">
            <div><dt>Folio</dt><dd>{{ venta.folio }}</dd></div>
            <div><dt>Fecha</dt><dd>{{ venta.fecha }}</dd></div>
            <div><dt>Atendió</dt><dd>{{ venta.cajero }}</dd></div>
        </dl>
        <table class="sale-receipt__items">
            <caption class="visually-hidden">Productos, cantidades, precios unitarios e importes</caption>
            <thead><tr><th scope="col">Producto</th><th scope="col">Importe</th></tr></thead>
            <tbody><tr v-for="(item, i) in venta.detalles" :key="i">
                <td><span class="sale-receipt__product">{{ item.producto }}</span><span class="sale-receipt__quantity"><span class="visually-hidden">Cantidad: </span><span class="sale-receipt__number">{{ numero(item.cantidad) }}</span> × <span class="visually-hidden">Precio unitario: </span><span class="sale-receipt__number">{{ moneda(item.precio_unitario) }}</span></span></td>
                <td class="sale-receipt__amount">{{ moneda(item.importe) }}</td>
            </tr></tbody>
        </table>
        <dl class="sale-receipt__totals">
            <div class="sale-receipt__total"><dt>Total</dt><dd>{{ moneda(venta.total) }}</dd></div>
            <div><dt>Método de pago</dt><dd>{{ venta.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</dd></div>
            <div v-if="venta.monto_recibido != null"><dt>Recibido</dt><dd>{{ moneda(venta.monto_recibido) }}</dd></div>
            <div v-if="venta.cambio != null"><dt>Cambio</dt><dd>{{ moneda(venta.cambio) }}</dd></div>
        </dl>
        <footer class="sale-receipt__footer"><p class="sale-receipt__message">{{ config?.pie || '¡Gracias por su compra!' }}</p><p>{{ config?.nombre || 'Dulcería POS' }}</p></footer>
    </article>
</template>

<style scoped>
.sale-receipt { width: min(100%, var(--receipt-width, 80mm)); margin-inline: auto; padding: 20px 16px; border: 1px solid var(--p-border); background: #fff; color: #202126; font-family: var(--p-font-sans); font-size: 12px; line-height: 1.5; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.sale-receipt__business { text-align: center; }
.sale-receipt__logo { display: block; margin: 0 auto 12px; max-width: 100%; object-fit: contain; }
.sale-receipt__business h2 { margin: 0 0 8px; font: inherit; font-size: 16px; font-weight: 700; line-height: 1.3; text-wrap: balance; }
.sale-receipt p { margin: 4px 0 0; }
.sale-receipt__message { white-space: pre-line; }
.sale-receipt__business .sale-receipt__message { margin-top: 12px; }
.sale-receipt__meta, .sale-receipt__totals { margin: 16px 0 0; padding: 12px 0 0; border-top: 1px dashed #777; }
.sale-receipt dl > div { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; column-gap: 12px; row-gap: 3px; margin-top: 5px; }
.sale-receipt dt { font-weight: 400; }
.sale-receipt dd { max-width: 100%; margin: 0 0 0 auto; text-align: right; font-weight: 500; }
.sale-receipt__items { width: 100%; margin-top: 16px; border-collapse: collapse; }
.sale-receipt__items th { padding: 8px 0; border-block: 1px solid #777; font-size: 11px; font-weight: 600; text-align: left; }
.sale-receipt__items th:last-child { text-align: right; }
.sale-receipt__items td { padding: 12px 0; vertical-align: top; border-bottom: 1px solid #ddd; }
.sale-receipt__items td:first-child { padding-right: 12px; }
.sale-receipt__product { display: block; }
.sale-receipt__quantity { display: block; margin-top: 4px; font-size: 11px; }
.sale-receipt__number { white-space: nowrap; }
.sale-receipt__amount { white-space: nowrap; text-align: right; font-weight: 600; }
.sale-receipt .sale-receipt__total { margin-bottom: 12px; font-size: 16px; }
.sale-receipt__total dt, .sale-receipt__total dd { font-weight: 700; }
.sale-receipt__totals dd { white-space: nowrap; }
.sale-receipt__footer { margin-top: 16px; padding-top: 12px; border-top: 1px dashed #777; text-align: center; }
@media print {
    .sale-receipt { padding: 2mm; border: 0; font-family: 'DM Sans', system-ui, sans-serif; font-size: 11px; color: #000; box-shadow: none; }
    .sale-receipt__business h2 { font-size: 14px; }
    .sale-receipt__logo { width: 14mm; height: 14mm; }
    .sale-receipt__items th, .sale-receipt__items td, .sale-receipt__meta, .sale-receipt__totals, .sale-receipt__footer { border-color: #000; }
    .sale-receipt__items tr, .sale-receipt dl > div, .sale-receipt__business, .sale-receipt__footer { break-inside: avoid; page-break-inside: avoid; }
    .sale-receipt__items thead { display: table-header-group; }
}
</style>
