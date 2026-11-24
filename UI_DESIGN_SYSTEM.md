# Dulcería POS · Design System

## Identidad de plataforma · Fase 2.13.2

La identidad aprobada procede de `dulceria-pos-figma-assets-seleccionados.zip`, exportado de Figma. Identifica **Dulcería POS** en páginas públicas y favicon; la superadministración adopta esta identidad de plataforma en su navegación desde la Fase 2.14. Nunca sustituye la identidad comercial, el logotipo ni la paleta de un tenant. El ZIP es entrada local fuera de Git y del contexto Docker.

| Recurso definitivo en `public/brand/` | Contexto / proporción original |
|---|---|
| `logo-primary.svg` | Autenticación sobre fondo claro, con descriptor; 300 × 65 |
| `logo-compact.svg` | Encabezado de autenticación bajo 992 px, sin descriptor; 300 × 65 |
| `logo-white.svg` | Header, footer y maquetas públicas de landing sobre fondo oscuro; 301 × 65 |
| `symbol-color.svg` | Sello de autenticación y componente de logo heredado; 168 × 99 |
| `favicon-16.svg`, `favicon-32.svg`, `favicon-64.svg` | D simplificada aprobada para cada tamaño; no usar el símbolo detallado a 16 px |
| `favicon-16.png`, `favicon-32.png`, `favicon-64.png` | Exportaciones originales de Figma: fallback y touch icon de 64 px |
| `android-foreground.svg`, `android-background.svg` | Fuentes reservadas para Android, 108 × 108; no se cargan en la web |

Los SVG se copiaron **sin modificar bytes**, trazados, colores, composición ni viewBox. XML válido, tipografía trazada, sin scripts, eventos ni referencias externas. No se añaden filtros, sombras, degradados ni rotación a la marca. Las variantes grafito y símbolo borgoña se dejan en el ZIP por no tener consumidor; la referencia Android compuesta tampoco se publica. Se retiraron los recursos provisionales, la lámina de exploración y su generador.

`platform-logo.blade.php` selecciona la variante y declara las dimensiones intrínsecas correctas; CSS conserva `height:auto`. Anchos aprobados de la interfaz: 206 px, 184 px en teléfono. El símbolo usa 62 px de ancho y altura automática; no se estira a un cuadrado. No se duplica el nombre inmediatamente junto al wordmark. `platform-favicons.blade.php` unifica favicon en Blade e Inertia; `public/favicon.ico` contiene los PNG originales de 16/32/64 sin remuestreo. URLs versionadas para los PNG e ICO reemplazados. El touch icon reutiliza el PNG original de 64 px: una exportación Apple de mayor resolución queda para la fase móvil.

| Rol de plataforma | Valor |
|---|---|
| Borgoña aprobado / hover de interfaz | `#960A2C` / `#6F1D30` |
| Tinte / canvas / superficie | `#F8EFF1` / `#F7F8FA` / `#FFFFFF` |
| Texto / secundario / borde | `#202126` / `#666973` / `#DEE0E5` |
| Contorno de campos | `#858892` |
| Error / éxito | `#B42318` / `#166534` |

[tokens.css](public/brand/tokens.css) limita las variables a `.platform-brand` y a la capa CSS `brand`. El color de acción se alinea con el borgoña del SVG; los tokens dinámicos `--p-*` de los tenants no cambian. DM Sans variable y su [licencia SIL OFL 1.1](public/brand/fonts/OFL.txt) se conservan locales. Contraste comprobado: blanco/borgoña 8.84:1; foco borgoña/panel claro 7.92:1.

Android: los dos SVG originales de 108 unidades son fuentes separadas de primer plano y fondo; este último usa `#F7F3F2`. No son VectorDrawable, APK, manifiesto, splash screen ni prueba de zona segura en Android. La conversión y comprobación con máscaras nativas corresponden a la fase móvil.

Responsive y evidencia: se conserva la composición 2.13.1, sin escalado global ni recorte del scroll. Cinco páginas públicas × las nueve resoluciones solicitadas: sin overflow horizontal; login y recuperación sin scroll, registro móvil con scroll natural y reset con scroll legítimo a 320 × 568. Confirmación y verificación de correo comparten el layout y se abrieron con sesión aislada. [Informe de integración](docs/branding-tenant-identity-phase-2-13-2.md) y [comparativas locales antes/después](.playwright-mcp/branding-tenant-2-13-2/comparison.html), fuera de Git.

### Identidad comercial del tenant y navegación

Fuente única: `HandleInertiaRequests::share()` deriva `tenantConfig.nombre` del usuario autenticado y su relación `User::tenant`. Orden: `configuracion.negocio.nombre_mostrar` no vacío, después `Tenant.nombre` no vacío y, por último, `Dulcería POS`. Se ignoran espacios de borde para decidir un valor vacío; no se trunca ni se persiste ninguna modificación. `tenantConfig.nombre_mostrar` conserva su contrato opcional original. No hay consultas en Vue, selectores de tenant, caché global ni localStorage de identidad.

Administrador y cajero reciben la misma identidad del negocio asociado. Para invitado, superadministrador o usuario sin relación de tenant, `tenantConfig` es `null`; el fallback visual es `Dulcería POS`. Durante impersonación se utiliza el usuario efectivo del tenant según el comportamiento existente; no se cambia esa funcionalidad.

`TenantIdentityName.vue` representa el mismo valor en sidebar de escritorio, drawer y breadcrumb de `AppLayout`, con actualización reactiva desde Inertia. La ruta conserva el nombre de sección de `tenantNavigation` y expone `nav[aria-label="Ruta de navegación"]` y `aria-current="page"`. La corrección llega a todos los módulos que usan el layout: Inicio, Productos, Usuarios, Caja, Cortes, Vender, Ventas, Reportes, Configuración, perfil y Catálogos; también a sus páginas de edición existentes.

Sidebar: se conserva `tenantConfig.logo`, `object-fit:contain`, paleta comercial e icono de respaldo existente cuando falta logo. La marca Figma no reemplaza un logotipo de negocio. Subtítulos: Administración para administrador y Punto de venta para cajero. Nombre limitado visualmente a dos líneas; breadcrumb de una línea con elipsis. `details/summary` compartido permite desplegar el nombre completo mediante toque, clic, Enter o Espacio; Escape o salida del foco cierran. Incluye nombre completo accesible, `title`, foco de 2 px y objetivo de al menos 44 px. No se recorta el dato original.

Composición: `min-width:0` en los contenedores, máximo de nombre de 240 px en escritorio, 145 px en móvil y 120 px bajo 360 px; panel de nombre completo acotado al viewport. Header medido de 56 px aun con el nombre extenso. Bottom Navigation bajo 768 px, drawer entre 768 y 991 px y sidebar desde 992 px; permanece el espacio inferior para no cubrir el último contenido.

Sesiones reales aisladas: administrador y cajero de dos negocios, logout nativo entre cuentas, superadministrador y caso defensivo sin tenant. No se reutiliza el nombre de la cuenta anterior. Pruebas de presentación y navegación de lectura: no equivalen a auditoría de aislamiento comercial ni de permisos. Evidencia y límites en el [informe 2.13.2](docs/branding-tenant-identity-phase-2-13-2.md).

### Familia de autenticación Blade

[auth/layout](resources/views/components/auth/layout.blade.php) compone identidad, formulario y pie. [auth/field](resources/views/components/auth/field.blade.php) es propietario de label, control, ayuda, error asociado y revelado de contraseña; `auth/feedback` presenta estados del servidor y resumen; `auth/submit` informa envío nativo. [auth.css](resources/css/auth.css) usa la capa `auth`, posterior a `brand`; [auth.js](resources/js/auth.js) añade únicamente revelado, espera, restauración de botón en `pageshow`, foco de error y compatibilidad del marcador histórico `#forgot`. Sin migración a Vue ni carga del bundle Inertia en estas vistas.

Composición dividida desde 992 px, proporción 46/54 y ancho total máximo 1680 px. Panel de identidad neutro `#F3F2F5`, ilustración conceptual propia sin estadísticas y formulario blanco de 400 px (520 en registro). Bajo 992 px el panel desaparece y se muestra la marca encima del formulario. Registro: dos fieldsets, negocio/nombre opcionales; nombre/correo y contraseñas usan dos columnas desde 600 px y una sola por debajo, manteniendo el orden del DOM. Main y pie se desplazan con el documento; no hay tarjeta con scroll interno ni altura fija de formulario.

Fase 2.13.1: padding vertical fluido `clamp(16px, 3vh, 32px)`, separación de campos de 16 px y secciones de 20 px. El encabezado reserva 76 px mínimos y el pie usa 8/12 px de padding superior/inferior. Hasta 800 px de alto, se usan 12 px de padding y separación de campos, secciones de 16 px y encabezado de 64 px. En móviles de hasta 600 px de alto, padding de 8 px, encabezado mínimo de 60 px y título de 26 px. Los controles no se encogen. El panel lateral reduce sus márgenes y la composición decorativa; a ≤680 px de alto y ≥992 px de ancho se oculta únicamente la ilustración, conservando marca y mensaje. La separación entre contraseña y acción de recuperación mantiene al menos 8 px. El centrado vertical cede al crecimiento natural del contenido, incluidos errores. Evidencia: [refinamiento responsive 2.13.1](docs/auth-responsive-refinement-phase-2-13-1.md).

Títulos de 30 px (28 móvil; 26 en móviles de altura ≤600 px), texto de ayuda de 12–14, inputs de 16 px y altura 48 px; acción principal renderizada de 50 px, controles secundarios ≥44 px y radio de control 6 px. Foco de 2 px con offset 3; errores con texto «Error», `aria-invalid`, ayudas mediante `aria-describedby` y foco inicial en primer inválido. Estado de éxito con `role=status`; envío con texto, disabled y `aria-busy`. Reduced motion detiene el único indicador de espera. Sin efectos 3D o movimiento decorativo.

CSRF, nombres de campos, old input, autocompletado, sesión y redirecciones se conservan. `GET /forgot-password` presenta la vista existente mediante su controlador; POST y tokens no cambian. Confirmación y verificación de correo usan la misma familia sin activar infraestructura nueva. Landing integra marca, tipografía y acciones; sus afirmaciones comerciales quedan registradas en el informe, sin rediseño de marketing.

WebView: `viewport-fit=cover`, safe areas laterales/superior/inferior, `min-height:100dvh` con fallbacks a `100svh` y `100vh`, scroll natural, teclado de email solo donde corresponde y `interactive-widget=resizes-content`. La altura mínima sigue al viewport dinámico sin limitar la altura del contenido. No se desactiva el zoom. Teclado Android físico, `adjustResize` nativo y splash screen quedan para integración futura.

Verificación y límites: [informe Fase 2.13](docs/auth-branding-phase-2-13.md), [galería local antes/después](.playwright-mcp/auth-branding-2-13/comparison.html). Cuatro pantallas × ocho anchuras, altura corta, teclado, zoom CSS 200 %, contexto táctil Android emulado, 16 pares de contraste y 20 pruebas Laravel / 69 aserciones en SQLite aislado. No equivale a revisión estética aprobada, certificación de accesibilidad ni prueba en WebView físico.

> Archivo de referencia: las capturas, resultados de diagnóstico y vistas previas temporales se retiraron en la Fase 2.5. La aplicación actual se consulta en [http://localhost](http://localhost). Se conservan aquí los patrones, resultados históricos y limitaciones.

## Alcance y activación

La navegación responsive es compartida por todos los módulos del tenant mediante `AppLayout`. El contenido premium se activa por página: [Dashboard.vue](resources/js/Pages/Dashboard.vue) lo utiliza para administrador y cajero desde la Fase 2.12; [Pos.vue](resources/js/Pages/Ventas/Pos.vue) lo activa para ambos roles del tenant en Fase 2.4; las vistas de [Productos](resources/js/Pages/Productos/Index.vue) lo activan en Fase 2.6 dentro de sus permisos existentes; Caja y Cortes lo activan en Fase 2.7 y Reportes en Fase 2.8. Usuarios, Catálogos e Historial de Ventas lo activan en Fase 2.9; Configuración y el perfil personal, en Fase 2.10. El ticket de venta se incorpora al sistema premium en Fase 2.12. Los módulos fuera de ese alcance conservan su presentación. `StatCard` y `WelcomeModal` mantienen sus variantes originales por defecto. No cambia la asignación ni la comprobación de permisos del servidor.

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

La configuración de colores completa, nombre y logo sigue funcionando. El piloto reserva el primario para acción principal, selección y gráfica; no reemplaza las opciones guardadas de acento, fondos o recibos, ni sus usos fuera del piloto. El logo conserva sus proporciones con `object-fit: contain`. Los nombres largos se muestran en hasta dos líneas en sidebar y en una línea con elipsis en breadcrumb. El control desplegable compartido permite consultar el valor completo con ratón, toque y teclado, sin aumentar la altura del header.

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

Se conservan todos los props de Inertia, las cuatro métricas, filtros de stock, enlace a Caja, último corte, POS, historial, últimos siete días, unidades e importes del ranking, cajero, hora, método de pago y enlace a cada ticket. Desde Fase 2.12 el dashboard de cajero utiliza los componentes premium con las mismas métricas y permisos; su navegación compartida se adapta al rol. No se cambian controladores, modelos, migraciones, rutas, autenticación, permisos ni endpoints.

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

## Fase 2.8 · Reportes y Analítica (aprobada)

[Reportes/Index.vue](resources/js/Pages/Reportes/Index.vue) activa `AppLayout premium` y reutiliza `FinanceWorkspace` y `FinancialMetric` sin modificarlos. Conserva los colores del tenant, DM Sans, Lucide, tokens, navegación y superficies de las fases anteriores. No se cambian otros módulos, dependencias, Grafica ni componentes compartidos existentes.

### Jerarquía y semántica

Encabezado con propósito y `rangoTexto` original. Filtros en panel propio; después, cuatro indicadores: ingresos por ventas, número de ventas, ventas en efectivo y ventas con tarjeta. Solo ingresos por ventas lleva énfasis superior de marca. No representa utilidad, saldo de caja ni movimientos manuales. Los importes usan `moneda()` y el conteo `numero()`, con cifras tabulares y sin saltos. Se conserva la composición financiera móvil compacta y la variante para valores largos de Fase 2.7.

El análisis sigue con ingresos diarios y distribución por método de pago; después, productos más vendidos y, a todo el ancho disponible, historial de ventas. No se añaden porcentajes, comparativas, tendencias calculadas, pronósticos ni estadísticas comerciales.

### Componentes analíticos

| Componente nuevo | Contrato de presentación |
|---|---|
| [AnalyticsChart](resources/js/Components/Analitica/AnalyticsChart.vue) | `kind: line/doughnut`, `data` con labels y datasets numéricos, `color` accesible y `label`. Reutiliza Grafica/Chart.js; clona datasets para cambiar únicamente su estilo. No ordena, agrega, interpola ni cambia valores. |
| [AnalyticsRanking](resources/js/Components/Analitica/AnalyticsRanking.vue) | `items` con `label`, `quantity` y `value` ya formateados. Mantiene el orden recibido, numera posiciones y presenta vacío. No calcula el ranking ni porcentajes. |
| [AnalyticsSalesList](resources/js/Components/Analitica/AnalyticsSalesList.vue) | `sales` con los campos existentes de cada venta y `links` del paginador. Tabla/tarjetas con fecha, cajero, método, importe y enlace original al ticket. No ejecuta operaciones comerciales. |

### Gráficas

Línea de 220 px de alto, tinta accesible de marca, trazo de 2 px y segmentos rectos para evitar suavizados que sugieran valores intermedios distintos. Puntos visibles hasta 31 fechas; en rangos mayores se mantienen los datos completos y el acceso por tooltip. Ejes con DM Sans de 12 px, máximo de cinco etiquetas de fecha sin rotación y cuadrícula horizontal discreta. Eje monetario compacto con Intl es-MX; tooltip con `moneda()` completo. Sin animaciones.

El donut mide 152 × 152 px, con dos segmentos, el segundo tramado para distinguirlo incluso con marcas grises, borde blanco y leyenda HTML de efectivo/tarjeta con iconos, muestra de textura, nombres e importes completos. El segundo color es el neutral analítico `#737b8c` (4.25:1 sobre blanco); no se usa como nuevo color global. La lectura exacta no depende del color ni del hover.

La línea tiene nombre accesible y un disclosure «Datos por día» con todas las fechas/importes originales; el listado tiene altura máxima de 260 px y puede enfocarse para recorrerlo con teclado. Los canvas son decorativos para tecnologías de asistencia porque el contenido se ofrece en texto adyacente. Sin ventas (`totalVentas === 0`) no se montan gráficas ni escalas. Con ventas de importe cero se conserva la línea de valores reales; si ambos importes son cero, el donut muestra «Sin importes por método de pago». Son condiciones de presentación, no cálculos nuevos.

### Filtros y resultados responsive

Se conservan Hoy, Ayer, Últimos 7 días y Últimos 30 días, con las mismas URL; personalizado mantiene los dos modelos, la función `aplicarPersonalizado`, sus parámetros y `preserveState`. No se añaden required, límites, normalización de fechas ni filtros. Activo se reconoce por texto, peso, borde y subrayado interior. Personalizado indica «Activo» cuando lo determina el prop original. Labels y errores de fechas se asocian mediante UiFormField; loading refleja eventos de navegación Inertia de Reportes sin cambiar consultas ni persistencia.

| Ancho CSS | Filtros | Indicadores y resultados |
|---|---|---|
| 320–359 px | Presets 2 × 2; fechas y Aplicar en una columna | Métricas/listas/tarjetas en una columna; gráficas apiladas |
| 360–599 px | Presets 2 × 2; fechas en dos columnas, Aplicar debajo | Mismo contenido móvil, con importes completos |
| 600–767 px | Presets en fila; fechas y Aplicar en una fila | Dos columnas de métricas; ventas y ranking en una columna |
| 768–1199 px | Panel apilado de períodos/rango | Métricas, tarjetas de ventas y ranking en dos columnas; gráficas apiladas |
| ≥1200 px | Períodos y rango a los lados del panel | Cuatro indicadores; gráficas 1.8:1; ranking de cinco posiciones; tabla de ventas a todo el ancho |

Un ranking con un solo producto ocupa una fila completa en escritorio. Nombres extensos se ajustan sin truncado; los importes largos pasan a una línea propia en listas estrechas. Las tarjetas de venta mantienen todos los campos y el enlace contextual al ticket, con metas compactas y total separado. Paginación reutiliza su variante premium, incluyendo URL, labels y `preserve-scroll`. Los vacíos usan UiEmptyState; los de gráficas tienen mínimo de 128 px. La barra inferior y su reserva de espacio se conservan.

Informe, capturas, contraste y límites: [Fase 2.8](docs/reports-analytics-2026-11-23.md). La simulación visual usa únicamente el navegador porque no hay sesión autenticada disponible. La Fase 2.8 fue aprobada por el propietario al solicitar la Fase 2.9; el informe conserva el registro de su entrega técnica original.

## Fase 2.9 · Usuarios, Catálogos e Historial de Ventas (implementada, pendiente de aprobación estética)

Las siete vistas del tenant activan `AppLayout premium`: listado/alta/edición de Usuarios, Categorías y Marcas sobre `Catalogos/Simple`, Proveedores e Historial de Ventas. Se conservan las rutas y permisos. Configuración y Superadministración quedan fuera del alcance. No se cambia el contenido de las fases anteriores; la variante predeterminada de la lista de ventas de Reportes conserva su presentación aprobada.

### Colecciones administrativas

Se reutilizan FinanceWorkspace y sus tablas, tarjetas y pares etiqueta/valor para mantener el mismo vocabulario de superficies y espaciados. Usuarios presenta nombre/apellido, usuario, correo, rol, estado y acciones. La marca «Tú» conserva la condición por id; activar/desactivar sigue ausente para la cuenta actual. Los estados inactivos conservan toda su legibilidad, sin opacidad aplicada a la fila.

Categorías y Marcas siguen compartiendo la misma página y sus cinco props: `titulo`, `singular`, `icono`, `base` e `items`. El prop `icono` se conserva en el contrato; sus emojis no se renderizan y la vista usa Lucide. No se añaden búsquedas ni filtros. Proveedores conserva nombre, correo, teléfono opcional, notas opcionales, estado y `productos_count`, sin truncar datos de contacto o notas.

| Componente nuevo | Contrato de presentación |
|---|---|
| [AdminActions](resources/js/Components/Admin/AdminActions.vue) | `name`, `active`, `editHref?`, `hideToggle?`, `labels?`; eventos `edit` y `toggle` sin payload. Link para editar usuarios y botón para abrir los diálogos de catálogos; no ejecuta HTTP ni confirmaciones propias. |
| [AdminStatus](resources/js/Components/Admin/AdminStatus.vue) | `active`, `activeLabel?`, `inactiveLabel?`. Icono y texto en superficie neutra; variantes Activo/Inactivo y Activa/Inactiva. |
| [UserRoleBadge](resources/js/Components/Admin/UserRoleBadge.vue) | `role`; administrador con ShieldCheck, cajero con ReceiptText. Mantiene la interpretación original del rol; no concede permisos. |
| [AdminEditorFrame](resources/js/Components/Admin/AdminEditorFrame.vue) | `title`, `description`, `backHref` y slot. Marco de máximo 1040 px con encabezado, Volver y panel; usado por alta/edición de Usuarios. |
| [AdminDialog](resources/js/Components/Admin/AdminDialog.vue) | `open`, `title`, `theme?`, `wide?`, slot y evento `close`. Reutiliza UiOverlay, con anchos máximos de 440/520 px, campos y pie de acciones en flujo. |

Acciones de escritorio con iconos y nombres accesibles, de 44 × 44 px como mínimo, sin envolver el par de botones. Las celdas reservan 140 px para evitar filas innecesariamente altas. En tarjetas se muestran etiquetas Editar y Activar/Desactivar. Los handlers originales siguen poseyendo confirmaciones, endpoints, payloads y condiciones. La confirmación existente de Usuarios no se añade a los catálogos, que mantienen su comportamiento anterior.

### Formularios administrativos

UsuarioForm mantiene props `form`, `esEdicion`, `textoBoton` y evento `enviar`. Agrupa información personal, datos de acceso y rol; conserva los seis inputs, los dos botones de rol y todas sus restricciones. Contraseña/confirmación mantienen `minlength=8`, `autocomplete=new-password` y las mismas condiciones de required en alta/edición. Solo existen cajero y administrador. La selección usa fieldset/legend, `aria-pressed`, borde, peso e icono además del color. Cancelar vuelve al índice existente, sin guardas nuevas.

UiFormField asocia labels, ids, `aria-invalid`, `aria-describedby` y errores con `role=alert`. Los errores de confirmación y rol también se presentan junto a su control. Texto de input de 16 px y altura mínima de 46 px. Los formularios de catálogos mantienen nombre de hasta 150 caracteres; Proveedores conserva email requerido de hasta 255, teléfono de hasta 20 y notas de hasta 500, con dos filas iniciales. No se añaden reglas ni requisitos.

Loading consume únicamente `form.processing`, con UiButton, spinner, disabled y `aria-busy`. Los botones principales conservan borde local en `--p-brand-ink` para marcas claras. Los diálogos reutilizan inercia, Tab contenido, Escape, cierre y restauración de foco del overlay existente; la barra inferior se suprime mediante el composable ya implementado. Los campos largos de una línea mantienen desplazamiento nativo y cursor, sin ensanchar el documento ni perder su valor.

### Historial y reutilización con Reportes

Historial reutiliza AnalyticsSalesList con una extensión optativa: `ticketAction=false` por defecto, `caption`, `emptyTitle`, `emptyDescription` y slot `empty-action`. El modo predeterminado conserva los cinco campos y el folio enlazado de Reportes. Historial solicita `ticketAction`: folio informativo y acción explícita «Ver ticket», manteniendo el destino original en tabla y tarjetas. No se añaden filtros, búsquedas, exportaciones o acciones comerciales.

Los estilos específicos de las tarjetas de ventas tienen precedencia estable para mantener el espaciado aprobado de Reportes, independientemente del orden de carga de CSS compartido. El vacío de Historial usa Lucide, mensaje, descripción y acceso al POS en un bloque compacto. La paginación conserva sus links, labels, condición de visibilidad y `preserve-scroll`.

### Responsive y evidencia

| Ancho CSS | Listados | Formularios y navegación |
|---|---|---|
| 320–399 px | Tarjetas completas de una columna, con acciones etiquetadas | Campos y roles apilados; barra inferior existente |
| 400–599 px | Mismas tarjetas de una columna | Roles en dos columnas; campos en una |
| 600–767 px | Tarjetas de una columna | Campos de usuario en dos columnas; barra inferior |
| 768–1199 px | Tarjetas de dos columnas | Drawer hasta 991; sidebar desde 992; formularios con introducción superior |
| ≥1200 px | Tablas completas, sin scroll horizontal | Introducción lateral en UsuarioForm; marco limitado a 1040 px |

Los diálogos de proveedores distribuyen correo y teléfono en dos columnas desde 600 px; en móvil se apilan. Todas las acciones y pies mantienen espacio respecto a la navegación inferior, sin nuevas barras flotantes.

Informe, matriz de las ocho anchuras, capturas y límites: [Fase 2.9](docs/users-catalogs-sales-history-phase-2-9.md). La comprobación visual usa únicamente datos simulados en el navegador porque no hay sesión autenticada disponible; no valida autorización, persistencia ni operaciones comerciales. Los componentes compartidos conservan sus contratos y el backend permanece sin cambios.

## Fase 2.10 · Configuración y cuenta personal (implementada, pendiente de aprobación estética)

Configuración y Mi perfil activan el contenido premium. La navegación de cuenta se comparte entre todos los módulos del tenant, incluidos los que conservan contenido heredado. SuperAdmin conserva su layout. Esta fase añade exclusivamente la persistencia del avatar personal; no amplía las funciones administrativas ni modifica los mecanismos del logotipo.

### Configuración y formularios

`SettingsWorkspace` organiza el encabezado y la navegación secundaria de Apariencia, Mi negocio, Tickets y Mi perfil. Usa botones nativos para cambiar de apartado y enlaces Inertia para el perfil personal. Los botones identifican el apartado actual con `aria-current` y `aria-controls`; no simulan un widget ARIA de pestañas. Los enlaces desde el perfil admiten los fragmentos `#apariencia`, `#negocio` y `#tickets`.

Un panel agrupa cada formulario; encabezado de 15 px semibold, controles de 16 px, ayudas de 12–13 px, separación de 20–24 px y pie de guardado dentro del flujo. `UiFormField` conserva etiquetas, errores cercanos y `aria-describedby`/`aria-invalid`. El primer error del apartado activo recibe foco al terminar la respuesta. Carga y disabled impiden envíos duplicados; una nueva edición tiene prioridad sobre el mensaje de guardado reciente. No se añaden campos, reglas de validación ni ajustes comerciales.

Las nueve paletas existentes mantienen sus valores y su payload. Los botones incluyen muestra, nombre, estado `aria-pressed`, borde e icono de selección. La miniatura de navegación usa superficies neutras y tokens calculados sobre blanco; se identifica siempre como **vista previa local**. La paleta del layout sigue representando el valor guardado hasta guardar la apariencia. Logotipo del negocio y avatar son entidades independientes. La vista de ticket conserva el ejemplo ilustrativo existente y usa los valores locales del formulario, con una indicación explícita de que hay que guardar para aplicarlos.

### Componentes de cuenta

| Componente | Contrato |
|---|---|
| `UserAvatar` | `name`, `src?`, `size=32`, `decorative=true`; círculo de tamaño reservado, `object-fit: cover`, iniciales de primer y último nombre cuando falta la imagen o falla su carga. No depende del tenant. |
| `AccountMenu` | Identidad compartida en `auth.user`, disclosure de cuenta con botón accesible y `aria-expanded`; identidad y rol, enlace a Mi perfil y formulario POST/CSRF de logout existente. Tab/Shift+Tab nativos, ArrowDown hacia el enlace, Escape y restauración de foco, cierre exterior y por navegación; listeners liberados al desmontar. |
| `SettingsWorkspace` | `active`, `admin`, `title`, `description`, evento `select` y slot; composición y estilos de los formularios de Configuración y Perfil. No decide permisos del servidor. |
| `AdminDialog` | Añade `focusFallback` opcional, reenviado al overlay existente. Permite regresar al selector cuando desaparece la acción de eliminar la fotografía. |

En Mi perfil, la fotografía guardada, la selección local y las iniciales tienen mensajes distintos. Se puede descartar la selección, guardar, reemplazar o eliminar con confirmación. El navbar muestra únicamente el avatar guardado. El formulario restaura el foco después de finalizar la petición, cuando el control ya está habilitado. La selección de archivos usa el control nativo del navegador y sus etiquetas localizadas por el sistema.

El sidebar conserva todos sus destinos y grupos, incluido Configuración para administradores. Se retiran el avatar, nombre, rol y logout inferiores; no se añade contenido para rellenar el espacio. El rol de la dulcería en su marca («Administración» / «Punto de venta») se conserva. La barra inferior y su política de visibilidad no cambian.

### Composición y roles

| Ancho | Composición |
|---|---|
| 320–359 px | Navegación secundaria 2 × 2; una columna de paletas y formularios; avatar y caret visibles, nombre del navbar oculto. |
| 360–599 px | Navegación 2 × 2 y paletas de dos columnas; campos y vistas previas apilados. |
| 600–899 px | Navegación secundaria horizontal; campos del negocio en dos columnas; vistas previas debajo del formulario. |
| 900–1199 px | Formularios y vista previa en dos columnas; navegación secundaria horizontal. |
| Desde 1200 px | Navegación secundaria lateral de 164 px; contenido de hasta 1200 px. |

La navegación general conserva sus cortes: barra inferior bajo 768 px, drawer hasta 991 px y sidebar desde 992 px. El menú de cuenta tiene ancho máximo de 288 px, respeta el ancho de ventana y limita su altura; no alcanza la barra inferior en las ocho anchuras revisadas. Los pies se alcanzan por scroll normal, con el espacio inferior existente y sin otra barra flotante.

Administrador: Configuración administrativa y perfil. Cajero: `/perfil`, con únicamente navegación personal; Configuración continúa bajo `solo.admin`. SuperAdmin: sin cambios visuales; `usuario.activo` conserva su redirección al panel central.

### Persistencia, evidencia y límites

`users.avatar_path` es opcional, privado en la serialización y gestionado únicamente desde el usuario autenticado. Inertia añade solo `auth.user.avatar_url`. JPEG/PNG/WebP hasta 2 MB, MIME real, nombres hash y rutas `avatars/{id}/…`; nunca se acepta una ruta ni un ID objetivo del cliente. El archivo nuevo se almacena antes de confirmar su referencia en una transacción; después se retira el anterior, validando siempre el directorio propio. Los fallos de almacenamiento o persistencia conservan el anterior.

La migración incremental `2026_11_23_000001_add_avatar_path_to_users_table` se aplicó únicamente en el entorno Docker local. No se modificaron migraciones históricas. No hubo escrituras sobre fotografías ni registros de empleados reales.

Validación: cuatro vistas en 320, 375, 390, 430, 768, 1024, 1366 y 1920 px; teclado, estados y roles mediante Playwright MCP con fixtures exclusivos del navegador. Persistencia real verificada por 15 pruebas Laravel / 150 aserciones, con SQLite en memoria y almacenamiento aislado. Se comprobó el disco público con Nginx en un contenedor aislado, sin publicar puertos. Los 38 pares de contraste calculados pasan: texto ≥4.5:1 y límites de controles ≥3:1. Son comprobaciones específicas, no una certificación de accesibilidad ni una auditoría integral.

Informe e inventario: [Fase 2.10](docs/settings-profile-phase-2-10.md). Galería local, fuera de Git: `.playwright-mcp/settings-profile/comparison.html`.


## Fase 2.12 · Tickets, cuenta y experiencia real del cajero

### Detalle de venta y comprobante

`Ventas/Ticket.vue` conserva los props `venta` y `config` del controlador. Encabeza «Detalle de venta» con regreso al historial y acciones Imprimir / Nueva venta. Desde 1200 px compone resumen de operación y comprobante en dos columnas; debajo se apilan en el mismo orden de lectura. A 320 px las acciones se apilan, con controles de al menos 44 px. Los importes usan cifras tabulares; nombres y metadatos extensos ajustan su línea sin truncarse.

`Components/Ventas/SaleReceipt.vue` es el único comprobante para pantalla e impresión. Recibe los importes del servidor, sin recalcularlos. Cada producto conserva nombre, cantidad × precio unitario e importe. Folio, fecha/hora, cajero, total, método, recibido y cambio se mantienen; los dos últimos solo aparecen cuando no son nulos. Se respetan nombre, logo, RFC, dirección, teléfono, encabezado y pie del contrato existente: las opciones de mostrar logo/RFC siguen resueltas en Laravel. No se inventan datos fiscales ni se añaden QR, CFDI o servicios externos.

La vista previa usa blanco, DM Sans y contraste neutro, sin emojis, fondos de marca ni sombras decorativas. El acento del tenant queda en las acciones y el foco. El selector de 80/58 mm controla el ancho del mismo comprobante; es una preferencia local de presentación, sin persistencia ni cambio del contrato Inertia.

### Impresión

El reset de impresión está limitado a la presencia de `.sale-ticket`. Oculta sidebar, header, barra inferior, banners, diálogos, notificaciones y acciones; elimina márgenes de navegación, bordes exteriores, fondos y sombras. El recibo conserva solo negro sobre blanco, separadores y logo configurado. Las filas de producto y parejas monetarias evitan cortes de página; nombres ajustan línea y cifras monetarias no se dividen. La tabla puede continuar en páginas posteriores y repetir su encabezado.

El ancho CSS es `min(100%, 58mm/80mm)`. El usuario debe elegir también tamaño físico, márgenes, escala y encabezados/pies en el diálogo normal del navegador. No hay autodetección de impresora. Se revisaron PDF Chromium a 58 mm, 80 mm y A4; esto no certifica hardware, drivers, área imprimible ni corte de rollo. Una fila más alta que una página puede requerir ajuste manual de papel/escala.

### Cuenta y finalización de sesión

El menú conserva su disclosure con controles nativos. ArrowDown enfoca Mi perfil, Tab alcanza Cerrar sesión y Escape devuelve el foco al disparador. El formulario nativo POST con CSRF permanece montado durante el envío: cerrar un `v-if` en `submit` lo desconectaba y cancelaba la petición. El botón informa «Cerrando sesión…» y evita envíos repetidos. Laravel conserva la invalidación y regeneración de token existentes; el destino público continúa siendo `/`.

Las respuestas autenticadas del tenant llevan `Cache-Control: no-store, private` para evitar que volver atrás restaure contenido privado después del logout. No cambian roles, rutas ni el controlador de sesión. SuperAdmin queda fuera de este ajuste de caché. Las comprobaciones de sesión se realizaron con credenciales de cuentas descartables en Laravel, sin alterar props de rol en JavaScript.

### Logotipo del negocio

El selector anuncia todos los formatos ya aceptados por la regla `image`: JPG, PNG, WebP, GIF y BMP, hasta 2048 KiB (2 MB en la interfaz). El cliente comprueba tamaño y MIME disponible; Laravel sigue siendo la autoridad. El JPG concreto reportado no estaba disponible. Un JPG válido mayor que el límite de PHP reproduce el mensaje anterior «The logo failed to upload»; ahora los errores de tamaño, formato, carga parcial y almacenamiento se explican en español.

Se distinguen «Imagen seleccionada… Sin guardar», «Logotipo guardado» y ausencia de logo guardado. Descartar selección revoca su object URL y vuelve a la imagen persistida. Un error mantiene el borrador identificado como pendiente, muestra el motivo y evita la confirmación de guardado. Al recargar se recupera siempre la referencia persistida. El avatar personal conserva su flujo independiente.

El reemplazo escribe primero, actualiza la configuración bajo transacción/bloqueo y elimina después el anterior. La eliminación también confirma primero la referencia. Un fallo de escritura o persistencia conserva el logo anterior; el fallo de DB intenta retirar la nueva subida. Un fallo al limpiar archivos se registra sin romper la referencia vigente. No cambian límites de PHP/Nginx ni se desactiva validación.

### Cajero

`CashierDashboard.vue` presenta las cuatro métricas propias, efectivo esperado, último corte cuando existe, POS/Caja y últimas ventas con el sistema premium. En móvil las últimas ventas usan una lista con importe visible; desde 768 px utilizan tabla. Los valores proceden de los mismos props del dashboard y no se recalculan. La bienvenida premium del cajero omite inventario y reportes administrativos.

Se conserva la fuente de navegación `tenantNavigation.js`: Inicio, Vender, Ventas y Caja actual. Mi perfil y logout permanecen exclusivamente en cuenta. Cajero tiene cuatro destinos en la barra inferior contando Menú. Cortes conserva su acceso de servidor existente, limitado al usuario; no se añade a la navegación que antes lo reservaba al administrador. POS, Historial, Ticket, Caja, Cortes y Perfil mantienen sus permisos. Las pruebas reales devolvieron 403 para Usuarios, Productos, Reportes, Configuración, Catálogos y ticket ajeno.

Evidencias y límites: [informe de la fase](docs/tickets-logout-logo-cashier-phase-2-12.md). La implementación sigue pendiente de revisión estética del usuario.


## Superadministración · Fase 2.14

`SuperAdminLayout` activa un shell propio de plataforma (`.sa-shell`), con DM Sans local, logo Figma compacto y Lucide. Usa superficies neutras y borgoña `#960A2C`, sin resolver nombre, logo ni colores de un negocio. El contenido tenant y sus tokens dinámicos no cambian. Sin animaciones.

Patrones compartidos: `SaHeading` (título/explicación/acciones), `SaMetric` (etiqueta/cifra/definición, sin porcentajes ficticios), `SaTable` (tabla semántica que recompone las mismas celdas en tarjetas sin eliminar información), `SaPagination` (navegación y totales del servidor) y `SaChart` (una serie, eje desde cero, tooltip y tabla de datos accesible, vacío sin curva decorativa). `SaPlanForm` y `SaCommunicationForm` conservan las operaciones existentes; labels, errores asociados y foco al primer inválido. `UiOverlay` se reutiliza para drawer y contraseña con Escape, contención y restauración del foco. Mensajes persistentes del servidor con cierre manual y roles status/alert.

Sidebar de 232 px desde 992; drawer por debajo. Gutter 32/24/16 px; contenido máximo de 1664 px. Cuatro métricas desde 1200, dos entre 360 y 1199, una bajo 360. Filas de tabla adaptadas al contenedor hasta 920 px, con dos columnas de datos desde 461; se conserva caption, encabezados y roles. Campos y acciones de al menos 44 px, borde de control `#858892`, foco borgoña de 2 px con offset de 3. Titular de 28/25 px, títulos de sección de 15, cuerpo de 14, datos de tabla de 13 y ayuda de 12. Cifras tabulares y nombres/correos largos con reflow. Contraste medido de blanco/borgoña 8.84:1 y secundario/blanco 5.48:1.

Los cuatro KPIs principales son registradas, habilitadas administrativamente, con ventas en 30 días y altas en el mismo período. Atención se presenta aparte, deduplicada y con motivos. Última venta es histórica. Volumen comercial pertenece a los negocios; no se presenta como facturación de la plataforma. Planes muestra distribución y catálogo nominal; no MRR cobrado. El monitoreo declara fuente, instante y alcance del filesystem. Las ausencias de medición se indican como no disponibles.

Definiciones completas, exclusiones, fuentes, pruebas SQL SQLite/MySQL, sesiones reales, accesibilidad, limitaciones y evidencia de nueve páginas × nueve resoluciones: [informe de Fase 2.14](docs/superadmin-premium-phase-2-14.md). [Galería local antes/después](.playwright-mcp/superadmin-2-14/comparison.html). Pendiente aprobación visual del usuario.
