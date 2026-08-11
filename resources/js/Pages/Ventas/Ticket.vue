<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { moneda, numero } from '../../utils';

defineProps({
    venta: Object,
    config: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="`Ticket ${venta.folio}`" />

        <div class="mx-auto ticket-imprimible" style="max-width: 400px;">
            <div class="card">
                <div class="card-body p-4">
                    <!-- Encabezado -->
                    <div class="text-center pb-3 mb-3" style="border-bottom: 2px dashed #eee;">
                        <img v-if="config?.logo" :src="config.logo" alt="Logo" class="ticket-logo mb-2" />
                        <h1 class="titulo-marca text-primary fs-4 mb-1">{{ config?.nombre || '🍬 Dulcería POS' }}</h1>
                        <p v-if="config?.rfc" class="text-secondary mb-0" style="font-size: .72rem;">RFC: {{ config.rfc }}</p>
                        <p v-if="config?.direccion" class="text-secondary mb-0" style="font-size: .72rem;">{{ config.direccion }}</p>
                        <p v-if="config?.telefono" class="text-secondary mb-0" style="font-size: .72rem;">Tel: {{ config.telefono }}</p>
                        <p v-if="config?.encabezado" class="text-secondary mt-1 mb-0" style="font-size: .72rem; white-space: pre-line;">{{ config.encabezado }}</p>
                        <p class="text-secondary small mb-0 mt-1">{{ venta.fecha }}</p>
                    </div>

                    <!-- Info -->
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <div class="text-secondary" style="font-size: 0.78rem;">Folio</div>
                            <div class="fw-bold text-primary">{{ venta.folio }}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-secondary" style="font-size: 0.78rem;">Atendió</div>
                            <div class="fw-semibold small">{{ venta.cajero }}</div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <table class="table table-sm mb-3">
                        <thead>
                            <tr>
                                <th class="bg-white">Producto</th>
                                <th class="bg-white text-center">Cant.</th>
                                <th class="bg-white text-end">Precio</th>
                                <th class="bg-white text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(d, i) in venta.detalles" :key="i">
                                <td class="small">{{ d.producto }}</td>
                                <td class="small text-center">{{ numero(d.cantidad) }}</td>
                                <td class="small text-end">{{ moneda(d.precio_unitario) }}</td>
                                <td class="small text-end fw-semibold">{{ moneda(d.importe) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Total -->
                    <div class="pt-3" style="border-top: 2px dashed #eee;">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-5 text-primary">{{ moneda(venta.total) }}</span>
                        </div>

                        <template v-if="venta.monto_recibido !== null">
                            <div class="d-flex justify-content-between small mt-2">
                                <span class="text-secondary">Recibido</span>
                                <span class="fw-semibold">{{ moneda(venta.monto_recibido) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span class="text-success fw-semibold">Cambio</span>
                                <span class="text-success fw-bold">{{ moneda(venta.cambio) }}</span>
                            </div>
                        </template>

                        <div class="text-end mt-2">
                            <span class="badge rounded-pill"
                                  :class="venta.metodo_pago === 'efectivo' ? 'text-bg-success' : 'text-bg-info'">
                                {{ venta.metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
                            </span>
                        </div>
                    </div>

                    <!-- Pie -->
                    <div class="text-center text-secondary small mt-3 pt-3" style="border-top: 2px dashed #eee;">
                        <p v-if="config?.pie" class="mb-1" style="white-space: pre-line;">{{ config.pie }}</p>
                        <p v-else class="mb-1">¡Gracias por su compra!</p>
                        <p class="mb-0">{{ config?.nombre || 'Dulcería POS' }}</p>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="d-flex gap-2 justify-content-center mt-4 no-imprimir">
                <button type="button" class="btn btn-primary fw-semibold" onclick="window.print()">
                    🖨️ Imprimir
                </button>
                <Link href="/ventas" class="btn btn-light fw-semibold">Nueva venta</Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.ticket-logo { max-height: 60px; max-width: 100%; object-fit: contain; }
</style>
