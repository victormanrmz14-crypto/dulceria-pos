<script setup>
import { computed, ref, useId } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import UiIcon from './Ui/UiIcon.vue';
import TenantIdentityName from './TenantIdentityName.vue';

const props = defineProps({ navigation: { type: Object, required: true } });

const page = usePage();
const user = computed(() => page.props.auth?.user);
const esAdmin = computed(() => user.value?.rol === 'admin');
const logoUrl = computed(() => page.props.tenantConfig?.logo);
const primaryLinks = computed(() => props.navigation.primary.filter(item => item.id !== 'products'));
const adminLinks = computed(() => props.navigation.items.filter(item => ['products', 'users', 'reports'].includes(item.id)));
const catalogLinks = computed(() => props.navigation.items.filter(item => ['categories', 'brands', 'suppliers'].includes(item.id)));
const cashLinks = computed(() => props.navigation.items.filter(item => ['cash', 'closures'].includes(item.id)));
const settings = computed(() => props.navigation.items.find(item => item.id === 'settings'));
const cashActive = computed(() => cashLinks.value.some(item => item.active));
const catalogsActive = computed(() => catalogLinks.value.some(item => item.active));
const cajaAbierta = ref(cashActive.value);
const catalogosAbiertos = ref(catalogsActive.value);
const cajaId = useId();
const catalogosId = useId();
</script>

<template>
    <div class="p-brand">
        <img v-if="logoUrl" :src="logoUrl" alt="" width="34" height="34" class="p-brand__logo" />
        <span v-else class="p-brand__symbol"><UiIcon name="candy" :size="22" /></span>
        <div><TenantIdentityName multiline /><span class="p-brand__caption">{{ esAdmin ? 'Administración' : 'Punto de venta' }}</span></div>
    </div>
    <nav class="p-nav" aria-label="Navegación principal">
        <span class="p-nav__label">Tu negocio</span>
        <Link v-for="item in primaryLinks" :key="item.href" :href="item.href" class="p-nav__item"
            :class="{ 'is-active': item.active }" :aria-current="item.current ? 'page' : undefined">
            <UiIcon :name="item.icon" /><span>{{ item.label }}</span>
        </Link>
        <button type="button" class="p-nav__item" :class="{ 'is-active': cashActive }"
            :aria-expanded="cajaAbierta" :aria-controls="cajaId" @click="cajaAbierta = !cajaAbierta">
            <UiIcon name="cash" /><span>Caja</span><UiIcon name="chevron" :size="15" class="p-nav__chevron" :class="{ 'is-open': cajaAbierta }" />
        </button>
        <Transition name="p-nav-group">
        <div v-show="cajaAbierta" :id="cajaId" class="p-nav__children">
            <Link v-for="item in cashLinks" :key="item.id" :href="item.href" class="p-nav__item"
                :class="{ 'is-active': item.active }" :aria-current="item.current ? 'page' : undefined"><UiIcon :name="item.icon" :size="16" />{{ item.label }}</Link>
        </div>
        </Transition>
        <template v-if="esAdmin">
            <span class="p-nav__label p-nav__label--section">Gestión</span>
            <Link v-for="item in adminLinks" :key="item.href" :href="item.href" class="p-nav__item"
                :class="{ 'is-active': item.active }" :aria-current="item.current ? 'page' : undefined">
                <UiIcon :name="item.icon" /><span>{{ item.label }}</span>
            </Link>
            <button type="button" class="p-nav__item" :class="{ 'is-active': catalogsActive }"
                :aria-expanded="catalogosAbiertos" :aria-controls="catalogosId" @click="catalogosAbiertos = !catalogosAbiertos">
                <UiIcon name="tags" /><span>Catálogos</span><UiIcon name="chevron" :size="15" class="p-nav__chevron" :class="{ 'is-open': catalogosAbiertos }" />
            </button>
            <Transition name="p-nav-group">
            <div v-show="catalogosAbiertos" :id="catalogosId" class="p-nav__children">
                <Link v-for="item in catalogLinks" :key="item.href" :href="item.href" class="p-nav__item"
                    :class="{ 'is-active': item.active }" :aria-current="item.current ? 'page' : undefined"><UiIcon :name="item.icon" :size="16" />{{ item.label }}</Link>
            </div>
            </Transition>
        </template>
    </nav>
    <div v-if="settings" class="p-nav__footer">
        <Link :href="settings.href" class="p-nav__item" :class="{ 'is-active': settings.active }" :aria-current="settings.current ? 'page' : undefined"><UiIcon :name="settings.icon" /><span>{{ settings.label }}</span></Link>
    </div>
</template>
