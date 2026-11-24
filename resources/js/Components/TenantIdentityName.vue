<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import UiIcon from './Ui/UiIcon.vue';

defineProps({ multiline: { type: Boolean, default: false } });
const page = usePage<{ tenantConfig?: { nombre?: string } | null }>();
const name = computed(() => page.props.tenantConfig?.nombre || 'Dulcería POS');
const disclosure = ref<HTMLDetailsElement | null>(null);
const close = () => { if (disclosure.value) disclosure.value.open = false; };
</script>

<template>
    <details ref="disclosure" class="p-identity-name" :class="{ 'p-identity-name--multiline': multiline }" translate="no"
        @keydown.esc.stop="close" @focusout="close">
        <summary :title="name" :aria-label="`Ver nombre completo: ${name}`">
            <span>{{ name }}</span><UiIcon name="chevron" :size="12" />
        </summary>
        <span class="p-identity-name__full" translate="no">{{ name }}</span>
    </details>
</template>
