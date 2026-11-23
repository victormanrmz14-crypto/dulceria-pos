<script setup>
import {
    House, CirclePlus, Menu, LayoutDashboard, ShoppingCart, Banknote, ReceiptText, Package, Users,
    ChartNoAxesCombined, Tags, Truck, Settings, LogOut, PanelLeft,
    ChevronDown, ArrowUpRight, ArrowRight, X, CircleCheck, TriangleAlert,
    Info, ShieldCheck, Store, CreditCard, LoaderCircle, Candy, Inbox, CalendarDays,
} from '@lucide/vue';

defineOptions({ inheritAttrs: false });
defineProps({ name: { type: String, required: true }, size: { type: Number, default: 18 } });

// Explicit imports keep the pilot's icon vocabulary small and tree-shakeable.
const icons = {
    house: House, plus: CirclePlus, dashboard: LayoutDashboard, cart: ShoppingCart, cash: Banknote, receipt: ReceiptText,
    package: Package, users: Users, chart: ChartNoAxesCombined, tags: Tags,
    truck: Truck, settings: Settings, logout: LogOut, menu: Menu, panel: PanelLeft,
    chevron: ChevronDown, external: ArrowUpRight, arrow: ArrowRight, close: X,
    success: CircleCheck, warning: TriangleAlert, info: Info, shield: ShieldCheck,
    store: Store, card: CreditCard, loading: LoaderCircle, candy: Candy,
    inbox: Inbox, calendar: CalendarDays,
};
</script>

<template>
    <component :is="icons[name] || icons.info" v-bind="$attrs" :size="size"
        :stroke-width="1.75" aria-hidden="true" focusable="false" class="p-icon" />
</template>
