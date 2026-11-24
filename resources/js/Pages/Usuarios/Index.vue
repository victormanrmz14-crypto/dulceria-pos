<script setup>
import { computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import AdminActions from '../../Components/Admin/AdminActions.vue';
import AdminStatus from '../../Components/Admin/AdminStatus.vue';
import UserRoleBadge from '../../Components/Admin/UserRoleBadge.vue';

defineProps({
    usuarios: Array,
});

const page = usePage();
const miId = computed(() => page.props.auth.user?.id);

const toggleActivo = (usuario) => {
    const accion = usuario.activo ? 'desactivar' : 'activar';
    if (!confirm(`¿Seguro que quieres ${accion} a ${usuario.nombre}?`)) return;
    router.delete(`/usuarios/${usuario.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout premium>
        <Head title="Usuarios" />
        <FinanceWorkspace>
            <div class="p-page-heading"><div><h1 class="p-page-title">Usuarios</h1><p class="finance-description">Administra las cuentas, los roles y el acceso de tu equipo.</p></div><UiButton href="/usuarios/create"><UiIcon name="plus" />Nuevo usuario</UiButton></div>
            <section class="p-panel" aria-labelledby="users-title">
                <div class="p-panel__heading"><div><h2 id="users-title" class="p-panel__title">Equipo</h2><p class="p-panel__description">Datos de acceso y estado de cada cuenta</p></div><UiIcon name="users" class="finance-muted" /></div>
                <template v-if="usuarios.length">
                    <table class="finance-table users-table">
                        <caption class="finance-table-caption">Usuarios, datos de acceso, rol, estado y acciones disponibles</caption>
                        <thead><tr><th scope="col">Nombre</th><th scope="col">Usuario</th><th scope="col">Correo electrónico</th><th scope="col">Rol</th><th scope="col">Estado</th><th scope="col" class="finance-number">Acciones</th></tr></thead>
                        <tbody><tr v-for="u in usuarios" :key="u.id">
                            <td><span class="user-name">{{ u.nombre }} {{ u.apellido }}</span><span v-if="u.id === miId" class="user-self">Tú</span></td><td>{{ u.username }}</td><td>{{ u.email }}</td><td><UserRoleBadge :role="u.rol" /></td><td><AdminStatus :active="u.activo" /></td>
                            <td><AdminActions :name="u.nombre" :active="u.activo" :edit-href="`/usuarios/${u.id}/edit`" :hide-toggle="u.id === miId" @toggle="toggleActivo(u)" /></td>
                        </tr></tbody>
                    </table>
                    <div class="finance-cards users-cards">
                        <article v-for="u in usuarios" :key="u.id" class="finance-record">
                            <div class="finance-record__heading"><div><h3 class="finance-record__title">{{ u.nombre }} {{ u.apellido }} <span v-if="u.id === miId" class="user-self">Tú</span></h3><p class="finance-record__meta">{{ u.username }}</p></div><AdminStatus :active="u.activo" /></div>
                            <p class="user-email">{{ u.email }}</p><UserRoleBadge :role="u.rol" />
                            <AdminActions class="user-card-actions" :name="u.nombre" :active="u.activo" :edit-href="`/usuarios/${u.id}/edit`" :hide-toggle="u.id === miId" labels @toggle="toggleActivo(u)" />
                        </article>
                    </div>
                </template>
                <UiEmptyState v-else icon="users" title="Sin usuarios para mostrar" description="Las cuentas de tu equipo aparecerán aquí con su rol y estado." />
            </section>
        </FinanceWorkspace>
    </AppLayout>
</template>

<style scoped>
.user-name { font-weight: 500; }
.user-self { display: inline-flex; padding: 2px 6px; margin-left: 6px; border-radius: 4px; background: var(--p-muted-surface); color: var(--p-muted); font-size: 12px; font-weight: 500; vertical-align: middle; }
.users-table :deep(td) { max-width: 240px; }
.users-table :deep(td:last-child) { width: 140px; }
.users-cards { padding: 16px; }
.user-email { font-size: 13px; line-height: 1.6; margin: 0 0 12px; overflow-wrap: anywhere; }
.user-card-actions { margin-top: 16px; }
</style>
