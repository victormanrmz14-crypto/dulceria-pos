<script setup lang="ts">
import { onBeforeUnmount, onMounted, shallowRef, useId } from 'vue';
import UiIcon from '../Ui/UiIcon.vue';
import UiOverlay from '../Ui/UiOverlay.vue';

defineProps<{ count: number; total: string; theme: Record<string, string> }>();

// Presentation only: one checkout instance, with an inline desktop panel or mobile dialog.
const desktop = shallowRef(window.matchMedia('(min-width: 992px)').matches);
const open = shallowRef(false);
const panelId = useId();
let query: MediaQueryList;
const update = () => { desktop.value = query.matches; open.value = false; };
onMounted(() => {
    query = window.matchMedia('(min-width: 992px)');
    query.addEventListener('change', update);
});
onBeforeUnmount(() => query?.removeEventListener('change', update));
</script>

<template>
    <div class="pos-cart-host">
        <button v-if="!desktop" type="button" class="pos-cart-trigger" data-pos-cart-trigger
            :aria-controls="panelId" :aria-expanded="open" aria-haspopup="dialog" @click="open = true">
            <UiIcon name="cart" :size="20" />
            <span>Ver carrito <span class="pos-cart-count">{{ count }}</span></span>
            <strong>{{ total }}</strong><UiIcon name="arrow" :size="18" />
        </button>
        <component :is="desktop ? 'aside' : UiOverlay" :id="panelId"
            :class="desktop ? 'p-panel pos-cart-panel' : 'pos-cart-sheet'"
            v-bind="desktop ? { 'aria-label': 'Carrito y cobro' } : {
                open, title: 'Carrito y cobro', theme,
                focusFallback: '[data-pos-cart-trigger], .pos-search input',
            }"
            @close="open = false">
            <slot :desktop="desktop" />
        </component>
    </div>
</template>

<style scoped>
.pos-cart-host { min-width: 0; }
.pos-cart-panel { position: sticky; top: 24px; }
.pos-cart-trigger {
    display: flex; align-items: center; gap: 10px; width: 100%; min-height: 52px;
    padding: 10px 14px; background: var(--p-surface); color: var(--p-brand-ink);
    border: 1px solid var(--p-brand-ink); border-radius: var(--p-radius-control);
    font: inherit; font-weight: 600; text-align: left; cursor: pointer;
}
.pos-cart-trigger:hover { background: var(--p-muted-surface); }
.pos-cart-trigger:active { box-shadow: inset 0 1px 2px #20212626; }
.pos-cart-trigger > span { flex: 1; }
.pos-cart-trigger strong { font-variant-numeric: tabular-nums; white-space: nowrap; }
.pos-cart-count { display: inline-grid; place-items: center; min-width: 22px; padding-inline: 4px; margin-left: 4px; border-radius: 4px; background: var(--p-muted-surface); color: var(--p-text); font-size: 12px; }
:global(.pos-cart-sheet.pos-premium.p-overlay) { width: min(480px, calc(100% - 24px)); }
@media (max-width: 767.98px) {
    :global(.pos-cart-sheet.pos-premium.p-overlay) {
        margin: auto 0 0; width: 100%; max-height: calc(100dvh - 16px);
        border-radius: 14px 14px 0 0; border-bottom: 0;
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
}
</style>
