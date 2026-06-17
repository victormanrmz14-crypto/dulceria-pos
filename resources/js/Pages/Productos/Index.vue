<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Paginacion from '../../Components/Paginacion.vue';
import { moneda, numero } from '../../utils';

const props = defineProps({
    productos: Object,
    categorias: Array,
    filtros: Object,
});

const buscar = ref(props.filtros.buscar);
const categoriaId = ref(props.filtros.categoria_id);
const stockBajo = ref(props.filtros.stock_bajo);

let timer = null;
const aplicarFiltros = () => {
    router.get('/productos', {
        buscar: buscar.value || undefined,
        categoria_id: categoriaId.value || undefined,
        stock_bajo: stockBajo.value ? 1 : undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(buscar, () => {
    clearTimeout(timer);
    timer = setTimeout(aplicarFiltros, 350);
});
watch([categoriaId, stockBajo], aplicarFiltros);

const toggleActivo = (producto) => {
    router.delete(`/productos/${producto.id}`, { preserveScroll: true });
};

const notificarProveedor = (producto) => {
    if (!confirm(`¿Enviar notificación de reabasto a ${producto.proveedor}?`)) return;
    router.post(`/productos/${producto.id}/notificar-proveedor`, {}, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head title="Productos" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="text-primary fs-3 mb-0">🍬 Productos</h2>
            <Link href="/productos/create" class="btn btn-primary fw-semibold">+ Nuevo producto</Link>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">🔍</span>
                            <input v-model="buscar" type="text" class="form-control"
                                   placeholder="Buscar por nombre...">
                        </div>
                    </div>
                    <div class="col-8 col-md-4">
                        <select v-model="categoriaId" class="form-select form-select-sm">
                            <option value="">Todas las categorías</option>
                            <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </div>
                    <div class="col-4 col-md-3">
                        <div class="form-check form-switch">
                            <input id="chk-stock" v-model="stockBajo" class="form-check-input" type="checkbox">
                            <label for="chk-stock" class="form-check-label small fw-semibold">Stock bajo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Marca</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in productos.data" :key="p.id" :class="{ 'opacity-50': !p.activo }">
                            <td class="fw-semibold small">{{ p.nombre }}</td>
                            <td class="small">{{ p.categoria }}</td>
                            <td class="small">{{ p.marca }}</td>
                            <td class="text-end fw-bold text-primary small">{{ moneda(p.precio) }}</td>
                            <td class="text-center">
                                <span class="badge" :class="p.stock_bajo ? 'text-bg-danger' : 'text-bg-success'">
                                    {{ numero(p.stock) }} {{ p.unidad_medida }}
                                </span>
                                <div v-if="p.stock_bajo" class="text-danger" style="font-size: 0.7rem;">
                                    mín: {{ numero(p.stock_minimo) }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" :class="p.activo ? 'text-bg-success' : 'text-bg-secondary'">
                                    {{ p.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <Link :href="`/productos/${p.id}/edit`" class="btn btn-light" title="Editar">✏️</Link>
                                    <button v-if="p.stock_bajo && p.proveedor" type="button" class="btn btn-light"
                                            title="Notificar proveedor" @click="notificarProveedor(p)">📧</button>
                                    <button type="button" class="btn btn-light"
                                            :title="p.activo ? 'Desactivar' : 'Activar'"
                                            @click="toggleActivo(p)">
                                        {{ p.activo ? '🚫' : '✅' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!productos.data.length">
                            <td colspan="7" class="text-center text-secondary py-5">
                                No se encontraron productos con esos filtros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <Paginacion :links="productos.links" />
            </div>
        </div>
    </AppLayout>
</template>
