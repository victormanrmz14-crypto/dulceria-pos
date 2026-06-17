<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const esAdmin = computed(() => user.value?.rol === 'admin');
const csrf = computed(() => page.props.csrf);

const activo = (prefijo) => page.url.startsWith(prefijo);

const cajaAbierta = ref(activo('/caja') || activo('/cortes'));
const catalogosAbiertos = ref(activo('/catalogos'));
</script>

<template>
    <div class="marca">
        <h1 class="titulo-marca">🍬 Dulcería POS</h1>
        <span>{{ user?.nombre ?? 'Usuario' }}</span>
    </div>

    <nav class="flex-grow-1 overflow-y-auto py-2">
        <Link href="/dashboard" class="nav-item-pos" :class="{ activo: activo('/dashboard') }">
            🏠 Inicio
        </Link>

        <Link href="/ventas" class="nav-item-pos" :class="{ activo: activo('/ventas') }">
            🛒 Ventas
        </Link>

        <!-- Caja -->
        <div>
            <button
                type="button"
                class="nav-item-pos justify-content-between pe-3"
                :class="{ activo: activo('/caja') || activo('/cortes') }"
                @click="cajaAbierta = !cajaAbierta"
            >
                <span>🏦 Caja</span>
                <span class="small">{{ cajaAbierta ? '▲' : '▼' }}</span>
            </button>
            <div v-show="cajaAbierta" class="submenu">
                <Link href="/caja" class="subitem" :class="{ activo: activo('/caja') }">
                    💰 Caja Actual
                </Link>
                <Link v-if="esAdmin" href="/cortes" class="subitem" :class="{ activo: activo('/cortes') }">
                    📋 Cortes Históricos
                </Link>
            </div>
        </div>

        <template v-if="esAdmin">
            <Link href="/productos" class="nav-item-pos" :class="{ activo: activo('/productos') }">
                🍬 Productos
            </Link>

            <Link href="/usuarios" class="nav-item-pos" :class="{ activo: activo('/usuarios') }">
                👥 Usuarios
            </Link>

            <Link href="/reportes" class="nav-item-pos" :class="{ activo: activo('/reportes') }">
                📊 Reportes
            </Link>

            <div>
                <button
                    type="button"
                    class="nav-item-pos justify-content-between pe-3"
                    :class="{ activo: activo('/catalogos') }"
                    @click="catalogosAbiertos = !catalogosAbiertos"
                >
                    <span>📝 Catálogos</span>
                    <span class="small">{{ catalogosAbiertos ? '▲' : '▼' }}</span>
                </button>
                <div v-show="catalogosAbiertos" class="submenu">
                    <Link href="/catalogos/categorias" class="subitem" :class="{ activo: activo('/catalogos/categorias') }">
                        🏷️ Categorías
                    </Link>
                    <Link href="/catalogos/marcas" class="subitem" :class="{ activo: activo('/catalogos/marcas') }">
                        🏭 Marcas
                    </Link>
                    <Link href="/catalogos/proveedores" class="subitem" :class="{ activo: activo('/catalogos/proveedores') }">
                        🚚 Proveedores
                    </Link>
                </div>
            </div>
        </template>
    </nav>

    <!-- Logout: form clásico (no XHR) para aterrizar en la página Blade de login -->
    <form method="POST" action="/logout">
        <input type="hidden" name="_token" :value="csrf">
        <button type="submit" class="btn-salir">🚪 Cerrar Sesión</button>
    </form>
</template>
