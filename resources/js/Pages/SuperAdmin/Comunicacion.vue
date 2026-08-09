<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '../../Layouts/SuperAdminLayout.vue';

const props = defineProps({
    anuncios: Array,
});

const page   = usePage();
const csrf   = computed(() => page.props.csrf);
const tab    = ref('anuncios');

const anuncioForm = useForm({
    titulo:    '',
    cuerpo:    '',
    tipo:      'info',
    expira_en: '',
});

const emailForm = useForm({
    asunto:  '',
    mensaje: '',
});

const submitAnuncio = () => anuncioForm.post('/superadmin/comunicacion', {
    onSuccess: () => anuncioForm.reset(),
});

const submitEmail = () => emailForm.post('/superadmin/comunicacion/email-masivo', {
    onSuccess: () => emailForm.reset(),
});

const tipoClase = (tipo) => ({
    info:   'badge-info',
    aviso:  'badge-aviso',
    alerta: 'badge-alerta',
}[tipo] ?? 'badge-info');

const tipoIcon = (tipo) => ({ info: 'ℹ️', aviso: '⚠️', alerta: '🚨' }[tipo] ?? 'ℹ️');
</script>

<template>
    <SuperAdminLayout>
        <Head title="Comunicación · Admin Panel" />

        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Comunicación</h1>
                <p class="page-sub">Anuncios de plataforma y mensajes masivos</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs mb-4">
            <button class="tab" :class="{ 'tab--active': tab === 'anuncios' }" @click="tab = 'anuncios'">📢 Anuncios</button>
            <button class="tab" :class="{ 'tab--active': tab === 'email' }"    @click="tab = 'email'">✉️ Email Masivo</button>
        </div>

        <!-- ── Tab Anuncios ── -->
        <template v-if="tab === 'anuncios'">
            <!-- Formulario -->
            <div class="panel mb-4">
                <div class="panel-header">
                    <span class="panel-title">Publicar nuevo anuncio</span>
                </div>
                <form class="panel-body" @submit.prevent="submitAnuncio">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label-sa">Título</label>
                            <input v-model="anuncioForm.titulo" type="text" class="form-input-sa" placeholder="Ej: Mantenimiento programado" required />
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label-sa">Tipo</label>
                            <select v-model="anuncioForm.tipo" class="form-input-sa">
                                <option value="info">ℹ️ Información</option>
                                <option value="aviso">⚠️ Aviso</option>
                                <option value="alerta">🚨 Alerta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-sa">Mensaje</label>
                            <textarea v-model="anuncioForm.cuerpo" class="form-input-sa" rows="3" placeholder="Escribe el mensaje que verán todas las dulcerías..." required></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-sa">Expira en (opcional)</label>
                            <input v-model="anuncioForm.expira_en" type="datetime-local" class="form-input-sa" />
                        </div>
                        <div class="col-12">
                            <button type="submit" class="submit-btn" :disabled="anuncioForm.processing">
                                {{ anuncioForm.processing ? 'Publicando...' : 'Publicar anuncio' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Lista de anuncios -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">Anuncios publicados</span>
                    <span class="panel-sub">{{ anuncios.filter(a => a.vigente).length }} activos</span>
                </div>
                <div v-if="anuncios.length === 0" class="empty-state">Sin anuncios publicados.</div>
                <div v-for="a in anuncios" :key="a.id" class="anuncio-item" :class="!a.vigente ? 'anuncio-item--inactivo' : ''">
                    <div class="anuncio-top">
                        <div class="anuncio-meta">
                            <span class="tipo-badge" :class="tipoClase(a.tipo)">{{ tipoIcon(a.tipo) }} {{ a.tipo }}</span>
                            <span class="anuncio-titulo">{{ a.titulo }}</span>
                        </div>
                        <div class="anuncio-actions">
                            <span v-if="a.vigente" class="vigente-badge">Vigente</span>
                            <span v-else class="inactivo-badge">Inactivo</span>
                            <form method="POST" :action="`/superadmin/comunicacion/${a.id}/toggle`" class="d-inline">
                                <input type="hidden" name="_token" :value="csrf">
                                <button type="submit" class="action-link">{{ a.activo ? 'Desactivar' : 'Activar' }}</button>
                            </form>
                            <form method="POST" :action="`/superadmin/comunicacion/${a.id}`" class="d-inline">
                                <input type="hidden" name="_token" :value="csrf">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="action-link action-link--red" onclick="return confirm('¿Eliminar este anuncio?')">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    <div class="anuncio-cuerpo">{{ a.cuerpo }}</div>
                    <div class="anuncio-footer">
                        Por {{ a.autor }} · {{ a.creado_en }}
                        <span v-if="a.expira_en"> · Expira: {{ a.expira_en }}</span>
                    </div>
                </div>
            </div>
        </template>

        <!-- ── Tab Email Masivo ── -->
        <template v-if="tab === 'email'">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">Enviar email a todos los administradores de dulcerías</span>
                </div>
                <form class="panel-body" @submit.prevent="submitEmail">
                    <div class="tip-box mb-4">
                        <span>⚠️</span>
                        <span>Este mensaje se enviará a <strong>todos los admins activos</strong> de dulcerías. Requiere configuración de mail en el servidor.</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-sa">Asunto</label>
                            <input v-model="emailForm.asunto" type="text" class="form-input-sa" placeholder="Asunto del correo" required />
                        </div>
                        <div class="col-12">
                            <label class="form-label-sa">Mensaje</label>
                            <textarea v-model="emailForm.mensaje" class="form-input-sa" rows="8" placeholder="Escribe el cuerpo del email..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="submit-btn submit-btn--orange" :disabled="emailForm.processing">
                                {{ emailForm.processing ? 'Enviando...' : '✉️ Enviar a todos los admins' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </template>
    </SuperAdminLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.page-title  { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
.page-sub    { font-size: .78rem; color: #64748b; margin: .2rem 0 0; }

.tabs { display: flex; gap: .25rem; background: #f1f5f9; border-radius: 10px; padding: .25rem; width: fit-content; }
.tab { font-size: .8rem; font-weight: 600; padding: .45rem 1.1rem; border: none; background: transparent; color: #64748b; border-radius: 8px; cursor: pointer; transition: all .15s; }
.tab--active { background: #fff; color: #1e293b; box-shadow: 0 1px 4px rgba(0,0,0,.1); }

.panel { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.panel-header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; flex-wrap: wrap; }
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }
.panel-sub   { font-size: .75rem; color: #94a3b8; }
.panel-body  { padding: 1.25rem; }

.form-label-sa { display: block; font-size: .75rem; font-weight: 600; color: #64748b; margin-bottom: .35rem; text-transform: uppercase; letter-spacing: .04em; }
.form-input-sa { width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .55rem .85rem; color: #1e293b; font-size: .85rem; outline: none; font-family: inherit; resize: vertical; transition: border-color .15s; }
.form-input-sa:focus { border-color: #3b82f6; background: #fff; }

.submit-btn { background: #3b82f6; color: #fff; border: none; border-radius: 8px; padding: .6rem 1.4rem; font-size: .85rem; font-weight: 600; cursor: pointer; transition: background .15s; }
.submit-btn:hover:not(:disabled) { background: #2563eb; }
.submit-btn:disabled { opacity: .6; cursor: not-allowed; }
.submit-btn--orange { background: #ea580c; }
.submit-btn--orange:hover:not(:disabled) { background: #c2410c; }

.tip-box { display: flex; gap: .6rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: .75rem 1rem; font-size: .8rem; color: #78350f; }

/* Anuncios */
.anuncio-item { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.anuncio-item:last-child { border-bottom: none; }
.anuncio-item--inactivo { opacity: .55; }
.anuncio-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: .4rem; flex-wrap: wrap; }
.anuncio-meta { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.anuncio-titulo { font-size: .85rem; font-weight: 600; color: #1e293b; }
.anuncio-cuerpo { font-size: .8rem; color: #475569; line-height: 1.55; margin-bottom: .4rem; }
.anuncio-footer { font-size: .7rem; color: #94a3b8; }
.anuncio-actions { display: flex; align-items: center; gap: .6rem; flex-shrink: 0; }

.tipo-badge { font-size: .67rem; font-weight: 700; padding: .15rem .55rem; border-radius: 6px; text-transform: uppercase; }
.badge-info   { background: #eff6ff; color: #2563eb; }
.badge-aviso  { background: #fffbeb; color: #b45309; }
.badge-alerta { background: #fff5f5; color: #dc2626; }

.vigente-badge  { font-size: .67rem; font-weight: 700; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: .15rem .5rem; border-radius: 999px; }
.inactivo-badge { font-size: .67rem; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: .15rem .5rem; border-radius: 999px; }

.action-link { background: none; border: none; font-size: .72rem; font-weight: 600; color: #3b82f6; cursor: pointer; padding: 0; }
.action-link:hover { text-decoration: underline; }
.action-link--red { color: #dc2626; }

.empty-state { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: .85rem; }
</style>
