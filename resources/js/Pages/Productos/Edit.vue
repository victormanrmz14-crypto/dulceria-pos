<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ProductoForm from '../../Components/ProductoForm.vue';
import ProductEditorFrame from '../../Components/Productos/ProductEditorFrame.vue';
import UiButton from '../../Components/Ui/UiButton.vue';

const props = defineProps({
    producto: Object,
    categorias: Array,
    marcas: Array,
    proveedores: Array,
});

const form = useForm({
    nombre: props.producto.nombre,
    categoria_id: props.producto.categoria_id,
    marca_id: props.producto.marca_id,
    proveedor_id: props.producto.proveedor_id,
    precio: props.producto.precio,
    stock: props.producto.stock,
    stock_minimo: props.producto.stock_minimo,
    unidad_medida: props.producto.unidad_medida,
});

const enviar = () => form.put(`/productos/${props.producto.id}`);
</script>

<template>
    <AppLayout premium>
        <Head :title="`Editar ${producto.nombre}`" />
        <ProductEditorFrame title="Editar producto"
            description="Actualiza la información, el precio y las existencias del producto.">
            <ProductoForm :form="form" :categorias="categorias" :marcas="marcas" :proveedores="proveedores"
                texto-boton="Guardar cambios" @enviar="enviar">
                <template #acciones><UiButton href="/productos" variant="secondary">Cancelar</UiButton></template>
            </ProductoForm>
        </ProductEditorFrame>
    </AppLayout>
</template>
