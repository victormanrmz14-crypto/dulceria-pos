<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ModalPos from '../../Components/ModalPos.vue';
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
    <AppLayout>
        <Head title="Punto de Venta" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="text-primary fs-3 mb-1">🛒 Punto de Venta</h2>
                <p class="text-secondary small mb-0">
                    {{ new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                </p>
            </div>
            <Link v-if="esAdmin" href="/ventas/historial" class="btn btn-light fw-semibold btn-sm">
                📋 Ver historial
            </Link>
        </div>

        <div class="row g-3">
            <!-- Productos -->
            <div class="col-12 col-lg-7 col-xl-8">
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">🔍</span>
                            <input
                                v-model="buscar"
                                type="text"
                                class="form-control border-start-0"
                                placeholder="Buscar producto por nombre..."
                            >
                            <span v-if="buscando" class="input-group-text bg-white">
                                <span class="spinner-border spinner-border-sm text-primary" />
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="list-group list-group-flush">
                        <button
                            v-for="producto in productos"
                            :key="producto.id"
                            type="button"
                            class="list-group-item producto-fila d-flex justify-content-between align-items-center border-0 border-bottom"
                            @click="agregar(producto)"
                        >
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded d-flex align-items-center justify-content-center fs-4"
                                     style="width: 42px; height: 42px;">🍬</div>
                                <div class="text-start">
                                    <div class="fw-semibold small">{{ producto.nombre }}</div>
                                    <div class="text-secondary" style="font-size: 0.78rem;">
                                        Stock: {{ numero(producto.stock) }} {{ producto.unidad_medida }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">{{ moneda(producto.precio) }}</div>
                                <div class="text-success fw-semibold" style="font-size: 0.75rem;">+ Agregar</div>
                            </div>
                        </button>
                        <div v-if="!productos.length" class="text-center text-secondary py-5">
                            <div class="fs-2">🍬</div>
                            <div class="small mt-2">No se encontraron productos.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carrito -->
            <div class="col-12 col-lg-5 col-xl-4">
                <div class="card">
                    <div class="card-header encabezado">🛒 Carrito</div>
                    <div class="card-body">
                        <div v-for="item in carrito" :key="item.id" class="py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span class="fw-semibold small flex-grow-1">{{ item.nombre }}</span>
                                <button type="button" class="btn btn-sm text-danger p-0" @click="quitar(item.id)">✕</button>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <input
                                    type="number"
                                    class="form-control form-control-sm text-center fw-semibold"
                                    :class="{ 'is-invalid': errores[item.id] }"
                                    style="width: 76px;"
                                    :value="item.cantidad"
                                    min="1"
                                    :max="item.stock"
                                    @change="cambiarCantidad(item, $event.target.value)"
                                >
                                <span class="text-secondary small ms-auto">
                                    {{ moneda(item.precio * item.cantidad) }}
                                </span>
                            </div>
                            <div v-if="errores[item.id]" class="text-danger mt-1" style="font-size: 0.75rem;">
                                {{ errores[item.id] }}
                            </div>
                        </div>

                        <div v-if="!carrito.length" class="text-center text-secondary py-5">
                            <div class="fs-1">🛒</div>
                            <div class="small mt-2">Agrega productos haciendo clic</div>
                        </div>

                        <!-- Totales y cobro -->
                        <div v-if="carrito.length" class="pt-3 mt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold fs-4 text-primary">{{ moneda(total) }}</span>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <button type="button"
                                            class="metodo-pago w-100 py-2"
                                            :class="{ activo: metodoPago === 'efectivo' }"
                                            @click="metodoPago = 'efectivo'">
                                        💵 Efectivo
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button"
                                            class="metodo-pago w-100 py-2"
                                            :class="{ activo: metodoPago === 'tarjeta' }"
                                            @click="metodoPago = 'tarjeta'">
                                        💳 Tarjeta
                                    </button>
                                </div>
                            </div>

                            <div v-if="metodoPago === 'efectivo'" class="mb-3">
                                <label class="form-label small fw-semibold text-secondary mb-1">Monto recibido</label>
                                <input
                                    v-model.number="montoRecibido"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control text-end"
                                    :class="{ 'is-invalid': errores.monto }"
                                    placeholder="0.00"
                                    @keydown.enter.prevent="confirmar"
                                >
                                <div v-if="errores.monto" class="invalid-feedback d-block">{{ errores.monto }}</div>
                                <div v-if="cambio > 0" class="text-success fw-bold text-end small mt-1">
                                    Cambio: {{ moneda(cambio) }}
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary w-100 py-2 fw-bold"
                                :disabled="hayErrores"
                                @click="confirmar"
                            >
                                Cobrar {{ moneda(total) }}
                            </button>
                            <button type="button" class="btn btn-light w-100 mt-2 btn-sm fw-semibold" @click="limpiar">
                                Limpiar carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal confirmación -->
        <ModalPos :abierto="mostrarConfirmacion" titulo="Confirmar venta" ancho="400px" @cerrar="mostrarConfirmacion = false">
            <p class="text-secondary small mb-2">
                Total: <strong class="text-primary fs-6">{{ moneda(total) }}</strong>
            </p>
            <p class="text-secondary small mb-2">
                Método: {{ metodoPago === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta' }}
            </p>
            <p v-if="metodoPago === 'efectivo' && montoRecibido > 0" class="text-secondary small mb-2">
                Recibido: <strong>{{ moneda(montoRecibido) }}</strong>
            </p>
            <p v-if="cambio > 0" class="text-success small fw-bold mb-3">
                Cambio: {{ moneda(cambio) }}
            </p>
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <button type="button" class="btn btn-light w-100 fw-semibold" @click="mostrarConfirmacion = false">
                        Cancelar
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-primary w-100 fw-bold" :disabled="procesando" @click="procesar">
                        <span v-if="procesando" class="spinner-border spinner-border-sm me-1" />
                        ✅ Confirmar
                    </button>
                </div>
            </div>
        </ModalPos>
    </AppLayout>
</template>
