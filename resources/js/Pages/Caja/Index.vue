<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ModalPos from '../../Components/ModalPos.vue';
import CampoForm from '../../Components/CampoForm.vue';
import { moneda } from '../../utils';

const props = defineProps({
    efectivoEsperado: Number,
    tarjetaEsperada: Number,
    ventasEfectivo: Number,
    fondoCaja: Number,
    ingresos: Number,
    retiros: Number,
    ultimoCorte: Object,
    movimientos: Array,
});

// ---- Modales ----
const modal = ref(null); // 'ingreso' | 'retiro' | 'corte' | null

// ---- Formularios ----
const formIngreso = useForm({ monto: null, motivo: '' });
const formRetiro  = useForm({ monto: null, motivo: '' });
const formCorte   = useForm({ notas: '', efectivo_contado: null, dinero_en_caja: null });

const enviarIngreso = () => {
    formIngreso.post('/caja/ingreso', {
        onSuccess: () => { formIngreso.reset(); modal.value = null; },
    });
};

const enviarRetiro = () => {
    formRetiro.post('/caja/retiro', {
        onSuccess: () => { formRetiro.reset(); modal.value = null; },
        // si el backend rechaza (supera el disponible) el toast muestra el error
        onError: () => {},
    });
};

// Validaciones en vivo del corte (mismas reglas que la versión anterior)
const errorRecuento = computed(() =>
    formCorte.efectivo_contado !== null &&
    formCorte.efectivo_contado !== '' &&
    Number(formCorte.efectivo_contado) > props.efectivoEsperado,
);

const errorDineroEnCaja = computed(() =>
    formCorte.dinero_en_caja !== null && formCorte.dinero_en_caja !== '' &&
    formCorte.efectivo_contado !== null && formCorte.efectivo_contado !== '' &&
    Number(formCorte.dinero_en_caja) > Number(formCorte.efectivo_contado),
);

const corteInvalido = computed(() => errorRecuento.value || errorDineroEnCaja.value);

const enviarCorte = () => {
    if (corteInvalido.value) return;
    formCorte.post('/cortes', {
        onSuccess: () => { formCorte.reset(); modal.value = null; },
    });
};

const cerrarModal = () => { modal.value = null; };
</script>

<template>
    <AppLayout>
        <Head title="Caja" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="text-primary fs-3 mb-1">🏦 Caja Actual</h2>
                <p class="text-secondary small mb-0">
                    <template v-if="ultimoCorte">Desde el último corte: {{ ultimoCorte.fecha }}</template>
                    <template v-else>Desde el inicio del día</template>
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-success fw-semibold" @click="modal = 'ingreso'">
                    ↑ Ingresar Efectivo
                </button>
                <button type="button" class="btn btn-danger fw-semibold" @click="modal = 'retiro'">
                    ↓ Retirar Efectivo
                </button>
                <button type="button" class="btn btn-outline-primary fw-bold" @click="modal = 'corte'">
                    📋 Hacer Corte de Caja
                </button>
            </div>
        </div>

        <!-- Resumen del turno -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card stat-card h-100" style="--stat-color: #28a745;">
                    <div class="card-body py-3">
                        <p class="text-secondary small mb-1">💵 Efectivo esperado</p>
                        <p class="valor mb-0" style="--stat-color: #28a745;">{{ moneda(efectivoEsperado) }}</p>
                        <p class="text-secondary small mb-0 mt-1">ventas + ingresos − retiros + fondo</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card stat-card h-100" style="--stat-color: #6f42c1;">
                    <div class="card-body py-3">
                        <p class="text-secondary small mb-1">💳 Tarjeta</p>
                        <p class="valor mb-0" style="--stat-color: #6f42c1;">{{ moneda(tarjetaEsperada) }}</p>
                        <p class="text-secondary small mb-0 mt-1">cobrado en el período</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100" style="--stat-color: #17a2b8;">
                    <div class="card-body py-3">
                        <p class="text-secondary small mb-1">↑ Ingresos manuales</p>
                        <p class="valor mb-0" style="--stat-color: #17a2b8;">{{ moneda(ingresos) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100" style="--stat-color: #dc3545;">
                    <div class="card-body py-3">
                        <p class="text-secondary small mb-1">↓ Retiros</p>
                        <p class="valor mb-0" style="--stat-color: #dc3545;">{{ moneda(retiros) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desglose -->
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header encabezado">Desglose del efectivo</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between small">
                            <span class="text-secondary">Ventas en efectivo</span>
                            <span class="fw-semibold">{{ moneda(ventasEfectivo) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small">
                            <span class="text-secondary">Fondo del corte anterior</span>
                            <span class="fw-semibold">{{ moneda(fondoCaja) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small">
                            <span class="text-secondary">Ingresos manuales</span>
                            <span class="fw-semibold text-success">+ {{ moneda(ingresos) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small">
                            <span class="text-secondary">Retiros</span>
                            <span class="fw-semibold text-danger">− {{ moneda(retiros) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">Efectivo esperado</span>
                            <span class="fw-bold text-primary">{{ moneda(efectivoEsperado) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header encabezado">Últimos movimientos</div>
                    <ul class="list-group list-group-flush">
                        <li v-for="m in movimientos" :key="m.id"
                            class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge me-2" :class="m.tipo === 'ingreso' ? 'text-bg-success' : 'text-bg-danger'">
                                    {{ m.tipo === 'ingreso' ? '↑' : '↓' }}
                                </span>
                                <span class="small fw-semibold">{{ m.motivo || (m.tipo === 'ingreso' ? 'Ingreso' : 'Retiro') }}</span>
                                <div class="text-secondary" style="font-size: 0.75rem;">{{ m.fecha }}</div>
                            </div>
                            <span class="fw-bold small" :class="m.tipo === 'ingreso' ? 'text-success' : 'text-danger'">
                                {{ m.tipo === 'ingreso' ? '+' : '−' }} {{ moneda(m.monto) }}
                            </span>
                        </li>
                        <li v-if="!movimientos.length" class="list-group-item text-center text-secondary small py-4">
                            Sin movimientos en este período.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal: Ingreso -->
        <ModalPos :abierto="modal === 'ingreso'" titulo="↑ Ingresar efectivo" @cerrar="cerrarModal">
            <form @submit.prevent="enviarIngreso" class="d-flex flex-column gap-3">
                <CampoForm etiqueta="Monto" :error="formIngreso.errors.monto" requerido>
                    <template #default="{ clase }">
                        <input v-model.number="formIngreso.monto" type="number" step="0.01" min="0.01"
                               class="form-control text-end" :class="clase" placeholder="0.00" required>
                    </template>
                </CampoForm>
                <CampoForm etiqueta="Motivo" :error="formIngreso.errors.motivo">
                    <template #default="{ clase }">
                        <input v-model="formIngreso.motivo" type="text" maxlength="255"
                               class="form-control" :class="clase" placeholder="Ej. fondo para cambio">
                    </template>
                </CampoForm>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 fw-semibold" @click="cerrarModal">Cancelar</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-success w-100 fw-bold" :disabled="formIngreso.processing">
                            Registrar
                        </button>
                    </div>
                </div>
            </form>
        </ModalPos>

        <!-- Modal: Retiro -->
        <ModalPos :abierto="modal === 'retiro'" titulo="↓ Retirar efectivo" @cerrar="cerrarModal">
            <form @submit.prevent="enviarRetiro" class="d-flex flex-column gap-3">
                <div class="alert alert-warning small py-2 mb-0">
                    Efectivo disponible: <strong>{{ moneda(efectivoEsperado) }}</strong>
                </div>
                <CampoForm etiqueta="Monto" :error="formRetiro.errors.monto" requerido>
                    <template #default="{ clase }">
                        <input v-model.number="formRetiro.monto" type="number" step="0.01" min="0.01"
                               class="form-control text-end" :class="clase" placeholder="0.00" required>
                    </template>
                </CampoForm>
                <CampoForm etiqueta="Motivo" :error="formRetiro.errors.motivo">
                    <template #default="{ clase }">
                        <input v-model="formRetiro.motivo" type="text" maxlength="255"
                               class="form-control" :class="clase" placeholder="Ej. pago a proveedor">
                    </template>
                </CampoForm>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 fw-semibold" @click="cerrarModal">Cancelar</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-danger w-100 fw-bold" :disabled="formRetiro.processing">
                            Retirar
                        </button>
                    </div>
                </div>
            </form>
        </ModalPos>

        <!-- Modal: Corte -->
        <ModalPos :abierto="modal === 'corte'" titulo="📋 Corte de caja" ancho="520px" @cerrar="cerrarModal">
            <form @submit.prevent="enviarCorte" class="d-flex flex-column gap-3">
                <div class="alert alert-info small py-2 mb-0">
                    Sistema espera en efectivo: <strong>{{ moneda(efectivoEsperado) }}</strong>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <CampoForm etiqueta="Efectivo contado" :error="formCorte.errors.efectivo_contado">
                            <template #default="{ clase }">
                                <input v-model.number="formCorte.efectivo_contado" type="number" step="0.01" min="0"
                                       class="form-control text-end"
                                       :class="[clase, { 'is-invalid': errorRecuento }]"
                                       placeholder="0.00">
                            </template>
                        </CampoForm>
                        <div v-if="errorRecuento" class="text-danger mt-1" style="font-size: 0.75rem;">
                            El contado supera lo esperado — verifica el recuento.
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <CampoForm etiqueta="Dinero que queda en caja" :error="formCorte.errors.dinero_en_caja">
                            <template #default="{ clase }">
                                <input v-model.number="formCorte.dinero_en_caja" type="number" step="0.01" min="0"
                                       class="form-control text-end"
                                       :class="[clase, { 'is-invalid': errorDineroEnCaja }]"
                                       placeholder="0.00">
                            </template>
                        </CampoForm>
                        <div v-if="errorDineroEnCaja" class="text-danger mt-1" style="font-size: 0.75rem;">
                            No puede ser mayor que el efectivo contado.
                        </div>
                    </div>
                </div>

                <CampoForm etiqueta="Notas" :error="formCorte.errors.notas">
                    <template #default="{ clase }">
                        <textarea v-model="formCorte.notas" rows="2" maxlength="500"
                                  class="form-control" :class="clase"
                                  placeholder="Observaciones del corte (opcional)"></textarea>
                    </template>
                </CampoForm>

                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 fw-semibold" @click="cerrarModal">Cancelar</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary w-100 fw-bold"
                                :disabled="formCorte.processing || corteInvalido">
                            Generar corte
                        </button>
                    </div>
                </div>
            </form>
        </ModalPos>
    </AppLayout>
</template>
