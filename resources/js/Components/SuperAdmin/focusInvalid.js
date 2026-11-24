import { nextTick } from "vue";

// Server validation arrives through Inertia; wait for aria-invalid to render.
export function focusInvalid(form) {
    nextTick(() => form?.querySelector('[aria-invalid="true"]')?.focus());
}
