<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UiIcon from '../Ui/UiIcon.vue';

withDefaults(defineProps<{ active: string; admin?: boolean; title?: string; description?: string }>(), {
    admin: true,
    title: 'Configuración',
    description: 'Personaliza la identidad de tu negocio y sus recibos.',
});
defineEmits<{ select: [section: string] }>();
const sections = [
    { id: 'apariencia', label: 'Apariencia', icon: 'store' },
    { id: 'negocio', label: 'Mi negocio', icon: 'settings' },
    { id: 'tickets', label: 'Tickets', icon: 'receipt' },
];
</script>

<template>
    <div class="pos-premium settings-workspace">
        <header class="p-page-heading"><div><h1 class="p-page-title">{{ title }}</h1><p class="settings-description">{{ description }}</p></div></header>
        <div class="settings-layout">
            <nav class="settings-nav" aria-label="Apartados de configuración">
                <template v-if="admin">
                    <template v-for="section in sections" :key="section.id">
                        <Link v-if="active === 'perfil'" :href="`/configuracion#${section.id}`" class="settings-nav__item"><UiIcon :name="section.icon" :size="18" />{{ section.label }}</Link>
                        <button v-else type="button" class="settings-nav__item" :class="{ 'is-active': active === section.id }"
                            :aria-current="active === section.id ? 'true' : undefined" :aria-controls="`settings-${section.id}`" @click="$emit('select', section.id)"><UiIcon :name="section.icon" :size="18" />{{ section.label }}</button>
                    </template>
                </template>
                <Link href="/perfil" class="settings-nav__item" :class="{ 'is-active': active === 'perfil' }" :aria-current="active === 'perfil' ? 'page' : undefined"><UiIcon name="users" :size="18" />Mi perfil</Link>
            </nav>
            <div class="settings-body"><slot /></div>
        </div>
    </div>
</template>

<style scoped>
.settings-workspace { max-width: 1200px; }
.settings-description { margin: 8px 0 0; color: var(--p-muted); font-size: 13px; line-height: 1.6; }
.settings-layout { display: grid; gap: 24px; align-items: start; }
.settings-body { min-width: 0; }
.settings-nav { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 4px; padding-bottom: 16px; border-bottom: 1px solid var(--p-border); }
.settings-nav__item { display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 10px 12px; border: 1px solid transparent; border-radius: 6px; background: transparent; color: var(--p-muted); font: inherit; font-size: 13px; font-weight: 500; text-decoration: none; text-align: left; }
.settings-nav__item:hover { background: var(--p-muted-surface); color: var(--p-text); }
.settings-nav__item.is-active { background: var(--p-surface); color: var(--p-brand-ink); border-color: var(--p-brand-ink); }
.settings-nav__item:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.settings-body :deep(.settings-panel) { border: 1px solid var(--p-border); border-radius: 10px; background: var(--p-surface); overflow-wrap: anywhere; }
.settings-body :deep(.settings-panel__header) { padding: 20px 24px; border-bottom: 1px solid var(--p-border); }
.settings-body :deep(.settings-title) { margin: 0; font-family: inherit; font-size: 15px; font-weight: 600; color: var(--p-text); line-height: 1.5; }
.settings-body :deep(.settings-help) { margin: 6px 0 0; color: var(--p-muted); font-size: 13px; line-height: 1.6; }
.settings-body :deep(.settings-panel__body) { padding: 24px; }
.settings-body :deep(.settings-footer) { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 24px; border-top: 1px solid var(--p-border); }
.settings-body :deep(.settings-status) { display: flex; align-items: center; gap: 6px; margin: 0; font-size: 12px; color: var(--p-muted); }
.settings-body :deep(.settings-status--success) { color: var(--p-success); }
.settings-body :deep(.p-button--primary:not(:disabled)) { border-color: var(--p-brand-ink); }
.settings-body :deep(.settings-error) { display: flex; gap: 6px; margin: 8px 0 0; font-size: 12px; color: var(--p-danger); line-height: 1.5; }
.settings-body :deep(.settings-file) { display: block; width: 100%; min-width: 0; margin-top: 12px; font: inherit; font-size: 13px; color: var(--p-muted); border-radius: 6px; }
.settings-body :deep(.settings-file::file-selector-button) { min-height: 44px; padding: 8px 12px; margin-right: 12px; border: 1px solid #858a94; border-radius: 6px; background: white; color: var(--p-text); font: inherit; cursor: pointer; }
.settings-body :deep(.settings-file:focus-visible) { outline: 2px solid var(--p-brand-ink); outline-offset: 3px; }
@media (min-width: 1200px) { .settings-layout { grid-template-columns: 164px minmax(0, 1fr); gap: 32px; } .settings-nav { grid-template-columns: minmax(0, 1fr); border-bottom: 0; padding: 0; } .settings-nav__item { justify-content: flex-start; } .settings-nav__item:last-child { margin-top: 12px; } }
@media (max-width: 599.98px) { .settings-nav { grid-template-columns: repeat(2, minmax(0, 1fr)); } .settings-nav__item { justify-content: flex-start; } .settings-body :deep(.settings-panel__header), .settings-body :deep(.settings-panel__body) { padding: 20px 16px; } .settings-body :deep(.settings-footer) { padding: 16px; } .settings-body :deep(.settings-footer > .p-button) { width: 100%; } }
</style>
