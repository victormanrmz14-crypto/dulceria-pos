<script setup>
import CampoForm from './CampoForm.vue';

// Formulario compartido de producto (crear / editar)
defineProps({
    form: Object, // useForm de Inertia
    categorias: Array,
    marcas: Array,
    proveedores: Array,
    textoBoton: { type: String, default: 'Guardar' },
});

defineEmits(['enviar']);
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
                        <input v-model="form.unidad_medida" type="text" maxlength="50"
                               class="form-control" :class="clase" placeholder="pieza, kg, bolsa..." required>
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
                <CampoForm etiqueta="Stock" :error="form.errors.stock" requerido>
                    <template #default="{ clase }">
                        <input v-model.number="form.stock" type="number" step="1" min="0"
                               class="form-control text-end" :class="clase" required>
                    </template>
                </CampoForm>
            </div>
            <div class="col-6 col-md-4">
                <CampoForm etiqueta="Stock mínimo" :error="form.errors.stock_minimo" requerido>
                    <template #default="{ clase }">
                        <input v-model.number="form.stock_minimo" type="number" step="1" min="0"
                               class="form-control text-end" :class="clase" required>
                    </template>
                </CampoForm>
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
