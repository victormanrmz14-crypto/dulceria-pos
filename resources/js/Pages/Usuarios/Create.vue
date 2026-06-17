<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import UsuarioForm from '../../Components/UsuarioForm.vue';

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
    <AppLayout>
        <Head title="Nuevo usuario" />

        <div class="mx-auto" style="max-width: 640px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="text-primary fs-3 mb-0">👥 Nuevo usuario</h2>
                <Link href="/usuarios" class="btn btn-light btn-sm fw-semibold">← Volver</Link>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <UsuarioForm :form="form" texto-boton="Crear usuario" @enviar="enviar" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
