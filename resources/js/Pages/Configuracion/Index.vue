<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    configuracion: Object,
    usuarios:      Array,
});

const page = usePage();
const tab  = ref('apariencia');

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
const customHex = ref('');
const mostrarCustom = ref(false);

const seleccionarPaleta = (p) => {
    paletaSeleccionada.value = { ...p };
    mostrarCustom.value = false;
};

const aplicarCustomHex = () => {
    const hex = customHex.value.trim();
    if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;
    paletaSeleccionada.value = {
        nombre: 'Personalizado',
        primario: hex,
        medio:    hex,
        oscuro:   hex,
        hover:    hex,
        acento:   '#ff7043',
        texto:    '#ffcccc',
        fondo:    '#fff5f5',
    };
};

// ── Formulario apariencia ─────────────────────────────────────────────────────
const aparienciaForm = useForm({ paleta: null, logo: null, eliminar_logo: false });
const logoInput     = ref(null);
const previewLogo   = ref(props.configuracion?.logo ?? null);

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        aparienciaForm.logo = file;
        previewLogo.value   = URL.createObjectURL(file);
    }
};

const guardarApariencia = () => {
    aparienciaForm.paleta = paletaSeleccionada.value;
    aparienciaForm.post('/configuracion/apariencia', {
        forceFormData: true,
        onSuccess: () => {
            aparienciaForm.logo        = null;
            aparienciaForm.eliminar_logo = false;
            if (logoInput.value) logoInput.value.value = '';
        },
    });
};

const eliminarLogo = () => {
    if (!confirm('¿Eliminar el logo actual?')) return;
    aparienciaForm.eliminar_logo = true;
    aparienciaForm.logo          = null;
    previewLogo.value            = null;
    aparienciaForm.post('/configuracion/apariencia', { forceFormData: true,
        onSuccess: () => { aparienciaForm.eliminar_logo = false; }
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

// ── Formulario contraseña ─────────────────────────────────────────────────────
const passForm = useForm({
    password_actual:       '',
    password:              '',
    password_confirmation: '',
});

// ── Usuarios (submodulo) ──────────────────────────────────────────────────────
const busqueda = ref('');
const usuariosFiltrados = computed(() => {
    if (!busqueda.value.trim()) return props.usuarios;
    const q = busqueda.value.toLowerCase();
    return props.usuarios.filter(u =>
        u.nombre.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q) ||
        u.username.toLowerCase().includes(q)
    );
});

const rolLabel = (u) => u.rol === 'admin' ? 'Admin' : 'Cajero';
const rolClase = (u) => u.rol === 'admin' ? 'ubadge--admin' : 'ubadge--cajero';
</script>

<template>
    <AppLayout>
        <Head title="Configuración" />

        <div class="cfg-wrap">
            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="cfg-header">
                <div>
                    <h1 class="cfg-title">⚙️ Configuración</h1>
                    <p class="cfg-sub">Personaliza tu dulcería</p>
                </div>
            </div>

            <!-- ── Tabs nav (dentro del lienzo) ──────────────────────── -->
            <div class="cfg-tabs">
                <button v-for="[k, icon, label] in [
                    ['apariencia','🎨','Apariencia'],
                    ['negocio','🏪','Mi Negocio'],
                    ['tickets','🧾','Recibos'],
                    ['usuarios','👥','Usuarios'],
                    ['seguridad','🔒','Seguridad'],
                ]"
                    :key="k"
                    class="cfg-tab"
                    :class="{ 'cfg-tab--active': tab === k }"
                    @click="tab = k"
                >
                    <span class="cfg-tab-icon">{{ icon }}</span>
                    <span class="cfg-tab-label">{{ label }}</span>
                </button>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- TAB: APARIENCIA                                           -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div v-if="tab === 'apariencia'" class="cfg-panel">

                <!-- Paleta de colores -->
                <div class="cfg-section">
                    <div class="cfg-section-title">Paleta de colores</div>
                    <p class="cfg-section-desc">El color que elijas se aplicará al sidebar y a los elementos destacados de tu dulcería.</p>

                    <div class="paletas-grid">
                        <button
                            v-for="p in paletas"
                            :key="p.primario"
                            class="paleta-chip"
                            :class="{ 'paleta-chip--active': paletaSeleccionada.primario === p.primario }"
                            @click="seleccionarPaleta(p)"
                            :title="p.nombre"
                        >
                            <span class="paleta-swatch" :style="{ background: p.primario }"></span>
                            <span class="paleta-dot" :style="{ background: p.acento }"></span>
                            <span class="paleta-nombre">{{ p.nombre }}</span>
                            <span v-if="paletaSeleccionada.primario === p.primario" class="paleta-check">✓</span>
                        </button>
                    </div>

                    <!-- Color personalizado -->
                    <div class="custom-color-wrap">
                        <button class="custom-color-toggle" @click="mostrarCustom = !mostrarCustom">
                            🎨 Color personalizado
                        </button>
                        <div v-if="mostrarCustom" class="custom-color-row">
                            <div class="custom-preview" :style="{ background: customHex || '#ccc' }"></div>
                            <input
                                v-model="customHex"
                                type="text"
                                placeholder="#3b82f6"
                                class="custom-hex-input"
                                maxlength="7"
                            />
                            <button class="btn-apply-hex" @click="aplicarCustomHex">Aplicar</button>
                        </div>
                        <p v-if="mostrarCustom" class="custom-note">Ingresa un color hexadecimal. Para mejores resultados usa tonos oscuros.</p>
                    </div>

                    <!-- Preview del sidebar -->
                    <div class="sidebar-preview" :style="{ background: paletaSeleccionada.primario }">
                        <div class="sp-marca" :style="{ background: paletaSeleccionada.medio }">
                            <div v-if="previewLogo" class="sp-logo-wrap">
                                <img :src="previewLogo" alt="Logo" class="sp-logo-img" />
                            </div>
                            <div class="sp-brand-name">{{ negocioForm.nombre_mostrar || '🍬 Dulcería POS' }}</div>
                            <div class="sp-user">Usuario Admin</div>
                        </div>
                        <div class="sp-nav">
                            <div class="sp-item sp-item--activo" :style="{ background: 'rgba(255,255,255,.15)' }">
                                <span class="sp-dot" :style="{ background: paletaSeleccionada.acento }"></span>
                                🏠 Inicio
                            </div>
                            <div class="sp-item">🛒 Ventas</div>
                            <div class="sp-item">🍬 Productos</div>
                            <div class="sp-item sp-item--cfg">⚙️ Configuración</div>
                        </div>
                    </div>
                </div>

                <!-- Logo del negocio -->
                <div class="cfg-section">
                    <div class="cfg-section-title">Logo del negocio</div>
                    <p class="cfg-section-desc">Aparecerá en el sidebar y en los recibos (si está habilitado). PNG o JPG, máx. 2 MB.</p>

                    <div class="logo-area">
                        <div class="logo-current">
                            <div v-if="previewLogo" class="logo-preview-wrap">
                                <img :src="previewLogo" alt="Logo actual" class="logo-preview" />
                                <button type="button" class="logo-remove" @click="eliminarLogo" title="Eliminar logo">✕</button>
                            </div>
                            <div v-else class="logo-placeholder">
                                <span class="logo-placeholder-icon">🍬</span>
                                <span class="logo-placeholder-txt">Sin logo</span>
                            </div>
                        </div>
                        <div class="logo-upload">
                            <label class="upload-btn" for="logoInput">📂 Seleccionar imagen</label>
                            <input id="logoInput" ref="logoInput" type="file" accept="image/*" class="d-none" @change="onLogoChange" />
                            <p class="logo-hint">PNG, JPG, WebP · máx. 2 MB</p>
                        </div>
                    </div>
                </div>

                <!-- Guardar apariencia -->
                <div class="cfg-footer">
                    <button class="btn-guardar" :disabled="aparienciaForm.processing" @click="guardarApariencia">
                        {{ aparienciaForm.processing ? 'Guardando...' : '💾 Guardar apariencia' }}
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- TAB: MI NEGOCIO                                           -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div v-if="tab === 'negocio'" class="cfg-panel">
                <div class="cfg-section">
                    <div class="cfg-section-title">Información del negocio</div>
                    <p class="cfg-section-desc">Esta información puede aparecer en los recibos y en el encabezado del sistema.</p>

                    <form class="cfg-form" @submit.prevent="negocioForm.post('/configuracion/negocio')">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="cfg-label">Nombre a mostrar</label>
                                <input v-model="negocioForm.nombre_mostrar" type="text" class="cfg-input" placeholder="Ej: Dulcería La Palomita" maxlength="100" />
                                <span class="cfg-hint">Reemplaza "Dulcería POS" en el sidebar</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="cfg-label">RFC</label>
                                <input v-model="negocioForm.rfc" type="text" class="cfg-input" placeholder="Ej: XAXX010101000" maxlength="20" />
                            </div>
                            <div class="col-12">
                                <label class="cfg-label">Dirección</label>
                                <input v-model="negocioForm.direccion" type="text" class="cfg-input" placeholder="Calle, número, colonia, ciudad" maxlength="250" />
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="cfg-label">Teléfono</label>
                                <input v-model="negocioForm.telefono" type="text" class="cfg-input" placeholder="Ej: 55 1234 5678" maxlength="30" />
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="cfg-label">Correo del negocio</label>
                                <input v-model="negocioForm.email_negocio" type="email" class="cfg-input" placeholder="contacto@tunegocio.com" maxlength="100" />
                            </div>
                        </div>
                        <div v-if="Object.keys(negocioForm.errors).length" class="cfg-errors">
                            <div v-for="(err, key) in negocioForm.errors" :key="key">{{ err }}</div>
                        </div>
                        <div class="cfg-footer">
                            <button type="submit" class="btn-guardar" :disabled="negocioForm.processing">
                                {{ negocioForm.processing ? 'Guardando...' : '💾 Guardar información' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- TAB: RECIBOS / TICKETS                                    -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div v-if="tab === 'tickets'" class="cfg-panel">
                <div class="cfg-section">
                    <div class="cfg-section-title">Personalización de recibos</div>
                    <p class="cfg-section-desc">Texto que aparecerá en la parte superior e inferior de cada comprobante de venta.</p>

                    <form class="cfg-form" @submit.prevent="ticketForm.post('/configuracion/tickets')">
                        <div class="row g-4">
                            <div class="col-12 col-lg-7">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="cfg-label">Encabezado del recibo</label>
                                        <textarea v-model="ticketForm.encabezado" class="cfg-input cfg-textarea" rows="3" placeholder="Ej: Gracias por tu compra en Dulcería La Palomita&#10;Tel: 55 1234 5678"></textarea>
                                        <span class="cfg-hint">Aparece arriba del ticket, antes del detalle de productos</span>
                                    </div>
                                    <div class="col-12">
                                        <label class="cfg-label">Pie del recibo</label>
                                        <textarea v-model="ticketForm.pie" class="cfg-input cfg-textarea" rows="3" placeholder="Ej: ¡Vuelve pronto! · No se aceptan devoluciones"></textarea>
                                        <span class="cfg-hint">Aparece al final del ticket</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="toggles-group">
                                            <label class="toggle-item">
                                                <div class="toggle-wrap">
                                                    <input type="checkbox" v-model="ticketForm.mostrar_logo" class="toggle-check" />
                                                    <span class="toggle-slider"></span>
                                                </div>
                                                <div>
                                                    <div class="toggle-label">Mostrar logo en recibo</div>
                                                    <div class="toggle-desc">Imprime el logo del negocio al inicio del ticket</div>
                                                </div>
                                            </label>
                                            <label class="toggle-item">
                                                <div class="toggle-wrap">
                                                    <input type="checkbox" v-model="ticketForm.mostrar_rfc" class="toggle-check" />
                                                    <span class="toggle-slider"></span>
                                                </div>
                                                <div>
                                                    <div class="toggle-label">Mostrar RFC en recibo</div>
                                                    <div class="toggle-desc">Incluye el RFC del negocio en el ticket</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista previa del ticket -->
                            <div class="col-12 col-lg-5">
                                <div class="cfg-label mb-2">Vista previa</div>
                                <div class="ticket-preview">
                                    <div class="tp-logo" v-if="ticketForm.mostrar_logo && previewLogo">
                                        <img :src="previewLogo" alt="Logo" class="tp-logo-img" />
                                    </div>
                                    <div class="tp-business">{{ negocioForm.nombre_mostrar || 'Dulcería POS' }}</div>
                                    <div v-if="ticketForm.mostrar_rfc && negocioForm.rfc" class="tp-rfc">RFC: {{ negocioForm.rfc }}</div>
                                    <div v-if="negocioForm.direccion" class="tp-addr">{{ negocioForm.direccion }}</div>
                                    <div class="tp-divider">────────────────</div>
                                    <div class="tp-encabezado" v-if="ticketForm.encabezado">{{ ticketForm.encabezado }}</div>
                                    <div class="tp-divider">────────────────</div>
                                    <div class="tp-item"><span>1x Mazapán</span><span>$6.00</span></div>
                                    <div class="tp-item"><span>2x Pulpa de tamarindo</span><span>$14.00</span></div>
                                    <div class="tp-divider">────────────────</div>
                                    <div class="tp-total"><span>TOTAL</span><span>$20.00</span></div>
                                    <div class="tp-divider">────────────────</div>
                                    <div class="tp-pie" v-if="ticketForm.pie">{{ ticketForm.pie }}</div>
                                    <div class="tp-footer">Dulcería POS · dulceriapos.com</div>
                                </div>
                            </div>
                        </div>
                        <div class="cfg-footer">
                            <button type="submit" class="btn-guardar" :disabled="ticketForm.processing">
                                {{ ticketForm.processing ? 'Guardando...' : '💾 Guardar configuración de recibos' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- TAB: USUARIOS                                             -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div v-if="tab === 'usuarios'" class="cfg-panel">
                <div class="cfg-section">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <div class="cfg-section-title mb-0">Usuarios de tu dulcería</div>
                            <p class="cfg-section-desc mb-0">{{ usuarios.length }} usuario{{ usuarios.length !== 1 ? 's' : '' }} registrado{{ usuarios.length !== 1 ? 's' : '' }}</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <input v-model="busqueda" type="text" class="cfg-input cfg-search" placeholder="🔍 Buscar..." />
                            <Link href="/usuarios/create" class="btn-nuevo">+ Nuevo usuario</Link>
                        </div>
                    </div>

                    <div v-if="usuariosFiltrados.length === 0" class="cfg-empty">
                        Sin usuarios que coincidan con la búsqueda.
                    </div>
                    <div v-else class="usuarios-grid">
                        <div v-for="u in usuariosFiltrados" :key="u.id" class="ucard" :class="!u.activo ? 'ucard--inact' : ''">
                            <div class="ucard-av" :class="u.rol === 'admin' ? 'ucard-av--admin' : 'ucard-av--cajero'">
                                {{ u.nombre.charAt(0).toUpperCase() }}
                            </div>
                            <div class="ucard-body">
                                <div class="ucard-nombre">{{ u.nombre }}</div>
                                <div class="ucard-email">{{ u.email }}</div>
                                <div class="ucard-meta">
                                    <span class="ubadge" :class="rolClase(u)">{{ rolLabel(u) }}</span>
                                    <span class="ubadge" :class="u.activo ? 'ubadge--ok' : 'ubadge--err'">
                                        {{ u.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                    <span class="ucard-desde">desde {{ u.desde }}</span>
                                </div>
                            </div>
                            <Link :href="`/usuarios/${u.id}/edit`" class="ucard-edit">✏️</Link>
                        </div>
                    </div>

                    <div class="cfg-footer">
                        <Link href="/usuarios" class="btn-secondary">Ver gestión completa →</Link>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- TAB: SEGURIDAD                                            -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div v-if="tab === 'seguridad'" class="cfg-panel">
                <div class="cfg-section">
                    <div class="cfg-section-title">Cambiar contraseña</div>
                    <p class="cfg-section-desc">Actualiza tu contraseña de acceso al sistema.</p>

                    <form class="cfg-form" style="max-width: 440px;" @submit.prevent="passForm.post('/configuracion/password', { onSuccess: () => passForm.reset() })">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="cfg-label">Contraseña actual</label>
                                <input v-model="passForm.password_actual" type="password" class="cfg-input" placeholder="Tu contraseña actual" autocomplete="current-password" />
                                <span v-if="passForm.errors.password_actual" class="cfg-error-msg">{{ passForm.errors.password_actual }}</span>
                            </div>
                            <div class="col-12">
                                <label class="cfg-label">Nueva contraseña</label>
                                <input v-model="passForm.password" type="password" class="cfg-input" placeholder="Mínimo 8 caracteres" autocomplete="new-password" minlength="8" />
                                <span v-if="passForm.errors.password" class="cfg-error-msg">{{ passForm.errors.password }}</span>
                            </div>
                            <div class="col-12">
                                <label class="cfg-label">Confirmar nueva contraseña</label>
                                <input v-model="passForm.password_confirmation" type="password" class="cfg-input" placeholder="Repite la nueva contraseña" autocomplete="new-password" />
                            </div>
                        </div>
                        <div class="cfg-footer">
                            <button type="submit" class="btn-guardar" :disabled="passForm.processing">
                                {{ passForm.processing ? 'Guardando...' : '🔒 Cambiar contraseña' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Estructura ────────────────────────────────────────────────────────────── */
.cfg-wrap   { max-width: 1000px; }
.cfg-header { margin-bottom: 1.5rem; }
.cfg-title  { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0; }
.cfg-sub    { font-size: .8rem; color: #64748b; margin: .2rem 0 0; }

/* ── Tabs ──────────────────────────────────────────────────────────────────── */
.cfg-tabs {
    display: flex;
    gap: .25rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: .35rem;
    margin-bottom: 1.5rem;
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.cfg-tab {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .2rem;
    padding: .55rem 1.1rem;
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 10px;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
    flex: 1;
    min-width: 80px;
}
.cfg-tab--active {
    background: var(--dulce-rojo, #8B0000);
    color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.cfg-tab-icon  { font-size: 1.1rem; }
.cfg-tab-label { font-size: .72rem; font-weight: 600; }

/* ── Panel ─────────────────────────────────────────────────────────────────── */
.cfg-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.cfg-section { padding: 1.75rem 2rem; border-bottom: 1px solid #f1f5f9; }
.cfg-section:last-child { border-bottom: none; }
.cfg-section-title { font-size: .95rem; font-weight: 700; color: #1e293b; margin-bottom: .35rem; }
.cfg-section-desc  { font-size: .8rem; color: #64748b; margin: 0 0 1.25rem; line-height: 1.55; }

.cfg-footer { padding: 1.25rem 2rem; background: #fafafa; border-top: 1px solid #f1f5f9; }

/* ── Form elements ─────────────────────────────────────────────────────────── */
.cfg-form { }
.cfg-label { display: block; font-size: .73rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .35rem; }
.cfg-input {
    width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .55rem .85rem;
    font-size: .88rem;
    color: #1e293b;
    outline: none;
    font-family: inherit;
    transition: border-color .15s, background .15s;
}
.cfg-input:focus { border-color: var(--dulce-rojo, #8B0000); background: #fff; }
.cfg-textarea { resize: vertical; min-height: 72px; }
.cfg-search { max-width: 200px; }
.cfg-hint { font-size: .7rem; color: #94a3b8; margin-top: .25rem; display: block; }
.cfg-error-msg { font-size: .72rem; color: #dc2626; margin-top: .25rem; display: block; }
.cfg-errors { background: #fff5f5; border: 1px solid #fecaca; border-radius: 8px; padding: .75rem 1rem; font-size: .8rem; color: #dc2626; margin-top: .75rem; }
.cfg-empty { text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: .88rem; }

/* ── Botones ───────────────────────────────────────────────────────────────── */
.btn-guardar {
    background: var(--dulce-rojo, #8B0000);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: .6rem 1.5rem;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s;
}
.btn-guardar:hover:not(:disabled) { opacity: .88; }
.btn-guardar:disabled { opacity: .55; cursor: not-allowed; }

.btn-nuevo {
    background: var(--dulce-rojo, #8B0000);
    color: #fff;
    border-radius: 8px;
    padding: .45rem 1rem;
    font-size: .82rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity .15s;
}
.btn-nuevo:hover { opacity: .88; color: #fff; }

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    padding: .5rem 1.1rem;
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s;
}
.btn-secondary:hover { background: #e2e8f0; color: #1e293b; }

/* ── Paletas ───────────────────────────────────────────────────────────────── */
.paletas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: .65rem;
    margin-bottom: 1rem;
}
.paleta-chip {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem .75rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    font-size: .78rem;
    font-weight: 600;
    color: #475569;
    transition: all .15s;
    text-align: left;
}
.paleta-chip:hover { border-color: #94a3b8; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,.08); }
.paleta-chip--active { border-color: var(--dulce-rojo, #8B0000) !important; box-shadow: 0 0 0 3px rgba(0,0,0,.06); }
.paleta-swatch { width: 22px; height: 22px; border-radius: 5px; flex-shrink: 0; }
.paleta-dot    { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.paleta-nombre { flex: 1; font-size: .73rem; }
.paleta-check  { color: #10b981; font-weight: 800; font-size: .85rem; margin-left: auto; }

.custom-color-wrap { margin-bottom: 1.25rem; }
.custom-color-toggle {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .4rem .9rem;
    font-size: .78rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    margin-bottom: .6rem;
    transition: background .15s;
}
.custom-color-toggle:hover { background: #e2e8f0; }
.custom-color-row { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.custom-preview { width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; flex-shrink: 0; }
.custom-hex-input { width: 130px; border: 1px solid #e2e8f0; border-radius: 8px; padding: .4rem .7rem; font-size: .85rem; outline: none; background: #f8fafc; font-family: monospace; }
.custom-hex-input:focus { border-color: var(--dulce-rojo, #8B0000); }
.btn-apply-hex { background: var(--dulce-rojo, #8B0000); color: #fff; border: none; border-radius: 8px; padding: .4rem .85rem; font-size: .78rem; font-weight: 600; cursor: pointer; }
.custom-note { font-size: .7rem; color: #94a3b8; margin-top: .3rem; }

/* ── Preview sidebar ───────────────────────────────────────────────────────── */
.sidebar-preview {
    border-radius: 12px;
    overflow: hidden;
    width: 200px;
    font-size: .72rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    margin-top: 1rem;
    transition: background .3s;
}
.sp-marca { padding: .75rem .85rem; text-align: center; transition: background .3s; }
.sp-logo-wrap { margin-bottom: .35rem; }
.sp-logo-img { max-height: 32px; max-width: 100%; object-fit: contain; border-radius: 4px; }
.sp-brand-name { color: #fff; font-weight: 700; font-size: .78rem; margin-bottom: .15rem; }
.sp-user  { color: rgba(255,255,255,.65); font-size: .65rem; }
.sp-nav { padding: .4rem 0; }
.sp-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .85rem;
    color: rgba(255,255,255,.75);
    font-size: .7rem;
    cursor: default;
}
.sp-item--activo { background: rgba(255,255,255,.15); color: #fff; }
.sp-item--cfg { margin-top: .25rem; border-top: 1px solid rgba(255,255,255,.08); padding-top: .5rem; }
.sp-dot { width: 3px; height: 14px; border-radius: 2px; flex-shrink: 0; transition: background .3s; }

/* ── Logo upload ───────────────────────────────────────────────────────────── */
.logo-area { display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap; }
.logo-current { flex-shrink: 0; }
.logo-preview-wrap { position: relative; display: inline-block; }
.logo-preview { max-width: 120px; max-height: 80px; object-fit: contain; border: 2px solid #e2e8f0; border-radius: 10px; display: block; }
.logo-remove {
    position: absolute; top: -8px; right: -8px;
    width: 22px; height: 22px; border-radius: 50%;
    background: #ef4444; color: #fff; border: none;
    font-size: .65rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.logo-placeholder {
    width: 100px; height: 70px; border: 2px dashed #e2e8f0; border-radius: 10px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .3rem;
}
.logo-placeholder-icon { font-size: 1.5rem; }
.logo-placeholder-txt  { font-size: .65rem; color: #94a3b8; }
.logo-upload { display: flex; flex-direction: column; gap: .4rem; }
.upload-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px;
    padding: .45rem 1rem; font-size: .8rem; font-weight: 600; color: #475569;
    cursor: pointer; transition: background .15s;
}
.upload-btn:hover { background: #e2e8f0; }
.logo-hint { font-size: .7rem; color: #94a3b8; margin: 0; }

/* ── Toggles ───────────────────────────────────────────────────────────────── */
.toggles-group { display: flex; flex-direction: column; gap: .85rem; }
.toggle-item { display: flex; align-items: flex-start; gap: .85rem; cursor: pointer; }
.toggle-wrap { position: relative; flex-shrink: 0; margin-top: .1rem; }
.toggle-check { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-slider {
    display: block; width: 40px; height: 22px; border-radius: 11px;
    background: #e2e8f0; transition: background .2s; cursor: pointer;
}
.toggle-slider::after {
    content: '';
    position: absolute; top: 3px; left: 3px;
    width: 16px; height: 16px; border-radius: 50%;
    background: #fff; transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle-check:checked + .toggle-slider { background: var(--dulce-rojo, #8B0000); }
.toggle-check:checked + .toggle-slider::after { transform: translateX(18px); }
.toggle-label { font-size: .82rem; font-weight: 600; color: #1e293b; }
.toggle-desc  { font-size: .72rem; color: #94a3b8; }

/* ── Ticket preview ─────────────────────────────────────────────────────────── */
.ticket-preview {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-size: .72rem;
    color: #1e293b;
    line-height: 1.7;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.tp-logo { text-align: center; margin-bottom: .5rem; }
.tp-logo-img { max-height: 40px; max-width: 100%; object-fit: contain; }
.tp-business { text-align: center; font-weight: 700; font-size: .82rem; }
.tp-rfc  { text-align: center; color: #64748b; }
.tp-addr { text-align: center; color: #64748b; font-size: .67rem; }
.tp-divider { text-align: center; color: #94a3b8; margin: .2rem 0; font-size: .65rem; }
.tp-encabezado { text-align: center; color: #475569; font-size: .67rem; white-space: pre-line; }
.tp-item { display: flex; justify-content: space-between; }
.tp-total { display: flex; justify-content: space-between; font-weight: 700; font-size: .82rem; }
.tp-pie { text-align: center; color: #475569; font-size: .67rem; white-space: pre-line; }
.tp-footer { text-align: center; color: #94a3b8; font-size: .62rem; margin-top: .25rem; }

/* ── Usuarios grid ─────────────────────────────────────────────────────────── */
.usuarios-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .75rem; }
.ucard {
    display: flex; align-items: center; gap: .85rem;
    padding: .9rem 1rem; border: 1px solid #f1f5f9;
    border-radius: 10px; background: #fafafa;
    transition: box-shadow .15s;
}
.ucard:hover { box-shadow: 0 2px 8px rgba(0,0,0,.07); background: #fff; }
.ucard--inact { opacity: .55; }
.ucard-av { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .95rem; flex-shrink: 0; }
.ucard-av--admin  { background: #eff6ff; color: #2563eb; }
.ucard-av--cajero { background: #f8fafc; color: #64748b; }
.ucard-body { flex: 1; min-width: 0; }
.ucard-nombre { font-size: .83rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ucard-email  { font-size: .72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ucard-meta   { display: flex; align-items: center; gap: .35rem; margin-top: .3rem; flex-wrap: wrap; }
.ucard-desde  { font-size: .65rem; color: #94a3b8; }
.ucard-edit   { color: #94a3b8; text-decoration: none; font-size: .85rem; flex-shrink: 0; padding: .3rem; border-radius: 6px; transition: background .15s; }
.ucard-edit:hover { background: #f1f5f9; color: #1e293b; }

.ubadge { display: inline-flex; padding: .12rem .5rem; border-radius: 5px; font-size: .62rem; font-weight: 700; }
.ubadge--admin  { background: #eff6ff; color: #2563eb; }
.ubadge--cajero { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.ubadge--ok  { background: #f0fdf4; color: #15803d; }
.ubadge--err { background: #fff5f5; color: #dc2626; }

/* ── Utilities ─────────────────────────────────────────────────────────────── */
.d-none { display: none; }
.mb-0 { margin-bottom: 0 !important; }
.mb-2 { margin-bottom: .5rem !important; }
.mb-3 { margin-bottom: .75rem !important; }
.mt-3 { margin-top: .75rem !important; }

@media (max-width: 600px) {
    .cfg-section { padding: 1.25rem 1rem; }
    .cfg-footer  { padding: 1rem; }
    .cfg-tab-label { display: none; }
    .cfg-tab-icon  { font-size: 1.2rem; }
    .cfg-tab { min-width: unset; }
    .paletas-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
}
</style>
