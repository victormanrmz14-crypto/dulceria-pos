<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatCard from '../../Components/StatCard.vue';
import { moneda } from '../../utils';

defineProps({
    corte: Object,
    ventas: Array,
});
</script>

<template>
    <AppLayout>
        <Head :title="`Corte #${corte.id}`" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="text-primary fs-3 mb-0">📋 Corte de caja #{{ corte.id }}</h2>
            <Link href="/cortes" class="btn btn-light btn-sm fw-semibold">← Volver</Link>
        </div>

        <!-- Información -->
        <div class="card mb-4">
            <div class="card-header encabezado">Información del corte</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small">Cajero</div>
                        <div class="fw-semibold">{{ corte.cajero }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small">Inicio del período</div>
                        <div class="fw-semibold">{{ corte.fecha_inicio }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small">Fecha del corte</div>
                        <div class="fw-semibold">{{ corte.fecha_corte }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small">Transacciones</div>
                        <div class="fw-semibold">{{ corte.num_transacciones }}</div>
                    </div>
                    <div v-if="corte.notas" class="col-12">
                        <div class="text-secondary small">Notas</div>
                        <div class="small">{{ corte.notas }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <StatCard etiqueta="💵 Efectivo" :valor="moneda(corte.total_efectivo)" color="#28a745" />
            </div>
            <div class="col-6 col-md-3">
                <StatCard etiqueta="💳 Tarjeta" :valor="moneda(corte.total_tarjeta)" color="#6f42c1" />
            </div>
            <div class="col-6 col-md-3">
                <StatCard etiqueta="Total general" :valor="moneda(corte.total_general)" color="#8B0000" />
            </div>
            <div class="col-6 col-md-3">
                <StatCard
                    v-if="corte.efectivo_contado !== null"
                    etiqueta="Efectivo contado"
                    :valor="moneda(corte.efectivo_contado)"
                    :sufijo="corte.diferencia === 0 ? 'cuadra exacto ✓' : (corte.diferencia > 0 ? `sobran ${moneda(corte.diferencia)}` : `faltan ${moneda(-corte.diferencia)}`)"
                    :color="corte.diferencia === 0 ? '#28a745' : '#ffc107'"
                />
                <StatCard v-else etiqueta="Efectivo contado" valor="—" sufijo="no capturado" color="#adb5bd" />
            </div>
        </div>

        <div v-if="corte.dinero_en_caja !== null" class="alert alert-info py-2 small">
            💰 Quedaron <strong>{{ moneda(corte.dinero_en_caja) }}</strong> en caja como fondo para el siguiente turno.
        </div>

        <!-- Ventas del período -->
        <div class="card">
            <div class="card-header encabezado">Ventas incluidas en el corte</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in ventas" :key="v.id">
                            <td>
                                <Link :href="`/ventas/${v.id}/ticket`" class="fw-bold text-primary text-decoration-none">
                                    {{ v.folio }}
                                </Link>
                            </td>
                            <td class="small">{{ v.hora }}</td>
                            <td class="small">{{ v.metodo_pago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}</td>
                            <td class="text-end fw-bold text-primary">{{ moneda(v.total) }}</td>
                        </tr>
                        <tr v-if="!ventas.length">
                            <td colspan="4" class="text-center text-secondary py-4">Sin ventas en el período.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
