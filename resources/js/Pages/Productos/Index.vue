<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Paginacion from '../../Components/Paginacion.vue';
import { moneda } from '../../utils';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import ProductActions from '../../Components/Productos/ProductActions.vue';
import ProductStock from '../../Components/Productos/ProductStock.vue';
import { Search } from '@lucide/vue';

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
    <AppLayout premium>
        <Head title="Productos" />
        <div class="inventory">
            <div class="p-page-heading inventory-heading">
                <div><h1 class="p-page-title">Productos e inventario</h1><p class="inventory-description">Consulta precios, existencias y disponibilidad de tus productos.</p></div>
                <UiButton href="/productos/create" class="inventory-create"><UiIcon name="plus" :size="18" />Nuevo producto</UiButton>
            </div>

            <div class="p-panel inventory-filters" role="search" aria-label="Filtrar productos">
                <div class="inventory-search-field">
                    <label for="product-search">Buscar producto</label>
                    <div class="inventory-search"><Search :size="18" :stroke-width="1.75" aria-hidden="true" /><input id="product-search" v-model="buscar" name="buscar" type="text" placeholder="Buscar por nombre…" autocomplete="off" aria-controls="product-results"></div>
                </div>
                <div class="inventory-category-field">
                    <label for="product-category">Categoría</label>
                    <select id="product-category" v-model="categoriaId" name="categoria_id" class="inventory-control" aria-controls="product-results">
                        <option value="">Todas las categorías</option>
                        <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </select>
                </div>
                <label class="inventory-stock-filter" for="chk-stock"><input id="chk-stock" v-model="stockBajo" name="stock_bajo" type="checkbox" aria-controls="product-results"><span>Stock bajo</span></label>
            </div>

            <section id="product-results" class="p-panel inventory-results" aria-labelledby="inventory-results-title">
                <header class="inventory-results-heading"><h2 id="inventory-results-title">Inventario</h2><span>{{ productos.total ?? productos.data.length }} productos</span></header>
                <template v-if="productos.data.length">
                    <div class="inventory-table-wrap">
                        <table class="inventory-table">
                            <caption class="visually-hidden">Productos, clasificación, precio, existencias, estado y acciones disponibles</caption>
                            <colgroup><col><col style="width: 14%;"><col style="width: 12%;"><col style="width: 12%;"><col style="width: 14%;"><col style="width: 96px;"><col style="width: 180px;"></colgroup>
                            <thead><tr><th scope="col">Producto</th><th scope="col">Categoría</th><th scope="col">Marca</th><th scope="col" class="inventory-number">Precio</th><th scope="col" class="inventory-number">Stock</th><th scope="col">Estado</th><th scope="col" class="inventory-number">Acciones</th></tr></thead>
                            <tbody>
                                <tr v-for="p in productos.data" :key="p.id">
                                    <th scope="row" class="inventory-product-name">{{ p.nombre }}</th>
                                    <td class="inventory-taxonomy">{{ p.categoria }}</td><td class="inventory-taxonomy">{{ p.marca }}</td>
                                    <td class="inventory-price inventory-number">{{ moneda(p.precio) }}</td>
                                    <td class="inventory-number"><ProductStock :stock="p.stock" :minimum="p.stock_minimo" :unit="p.unidad_medida" :low="p.stock_bajo" /></td>
                                    <td><span class="inventory-status" :class="{ 'is-inactive': !p.activo }"><span aria-hidden="true" />{{ p.activo ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td><ProductActions :producto="p" @toggle="toggleActivo" @notify="notificarProveedor" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="inventory-cards">
                        <article v-for="p in productos.data" :key="p.id" class="inventory-card">
                            <div class="inventory-card-heading"><h3>{{ p.nombre }}</h3><span class="inventory-status" :class="{ 'is-inactive': !p.activo }"><span aria-hidden="true" />{{ p.activo ? 'Activo' : 'Inactivo' }}</span></div>
                            <dl class="inventory-card-meta"><div><dt>Categoría</dt><dd>{{ p.categoria }}</dd></div><div><dt>Marca</dt><dd>{{ p.marca }}</dd></div></dl>
                            <dl class="inventory-card-values"><div><dt>Precio</dt><dd class="inventory-price">{{ moneda(p.precio) }}</dd></div><div><dt>Existencias</dt><dd><ProductStock :stock="p.stock" :minimum="p.stock_minimo" :unit="p.unidad_medida" :low="p.stock_bajo" /></dd></div></dl>
                            <ProductActions :producto="p" labels @toggle="toggleActivo" @notify="notificarProveedor" />
                        </article>
                    </div>
                </template>
                <UiEmptyState v-else icon="package" title="No se encontraron productos" description="No se encontraron productos con esos filtros." class="inventory-empty" />
                <footer class="inventory-footer"><Paginacion :links="productos.links" premium /></footer>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.inventory-heading { align-items: flex-start; gap: 16px; }
.inventory-description { margin: 8px 0 0; color: var(--p-muted); font-size: 13px; }
.inventory-create { flex-shrink: 0; }
.inventory-filters { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(220px, 1fr) auto; align-items: end; gap: 16px; padding: 20px; margin-bottom: 24px; }
.inventory-filters > div { min-width: 0; }
.inventory-filters > div > label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500; }
.inventory-control, .inventory-search { width: 100%; min-width: 0; min-height: 46px; border: 1px solid #858a94; border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-text); font: inherit; font-size: 16px; }
.inventory-control { padding: 10px 28px 10px 12px; }
.inventory-search { display: flex; align-items: center; gap: 10px; padding-inline: 12px; color: var(--p-muted); }
.inventory-search svg { flex-shrink: 0; }
.inventory-search input { width: 100%; min-width: 0; min-height: 44px; padding-block: 10px; border: 0; outline: 0; color: var(--p-text); background: transparent; font: inherit; }
.inventory-search input::placeholder { color: var(--p-muted); opacity: 1; }
.inventory-search:focus-within, .inventory-control:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.inventory-stock-filter { display: flex; align-items: center; gap: 10px; min-height: 46px; padding: 10px 12px; border: 1px solid #858a94; border-radius: var(--p-radius-control); font-size: 13px; cursor: pointer; }
.inventory-stock-filter input { width: 18px; height: 18px; margin: 0; accent-color: var(--p-brand-ink); cursor: pointer; }
.inventory-stock-filter input:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 3px; }
.inventory-stock-filter:has(input:checked) { color: var(--p-brand-ink); background: color-mix(in srgb, var(--p-brand) 5%, white); border-color: var(--p-brand-ink); }
.inventory-results-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--p-border); }
.inventory-results-heading h2 { margin: 0; font: inherit; font-weight: 600; }
.inventory-results-heading > span { font-size: 12px; color: var(--p-muted); }
.inventory-table { width: 100%; table-layout: auto; border-collapse: collapse; }
.inventory-table th, .inventory-table td { padding: 12px; border-bottom: 1px solid var(--p-border); vertical-align: middle; overflow-wrap: anywhere; }
.inventory-table th:first-child { padding-left: 20px; }
.inventory-table td:last-child, .inventory-table th:last-child { padding-right: 20px; }
.inventory-table thead th { padding-block: 12px; color: var(--p-muted); background: var(--p-canvas); font-size: 12px; font-weight: 500; }
.inventory-table tbody tr:last-child > * { border-bottom: 0; }
.inventory-table tbody tr:hover { background: var(--p-canvas); }
.inventory-product-name { color: var(--p-text); font-size: 13px; font-weight: 500; }
.inventory-taxonomy { font-size: 12px; color: var(--p-muted); }
.inventory-number { text-align: right; }
.inventory-number :deep(.product-stock) { align-items: flex-end; }
.inventory-price { font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.inventory-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 7px; border-radius: 5px; background: var(--p-canvas); color: var(--p-text); font-size: 12px; line-height: 1.5; white-space: nowrap; }
.inventory-status > span { width: 6px; height: 6px; border-radius: 50%; background: var(--p-success); }
.inventory-status.is-inactive { color: var(--p-muted); background: var(--p-muted-surface); }
.inventory-status.is-inactive > span { background: var(--p-muted); }
.inventory-cards { display: none; }
.inventory-empty { min-height: 256px; }
.inventory-footer { padding: 16px 20px; border-top: 1px solid var(--p-border); }
@media (max-width: 1199.98px) {
    .inventory-table-wrap { display: none; }
    .inventory-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .inventory-card { min-width: 0; padding: 20px; border-bottom: 1px solid var(--p-border); }
    .inventory-card:nth-child(odd) { border-right: 1px solid var(--p-border); }
    .inventory-card-heading { display: flex; align-items: flex-start; flex-wrap: wrap; gap: 12px; }
    .inventory-card-heading h3 { flex: 1 1 160px; min-width: 0; margin: 2px 0 0; font: inherit; font-size: 14px; font-weight: 600; line-height: 1.5; overflow-wrap: anywhere; }
    .inventory-card-meta, .inventory-card-values { display: flex; flex-wrap: wrap; gap: 12px 20px; margin: 16px 0 0; }
    .inventory-card-meta > div { flex: 1 1 100px; min-width: 0; }
    .inventory-card-values > div { flex: 1 0 auto; max-width: 100%; }
    .inventory-card dt { color: var(--p-muted); font-size: 12px; font-weight: 400; margin-bottom: 4px; }
    .inventory-card dd { margin: 0; font-size: 13px; overflow-wrap: anywhere; }
    .inventory-card dd.inventory-price { font-size: 16px; }
    .inventory-card :deep(.product-actions) { margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--p-border); }
}
@media (max-width: 767.98px) {
    .inventory-heading { margin-bottom: 20px; }
    .inventory-create { width: 100%; }
    .inventory-filters { grid-template-columns: minmax(0, 1fr) auto; padding: 16px; gap: 12px; margin-bottom: 20px; }
    .inventory-search-field { grid-column: 1 / -1; }
    .inventory-cards { grid-template-columns: minmax(0, 1fr); }
    .inventory-card { padding: 16px; }
    .inventory-card:nth-child(odd) { border-right: 0; }
    .inventory-card-heading h3 { flex-basis: 140px; }
    .inventory-results-heading { padding: 14px 16px; }
    .inventory-footer { padding: 16px; }
}
@media (max-width: 479.98px) { .inventory-category-field, .inventory-stock-filter { grid-column: 1 / -1; } .inventory-stock-filter { width: fit-content; } }
</style>
