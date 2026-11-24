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
import { Mail, Phone } from '@lucide/vue';

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
    <AppLayout premium v-slot="{ theme }">
        <Head title="Proveedores" />
        <FinanceWorkspace>
            <div class="p-page-heading"><div><h1 class="p-page-title">Proveedores</h1><p class="finance-description">Consulta los contactos y administra tus proveedores.</p></div><UiButton @click="abrirCrear"><UiIcon name="plus" />Nuevo proveedor</UiButton></div>
            <section class="p-panel" aria-labelledby="suppliers-title">
                <div class="p-panel__heading"><div><h2 id="suppliers-title" class="p-panel__title">Directorio de proveedores</h2><p class="p-panel__description">Contacto, productos asociados y estado</p></div><UiIcon name="truck" class="finance-muted" /></div>
                <template v-if="proveedores.length">
                    <table class="finance-table suppliers-table"><caption class="finance-table-caption">Proveedores, datos de contacto, productos, estado y acciones</caption><thead><tr><th scope="col">Proveedor</th><th scope="col">Contacto</th><th scope="col" class="finance-number">Productos</th><th scope="col">Estado</th><th scope="col" class="finance-number">Acciones</th></tr></thead><tbody>
                        <tr v-for="p in proveedores" :key="p.id"><td><span class="supplier-name">{{ p.nombre }}</span><p v-if="p.notas" class="supplier-notes">{{ p.notas }}</p></td><td><p class="supplier-contact"><Mail :size="15" :stroke-width="1.75" aria-hidden="true" /><span>{{ p.email }}</span></p><p v-if="p.telefono" class="supplier-contact"><Phone :size="15" :stroke-width="1.75" aria-hidden="true" /><span>{{ p.telefono }}</span></p></td><td class="finance-number">{{ p.productos_count }}</td><td><AdminStatus :active="p.activo" /></td><td><AdminActions :name="p.nombre" :active="p.activo" @edit="abrirEditar(p)" @toggle="toggleActivo(p)" /></td></tr>
                    </tbody></table>
                    <div class="finance-cards supplier-cards"><article v-for="p in proveedores" :key="p.id" class="finance-record">
                        <div class="finance-record__heading"><h3 class="finance-record__title">{{ p.nombre }}</h3><AdminStatus :active="p.activo" /></div>
                        <p class="supplier-contact"><Mail :size="15" :stroke-width="1.75" aria-hidden="true" /><span>{{ p.email }}</span></p><p v-if="p.telefono" class="supplier-contact"><Phone :size="15" :stroke-width="1.75" aria-hidden="true" /><span>{{ p.telefono }}</span></p>
                        <p v-if="p.notas" class="supplier-notes">{{ p.notas }}</p><dl class="finance-pairs"><div class="finance-pair supplier-products"><dt>Productos asociados</dt><dd>{{ p.productos_count }}</dd></div></dl>
                        <AdminActions class="supplier-card-actions" :name="p.nombre" :active="p.activo" labels @edit="abrirEditar(p)" @toggle="toggleActivo(p)" />
                    </article></div>
                </template>
                <UiEmptyState v-else icon="truck" title="Sin proveedores registrados" description="Añade un proveedor para consultar sus datos de contacto y productos asociados." />
            </section>
        </FinanceWorkspace>
        <AdminDialog :open="modalAbierto" :title="editando ? 'Editar proveedor' : 'Nuevo proveedor'" :theme="theme" wide @close="modalAbierto = false">
            <form @submit.prevent="enviar" class="admin-dialog-form">
                <UiFormField etiqueta="Nombre" :error="form.errors.nombre" requerido><template #default="{ control }"><input v-bind="control" v-model="form.nombre" type="text" name="nombre" autocomplete="off" maxlength="150" class="p-field-control" required></template></UiFormField>
                <div class="admin-dialog-grid">
                    <UiFormField etiqueta="Correo electrónico" :error="form.errors.email" requerido><template #default="{ control }"><input v-bind="control" v-model="form.email" type="email" name="email" autocomplete="off" :spellcheck="false" maxlength="255" class="p-field-control" required></template></UiFormField>
                    <UiFormField etiqueta="Teléfono" :error="form.errors.telefono"><template #default="{ control }"><input v-bind="control" v-model="form.telefono" type="tel" inputmode="tel" name="telefono" autocomplete="off" maxlength="20" class="p-field-control"></template></UiFormField>
                </div>
                <UiFormField etiqueta="Notas" :error="form.errors.notas"><template #default="{ control }"><textarea v-bind="control" v-model="form.notas" name="notas" autocomplete="off" rows="2" maxlength="500" class="p-field-control"></textarea></template></UiFormField>
                <div class="admin-dialog-actions"><UiButton variant="secondary" @click="modalAbierto = false">Cancelar</UiButton><UiButton type="submit" :loading="form.processing">{{ form.processing ? 'Guardando…' : (editando ? 'Guardar' : 'Crear') }}</UiButton></div>
            </form>
        </AdminDialog>
    </AppLayout>
</template>

<style scoped>
.supplier-name { font-weight: 500; }
.suppliers-table :deep(td:first-child) { max-width: 320px; }
.suppliers-table :deep(td:nth-child(2)) { max-width: 260px; }
.suppliers-table :deep(td:last-child) { width: 140px; }
.supplier-contact { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; line-height: 1.6; margin: 0 0 6px; overflow-wrap: anywhere; }
.supplier-contact:last-child { margin-bottom: 0; }
.supplier-contact svg { flex-shrink: 0; margin-top: 3px; color: var(--p-muted); }
.supplier-contact span { min-width: 0; }
.supplier-notes { font-size: 12px; color: var(--p-muted); line-height: 1.7; margin: 8px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; }
.supplier-cards { padding: 16px; }
.supplier-cards :deep(.supplier-products) { padding: 12px 0; margin-top: 8px; border-bottom: 0; }
.supplier-card-actions { margin-top: 8px; }
</style>
