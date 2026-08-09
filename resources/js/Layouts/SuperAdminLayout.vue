<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const csrf = computed(() => page.props.csrf);

const drawerAbierto = ref(false);
router.on('navigate', () => { drawerAbierto.value = false; });

// Toasts
const toasts = ref([]);
let toastId = 0;
const empujarToast = (tipo, mensaje) => {
    const id = ++toastId;
    toasts.value.push({ id, tipo, mensaje });
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 4500);
};
watch(() => page.props.flash, (flash) => {
    if (flash?.success) empujarToast('success', flash.success);
    if (flash?.error)   empujarToast('error',   flash.error);
}, { immediate: true, deep: true });

const activo = (prefijo) => page.url.startsWith(prefijo);

const navSections = [
    {
        titulo: 'General',
        links: [
            { href: '/superadmin/dashboard',   icon: '◈',  label: 'Panel General' },
            { href: '/superadmin/monitoreo',   icon: '📡', label: 'Monitoreo' },
        ],
    },
    {
        titulo: 'Gestión',
        links: [
            { href: '/superadmin/dulcerias',   icon: '🏪', label: 'Dulcerías' },
            { href: '/superadmin/usuarios',    icon: '👥', label: 'Usuarios' },
        ],
    },
    {
        titulo: 'Operación',
        links: [
            { href: '/superadmin/soporte',     icon: '🔧', label: 'Soporte' },
            { href: '/superadmin/comunicacion',icon: '📢', label: 'Comunicación' },
        ],
    },
    {
        titulo: 'Negocio',
        links: [
            { href: '/superadmin/saas',        icon: '💼', label: 'Planes & SaaS' },
            { href: '/superadmin/auditoria',   icon: '📋', label: 'Auditoría' },
        ],
    },
];
</script>

<template>
    <div class="sa-layout">
        <!-- ── Sidebar escritorio ── -->
        <aside class="sa-sidebar d-none d-lg-flex flex-column">
            <SidebarContent :user="user" :csrf="csrf" :navSections="navSections" :activo="activo" />
        </aside>

        <!-- ── Drawer móvil ── -->
        <Teleport to="body">
            <div v-if="drawerAbierto" class="d-lg-none">
                <aside class="sa-sidebar sa-sidebar--drawer shadow-lg">
                    <SidebarContent :user="user" :csrf="csrf" :navSections="navSections" :activo="activo" />
                </aside>
                <div class="sa-drawer-overlay" @click="drawerAbierto = false" />
            </div>
        </Teleport>

        <!-- ── Área de contenido ── -->
        <div class="sa-content-area d-flex flex-column">
            <!-- Topbar -->
            <header class="sa-topbar d-flex align-items-center gap-3 px-4 py-0">
                <!-- Hamburger móvil -->
                <button
                    class="btn btn-link sa-hamburger d-lg-none p-0 me-1"
                    @click="drawerAbierto = true"
                    aria-label="Menú"
                >☰</button>

                <!-- Breadcrumb / título de página -->
                <div class="flex-grow-1">
                    <slot name="topbar">
                        <span class="sa-topbar-title">Panel de Plataforma</span>
                    </slot>
                </div>

                <!-- Usuario -->
                <div class="sa-topbar-user">
                    <div class="sa-topbar-avatar">{{ user?.nombre?.charAt(0)?.toUpperCase() }}</div>
                    <span class="d-none d-md-inline sa-topbar-nombre">{{ user?.nombre }}</span>
                </div>
            </header>

            <!-- Contenido principal -->
            <main class="sa-main flex-grow-1">
                <slot />
            </main>
        </div>

        <!-- Toasts -->
        <div class="sa-toasts">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="sa-toast"
                :class="toast.tipo === 'success' ? 'sa-toast--ok' : 'sa-toast--err'"
            >
                <span>{{ toast.tipo === 'success' ? '✅' : '❌' }}</span>
                <span>{{ toast.mensaje }}</span>
            </div>
        </div>
    </div>

    <!-- Componente interno del sidebar -->
    <component :is="'template'" />
</template>

<!-- Sub-componente en línea para el contenido del sidebar -->
<script>
import { defineComponent, h, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const SidebarContent = defineComponent({
    props: ['user', 'csrf', 'navSections', 'activo'],
    setup(props) {
        return () => h('div', { class: 'sa-sidebar-inner' }, [
            // Logo
            h('div', { class: 'sa-logo' }, [
                h('span', { class: 'sa-logo-icon' }, '🛡️'),
                h('div', {}, [
                    h('div', { class: 'sa-logo-title' }, 'Admin Panel'),
                    h('div', { class: 'sa-logo-sub' }, 'Dulcería POS'),
                ]),
            ]),

            // Nav con secciones
            h('nav', { class: 'sa-nav flex-grow-1' },
                props.navSections.flatMap(section => [
                    h('div', { class: 'sa-nav-section-label' }, section.titulo),
                    ...section.links.map(link =>
                        h(Link, {
                            href: link.href,
                            class: ['sa-nav-item', props.activo(link.href) ? 'sa-nav-item--active' : ''].join(' '),
                        }, () => [
                            h('span', { class: 'sa-nav-icon' }, link.icon),
                            h('span', {}, link.label),
                        ])
                    ),
                ])
            ),

            // Footer usuario
            h('div', { class: 'sa-sidebar-footer' }, [
                h('div', { class: 'sa-footer-user' }, [
                    h('div', { class: 'sa-footer-avatar' }, props.user?.nombre?.charAt(0)?.toUpperCase()),
                    h('div', { class: 'sa-footer-info' }, [
                        h('span', { class: 'sa-footer-nombre' }, props.user?.nombre),
                        h('span', { class: 'sa-footer-rol' }, 'Super Admin'),
                    ]),
                ]),
                h('form', { method: 'POST', action: '/logout' }, [
                    h('input', { type: 'hidden', name: '_token', value: props.csrf }),
                    h('button', { type: 'submit', class: 'sa-logout-btn' }, '← Cerrar sesión'),
                ]),
            ]),
        ]);
    },
});

export default { components: { SidebarContent } };
</script>

<style>
/* ── Layout ── */
.sa-layout {
    display: flex;
    min-height: 100dvh;
    background: #f1f5f9;
}

/* ── Sidebar ── */
.sa-sidebar {
    width: 240px;
    flex-shrink: 0;
    background: #0f172a;
    display: flex;
    flex-direction: column;
}
.sa-sidebar--drawer {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 1045;
    width: 240px;
}
.sa-drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1040;
}
.sa-sidebar-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 0;
}

/* Logo */
.sa-logo {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1.25rem 1.1rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.sa-logo-icon { font-size: 1.5rem; line-height: 1; }
.sa-logo-title {
    font-size: .95rem;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1.2;
    letter-spacing: .01em;
}
.sa-logo-sub {
    font-size: .68rem;
    color: #475569;
    letter-spacing: .03em;
}

/* Nav */
.sa-nav {
    padding: .75rem .6rem;
    overflow-y: auto;
}
.sa-nav-section-label {
    font-size: .6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #334155;
    padding: .8rem .85rem .25rem;
    margin-top: .25rem;
}
.sa-nav-item {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .6rem .85rem;
    border-radius: 8px;
    color: #94a3b8;
    font-size: .82rem;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s, color .15s;
    margin-bottom: 2px;
}
.sa-nav-item:hover {
    background: rgba(255,255,255,.06);
    color: #e2e8f0;
}
.sa-nav-item--active {
    background: rgba(59,130,246,.15);
    color: #93c5fd;
    font-weight: 600;
    border-left: 3px solid #3b82f6;
    padding-left: calc(.85rem - 3px);
}
.sa-nav-icon {
    font-size: 1rem;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

/* Footer */
.sa-sidebar-footer {
    padding: .9rem .85rem;
    border-top: 1px solid rgba(255,255,255,.06);
}
.sa-footer-user {
    display: flex;
    align-items: center;
    gap: .65rem;
    margin-bottom: .75rem;
}
.sa-footer-avatar {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: rgba(59,130,246,.25);
    color: #93c5fd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: .85rem;
    flex-shrink: 0;
}
.sa-footer-info { display: flex; flex-direction: column; min-width: 0; }
.sa-footer-nombre {
    font-size: .78rem;
    font-weight: 600;
    color: #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sa-footer-rol {
    font-size: .65rem;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.sa-logout-btn {
    width: 100%;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 7px;
    color: #64748b;
    font-size: .75rem;
    padding: .4rem .75rem;
    text-align: left;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.sa-logout-btn:hover {
    background: rgba(239,68,68,.12);
    color: #fca5a5;
    border-color: rgba(239,68,68,.2);
}

/* ── Área de contenido ── */
.sa-content-area {
    flex: 1;
    min-width: 0;
    background: #f1f5f9;
}

/* Topbar */
.sa-topbar {
    height: 56px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 100;
}
.sa-hamburger { color: #334155; font-size: 1.3rem; text-decoration: none; }
.sa-topbar-title {
    font-size: .88rem;
    font-weight: 600;
    color: #334155;
}
.sa-topbar-user {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.sa-topbar-avatar {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(59,130,246,.15);
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: .8rem;
}
.sa-topbar-nombre { font-size: .8rem; color: #64748b; font-weight: 500; }

/* Main */
.sa-main { padding: 1.5rem; }
@media (min-width: 992px) { .sa-main { padding: 2rem 2.5rem; } }

/* ── Toasts ── */
.sa-toasts {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.sa-toast {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .65rem 1rem;
    border-radius: 10px;
    font-size: .82rem;
    font-weight: 500;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    animation: toastIn .2s ease;
}
.sa-toast--ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.sa-toast--err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
@keyframes toastIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
</style>
