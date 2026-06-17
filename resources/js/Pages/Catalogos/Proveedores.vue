<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ModalPos from '../../Components/ModalPos.vue';
import CampoForm from '../../Components/CampoForm.vue';

defineProps({
    proveedores: Array,
});

const modalAbierto = ref(false);
const editando = ref(null);

const form = useForm({ nombre: '', email: '', telefono: '', notas: '' });

const abrirCrear = () => {
    editando.value = null;
    form.reset();
    form.clearErrors();
    modalAbierto.value = true;
};

const abrirEditar = (p) => {
    editando.value = p;
    form.nombre = p.nombre;
    form.email = p.email;
    form.telefono = p.telefono ?? '';
    form.notas = p.notas ?? '';
    form.clearErrors();
    modalAbierto.value = true;
};

const enviar = () => {
    const opciones = {
        onSuccess: () => { modalAbierto.value = false; form.reset(); },
        preserveScroll: true,
    };
    if (editando.value) {
        form.put(`/catalogos/proveedores/${editando.value.id}`, opciones);
    } else {
        form.post('/catalogos/proveedores', opciones);
    }
};

const toggleActivo = (p) => {
    router.delete(`/catalogos/proveedores/${p.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head title="Proveedores" />

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="text-primary fs-3 mb-0">🚚 Proveedores</h2>
            <button type="button" class="btn btn-primary fw-semibold" @click="abrirCrear">
                + Nuevo proveedor
            </button>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Contacto</th>
                            <th class="text-center">Productos</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in proveedores" :key="p.id" :class="{ 'opacity-50': !p.activo }">
                            <td>
                                <div class="fw-semibold small">{{ p.nombre }}</div>
                                <div v-if="p.notas" class="text-secondary" style="font-size: 0.75rem;">{{ p.notas }}</div>
                            </td>
                            <td>
                                <div class="small">📧 {{ p.email }}</div>
                                <div v-if="p.telefono" class="small text-secondary">📞 {{ p.telefono }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-light">{{ p.productos_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" :class="p.activo ? 'text-bg-success' : 'text-bg-secondary'">
                                    {{ p.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-light" title="Editar" @click="abrirEditar(p)">✏️</button>
                                    <button type="button" class="btn btn-light"
                                            :title="p.activo ? 'Desactivar' : 'Activar'"
                                            @click="toggleActivo(p)">
                                        {{ p.activo ? '🚫' : '✅' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!proveedores.length">
                            <td colspan="5" class="text-center text-secondary py-5">Sin proveedores registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal crear / editar -->
        <ModalPos
            :abierto="modalAbierto"
            :titulo="editando ? '✏️ Editar proveedor' : '🚚 Nuevo proveedor'"
            ancho="520px"
            @cerrar="modalAbierto = false"
        >
            <form @submit.prevent="enviar" class="d-flex flex-column gap-3">
                <CampoForm etiqueta="Nombre" :error="form.errors.nombre" requerido>
                    <template #default="{ clase }">
                        <input v-model="form.nombre" type="text" maxlength="150"
                               class="form-control" :class="clase" required>
                    </template>
                </CampoForm>
                <div class="row g-3">
                    <div class="col-12 col-sm-7">
                        <CampoForm etiqueta="Email" :error="form.errors.email" requerido>
                            <template #default="{ clase }">
                                <input v-model="form.email" type="email" maxlength="255"
                                       class="form-control" :class="clase" required>
                            </template>
                        </CampoForm>
                    </div>
                    <div class="col-12 col-sm-5">
                        <CampoForm etiqueta="Teléfono" :error="form.errors.telefono">
                            <template #default="{ clase }">
                                <input v-model="form.telefono" type="tel" maxlength="20"
                                       class="form-control" :class="clase">
                            </template>
                        </CampoForm>
                    </div>
                </div>
                <CampoForm etiqueta="Notas" :error="form.errors.notas">
                    <template #default="{ clase }">
                        <textarea v-model="form.notas" rows="2" maxlength="500"
                                  class="form-control" :class="clase"></textarea>
                    </template>
                </CampoForm>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 fw-semibold"
                                @click="modalAbierto = false">Cancelar</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" :disabled="form.processing">
                            {{ editando ? 'Guardar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </form>
        </ModalPos>
    </AppLayout>
</template>
