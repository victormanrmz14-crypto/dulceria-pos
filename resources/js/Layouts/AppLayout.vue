<script setup>
import { computed, onBeforeUnmount, onMounted, ref, useId, watch, watchEffect } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import '../../css/premium.css';
import UiBottomNav from '../Components/Ui/UiBottomNav.vue';
import { tenantNavigation } from '../Navigation/tenantNavigation';
import { useMobileNavigationVisibility } from '../Composables/useMobileNavigationVisibility';
import SidebarNav from '../Components/SidebarNav.vue';
import UiIcon from '../Components/Ui/UiIcon.vue';
import UiOverlay from '../Components/Ui/UiOverlay.vue';
import AccountMenu from '../Components/AccountMenu.vue';
import { premiumTheme as createPremiumTheme } from '../Components/Ui/theme';

const props = defineProps({ premium: { type: Boolean, default: false } });

const page = usePage();

const drawer = ref(false);
const drawerId = useId();
const navigation = computed(() => tenantNavigation(page.props.auth?.user?.rol, page.url));
const { suppressed } = useMobileNavigationVisibility();
const stopNavigation = router.on('navigate', () => { drawer.value = false; });
const brandName = computed(() => page.props.tenantConfig?.nombre_mostrar || 'Dulcería POS');
const premiumTheme = computed(() => createPremiumTheme(page.props.tenantConfig?.colores));
let desktopQuery;
let tabletQuery;
const closeOnBreakpoint = () => { drawer.value = false; };
onMounted(() => {
    desktopQuery = matchMedia('(min-width: 992px)');
    desktopQuery.addEventListener('change', closeOnBreakpoint);
    tabletQuery = matchMedia('(min-width: 768px)');
    tabletQuery.addEventListener('change', closeOnBreakpoint);
});
onBeforeUnmount(() => {
    stopNavigation();
    desktopQuery?.removeEventListener('change', closeOnBreakpoint);
    tabletQuery?.removeEventListener('change', closeOnBreakpoint);
});

const toasts = ref([]);
let toastId = 0;
const empujarToast = (tipo, mensaje) => {
    const id = ++toastId;
    toasts.value.push({ id, tipo, mensaje });
    setTimeout(() => { toasts.value = toasts.value.filter((t) => t.id !== id); }, 4500);
};

// El layout no es persistente (se usa como <AppLayout> dentro de cada página),
// así que se re-monta en cada navegación y `immediate` vuelve a leer el flash
// — funciona incluso cuando el mensaje es idéntico al anterior. Mismo patrón
// que SuperAdminLayout, donde los toasts sí funcionaban.
watch(() => page.props.flash, (flash) => {
    if (flash?.success) empujarToast('success', flash.success);
    if (flash?.error)   empujarToast('error',   flash.error);
}, { immediate: true, deep: true });

const erroresGlobales = computed(() => page.props.errors ?? {});
const impersonando    = computed(() => !!page.props.impersonando);

// Aplica la paleta de colores del tenant via CSS custom properties
watchEffect(() => {
    const colores = page.props.tenantConfig?.colores;
    const root = document.documentElement;
    if (colores?.primario) {
        root.style.setProperty('--dulce-rojo',         colores.primario);
        root.style.setProperty('--dulce-rojo-medio',   colores.medio   ?? colores.primario);
        root.style.setProperty('--dulce-rojo-oscuro',  colores.oscuro  ?? colores.primario);
        root.style.setProperty('--dulce-rojo-hover',   colores.hover   ?? colores.oscuro ?? colores.primario);
        root.style.setProperty('--dulce-acento',       colores.acento  ?? '#ff7043');
        root.style.setProperty('--dulce-texto-suave',  colores.texto   ?? '#ffcccc');
        root.style.setProperty('--dulce-rosa-suave',   colores.fondo   ?? '#fff5f5');
        root.style.setProperty('--bs-primary',         colores.primario);
    } else {
        // Restaura los defaults si no hay paleta guardada
        root.style.removeProperty('--dulce-rojo');
        root.style.removeProperty('--dulce-rojo-medio');
        root.style.removeProperty('--dulce-rojo-oscuro');
        root.style.removeProperty('--dulce-rojo-hover');
        root.style.removeProperty('--dulce-acento');
        root.style.removeProperty('--dulce-texto-suave');
        root.style.removeProperty('--dulce-rosa-suave');
        root.style.removeProperty('--bs-primary');
    }
});
const anuncios        = computed(() => page.props.anuncios ?? []);
const csrf            = computed(() => page.props.csrf);

const anunciosDismissed = ref(new Set());
const dismissAnuncio = (id) => anunciosDismissed.value.add(id);
const anunciosVisibles = computed(() =>
    anuncios.value.filter(a => !anunciosDismissed.value.has(a.id))
);
</script>

<template>
    <div class="tenant-shell d-flex flex-column" :class="{ 'pos-premium p-shell': premium }"
        :style="premiumTheme" style="min-height: 100dvh;">
        <a v-if="premium" href="#premium-main" class="p-skip">Saltar al contenido</a>

        <!-- ── Banner de impersonación ── -->
        <div v-if="impersonando" class="impersonacion-banner">
            <span><UiIcon v-if="premium" name="shield" /><template v-else>🛡️</template> Estás viendo el sistema como administrador de esta dulcería</span>
            <form method="POST" action="/superadmin/salir-impersonacion" class="d-inline">
                <input type="hidden" name="_token" :value="csrf">
                <button type="submit" class="impersonacion-salir">Salir de impersonación →</button>
            </form>
        </div>

        <!-- ── Anuncios de plataforma ── -->
        <div
            v-for="a in anunciosVisibles"
            :key="a.id"
            class="anuncio-banner"
            :class="`anuncio-banner--${a.tipo}`"
        >
            <UiIcon v-if="premium" :name="a.tipo === 'alerta' ? 'warning' : a.tipo === 'aviso' ? 'warning' : 'info'" />
            <span v-else class="anuncio-icon">{{ a.tipo === 'alerta' ? '🚨' : a.tipo === 'aviso' ? '⚠️' : 'ℹ️' }}</span>
            <div class="flex-grow-1">
                <strong>{{ a.titulo }}</strong>
                <span class="anuncio-cuerpo"> — {{ a.cuerpo }}</span>
            </div>
            <button class="anuncio-close" :aria-label="premium ? 'Cerrar anuncio' : undefined" @click="dismissAnuncio(a.id)"><UiIcon v-if="premium" name="close" /><template v-else>✕</template></button>
        </div>

        <div class="d-flex flex-grow-1" style="min-height: 0;">
            <!-- Sidebar escritorio -->
            <aside class="pos-premium p-sidebar sidebar d-none d-lg-flex flex-column flex-shrink-0" :style="premiumTheme">
                <SidebarNav :navigation="navigation" />
            </aside>

            <!-- One accessible drawer for every tenant module. -->
            <UiOverlay :id="drawerId" :open="drawer" title="Navegación" variant="drawer"
                :theme="premiumTheme" focus-fallback="[data-tenant-menu-trigger], .p-sidebar a.is-active" @close="drawer = false">
                <SidebarNav :navigation="navigation" />
            </UiOverlay>

            <!-- Contenido -->
            <div class="flex-grow-1 d-flex flex-column" :class="{ 'p-workspace': premium }" style="min-width: 0;">
                <header class="pos-premium p-header" :style="premiumTheme">
                    <div class="p-header__inner">
                        <button type="button" class="p-icon-button d-none d-md-inline-grid d-lg-none" data-tenant-menu-trigger aria-label="Abrir menú" :aria-expanded="drawer"
                            :aria-controls="drawerId" aria-haspopup="dialog" @click="drawer = true"><UiIcon name="menu" :size="20" /></button>
                        <div class="p-header__breadcrumb"><span>{{ brandName }}</span><span aria-hidden="true">/</span><strong>{{ navigation.sectionLabel }}</strong></div>
                        <AccountMenu />
                    </div>
                </header>
                <main class="flex-grow-1 p-3 p-lg-4" :class="{ 'p-main': premium }" :id="premium ? 'premium-main' : undefined" :tabindex="premium ? -1 : undefined">
                    <slot :theme="premium ? premiumTheme : undefined" />
                </main>
            </div>
        </div>

        <UiBottomNav :style="premiumTheme" :items="navigation.bottom" :drawer-open="drawer" :drawer-id="drawerId"
            :suppressed="suppressed" @open-menu="drawer = true" />

        <!-- Toasts -->
        <div class="toast-container-pos d-flex flex-column gap-2">
            <div v-for="toast in toasts" :key="toast.id" class="toast show border-0 shadow" role="alert">
                <div class="toast-body d-flex align-items-center gap-2 fw-medium rounded"
                    :class="toast.tipo === 'success' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'">
                    <UiIcon v-if="premium" :name="toast.tipo === 'success' ? 'success' : 'warning'" />
                    <span v-else>{{ toast.tipo === 'success' ? '✅' : '❌' }}</span>
                    <span>{{ toast.mensaje }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Banner impersonación */
.impersonacion-banner {
    background: #7c3aed;
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    padding: .5rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    position: sticky;
    top: 0;
    z-index: 200;
}
.impersonacion-salir {
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.35);
    color: #fff;
    border-radius: 6px;
    padding: .25rem .75rem;
    font-size: .75rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
}
.impersonacion-salir:hover { background: rgba(255,255,255,.35); }

/* Anuncios */
.anuncio-banner {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .6rem 1.25rem;
    font-size: .8rem;
    border-bottom: 1px solid rgba(0,0,0,.07);
}
.anuncio-banner--info   { background: #eff6ff; color: #1e3a5f; }
.anuncio-banner--aviso  { background: #fffbeb; color: #78350f; }
.anuncio-banner--alerta { background: #fef2f2; color: #7f1d1d; }
.anuncio-icon { font-size: .9rem; flex-shrink: 0; margin-top: .05rem; }
.anuncio-cuerpo { color: inherit; opacity: .8; }
.anuncio-close {
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: .5;
    font-size: .8rem;
    padding: 0;
    flex-shrink: 0;
    line-height: 1;
    margin-top: .1rem;
}
.anuncio-close:hover { opacity: 1; }
</style>
