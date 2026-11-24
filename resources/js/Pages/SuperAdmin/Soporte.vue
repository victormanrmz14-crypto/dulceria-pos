<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import UiIcon from "../../Components/Ui/UiIcon.vue";
const tools = [
    {
        title: "Restablecer contraseña",
        icon: "shield",
        description: "Define una nueva contraseña desde la cuenta del usuario.",
        note: "Selecciona al usuario en el listado global y abre Restablecer contraseña.",
        href: "/superadmin/usuarios",
        action: "Buscar usuario",
    },
    {
        title: "Reenviar bienvenida",
        icon: "users",
        description:
            "Genera una nueva contraseña y envía las credenciales al correo del usuario.",
        note: "Requiere correo configurado y confirmación. Reemplaza la contraseña actual.",
        href: "/superadmin/usuarios",
        action: "Ir a usuarios",
    },
    {
        title: "Impersonar administrador",
        icon: "store",
        description:
            "Abre la sesión del administrador de una dulcería para reproducir una incidencia.",
        note: "Disponible desde el detalle del negocio. Usa Salir de impersonación al terminar.",
        href: "/superadmin/dulcerias",
        action: "Buscar dulcería",
    },
    {
        title: "Habilitar o deshabilitar dulcería",
        icon: "settings",
        description: "Administra el acceso del negocio conservando sus datos.",
        note: "La acción del detalle solicita confirmación y queda en el registro administrativo.",
        href: "/superadmin/dulcerias",
        action: "Ir a dulcerías",
    },
];
</script>
<template>
    <SuperAdminLayout>
        <Head title="Soporte · Superadministración" />
        <SaHeading
            title="Soporte"
            description="Herramientas administrativas existentes para atender a los negocios."
        />
        <div class="sa-two">
            <section v-for="tool in tools" :key="tool.title" class="sa-panel">
                <header>
                    <h2 class="sa-form-actions">
                        <UiIcon :name="tool.icon" />
                        {{ tool.title }}
                    </h2>
                </header>
                <div class="sa-panel-body">
                    <p>{{ tool.description }}</p>
                    <p class="sa-note">{{ tool.note }}</p>
                    <Link :href="tool.href" class="sa-button">
                        {{ tool.action }}
                    </Link>
                </div>
            </section>
        </div>
    </SuperAdminLayout>
</template>
