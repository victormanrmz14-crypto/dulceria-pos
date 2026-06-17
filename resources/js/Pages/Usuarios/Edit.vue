<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UsuarioForm from '../../Components/UsuarioForm.vue';

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
    <AppLayout>
        <Head :title="`Editar ${usuario.nombre}`" />

        <div class="mx-auto" style="max-width: 640px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="text-primary fs-3 mb-0">✏️ Editar usuario</h2>
                <Link href="/usuarios" class="btn btn-light btn-sm fw-semibold">← Volver</Link>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <UsuarioForm :form="form" es-edicion texto-boton="Guardar cambios" @enviar="enviar" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
