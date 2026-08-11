<script setup>
import { computed } from 'vue';
import CampoForm from './CampoForm.vue';

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
    <form @submit.prevent="$emit('enviar')">
        <div class="row g-3">
            <div class="col-12">
                <CampoForm etiqueta="Nombre del producto" :error="form.errors.nombre" requerido>
                    <template #default="{ clase }">
                        <input v-model="form.nombre" type="text" maxlength="255"
                               class="form-control" :class="clase" required>
                    </template>
                </CampoForm>
            </div>

            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Categoría" :error="form.errors.categoria_id" requerido>
                    <template #default="{ clase }">
                        <select v-model="form.categoria_id" class="form-select" :class="clase" required>
                            <option value="">Selecciona...</option>
                            <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </template>
                </CampoForm>
            </div>
            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Marca" :error="form.errors.marca_id" requerido>
                    <template #default="{ clase }">
                        <select v-model="form.marca_id" class="form-select" :class="clase" required>
                            <option value="">Selecciona...</option>
                            <option v-for="m in marcas" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                        </select>
                    </template>
                </CampoForm>
            </div>

            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Proveedor" :error="form.errors.proveedor_id">
                    <template #default="{ clase }">
                        <select v-model="form.proveedor_id" class="form-select" :class="clase">
                            <option :value="null">Sin proveedor</option>
                            <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </template>
                </CampoForm>
            </div>
            <div class="col-12 col-md-6">
                <CampoForm etiqueta="Unidad de medida" :error="form.errors.unidad_medida" requerido>
                    <template #default="{ clase }">
                        <select v-model="form.unidad_medida" class="form-select" :class="clase" required>
                            <option v-for="u in opcionesUnidad" :key="u.value" :value="u.value">{{ u.label }}</option>
                        </select>
                    </template>
                </CampoForm>
            </div>

            <div class="col-12 col-md-4">
                <CampoForm etiqueta="Precio" :error="form.errors.precio" requerido>
                    <template #default="{ clase }">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input v-model.number="form.precio" type="number" step="0.01" min="0"
                                   class="form-control text-end" :class="clase" required>
                        </div>
                    </template>
                </CampoForm>
            </div>
            <div class="col-6 col-md-4">
                <CampoForm :etiqueta="esGranel ? 'Existencias' : 'Stock'" :error="form.errors.stock" requerido>
                    <template #default="{ clase }">
                        <div class="input-group">
                            <input v-model.number="form.stock" type="number" :step="esGranel ? '0.001' : '1'" min="0"
                                   class="form-control text-end" :class="clase" required>
                            <span class="input-group-text">{{ sufijoUnidad }}</span>
                        </div>
                    </template>
                </CampoForm>
            </div>
            <div class="col-6 col-md-4">
                <CampoForm etiqueta="Stock mínimo" :error="form.errors.stock_minimo" requerido>
                    <template #default="{ clase }">
                        <div class="input-group">
                            <input v-model.number="form.stock_minimo" type="number" :step="esGranel ? '0.001' : '1'" min="0"
                                   class="form-control text-end" :class="clase" required>
                            <span class="input-group-text">{{ sufijoUnidad }}</span>
                        </div>
                    </template>
                </CampoForm>
            </div>

            <div v-if="esGranel" class="col-12">
                <div class="alert alert-info py-2 px-3 mb-0 small">
                    ⚖️ Producto <strong>a granel</strong>: el inventario se maneja en <strong>{{ form.unidad_medida }}</strong> y admite decimales (ej. 1.5).
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4">
            <slot name="acciones" />
            <button type="submit" class="btn btn-primary fw-bold px-4" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" />
                {{ textoBoton }}
            </button>
        </div>
    </form>
</template>
