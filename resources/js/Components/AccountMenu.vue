<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useId } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import UiIcon from './Ui/UiIcon.vue';
import UserAvatar from './Ui/UserAvatar.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user as { nombre?: string; apellido?: string; rol?: string; avatar_url?: string | null } | undefined);
const name = computed(() => [user.value?.nombre, user.value?.apellido].filter(Boolean).join(' ') || 'Usuario');
const role = computed(() => user.value?.rol === 'admin' ? 'Administrador' : 'Cajero');
const open = ref(false);
const loggingOut = ref(false);
const root = ref<HTMLElement | null>(null);
const trigger = ref<HTMLButtonElement | null>(null);
const panel = ref<HTMLElement | null>(null);
const id = useId();
const close = (restore = false) => { open.value = false; if (restore) trigger.value?.focus(); };
const outside = (event: PointerEvent) => {
    if (!root.value?.contains(event.target as Node)) close(!!panel.value?.contains(document.activeElement));
};
const focusOutside = (event: FocusEvent) => { if (!root.value?.contains(event.target as Node)) close(); };
const openWithKeyboard = async () => {
    open.value = true;
    await nextTick();
    panel.value?.querySelector<HTMLAnchorElement>('a')?.focus();
};
const stopNavigation = router.on('navigate', () => close());
onMounted(() => {
    document.addEventListener('pointerdown', outside);
    document.addEventListener('focusin', focusOutside);
});
onBeforeUnmount(() => {
    stopNavigation();
    document.removeEventListener('pointerdown', outside);
    document.removeEventListener('focusin', focusOutside);
});
</script>

<template>
    <div ref="root" class="account" @keydown.esc.stop.prevent="close(true)">
        <button ref="trigger" type="button" class="account__trigger" :aria-label="`Cuenta de ${name}`"
            :aria-expanded="open" :aria-controls="id" @click="open = !open" @keydown.down.prevent="openWithKeyboard">
            <UserAvatar :name="name" :src="user?.avatar_url" />
            <span class="account__name">{{ user?.nombre || 'Usuario' }}</span><UiIcon name="chevron" :size="14" />
        </button>
        <div v-if="open" :id="id" ref="panel" class="account__panel">
            <div class="account__identity"><UserAvatar :name="name" :src="user?.avatar_url" :size="40" /><div><strong>{{ name }}</strong><span>{{ role }}</span></div></div>
            <nav aria-label="Tu cuenta"><Link href="/perfil" class="account__action" @click="close()"><UiIcon name="users" :size="18" />Mi perfil</Link></nav>
            <!-- Keep the native form connected until the browser sends its POST. -->
            <form method="POST" action="/logout" @submit="loggingOut = true"><input type="hidden" name="_token" :value="page.props.csrf as string" /><button type="submit" class="account__action" :disabled="loggingOut" :aria-busy="loggingOut || undefined"><UiIcon :name="loggingOut ? 'loading' : 'logout'" :size="18" />{{ loggingOut ? 'Cerrando sesión…' : 'Cerrar sesión' }}</button></form>
        </div>
    </div>
</template>

<style scoped>
.account { position: relative; margin-left: auto; flex-shrink: 0; z-index: 40; }
.account__trigger { display: flex; align-items: center; gap: 8px; min-height: 44px; padding: 4px 6px; border: 1px solid transparent; border-radius: 6px; background: transparent; color: var(--p-text); font: inherit; font-size: 12px; }
.account__trigger:hover, .account__trigger[aria-expanded="true"] { background: var(--p-muted-surface); }
.account__trigger:focus-visible, .account__action:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 2px; }
.account__name { max-width: 180px; overflow-wrap: anywhere; text-align: left; }
.account__panel { position: absolute; right: 0; top: calc(100% + 6px); width: min(288px, calc(100vw - 32px)); max-height: calc(100dvh - 152px); overflow-y: auto; overscroll-behavior: contain; padding: 6px; background: var(--p-surface); border: 1px solid var(--p-border); border-radius: 10px; box-shadow: 0 8px 28px #1719231c; }
.account__identity { display: flex; align-items: center; gap: 12px; padding: 12px 10px 16px; margin-bottom: 6px; border-bottom: 1px solid var(--p-border); }
.account__identity > div { min-width: 0; }
.account__identity strong { display: block; font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }
.account__identity div > span { display: block; margin-top: 3px; font-size: 12px; color: var(--p-muted); }
.account__action { display: flex; align-items: center; gap: 10px; width: 100%; min-height: 44px; padding: 10px; border: 0; border-radius: 6px; color: var(--p-text); background: var(--p-surface); font: inherit; font-size: 13px; text-decoration: none; text-align: left; }
.account__action:hover { background: var(--p-muted-surface); color: var(--p-text); }
@media (max-width: 767.98px) { .account__name { display: none; } }
</style>
