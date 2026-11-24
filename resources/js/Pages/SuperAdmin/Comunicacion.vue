<script setup>
import { Head } from "@inertiajs/vue3";
import SuperAdminLayout from "../../Layouts/SuperAdminLayout.vue";
import SaHeading from "../../Components/SuperAdmin/SaHeading.vue";
import SaPagination from "../../Components/SuperAdmin/SaPagination.vue";
import { shallowRef } from "vue";
import { usePage } from "@inertiajs/vue3";
import SaCommunicationForm from "../../Components/SuperAdmin/SaCommunicationForm.vue";
defineProps({ anuncios: Object, vigentes: Number });
const page = usePage();
const tab = shallowRef("anuncios");
const remove = (event) => {
    if (!confirm("¿Eliminar este anuncio?")) event.preventDefault();
};
</script>
<template>
    <SuperAdminLayout>
        <Head title="Comunicación · Superadministración" />
        <SaHeading
            title="Comunicación"
            description="Anuncios de plataforma y mensajes para los administradores."
        />
        <div class="sa-tabs" aria-label="Tipo de comunicación">
            <button
                class="sa-button"
                :aria-pressed="tab === 'anuncios'"
                @click="tab = 'anuncios'"
            >
                Anuncios
            </button>
            <button
                class="sa-button"
                :aria-pressed="tab === 'email'"
                @click="tab = 'email'"
            >
                Correo administrativo
            </button>
        </div>
        <template v-if="tab === 'anuncios'">
            <div class="sa-grid">
                <section class="sa-panel">
                    <header>
                        <div>
                            <h2>Anuncios publicados</h2>
                            <p>{{ vigentes }} vigentes en toda la plataforma</p>
                        </div>
                    </header>
                    <p v-if="!anuncios.data.length" class="sa-empty">
                        Aún no se han publicado anuncios.
                    </p>
                    <article
                        v-for="a in anuncios.data"
                        :key="a.id"
                        class="sa-announcement"
                    >
                        <div class="sa-form-actions">
                            <span class="sa-status">
                                {{
                                    a.tipo === "info"
                                        ? "Información"
                                        : a.tipo === "aviso"
                                          ? "Aviso"
                                          : "Alerta"
                                }}
                            </span>
                            <span
                                class="sa-status"
                                :class="a.vigente ? 'sa-status--ok' : ''"
                            >
                                {{
                                    a.vigente
                                        ? "Vigente"
                                        : a.activo
                                          ? "Expirado"
                                          : "Desactivado"
                                }}
                            </span>
                        </div>
                        <h3>{{ a.titulo }}</h3>
                        <p class="sa-announcement-body">{{ a.cuerpo }}</p>
                        <p class="sa-note">
                            Por {{ a.autor }} · {{ a.creado_en }}
                            <br />
                            Expira:
                            {{ a.expira_en || "Sin fecha" }}
                        </p>
                        <div class="sa-form-actions">
                            <form
                                method="POST"
                                :action="`/superadmin/comunicacion/${a.id}/toggle`"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    :value="page.props.csrf"
                                />
                                <button class="sa-button">
                                    {{ a.activo ? "Desactivar" : "Activar" }}
                                </button>
                            </form>
                            <form
                                method="POST"
                                :action="`/superadmin/comunicacion/${a.id}`"
                                @submit="remove"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    :value="page.props.csrf"
                                />
                                <input
                                    type="hidden"
                                    name="_method"
                                    value="DELETE"
                                />
                                <button class="sa-button sa-button--danger">
                                    Eliminar anuncio
                                </button>
                            </form>
                        </div>
                    </article>
                    <SaPagination :paginator="anuncios" />
                </section>
                <section class="sa-panel">
                    <header><h2>Publicar anuncio</h2></header>
                    <div class="sa-panel-body">
                        <SaCommunicationForm mode="anuncio" />
                    </div>
                </section>
            </div>
        </template>
        <section v-else class="sa-panel">
            <header><h2>Enviar correo administrativo</h2></header>
            <div class="sa-panel-body">
                <SaCommunicationForm mode="email" />
            </div>
        </section>
    </SuperAdminLayout>
</template>
