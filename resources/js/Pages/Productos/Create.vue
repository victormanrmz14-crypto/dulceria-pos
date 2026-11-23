<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ProductoForm from '../../Components/ProductoForm.vue';
import ProductEditorFrame from '../../Components/Productos/ProductEditorFrame.vue';
import UiButton from '../../Components/Ui/UiButton.vue';

defineProps({
    categorias: Array,
    marcas: Array,
    proveedores: Array,
});

const form = useForm({
    nombre: '',
    categoria_id: '',
    marca_id: '',
    proveedor_id: null,
    precio: null,
    stock: null,
    stock_minimo: 10,
    unidad_medida: 'pieza',
});

const enviar = () => form.post('/productos');
</script>

<template>
    <AppLayout premium>
        <Head title="Nuevo producto" />
        <ProductEditorFrame title="Nuevo producto"
            description="Agrega la información, el precio y las existencias del producto.">
            <ProductoForm :form="form" :categorias="categorias" :marcas="marcas" :proveedores="proveedores"
                texto-boton="Crear producto" @enviar="enviar">
                <template #acciones><UiButton href="/productos" variant="secondary">Cancelar</UiButton></template>
            </ProductoForm>
        </ProductEditorFrame>
    </AppLayout>
</template>
