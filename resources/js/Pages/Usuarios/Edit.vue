<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UsuarioForm from '../../Components/UsuarioForm.vue';
import AdminEditorFrame from '../../Components/Admin/AdminEditorFrame.vue';

const props = defineProps({
    usuario: Object,
});

const form = useForm({
    nombre: props.usuario.nombre,
    apellido: props.usuario.apellido ?? '',
    username: props.usuario.username,
    email: props.usuario.email,
    password: '',
    password_confirmation: '',
    rol: props.usuario.rol,
});

const enviar = () => form.put(`/usuarios/${props.usuario.id}`);
</script>

<template>
    <AppLayout premium>
        <Head :title="`Editar ${usuario.nombre}`" />
        <AdminEditorFrame title="Editar usuario" description="Actualiza los datos de la cuenta y su rol." back-href="/usuarios"><UsuarioForm :form="form" es-edicion texto-boton="Guardar cambios" @enviar="enviar" /></AdminEditorFrame>
    </AppLayout>
</template>
