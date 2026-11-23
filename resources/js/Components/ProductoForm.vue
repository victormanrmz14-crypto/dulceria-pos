<script setup>
import { computed } from 'vue';
import UiFormField from './Ui/UiFormField.vue';
import UiButton from './Ui/UiButton.vue';
import UiIcon from './Ui/UiIcon.vue';

// Formulario compartido de producto (crear / editar)
const props = defineProps({
    form: Object, // useForm de Inertia
    categorias: Array,
    marcas: Array,
    proveedores: Array,
    textoBoton: { type: String, default: 'Guardar' },
});

defineEmits(['enviar']);

// Unidades de medida. Las "a granel" (peso/volumen) manejan el inventario con
// decimales; las demás se cuentan por unidades enteras.
const unidades = [
    { value: 'pieza',   label: 'Pieza',          granel: false },
    { value: 'paquete', label: 'Paquete',        granel: false },
    { value: 'bolsa',   label: 'Bolsa',          granel: false },
    { value: 'caja',    label: 'Caja',           granel: false },
    { value: 'kg',      label: 'Kilogramo (kg)', granel: true  },
    { value: 'g',       label: 'Gramo (g)',      granel: true  },
    { value: 'litro',   label: 'Litro (L)',      granel: true  },
    { value: 'ml',      label: 'Mililitro (ml)', granel: true  },
];

// Conserva el valor actual aunque no esté en la lista (productos antiguos).
const opcionesUnidad = computed(() => {
    const base = [...unidades];
    const actual = props.form.unidad_medida;
    if (actual && !base.some(u => u.value === actual)) {
        base.unshift({ value: actual, label: actual, granel: false });
    }
    return base;
});

const esGranel     = computed(() => unidades.find(u => u.value === props.form.unidad_medida)?.granel ?? false);
const sufijoUnidad = computed(() => esGranel.value ? props.form.unidad_medida : 'uds');
</script>

<template>
    <form class="product-form" @submit.prevent="$emit('enviar')">
        <section class="product-form-section" aria-labelledby="product-identity-title">
            <div class="product-form-section__intro"><h2 id="product-identity-title">Información del producto</h2><p>Identifica el producto y su clasificación.</p></div>
            <div class="product-form-fields">
                <UiFormField class="product-form-full" etiqueta="Nombre del producto" :error="form.errors.nombre" requerido>
                    <template #default="{ control }"><input v-bind="control" v-model="form.nombre" name="nombre" type="text" maxlength="255" class="p-field-control" autocomplete="off" required></template>
                </UiFormField>
                <UiFormField etiqueta="Categoría" :error="form.errors.categoria_id" requerido>
                    <template #default="{ control }">
                        <select v-bind="control" v-model="form.categoria_id" name="categoria_id" class="p-field-control" required>
                            <option value="">Selecciona...</option><option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </template>
                </UiFormField>
                <UiFormField etiqueta="Marca" :error="form.errors.marca_id" requerido>
                    <template #default="{ control }">
                        <select v-bind="control" v-model="form.marca_id" name="marca_id" class="p-field-control" required>
                            <option value="">Selecciona...</option><option v-for="m in marcas" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                        </select>
                    </template>
                </UiFormField>
                <UiFormField class="product-form-full" etiqueta="Proveedor" :error="form.errors.proveedor_id">
                    <template #default="{ control }">
                        <select v-bind="control" v-model="form.proveedor_id" name="proveedor_id" class="p-field-control">
                            <option :value="null">Sin proveedor</option><option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </template>
                </UiFormField>
            </div>
        </section>

        <section class="product-form-section" aria-labelledby="product-inventory-title">
            <div class="product-form-section__intro"><h2 id="product-inventory-title">Precio e inventario</h2><p>Define la unidad de medida, el precio y las existencias.</p></div>
            <div class="product-form-fields">
                <UiFormField etiqueta="Unidad de medida" :error="form.errors.unidad_medida" requerido>
                    <template #default="{ control }">
                        <select v-bind="control" v-model="form.unidad_medida" name="unidad_medida" class="p-field-control" required>
                            <option v-for="u in opcionesUnidad" :key="u.value" :value="u.value">{{ u.label }}</option>
                        </select>
                    </template>
                </UiFormField>
                <UiFormField etiqueta="Precio" :error="form.errors.precio" requerido>
                    <template #default="{ control }">
                        <div class="product-input-group product-input-group--currency"><span aria-hidden="true">$</span><input v-bind="control" v-model.number="form.precio" name="precio" type="number" inputmode="decimal" step="0.01" min="0" class="p-field-control" required></div>
                    </template>
                </UiFormField>
                <UiFormField :etiqueta="esGranel ? 'Existencias' : 'Stock'" :error="form.errors.stock" requerido>
                    <template #default="{ control }">
                        <div class="product-input-group"><input v-bind="control" v-model.number="form.stock" name="stock" type="number" :inputmode="esGranel ? 'decimal' : 'numeric'" :step="esGranel ? '0.001' : '1'" min="0" class="p-field-control" required><span>{{ sufijoUnidad }}</span></div>
                    </template>
                </UiFormField>
                <UiFormField etiqueta="Stock mínimo" :error="form.errors.stock_minimo" requerido>
                    <template #default="{ control }">
                        <div class="product-input-group"><input v-bind="control" v-model.number="form.stock_minimo" name="stock_minimo" type="number" :inputmode="esGranel ? 'decimal' : 'numeric'" :step="esGranel ? '0.001' : '1'" min="0" class="p-field-control" required><span>{{ sufijoUnidad }}</span></div>
                    </template>
                </UiFormField>
                <div v-if="esGranel" class="product-form-full product-bulk-note"><UiIcon name="info" :size="18" /><p>Producto <strong>a granel</strong>: el inventario se maneja en <strong>{{ form.unidad_medida }}</strong> y admite decimales (ej. 1.5).</p></div>
            </div>
        </section>
        <div class="product-form-actions"><slot name="acciones" /><UiButton type="submit" :loading="form.processing">{{ textoBoton }}</UiButton></div>
    </form>
</template>

<style scoped>
.product-form-section { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: 32px; padding: 28px; }
.product-form-section + .product-form-section { border-top: 1px solid var(--p-border); }
.product-form-section__intro h2 { margin: 0; font: inherit; font-size: 14px; font-weight: 600; line-height: 1.5; }
.product-form-section__intro p { color: var(--p-muted); font-size: 12px; margin: 8px 0 0; max-width: 32ch; }
.product-form-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: 24px 20px; min-width: 0; }
.product-form-full { grid-column: 1 / -1; }
.product-input-group { display: flex; align-items: stretch; min-width: 0; border: 1px solid #858a94; border-radius: var(--p-radius-control); background: var(--p-surface); }
.product-input-group:focus-within { outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.product-input-group:has([aria-invalid="true"]) { border-color: var(--p-danger); }
.product-input-group :deep(.p-field-control) { flex: 1; width: 0; border: 0; text-align: right; font-variant-numeric: tabular-nums; background: transparent; }
.product-input-group :deep(.p-field-control:focus-visible) { outline: 0; }
.product-input-group > span { display: flex; align-items: center; padding: 10px 12px; color: var(--p-muted); font-size: 13px; border-left: 1px solid var(--p-border); }
.product-input-group--currency > span { border-left: 0; border-right: 1px solid var(--p-border); }
.product-bulk-note { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border: 1px solid var(--p-border); border-radius: var(--p-radius-control); background: var(--p-canvas); color: var(--p-muted); font-size: 12px; }
.product-bulk-note > .p-icon { flex-shrink: 0; margin-top: 1px; color: var(--p-info); }
.product-bulk-note p { margin: 0; }
.product-bulk-note strong { color: var(--p-text); font-weight: 500; }
.product-form-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; padding: 20px 28px; border-top: 1px solid var(--p-border); background: var(--p-canvas); border-radius: 0 0 var(--p-radius-panel) var(--p-radius-panel); }
@media (max-width: 1199.98px) { .product-form-section { grid-template-columns: minmax(0, 1fr); gap: 24px; padding: 24px; } .product-form-section__intro p { max-width: none; margin-top: 4px; } }
@media (max-width: 767.98px) { .product-form-section { padding: 20px 16px; gap: 20px; } .product-form-fields { gap: 20px 16px; } .product-form-actions { padding: 16px; } .product-form-actions > :deep(.p-button) { flex: 1 1 auto; } }
@media (max-width: 599.98px) { .product-form-fields { grid-template-columns: minmax(0, 1fr); } }
</style>
