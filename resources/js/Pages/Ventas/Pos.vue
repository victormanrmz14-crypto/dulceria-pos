<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PosCartPanel from '../../Components/Pos/PosCartPanel.vue';
import PosCheckout from '../../Components/Pos/PosCheckout.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiOverlay from '../../Components/Ui/UiOverlay.vue';
import { Search } from '@lucide/vue';
import { moneda, numero } from '../../utils';

const props = defineProps({
    productosIniciales: Array,
});

const page = usePage();
const esAdmin = computed(() => page.props.auth.user?.rol === 'admin');

// ---- Búsqueda con debounce ----
const buscar = ref('');
const productos = ref([...props.productosIniciales]);
const buscando = ref(false);
let timer = null;

watch(buscar, (valor) => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
        buscando.value = true;
        try {
            const { data } = await window.axios.get('/ventas/buscar-productos', {
                params: { buscar: valor },
            });
            productos.value = data;
        } finally {
            buscando.value = false;
        }
    }, 300);
});

// ---- Carrito ----
const carrito = ref([]);   // [{ id, nombre, precio, cantidad, stock, unidad }]
const errores = ref({});   // { [id]: 'mensaje' }

const agregar = (producto) => {
    const item = carrito.value.find((i) => i.id === producto.id);
    if (item) {
        if (item.cantidad >= producto.stock) {
            errores.value[producto.id] = `Solo hay ${numero(producto.stock)} disponibles`;
            return;
        }
        delete errores.value[producto.id];
        item.cantidad++;
    } else {
        carrito.value.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            cantidad: 1,
            stock: producto.stock,
            unidad: producto.unidad_medida,
        });
    }
    buscar.value = '';
};

const quitar = (id) => {
    carrito.value = carrito.value.filter((i) => i.id !== id);
    delete errores.value[id];
};

const cambiarCantidad = (item, valor) => {
    const cantidad = parseInt(valor, 10);
    if (!cantidad || cantidad <= 0) return quitar(item.id);
    if (cantidad > item.stock) {
        errores.value[item.id] = `Solo hay ${numero(item.stock)} disponibles`;
        return;
    }
    delete errores.value[item.id];
    item.cantidad = cantidad;
};

const limpiar = () => {
    carrito.value = [];
    errores.value = {};
    metodoPago.value = 'efectivo';
    montoRecibido.value = null;
};

// ---- Totales ----
// Sin IVA: el total es la suma de los productos (el precio ya es el final).
const total = computed(() =>
    Math.round(carrito.value.reduce((s, i) => s + i.precio * i.cantidad, 0) * 100) / 100,
);

// ---- Pago ----
const metodoPago = ref('efectivo');
const montoRecibido = ref(null);

const cambio = computed(() => {
    if (metodoPago.value !== 'efectivo' || !montoRecibido.value) return 0;
    return montoRecibido.value >= total.value
        ? Math.round((montoRecibido.value - total.value) * 100) / 100
        : 0;
});

const hayErrores = computed(() => Object.keys(errores.value).length > 0);

// ---- Confirmar y procesar ----
const mostrarConfirmacion = ref(false);
const procesando = ref(false);

const confirmar = () => {
    if (!carrito.value.length || hayErrores.value) return;
    if (
        metodoPago.value === 'efectivo' &&
        montoRecibido.value > 0 &&
        montoRecibido.value < total.value
    ) {
        errores.value.monto = 'El monto recibido es menor al total.';
        return;
    }
    delete errores.value.monto;
    mostrarConfirmacion.value = true;
};

const procesar = () => {
    procesando.value = true;
    router.post('/ventas', {
        items: carrito.value.map((i) => ({ id: i.id, cantidad: i.cantidad })),
        metodo_pago: metodoPago.value,
        monto_recibido: montoRecibido.value,
        total_cliente: total.value,
    }, {
        onFinish: () => {
            procesando.value = false;
            mostrarConfirmacion.value = false;
        },
    });
};
</script>

<template>
    <AppLayout premium v-slot="{ theme }">
        <Head title="Punto de Venta" />

        <div class="p-page-heading pos-heading">
            <div>
                <h1 class="p-page-title">Punto de venta</h1>
                <p class="p-page-date">{{ new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            </div>
            <UiButton v-if="esAdmin" href="/ventas/historial" variant="secondary" class="pos-history"><UiIcon name="receipt" :size="16" />Ver historial</UiButton>
        </div>

        <div class="pos-workbench">
            <div class="pos-search-area">
                <label for="pos-search" class="pos-search-label">Buscar productos</label>
                <div class="pos-search">
                    <Search :size="20" :stroke-width="1.75" aria-hidden="true" />
                    <input id="pos-search" v-model="buscar" type="search" placeholder="Buscar por nombre…" autocomplete="off" enterkeyhint="search" aria-controls="pos-products">
                    <UiIcon v-if="buscando" name="loading" class="p-spin" :size="18" />
                </div>
                <span class="pos-sr-only" role="status">{{ buscando ? 'Buscando productos…' : `${productos.length} productos en la lista` }}</span>
            </div>

            <PosCartPanel :count="carrito.length" :total="moneda(total)" :theme="theme" v-slot="{ desktop }">
                <PosCheckout :items="carrito" :errors="errores" :total="total" :change="cambio" :desktop="desktop"
                    v-model:payment="metodoPago" v-model:received="montoRecibido" :has-errors="hayErrores"
                    @remove="quitar" @quantity="cambiarCantidad" @clear="limpiar" @confirm="confirmar" />
            </PosCartPanel>

            <section id="pos-products" class="p-panel pos-catalog" aria-labelledby="pos-products-title" :aria-busy="buscando">
                <header class="pos-catalog-heading"><h2 id="pos-products-title">Productos</h2><span>{{ productos.length }} {{ productos.length === 1 ? 'disponible' : 'disponibles' }}</span></header>
                <div class="pos-product-list">
                    <button v-for="producto in productos" :key="producto.id" type="button" class="pos-product" @click="agregar(producto)">
                        <span class="pos-product-symbol"><UiIcon name="candy" :size="20" /></span>
                        <span class="pos-product-copy"><strong>{{ producto.nombre }}</strong><span>Existencias: {{ numero(producto.stock) }} {{ producto.unidad_medida }}</span></span>
                        <span class="pos-product-price">{{ moneda(producto.precio) }}<span>por {{ producto.unidad_medida }}</span></span>
                        <span class="pos-product-add"><UiIcon name="plus" :size="20" /><span class="pos-sr-only">Agregar al carrito</span></span>
                    </button>
                    <UiEmptyState v-if="!productos.length" icon="package" title="No se encontraron productos" description="Prueba con otro nombre en el buscador." class="pos-catalog-empty" />
                </div>
            </section>

        </div>

        <UiOverlay :open="mostrarConfirmacion" title="Confirmar venta" :theme="theme" class="pos-confirmation"
            @close="mostrarConfirmacion = false">
            <div class="pos-confirm-body">
                <div class="pos-confirm-total"><span>Total a cobrar</span><strong>{{ moneda(total) }}</strong></div>
                <dl class="pos-confirm-details">
                    <div><dt>Método de pago</dt><dd><UiIcon :name="metodoPago === 'efectivo' ? 'cash' : 'card'" :size="16" />{{ metodoPago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</dd></div>
                    <div v-if="metodoPago === 'efectivo' && montoRecibido > 0"><dt>Monto recibido</dt><dd>{{ moneda(montoRecibido) }}</dd></div>
                    <div v-if="cambio > 0" class="pos-confirm-change"><dt>Cambio</dt><dd>{{ moneda(cambio) }}</dd></div>
                </dl>
                <div class="pos-confirm-actions">
                    <UiButton variant="secondary" @click="mostrarConfirmacion = false">Cancelar</UiButton>
                    <UiButton :loading="procesando" @click="procesar"><UiIcon v-if="!procesando" name="success" :size="16" />Confirmar</UiButton>
                </div>
            </div>
        </UiOverlay>
    </AppLayout>
</template>

<style scoped>
.pos-heading { margin-bottom: 24px; }
.pos-history { flex-shrink: 0; }
.pos-workbench { display: grid; grid-template-columns: minmax(0, 1fr) 360px; grid-template-rows: auto 1fr; gap: 20px 24px; align-items: start; }
.pos-search-area { grid-column: 1; min-width: 0; }
.pos-search-label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500; }
.pos-search { display: flex; align-items: center; gap: 12px; min-height: 52px; padding: 0 16px; border: 1px solid #858a94; border-radius: var(--p-radius-control); background: var(--p-surface); color: var(--p-muted); }
.pos-search:focus-within { border-color: var(--p-brand-ink); outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.pos-search > svg { flex-shrink: 0; }
.pos-search input { width: 100%; min-width: 0; min-height: 50px; padding: 10px 0; border: 0; outline: 0; background: transparent; color: var(--p-text); font: inherit; font-size: 16px; }
.pos-search input::placeholder { color: var(--p-muted); opacity: 1; }
.pos-sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; border: 0; }
.pos-catalog { grid-column: 1; min-width: 0; }
.pos-catalog-heading { display: flex; align-items: center; flex-wrap: wrap; justify-content: space-between; gap: 8px; padding: 16px 20px; border-bottom: 1px solid var(--p-border); }
.pos-catalog-heading h2 { font: inherit; font-weight: 600; margin: 0; }
.pos-catalog-heading > span { color: var(--p-muted); font-size: 12px; }
.pos-product { display: grid; grid-template-columns: 36px minmax(0, 1fr) auto 32px; align-items: center; gap: 12px; width: 100%; min-height: 72px; padding: 12px 20px; border: 0; border-bottom: 1px solid var(--p-border); background: transparent; color: var(--p-text); font: inherit; text-align: left; cursor: pointer; }
.pos-product:last-child { border-bottom: 0; border-radius: 0 0 var(--p-radius-panel) var(--p-radius-panel); }
.pos-product:hover { background: var(--p-canvas); }
.pos-product:active { background: var(--p-muted-surface); }
.pos-product:focus-visible { position: relative; z-index: 1; outline-offset: -3px; }
.pos-product-symbol { display: grid; place-items: center; width: 36px; height: 40px; border-radius: 6px; background: var(--p-canvas); color: var(--p-muted); }
.pos-product-copy { min-width: 0; }
.pos-product-copy > strong { display: block; font-size: 13px; font-weight: 500; overflow-wrap: anywhere; line-height: 1.5; }
.pos-product-copy > span { display: block; color: var(--p-muted); font-size: 12px; margin-top: 4px; overflow-wrap: anywhere; }
.pos-product-price { font-size: 15px; font-weight: 600; white-space: nowrap; text-align: right; font-variant-numeric: tabular-nums; }
.pos-product-price > span { display: block; color: var(--p-muted); font-size: 12px; font-weight: 400; margin-top: 4px; }
.pos-product-add { display: grid; place-items: center; width: 32px; height: 32px; border: 1px solid var(--p-border); border-radius: 6px; color: var(--p-brand-ink); }
.pos-product:hover .pos-product-add { border-color: var(--p-brand-ink); }
.pos-catalog-empty { min-height: 280px; }
.pos-workbench > :deep(.pos-cart-host) { grid-column: 2; grid-row: 1 / span 2; }
.pos-confirm-body { padding: 24px; }
.pos-confirm-total { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px; margin-bottom: 24px; }
.pos-confirm-total > span { font-size: 13px; color: var(--p-muted); }
.pos-confirm-total > strong { font-size: 30px; font-weight: 600; line-height: 1.3; letter-spacing: -.8px; font-variant-numeric: tabular-nums; white-space: nowrap; }
.pos-confirm-details { margin: 0 0 24px; }
.pos-confirm-details > div { display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; gap: 8px; padding-block: 12px; border-top: 1px solid var(--p-border); font-size: 13px; }
.pos-confirm-details dt { font-weight: 400; color: var(--p-muted); }
.pos-confirm-details dd { display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 500; font-variant-numeric: tabular-nums; }
.pos-confirm-details .pos-confirm-change { color: var(--p-success); }
.pos-confirm-change dt { color: inherit; }
.pos-confirm-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (min-width: 992px) and (max-width: 1199.98px) {
    .pos-workbench { grid-template-columns: minmax(0, 1fr) 320px; column-gap: 16px; }
    .pos-product { grid-template-columns: minmax(0, 1fr) auto; gap: 12px; padding: 16px; }
    .pos-product-symbol, .pos-product-add { display: none; }
}
@media (max-width: 991.98px) {
    .pos-workbench { grid-template-columns: minmax(0, 1fr); gap: 16px; }
    .pos-workbench > :deep(.pos-cart-host) { grid-column: 1; grid-row: 2; position: sticky; top: 12px; z-index: 2; }
    .pos-catalog { grid-row: 3; }
}
@media (max-width: 767.98px) {
    .pos-heading { align-items: flex-start; gap: 12px; margin-bottom: 20px; }
    .pos-history { min-height: 44px; padding: 8px 10px; font-size: 12px; }
    .pos-product { grid-template-columns: minmax(0, 1fr) auto 28px; gap: 10px; padding: 14px 16px; min-height: 80px; }
    .pos-product-symbol { display: none; }
    .pos-product-add { width: 28px; height: 28px; }
    .pos-product-copy > strong { font-weight: 500; }
    .pos-catalog-heading { padding: 14px 16px; }
    .pos-confirm-body { padding: 20px 16px; }
}
@media (max-width: 359.98px) {
    .pos-heading .pos-history { padding: 8px 12px; }
    .pos-product { grid-template-columns: minmax(0, 1fr) auto; }
    .pos-product-add { display: none; }
}
</style>
