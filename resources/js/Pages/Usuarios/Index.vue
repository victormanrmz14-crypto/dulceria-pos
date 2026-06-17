<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

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
    <AppLayout>
        <Head title="Usuarios" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="text-primary fs-3 mb-0">👥 Usuarios</h2>
            <Link href="/usuarios/create" class="btn btn-primary fw-semibold">+ Nuevo usuario</Link>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in usuarios" :key="u.id" :class="{ 'opacity-50': !u.activo }">
                            <td class="fw-semibold small">
                                {{ u.nombre }} {{ u.apellido }}
                                <span v-if="u.id === miId" class="badge text-bg-light ms-1">tú</span>
                            </td>
                            <td class="small">{{ u.username }}</td>
                            <td class="small">{{ u.email }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill" :class="u.rol === 'admin' ? 'text-bg-primary' : 'text-bg-info'">
                                    {{ u.rol === 'admin' ? '👑 Admin' : '🧾 Cajero' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" :class="u.activo ? 'text-bg-success' : 'text-bg-secondary'">
                                    {{ u.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <Link :href="`/usuarios/${u.id}/edit`" class="btn btn-light" title="Editar">✏️</Link>
                                    <button v-if="u.id !== miId" type="button" class="btn btn-light"
                                            :title="u.activo ? 'Desactivar' : 'Activar'"
                                            @click="toggleActivo(u)">
                                        {{ u.activo ? '🚫' : '✅' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
