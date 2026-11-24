<script setup>
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import SettingsWorkspace from '../../Components/Settings/SettingsWorkspace.vue';
import UiButton from '../../Components/Ui/UiButton.vue';
import UiIcon from '../../Components/Ui/UiIcon.vue';
import UiFormField from '../../Components/Ui/UiFormField.vue';
import { premiumTheme as createPremiumTheme } from '../../Components/Ui/theme';

const props = defineProps({
    configuracion: Object,
    logoUrl: String,
});

const tab = ref('apariencia');
onMounted(() => {
    const section = window.location.hash.slice(1);
    if (['apariencia', 'negocio', 'tickets'].includes(section)) tab.value = section;
});
const focusError = () => requestAnimationFrame(() => document.querySelector(`#settings-${tab.value} [aria-invalid="true"]`)?.focus());

// ── Paletas predefinidas ──────────────────────────────────────────────────────
const paletas = [
    { nombre: 'Dulce Rojo',      primario: '#8B0000', medio: '#A52A2A', oscuro: '#580000', hover: '#6d0000', acento: '#ff7043', texto: '#ffcccc', fondo: '#fff5f5' },
    { nombre: 'Rosa Dulce',      primario: '#be185d', medio: '#db2777', oscuro: '#9d174d', hover: '#a21057', acento: '#f472b6', texto: '#fce7f3', fondo: '#fdf2f8' },
    { nombre: 'Azul Marino',     primario: '#1e3a5f', medio: '#1d4ed8', oscuro: '#172554', hover: '#1e40af', acento: '#60a5fa', texto: '#bfdbfe', fondo: '#eff6ff' },
    { nombre: 'Verde Esmeralda', primario: '#15803d', medio: '#16a34a', oscuro: '#14532d', hover: '#166534', acento: '#4ade80', texto: '#bbf7d0', fondo: '#f0fdf4' },
    { nombre: 'Naranja Cítrico', primario: '#c2410c', medio: '#ea580c', oscuro: '#9a3412', hover: '#b45309', acento: '#fb923c', texto: '#fed7aa', fondo: '#fff7ed' },
    { nombre: 'Morado Real',     primario: '#6d28d9', medio: '#7c3aed', oscuro: '#5b21b6', hover: '#6021d0', acento: '#a78bfa', texto: '#ddd6fe', fondo: '#f5f3ff' },
    { nombre: 'Café Oscuro',     primario: '#78350f', medio: '#92400e', oscuro: '#451a03', hover: '#6b2d0c', acento: '#fbbf24', texto: '#fde68a', fondo: '#fffbeb' },
    { nombre: 'Teal',            primario: '#0e7490', medio: '#0891b2', oscuro: '#164e63', hover: '#0c6b7a', acento: '#22d3ee', texto: '#a5f3fc', fondo: '#ecfeff' },
    { nombre: 'Gris Carbón',     primario: '#1e293b', medio: '#334155', oscuro: '#0f172a', hover: '#1e293b', acento: '#94a3b8', texto: '#cbd5e1', fondo: '#f8fafc' },
];

// Determina la paleta actualmente guardada
const paletaGuardada = computed(() => {
    const c = props.configuracion?.colores;
    if (!c?.primario) return paletas[0];
    return paletas.find(p => p.primario === c.primario) ?? paletas[0];
});

const paletaSeleccionada = ref({ ...paletaGuardada.value });

const seleccionarPaleta = (p) => {
    paletaSeleccionada.value = { ...p };
};

// ── Formulario apariencia ─────────────────────────────────────────────────────
const aparienciaForm = useForm({ paleta: null, logo: null, eliminar_logo: false });
const logoInput     = ref(null);
const previewLogo = ref(props.logoUrl ?? null);
const invalidLogoSelection = ref(false);
let localLogoUrl;
const releaseLogoPreview = () => { if (localLogoUrl) URL.revokeObjectURL(localLogoUrl); localLogoUrl = null; };
onBeforeUnmount(releaseLogoPreview);

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        aparienciaForm.clearErrors('logo');
        aparienciaForm.recentlySuccessful = false;
        aparienciaForm.eliminar_logo = false;
        invalidLogoSelection.value = false;
        if (file.size > 2 * 1024 * 1024 || (file.type && !['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/x-ms-bmp'].includes(file.type))) {
            descartarLogo();
            invalidLogoSelection.value = true;
            aparienciaForm.setError('logo', file.size > 2 * 1024 * 1024
                ? 'El logotipo debe pesar como máximo 2 MB. El logotipo guardado se conserva.'
                : 'Selecciona una imagen JPG, PNG, WebP, GIF o BMP válida.');
            return;
        }
        aparienciaForm.logo = file;
        releaseLogoPreview();
        localLogoUrl = URL.createObjectURL(file);
        previewLogo.value = localLogoUrl;
    }
};

const descartarLogo = () => {
    aparienciaForm.logo = null;
    aparienciaForm.eliminar_logo = false;
    aparienciaForm.clearErrors('logo');
    aparienciaForm.recentlySuccessful = false;
    invalidLogoSelection.value = false;
    if (logoInput.value) logoInput.value.value = '';
    releaseLogoPreview();
    previewLogo.value = props.logoUrl ?? null;
};

const guardarApariencia = () => {
    if (invalidLogoSelection.value) { focusError(); return; }
    aparienciaForm.paleta = paletaSeleccionada.value;
    aparienciaForm.post('/configuracion/apariencia', {
        forceFormData: true,
        onSuccess: () => {
            aparienciaForm.logo        = null;
            aparienciaForm.eliminar_logo = false;
            if (logoInput.value) logoInput.value.value = '';
            releaseLogoPreview();
            previewLogo.value = props.logoUrl ?? null;
        },
        onError: focusError,
    });
};

const eliminarLogo = () => {
    if (!confirm('¿Eliminar el logo actual?')) return;
    descartarLogo();
    aparienciaForm.paleta = null;
    aparienciaForm.eliminar_logo = true;
    aparienciaForm.logo          = null;
    previewLogo.value            = null;
    aparienciaForm.post('/configuracion/apariencia', { forceFormData: true,
        onSuccess: () => { aparienciaForm.eliminar_logo = false; previewLogo.value = props.logoUrl ?? null; releaseLogoPreview(); },
        onError: () => { aparienciaForm.eliminar_logo = false; previewLogo.value = props.logoUrl ?? null; focusError(); }
    });
};

// ── Formulario negocio ────────────────────────────────────────────────────────
const neg = props.configuracion?.negocio ?? {};
const negocioForm = useForm({
    nombre_mostrar: neg.nombre_mostrar ?? '',
    direccion:      neg.direccion ?? '',
    telefono:       neg.telefono ?? '',
    rfc:            neg.rfc ?? '',
    email_negocio:  neg.email ?? '',
});

// ── Formulario tickets ────────────────────────────────────────────────────────
const tkt = props.configuracion?.ticket ?? {};
const ticketForm = useForm({
    encabezado:   tkt.encabezado ?? '',
    pie:          tkt.pie ?? '',
    mostrar_logo: tkt.mostrar_logo ?? true,
    mostrar_rfc:  tkt.mostrar_rfc ?? true,
});


const previewTheme = computed(() => createPremiumTheme(paletaSeleccionada.value));
const appearanceDirty = computed(() => invalidLogoSelection.value || !!aparienciaForm.logo || paletaSeleccionada.value.primario !== paletaGuardada.value.primario);
const businessFields = [
    { key: 'nombre_mostrar', label: 'Nombre comercial', max: 100, type: 'text', placeholder: 'Nombre de tu dulcería' },
    { key: 'rfc', label: 'RFC', max: 20, type: 'text', placeholder: 'RFC del negocio' },
    { key: 'direccion', label: 'Dirección', max: 250, type: 'text', placeholder: 'Calle, número, colonia y ciudad', wide: true },
    { key: 'telefono', label: 'Teléfono', max: 30, type: 'text', placeholder: '55 1234 5678' },
    { key: 'email_negocio', label: 'Correo electrónico', max: 100, type: 'email', placeholder: 'contacto@tudulceria.mx' },
];
</script>

<template>
    <Head title="Configuración" />
    <AppLayout premium v-slot="{ theme }">
        <SettingsWorkspace :style="theme" :active="tab" @select="tab = $event">
            <section v-show="tab === 'apariencia'" id="settings-apariencia" class="settings-panel" aria-labelledby="appearance-title">
                <header class="settings-panel__header"><h2 id="appearance-title" class="settings-title">Apariencia del negocio</h2><p class="settings-help">Elige los colores y el logotipo que identifican a tu dulcería.</p></header>
                <form @submit.prevent="guardarApariencia">
                    <div class="settings-panel__body appearance-grid">
                        <fieldset class="palette-fieldset"><legend class="settings-title">Paleta de colores</legend><p class="settings-help">La marca se aplica a las acciones y elementos destacados.</p>
                            <div class="palette-grid"><button v-for="p in paletas" :key="p.primario" type="button" class="palette-option" :class="{ 'is-selected': paletaSeleccionada.primario === p.primario }" :aria-pressed="paletaSeleccionada.primario === p.primario" :disabled="aparienciaForm.processing" @click="seleccionarPaleta(p)"><span class="palette-swatch" :style="{ background: p.primario }" aria-hidden="true"><span :style="{ background: p.acento }"></span></span><span>{{ p.nombre }}</span><UiIcon v-if="paletaSeleccionada.primario === p.primario" name="success" :size="16" /></button></div>
                            <p v-if="aparienciaForm.errors.paleta" class="settings-error" role="alert"><UiIcon name="warning" :size="16" />{{ aparienciaForm.errors.paleta }}</p>
                        </fieldset>
                        <figure class="appearance-preview" :style="previewTheme"><figcaption>Vista previa local</figcaption><div class="brand-preview"><div class="brand-preview__brand"><img v-if="previewLogo" :src="previewLogo" alt="" width="32" height="32" /><span v-else class="brand-preview__symbol"><UiIcon name="candy" :size="20" /></span><strong>{{ negocioForm.nombre_mostrar || 'Dulcería POS' }}</strong></div><div class="brand-preview__nav"><span><UiIcon name="house" :size="16" />Inicio</span><span><UiIcon name="plus" :size="16" />Vender</span><span><UiIcon name="package" :size="16" />Productos</span><span class="brand-preview__active"><UiIcon name="settings" :size="16" />Configuración</span></div></div><p>Los cambios se aplicarán al guardar.</p></figure>
                    </div>
                    <div class="settings-panel__body logo-section"><div class="logo-preview"><img v-if="previewLogo" :src="previewLogo" alt="Vista previa del logotipo del negocio" width="88" height="88" /><template v-else><UiIcon name="store" :size="28" /><span>Sin logotipo</span></template></div><div class="logo-controls"><label for="logoInput" class="settings-title">Logotipo del negocio</label><p id="logo-help" class="settings-help">Aparece en la navegación y en los tickets, si lo habilitas.</p><input id="logoInput" ref="logoInput" type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,.bmp" name="logo" class="settings-file" :disabled="aparienciaForm.processing" :aria-invalid="aparienciaForm.errors.logo ? 'true' : undefined" :aria-describedby="`logo-help logo-format${aparienciaForm.errors.logo ? ' logo-error' : ''}`" @change="onLogoChange" /><p id="logo-format" class="settings-help logo-format">JPG, PNG, WebP, GIF o BMP · Máximo 2 MB</p><p class="settings-help logo-state" role="status">{{ aparienciaForm.logo ? `Imagen seleccionada: ${aparienciaForm.logo.name}. Sin guardar.` : props.logoUrl ? 'Logotipo guardado.' : 'No hay un logotipo guardado.' }}</p><p v-if="aparienciaForm.errors.logo" class="settings-help">No se guardó una imagen nueva. El logotipo guardado se conserva.</p><button v-if="aparienciaForm.logo || invalidLogoSelection" type="button" class="settings-text-action" :disabled="aparienciaForm.processing" @click="descartarLogo">Descartar selección</button><p v-if="aparienciaForm.errors.logo" id="logo-error" class="settings-error" role="alert"><UiIcon name="warning" :size="16" />{{ aparienciaForm.errors.logo }}</p><button v-if="props.logoUrl" type="button" class="settings-text-action" :disabled="aparienciaForm.processing" @click="eliminarLogo">Eliminar logotipo</button></div></div>
                    <footer class="settings-footer"><p class="settings-status" :class="{ 'settings-status--success': !appearanceDirty && aparienciaForm.recentlySuccessful }" role="status"><UiIcon :name="!appearanceDirty && aparienciaForm.recentlySuccessful ? 'success' : 'info'" :size="16" />{{ aparienciaForm.errors.logo || aparienciaForm.errors.paleta ? 'No se guardaron los cambios' : appearanceDirty ? 'Hay cambios sin guardar' : aparienciaForm.recentlySuccessful ? 'Apariencia guardada' : 'Apariencia actual del negocio' }}</p><UiButton type="submit" :loading="aparienciaForm.processing">{{ aparienciaForm.processing ? 'Guardando…' : 'Guardar apariencia' }}</UiButton></footer>
                </form>
            </section>

            <section v-show="tab === 'negocio'" id="settings-negocio" class="settings-panel" aria-labelledby="business-title">
                <header class="settings-panel__header"><h2 id="business-title" class="settings-title">Información del negocio</h2><p class="settings-help">Estos datos identifican tu dulcería en el sistema y los recibos.</p></header>
                <form @submit.prevent="negocioForm.post('/configuracion/negocio', { onError: focusError })"><div class="settings-panel__body business-grid"><UiFormField v-for="field in businessFields" :key="field.key" :etiqueta="field.label" :error="negocioForm.errors[field.key]" :class="{ 'business-grid__wide': field.wide }" v-slot="{ control }"><input v-bind="control" v-model="negocioForm[field.key]" :type="field.type" :maxlength="field.max" :placeholder="field.placeholder" :disabled="negocioForm.processing" class="p-field-control" /></UiFormField></div><footer class="settings-footer"><p class="settings-status" :class="{ 'settings-status--success': !negocioForm.isDirty && negocioForm.recentlySuccessful }" role="status"><UiIcon :name="!negocioForm.isDirty && negocioForm.recentlySuccessful ? 'success' : 'info'" :size="16" />{{ negocioForm.isDirty ? 'Hay cambios sin guardar' : negocioForm.recentlySuccessful ? 'Información guardada' : 'Información actual del negocio' }}</p><UiButton type="submit" :loading="negocioForm.processing">{{ negocioForm.processing ? 'Guardando…' : 'Guardar información' }}</UiButton></footer></form>
            </section>

            <section v-show="tab === 'tickets'" id="settings-tickets" class="settings-panel" aria-labelledby="tickets-title">
                <header class="settings-panel__header"><h2 id="tickets-title" class="settings-title">Configuración de tickets</h2><p class="settings-help">Personaliza los mensajes y la información que aparece en tus recibos.</p></header>
                <form @submit.prevent="ticketForm.post('/configuracion/tickets', { onError: focusError })"><div class="settings-panel__body ticket-grid"><div class="ticket-controls"><UiFormField etiqueta="Encabezado del recibo" :error="ticketForm.errors.encabezado" v-slot="{ control }"><textarea v-bind="control" v-model="ticketForm.encabezado" class="p-field-control" rows="3" placeholder="Gracias por tu compra" :disabled="ticketForm.processing"></textarea><p class="settings-help">Aparece antes del detalle de productos.</p></UiFormField><UiFormField etiqueta="Pie del recibo" :error="ticketForm.errors.pie" v-slot="{ control }"><textarea v-bind="control" v-model="ticketForm.pie" class="p-field-control" rows="3" placeholder="¡Vuelve pronto!" :disabled="ticketForm.processing"></textarea><p class="settings-help">Aparece al final del ticket.</p></UiFormField><fieldset class="ticket-options"><legend class="settings-title">Información visible</legend><label class="ticket-toggle"><input v-model="ticketForm.mostrar_logo" type="checkbox" :disabled="ticketForm.processing" /><span><strong>Mostrar logotipo</strong><span>Incluye el logotipo al inicio del recibo.</span></span></label><p v-if="ticketForm.errors.mostrar_logo" class="settings-error" role="alert">{{ ticketForm.errors.mostrar_logo }}</p><label class="ticket-toggle"><input v-model="ticketForm.mostrar_rfc" type="checkbox" :disabled="ticketForm.processing" /><span><strong>Mostrar RFC</strong><span>Incluye el RFC del negocio en el recibo.</span></span></label><p v-if="ticketForm.errors.mostrar_rfc" class="settings-error" role="alert">{{ ticketForm.errors.mostrar_rfc }}</p></fieldset></div><figure class="receipt-figure"><figcaption>Vista previa local</figcaption><div class="ticket-preview"><img v-if="ticketForm.mostrar_logo && previewLogo" :src="previewLogo" alt="Logotipo en el recibo" width="64" height="64" /><strong class="ticket-preview__business">{{ negocioForm.nombre_mostrar || 'Dulcería POS' }}</strong><p v-if="ticketForm.mostrar_rfc && negocioForm.rfc">RFC: {{ negocioForm.rfc }}</p><p v-if="negocioForm.direccion">{{ negocioForm.direccion }}</p><div class="ticket-divider"></div><p v-if="ticketForm.encabezado" class="ticket-message">{{ ticketForm.encabezado }}</p><div class="ticket-divider"></div><div class="ticket-line"><span>1x Mazapán</span><span>$6.00</span></div><div class="ticket-line"><span>2x Pulpa de tamarindo</span><span>$14.00</span></div><div class="ticket-divider"></div><div class="ticket-line ticket-total"><span>TOTAL</span><span>$20.00</span></div><div class="ticket-divider"></div><p v-if="ticketForm.pie" class="ticket-message">{{ ticketForm.pie }}</p><p class="ticket-preview__footer">Dulcería POS · dulceriapos.com</p></div><p class="settings-help">Ejemplo ilustrativo con los valores del formulario. Guarda para aplicar los cambios.</p></figure></div><footer class="settings-footer"><p class="settings-status" :class="{ 'settings-status--success': !ticketForm.isDirty && ticketForm.recentlySuccessful }" role="status"><UiIcon :name="!ticketForm.isDirty && ticketForm.recentlySuccessful ? 'success' : 'info'" :size="16" />{{ ticketForm.isDirty ? 'Hay cambios sin guardar' : ticketForm.recentlySuccessful ? 'Configuración guardada' : 'Configuración actual de tickets' }}</p><UiButton type="submit" :loading="ticketForm.processing">{{ ticketForm.processing ? 'Guardando…' : 'Guardar tickets' }}</UiButton></footer></form>
            </section>
        </SettingsWorkspace>
    </AppLayout>
</template>

<style scoped>
.logo-state { overflow-wrap: anywhere; }
.palette-fieldset, .ticket-options { min-width: 0; padding: 0; margin: 0; border: 0; }
.palette-fieldset legend, .ticket-options legend { float: none; width: auto; }
.appearance-grid { display: grid; grid-template-columns: minmax(0, 1fr) 220px; gap: 28px; align-items: start; }
.palette-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-top: 20px; }
.palette-option { display: flex; align-items: center; gap: 8px; min-height: 48px; padding: 10px; border: 1px solid #858a94; border-radius: 6px; background: white; color: var(--p-text); font: inherit; font-size: 12px; text-align: left; }
.palette-option > span:nth-child(2) { flex: 1; min-width: 0; overflow-wrap: anywhere; }
.palette-option:hover { background: var(--p-canvas); }
.palette-option.is-selected { border: 2px solid var(--p-brand-ink); padding: 9px; background: white; }
.palette-option.is-selected > .p-icon { flex-shrink: 0; color: var(--p-brand-ink); }
.palette-option:focus-visible, .settings-text-action:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 3px; }
.palette-option:disabled { opacity: .6; cursor: wait; }
.palette-swatch { position: relative; display: block; width: 24px; height: 24px; flex-shrink: 0; border-radius: 5px; }
.palette-swatch > span { position: absolute; right: -2px; bottom: -2px; width: 10px; height: 10px; border: 2px solid white; border-radius: 50%; }
.appearance-preview { margin: 0; padding-top: 2px; }
.appearance-preview figcaption, .receipt-figure figcaption { margin-bottom: 12px; color: var(--p-muted); font-size: 12px; font-weight: 500; }
.appearance-preview > p { margin: 10px 0 0; color: var(--p-muted); font-size: 12px; line-height: 1.6; }
.brand-preview { border: 1px solid var(--p-border); border-radius: 8px; overflow: hidden; background: white; }
.brand-preview__brand { display: flex; align-items: center; gap: 8px; padding: 16px 12px; border-bottom: 1px solid var(--p-border); }
.brand-preview__brand strong { min-width: 0; font-size: 12px; font-weight: 600; overflow-wrap: anywhere; }
.brand-preview__brand img { object-fit: contain; flex-shrink: 0; }
.brand-preview__symbol { display: grid; place-items: center; width: 32px; height: 32px; flex-shrink: 0; background: var(--p-brand); color: var(--p-on-brand); border-radius: 8px; }
.brand-preview__nav { display: grid; gap: 4px; padding: 8px; }
.brand-preview__nav > span { display: flex; align-items: center; gap: 8px; padding: 10px 8px; color: var(--p-muted); font-size: 12px; }
.brand-preview__nav > .brand-preview__active { border-left: 2px solid var(--p-brand-ink); color: var(--p-brand-ink); font-weight: 600; }
.logo-section { display: flex; align-items: flex-start; gap: 20px; border-top: 1px solid var(--p-border); }
.logo-preview { display: grid; align-content: center; justify-items: center; gap: 6px; width: 88px; min-height: 88px; flex-shrink: 0; border: 1px dashed #858a94; border-radius: 8px; color: var(--p-muted); }
.logo-preview img { object-fit: contain; width: 86px; height: 86px; padding: 6px; }
.logo-preview > span { font-size: 12px; }
.logo-controls { flex: 1; min-width: 0; }
.logo-controls .settings-title { font-size: 13px; }
.logo-format { font-size: 12px; }
.settings-text-action { min-height: 44px; padding: 8px 0; border: 0; background: transparent; font: inherit; font-size: 13px; color: var(--p-danger); }
.settings-text-action:hover { text-decoration: underline; }
.business-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px 20px; }
.business-grid__wide { grid-column: 1 / -1; }
.ticket-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(220px, 280px); gap: 32px; align-items: start; }
.ticket-controls { display: grid; gap: 24px; min-width: 0; }
.ticket-controls textarea { resize: vertical; }
.ticket-options { padding-top: 4px; }
.ticket-options legend { margin-bottom: 8px; font-size: 13px; }
.ticket-toggle { display: flex; align-items: flex-start; gap: 12px; min-height: 64px; padding: 12px 0; cursor: pointer; }
.ticket-toggle input { margin-top: 3px; width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--p-brand-ink); }
.ticket-toggle input:focus-visible { outline: 2px solid var(--p-brand-ink); outline-offset: 3px; }
.ticket-toggle > span { min-width: 0; }
.ticket-toggle strong { display: block; font-size: 13px; font-weight: 500; }
.ticket-toggle span > span { display: block; margin-top: 4px; color: var(--p-muted); font-size: 12px; line-height: 1.6; }
.receipt-figure { min-width: 0; margin: 0; }
.ticket-preview { border: 1px solid #c9ccd3; border-radius: 4px; padding: 20px 16px; background: white; color: #34363d; font-family: var(--p-font-sans); font-size: 12px; text-align: center; overflow-wrap: anywhere; }
.ticket-preview > img { object-fit: contain; margin-bottom: 12px; }
.ticket-preview__business { display: block; font-size: 14px; }
.ticket-preview p { margin: 6px 0 0; font-size: 12px; line-height: 1.6; }
.ticket-message { white-space: pre-wrap; }
.ticket-divider { margin: 14px 0; border-top: 1px dashed #a1a5af; }
.ticket-line { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin: 8px 0; text-align: left; }
.ticket-line span:last-child { white-space: nowrap; font-variant-numeric: tabular-nums; }
.ticket-total { font-weight: 600; }
.ticket-preview .ticket-preview__footer { margin-top: 16px; color: #666973; }
@media (max-width: 899.98px) { .appearance-grid, .ticket-grid { grid-template-columns: minmax(0, 1fr); gap: 24px; } .appearance-preview { max-width: 300px; width: 100%; } .receipt-figure { max-width: 320px; width: 100%; margin-inline: auto; } }
@media (max-width: 599.98px) { .business-grid { grid-template-columns: minmax(0, 1fr); gap: 20px; } .logo-section { flex-direction: column; gap: 16px; } .logo-controls { width: 100%; } }
@media (max-width: 359.98px) { .palette-grid { grid-template-columns: minmax(0, 1fr); gap: 8px; } }
</style>
