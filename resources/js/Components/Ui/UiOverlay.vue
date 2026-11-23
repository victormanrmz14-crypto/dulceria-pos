<script setup>
import { nextTick, onBeforeUnmount, shallowRef, useId, watch } from 'vue';
import UiIcon from './UiIcon.vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    open: Boolean,
    focusFallback: { type: String, default: '' },
    title: { type: String, required: true },
    variant: { type: String, default: 'dialog' },
    theme: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['close']);
const dialog = shallowRef(null);
const titleId = useId();
let opener = null;
let previousOverflow;

function release() {
    if (previousOverflow !== undefined) {
        document.body.style.overflow = previousOverflow;
        previousOverflow = undefined;
    }
    if (!opener) return;
    const available = (el) => el?.isConnected && el.getClientRects().length && !el.closest('[inert]') && getComputedStyle(el).visibility !== 'hidden';
    const target = available(opener) ? opener : props.focusFallback
        ? [...document.querySelectorAll(props.focusFallback)].find(available) : null;
    target?.focus({ preventScroll: true });
    opener = null;
}

watch(() => props.open, async (open) => {
    await nextTick();
    if (!dialog.value) return;
    if (open && !dialog.value.open) {
        opener = document.activeElement;
        previousOverflow = document.body.style.overflow;
        dialog.value.showModal();
        document.body.style.overflow = 'hidden';
    } else if (!open && dialog.value.open) {
        dialog.value.close();
        release();
    }
}, { immediate: true });

// Native modal dialog makes the page behind it inert. Explicit wrapping also
// keeps Tab inside when the last control would otherwise focus browser chrome.
function trapTab(event) {
    if (event.key !== 'Tab') return;
    const items = [...dialog.value.querySelectorAll('a[href],button:not(:disabled),input:not(:disabled),select:not(:disabled),textarea:not(:disabled),[tabindex="0"]')]
        .filter((el) => el.getClientRects().length && !el.closest('[hidden],[inert]') && getComputedStyle(el).visibility !== 'hidden');
    const first = items[0];
    const last = items.at(-1);
    if (!first) { event.preventDefault(); dialog.value.focus(); return; }
    if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog.value)) {
        event.preventDefault(); last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault(); first.focus();
    }
}

function backdrop(event) {
    if (event.target !== dialog.value) return;
    const box = dialog.value.getBoundingClientRect();
    if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) emit('close');
}

onBeforeUnmount(() => { dialog.value?.close(); release(); });
</script>

<template>
    <Teleport to="body">
        <dialog v-bind="$attrs" ref="dialog" class="pos-premium p-overlay" :class="`p-overlay--${variant}`"
            :style="theme" :aria-labelledby="titleId" @cancel.prevent="emit('close')"
            @keydown="trapTab" @click="backdrop" @close="release">
            <div class="p-overlay__heading">
                <h2 :id="titleId" class="p-overlay__title">{{ title }}</h2>
                <button type="button" class="p-icon-button" aria-label="Cerrar" autofocus @click="emit('close')">
                    <UiIcon name="close" :size="20" />
                </button>
            </div>
            <slot />
        </dialog>
    </Teleport>
</template>
