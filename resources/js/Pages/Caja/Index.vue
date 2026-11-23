<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiFormField from '../../Components/Ui/UiFormField.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import FinancialMetric from '../../Components/Finanzas/FinancialMetric.vue';
import CashMovementList from '../../Components/Finanzas/CashMovementList.vue';
import CashDialog from '../../Components/Finanzas/CashDialog.vue';
import { ArrowDownLeft, ArrowUpRight } from '@lucide/vue';
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
    <AppLayout premium v-slot="{ theme }">
        <Head title="Caja" />
        <FinanceWorkspace>
            <div class="p-page-heading cash-heading">
                <div>
                    <h1 class="p-page-title">Caja actual</h1>
                    <p class="finance-description"><template v-if="ultimoCorte">Desde el último corte: {{ ultimoCorte.fecha }}</template><template v-else>Desde el inicio del día</template></p>
                </div>
                <div class="p-page-actions cash-actions">
                    <UiButton variant="secondary" @click="modal = 'ingreso'"><ArrowDownLeft :size="18" :stroke-width="1.75" aria-hidden="true" />Ingresar efectivo</UiButton>
                    <UiButton variant="secondary" @click="modal = 'retiro'"><ArrowUpRight :size="18" :stroke-width="1.75" aria-hidden="true" />Retirar efectivo</UiButton>
                    <UiButton @click="modal = 'corte'"><UiIcon name="receipt" />Hacer corte de caja</UiButton>
                </div>
            </div>

            <div class="finance-metrics" aria-label="Resumen de caja">
                <FinancialMetric label="Efectivo esperado" :value="moneda(efectivoEsperado)" icon="cash" description="Ventas + ingresos − retiros + fondo" prominent />
                <FinancialMetric label="Ventas con tarjeta" :value="moneda(tarjetaEsperada)" icon="card" description="Cobrado en el período" />
                <FinancialMetric label="Ingresos adicionales" :value="moneda(ingresos)" icon="plus" description="Entradas manuales de efectivo" />
                <FinancialMetric label="Retiros" :value="moneda(retiros)" icon="external" description="Salidas de efectivo de caja" />
            </div>

            <div class="cash-sections">
                <section class="p-panel cash-breakdown" aria-labelledby="cash-breakdown-title">
                    <div class="p-panel__heading"><div><h2 id="cash-breakdown-title" class="p-panel__title">Desglose del efectivo</h2><p class="p-panel__description">Composición del saldo de caja</p></div><UiIcon name="cash" class="finance-muted" /></div>
                    <dl class="finance-pairs">
                        <div class="finance-pair"><dt>Ventas en efectivo</dt><dd class="finance-money">{{ moneda(ventasEfectivo) }}</dd></div>
                        <div class="finance-pair"><dt>Fondo del corte anterior</dt><dd class="finance-money">{{ moneda(fondoCaja) }}</dd></div>
                        <div class="finance-pair"><dt>Ingresos manuales</dt><dd class="finance-money">+ {{ moneda(ingresos) }}</dd></div>
                        <div class="finance-pair"><dt>Retiros</dt><dd class="finance-money">− {{ moneda(retiros) }}</dd></div>
                        <div class="finance-pair finance-pair--total"><dt>Efectivo esperado</dt><dd class="finance-money">{{ moneda(efectivoEsperado) }}</dd></div>
                    </dl>
                </section>
                <section class="p-panel" aria-labelledby="cash-movements-title">
                    <div class="p-panel__heading"><div><h2 id="cash-movements-title" class="p-panel__title">Últimos movimientos</h2><p class="p-panel__description">Ingresos y retiros de efectivo</p></div><UiIcon name="receipt" class="finance-muted" /></div>
                    <CashMovementList :movements="movimientos" />
                </section>
            </div>
        </FinanceWorkspace>

        <CashDialog :open="modal === 'ingreso'" title="Ingresar efectivo" :theme="theme" @close="cerrarModal">
            <form @submit.prevent="enviarIngreso" class="cash-form">
                <UiFormField etiqueta="Monto" :error="formIngreso.errors.monto" requerido>
                    <template #default="{ control }"><input v-bind="control" v-model.number="formIngreso.monto" type="number" inputmode="decimal" name="monto" autocomplete="off" step="0.01" min="0.01" class="p-field-control cash-form__money" placeholder="0.00" required></template>
                </UiFormField>
                <UiFormField etiqueta="Motivo" :error="formIngreso.errors.motivo">
                    <template #default="{ control }"><input v-bind="control" v-model="formIngreso.motivo" type="text" name="motivo" autocomplete="off" maxlength="255" class="p-field-control" placeholder="Ej. fondo para cambio"></template>
                </UiFormField>
                <div class="cash-form__actions"><UiButton variant="secondary" @click="cerrarModal">Cancelar</UiButton><UiButton type="submit" :loading="formIngreso.processing"><ArrowDownLeft v-if="!formIngreso.processing" :size="18" aria-hidden="true" />{{ formIngreso.processing ? 'Registrando…' : 'Registrar ingreso' }}</UiButton></div>
            </form>
        </CashDialog>

        <CashDialog :open="modal === 'retiro'" title="Retirar efectivo" :theme="theme" @close="cerrarModal">
            <form @submit.prevent="enviarRetiro" class="cash-form">
                <p class="cash-form__context"><span>Efectivo disponible</span><strong>{{ moneda(efectivoEsperado) }}</strong></p>
                <UiFormField etiqueta="Monto" :error="formRetiro.errors.monto" requerido>
                    <template #default="{ control }"><input v-bind="control" v-model.number="formRetiro.monto" type="number" inputmode="decimal" name="monto" autocomplete="off" step="0.01" min="0.01" class="p-field-control cash-form__money" placeholder="0.00" required></template>
                </UiFormField>
                <UiFormField etiqueta="Motivo" :error="formRetiro.errors.motivo">
                    <template #default="{ control }"><input v-bind="control" v-model="formRetiro.motivo" type="text" name="motivo" autocomplete="off" maxlength="255" class="p-field-control" placeholder="Ej. pago a proveedor"></template>
                </UiFormField>
                <div class="cash-form__actions"><UiButton variant="secondary" @click="cerrarModal">Cancelar</UiButton><UiButton type="submit" class="cash-form__withdraw" :loading="formRetiro.processing"><ArrowUpRight v-if="!formRetiro.processing" :size="18" aria-hidden="true" />{{ formRetiro.processing ? 'Retirando…' : 'Retirar efectivo' }}</UiButton></div>
            </form>
        </CashDialog>

        <CashDialog :open="modal === 'corte'" title="Corte de caja" :theme="theme" wide @close="cerrarModal">
            <form @submit.prevent="enviarCorte" class="cash-form">
                <p class="cash-form__context"><span>Efectivo esperado por el sistema</span><strong>{{ moneda(efectivoEsperado) }}</strong></p>
                <div class="cash-form__grid">
                    <UiFormField etiqueta="Efectivo contado" :error="[formCorte.errors.efectivo_contado, errorRecuento ? 'El contado supera lo esperado — verifica el recuento.' : null].filter(Boolean).join(' ')">
                        <template #default="{ control }"><input v-bind="control" v-model.number="formCorte.efectivo_contado" type="number" inputmode="decimal" name="efectivo_contado" autocomplete="off" step="0.01" min="0" class="p-field-control cash-form__money" placeholder="0.00"></template>
                    </UiFormField>
                    <UiFormField etiqueta="Dinero que queda en caja" :error="[formCorte.errors.dinero_en_caja, errorDineroEnCaja ? 'No puede ser mayor que el efectivo contado.' : null].filter(Boolean).join(' ')">
                        <template #default="{ control }"><input v-bind="control" v-model.number="formCorte.dinero_en_caja" type="number" inputmode="decimal" name="dinero_en_caja" autocomplete="off" step="0.01" min="0" class="p-field-control cash-form__money" placeholder="0.00"></template>
                    </UiFormField>
                </div>
                <UiFormField etiqueta="Notas" :error="formCorte.errors.notas">
                    <template #default="{ control }"><textarea v-bind="control" v-model="formCorte.notas" name="notas" autocomplete="off" rows="2" maxlength="500" class="p-field-control" placeholder="Observaciones del corte (opcional)"></textarea></template>
                </UiFormField>
                <div class="cash-form__actions"><UiButton variant="secondary" @click="cerrarModal">Cancelar</UiButton><UiButton type="submit" :loading="formCorte.processing" :disabled="corteInvalido"><UiIcon v-if="!formCorte.processing" name="receipt" />{{ formCorte.processing ? 'Generando…' : 'Generar corte' }}</UiButton></div>
            </form>
        </CashDialog>
    </AppLayout>
</template>

<style scoped>
.cash-sections { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; align-items: start; }
.cash-actions { gap: 8px; }
@media (min-width: 1200px) { .cash-sections { grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); } }
@media (max-width: 599.98px) { .cash-actions :deep(.p-button) { flex: 1 1 100%; } }
</style>
