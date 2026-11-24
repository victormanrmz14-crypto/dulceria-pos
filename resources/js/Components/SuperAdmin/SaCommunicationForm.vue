<script setup lang="ts">
import { computed, useId } from "vue";
import { useTemplateRef } from "vue";
import { focusInvalid } from "./focusInvalid";
import { useForm } from "@inertiajs/vue3";
const props = defineProps<{ mode: "anuncio" | "email" }>();
const id = useId();
const formElement = useTemplateRef<HTMLFormElement>("formElement");
const announcement = useForm({
    titulo: "",
    cuerpo: "",
    tipo: "info",
    expira_en: "",
});
const email = useForm({ asunto: "", mensaje: "" });
const form = computed(() => (props.mode === "email" ? email : announcement));
const save = () => {
    if (props.mode === "email")
        email.post("/superadmin/comunicacion/email-masivo", {
            onSuccess: () => email.reset(),
            preserveScroll: true,
            onError: () => focusInvalid(formElement.value),
        });
    else
        announcement.post("/superadmin/comunicacion", {
            onSuccess: () => announcement.reset(),
            preserveScroll: true,
            onError: () => focusInvalid(formElement.value),
        });
};
</script>
<template>
    <form ref="formElement" class="sa-form" @submit.prevent="save">
        <template v-if="mode === 'anuncio'">
            <div class="sa-field">
                <label :for="id + '-title'">Título del anuncio</label>
                <input
                    :id="id + '-title'"
                    v-model="announcement.titulo"
                    required
                    maxlength="200"
                    :aria-invalid="!!announcement.errors.titulo"
                    :aria-describedby="id + '-title-error'"
                />
                <span :id="id + '-title-error'" class="sa-error">
                    {{ announcement.errors.titulo }}
                </span>
            </div>
            <div class="sa-field">
                <label :for="id + '-type'">Tipo</label>
                <select
                    :id="id + '-type'"
                    v-model="announcement.tipo"
                    :aria-invalid="!!announcement.errors.tipo"
                    :aria-describedby="id + '-type-error'"
                >
                    <option value="info">Información</option>
                    <option value="aviso">Aviso</option>
                    <option value="alerta">Alerta</option>
                </select>
                <span :id="id + '-type-error'" class="sa-error">
                    {{ announcement.errors.tipo }}
                </span>
            </div>
            <div class="sa-field">
                <label :for="id + '-body'">Mensaje del anuncio</label>
                <textarea
                    :id="id + '-body'"
                    v-model="announcement.cuerpo"
                    rows="4"
                    required
                    maxlength="2000"
                    :aria-invalid="!!announcement.errors.cuerpo"
                    :aria-describedby="id + '-body-error'"
                />
                <span :id="id + '-body-error'" class="sa-error">
                    {{ announcement.errors.cuerpo }}
                </span>
            </div>
            <div class="sa-field">
                <label :for="id + '-expires'">Expira el (opcional)</label>
                <input
                    :id="id + '-expires'"
                    v-model="announcement.expira_en"
                    type="datetime-local"
                    :aria-invalid="!!announcement.errors.expira_en"
                    :aria-describedby="`${id}-expires-help ${id}-expires-error`"
                />
                <span :id="id + '-expires-help'" class="sa-muted">
                    Zona horaria del sistema: America/Mexico_City. Sin fecha,
                    permanece vigente hasta desactivarlo.
                </span>
                <span :id="id + '-expires-error'" class="sa-error">
                    {{ announcement.errors.expira_en }}
                </span>
            </div>
        </template>
        <template v-else>
            <p class="sa-note">
                Se enviará a todos los administradores habilitados de dulcerías.
                Usa la configuración de correo existente.
            </p>
            <div class="sa-field">
                <label :for="id + '-subject'">Asunto</label>
                <input
                    :id="id + '-subject'"
                    v-model="email.asunto"
                    maxlength="200"
                    required
                    :aria-invalid="!!email.errors.asunto"
                    :aria-describedby="id + '-subject-error'"
                />
                <span :id="id + '-subject-error'" class="sa-error">
                    {{ email.errors.asunto }}
                </span>
            </div>
            <div class="sa-field">
                <label :for="id + '-message'">Mensaje del correo</label>
                <textarea
                    :id="id + '-message'"
                    v-model="email.mensaje"
                    rows="8"
                    maxlength="5000"
                    required
                    :aria-invalid="!!email.errors.mensaje"
                    :aria-describedby="id + '-message-error'"
                />
                <span :id="id + '-message-error'" class="sa-error">
                    {{ email.errors.mensaje }}
                </span>
            </div>
        </template>
        <div
            v-if="Object.keys(form.errors).length"
            class="sa-error"
            role="alert"
        >
            Revisa los campos indicados antes de enviar.
        </div>
        <div>
            <button
                class="sa-button sa-button--primary"
                :disabled="form.processing"
                :aria-busy="form.processing"
            >
                {{
                    form.processing
                        ? "Enviando…"
                        : mode === "email"
                          ? "Enviar a administradores"
                          : "Publicar anuncio"
                }}
            </button>
        </div>
    </form>
</template>
