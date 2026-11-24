<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UsuarioForm from '../../Components/UsuarioForm.vue';
import AdminEditorFrame from '../../Components/Admin/AdminEditorFrame.vue';

const form = useForm({
    nombre: '',
    apellido: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    rol: 'cajero',
});

const enviar = () => form.post('/usuarios');
</script>

<template>
    <AppLayout premium>
        <Head title="Nuevo usuario" />
        <AdminEditorFrame title="Nuevo usuario" description="Completa los datos de la cuenta y asigna su rol." back-href="/usuarios"><UsuarioForm :form="form" texto-boton="Crear usuario" @enviar="enviar" /></AdminEditorFrame>
    </AppLayout>
</template>
