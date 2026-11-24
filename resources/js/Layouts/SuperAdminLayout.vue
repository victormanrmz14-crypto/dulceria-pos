<script setup>
import { computed, onBeforeUnmount, shallowRef, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import SaNavigation from "../Components/SuperAdmin/SaNavigation.vue";
import UiOverlay from "../Components/Ui/UiOverlay.vue";
import UiIcon from "../Components/Ui/UiIcon.vue";
import "../../css/premium.css";
import "../../css/superadmin.css";
const page = usePage();
const drawer = shallowRef(false);
const dismissed = shallowRef(false);
const user = computed(() => page.props.auth?.user);
const titles = {
    dashboard: "Vista general",
    dulcerias: "Dulcerías",
    usuarios: "Usuarios",
    saas: "Planes",
    auditoria: "Auditoría",
    comunicacion: "Comunicación",
    soporte: "Soporte",
    monitoreo: "Monitoreo",
};
const title = computed(
    () => titles[page.url.split("/")[2]?.split("?")[0]] || "Plataforma",
);
const stopNavigate = router.on("navigate", () => {
    drawer.value = false;
    dismissed.value = false;
});
watch(
    () => page.props.flash,
    () => {
        dismissed.value = false;
    },
);
onBeforeUnmount(stopNavigate);
</script>
<template>
    <div class="platform-brand pos-premium sa-shell">
        <a class="sa-skip" href="#sa-main">Saltar al contenido</a>
        <aside class="sa-sidebar">
            <SaNavigation
                :url="page.url"
                :csrf="page.props.csrf"
                :nombre="user?.nombre || ''"
            />
        </aside>
        <div class="sa-workspace">
            <header class="sa-topbar">
                <button
                    class="sa-menu sa-button"
                    type="button"
                    aria-label="Abrir navegación"
                    :aria-expanded="drawer"
                    @click="drawer = true"
                >
                    <UiIcon name="menu" />
                </button>
                <nav aria-label="Ruta de navegación">
                    <span class="sa-breadcrumb-platform">
                        Dulcería POS
                        <span aria-hidden="true">/</span>
                    </span>
                    <span aria-current="page">{{ title }}</span>
                </nav>
                <span class="sa-account">
                    <UiIcon name="shield" :size="16" />
                    <span>Plataforma</span>
                </span>
            </header>
            <main id="sa-main" tabindex="-1" class="sa-main">
                <div
                    v-if="
                        !dismissed &&
                        (page.props.flash?.success || page.props.flash?.error)
                    "
                    :role="page.props.flash.error ? 'alert' : 'status'"
                    class="sa-notice"
                    :class="{ 'sa-notice--danger': page.props.flash.error }"
                >
                    <UiIcon
                        :name="page.props.flash.error ? 'warning' : 'success'"
                    />
                    <span>
                        {{ page.props.flash.error || page.props.flash.success }}
                    </span>
                    <button
                        class="sa-button"
                        aria-label="Cerrar mensaje"
                        @click="dismissed = true"
                    >
                        <UiIcon name="close" />
                    </button>
                </div>
                <slot />
            </main>
        </div>
        <UiOverlay
            :open="drawer"
            title="Navegación"
            variant="drawer"
            class="sa-overlay"
            @close="drawer = false"
        >
            <SaNavigation
                :url="page.url"
                :csrf="page.props.csrf"
                :nombre="user?.nombre || ''"
            />
        </UiOverlay>
    </div>
</template>
