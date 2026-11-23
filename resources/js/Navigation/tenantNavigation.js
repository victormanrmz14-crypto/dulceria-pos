// UI visibility mirrors the existing tenant routes; Laravel remains authoritative.
const destinations = [
    { id: 'home', href: '/dashboard', icon: 'house', label: 'Inicio', primary: true },
    { id: 'products', href: '/productos', icon: 'package', label: 'Productos', admin: true, primary: true },
    { id: 'sell', href: '/ventas', icon: 'plus', label: 'Vender', primary: true },
    { id: 'sales', href: '/ventas/historial', icon: 'receipt', label: 'Ventas', primary: true },
    { id: 'cash', href: '/caja', icon: 'cash', label: 'Caja actual' },
    { id: 'closures', href: '/cortes', icon: 'receipt', label: 'Cortes históricos', admin: true },
    { id: 'users', href: '/usuarios', icon: 'users', label: 'Usuarios', admin: true },
    { id: 'reports', href: '/reportes', icon: 'chart', label: 'Reportes', admin: true },
    { id: 'categories', href: '/catalogos/categorias', icon: 'tags', label: 'Categorías', admin: true },
    { id: 'brands', href: '/catalogos/marcas', icon: 'store', label: 'Marcas', admin: true },
    { id: 'suppliers', href: '/catalogos/proveedores', icon: 'truck', label: 'Proveedores', admin: true },
    { id: 'settings', href: '/configuracion', icon: 'settings', label: 'Configuración', admin: true },
];

export const navigationPath = (url) => new URL(url, 'http://tenant.local').pathname.replace(/\/+$/, '') || '/';
export const isWithin = (path, base) => path === base || path.startsWith(`${base}/`);

export function matchesDestination(path, item) {
    if (item.id === 'sell') return path === '/ventas';
    if (item.id === 'sales') return isWithin(path, '/ventas/historial') || /^\/ventas\/[^/]+\/ticket$/.test(path);
    if (item.id === 'home') return path === item.href;
    return isWithin(path, item.href);
}

export function tenantNavigation(role, url) {
    const path = navigationPath(url);
    const items = destinations.filter((item) => !item.admin || role === 'admin').map((item) => ({
        ...item,
        active: matchesDestination(path, item),
        // Parent sections remain visually selected on edit/ticket pages, but
        // aria-current="page" belongs only to the actual destination URL.
        current: path === item.href,
    }));
    const primary = items.filter((item) => item.primary);
    const selected = items.find((item) => item.active);
    return {
        items,
        primary,
        bottom: [...primary, { id: 'menu', label: 'Menú', icon: 'menu', active: !primary.some((item) => item.active) }],
        sectionLabel: selected?.label || 'Menú',
    };
}
