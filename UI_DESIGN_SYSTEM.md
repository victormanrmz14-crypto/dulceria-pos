# Dulcería POS · Design System del dashboard piloto

## Alcance y activación

Implementación optativa para el dashboard de administrador de tenant. [Dashboard.vue](resources/js/Pages/Dashboard.vue) activa `AppLayout premium` únicamente cuando el rol existente es `admin`. Los demás consumidores del layout, de `SidebarNav`, `StatCard` y `WelcomeModal` conservan la variante original por defecto. No cambia la asignación ni la comprobación de permisos del servidor.

Todos los selectores nuevos están bajo `.pos-premium`; los overlays teletransportados llevan esa misma raíz y reciben los tokens de marca. No se cambian resets, variables globales de Bootstrap ni Tailwind. La carga del CSS del piloto después de navegar no debe alterar pantallas vecinas.

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
| [UiOverlay](resources/js/Components/Ui/UiOverlay.vue) | `open`, `title`, `variant`, `theme`, evento `close`; dialog nativo, foco inicial, contención de Tab, Escape, backdrop, bloqueo de scroll y restauración del foco |
| [StatCard](resources/js/Components/StatCard.vue) | Variante `premium`; preserva `etiqueta`, `valor`, `sufijo`, `href`; añade `icon` y `attention` |
| [SidebarNav](resources/js/Components/SidebarNav.vue) | Variante `premium`; mismos destinos y condiciones de rol, grupos desplegables accesibles y cierre por navegación |
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
| Menos de 360 px | 1 columna, tarjetas horizontales | Drawer modal | 1 columna, acciones apiladas |
| 360–479 px | 1 columna, tarjetas horizontales | Drawer modal | 1 columna |
| 480–767 px | 2 columnas | Drawer modal | 1 columna |
| 768–991 px | 2 columnas | Drawer modal | 1 columna |
| 992–1199 px | 2 columnas | Sidebar | 1 columna |
| Desde 1200 px | 4 columnas | Sidebar | Gráfica y ranking, proporción 1.65:1 |

En la segunda iteración la proporción de análisis en escritorio es 1.65:1. Cuando tanto gráfica como ranking están vacíos, se usan dos columnas desde 768 px con proporción 1.4:1 hasta 1199 px; con datos se conserva el apilado en ese rango. Esto reduce el espacio dedicado a ausencia de datos sin comprimir la gráfica poblada.

Main: 32 px en escritorio, 24 en tablet y 16 horizontales en móvil. La tabla puede desplazarse dentro de su región; el documento completo no debe desbordarse. Acciones principales visibles antes del primer scroll en las seis resoluciones acordadas, con altura de viewport de 900 px.

## Conservación funcional

Se conservan todos los props de Inertia, las cuatro métricas, filtros de stock, enlace a Caja, último corte, POS, historial, últimos siete días, unidades e importes del ranking, cajero, hora, método de pago y enlace a cada ticket. La rama del dashboard de cajero conserva su plantilla original. No se cambian controladores, modelos, migraciones, rutas, autenticación, permisos ni endpoints.

Dependencia adicional única: `@lucide/vue@1.77.0`, fijada en package y lockfile. Sin Tailwind global, shadcn-vue ni instalación de herramientas de test adicionales.

## Validación y evidencias de la primera iteración

Comparativas: [.playwright-mcp/premium-pilot/comparison.html](.playwright-mcp/premium-pilot/comparison.html). Capturas reales antes y después a 320, 375, 768, 1024, 1366 y 1920 px, altura de viewport 900 px y captura de página completa. Las capturas iniciales son del 22 de noviembre y las finales del 23; la fecha visible y la ventana móvil de siete días cambian naturalmente.

El tenant de auditoría no tiene operaciones comerciales. Las pruebas adicionales con tabla, ranking, gráfica, último corte, bienvenida y paletas se realizan sustituyendo respuestas únicamente en el navegador, sin escribir registros. Sus capturas se identifican como `simulated-*`; no prueban una venta ni la emisión real de un ticket.

Se usa una vista previa temporal de solo lectura en `http://localhost:8181/dashboard`: sirve los assets compilados del checkout y reenvía GET al Laravel local existente, usando la sesión normal. No altera configuración ni imágenes Docker, base de datos o volúmenes; bloquea POST. La aplicación original de `http://localhost` sigue sirviendo la imagen anterior hasta una futura actualización de assets/imagen. La vista previa requiere una sesión de localhost en ese navegador y no es un mecanismo de autenticación alternativo.

Compilación con `npm run build`. No hay script configurado de lint ni pruebas unitarias frontend, y PHPUnit no está instalado en host ni en el contenedor de producción local. Se validan compilación Vue/Vite, diagnósticos del editor y comportamiento mediante Playwright. Browserslist advierte que su catálogo está desactualizado; no se actualizan dependencias fuera de la autorizada.

El piloto queda pendiente de revisión visual. La extensión del sistema a otros módulos, formularios y navegación compartida requiere aprobación posterior.

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
