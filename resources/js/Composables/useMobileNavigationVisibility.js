import { onBeforeUnmount, onMounted, shallowRef } from 'vue';

// Owns browser-only visibility signals. No module needs to know about the bar.
export function useMobileNavigationVisibility() {
    const suppressed = shallowRef(false);
    let observer;
    let viewport;
    let frame;
    const update = () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
            const editable = document.activeElement?.matches('input:not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]), textarea, [contenteditable="true"]');
            const keyboard = editable && (
                matchMedia('(hover: none) and (pointer: coarse)').matches ||
                (viewport && viewport.scale <= 1.05 && window.innerHeight - viewport.height > 150)
            );
            const overlay = [...document.querySelectorAll('dialog[open]:not(.p-overlay--drawer), .modal.show, .wm-backdrop, .offcanvas.show')]
                .some((el) => el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden');
            suppressed.value = Boolean(keyboard || overlay);
        });
    };
    onMounted(() => {
        viewport = window.visualViewport;
        viewport?.addEventListener('resize', update);
        window.addEventListener('resize', update);
        document.addEventListener('focusin', update);
        document.addEventListener('focusout', update);
        observer = new MutationObserver(update);
        observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'style', 'open'] });
        update();
    });
    onBeforeUnmount(() => {
        observer?.disconnect();
        cancelAnimationFrame(frame);
        viewport?.removeEventListener('resize', update);
        window.removeEventListener('resize', update);
        document.removeEventListener('focusin', update);
        document.removeEventListener('focusout', update);
    });
    return { suppressed };
}
