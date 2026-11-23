# Fase 2.3 · Bottom Navigation e integración con el Design System

Implementada sobre `refactor/premium-ui`, conservando el checkout de la Fase 2.2 sin crear commits. La navegación premium ya pertenece a `AppLayout` y está disponible al entrar directamente o navegar mediante Inertia a cualquiera de los módulos del tenant. El contenido de esos módulos mantiene Bootstrap; el dashboard conserva sus componentes refinados.

## Revisión visual

- [Vista previa actualizada](http://localhost:8184/dashboard).
- [Galería antes/después](http://localhost:8185/comparison.html) · [archivo guardado](../.playwright-mcp/bottom-navigation/comparison.html).
- Capturas: [móvil 320](../.playwright-mcp/bottom-navigation/after-dashboard-320.png), [375](../.playwright-mcp/bottom-navigation/after-dashboard-375.png), [390](../.playwright-mcp/bottom-navigation/after-dashboard-390.png), [430](../.playwright-mcp/bottom-navigation/after-dashboard-430.png), [767](../.playwright-mcp/bottom-navigation/after-dashboard-767.png).
- [Drawer móvil](../.playwright-mcp/bottom-navigation/drawer-390.png), [tablet 768](../.playwright-mcp/bottom-navigation/after-dashboard-768.png), [escritorio 992](../.playwright-mcp/bottom-navigation/after-dashboard-992.png), [1366](../.playwright-mcp/bottom-navigation/after-dashboard-1366.png) y [1920](../.playwright-mcp/bottom-navigation/after-dashboard-1920.png).

La vista previa es un proxy local de solo lectura: sirve los assets compilados del checkout y reenvía GET al Laravel local existente. Comparte la sesión de `localhost`; bloquea POST y no sustituye la autenticación. El proxy temporal de 8184 refleja también `viewport-fit=cover` del Blade local. No se modificaron imágenes Docker, configuración de producción ni base de datos. Los servidores 8181/8182/8183 de las fases anteriores se conservaron.

Las comparativas propias de esta fase usan el mismo tenant, fecha, datos vacíos y viewport. Se guardaron antes de editar: Dashboard 320×568, 390×844, 768×1024 y 1366×768; POS 390×844. El material de Fase 2.2 permanece en sus directorios originales.

## Implementación

- Administrador: Inicio, Productos, Vender, Ventas y Menú. Cajero: Inicio, Vender, Ventas y Menú. Historial y tickets están autorizados por las rutas existentes y limitados a las ventas propias del cajero por VentaController.
- `tenantNavigation()` centraliza destinos, condiciones de rol y coincidencias. AppLayout lo calcula una vez y pasa el resultado a sidebar, drawer y barra. `/ventas` solo selecciona Vender; historial y tickets seleccionan Ventas; creación/edición de productos seleccionan Productos; las demás secciones seleccionan Menú.
- `aria-current="page"` se reserva a la coincidencia exacta del destino con el pathname. Los índices permanecen seleccionados visualmente en rutas hijas sin declararse página actual.
- Barra fija de 68 px más safe area; blanco, DM Sans, Lucide, borde sutil, tinta de marca calculada, indicador activo de 24×3 px y peso 700. Vender tiene un icono de marca contenido, sin aumentar la altura.
- Un solo drawer accesible, mediante UiOverlay y su dialog nativo. Un solo disparador visible: Menú en móvil o botón del header en tablet; sidebar desde 992 px. Al cambiar de rango se cierra el drawer y se restaura el foco en un control visible.
- Reserva inferior en todas las páginas, márgenes de scroll y notificaciones situadas encima de la barra. Modales Bootstrap/UiOverlay y bienvenida heredada ocultan e inhabilitan la barra. El drawer nativo la deja debajo del backdrop e inerte.
- Durante captura en campos de dispositivos táctiles o una reducción detectada de VisualViewport, la barra se oculta sin cambiar el espacio reservado. Su reaparición no salta el contenido. Reduced motion e impresión contemplados.
- Las métricas móviles conservan una columna por debajo de 480 px: mínimo de fila 76 px y gap de 8 px. A 390 px las cuatro filas vacías ahorran 60 px. Se preservan importes indivisibles y el tratamiento de valores largos. No se editó Dashboard.vue en esta fase.

## Matriz de resoluciones y rutas

Cada resolución se probó en las **13 rutas** siguientes: `/dashboard`, `/ventas`, `/ventas/historial`, `/productos`, `/caja`, `/usuarios`, `/reportes`, `/configuracion`, `/catalogos/categorias`, `/catalogos/marcas`, `/catalogos/proveedores`, `/productos/create` y `/usuarios/create`. Total: **143 combinaciones reales**, entrando directamente a cada página; además se verificó la navegación entre módulos mediante enlaces reales de Inertia.

| Viewport | Navegación visible | Rutas comprobadas | Resultado |
|---|---|---|---|
| 320 × 568 | Bottom Navigation | 13/13 | Pasa |
| 375 × 667 | Bottom Navigation | 13/13 | Pasa |
| 390 × 844 | Bottom Navigation | 13/13 | Pasa |
| 430 × 932 | Bottom Navigation | 13/13 | Pasa |
| 767 × 900 | Bottom Navigation | 13/13 | Pasa |
| 768 × 1024 | Header + drawer | 13/13 | Pasa |
| 991 × 900 | Header + drawer | 13/13 | Pasa |
| 992 × 900 | Sidebar | 13/13 | Pasa |
| 1024 × 768 | Sidebar | 13/13 | Pasa |
| 1366 × 768 | Sidebar | 13/13 | Pasa |
| 1920 × 1080 | Sidebar | 13/13 | Pasa |

Se comprobaron navegación y destino activo, un único control de Menú visible, etiqueta completa, tamaño de cada celda, sidebar, separación del final de main respecto de la barra y ausencia de overflow del documento. A 320 px las cinco celdas miden 60×56 px. Al final del scroll, el contenido de main queda antes de la barra en todos los casos móviles. Los formularios largos de creación fueron accesibles hasta el final. El contenido premium permanece limitado al dashboard de administrador.

Transiciones comprobadas en los límites 767→768 y 991→992. Orientación adicional 667×375: barra; 844×390: drawer de tablet, según ancho disponible. Barra fija comprobada durante scroll. [Resultados de la matriz](../.playwright-mcp/bottom-navigation/viewport-results.json).

La primera pasada detectó que la etiqueta activa Productos excedía ligeramente su celda a 320 px. Se amplió su reparto horizontal y la matriz completa pasó después. [Resultado inicial conservado](../.playwright-mcp/bottom-navigation/viewport-results-initial.json).

## Navegación y accesibilidad básica

35 comprobaciones de interacción aprobadas: apertura con Enter/Espacio, foco inicial en Cerrar, `aria-controls` correcto, ciclos Tab/Shift+Tab, fondo inerte, Escape, botón, backdrop, restauración de foco, cierre y foco al cambiar de breakpoint, liberación de scroll, navegación Inertia real, selección de destinos, cierre al elegir rutas secundarias, modal de Caja, reduced motion, orientación, foco visible, barra fija e impresión. [Resultados](../.playwright-mcp/bottom-navigation/interaction-results.json).

Las visitas reales mediante Inertia incluyen Inicio, Productos, POS, historial, Caja, Usuarios, Reportes, Configuración y los tres catálogos. Un marcador en window confirmó continuidad de la visita sin recarga completa. El drawer se cierra y el scroll queda liberado al entrar en cada módulo. [Foco visible en Menú](../.playwright-mcp/bottom-navigation/focus-menu-320.png).

El modal existente de Ingresar efectivo se abrió y cerró sin enviar formularios: [captura](../.playwright-mcp/bottom-navigation/modal-caja-390.png). La barra queda oculta e inerte durante el modal y vuelve al cerrarlo. Esto valida convivencia con la navegación; no certifica la accesibilidad de todos los modales heredados.

Se midieron **60 pares de contraste** sobre estilos calculados: las nueve paletas existentes y blanco, negro y amarillo adicionales. Texto activo/inactivo: umbral 4.5:1; iconos y contorno de Vender: 3:1. Mínimo de etiquetas activas: 5.02:1; texto inactivo: 5.48:1; mínimo del icono activo sobre superficie neutra: 4.44:1. Todos pasan su umbral. [Pares](../.playwright-mcp/bottom-navigation/contrast-pairs.json) y [resultado del comprobador de contraste](../.playwright-mcp/bottom-navigation/contrast-results.txt). La selección incluye forma y peso, además del color.

También se ejecutaron 20 casos de coincidencia de rutas y filtrado por rol, incluyendo query, slash final, rutas con prefijos similares, creación, edición y ticket. Las rutas de edición se verificaron mediante esa lógica; no había productos existentes para abrir una edición real.

## Estados simulados y límites de la evidencia

Las siguientes muestras sustituyen respuestas GET exclusivamente en el navegador, retirando las interceptaciones al terminar. No escriben datos ni prueban autorizaciones Laravel con otra sesión.

- **Cajero:** no hay cuentas con ese rol en el entorno; se sustituyó el prop de rol en una respuesta Inertia. La barra muestra cuatro destinos y el drawer solo Inicio, Vender, Ventas, Caja actual y salir. [Barra simulada](../.playwright-mcp/bottom-navigation/simulated-cashier-320.png), [drawer simulado](../.playwright-mcp/bottom-navigation/simulated-cashier-drawer-320.png).
- **Ticket:** no existen ventas reales. Una respuesta Inertia simulada de `/ventas/999/ticket` selecciona Ventas sin `aria-current` en el enlace al historial. [Captura simulada](../.playwright-mcp/bottom-navigation/simulated-ticket-320.png).
- **POS:** un producto simulado se añadió al carrito local para comprobar que Cobrar y Limpiar quedan alcanzables al final del scroll. No se pulsó Cobrar ni se abrió una confirmación comercial. [Carrito simulado](../.playwright-mcp/bottom-navigation/simulated-pos-cart-320.png).
- **Importes, tabla, banners y avisos:** importe `$123,456,789.90` sin clipping, tabla desplazable con flechas de teclado, banners de impersonación/anuncio en su flujo y toast situado encima de la barra. [Banners simulados](../.playwright-mcp/bottom-navigation/simulated-banners-money-320.png), [importe simulado](../.playwright-mcp/bottom-navigation/simulated-money-320.png).
- **Teclado virtual:** una reducción artificial de VisualViewport con el buscador enfocado oculta/inactiva la barra; al restaurarla reaparece. Es comprobación del mecanismo, no una reproducción fiel del teclado de iOS/Android.

[11 comprobaciones con fixtures](../.playwright-mcp/bottom-navigation/simulated-results.json), sin errores JavaScript. La matriz real tampoco produjo errores JavaScript ni solicitudes distintas de GET.

Pendientes: dispositivo físico con safe areas, teclados virtuales y barras del navegador; Safari/Firefox, lector de pantalla y zoom real del navegador; sesión real de cajero y tickets reales cuando estén disponibles. No se declara certificación integral de accesibilidad. Las tablas y controles heredados mantienen su diseño; su rediseño está fuera de esta fase.

## Archivos de esta fase

| Archivo | Cambio |
|---|---|
| [AppLayout.vue](../resources/js/Layouts/AppLayout.vue) | Navegación compartida independiente del contenido premium, único estado de drawer y cálculo de rutas, integración de barra y comportamiento entre breakpoints |
| [SidebarNav.vue](../resources/js/Components/SidebarNav.vue) | Reutiliza la plantilla premium existente; recibe navigation, conserva grupos y adapta identidad/opciones al rol |
| [UiBottomNav.vue](../resources/js/Components/Ui/UiBottomNav.vue) | Nuevo componente presentacional reutilizable con Links Inertia y evento de Menú |
| [tenantNavigation.js](../resources/js/Navigation/tenantNavigation.js) | Fuente única de destinos, filtrado de rol y selección exacta/por sección |
| [useMobileNavigationVisibility.js](../resources/js/Composables/useMobileNavigationVisibility.js) | Coordinación con overlays y señales de captura/viewport; limpieza al desmontar |
| [UiIcon.vue](../resources/js/Components/Ui/UiIcon.vue) | House, CirclePlus y Menu verificados; conserva el resto del vocabulario Lucide |
| [UiOverlay.vue](../resources/js/Components/Ui/UiOverlay.vue) | Reenvío explícito de atributos al dialog y fallback de foco cuando cambia el control visible |
| [premium.css](../resources/css/premium.css) | Barra, integración de navegación, safe areas, scroll, notificaciones y ajuste acotado de densidad móvil |
| [app.blade.php](../resources/views/app.blade.php) | `viewport-fit=cover` sin limitar zoom |
| [UI_DESIGN_SYSTEM.md](../UI_DESIGN_SYSTEM.md) | Alcance actual, arquitectura responsive, API, roles, tokens/estados, capas, overlays/teclado y estado implementado/futuro |
| Este informe y [evidencias](../.playwright-mcp/bottom-navigation/) | Capturas, galería antes/después, matriz y guiones/resultados de Playwright MCP |

El checkout ya contenía cambios sin commit de fases anteriores, package/lockfile, skills y evidencias. Se preservaron. No se añadieron dependencias ni se modificaron Dashboard.vue, StatCard, WelcomeModal, theme.js, SuperAdminLayout, rutas, controladores, modelos, contratos de Inertia o cálculos comerciales durante esta fase.

`npm run build` y `git diff --check` pasan. Se mantiene el aviso anterior de Browserslist desactualizado, sin actualizar dependencias ajenas al alcance. La validación usa Chromium a través de Playwright MCP con el ejecutable ya instalado: el MCP predeterminado no encontró Chrome y se utilizó un cliente MCP local con `--executable-path`; no se instaló otro navegador ni una dependencia de pruebas en el proyecto.

La implementación se detiene aquí para revisión visual. La navegación del tenant está integrada; el contenido de los otros módulos y la extensión a SuperAdmin quedan para fases posteriores con aprobación del propietario.
