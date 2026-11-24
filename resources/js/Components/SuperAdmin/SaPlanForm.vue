<script setup lang="ts">
import { useTemplateRef } from "vue";
import { focusInvalid } from "./focusInvalid";
import { useForm } from "@inertiajs/vue3";
import { useId } from "vue";
const props = defineProps<{
    tenant: {
        id: number;
        nombre: string;
        plan: string;
        plan_expira_input: string | null;
    };
    planes: Record<string, { label: string }>;
}>();
const id = useId();
const formElement = useTemplateRef<HTMLFormElement>("formElement");
const form = useForm({
    plan: props.tenant.plan,
    plan_expira_en: props.tenant.plan_expira_input || "",
});
const save = () =>
    form.post(`/superadmin/saas/${props.tenant.id}/plan`, {
        preserveScroll: true,
        onError: () => focusInvalid(formElement.value),
    });
</script>
<template>
    <form ref="formElement" class="sa-form" @submit.prevent="save">
        <div class="sa-field">
            <label :for="id + '-plan'">Plan asignado</label>
            <select
                :id="id + '-plan'"
                v-model="form.plan"
                :aria-label="`Plan de ${tenant.nombre}`"
                :aria-invalid="!!form.errors.plan"
                :aria-describedby="id + '-error'"
            >
                <option v-for="(plan, key) in planes" :key="key" :value="key">
                    {{ plan.label }}
                </option>
            </select>
        </div>
        <div class="sa-field">
            <label :for="id + '-expiry'">Fecha de expiración</label>
            <input
                :id="id + '-expiry'"
                v-model="form.plan_expira_en"
                type="date"
                :aria-invalid="!!form.errors.plan_expira_en"
                :aria-describedby="id + '-error'"
            />
        </div>
        <span :id="id + '-error'" class="sa-error" role="status">
            {{ form.errors.plan || form.errors.plan_expira_en }}
        </span>
        <button
            class="sa-button"
            :disabled="form.processing"
            :aria-busy="form.processing"
        >
            {{ form.processing ? "Guardando…" : "Guardar plan" }}
        </button>
    </form>
</template>
