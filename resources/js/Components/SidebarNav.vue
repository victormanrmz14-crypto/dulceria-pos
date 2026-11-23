<script setup>
import { computed, ref, useId } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import UiIcon from './Ui/UiIcon.vue';

defineProps({ premium: { type: Boolean, default: false } });

const page = usePage();
const user = computed(() => page.props.auth.user);
const esAdmin = computed(() => user.value?.rol === 'admin');
const csrf = computed(() => page.props.csrf);
const tenantConfig = computed(() => page.props.tenantConfig ?? null);
const logoUrl = computed(() => tenantConfig.value?.logo ?? null);
const nombreMostrar = computed(() => tenantConfig.value?.nombre_mostrar || '🍬 Dulcería POS');

const activo = (prefijo) => page.url.startsWith(prefijo);

const cajaAbierta = ref(activo('/caja') || activo('/cortes'));
const catalogosAbiertos = ref(activo('/catalogos'));
const cajaId = useId();
const catalogosId = useId();
const premiumName = computed(() => tenantConfig.value?.nombre_mostrar || 'Dulcería POS');
const primaryLinks = [
    { href: '/dashboard', icon: 'dashboard', label: 'Inicio' },
    { href: '/ventas', icon: 'cart', label: 'Ventas' },
];
const adminLinks = [
    { href: '/productos', icon: 'package', label: 'Productos' },
    { href: '/usuarios', icon: 'users', label: 'Usuarios' },
    { href: '/reportes', icon: 'chart', label: 'Reportes' },
];
const catalogLinks = [
    { href: '/catalogos/categorias', icon: 'tags', label: 'Categorías' },
    { href: '/catalogos/marcas', icon: 'store', label: 'Marcas' },
    { href: '/catalogos/proveedores', icon: 'truck', label: 'Proveedores' },
];
</script>

<template>
    <template v-if="premium">
        <div class="p-brand">
            <img v-if="logoUrl" :src="logoUrl" :alt="premiumName" width="34" height="34" class="p-brand__logo" />
            <span v-else class="p-brand__symbol"><UiIcon name="candy" :size="22" /></span>
            <div><span class="p-brand__name">{{ premiumName }}</span><span class="p-brand__caption">Administración</span></div>
        </div>
        <nav class="p-nav" aria-label="Navegación principal">
            <span class="p-nav__label">Tu negocio</span>
            <Link v-for="item in primaryLinks" :key="item.href" :href="item.href" class="p-nav__item"
                :class="{ 'is-active': activo(item.href) }" :aria-current="activo(item.href) ? 'page' : undefined">
                <UiIcon :name="item.icon" /><span>{{ item.label }}</span>
            </Link>
            <button type="button" class="p-nav__item" :class="{ 'is-active': activo('/caja') || activo('/cortes') }"
                :aria-expanded="cajaAbierta" :aria-controls="cajaId" @click="cajaAbierta = !cajaAbierta">
                <UiIcon name="cash" /><span>Caja</span><UiIcon name="chevron" :size="15" class="p-nav__chevron" :class="{ 'is-open': cajaAbierta }" />
            </button>
            <Transition name="p-nav-group">
            <div v-show="cajaAbierta" :id="cajaId" class="p-nav__children">
                <Link href="/caja" class="p-nav__item" :class="{ 'is-active': activo('/caja') }" :aria-current="activo('/caja') ? 'page' : undefined"><UiIcon name="cash" :size="16" />Caja actual</Link>
                <Link v-if="esAdmin" href="/cortes" class="p-nav__item" :class="{ 'is-active': activo('/cortes') }" :aria-current="activo('/cortes') ? 'page' : undefined"><UiIcon name="receipt" :size="16" />Cortes históricos</Link>
            </div>
            </Transition>
            <template v-if="esAdmin">
                <span class="p-nav__label p-nav__label--section">Gestión</span>
                <Link v-for="item in adminLinks" :key="item.href" :href="item.href" class="p-nav__item"
                    :class="{ 'is-active': activo(item.href) }" :aria-current="activo(item.href) ? 'page' : undefined">
                    <UiIcon :name="item.icon" /><span>{{ item.label }}</span>
                </Link>
                <button type="button" class="p-nav__item" :class="{ 'is-active': activo('/catalogos') }"
                    :aria-expanded="catalogosAbiertos" :aria-controls="catalogosId" @click="catalogosAbiertos = !catalogosAbiertos">
                    <UiIcon name="tags" /><span>Catálogos</span><UiIcon name="chevron" :size="15" class="p-nav__chevron" :class="{ 'is-open': catalogosAbiertos }" />
                </button>
                <Transition name="p-nav-group">
                <div v-show="catalogosAbiertos" :id="catalogosId" class="p-nav__children">
                    <Link v-for="item in catalogLinks" :key="item.href" :href="item.href" class="p-nav__item"
                        :class="{ 'is-active': activo(item.href) }" :aria-current="activo(item.href) ? 'page' : undefined"><UiIcon :name="item.icon" :size="16" />{{ item.label }}</Link>
                </div>
                </Transition>
            </template>
        </nav>
        <div class="p-nav__footer">
            <Link v-if="esAdmin" href="/configuracion" class="p-nav__item" :class="{ 'is-active': activo('/configuracion') }" :aria-current="activo('/configuracion') ? 'page' : undefined"><UiIcon name="settings" /><span>Configuración</span></Link>
            <div class="p-nav__identity"><span class="p-avatar">{{ user?.nombre?.charAt(0) }}</span><div><strong>{{ user?.nombre }}</strong><span>Administrador</span></div></div>
            <form method="POST" action="/logout"><input type="hidden" name="_token" :value="csrf"><button type="submit" class="p-nav__item p-nav__logout"><UiIcon name="logout" /><span>Cerrar sesión</span></button></form>
        </div>
    </template>
    <template v-else>
    <div class="marca">
        <img v-if="logoUrl" :src="logoUrl" :alt="nombreMostrar" class="sidebar-logo" />
        <h1 class="titulo-marca" :class="{ 'titulo-con-logo': logoUrl }">{{ nombreMostrar }}</h1>
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

    <!-- Configuración (solo admins) -->
    <Link v-if="esAdmin" href="/configuracion" class="nav-item-pos nav-item-config" :class="{ activo: activo('/configuracion') }">
        ⚙️ Configuración
    </Link>

    <!-- Logout: form clásico (no XHR) para aterrizar en la página Blade de login -->
    <form method="POST" action="/logout">
        <input type="hidden" name="_token" :value="csrf">
        <button type="submit" class="btn-salir">🚪 Cerrar Sesión</button>
    </form>
    </template>
</template>
