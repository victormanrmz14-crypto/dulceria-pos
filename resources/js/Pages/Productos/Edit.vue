<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ProductoForm from '../../Components/ProductoForm.vue';

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
    <AppLayout>
        <Head :title="`Editar ${producto.nombre}`" />

        <div class="mx-auto" style="max-width: 720px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="text-primary fs-3 mb-0">✏️ Editar producto</h2>
                <Link href="/productos" class="btn btn-light btn-sm fw-semibold">← Volver</Link>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <ProductoForm
                        :form="form"
                        :categorias="categorias"
                        :marcas="marcas"
                        :proveedores="proveedores"
                        texto-boton="Guardar cambios"
                        @enviar="enviar"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
