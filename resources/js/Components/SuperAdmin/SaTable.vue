<script setup lang="ts">
defineProps<{
    rows: Array<{ id: number | string; [key: string]: any }>;
    columns: Array<{ key: string; label: string; width?: string }>;
    caption: string;
    empty?: string;
}>();
</script>
<template>
    <div class="sa-table-container">
        <table class="sa-table" role="table">
            <caption class="visually-hidden">
                {{ caption }}
            </caption>
            <thead role="rowgroup">
                <tr role="row">
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        scope="col"
                        :style="{ width: col.width }"
                        role="columnheader"
                    >
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                <tr v-if="!rows.length" role="row">
                    <td :colspan="columns.length" class="sa-empty" role="cell">
                        {{ empty || "No hay registros para estos filtros." }}
                    </td>
                </tr>
                <tr v-for="row in rows" :key="row.id" role="row">
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        :data-label="col.label"
                        role="cell"
                    >
                        <slot :name="`cell-${col.key}`" :row="row">
                            {{ row[col.key] ?? "—" }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
