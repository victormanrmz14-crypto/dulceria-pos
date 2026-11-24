# Fase 2.8 · Reportes y Analítica

23 de noviembre de 2026, America/Mexico_City. Rama `refactor/premium-ui`, base `ae978a7`. Implementación técnica pendiente de aprobación estética.

## Resultado

Reportes adopta el sistema premium: encabezado y rango claros; filtros separados; cuatro indicadores legibles; línea de ingresos y donut de pagos proporcionados, con tramado para distinguir tarjeta incluso con marcas grises; ranking por cantidad; historial completo con tabla en escritorio y tarjetas móviles. Se conservan DM Sans, Lucide, marca configurable y navegación. No se añaden estadísticas, comparaciones, porcentajes ni bibliotecas.

Ingresos significa **ingresos por ventas**, sin equipararlos a utilidad ni efectivo en caja. Los cuatro valores se consumen directamente del backend. El ranking conserva su orden; gráficos, tickets, métodos y paginación mantienen datos y destinos originales.

## Evidencias visuales y responsive

[Galería local antes/después, filtros y estados](../.playwright-mcp/reports-premium/comparison.html). Abrir el HTML local, sin servidor auxiliar. Contiene 26 capturas indispensables, ignoradas por Git. Antes/después usan los mismos fixtures en 390 y 1366 px, tanto con ventas como sin ellas.

| Ancho | Evidencia | Composición | Resultado |
|---|---|---|---|
| 320 | [Captura](../.playwright-mcp/reports-premium/after-normal-320.png) | Fechas en una columna; métricas, ranking y ventas móviles | Sin overflow ni valores cortados |
| 375 | [Captura](../.playwright-mcp/reports-premium/after-normal-375.png) | Fechas en dos columnas; indicadores compactos | Sin overflow ni valores cortados |
| 390 | [Captura](../.playwright-mcp/reports-premium/after-normal-390.png) | Móvil, tarjetas completas | Sin overflow ni valores cortados |
| 430 | [Captura](../.playwright-mcp/reports-premium/after-normal-430.png) | Móvil, tarjetas completas | Sin overflow ni valores cortados |
| 768 | [Captura](../.playwright-mcp/reports-premium/after-normal-768.png) | Métricas, ranking y ventas en dos columnas | Sin overflow ni valores cortados |
| 1024 | [Captura](../.playwright-mcp/reports-premium/after-normal-1024.png) | Sidebar y resultados en dos columnas | Sin overflow ni valores cortados |
| 1366 | [Captura](../.playwright-mcp/reports-premium/after-normal-1366.png) | Cuatro indicadores; gráficas 1.8:1; ranking horizontal; tabla completa | Sin overflow ni valores cortados |
| 1920 | [Captura](../.playwright-mcp/reports-premium/after-normal-1920.png) | Mismo sistema, contenido limitado por el marco premium | Sin overflow ni valores cortados |

Playwright MCP con Chromium, una sesión redimensionada secuencialmente, altura de 900 px. Se comprueban también ambos lados de 360, 600, 768, 992 y 1200 px con datos normales y extensos: veinte combinaciones sin roturas. La tabla aparece desde 1200 px; la navegación conserva sus breakpoints existentes.

Comprobaciones de presentación realizadas:

- **56 combinaciones de estado/anchura:** datos normales, sin ventas, una venta, importes/nombres extensos, rango anual, venta de importe cero y ranking sin actividad. Sin overflow del documento ni de contenido importante; controles sin labels ausentes y objetivos táctiles de al menos 44 px.
- **48 combinaciones de gráficas/anchura:** siete fechas, un punto, treinta días, 366 días, importes grandes y valores cero. Datos y labels de las instancias reales de Chart.js coinciden con los props; sin etiquetas de fecha superpuestas. Línea de 220 px, donut de 152 px, sin animación. Tooltip observado con `$987,654,321.99` completo.
- Los importes diarios grandes y sus agregados de miles de millones se muestran sin partir moneda/céntimos. Los textos extensos incluyen productos, folios, responsables y cantidades; no se truncan nombres ni campos financieros.
- Sin ventas no se montan canvas ni escalas. Con una venta de importe cero se conserva su dato cero; el reparto sin importes muestra un vacío específico. Productos sin actividad no reciben posiciones inventadas.
- Los cuatro presets conservan sus href; personalizado mantiene `periodo`, `desde`, `hasta` y `preserveState`. Se observa «Activo», fechas completas y «Aplicando…» con disabled/`aria-busy`. La respuesta se simula: no constituye prueba de fechas del servidor.
- Errores compartidos de fecha se presentan con label, `aria-invalid`, `aria-describedby` y mensaje asociado. Tab recorre los presets con foco de 2 px; el disclosure abre con Enter y su lista diaria puede enfocarse/desplazarse con teclado.
- Paginación conserva query y acceso al ticket. Al final móvil termina en y≈791, antes de la navegación en y=832. Menú abre y Escape devuelve foco al botón existente.
- **65 pares de contraste** pasan en granate, azul, amarillo, blanco y gris `#767676`. El activo usa superficie blanca para proteger marcas cercanas al umbral. Texto secundario sobre blanco 5.48:1; borde de campo 3.47:1; segmento neutral 4.25:1; errores 6.57:1. No se cambia ni guarda configuración de tenant.
- Sin excepciones JavaScript ni solicitudes de escritura durante los recorridos finales. Reduced motion activado. La coherencia se apoya en los componentes, tokens y composiciones de las fases anteriores, conservados sin cambios.

## Archivos y conservación

| Archivo | Cambio |
|---|---|
| `resources/js/Pages/Reportes/Index.vue` | Composición premium, filtros etiquetados, estados, métricas y composición analítica |
| `resources/js/Components/Analitica/AnalyticsChart.vue` | Nuevo: estilos y opciones de Chart.js para línea/donut, sin transformar valores |
| `resources/js/Components/Analitica/AnalyticsRanking.vue` | Nuevo: clasificación recibida y valores ya formateados, con vacío |
| `resources/js/Components/Analitica/AnalyticsSalesList.vue` | Nuevo: tabla/tarjetas, ticket y paginación con los campos existentes |
| `UI_DESIGN_SYSTEM.md` | Patrones y contratos de analítica |
| Este informe | Evidencias, comprobaciones y límites |

FinanceWorkspace, FinancialMetric, UiButton, UiIcon, UiFormField, UiEmptyState, Paginacion, Grafica y AppLayout se reutilizan **sin modificar**. Los componentes nuevos usan Composition API y contratos TypeScript.

Props, períodos, modelos locales de fecha y función `aplicarPersonalizado` son idénticos a `ae978a7`, comprobados por comparación textual. Los mapeos de fecha/total y efectivo/tarjeta conservan valores y orden; cambian únicamente opciones gráficas y etiquetas decorativas. El estado de carga observa visitas Inertia y libera sus listeners al desmontarse. Los scripts originales de Dashboard, POS, Productos, Caja y Cortes, layout, CSS premium, Grafica y los controladores/servicio protegidos permanecen idénticos. No se cambia lógica comercial ni base de datos.

`npm run build` y `git diff --check` correctos; compilan todos los módulos existentes. Persiste la advertencia de Browserslist antiguo, sin actualizar dependencias. La revisión de Web Interface Guidelines no deja defectos visuales importantes pendientes en el alcance intervenido; no equivale a certificación de accesibilidad.

## Límites, entorno y pendientes

La sesión disponible redirige a login. Se sustituyen únicamente respuestas de página dentro del navegador, cargando los assets reales de localhost. Para inspeccionar valores/ejes/tooltips se expone temporalmente la clase de Chart.js mediante la respuesta JavaScript interceptada en Playwright; no se modifica el bundle del servidor ni el código del proyecto. Los fixtures no se persisten y se bloquean solicitudes distintas de GET/HEAD. Autenticación, permisos, consultas, fechas comerciales y persistencia no se validan funcionalmente.

El MCP predeterminado no encontraba Chrome; se usa el Playwright MCP instalado con el Chromium existente, stdio y socket Unix temporal, sin puerto TCP ni preview adicional. Se retiran interceptores, instrumentación, scripts y logs al terminar; se cierra el navegador y su proceso auxiliar. App y Nginx publican los nuevos assets; MySQL y Redis conservan sus contenedores y volúmenes. Localhost responde HTTP 200. Manifest idéntico en checkout/app/Nginx: `647276184167e07ad0f605bec2e7e684a1df38fcae230fb4e3b02b76111ee02b`.

**Posible desfase de rango, sin corregir:** `app/Http/Controllers/ReporteController.php:142` limita con `subDays(MAX_DIAS_PERIODO)` mientras `index()` incluye ambos extremos al generar las fechas. Un rango personalizado que active el límite nominal de 366 días podría producir 367 fechas incluidas. Es una observación estática; queda pendiente aclarar si el máximo cuenta intervalos o fechas y confirmarlo funcionalmente en otra fase. La simulación anual de esta revisión usa 366 puntos y no prueba ese recorte del backend.

Las sospechas previas de Caja y Cortes no se investigan ni corrigen. Pendientes: aprobación estética, dispositivos físicos, teclado/calendario nativos, Safari/Firefox y lector de pantalla. No se realiza testing de operaciones comerciales ni auditoría de seguridad. No hay push, merge ni continuación hacia otros módulos.
