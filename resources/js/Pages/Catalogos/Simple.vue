<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FinanceWorkspace from '../../Components/Finanzas/FinanceWorkspace.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiEmptyState from '../../Components/Ui/UiEmptyState.vue';
import UiFormField from '../../Components/Ui/UiFormField.vue';
import AdminActions from '../../Components/Admin/AdminActions.vue';
import AdminStatus from '../../Components/Admin/AdminStatus.vue';
import AdminDialog from '../../Components/Admin/AdminDialog.vue';

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
    <AppLayout premium v-slot="{ theme }">
        <Head :title="titulo" />
        <FinanceWorkspace>
            <div class="catalog-simple">
                <div class="p-page-heading"><div><h1 class="p-page-title">{{ titulo }}</h1><p class="finance-description">Organiza las opciones disponibles para clasificar tus productos.</p></div><UiButton @click="abrirCrear"><UiIcon name="plus" />Nueva {{ singular }}</UiButton></div>
                <section class="p-panel" aria-labelledby="catalog-simple-title">
                    <div class="p-panel__heading"><div><h2 id="catalog-simple-title" class="p-panel__title">{{ titulo }} registradas</h2><p class="p-panel__description">Nombre, estado y acciones disponibles</p></div><UiIcon :name="base.endsWith('/marcas') ? 'store' : 'tags'" class="finance-muted" /></div>
                    <template v-if="items.length">
                        <table class="finance-table catalog-table"><caption class="finance-table-caption">{{ titulo }} y sus acciones de administración</caption><thead><tr><th scope="col">Nombre</th><th scope="col">Estado</th><th scope="col" class="finance-number">Acciones</th></tr></thead><tbody>
                            <tr v-for="item in items" :key="item.id"><td class="catalog-name">{{ item.nombre }}</td><td><AdminStatus :active="item.activo" active-label="Activa" inactive-label="Inactiva" /></td><td><AdminActions :name="item.nombre" :active="item.activo" @edit="abrirEditar(item)" @toggle="toggleActivo(item)" /></td></tr>
                        </tbody></table>
                        <div class="finance-cards catalog-cards"><article v-for="item in items" :key="item.id" class="finance-record"><div class="finance-record__heading"><h3 class="finance-record__title">{{ item.nombre }}</h3><AdminStatus :active="item.activo" active-label="Activa" inactive-label="Inactiva" /></div><AdminActions :name="item.nombre" :active="item.activo" labels @edit="abrirEditar(item)" @toggle="toggleActivo(item)" /></article></div>
                    </template>
                    <UiEmptyState v-else :icon="base.endsWith('/marcas') ? 'store' : 'tags'" title="Sin registros todavía" :description="`Crea una ${singular} para organizar tus productos.`" />
                </section>
            </div>
        </FinanceWorkspace>
        <AdminDialog :open="modalAbierto" :title="editando ? `Editar ${singular}` : `Nueva ${singular}`" :theme="theme" @close="modalAbierto = false">
            <form @submit.prevent="enviar" class="admin-dialog-form">
                <UiFormField etiqueta="Nombre" :error="form.errors.nombre" requerido><template #default="{ control }"><input v-bind="control" v-model="form.nombre" type="text" name="nombre" autocomplete="off" maxlength="150" class="p-field-control" required autofocus></template></UiFormField>
                <div class="admin-dialog-actions"><UiButton variant="secondary" @click="modalAbierto = false">Cancelar</UiButton><UiButton type="submit" :loading="form.processing">{{ form.processing ? 'Guardando…' : (editando ? 'Guardar' : 'Crear') }}</UiButton></div>
            </form>
        </AdminDialog>
    </AppLayout>
</template>

<style scoped>
.catalog-simple { max-width: 1040px; margin-inline: auto; }
.catalog-name { font-weight: 500; }
.catalog-table :deep(td:first-child) { width: 65%; }
.catalog-table :deep(td:last-child) { width: 140px; }
.catalog-cards { padding: 16px; }
</style>
