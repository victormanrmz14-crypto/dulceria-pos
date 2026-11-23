# Dulcería POS · Design System

## Alcance y activación

La navegación responsive es compartida por todos los módulos del tenant mediante `AppLayout`. El contenido premium sigue siendo optativo: [Dashboard.vue](resources/js/Pages/Dashboard.vue) lo activa únicamente para `admin`. El contenido Bootstrap de los demás módulos y del dashboard de cajero conserva su presentación. `StatCard` y `WelcomeModal` mantienen sus variantes originales por defecto. No cambia la asignación ni la comprobación de permisos del servidor.

Los componentes premium usan raíces `.pos-premium` locales: sidebar, header, Bottom Navigation y overlays reciben los tokens de marca sin activar esos estilos sobre el contenido heredado. `.tenant-shell` controla exclusivamente el espacio de navegación, el offset de notificaciones y el margen de scroll; `html:has(.tenant-shell)` reserva espacio de scroll en móvil. Los overlays teletransportados llevan su propia raíz premium. No se cambian resets, variables globales de Bootstrap ni Tailwind. La carga del CSS del piloto después de navegar no debe alterar pantallas vecinas.

La dirección visual usa superficies neutras, navegación discreta, métricas legibles y acentos de marca contenidos. Conserva la identidad de cada negocio sin teñir todas las superficies.

## Tokens implementados

Fuente de verdad: [premium.css](resources/css/premium.css). Tema de marca: [theme.js](resources/js/Components/Ui/theme.js).

| Token | Valor / función |
|---|---|
| `--p-canvas` | `#f7f8fa`, fondo del área de trabajo |
| `--p-surface` | `#ffffff`, tarjetas y header |
| `--p-sidebar-surface` | `#fcfcfd`, fondo neutro de navegación |
| `--p-muted-surface` | `#f0f1f4`, hover y disabled |
| `--p-text` / `--p-muted` | `#202126` / `#666973` |
| `--p-border` | `#dee0e5`, separadores y contornos |
| `--p-brand` | `tenantConfig.colores.primario`; fallback `#8B0000` |
| `--p-on-brand` | Texto blanco u oscuro elegido por contraste mínimo 4.5:1 |
| `--p-brand-ink` | Marca apta para texto sobre blanco, u oscuro para colores claros |
| `--p-brand-hover-mix` | Negro o blanco para aumentar el contraste en hover |
| `--p-warning` / `--p-warning-bg` | `#8a5209` / `#fff6df` |
| `--p-success` / `--p-danger` / `--p-info` | `#166534` / `#b42318` / `#1d4e89` |
| `--p-font-sans` | DM Sans existente; fallback system-ui, sans-serif |
| `--p-text-xs` / `--p-text-sm` / `--p-text-base` / `--p-text-title` | 12 / 13 / 14 / 26 px |
| `--p-space-1/2/3/4/5/6/8` | 4 / 8 / 12 / 16 / 20 / 24 / 32 px |
| `--p-radius-control/panel/overlay` | 6 / 10 / 14 px |
| `--p-shadow-control` | `0 1px 2px #17192314` |
| `--p-shadow-overlay` | `0 24px 80px #15182233` |

Las cantidades usan cifras tabulares y no se dividen entre líneas; título de página 26 px (24 en móvil), métricas fluidas hasta 30 px y secciones 14 px semibold. Las etiquetas de métricas usan 13 px en escritorio; estados, unidades y moneda usan 12 px. Los ejes de la gráfica usan 11 px con DM Sans. En tarjetas estrechas la cifra se adapta al ancho del contenedor; los valores largos tienen una variante de al menos 18 px y pasan a una línea propia en móvil. Se mantiene la carga de fuentes existente, sin nuevas fuentes remotas.

La configuración de colores completa, nombre y logo sigue funcionando. El piloto reserva el primario para acción principal, selección y gráfica; no reemplaza las opciones guardadas de acento, fondos o recibos, ni sus usos fuera del piloto. El logo conserva sus proporciones con `object-fit: contain`. Los nombres largos se ajustan en sidebar y breadcrumb; el header puede aumentar su altura para conservar el texto completo.

## Componentes y estados

| Componente | API / responsabilidad |
|---|---|
| [UiIcon](resources/js/Components/Ui/UiIcon.vue) | `name`, `size`; imports explícitos de `@lucide/vue@1.77.0`, SVG decorativo oculto a tecnologías asistivas |
| [UiButton](resources/js/Components/Ui/UiButton.vue) | `href`, `variant`, `type`, `disabled`, `loading`; Link para navegación y button para acciones; altura mínima 44 px |
| [UiEmptyState](resources/js/Components/Ui/UiEmptyState.vue) | `icon`, `title`, `description` y slot; explica ausencia de datos sin inventar métricas |
| [UiOverlay](resources/js/Components/Ui/UiOverlay.vue) | `open`, `title`, `variant`, `theme`, `focusFallback` y atributos HTML reenviados al dialog, evento `close`; dialog nativo, foco inicial, contención de Tab, Escape, backdrop, bloqueo de scroll y restauración del foco |
| [StatCard](resources/js/Components/StatCard.vue) | Variante `premium`; preserva `etiqueta`, `valor`, `sufijo`, `href`; añade `icon` y `attention` |
| [SidebarNav](resources/js/Components/SidebarNav.vue) | Navegación premium permanente del tenant, destinos/roles centralizados, grupos desplegables accesibles; AppLayout cierra el drawer por navegación |
| [WelcomeModal](resources/js/Components/WelcomeModal.vue) | Variante `premium`; mismo flash de bienvenida y cierre local, sin persistencia adicional |

Botón primario: color del tenant, texto de contraste calculado y hover más contrastado. Secundario: blanco con borde. Disabled/loading: button deshabilitado, `aria-busy` y spinner Lucide para loading. Foco visible de 2 px con separación de 3 px. Estado de inventario bajo: icono de advertencia y texto específico; cero no se presenta como una alerta. Los errores y mensajes existentes conservan su comportamiento.

Tarjetas: borde de 1 px y jerarquía por tamaño/espaciado, sin sombras dominantes. Navegación: marcador activo lateral, tinte suave, grupos separados y submenús con guía vertical, `aria-controls` único y transición breve. Tabla: encabezados semánticos, cifras alineadas a la derecha, método de pago con texto y SVG, folio enlazado al ticket existente; scroll local con foco de teclado en móviles, ancho mínimo de 660 px y filas compactas que conservan enlaces de 44 px. Gráfica: color de marca legible, eje desde cero con etiquetas monetarias compactas, tooltip monetario completo y alternativa de datos mediante `details`. El lienzo usa 220 px de alto y queda limitado al ancho disponible durante el resize. Con importes nulos muestra un estado vacío de 144 px de altura mínima.

Los estados vacíos usan icono de 40 px y un grupo de título/descripción contiguo. El vacío de ventas recientes se alinea al inicio y tiene altura mínima de 112 px. Los importes largos del ranking pasan debajo del nombre en móviles, sin partir cifras ni recortar nombres.

Formularios: no se reemplaza ningún campo en este piloto, porque el dashboard no necesita capturar datos. Para la siguiente fase se propone etiqueta asociada, ayuda y error con `aria-describedby`, `aria-invalid`, foco en primer error y controles de al menos 44 px. El componente existente de formularios y el modal general permanecen intactos; estas reglas no constituyen una migración ya realizada.

Transiciones de 120–150 ms para navegación, bordes y overlays; sin movimiento de tarjetas ni contadores animados. `prefers-reduced-motion` desactiva animaciones y transiciones del piloto.

## Composición y responsive

Sidebar blanca de 232 px, header de 56 px y contenido centrado de máximo 1440 px. Saludo y fecha preceden al título; acciones de Caja y POS quedan separadas de los cuatro indicadores. Después aparecen ingresos semanales, productos destacados y ventas recientes. El último corte sigue visible cuando existe.

| Rango | Indicadores | Navegación | Análisis |
|---|---|---|---|
| Menos de 360 px | 1 columna, tarjetas horizontales | Bottom Navigation + drawer secundario | 1 columna, acciones apiladas |
| 360–479 px | 1 columna, tarjetas horizontales | Bottom Navigation + drawer secundario | 1 columna |
| 480–767 px | 2 columnas | Bottom Navigation + drawer secundario | 1 columna |
| 768–991 px | 2 columnas | Drawer modal | 1 columna |
| 992–1199 px | 2 columnas | Sidebar | 1 columna |
| Desde 1200 px | 4 columnas | Sidebar | Gráfica y ranking, proporción 1.65:1 |

En la segunda iteración la proporción de análisis en escritorio es 1.65:1. Cuando tanto gráfica como ranking están vacíos, se usan dos columnas desde 768 px con proporción 1.4:1 hasta 1199 px; con datos se conserva el apilado en ese rango. Esto reduce el espacio dedicado a ausencia de datos sin comprimir la gráfica poblada.

Main: 32 px en escritorio, 24 en tablet y 16 horizontales en móvil. La tabla puede desplazarse dentro de su región; el documento completo no debe desbordarse. Acciones principales visibles antes del primer scroll en las seis resoluciones acordadas, con altura de viewport de 900 px.

## Conservación funcional

Se conservan todos los props de Inertia, las cuatro métricas, filtros de stock, enlace a Caja, último corte, POS, historial, últimos siete días, unidades e importes del ranking, cajero, hora, método de pago y enlace a cada ticket. El contenido del dashboard de cajero conserva su plantilla original; su navegación compartida se adapta al rol. No se cambian controladores, modelos, migraciones, rutas, autenticación, permisos ni endpoints.

Dependencia adicional única: `@lucide/vue@1.77.0`, fijada en package y lockfile. Sin Tailwind global, shadcn-vue ni instalación de herramientas de test adicionales.

## Historial · Validación y evidencias de la primera iteración

Comparativas: [.playwright-mcp/premium-pilot/comparison.html](.playwright-mcp/premium-pilot/comparison.html). Capturas reales antes y después a 320, 375, 768, 1024, 1366 y 1920 px, altura de viewport 900 px y captura de página completa. Las capturas iniciales son del 22 de noviembre y las finales del 23; la fecha visible y la ventana móvil de siete días cambian naturalmente.

El tenant de auditoría no tiene operaciones comerciales. Las pruebas adicionales con tabla, ranking, gráfica, último corte, bienvenida y paletas se realizan sustituyendo respuestas únicamente en el navegador, sin escribir registros. Sus capturas se identifican como `simulated-*`; no prueban una venta ni la emisión real de un ticket.

Se usa una vista previa temporal de solo lectura en `http://localhost:8181/dashboard`: sirve los assets compilados del checkout y reenvía GET al Laravel local existente, usando la sesión normal. No altera configuración ni imágenes Docker, base de datos o volúmenes; bloquea POST. La aplicación original de `http://localhost` sigue sirviendo la imagen anterior hasta una futura actualización de assets/imagen. La vista previa requiere una sesión de localhost en ese navegador y no es un mecanismo de autenticación alternativo.

Compilación con `npm run build`. No hay script configurado de lint ni pruebas unitarias frontend, y PHPUnit no está instalado en host ni en el contenedor de producción local. Se validan compilación Vue/Vite, diagnósticos del editor y comportamiento mediante Playwright. Browserslist advierte que su catálogo está desactualizado; no se actualizan dependencias fuera de la autorizada.

El contenido del piloto queda pendiente de revisión visual. La navegación compartida se implementa en Fase 2.3; extender el diseño del contenido a otros módulos y formularios requiere aprobación posterior.

### Resultados comprobados en la primera iteración

- Seis anchos: documento sin overflow, cuatro indicadores presentes y acciones visibles antes del primer scroll; columnas 1 / 2 / 2 / 2 / 4 / 4 respectivamente.
- Drawer: Tab y Shift+Tab contenidos, Escape y backdrop cierran, foco devuelto al botón, cierre al pasar a escritorio y liberación del scroll al navegar. Enlace de salto al contenido y reduced motion comprobados.
- Bienvenida: foco contenido y Escape con flash simulado. Nombre comercial largo a 320 px sin overflow.
- Nueve paletas: contraste del botón mínimo 5.02:1 en reposo y 5.73:1 en hover. También se probó un color claro adicional. Sin guardar configuración.
- Comprobación adicional del cálculo de texto sobre marca con 4096 colores muestreados: todos alcanzan 4.5:1; mínimo observado 4.5012:1.
- Tabla, ranking, gráfica y último corte con datos simulados en las seis resoluciones. Enlaces de ticket verificados por destino; no hubo un ticket real disponible para abrir.
- Productos, Usuarios y Configuración a 1366 px: capturas antes/después idénticas byte a byte. POS conserva estilo y estructura; cambia únicamente la fecha visible entre días. Navegación desde el piloto mediante Inertia sin aplicar la raíz premium a Productos.
- Compilación correcta y diagnósticos del editor sin errores. Sin excepciones JavaScript durante el recorrido. `git diff --check` correcto.
- Conexión local e identidad del usuario temporal verificadas. Al cierre: 3 usuarios, 2 tenants, 0 productos, 0 ventas y 0 cortes. No se crearon cuentas adicionales ni operaciones comerciales.

### Archivos de la primera implementación

- [Dashboard.vue](resources/js/Pages/Dashboard.vue): composición del piloto, gráfica y tabla; conserva la rama de cajero.
- [AppLayout.vue](resources/js/Layouts/AppLayout.vue): variante optativa, tema y drawer accesible.
- [SidebarNav.vue](resources/js/Components/SidebarNav.vue), [StatCard.vue](resources/js/Components/StatCard.vue) y [WelcomeModal.vue](resources/js/Components/WelcomeModal.vue): variantes premium; valores por defecto heredados.
- [UiIcon.vue](resources/js/Components/Ui/UiIcon.vue), [UiButton.vue](resources/js/Components/Ui/UiButton.vue), [UiEmptyState.vue](resources/js/Components/Ui/UiEmptyState.vue), [UiOverlay.vue](resources/js/Components/Ui/UiOverlay.vue) y [theme.js](resources/js/Components/Ui/theme.js): componentes mínimos y cálculo de contraste.
- [premium.css](resources/css/premium.css): tokens y estilos aislados.
- [package.json](package.json) y [package-lock.json](package-lock.json): única dependencia nueva autorizada.
- Este documento y las evidencias locales de la comparativa. Los directorios de skills y evidencias previas ya estaban sin seguimiento; no se modificaron ni limpiaron.

## Fase 2.2 · Refinamiento visual

La segunda iteración conserva el alcance optativo del dashboard administrador y todos sus contratos funcionales. No añade dependencias. Las reglas actuales de composición, tipografía, navegación y estados se describen arriba y sirven como referencia del piloto, pendientes de aprobación visual antes de extenderlas.

Entrega y pruebas: [informe de refinamiento](docs/premium-dashboard-refinement-2026-11-23.md). Comparativas entre primera y segunda iteración: [galería local](http://localhost:8183/comparison.html) y [archivo guardado](.playwright-mcp/premium-refinement/comparison.html). La vista previa del dashboard sigue en [localhost:8181](http://localhost:8181/dashboard), con datos reales y sin interceptaciones del navegador al cierre.

Se probaron 320, 375, 768, 1024, 1366 y 1920 px con datos vacíos y simulados; límites de breakpoints; navegación real; teclado, foco, drawer y reduced motion; nueve paletas con 72 pares de contraste; nombres largos, logo e importes de nueve cifras. El informe distingue estos resultados de pruebas aún pendientes en dispositivos físicos, otros motores y lector de pantalla.


## Fase 2.3 · Navegación responsive del tenant (aprobada como base para Fase 2.4)

### Arquitectura y alcance

`AppLayout` siempre compone el header, `SidebarNav`, un único `UiOverlay` para el drawer y `UiBottomNav`. La prop `premium` solo activa la superficie y composición premium del contenido del dashboard. El CSS se importa desde el layout; entrar directamente en un módulo heredado carga también la navegación, sin depender de una visita previa a Inicio. Cada página sigue montando su propio layout: la continuidad es visual entre visitas Inertia; no se promete conservar una instancia de AppLayout ni el estado abierto del drawer.

| Ancho CSS | Patrón implementado | Control de Menú |
|---|---|---|
| <768 px | Barra inferior fija + drawer secundario | Solo Menú en la barra |
| 768–991.98 px | Drawer | Solo botón del header, 44 × 44 px |
| ≥992 px | Sidebar, 232 px | Sin botón de drawer ni barra inferior |

Los límites `md=768` y `lg=992` coinciden con Bootstrap 5. Se cierra el drawer al cruzar cualquiera de esos límites y al navegar. Su estado pertenece al layout. `UiOverlay` usa dialog modal nativo: fondo inerte, autofocus en Cerrar, contención de Tab/Shift+Tab, Escape, backdrop y restauración de foco. `focusFallback` elige el control visible del nuevo breakpoint o el enlace activo de sidebar si desapareció el disparador original. El dialog recibe el id indicado por `aria-controls`.

### Destinos y adaptación por rol

Fuente única: [tenantNavigation.js](resources/js/Navigation/tenantNavigation.js). `tenantNavigation(role, url)` devuelve `items`, `primary`, `bottom` y `sectionLabel`; cada destino incluye `id`, `href`, `icon`, `label`, `active` y `current`. AppLayout calcula una única instancia computed y pasa sus resultados a sidebar, drawer y barra. SidebarNav recibe la prop obligatoria `navigation: Object`; no vuelve a resolver rutas ni roles de destinos. Sidebar y barra consumen esas mismas definiciones, visibilidad y coincidencias. El drawer reutiliza SidebarNav; no mantiene otra lista de enlaces.

| Rol | Barra inferior | Drawer / sidebar |
|---|---|---|
| Administrador | Inicio, Productos, Vender, Ventas, Menú | Inicio, Vender, Ventas; Caja actual/Cortes históricos; Productos, Usuarios, Reportes, Catálogos; Configuración y salir |
| Cajero | Inicio, Vender, Ventas, Menú | Inicio, Vender, Ventas; Caja actual y salir |

Historial está fuera de `solo.admin`; VentaController limita ventas y tickets del cajero a los propios. Se conserva Cortes históricos solo para admin, como en la navegación anterior. Esto controla enlaces; Laravel conserva la autoridad sobre acceso. No se modifican permisos ni middleware. No existe cuenta de cajero en el entorno revisado: su evidencia visual es simulada en navegador, no una prueba de autorización del servidor.

`navigationPath()` ignora query/hash y normaliza slash final. Inicio coincide exactamente con `/dashboard`; Vender exactamente con `/ventas`. Ventas coincide con `/ventas/historial` y `/ventas/{id}/ticket`. Productos mantiene selección en su árbol de creación/edición. Las demás secciones y rutas sin destino primario seleccionan Menú. Sus botones no tienen `aria-current`; la selección se anuncia como «sección actual». Los enlaces llevan `aria-current="page"` solo si la URL de destino coincide exactamente con el pathname actual; un ticket o formulario selecciona su sección visualmente sin declarar que el enlace al índice es la página actual.

### Especificaciones visuales implementadas

| Propiedad | Valor |
|---|---|
| Altura móvil | 68 px + `env(safe-area-inset-bottom, 0px)` |
| Superficie y borde | `--p-surface` blanco, borde superior 1 px `--p-border`, sin sombra |
| Espaciado | 5 px superior, 6 px inferior + safe area; laterales ≥6 px o safe area; gap 2 px |
| Celdas | Flex con reparto equitativo; 60 × 56 px a 320 px para admin, mínimo táctil 44 × 44 |
| Tipografía | DM Sans, 12 px / 18 px, peso 500; activo 700; etiquetas visibles sin truncado |
| Iconos | House, Package, CirclePlus, ReceiptText y Menu, exports verificados en `@lucide/vue@1.77.0`; 21 px, trazo 1.75 |
| Contenedor de icono | 40 × 28 px, radio 8 px; Vender con marca, texto calculado y borde contrastado |
| Activo | `--p-brand-ink`, peso 700, indicador superior de 24 × 3 px y superficie neutra del icono |
| Interacción | Hover neutro; pressed con superficie neutra y sombra interior sutil; foco de 2 px, offset interior -2 px |
| Transición | Color/superficie 120 ms; reduced motion la desactiva |
| Capas | Barra z-index 1020; backdrop Bootstrap 1050 / modal 1055; notificaciones heredadas 1080; dialog nativo en top layer |

`premiumTheme()` sigue calculando marca, texto y tinta accesibles; no se guardan nuevos colores. Cada raíz de navegación recibe el tema para que una marca clara use la tinta oscura calculada. El borde de Vender permite reconocer su contorno incluso con marca blanca. La selección tiene indicador y peso además del color.

### Contrato técnico de UiBottomNav

| Entrada / evento | Contrato |
|---|---|
| `items: Array` requerido | Destinos filtrados por rol de `navigation.bottom`, máximo cinco; Menú usa `id: 'menu'` sin href |
| `drawerOpen: Boolean` | Estado de apertura reflejado en `aria-expanded` |
| `drawerId: String` requerido | Id real del dialog para `aria-controls` |
| `suppressed: Boolean` | Oculta con visibility y desactiva foco/interacción mediante inert; mantiene espacio reservado |
| `open-menu` | Evento sin payload; AppLayout abre su drawer existente |
| Atributos HTML | Clase/style se aplican a la raíz nav, incluido el tema del tenant |

Los enlaces usan `Link` de Inertia. La barra no calcula rutas ni permisos ni importa contenido de módulos. Para reutilizarla, un módulo usa AppLayout sin cambios en sus props de Inertia. No debe crear otro menú, otra barra fija ni otra copia de destinos.

### Contenido, overlays y teclado

`.tenant-shell` reserva la altura completa de barra y safe area con padding inferior; no recubre el final de formularios, carrito ni botones. Se incluyen márgenes de scroll para controles y scroll-padding del documento. Las notificaciones se sitúan encima de la barra en móvil (`top:auto`, `bottom: espacio + 16px`, ancho máximo `100vw - 32px`); conservan su posición superior en tablet/escritorio. Los banners de anuncios e impersonación conservan su flujo y comportamiento.

[useMobileNavigationVisibility](resources/js/Composables/useMobileNavigationVisibility.js) observa modales Bootstrap `.modal.show`, bienvenida heredada, offcanvas y dialogs activos distintos del drawer. Durante ellos la barra queda oculta e inerte, reapareciendo al cerrar. El drawer nativo conserva la barra debajo de su backdrop y la vuelve inerte de forma nativa. El composable elimina listeners, MutationObserver y RAF al desmontarse.

Con foco en un campo editable en un dispositivo de puntero coarse sin hover, la barra se oculta mientras dura la captura. También se oculta si VisualViewport reduce su altura respecto al layout más de 150 px con un editable enfocado y escala ≤1.05. No se fuerza su posición encima del teclado ni se interpreta pinch zoom como teclado. Se preserva el padding para evitar saltos del contenido. Son señales heurísticas: teclado virtual real, barras del navegador, safe areas y cambios de orientación requieren comprobación física. El HTML añade `viewport-fit=cover`; no se limita el zoom. En impresión se eliminan barra y reserva inferior.

### Refinamiento acotado del dashboard

Se conserva una columna por debajo de 480 px para proteger importes completos. La fila horizontal pasa de 88 a 76 px de mínimo con padding vertical de 12 px; el gap móvil pasa de 12 a 8 px. A 390 px con las cuatro métricas vacías reduce 60 px el bloque. No se modifican Dashboard.vue, tarjetas, gráficas, ranking, tablas ni estados vacíos en esta fase. Los importes largos siguen usando su composición propia de la Fase 2.2.

### Estado y siguiente extensión

- **Implementados:** navegación compartida por rol; sidebar premium; drawer accesible; barra reutilizable; selección de rutas; espacio inferior; coordinación con overlays/teclado; tema y adaptación responsive.
- **Patrones conservados de Fase 2.2:** dashboard admin, métricas, gráfica, ranking, tabla desplazable y vacíos.
- **Aprobada como base:** integración y estética de Fase 2.3, según la solicitud de Fase 2.4. Esta aprobación no se extiende al nuevo contenido del POS.
- **Pendientes:** comprobación en dispositivos físicos, Safari/Firefox y lector de pantalla; sesión real de cajero y ticket real cuando existan datos autorizados.
- **Futuras fases, no implementadas:** contenido premium de POS, Productos, Caja, Usuarios, Reportes, Configuración y Catálogos; componentes de formularios; navegación de SuperAdmin. Para SuperAdmin se podría reutilizar UiBottomNav/UiOverlay con otra fuente de destinos y rol, integrándolos posteriormente en su propio layout. SuperAdminLayout permanece sin cambios.

Entrega, matriz, capturas y limitaciones: [informe Fase 2.3](docs/bottom-navigation-2026-11-23.md). Vista previa de solo lectura: [localhost:8184/dashboard](http://localhost:8184/dashboard), assets de este checkout y respuestas GET del Laravel local con su sesión existente. La galería comparativa de esta fase se entrega por separado del dashboard.
