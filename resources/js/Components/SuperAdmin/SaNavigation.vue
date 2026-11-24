<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import UiIcon from "../Ui/UiIcon.vue";
defineProps<{ url: string; csrf: string; nombre: string }>();
const groups = [
    {
        title: "Plataforma",
        links: [
            ["dashboard", "dashboard", "Vista general"],
            ["monitoreo", "chart", "Monitoreo"],
        ],
    },
    {
        title: "Administración",
        links: [
            ["dulcerias", "store", "Dulcerías"],
            ["usuarios", "users", "Usuarios"],
            ["saas", "card", "Planes"],
        ],
    },
    {
        title: "Operaciones",
        links: [
            ["auditoria", "shield", "Auditoría"],
            ["comunicacion", "info", "Comunicación"],
            ["soporte", "settings", "Soporte"],
        ],
    },
];
</script>
<template>
    <div class="sa-navigation">
        <Link
            href="/superadmin/dashboard"
            class="sa-brand"
            aria-label="Dulcería POS, vista general"
        >
            <img
                src="/brand/logo-compact.svg"
                width="300"
                height="65"
                alt="Dulcería POS"
            />
        </Link>
        <span class="sa-platform-label">
            <UiIcon name="shield" :size="15" />
            Superadministración
        </span>
        <nav aria-label="Superadministración" class="sa-nav">
            <div
                v-for="group in groups"
                :key="group.title"
                class="sa-nav-group"
            >
                <p>{{ group.title }}</p>
                <Link
                    v-for="[path, icon, label] in group.links"
                    :key="path"
                    :href="`/superadmin/${path}`"
                    :aria-current="
                        url.startsWith(`/superadmin/${path}`)
                            ? 'page'
                            : undefined
                    "
                    class="sa-nav-link"
                >
                    <UiIcon :name="icon" />
                    {{ label }}
                </Link>
            </div>
        </nav>
        <footer class="sa-nav-footer">
            <strong>{{ nombre }}</strong>
            <span>Cuenta de plataforma</span>
            <form method="POST" action="/logout">
                <input type="hidden" name="_token" :value="csrf" />
                <button class="sa-logout" type="submit">
                    <UiIcon name="logout" />
                    Cerrar sesión
                </button>
            </form>
        </footer>
    </div>
</template>
