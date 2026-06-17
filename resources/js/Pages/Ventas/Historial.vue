<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Paginacion from '../../Components/Paginacion.vue';
import { moneda } from '../../utils';

defineProps({
    ventas: Object, // paginador de Laravel
});
</script>

<template>
    <AppLayout>
        <Head title="Historial de ventas" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="text-primary fs-3 mb-0">📋 Historial de ventas</h2>
            <Link href="/ventas" class="btn btn-primary btn-sm fw-semibold">🛒 Ir al punto de venta</Link>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Cajero</th>
                            <th>Método</th>
                            <th class="text-end">Total</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in ventas.data" :key="v.id">
                            <td class="fw-bold text-primary">{{ v.folio }}</td>
                            <td class="small">{{ v.fecha }}</td>
                            <td class="small">{{ v.cajero }}</td>
                            <td class="small">
                                {{ v.metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
                            </td>
                            <td class="text-end fw-bold text-primary">{{ moneda(v.total) }}</td>
                            <td class="text-end">
                                <Link :href="`/ventas/${v.id}/ticket`" class="btn btn-sm btn-light fw-semibold">
                                    Ver ticket
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!ventas.data.length">
                            <td colspan="6" class="text-center text-secondary py-5">Sin ventas registradas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <Paginacion :links="ventas.links" />
            </div>
        </div>
    </AppLayout>
</template>
