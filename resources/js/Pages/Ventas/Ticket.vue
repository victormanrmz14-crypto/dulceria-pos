<script setup lang="ts">
import { computed, shallowRef } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import SaleReceipt from '../../Components/Ventas/SaleReceipt.vue';
import type { TicketSale, TicketConfig } from '../../Components/Ventas/receipt';
import { moneda } from '../../utils';

const props = defineProps<{ venta: TicketSale; config?: TicketConfig | null }>();
const paperWidth = shallowRef('80');
const receiptStyle = computed(() => ({ '--receipt-width': `${paperWidth.value}mm` }));
const paymentLabel = computed(() => props.venta.metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta');
const print = () => window.print();
</script>

<template>
    <AppLayout premium>
        <Head :title="`Ticket ${venta.folio}`" />
        <div class="sale-ticket" :style="receiptStyle">
            <header class="p-page-heading sale-ticket__heading no-imprimir">
                <div><Link href="/ventas/historial" class="p-text-link sale-ticket__back"><UiIcon name="receipt" :size="15" />Historial de ventas</Link><h1 class="p-page-title">Detalle de venta</h1><p class="p-page-date">Consulta la operación y su comprobante.</p></div>
                <div class="p-page-actions"><UiButton variant="secondary" @click="print"><UiIcon name="printer" />Imprimir</UiButton><UiButton href="/ventas"><UiIcon name="plus" />Nueva venta</UiButton></div>
            </header>
            <div class="sale-ticket__grid">
                <section class="p-panel sale-summary no-imprimir" aria-labelledby="sale-summary-title">
                    <div class="p-panel__heading"><h2 id="sale-summary-title" class="p-panel__title">Resumen de la operación</h2><UiIcon name="receipt" class="sale-summary__icon" /></div>
                    <div class="sale-summary__body">
                        <p class="sale-summary__label">Total de la venta</p><p class="sale-summary__total">{{ moneda(venta.total) }}</p>
                        <dl class="sale-summary__details"><div><dt>Folio</dt><dd>{{ venta.folio }}</dd></div><div><dt>Fecha y hora</dt><dd>{{ venta.fecha }}</dd></div><div><dt>Cajero</dt><dd>{{ venta.cajero }}</dd></div><div><dt>Método de pago</dt><dd class="sale-summary__payment"><UiIcon :name="venta.metodo_pago === 'efectivo' ? 'cash' : 'card'" :size="16" />{{ paymentLabel }}</dd></div><div v-if="venta.monto_recibido != null"><dt>Monto recibido</dt><dd>{{ moneda(venta.monto_recibido) }}</dd></div><div v-if="venta.cambio != null"><dt>Cambio</dt><dd>{{ moneda(venta.cambio) }}</dd></div></dl>
                    </div>
                    <div class="sale-summary__note"><UiIcon name="info" :size="17" /><p>El comprobante incluye la información y los mensajes configurados por tu negocio.</p></div>
                </section>
                <section class="p-panel receipt-preview" aria-labelledby="receipt-title">
                    <div class="p-panel__heading no-imprimir"><div><h2 id="receipt-title" class="p-panel__title">Comprobante</h2><p class="p-panel__description">Vista previa de impresión</p></div><label class="receipt-paper" for="receipt-width">Ancho de papel<select id="receipt-width" v-model="paperWidth" aria-describedby="receipt-help"><option value="80">80 mm</option><option value="58">58 mm</option></select></label></div>
                    <div class="receipt-preview__paper"><SaleReceipt :venta="venta" :config="config" /></div>
                    <p id="receipt-help" class="receipt-preview__help no-imprimir"><UiIcon name="printer" :size="16" /><span>Al imprimir, selecciona el tamaño de papel y los márgenes de tu impresora. Desactiva los encabezados y pies del navegador.</span></p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.sale-ticket { max-width: 1120px; margin-inline: auto; }
.sale-ticket__back { display: inline-flex; gap: 6px; align-items: center; min-height: 44px; margin-top: -8px; margin-bottom: 4px; font-size: 12px; }
.sale-ticket__heading { align-items: center; }
.sale-ticket__grid { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: 24px; align-items: start; }
.sale-summary__icon { color: var(--p-muted); }
.sale-summary__body { padding: 24px; }
.sale-summary__label { margin: 0 0 8px; color: var(--p-muted); font-size: 13px; }
.sale-summary__total { margin: 0 0 24px; font-size: clamp(22px, 2.5vw, 32px); line-height: 1.2; font-weight: 650; letter-spacing: -.5px; font-variant-numeric: tabular-nums; white-space: nowrap; }
.sale-summary__details { margin: 0; }
.sale-summary__details > div { display: flex; justify-content: space-between; flex-wrap: wrap; align-items: baseline; gap: 4px 16px; padding-block: 12px; border-top: 1px solid var(--p-border); font-size: 13px; }
.sale-summary__details dt { color: var(--p-muted); font-weight: 400; }
.sale-summary__details dd { margin: 0 0 0 auto; max-width: 100%; text-align: right; font-weight: 500; overflow-wrap: anywhere; font-variant-numeric: tabular-nums; }
.sale-summary__payment { display: flex; align-items: center; gap: 6px; }
.sale-summary__note { display: flex; align-items: start; gap: 10px; padding: 16px 24px; border-top: 1px solid var(--p-border); color: var(--p-muted); font-size: 12px; }
.sale-summary__note p { margin: 0; }
.receipt-preview { overflow: visible; }
.receipt-preview .p-panel__heading { flex-wrap: wrap; align-items: center; }
.receipt-paper { display: grid; gap: 4px; font-size: 12px; color: var(--p-muted); }
.receipt-paper select { min-height: 44px; padding: 8px 30px 8px 12px; border: 1px solid #858a94; border-radius: 6px; background: #fff; color: var(--p-text); font: inherit; }
.receipt-paper select:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 3px; }
.receipt-preview__paper { padding: 24px; background: var(--p-muted-surface); border-block: 1px solid var(--p-border); }
.receipt-preview__help { display: flex; align-items: start; gap: 10px; margin: 0; padding: 16px 24px; color: var(--p-muted); font-size: 12px; }
@media (max-width: 1199.98px) { .sale-ticket__grid { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 479.98px) { .sale-ticket__grid { gap: 16px; } .sale-summary__body { padding: 16px; } .sale-summary__total { margin-bottom: 16px; } .sale-summary__details > div { padding-block: 8px; } .sale-summary__note, .receipt-preview__help { padding: 16px; } .receipt-preview__paper { padding: 16px 8px; } }
</style>

<style>
/* Limit the print reset to this page; other modules keep their print behavior. */
@media print {
    html:has(.sale-ticket), body:has(.sale-ticket) { background: #fff !important; }
    .tenant-shell:has(.sale-ticket), .tenant-shell:has(.sale-ticket) .p-workspace,
    .tenant-shell:has(.sale-ticket) > .d-flex, .tenant-shell:has(.sale-ticket) main {
        display: block !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; background: #fff !important;
    }
    .tenant-shell:has(.sale-ticket) :is(.p-header, .p-sidebar, .p-bottom-nav, .p-skip, .impersonacion-banner, .anuncio-banner, .toast-container-pos, dialog, .no-imprimir) { display: none !important; }
    .sale-ticket, .sale-ticket__grid, .sale-ticket .receipt-preview, .sale-ticket .receipt-preview__paper { display: block !important; margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 0 !important; background: #fff !important; box-shadow: none !important; max-width: none !important; }
}
</style>
