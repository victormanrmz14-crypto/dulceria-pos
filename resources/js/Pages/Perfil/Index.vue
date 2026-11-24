<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, useId } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import SettingsWorkspace from '../../Components/Settings/SettingsWorkspace.vue';
import UserAvatar from '../../Components/Ui/UserAvatar.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import AdminDialog from '../../Components/Admin/AdminDialog.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user as { nombre: string; apellido?: string; rol: string; avatar_url?: string | null });
const name = computed(() => [user.value.nombre, user.value.apellido].filter(Boolean).join(' '));
const form = useForm<{ avatar: File | null }>({ avatar: null });
const removal = useForm({});
const input = ref<HTMLInputElement | null>(null);
const id = useId();
const draftUrl = ref<string | null>(null);
const confirmRemoval = ref(false);
const busy = computed(() => form.processing || removal.processing);
const clearDraft = () => {
    if (draftUrl.value) URL.revokeObjectURL(draftUrl.value);
    draftUrl.value = null;
    form.reset();
    form.clearErrors();
    if (input.value) input.value.value = '';
};
const selectPhoto = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (draftUrl.value) URL.revokeObjectURL(draftUrl.value);
    form.clearErrors();
    form.avatar = file;
    draftUrl.value = URL.createObjectURL(file);
};
const save = () => form.post('/perfil/avatar', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: clearDraft,
    onFinish: async () => {
        if (form.hasErrors) { await nextTick(); input.value?.focus(); }
    },
});
const remove = () => removal.delete('/perfil/avatar', {
    preserveScroll: true,
    onSuccess: () => { clearDraft(); confirmRemoval.value = false; },
    onFinish: async () => {
        if (!confirmRemoval.value) { await nextTick(); input.value?.focus(); }
    },
});
onBeforeUnmount(() => { if (draftUrl.value) URL.revokeObjectURL(draftUrl.value); });
</script>

<template>
    <Head title="Mi perfil" />
    <AppLayout premium v-slot="{ theme }">
        <SettingsWorkspace :style="theme" active="perfil" :admin="user.rol === 'admin'" title="Mi perfil" description="Personaliza cómo apareces en tu cuenta.">
            <section class="settings-panel" aria-labelledby="profile-photo-title">
                <header class="settings-panel__header"><h2 id="profile-photo-title" class="settings-title">Fotografía de perfil</h2><p class="settings-help">Tu fotografía es opcional. Si prefieres, puedes usar tus iniciales.</p></header>
                <form @submit.prevent="save">
                    <div class="settings-panel__body">
                        <div class="profile-identity"><UserAvatar :name="name" :src="draftUrl || user.avatar_url" :size="88" :decorative="false" /><div><h3>{{ name }}</h3><p>{{ user.rol === 'admin' ? 'Administrador' : 'Cajero' }}</p><span class="profile-preview-state" role="status">{{ draftUrl ? 'Vista previa · Sin guardar' : user.avatar_url ? 'Fotografía guardada' : 'Usando tus iniciales' }}</span></div></div>
                        <div class="profile-upload">
                            <label :for="id" class="profile-label">{{ user.avatar_url ? 'Reemplazar fotografía' : 'Seleccionar fotografía' }}</label>
                            <p :id="`${id}-help`" class="settings-help">JPEG, PNG o WebP, hasta 2 MB. La imagen se mostrará en un círculo.</p>
                            <input :id="id" ref="input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="settings-file" :disabled="busy"
                                :aria-invalid="form.errors.avatar ? 'true' : undefined" :aria-describedby="`${id}-help${form.errors.avatar ? ` ${id}-error` : ''}`" @change="selectPhoto" />
                            <p v-if="form.avatar" class="profile-filename">Seleccionada: {{ form.avatar.name }}</p>
                            <p v-if="form.errors.avatar" :id="`${id}-error`" class="settings-error" role="alert"><UiIcon name="warning" :size="16" />{{ form.errors.avatar }}</p>
                            <p v-if="form.progress" class="settings-status" role="status">Subiendo fotografía: {{ form.progress.percentage }} %</p>
                            <div v-if="draftUrl || user.avatar_url" class="profile-secondary-actions">
                                <UiButton v-if="draftUrl" variant="secondary" :disabled="busy" @click="clearDraft">Descartar selección</UiButton>
                                <button v-if="user.avatar_url" type="button" class="profile-remove" :disabled="busy" @click="confirmRemoval = true"><UiIcon name="close" :size="16" />Eliminar fotografía</button>
                            </div>
                        </div>
                        <div class="profile-note"><UiIcon name="info" :size="18" /><p>Esta fotografía es personal y aparecerá en el menú de tu cuenta.</p></div>
                    </div>
                    <footer class="settings-footer"><p class="settings-status" :class="{ 'settings-status--success': !draftUrl && (form.recentlySuccessful || removal.recentlySuccessful) }" role="status"><UiIcon :name="!draftUrl && (form.recentlySuccessful || removal.recentlySuccessful) ? 'success' : 'info'" :size="16" />{{ draftUrl ? 'Hay una fotografía sin guardar' : form.recentlySuccessful ? 'Fotografía guardada' : removal.recentlySuccessful ? 'Fotografía eliminada' : 'Tus cambios se guardan en tu perfil' }}</p><UiButton type="submit" :loading="form.processing" :disabled="!form.avatar || removal.processing">{{ form.processing ? 'Guardando…' : 'Guardar fotografía' }}</UiButton></footer>
                </form>
            </section>
            <AdminDialog :open="confirmRemoval" title="Eliminar fotografía" :theme="theme" focus-fallback=".profile-upload input" @close="!removal.processing && (confirmRemoval = false)">
                <form class="admin-dialog-form" @submit.prevent="remove"><p class="settings-help">Se eliminará tu fotografía guardada y volverán a mostrarse tus iniciales.</p><div class="admin-dialog-actions"><UiButton variant="secondary" :disabled="removal.processing" @click="confirmRemoval = false">Cancelar</UiButton><UiButton type="submit" :loading="removal.processing">{{ removal.processing ? 'Eliminando…' : 'Eliminar fotografía' }}</UiButton></div></form>
            </AdminDialog>
        </SettingsWorkspace>
    </AppLayout>
</template>

<style scoped>
.profile-identity { display: flex; align-items: center; gap: 20px; padding-bottom: 28px; }
.profile-identity > div { min-width: 0; }
.profile-identity h3 { margin: 0; font-family: inherit; font-size: 16px; font-weight: 600; line-height: 1.5; overflow-wrap: anywhere; }
.profile-identity p { margin: 4px 0 8px; font-size: 13px; color: var(--p-muted); }
.profile-preview-state { font-size: 12px; color: var(--p-muted); }
.profile-upload { max-width: 600px; }
.profile-label { display: block; font-size: 13px; font-weight: 600; }
.profile-filename { margin: 12px 0 0; color: var(--p-muted); font-size: 12px; overflow-wrap: anywhere; }
.profile-secondary-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 16px; margin-top: 16px; }
.profile-remove { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 8px 0; border: 0; background: transparent; color: var(--p-danger); font: inherit; font-size: 13px; }
.profile-remove:hover { text-decoration: underline; }
.profile-remove:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 3px; }
.profile-remove:disabled { color: var(--p-muted); }
.profile-note { display: flex; align-items: flex-start; gap: 8px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--p-border); color: var(--p-muted); }
.profile-note p { margin: 0; font-size: 12px; line-height: 1.6; }
.profile-note .p-icon { flex-shrink: 0; }
@media (max-width: 359.98px) { .profile-identity { gap: 12px; } .profile-identity h3 { font-size: 14px; } }
</style>
