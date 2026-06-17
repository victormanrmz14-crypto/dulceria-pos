<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ProductoForm from '../../Components/ProductoForm.vue';

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
    <AppLayout>
        <Head title="Nuevo producto" />

        <div class="mx-auto" style="max-width: 720px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="text-primary fs-3 mb-0">🍬 Nuevo producto</h2>
                <Link href="/productos" class="btn btn-light btn-sm fw-semibold">← Volver</Link>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <ProductoForm
                        :form="form"
                        :categorias="categorias"
                        :marcas="marcas"
                        :proveedores="proveedores"
                        texto-boton="Crear producto"
                        @enviar="enviar"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
