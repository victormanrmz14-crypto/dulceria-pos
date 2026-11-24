# Fase 2.9 · Usuarios, Catálogos e Historial de Ventas

23 de noviembre de 2026, America/Mexico_City. Rama `refactor/premium-ui`, base `f926e2d` (Fase 2.8 aprobada). Los tres apartados están implementados; esta fase queda pendiente de revisión estética.

## Resultado

**Usuarios:** listado con nombre/apellido, username, correo, rol y estado; tabla de escritorio y tarjetas completas en móvil. Inactivos legibles, marca «Tú» y acciones contextuales con Lucide. Se conserva la exclusión del cambio de estado para la cuenta propia y la confirmación original. Alta/edición comparten un marco y formulario agrupado, con labels, errores asociados, rol por teclado, Cancelar y carga. Se mantienen seis inputs y el grupo de rol, sus requisitos y las dos opciones existentes.

**Catálogos:** Categorías y Marcas siguen usando Simple.vue y sus mismos props, handlers y endpoints. Listas/tablas y tarjetas con estado y acciones visibles; formularios de alta/edición sobre el overlay premium. Proveedores mantiene nombre, email, teléfono opcional, notas opcionales, estado y número de productos; conserva los cuatro campos de formulario. No se añaden búsquedas, campos o confirmaciones.

**Historial:** encabezado DM Sans con Lucide y regreso al POS; tabla completa, tarjetas móviles, importes tabulares, método identificado y acción «Ver ticket». Reutiliza AnalyticsSalesList mediante opciones de presentación. Vacío compacto con mensaje, descripción y acción al POS. Sin filtros, exportaciones ni operaciones nuevas.

## Evidencias y responsive

[Galería local antes/después de las siete vistas](../.playwright-mcp/admin-premium/comparison.html), con comparaciones móvil/escritorio, estados y capturas de cada vista en las ocho anchuras. Abrir el HTML local; no necesita servidor de preview. Capturas, fixtures y diagnósticos no forman parte del commit.

| Ancho | Usuarios | Catálogos | Historial | Resultado |
|---|---|---|---|---|
| 320 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-320.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-320.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-320.png) | Sin overflow del documento; registros íntegros |
| 375 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-375.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-375.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-375.png) | Móvil, una columna |
| 390 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-390.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-390.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-390.png) | Móvil, una columna |
| 430 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-430.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-430.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-430.png) | Móvil, una columna |
| 768 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-768.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-768.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-768.png) | Tarjetas en dos columnas |
| 1024 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-1024.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-1024.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-1024.png) | Sidebar; tarjetas en dos columnas |
| 1366 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-1366.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-1366.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-1366.png) | Tablas completas |
| 1920 | [Listado](../.playwright-mcp/admin-premium/after-normal-users-1920.png) | [Categorías](../.playwright-mcp/admin-premium/after-normal-categories-1920.png) | [Ventas](../.playwright-mcp/admin-premium/after-normal-history-1920.png) | Tablas completas y marcos limitados |

La galería añade alta/edición de Usuarios, Marcas y Proveedores en las mismas ocho anchuras. Playwright MCP con Chromium, viewport de 900 px de alto y una sesión redimensionada secuencialmente.

- **144 combinaciones de vista/estado/anchura:** normales, vacíos y datos extensos. Usuarios con doce registros, catálogos con veinte, proveedores con doce y ventas con quince; roles, estados y datos opcionales. Sin overflow del documento, cortes en registros, controles sin label ni objetivos táctiles menores de 44 px.
- **48 composiciones de diálogos:** creación/edición de Categorías, Marcas y Proveedores en los ocho anchos. Campos, prefills, errores y procesamiento conservados; Tab contenido, Escape, retorno al origen y navegación inferior oculta. Formularios de usuario cubiertos por la matriz anterior; contraseña opcional en edición y confirmación requerida al capturar una nueva, como antes.
- **50 comprobaciones de límites:** 399/400 (roles), 599/600 (campos), 767/768 (tarjetas/navegación), 991/992 (sidebar) y 1199/1200 (tablas/introducciones laterales). Sin roturas con datos extensos.
- Nombres, apellidos, usernames, correos, notas y folios extensos; importe `$987,654,321.99` completo. Los registros se ajustan sin truncar. Inputs de una línea conservan su scroll nativo y el valor completo: Home/End permite recorrer el username largo a 320 px. Textareas conservan scroll/resize vertical y sus dos filas iniciales; no se cambian sus tipos o contratos para forzar todo el texto a la vista.
- Errores simulados en las instancias montadas de useForm, con `aria-invalid`, `aria-describedby` y mensaje enlazado, incluido rol y confirmación. Processing simulado muestra disabled, `aria-busy` y carga sin enviar formularios. Selección de rol por teclado con foco de 2 px.
- **85 pares de contraste** pasan en granate, azul, amarillo, blanco y gris `#767676`: textos, estados, rol, marca de cuenta propia, acciones, campos, foco, errores y ticket. Texto secundario sobre blanco 5.48:1; borde de campo 3.47:1; error 6.57:1. No se guarda ninguna paleta.
- La cuenta propia no ofrece activar/desactivar; la confirmación existente de otra cuenta se observa y descarta. En la simulación de cajero, el drawer no muestra Usuarios ni Catálogos. Esto comprueba presentación; no autorización del servidor.
- El final del contenido móvil queda alrededor de y=832, incluyendo su padding, y los controles quedan por encima de la barra que comienza en y=832. Menú y restauración de foco conservados. Sin solicitudes de escritura ni excepciones JavaScript en los recorridos finales registrados.
- Comparación de Reportes a 390/1366: misma geometría y píxeles idénticos fuera del canvas de la gráfica. Se observó variación de pintado dentro del lienzo; sus datos, opciones y código no fueron modificados. Su lista conserva el estilo predeterminado aprobado. Las capturas de diagnóstico de esta comparación se retiran.

## Archivos y componentes

| Archivo | Cambio |
|---|---|
| `resources/js/Pages/Usuarios/Index.vue` | Tabla, tarjetas, rol, estado, identidad y acciones |
| `resources/js/Pages/Usuarios/Create.vue` | Activación premium y marco compartido |
| `resources/js/Pages/Usuarios/Edit.vue` | Activación premium y marco compartido |
| `resources/js/Components/UsuarioForm.vue` | Secciones, labels, errores, rol accesible y pie de acciones |
| `resources/js/Pages/Catalogos/Simple.vue` | Página compartida de Categorías/Marcas, listados y diálogos |
| `resources/js/Pages/Catalogos/Proveedores.vue` | Datos completos, contactos, notas y formulario premium |
| `resources/js/Pages/Ventas/Historial.vue` | Composición, lista compartida, acción de ticket y vacío con POS |
| `resources/js/Components/Analitica/AnalyticsSalesList.vue` | Opciones aditivas para Historial; precedencia CSS estable de las tarjetas |
| `resources/js/Components/Admin/AdminActions.vue` | Nuevo: edición y toggle de presentación; solo emite eventos |
| `resources/js/Components/Admin/AdminStatus.vue` | Nuevo: estado con icono/texto y etiquetas configurables |
| `resources/js/Components/Admin/UserRoleBadge.vue` | Nuevo: identificación del rol existente |
| `resources/js/Components/Admin/AdminEditorFrame.vue` | Nuevo: marco de formulario con Volver |
| `resources/js/Components/Admin/AdminDialog.vue` | Nuevo: marco de diálogo basado en UiOverlay |
| `UI_DESIGN_SYSTEM.md` | Patrones administrativos y registro de aprobación de Fase 2.8 |
| Este informe | Resultados, evidencias y límites |

Reutilizados sin modificar: FinanceWorkspace, UiButton, UiIcon, UiFormField, UiEmptyState, UiOverlay, Paginacion, AppLayout, navegación y tema. No se instalan dependencias.

Los scripts de las seis páginas, desde props/useForm hasta su cierre, son idénticos a `f926e2d`, salvo imports de presentación. UsuarioForm conserva sus props/evento; la comparación estática de controles verifica modelos, restricciones, tipos y condiciones de required. No cambian handlers, confirmaciones, métodos, payloads, endpoints ni reglas de contraseña. Dashboard, POS, Productos, Caja, Cortes y Reportes conservan sus archivos; el único componente aprobado extendido es AnalyticsSalesList, con opciones optativas y valores predeterminados compatibles.

## Entorno, límites y pendientes

Compilación frontend y `git diff --check` correctos; compilan los módulos anteriores. Persiste la advertencia de Browserslist antiguo, sin actualizar dependencias. Revisión de Web Interface Guidelines dentro del alcance visual: sin defectos importantes pendientes; no constituye certificación de accesibilidad.

Sin sesión autenticada disponible: las rutas reales redirigen a login. Se sustituyen respuestas de página únicamente en el navegador, usando componentes/estilos compilados reales de localhost. Para errores/loading se instrumentan temporalmente las respuestas JS de los formularios dentro de Playwright, exponiendo useForm solo en ese navegador. Los bundles del servidor no se instrumentan. Se bloquean solicitudes distintas de GET/HEAD; no se crean, editan, activan ni eliminan registros.

El MCP predeterminado no encontraba Chrome. Se usa Playwright MCP ya instalado con Chromium existente, stdio y socket Unix temporal, sin puerto TCP ni servidor de preview. Al finalizar se retiran fixtures/instrumentación y se cierran navegador y proceso auxiliar; scripts, logs y diagnósticos descartables se eliminan. App y Nginx publican el frontend; MySQL y Redis conservan sus contenedores y volúmenes. Localhost y los assets de las seis páginas responden HTTP 200. Manifest idéntico en checkout/app/Nginx: `2a3fcfd80c46dd7a02ca773dde41c9ba12a836c8e2ba15fdacf770d2f92579d0`.

No se identifican defectos funcionales nuevos confirmados. No se prueban autenticación, autorización, persistencia, validación comercial ni tickets reales; no se inicia auditoría de seguridad o testing funcional. Pendientes: aprobación estética de los tres apartados, dispositivos físicos/teclado nativo, Safari/Firefox y lector de pantalla. Configuración y Superadministración permanecen fuera del alcance. Sin push ni merge; la fase se detiene para revisión.
