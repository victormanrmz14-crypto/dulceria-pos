<script setup lang="ts">
// Shared presentation vocabulary, scoped to the financial views only.
</script>

<template><div class="finance"><slot /></div></template>

<style scoped>
.finance { min-width: 0; }
.finance :deep(.p-button--primary:not(:disabled)) { border-color: var(--p-brand-ink); }
.finance :deep(.finance-description) { color: var(--p-muted); font-size: 13px; line-height: 1.6; margin: 8px 0 0; }
.finance :deep(.finance-metrics) { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; margin-bottom: 24px; }
.finance :deep(.finance-section) { margin-top: 24px; }
.finance :deep(.finance-muted) { color: var(--p-muted); }
.finance :deep(.finance-money) { font-variant-numeric: tabular-nums; white-space: nowrap; font-weight: 600; }
.finance :deep(.finance-pairs) { margin: 0; }
.finance :deep(.finance-pair) { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 6px 16px; padding: 14px 16px; border-bottom: 1px solid var(--p-border); }
.finance :deep(.finance-pair:last-child) { border-bottom: 0; }
.finance :deep(.finance-pair dt) { font-weight: 400; color: var(--p-muted); font-size: 13px; }
.finance :deep(.finance-pair dd) { margin: 0 0 0 auto; font-size: 14px; }
.finance :deep(.finance-pair--total) { background: var(--p-canvas); border-radius: 0 0 var(--p-radius-panel) var(--p-radius-panel); }
.finance :deep(.finance-pair--total dt) { font-weight: 600; color: var(--p-text); }
.finance :deep(.finance-table) { display: none; width: 100%; border-collapse: collapse; font-size: 13px; }
.finance :deep(.finance-table th) { background: var(--p-canvas); padding: 12px 16px; font-size: 12px; font-weight: 500; color: var(--p-muted); border-bottom: 1px solid var(--p-border); text-align: left; }
.finance :deep(.finance-table td) { padding: 12px 16px; border-bottom: 1px solid var(--p-border); overflow-wrap: anywhere; }
.finance :deep(.finance-table tr:last-child td) { border-bottom: 0; }
.finance :deep(.finance-table tbody tr:hover) { background: var(--p-canvas); }
.finance :deep(.finance-table .finance-number) { text-align: right; font-variant-numeric: tabular-nums; }
.finance :deep(.finance-table-caption) { position: absolute; width: 1px; height: 1px; padding: 0; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
.finance :deep(.finance-cards) { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; }
.finance :deep(.finance-record) { min-width: 0; padding: 16px; border: 1px solid var(--p-border); background: var(--p-surface); border-radius: var(--p-radius-panel); }
.finance :deep(.finance-record__heading) { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px 12px; margin-bottom: 12px; }
.finance :deep(.finance-record__title) { font: inherit; font-size: 14px; font-weight: 600; margin: 0; overflow-wrap: anywhere; }
.finance :deep(.finance-record__meta) { font-size: 12px; color: var(--p-muted); margin: 4px 0 0; overflow-wrap: anywhere; }
.finance :deep(.finance-record .finance-pair) { padding: 10px 0; }
.finance :deep(.finance-record .finance-pair--total) { border-radius: 0; background: transparent; padding-bottom: 0; }
.finance :deep(.finance-record__action) { width: 100%; margin-top: 16px; }
.finance :deep(.finance-link) { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; color: var(--p-brand-ink); font-weight: 600; text-decoration: none; overflow-wrap: anywhere; }
.finance :deep(.finance-link:hover) { text-decoration: underline; }
.finance :deep(.finance-pagination) { border-top: 1px solid var(--p-border); padding: 16px; }
.finance :deep(.finance-payment) { display: inline-flex; align-items: center; gap: 6px; color: var(--p-muted); font-size: 12px; }
@media (min-width: 600px) {
    .finance :deep(.finance-metrics) { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .finance :deep(.finance-pair) { padding-inline: 20px; }
}
@media (min-width: 768px) { .finance :deep(.finance-cards) { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1200px) {
    .finance :deep(.finance-metrics) { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .finance :deep(.finance-metrics--three) { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .finance :deep(.finance-table) { display: table; }
    .finance :deep(.finance-cards) { display: none; }
}
</style>
