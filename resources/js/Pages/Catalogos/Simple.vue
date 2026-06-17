<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import ModalPos from '../../Components/ModalPos.vue';
import CampoForm from '../../Components/CampoForm.vue';

// Página genérica para catálogos de un solo campo (Categorías y Marcas)
const props = defineProps({
    titulo: String,
    singular: String,
    icono: String,
    base: String,     // ej. /catalogos/categorias
    items: Array,     // [{ id, nombre, activo }]
});

const modalAbierto = ref(false);
const editando = ref(null); // item en edición o null para crear

const form = useForm({ nombre: '' });

const abrirCrear = () => {
    editando.value = null;
    form.reset();
    form.clearErrors();
    modalAbierto.value = true;
};

const abrirEditar = (item) => {
    editando.value = item;
    form.nombre = item.nombre;
    form.clearErrors();
    modalAbierto.value = true;
};

const enviar = () => {
    const opciones = {
        onSuccess: () => { modalAbierto.value = false; form.reset(); },
        preserveScroll: true,
    };
    if (editando.value) {
        form.put(`${props.base}/${editando.value.id}`, opciones);
    } else {
        form.post(props.base, opciones);
    }
};

const toggleActivo = (item) => {
    router.delete(`${props.base}/${item.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head :title="titulo" />

        <div class="mx-auto" style="max-width: 720px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="text-primary fs-3 mb-0">{{ icono }} {{ titulo }}</h2>
                <button type="button" class="btn btn-primary fw-semibold" @click="abrirCrear">
                    + Nueva {{ singular }}
                </button>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id" :class="{ 'opacity-50': !item.activo }">
                                <td class="fw-semibold small">{{ item.nombre }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill"
                                          :class="item.activo ? 'text-bg-success' : 'text-bg-secondary'">
                                        {{ item.activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-light" title="Editar"
                                                @click="abrirEditar(item)">✏️</button>
                                        <button type="button" class="btn btn-light"
                                                :title="item.activo ? 'Desactivar' : 'Activar'"
                                                @click="toggleActivo(item)">
                                            {{ item.activo ? '🚫' : '✅' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="3" class="text-center text-secondary py-5">
                                    Sin registros todavía.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal crear / editar -->
        <ModalPos
            :abierto="modalAbierto"
            :titulo="editando ? `✏️ Editar ${singular}` : `${icono} Nueva ${singular}`"
            @cerrar="modalAbierto = false"
        >
            <form @submit.prevent="enviar" class="d-flex flex-column gap-3">
                <CampoForm etiqueta="Nombre" :error="form.errors.nombre" requerido>
                    <template #default="{ clase }">
                        <input v-model="form.nombre" type="text" maxlength="150"
                               class="form-control" :class="clase" required autofocus>
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
