<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import UiOverlay from './Ui/UiOverlay.vue';
import UiIcon from './Ui/UiIcon.vue';
import UiButton from './Ui/UiButton.vue';

defineProps({ premium: { type: Boolean, default: false }, theme: { type: Object, default: () => ({}) }, admin: { type: Boolean, default: true } });

const visible = ref(true);

function cerrar() {
    visible.value = false;
}
</script>

<template>
    <UiOverlay v-if="premium" :open="visible" :title="admin ? 'Todo listo para gestionar tu negocio' : 'Todo listo para tu turno'" :theme="theme" @close="cerrar">
        <div class="p-welcome">
            <span class="p-welcome__symbol"><UiIcon name="candy" :size="32" /></span>
            <p>Explora los módulos que tienes disponibles desde hoy.</p>
            <ul class="p-welcome__features">
                <li v-if="admin"><UiIcon name="package" />Gestiona tu inventario</li>
                <li><UiIcon name="cart" />Vende fácilmente</li>
                <li><UiIcon name="cash" />Controla tu caja</li>
                <li v-if="admin"><UiIcon name="chart" />Consulta tus reportes</li>
            </ul>
            <UiButton @click="cerrar">Comenzar<UiIcon name="arrow" /></UiButton>
        </div>
    </UiOverlay>
    <Transition v-else name="modal-fade">
        <div v-if="visible" class="wm-backdrop" @click.self="cerrar">
            <div class="wm-card" role="dialog" aria-modal="true" aria-labelledby="wm-title">

                <div class="wm-logo">🍬</div>
                <p class="wm-brand">Dulcería POS</p>
                <h2 id="wm-title" class="wm-title">
                    Todo listo para gestionar tu negocio
                </h2>
                <p class="wm-sub">
                    Explora los módulos que tienes disponibles desde hoy.
                </p>

                <div class="wm-grid">
                    <div class="wm-feature">
                        <span class="wm-icon">📦</span>
                        <span>Gestiona tu Inventario</span>
                    </div>
                    <div class="wm-feature">
                        <span class="wm-icon">🛒</span>
                        <span>Vende Fácilmente</span>
                    </div>
                    <div class="wm-feature">
                        <span class="wm-icon">💵</span>
                        <span>Controla tu Caja</span>
                    </div>
                    <div class="wm-feature">
                        <span class="wm-icon">📊</span>
                        <span>Reportes en Tiempo Real</span>
                    </div>
                </div>

                <button class="wm-btn" @click="cerrar">¡Comenzar!</button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.wm-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 16px;
}

.wm-card {
    background: #fff;
    border-radius: 20px;
    padding: 44px 40px 36px;
    width: 100%;
    max-width: 500px;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.22);
}

.wm-logo {
    font-size: 48px;
    margin-bottom: 6px;
}

.wm-brand {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    color: #8B0000;
    font-weight: 700;
    margin: 0 0 10px;
}

.wm-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 8px;
    line-height: 1.3;
}

.wm-sub {
    color: #777;
    font-size: 0.88rem;
    margin: 0 0 28px;
}

.wm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 30px;
}

.wm-feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 18px 12px;
    border: 1.5px solid #f0e8e8;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #333;
    background: #fdf8f8;
    transition: border-color 0.15s, background 0.15s;
}

.wm-feature:hover {
    border-color: #8B0000;
    background: #fff5f5;
}

.wm-icon { font-size: 32px; }

.wm-btn {
    background: #8B0000;
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 13px 42px;
    font-size: 1rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.wm-btn:hover  { background: #6d0000; }
.wm-btn:active { transform: scale(0.97); }

/* Animación de entrada/salida */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.22s ease;
}
.modal-fade-enter-active .wm-card,
.modal-fade-leave-active .wm-card {
    transition: transform 0.22s ease, opacity 0.22s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-fade-enter-from .wm-card {
    transform: scale(0.94) translateY(12px);
    opacity: 0;
}

@media (max-width: 420px) {
    .wm-card { padding: 32px 22px 28px; }
    .wm-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
}
</style>
