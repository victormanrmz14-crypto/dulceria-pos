<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SidebarNav from '../Components/SidebarNav.vue';

const page = usePage();

// ---- Drawer móvil ----
const drawer = ref(false);
router.on('navigate', () => { drawer.value = false; });

// ---- Toasts desde mensajes flash ----
const toasts = ref([]);
let toastId = 0;

const empujarToast = (tipo, mensaje) => {
    const id = ++toastId;
    toasts.value.push({ id, tipo, mensaje });
    setTimeout(() => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }, 4500);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) empujarToast('success', flash.success);
        if (flash?.error) empujarToast('error', flash.error);
    },
    { immediate: true, deep: true },
);

// Errores de validación también como toast (los formularios además los muestran en línea)
const erroresGlobales = computed(() => page.props.errors ?? {});
</script>

<template>
    <div class="d-flex" style="min-height: 100dvh;">
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
            <!-- Topbar móvil -->
            <header class="topbar-movil d-lg-none d-flex align-items-center gap-3 px-3 py-2 sticky-top shadow-sm">
                <button
                    type="button"
                    class="btn btn-link text-white fs-3 p-0 text-decoration-none"
                    aria-label="Abrir menú"
                    @click="drawer = true"
                >☰</button>
                <h1 class="titulo-marca fs-5 mb-0">🍬 Dulcería POS</h1>
            </header>

            <main class="flex-grow-1 p-3 p-lg-4">
                <slot />
            </main>
        </div>

        <!-- Toasts -->
        <div class="toast-container-pos d-flex flex-column gap-2">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="toast show border-0 shadow"
                role="alert"
            >
                <div
                    class="toast-body d-flex align-items-center gap-2 fw-medium rounded"
                    :class="toast.tipo === 'success' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'"
                >
                    <span>{{ toast.tipo === 'success' ? '✅' : '❌' }}</span>
                    <span>{{ toast.mensaje }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
