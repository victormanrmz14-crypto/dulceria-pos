<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';

const page = usePage();

// Formulario de reset por email
const emailBusqueda = ref('');
const usuarioEncontrado = ref(null);
const buscando = ref(false);

const buscarUsuario = async () => {
    if (!emailBusqueda.value.trim()) return;
    buscando.value = true;
    try {
        const res = await fetch(`/superadmin/usuarios?email=${encodeURIComponent(emailBusqueda.value)}`, {
            headers: { 'X-Inertia': '1', 'Accept': 'application/json' },
        });
        // Usamos la lista de usuarios del prop directamente en el componente Usuarios
        usuarioEncontrado.value = { email: emailBusqueda.value, encontrado: true };
    } finally {
        buscando.value = false;
    }
};

const resetForm = useForm({ password: '', password_confirmation: '', usuario_id: null });
const bienvenidaForm = useForm({ usuario_id: null });

const csrf = page.props.csrf;
</script>

<template>
    <SuperAdminLayout>
        <Head title="Soporte · Admin Panel" />

        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Soporte</h1>
                <p class="page-sub">Herramientas para resolver problemas de dulcerías</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Resetear contraseña -->
            <div class="col-12 col-lg-6">
                <div class="panel h-100">
                    <div class="panel-header">
                        <span class="panel-title">🔑 Resetear Contraseña de Usuario</span>
                    </div>
                    <div class="panel-body">
                        <p class="tool-desc">Cambia la contraseña de cualquier usuario de la plataforma. Busca al usuario en la página <strong>Usuarios</strong> y usa el botón "Resetear contraseña" en su fila.</p>
                        <div class="tip-box">
                            <span class="tip-icon">💡</span>
                            <div>
                                <strong>¿Cómo hacerlo?</strong><br>
                                Ve a <strong>Gestión → Usuarios</strong>, busca al usuario por nombre o email y haz clic en <em>"Resetear contraseña"</em> en su fila. Se desplegará un formulario inline.
                            </div>
                        </div>
                        <a href="/superadmin/usuarios" class="action-btn mt-3 d-inline-block">Ir a Usuarios →</a>
                    </div>
                </div>
            </div>

            <!-- Reenviar bienvenida -->
            <div class="col-12 col-lg-6">
                <div class="panel h-100">
                    <div class="panel-header">
                        <span class="panel-title">📧 Reenviar Credenciales</span>
                    </div>
                    <div class="panel-body">
                        <p class="tool-desc">Genera una nueva contraseña aleatoria y envía las credenciales al email del usuario. También disponible en la página de Usuarios.</p>
                        <div class="tip-box tip-box--warn">
                            <span class="tip-icon">⚠️</span>
                            <div>
                                <strong>Requiere mail configurado.</strong><br>
                                Si el servidor de mail no está configurado, la contraseña nueva se muestra en el mensaje de respuesta para que la comuniques manualmente.
                            </div>
                        </div>
                        <a href="/superadmin/usuarios" class="action-btn mt-3 d-inline-block">Ir a Usuarios →</a>
                    </div>
                </div>
            </div>

            <!-- Impersonación -->
            <div class="col-12 col-lg-6">
                <div class="panel h-100">
                    <div class="panel-header">
                        <span class="panel-title">👁️ Impersonación de Dulcería</span>
                    </div>
                    <div class="panel-body">
                        <p class="tool-desc">Accede al sistema como si fueras el administrador de una dulcería específica para reproducir y diagnosticar problemas desde su perspectiva.</p>
                        <div class="tip-box">
                            <span class="tip-icon">💡</span>
                            <div>
                                Ve a <strong>Gestión → Dulcerías</strong>, abre el detalle de la dulcería y usa el botón <em>"Impersonar admin"</em>. Un banner morado te avisará que estás en modo impersonación. Al terminar, haz clic en <em>"Salir de impersonación"</em>.
                            </div>
                        </div>
                        <a href="/superadmin/dulcerias" class="action-btn mt-3 d-inline-block">Ir a Dulcerías →</a>
                    </div>
                </div>
            </div>

            <!-- Activar/Desactivar -->
            <div class="col-12 col-lg-6">
                <div class="panel h-100">
                    <div class="panel-header">
                        <span class="panel-title">🔒 Activar / Desactivar Dulcería</span>
                    </div>
                    <div class="panel-body">
                        <p class="tool-desc">Bloquea el acceso de todos los usuarios de una dulcería sin eliminar sus datos. Útil para cuentas con problemas de pago o abuso.</p>
                        <div class="tip-box tip-box--err">
                            <span class="tip-icon">🚨</span>
                            <div>
                                <strong>Acción inmediata.</strong><br>
                                Al desactivar, todos los usuarios de esa dulcería quedan bloqueados en su próxima petición. La acción queda registrada en Auditoría.
                            </div>
                        </div>
                        <a href="/superadmin/dulcerias" class="action-btn mt-3 d-inline-block">Ir a Dulcerías →</a>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
.page-sub    { font-size: .78rem; color: #64748b; margin: .2rem 0 0; }

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.panel-title  { font-size: .88rem; font-weight: 700; color: #1e293b; }
.panel-body   { padding: 1.25rem; }

.tool-desc { font-size: .83rem; color: #475569; line-height: 1.6; margin-bottom: 1rem; }

.tip-box {
    display: flex;
    gap: .75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    padding: .85rem 1rem;
    font-size: .78rem;
    color: #475569;
    line-height: 1.55;
}
.tip-box--warn { background: #fffbeb; border-color: #fde68a; color: #78350f; }
.tip-box--err  { background: #fff5f5; border-color: #fecaca; color: #7f1d1d; }
.tip-icon { font-size: 1rem; flex-shrink: 0; margin-top: .05rem; }

.action-btn {
    display: inline-flex;
    align-items: center;
    background: #3b82f6;
    color: #fff;
    text-decoration: none;
    padding: .5rem 1.1rem;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    transition: background .15s;
}
.action-btn:hover { background: #2563eb; color: #fff; }
</style>
