<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { moneda, numero } from '../../utils';

defineProps({
    venta: Object,
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
                        <h1 class="titulo-marca text-primary fs-4 mb-1">🍬 Dulcería POS</h1>
                        <p class="text-secondary small mb-0">{{ venta.fecha }}</p>
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

                    <!-- Totales -->
                    <div class="pt-3" style="border-top: 2px dashed #eee;">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-secondary">Subtotal</span>
                            <span class="fw-semibold">{{ moneda(venta.subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-secondary">IVA (16%)</span>
                            <span class="fw-semibold">{{ moneda(venta.impuestos) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
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
                        <p class="mb-1">¡Gracias por su compra!</p>
                        <p class="mb-0">Dulcería POS</p>
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
