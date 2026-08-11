<script setup>
import { computed, ref, watchEffect } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SidebarNav from '../Components/SidebarNav.vue';

const page = usePage();

const drawer = ref(false);
router.on('navigate', () => { drawer.value = false; });

const toasts = ref([]);
let toastId = 0;
const empujarToast = (tipo, mensaje) => {
    const id = ++toastId;
    toasts.value.push({ id, tipo, mensaje });
    setTimeout(() => { toasts.value = toasts.value.filter((t) => t.id !== id); }, 4500);
};

const procesarFlash = () => {
    const flash = page.props.flash;
    if (flash?.success) empujarToast('success', flash.success);
    if (flash?.error)   empujarToast('error',   flash.error);
};

// Usar el evento de Inertia para detectar flash después de cada navegación,
// incluyendo cuando el mensaje es igual al anterior (el watch no lo detectaría).
router.on('navigate', procesarFlash);

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
    <div class="d-flex flex-column" style="min-height: 100dvh;">

        <!-- ── Banner de impersonación ── -->
        <div v-if="impersonando" class="impersonacion-banner">
            <span>🛡️ Estás viendo el sistema como administrador de esta dulcería</span>
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
            <span class="anuncio-icon">{{ a.tipo === 'alerta' ? '🚨' : a.tipo === 'aviso' ? '⚠️' : 'ℹ️' }}</span>
            <div class="flex-grow-1">
                <strong>{{ a.titulo }}</strong>
                <span class="anuncio-cuerpo"> — {{ a.cuerpo }}</span>
            </div>
            <button class="anuncio-close" @click="dismissAnuncio(a.id)">✕</button>
        </div>

        <div class="d-flex flex-grow-1" style="min-height: 0;">
            <!-- Sidebar escritorio -->
            <aside class="sidebar d-none d-lg-flex flex-column flex-shrink-0">
                <SidebarNav />
            </aside>

            <!-- Drawer móvil -->
            <Teleport to="body">
                <div v-if="drawer" class="d-lg-none">
                    <div
                        class="position-fixed top-0 start-0 bottom-0 sidebar d-flex flex-column shadow-lg"
                        style="z-index: 1045; width: 270px; position: fixed !important; height: 100dvh;"
                    >
                        <SidebarNav />
                    </div>
                    <div
                        class="position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-50"
                        style="z-index: 1040;"
                        @click="drawer = false"
                    />
                </div>
            </Teleport>

            <!-- Contenido -->
            <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
                <header class="topbar-movil d-lg-none d-flex align-items-center gap-3 px-3 py-2 sticky-top shadow-sm">
                    <button type="button" class="btn btn-link text-white fs-3 p-0 text-decoration-none" aria-label="Abrir menú" @click="drawer = true">☰</button>
                    <h1 class="titulo-marca fs-5 mb-0">🍬 Dulcería POS</h1>
                </header>
                <main class="flex-grow-1 p-3 p-lg-4">
                    <slot />
                </main>
            </div>
        </div>

        <!-- Toasts -->
        <div class="toast-container-pos d-flex flex-column gap-2">
            <div v-for="toast in toasts" :key="toast.id" class="toast show border-0 shadow" role="alert">
                <div class="toast-body d-flex align-items-center gap-2 fw-medium rounded"
                    :class="toast.tipo === 'success' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'">
                    <span>{{ toast.tipo === 'success' ? '✅' : '❌' }}</span>
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
