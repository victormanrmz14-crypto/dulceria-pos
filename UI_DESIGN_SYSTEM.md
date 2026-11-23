# Dulcería POS · Design System

> Archivo de referencia: las capturas, resultados de diagnóstico y vistas previas temporales se retiraron en la Fase 2.5. La aplicación actual se consulta en [http://localhost](http://localhost). Se conservan aquí los patrones, resultados históricos y limitaciones.

## Alcance y activación

La navegación responsive es compartida por todos los módulos del tenant mediante `AppLayout`. El contenido premium sigue siendo optativo: [Dashboard.vue](resources/js/Pages/Dashboard.vue) lo activa únicamente para `admin`; [Pos.vue](resources/js/Pages/Ventas/Pos.vue) lo activa para ambos roles del tenant en Fase 2.4; las vistas de [Productos](resources/js/Pages/Productos/Index.vue) lo activan en Fase 2.6 dentro de sus permisos existentes; Caja y Cortes lo activan en Fase 2.7. El contenido Bootstrap de los demás módulos y del dashboard de cajero conserva su presentación. `StatCard` y `WelcomeModal` mantienen sus variantes originales por defecto. No cambia la asignación ni la comprobación de permisos del servidor.

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

Comparativas: .playwright-mcp/premium-pilot/comparison.html (evidencia temporal retirada). Capturas reales antes y después a 320, 375, 768, 1024, 1366 y 1920 px, altura de viewport 900 px y captura de página completa. Las capturas iniciales son del 22 de noviembre y las finales del 23; la fecha visible y la ventana móvil de siete días cambian naturalmente.

El tenant de auditoría no tiene operaciones comerciales. Las pruebas adicionales con tabla, ranking, gráfica, último corte, bienvenida y paletas se realizan sustituyendo respuestas únicamente en el navegador, sin escribir registros. Sus capturas se identifican como `simulated-*`; no prueban una venta ni la emisión real de un ticket.

Se usa una vista previa temporal de solo lectura en `http://localhost:8181/dashboard`: sirve los assets compilados del checkout y reenvía GET al Laravel local existente, usando la sesión normal. No altera configuración ni imágenes Docker, base de datos o volúmenes; bloquea POST. En esa validación, la aplicación original de `http://localhost` todavía servía la imagen anterior. La Fase 2.5 actualiza las imágenes locales. La vista previa requiere una sesión de localhost en ese navegador y no es un mecanismo de autenticación alternativo.

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

Entrega y pruebas: [informe de refinamiento](docs/premium-dashboard-refinement-2026-11-23.md). Comparativas entre primera y segunda iteración: galería local (vista previa temporal retirada) y archivo guardado (evidencia temporal retirada). La vista previa del dashboard sigue en localhost:8181 (vista previa temporal retirada), con datos reales y sin interceptaciones del navegador al cierre.

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

Entrega, matriz, capturas y limitaciones: [informe Fase 2.3](docs/bottom-navigation-2026-11-23.md). Vista previa de solo lectura: localhost:8184/dashboard (vista previa temporal retirada), assets de este checkout y respuestas GET del Laravel local con su sesión existente. La galería comparativa de esta fase se entrega por separado del dashboard.

## Fase 2.4 · Punto de venta (implementada, pendiente de revisión y aprobación visual)

La navegación aprobada de Fase 2.3 permanece intacta. El POS adopta el tema premium mediante `AppLayout premium`: DM Sans, Lucide, superficies neutras y marca configurable. No se modifican tokens compartidos, dependencias, rutas ni otros módulos. Las referencias anteriores a formularios y POS pendientes describen el alcance histórico del piloto; esta sección registra exclusivamente la extensión actual al POS.

### Composición y jerarquía

Encabezado con título, fecha existente y acceso secundario al historial solo para administrador. Buscador con etiqueta visible, SVG Search de Lucide, campo de 16 px y 52 px de alto, estado de carga y región de estado. Filas de catálogo con nombre completo, existencias, precio final y unidad; toda la fila agrega el producto. Los precios usan tinta neutra y cifras tabulares; el acento se reserva para acciones y selección. Los nombres se ajustan sin truncamiento.

| Ancho | Catálogo y carrito | Navegación conservada |
|---|---|---|
| 320–359 px | Fila compacta sin iconos laterales; acceso al carrito bajo el buscador; panel modal inferior | Barra inferior |
| 360–767 px | Fila compacta con indicador Agregar; mismo panel inferior | Barra inferior |
| 768–991 px | Catálogo de una columna, botón de carrito y dialog centrado hasta 480 px | Drawer del header |
| 992–1199 px | Catálogo y carrito de 320 px, separación 16 px; se omiten iconos de fila para proteger nombres y precios | Sidebar |
| ≥1200 px | Catálogo flexible y carrito de 360 px, separación 24 px | Sidebar |

El acceso móvil al carrito es sticky dentro del contenido, con total y número de productos distintos. No agrega otra barra fija. El orden de teclado móvil es buscador, acceso al carrito y filas del catálogo. El panel de escritorio es sticky a 24 px. La lista del carrito tiene scroll local cuando supera `min(360px, 42dvh)` en escritorio o `34dvh` en móvil/tablet; los métodos y total permanecen fuera de ese scroll. El dialog completo puede desplazarse cuando la altura disponible es pequeña.

El panel móvil usa `100dvh`, safe area y ancho completo por debajo de 768 px; el dialog de confirmación conserva el ancho del overlay existente. Ambos reutilizan foco contenido, Escape, cierre por backdrop, fondo inerte y restauración de foco de `UiOverlay`. La visibilidad de la barra inferior sigue gestionada por el composable de Fase 2.3, sin modificaciones. No se fuerza el teclado virtual ni se bloquea el zoom.

### Componentes de presentación

| Componente | Responsabilidad y contrato |
|---|---|
| [PosCartPanel](resources/js/Components/Pos/PosCartPanel.vue) | `count`, total ya formateado y `theme`; slot con `desktop`. Posee únicamente apertura/cierre y media query de 992 px, con cleanup. Renderiza una sola instancia del checkout, inline o en `UiOverlay`; cierra al cruzar el breakpoint. |
| [PosCheckout](resources/js/Components/Pos/PosCheckout.vue) | Props de líneas, errores, importes y estado; modelos `payment` y `received`; eventos `remove`, `quantity`, `clear`, `confirm`. Presenta cantidades, subtotales, total, métodos y cambio. No calcula totales comerciales ni registra ventas. Conserva `v-model.number` y los atributos originales de cantidad/importe. |
| Componentes existentes reutilizados | `UiButton`, `UiEmptyState`, `UiIcon`, `UiOverlay`, `AppLayout` y su tema. No se cambia su implementación ni `ModalPos`, que mantiene sus consumidores heredados. |

Métodos de pago en fieldset con legend; botones con `aria-pressed`, borde, peso e indicador Lucide para selección. Cantidad y monto tienen label asociado, `aria-invalid` y error enlazado por `aria-describedby`. Inputs de al menos 44 px y texto de 16 px; métodos y cobro de 48 px. Borde de campo `#858a94`, contraste 3.47:1 sobre blanco; foco de 2 px en tinta de marca. Los errores conservan su texto y condiciones originales, con icono y `role="alert"`.

Total de 24–30 px, cifras completas y flex-wrap para importes largos. Confirmación con total dominante y método/recibido/cambio agrupados, sin emojis. Los vacíos reutilizan `UiEmptyState`; el carrito vacío presenta total cero y cobro deshabilitado. Los mensajes flash usan la variante premium existente del layout. Sin animaciones nuevas; se respeta reduced motion.

### Conservación y evidencia

El bloque de lógica desde `defineProps` hasta `procesar` permanece idéntico a la línea base, comprobado por comparación de texto. `Pos.vue` conserva props, búsqueda y debounce, carrito, cálculos, validaciones, selección de pagos y payload Inertia. No se corrigen problemas a granel. No se ejecutan ventas.

Las ocho resoluciones solicitadas se verifican con Playwright MCP en Chromium y fixtures aisladas. Informe: [Fase 2.4 y responsiveness check](docs/responsiveness-check-2026-11-23.md). Capturas y comparativa: .playwright-mcp/pos-premium/comparison.html (evidencia temporal retirada). La vista previa actual en localhost:8184/ventas (vista previa temporal retirada) usa solo datos simulados y bloquea envíos; sustituye temporalmente el servidor anterior de ese puerto, que no estaba activo en este entorno. No es una sesión Laravel ni valida autenticación o persistencia. Pendientes: revisión del propietario, dispositivos físicos/teclado virtual, Safari/Firefox y lector de pantalla.

## Fase 2.5 · Consolidación del entorno

Las fases visuales quedan versionadas en `refactor/premium-ui`, sin cambios adicionales de diseño. La fundación y el refinamiento del dashboard (2.1–2.2) se consolidan juntos; navegación (2.3) y POS (2.4) tienen commits separados. La revisión personal del POS sigue siendo necesaria; consolidar Git no constituye aprobación visual.

Se retiraron las evidencias y servidores temporales tras inventariarlos. Se conservan este documento, los informes históricos resumidos en contexto, el código, las skills y su lockfile. La configuración MCP local sigue disponible en `.vscode/mcp.json` y permanece ignorada por Git. Los artefactos futuros de `.playwright-mcp/` y los caches Python se ignoran.

El acceso habitual vuelve a ser [http://localhost](http://localhost), con los servicios Docker existentes y las imágenes de `app` y `nginx` actualizadas. Se utiliza `docker compose -f docker-compose.yml` para conservar el modo de ejecución existente; no se levanta Vite de este proyecto ni se ocupa el 5173 de otro proyecto. Los volúmenes de base de datos, Redis y archivos subidos permanecen intactos.

Detalles operativos, commits, inventario y puertos: [informe Fase 2.5](docs/environment-consolidation-2026-11-23.md).

## Fase 2.6 · Productos e inventario (implementada, pendiente de aprobación estética)

Se activan las superficies premium de listado, creación y edición de Productos. No se modifican navegación, tokens compartidos, dependencias, backend, permisos ni contenido de otros módulos. La composición conserva el propósito administrativo: consultar y mantener productos, precios y existencias.

### Listado y responsive

Encabezado con título, descripción y una acción principal de Nuevo producto. Filtros con etiquetas visibles, controles de 16 px y altura mínima de 46 px; el filtro Stock bajo tiene un label completo de al menos 44 px de alto como objetivo táctil. La búsqueda conserva su debounce de 350 ms, los filtros mantienen sus valores y la paginación sus URL y `preserve-scroll`.

| Ancho CSS | Presentación del inventario | Formularios |
|---|---|---|
| 320–479 px | Tarjetas de una columna; búsqueda, categoría y stock bajo en filas propias | Campos en una columna |
| 480–599 px | Tarjetas de una columna; categoría y stock bajo comparten fila | Campos en una columna |
| 600–767 px | Tarjetas de una columna | Campos en dos columnas |
| 768–1199 px | Tarjetas en dos columnas, también cuando el sidebar reduce el espacio a 1024 px | Secciones con introducción superior; campos en dos columnas |
| ≥1200 px | Tabla de siete columnas con precios y cantidades alineados a la derecha | Introducción de sección a la izquierda y campos a la derecha |

El breakpoint de tabla responde al espacio necesario para siete columnas y hasta tres acciones, no al cambio de navegación en 992 px. Tabla y tarjetas recorren exactamente `productos.data`; comparten `ProductStock` y `ProductActions`. Se mantienen nombre, categoría, marca, precio, stock/unidad, mínimo cuando hay alerta, estado, editar, activar/desactivar y notificar proveedor en su condición original. No se calcula de nuevo el stock bajo: se consume `stock_bajo` del servidor.

Estados Activo/Inactivo con texto y punto de apoyo, sin reducir opacidad a toda la fila. Stock bajo con icono, texto y mínimo; existencias normales en tinta neutra. Cifras tabulares y precios sin saltos de línea. Nombres y clasificaciones se ajustan sin truncamiento. La tabla usa layout automático para reservar el ancho de importes largos. Las tarjetas ajustan sus grupos de valores si el contenido necesita otra línea.

### Formularios

Marco compartido de máximo 1040 px, con título, descripción y Volver. Un solo panel contiene dos secciones: Información del producto y Precio e inventario. Se conserva el orden nombre/categoría/marca/proveedor/unidad/precio/stock/mínimo, los ocho controles, sus opciones, valores iniciales, required/min/maxlength y modificadores `v-model.number`.

Campos con label asociado por id único, error con icono/texto y `role="alert"`, `aria-invalid` y `aria-describedby`. Controles de 16 px y al menos 46 px; borde `#858a94` (3.47:1 sobre blanco). Foco de 2 px en tinta de marca, incluido el grupo de moneda/unidad mediante `focus-within`. El texto de error usa `--p-danger`. Se conserva el aviso de granel, con Lucide en lugar de emoji.

El pie Guardar/Cancelar permanece en el flujo y deja libre el espacio de la navegación inferior. Cancelar y Volver llevan al índice existente. Loading/disabled consumen exclusivamente `form.processing` mediante `UiButton`. No se agregan validaciones ni guardas de navegación.

### Componentes

| Componente | Contrato de presentación |
|---|---|
| [UiFormField](resources/js/Components/Ui/UiFormField.vue) | `etiqueta`, `error`, `requerido`; slot `control` con id y atributos ARIA. Presenta label, estado y error; no modifica valores ni valida. `CampoForm` queda intacto para otros módulos. |
| [ProductEditorFrame](resources/js/Components/Productos/ProductEditorFrame.vue) | `title`, `description` y slot de formulario; compone marco, cabecera y Volver para Create/Edit. |
| [ProductActions](resources/js/Components/Productos/ProductActions.vue) | `producto`, `labels`; edición al destino existente y eventos `toggle`/`notify` con el mismo objeto. La condición de proveedor es idéntica a la original; no ejecuta llamadas HTTP propias. |
| [ProductStock](resources/js/Components/Productos/ProductStock.vue) | `stock`, `minimum`, `unit`, `low`; formato existente, unidad, alerta y mínimo. Sin cálculo de umbral. |
| [Paginacion](resources/js/Components/Paginacion.vue) | Variante optativa `premium=false` por defecto. Mantiene links, condición `links.length > 3`, labels y `preserve-scroll`; añade targets de 44 px y `aria-current` solo a la variante premium. Los consumidores heredados conservan su plantilla. |

Los iconos Search, ArrowLeft, Pencil, Mail y Power provienen de la dependencia Lucide ya instalada. No se cambia el mapa compartido de iconos ni se añaden dependencias. Estilos scoped en los componentes; no hay una migración global de CSS.

### Conservación y evidencia

Los bloques de script posteriores a imports en Index, Create, Edit y ProductoForm permanecen idénticos a `ee01b43`, comprobado por comparación textual. La venta fraccionaria y el descuento de inventario siguen fuera del alcance. Se mantienen las ocho unidades y el paso de inventario `0.001` para kg/g/litro/ml; unidades antiguas conservan su opción existente.

Las tres vistas se inspeccionan con Playwright MCP en las ocho anchuras solicitadas, cargando los assets desde `http://localhost`. Como la sesión disponible estaba expirada, las respuestas Inertia de revisión se simulan exclusivamente en el navegador; no se alteran rutas o permisos del servidor. Los errores y el estado processing se simulan sobre el formulario montado, sin enviar solicitudes de guardado. No se crean productos, operaciones comerciales ni cuentas.

Informe y enlaces a las evidencias indispensables: [Fase 2.6](docs/products-inventory-2026-11-23.md). Las capturas y galería locales permanecen ignoradas por Git. Acceso habitual: [http://localhost/productos](http://localhost/productos). La revisión técnica y el commit no constituyen aprobación estética. Pendientes de evaluación física: teclado virtual, Safari/Firefox y lector de pantalla.

## Fase 2.7 · Caja y Cortes (implementada, pendiente de aprobación estética)

Caja, historial y detalle de cortes activan `AppLayout premium`. Se conservan DM Sans, Lucide, los tokens anteriores y el tema configurable del tenant. Dashboard, POS, Productos y los componentes compartidos existentes no se modifican. La dirección sigue los principios de jerarquía y adaptación del sistema; la referencia HIG requerida por la skill se consultó en [Design principles](https://developer.apple.com/design/human-interface-guidelines/design-principles).

### Composición financiera

Caja prioriza el efectivo esperado, con un borde superior en tinta de marca. Tarjeta, ingresos adicionales y retiros son indicadores de apoyo. Ventas en efectivo y fondo se mantienen visibles en el desglose; no se inventan estadísticas ni se recalculan importes. El último corte conserva su fecha original y la alternativa «Desde el inicio del día».

En el detalle se separan información general, totales, recuento, notas y ventas. El efectivo esperado consume `total_efectivo`; el total general consume `total_general`. El recuento conserva las condiciones originales para efectivo contado, diferencia y dinero en caja. Diferencia cero muestra «Cuadra exacto»; positiva «Sobran»; negativa «Faltan», con icono y texto. Recuento nulo muestra «— / No capturado». Las notas conservan saltos de línea y ajustan textos largos.

| Componente nuevo | Contrato exclusivamente visual |
|---|---|
| [FinanceWorkspace](resources/js/Components/Finanzas/FinanceWorkspace.vue) | Slot de contenido; vocabulario financiero local de secciones, pares etiqueta/valor, tablas, tarjetas y paginación. No controla datos, permisos ni navegación. |
| [FinancialMetric](resources/js/Components/Finanzas/FinancialMetric.vue) | `label`, `value` ya formateado, `icon`, `description?`, `prominent?`. Sin cálculos. Protege importes con composición diferenciada para valores largos. |
| [CashMovementList](resources/js/Components/Finanzas/CashMovementList.vue) | `movements`: id, tipo, monto, motivo y fecha existentes. Lista con tipo explícito, signo, icono, importe, motivo y fecha; vacío compartido. No filtra ni ordena. |
| [CashDialog](resources/js/Components/Finanzas/CashDialog.vue) | `open`, `title`, `theme?`, `wide?`, slot y evento `close`. Reutiliza UiOverlay; presenta formularios, contexto monetario y acciones. No posee formularios comerciales ni envía solicitudes. |

Se reutilizan sin cambios `UiButton`, `UiIcon`, `UiEmptyState`, `UiFormField`, `UiOverlay`, `Paginacion premium`, `AppLayout` y su tema. Los cuatro componentes nuevos usan Composition API y contratos TypeScript; las páginas mantienen sus scripts JavaScript existentes.

### Importes, movimientos y consulta

Importes con `moneda()` original, cifras tabulares y `white-space: nowrap`, sin truncamiento ni división de moneda/céntimos. Las métricas usan contenedores para ajustar la fuente entre 22–30 px, o 18–28 px cuando el valor supera 12 caracteres. En móvil un valor largo ocupa su propia línea completa. Los pares etiqueta/valor y los movimientos permiten reflujo de grupos completos para conservar el importe.

Las filas de movimientos separan tipo e importe del motivo, que puede ocupar varias líneas. Ingreso usa ArrowDownLeft, texto «Ingreso» y signo +; retiro usa ArrowUpRight, «Retiro» y signo −. El color semántico de apoyo no es la única señal. Se presenta exactamente el arreglo recibido del servidor.

Historial y ventas usan tablas semánticas en escritorio, con caption accesible, encabezados de columna, importes alineados a la derecha y enlaces con nombre contextual. En móvil/tablet se muestran tarjetas con todos los campos y destinos originales. Ambas presentaciones recorren los mismos datos. No se ocultan columnas mediante truncamiento ni se requiere scroll horizontal para consultar valores. Paginación conserva URL, labels, condición de visibilidad y `preserve-scroll` del componente existente.

| Ancho CSS | Indicadores | Historial y ventas | Caja / recuento |
|---|---|---|---|
| 320–599 px | Una columna; secundarios compactos, valores largos en línea propia | Tarjetas de una columna | Secciones apiladas; acciones de Caja a ancho completo |
| 600–767 px | Dos columnas | Tarjetas de una columna | Secciones apiladas |
| 768–1199 px | Dos columnas | Tarjetas de dos columnas | Secciones apiladas; navegación existente cambia a sidebar en 992 px |
| ≥1200 px | Cuatro en Caja, tres en detalle | Tablas completas | Desglose/movimientos y recuento/notas en dos columnas |

La información general del corte usa dos columnas en móvil y cuatro en escritorio. Los campos del diálogo de corte pasan a dos columnas desde 520 px. Los diálogos conservan máximo de 480 px, o 520 px para corte, y pueden desplazarse verticalmente con poca altura. No se añade ninguna barra flotante; AppLayout reserva la navegación inferior y su composable la oculta mientras un dialog está abierto.

### Formularios y estados

Labels visibles con id único, `aria-invalid`, `aria-describedby` y errores con `role="alert"` mediante UiFormField. Se conservan todos los modelos, modificadores `.number`, required, min, step, maxlength y textos de validación. Los errores del servidor y las advertencias en vivo del corte se presentan juntos cuando coinciden. Inputs numéricos con `inputmode="decimal"`, texto de 16 px y altura mínima de 46 px. Foco visible de 2 px; botones y enlaces de al menos 44 px.

Ingresar y retirar se distinguen por verbo e icono; retiro usa además `--p-danger`. Loading consume únicamente `form.processing`: spinner, texto de progreso, `aria-busy` y disabled. La condición `corteInvalido` sigue intacta. No se añaden confirmaciones, requisitos, validaciones ni pasos. Los diálogos reutilizan contención y restauración de foco, Escape, backdrop e inercia nativa del overlay existente.

Los botones principales de estas vistas y diálogos añaden borde local `--p-brand-ink`, excepto el retiro semántico, para conservar el contorno con marcas claras. No se modifica UiButton ni los tokens globales. Vacíos de movimientos, cortes y ventas reutilizan UiEmptyState con icono y explicación contextual.

### Evidencia y conservación

Informe: [Fase 2.7 y comprobación responsive](docs/cash-cuts-2026-11-23.md). Playwright MCP, Chromium, las ocho anchuras solicitadas y simulaciones exclusivamente en navegador porque la sesión estaba expirada. Los assets se sirven desde localhost; no se realizan operaciones comerciales. Se conservan capturas y galería ignoradas por Git. Las comprobaciones visuales no certifican permisos, validación de servidor ni persistencia; el commit técnico queda pendiente de aprobación estética.
