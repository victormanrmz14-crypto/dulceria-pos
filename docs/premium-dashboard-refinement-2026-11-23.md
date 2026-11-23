# Fase 2.2 · Refinamiento visual del dashboard premium

Implementado sobre `refactor/premium-ui`, con la primera iteración existente como punto de partida. La aplicación conserva sus datos, cuatro indicadores, permisos, rutas y cálculos. Esta fase modifica cinco archivos de interfaz y actualiza la documentación del piloto; no incorpora dependencias.

## Revisión visual

- [Dashboard en vivo](http://localhost:8181/dashboard), con los datos reales del tenant y la sesión existente. Se mantiene la vista previa local de solo lectura del piloto anterior.
- [Galería comparativa](http://localhost:8183/comparison.html), con los seis anchos y dos estados seleccionables.
- [Galería guardada](../.playwright-mcp/premium-refinement/comparison.html), [capturas y resultados](../.playwright-mcp/premium-refinement/).

Las capturas se tomaron con Playwright MCP el 23 de noviembre de 2026, a 320, 375, 768, 1024, 1366 y 1920 px, siempre con viewport de 900 px de alto y página completa. Se comparan la misma fecha, contenido y configuración. Los casos poblados sustituyen respuestas únicamente en el navegador: 38 ventas, $123,456.78, 284 productos, 7 alertas, siete puntos, cinco productos y tres filas. No son operaciones comerciales reales. Al finalizar se retiró la interceptación y se restauró el dashboard real.

## Diagnóstico y correcciones

| Evidencia observada antes | Consecuencia | Corrección implementada |
|---|---|---|
| A 375 px, `$123,456.78` ocupaba dos líneas; la última cifra quedaba sola | Dificulta reconocer y comparar el total | Importes indivisibles, cifras tabulares y composición horizontal de tarjetas por debajo de 480 px |
| Tarjetas estrechas con etiquetas y estados de distinta altura | Jerarquía y ritmo inestables | Etiquetas de 13 px en escritorio, valores fluidos según el espacio de la tarjeta, estados alineados y variante para valores largos |
| Dashboard vacío de 1070 px de alto en escritorio y 1505 px en tablet | Demasiado espacio para indicar ausencia de datos | Vacíos con icono de 40 px y texto contiguo; cuerpo vacío de gráfica de 144 px; tabla vacía de 112 px; análisis vacío en dos columnas desde 768 px |
| Misma presentación vertical para casi todos los estados vacíos | Mensajes dispersos y repetitivos | Una composición compartida con títulos explícitos; ventas recientes alineadas con el encabezado |
| Eje monetario con decimales completos y fuente distinta al resto | El eje consume demasiado ancho en móvil | DM Sans también en Chart.js, etiquetas compactas, cuadrícula tenue y lienzo de 220 px |
| Filas de tabla altas y cajeros comprimidos en columnas estrechas | Mucho scroll y lectura fragmentada | Filas de aproximadamente 60 px, ancho mínimo de cajero y scroll horizontal local, operable con teclado |
| Selección de navegación apoyada principalmente en tinte y color | Estado activo poco definido | Marcador lateral de 3 px, peso semibold y superficie neutra de sidebar |
| Submenús con poca diferenciación de nivel | Agrupación poco clara | Guía vertical, sangría consistente, separación de Gestión, transiciones breves y relaciones `aria-controls` únicas |
| Nombre comercial abreviado en el header | Pérdida de información con nombres largos | Ajuste de línea y altura natural del header |
| Al reducir rápidamente desde escritorio, el canvas retenía brevemente su ancho anterior | Desbordamiento transitorio del documento | `max-width: 100%` en el canvas del piloto; transición de 1920 a 320 px verificada |

La dirección mantiene DM Sans, Lucide, superficies `#f7f8fa` / `#ffffff`, texto `#202126`, texto secundario `#666973` y bordes `#dee0e5`. La única superficie nueva es el fondo de navegación `#fcfcfd`. El acento del tenant sigue concentrado en acción principal, selección y gráfica. Se mantienen controles de al menos 44 px y reduced motion. No se añadieron decoraciones ni nuevos flujos.

La estructura y los propietarios de estado se conservaron: el dashboard compone datos y secciones; las tarjetas presentan sus props; la navegación mantiene sus disclosures locales; el componente de vacío agrupa icono y texto; el CSS del piloto controla la composición responsive. Se mantuvo Composition API y JavaScript del proyecto, sin introducir una migración a TypeScript en esta fase visual.

## Comparativas

| Estado y ancho | Primera iteración | Segunda iteración |
|---|---|---|
| Con datos · 375 px | [Antes](../.playwright-mcp/premium-refinement/before-data-375.png) | [Después](../.playwright-mcp/premium-refinement/after-data-375.png) |
| Sin operaciones · 1366 px | [Antes](../.playwright-mcp/premium-refinement/before-empty-1366.png) | [Después](../.playwright-mcp/premium-refinement/after-empty-1366.png) |
| Sin operaciones · 768 px | [Antes](../.playwright-mcp/premium-refinement/before-empty-768.png) | [Después](../.playwright-mcp/premium-refinement/after-empty-768.png) |
| Con datos · 320 px | [Antes, original](../.playwright-mcp/premium-refinement/before-data-320.png) | [Después](../.playwright-mcp/premium-refinement/after-data-320.png) |

La captura inicial con datos a 320 px conserva un margen exterior causado por el desbordamiento transitorio del canvas. La galería encuadra el área del viewport y enlaza el original completo; no se modificó esa evidencia. La captura final ya tiene 320 px de ancho. Las demás comparativas están disponibles mediante los selectores de la galería.

## Responsive

| Ancho | Columnas de métricas | Overflow final del documento | Total completo en una línea | Altura vacía antes → después |
|---|---|---|---|---|
| 320 | 1, tarjetas horizontales | 0 px | Pasa | 1895 → 1500 px |
| 375 | 1, tarjetas horizontales | 0 px | Pasa | 1535 → 1399 px |
| 768 | 2 | 0 px | Pasa | 1505 → 999 px |
| 1024 | 2 | 0 px | Pasa | 1525 → 1019 px |
| 1366 | 4 | 0 px | Pasa | 1070 → 900 px |
| 1920 | 4 | 0 px | Pasa | 1070 → 900 px |

En los doce casos principales, las cuatro métricas están presentes, las acciones de Caja/POS aparecen antes del primer scroll y los enlaces, botones y disclosures visibles tienen objetivos de al menos 44 × 44 px. La tabla conserva intencionalmente scroll horizontal dentro de su región. Se comprobó que se alcanzan los totales completos usando flechas de teclado.

Se comprobaron también los límites 479/480, 767/768, 991/992 y 1199/1200 px. La cuadrícula pasa a dos columnas en 480 px; la navegación pasa de drawer a sidebar en 992 px; cuatro métricas y análisis con datos en paralelo aparecen en 1200 px. Con análisis vacío, las dos columnas empiezan en 768 px. Ninguna transición probada presenta overflow, incluso al pasar directamente de 1920 a 320 px.

## Pruebas de interacción, identidad y conservación

- 18 comprobaciones de interacción aprobadas: foco inicial del drawer, Enter/Espacio en disclosures, `aria-controls`, foco visible, Tab/Shift+Tab contenidos, Escape, devolución del foco, backdrop, cierre al pasar a escritorio, ids únicos, salto al contenido, detalle de gráfica, scroll de tabla y reduced motion. [Resultados](../.playwright-mcp/premium-refinement/interaction-results.json).
- Navegación real mediante Inertia hacia historial, Caja, POS, Productos, Usuarios y Configuración; también desde el drawer a Productos. En todos los destinos hay cero raíces premium y el scroll del documento queda liberado. [Resultados](../.playwright-mcp/premium-refinement/additional-results.json).
- Nueve primarios de tenant probados en el navegador: rojo, azul, verde, violeta, naranja, amarillo, gris claro, blanco y negro. 72 pares de texto/fondo calculados desde colores renderizados pasan el comprobador de contraste de la skill. Mínimo observado: 4.57:1 en navegación activa verde; botón primario mínimo: 5.02:1. [Pares medidos](../.playwright-mcp/premium-refinement/contrast-pairs.json).
- Nombre comercial largo, nombre de usuario largo, logo rectangular e importe `$123,456,789.90` probados en los seis anchos. Sin pérdida de cifras ni overflow; el ranking desplaza importes largos debajo del producto en móvil. [Captura a 320 px](../.playwright-mcp/premium-refinement/after-long-content-320.png).
- Caso de una sola venta, un producto y siete puntos con seis ceros: gráfica, tabla y ranking visibles; cero stock bajo no genera alerta. [Captura](../.playwright-mcp/premium-refinement/after-one-sale-375.png).
- Tooltip monetario completo inspeccionado visualmente; siete valores completos accesibles mediante el disclosure de datos. [Captura del tooltip](../.playwright-mcp/premium-refinement/after-chart-tooltip-1366.png).
- `npm run build` aprobado. Diagnósticos del editor sin errores en los cinco archivos de interfaz. `git diff --check` aprobado. Sin errores de consola en el recorrido final. Persiste el aviso previo de catálogo Browserslist desactualizado; no se actualizaron dependencias.
- Las ramas heredadas de sidebar, tarjeta y dashboard de cajero se compararon con la copia inicial: son idénticas. Los archivos de backend y el layout compartido no se modificaron durante esta fase.

## Archivos modificados en esta iteración

| Archivo | Cambio |
|---|---|
| [premium.css](../resources/css/premium.css) | Jerarquía, composición responsive, navegación, estados, tabla y contención del canvas, siempre bajo `.pos-premium` |
| [Dashboard.vue](../resources/js/Pages/Dashboard.vue) | Presentación de Chart.js, composición vacía, ranking con importes largos y textos de vacío |
| [StatCard.vue](../resources/js/Components/StatCard.vue) | Variante premium para valores largos e indicador de navegación interna |
| [SidebarNav.vue](../resources/js/Components/SidebarNav.vue) | Estados premium, disclosures accesibles y transiciones |
| [UiEmptyState.vue](../resources/js/Components/Ui/UiEmptyState.vue) | Agrupación semántica del texto y escala de icono |
| [UI_DESIGN_SYSTEM.md](../UI_DESIGN_SYSTEM.md) | Reglas actuales y enlace a esta entrega |
| Este informe y [galería/evidencias](../.playwright-mcp/premium-refinement/) | Comparativas y resultados para aprobación visual |

El checkout ya contenía la primera iteración sin commit, cambios en package/lockfile y otros archivos del piloto. Se preservaron; no corresponden a nuevas modificaciones de esta fase.

## Qué queda por mejorar o validar

- Validación visual del propietario y prueba de lectura con usuarios de caja/administración antes de extender el sistema. La mejora de composición es una decisión de diseño comprobada en navegador, no una medición de productividad en uso real.
- Revisar en dispositivos físicos, Safari/Firefox y lector de pantalla. La validación actual se realizó en Chromium con Playwright MCP; no constituye certificación completa de accesibilidad.
- En móviles, la tabla requiere desplazamiento para consultar método y total. Se preservó esta estrategia para mantener la comparación entre columnas; una vista alternativa de registros sería una decisión de producto posterior.
- Con primarios blancos o casi blancos, el contraste del texto pasa pero la acción principal tiene menos énfasis cromático. Puede definirse una política de identidad para esos tenants en una fase posterior.
- La fuente continúa usando la carga remota existente de DM Sans. Un alojamiento local y una estrategia de precarga podrían revisarse en un trabajo de rendimiento separado.
- No había tickets reales en el tenant: se verificó el destino de enlaces simulados, sin generar ventas ni emitir tickets. La vista previa es de solo lectura y depende de la sesión local existente.

POS, inventario, ventas y superadministración mantienen su diseño actual. El sistema refinado queda como referencia del piloto, pendiente de aprobación visual antes de cualquier extensión.
