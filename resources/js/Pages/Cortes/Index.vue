<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Paginacion from '../../Components/Paginacion.vue';
import { moneda } from '../../utils';

defineProps({
    cortes: Object,
});
</script>

<template>
    <AppLayout>
        <Head title="Cortes de caja" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="text-primary fs-3 mb-0">📋 Cortes Históricos</h2>
            <Link href="/caja" class="btn btn-primary btn-sm fw-semibold">🏦 Ir a caja</Link>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Fecha del corte</th>
                            <th>Cajero</th>
                            <th class="text-center">Transacciones</th>
                            <th class="text-end">💵 Efectivo</th>
                            <th class="text-end">💳 Tarjeta</th>
                            <th class="text-end">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in cortes.data" :key="c.id">
                            <td class="small fw-semibold">{{ c.fecha }}</td>
                            <td class="small">{{ c.cajero }}</td>
                            <td class="text-center small">{{ c.num_transacciones }}</td>
                            <td class="text-end small">{{ moneda(c.total_efectivo) }}</td>
                            <td class="text-end small">{{ moneda(c.total_tarjeta) }}</td>
                            <td class="text-end fw-bold text-primary">{{ moneda(c.total_general) }}</td>
                            <td class="text-end">
                                <Link :href="`/cortes/${c.id}`" class="btn btn-sm btn-light fw-semibold">Ver</Link>
                            </td>
                        </tr>
                        <tr v-if="!cortes.data.length">
                            <td colspan="7" class="text-center text-secondary py-5">Sin cortes registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <Paginacion :links="cortes.links" />
            </div>
        </div>
    </AppLayout>
</template>
