# Fase 2.6 · Productos e inventario

Implementada en `refactor/premium-ui`, sobre `ee01b43`. Pendiente de revisión estética del propietario. Fecha: 23 de noviembre de 2026, America/Mexico_City.

## Resultado

Listado premium con buscador y filtros etiquetados, tabla de siete columnas en escritorio y tarjetas completas en móvil/tablet. Las tarjetas mantienen categoría, marca, estado y las tres acciones originales además de precio y existencias. Stock bajo se reconoce por texto e icono, sin depender solo del color. Los productos inactivos mantienen legibilidad.

Creación y edición comparten una composición de dos secciones, campos más amplios, etiquetas asociadas, errores junto al control y pie Guardar/Cancelar. Se mantienen los ocho campos, las ocho unidades, las opciones antiguas, decimales y condiciones existentes. No se modifica lógica comercial.

Acceso habitual: [http://localhost/productos](http://localhost/productos), creación en [http://localhost/productos/create](http://localhost/productos/create). Para editar, usar el enlace del producto real en el listado; los ids de las capturas son simulados.

## Evidencias indispensables

[Galería local antes/después](../.playwright-mcp/products-premium/comparison.html). No requiere un servidor auxiliar; abrir el HTML local. Las capturas están disponibles en el workspace e ignoradas por Git, no se incluyen en el commit.

| Vista | Antes móvil | Después móvil | Antes escritorio | Después escritorio | Después tablet |
|---|---|---|---|---|---|
| Listado | [390](../.playwright-mcp/products-premium/before-list-390.png) | [390](../.playwright-mcp/products-premium/after-list-390.png) | [1366](../.playwright-mcp/products-premium/before-list-1366.png) | [1366](../.playwright-mcp/products-premium/after-list-1366.png) | [768](../.playwright-mcp/products-premium/after-list-768.png) |
| Creación | [390](../.playwright-mcp/products-premium/before-create-390.png) | [390](../.playwright-mcp/products-premium/after-create-390.png) | [1366](../.playwright-mcp/products-premium/before-create-1366.png) | [1366](../.playwright-mcp/products-premium/after-create-1366.png) | [768](../.playwright-mcp/products-premium/after-create-768.png) |
| Edición | [390](../.playwright-mcp/products-premium/before-edit-390.png) | [390](../.playwright-mcp/products-premium/after-edit-390.png) | [1366](../.playwright-mcp/products-premium/before-edit-1366.png) | [1366](../.playwright-mcp/products-premium/after-edit-1366.png) | [768](../.playwright-mcp/products-premium/after-edit-768.png) |

Estados adicionales: [vacío a 320](../.playwright-mcp/products-premium/after-empty-320.png), [contenido largo e importes a 320](../.playwright-mcp/products-premium/after-stress-320.png), [errores a 390](../.playwright-mcp/products-premium/after-validation-390.png), [paginación y barra inferior](../.playwright-mcp/products-premium/after-pagination-390.png), [loading](../.playwright-mcp/products-premium/after-loading-390.png).

Se conservan veinte capturas y una galería para revisión. Las capturas intermedias, scripts, resultados JSON, logs y mosaicos de inspección se retiran al terminar.

## Responsive y presentación

Playwright MCP con Chromium, viewport de 900 px de alto, una sesión redimensionada secuencialmente. Se cargaron listado, creación y edición en cada anchura. No se enviaron formularios, DELETE ni notificaciones de proveedor.

| Ancho | Listado | Creación | Edición | Overflow horizontal |
|---|---|---|---|---|
| 320 | Tarjetas, una columna | Una columna de campos | Una columna de campos | Ninguno |
| 375 | Tarjetas, una columna | Una columna de campos | Una columna de campos | Ninguno |
| 390 | Tarjetas, una columna | Una columna de campos | Una columna de campos | Ninguno |
| 430 | Tarjetas, una columna | Una columna de campos | Una columna de campos | Ninguno |
| 768 | Tarjetas, dos columnas | Dos columnas de campos | Dos columnas de campos | Ninguno |
| 1024 | Tarjetas, dos columnas | Dos columnas de campos | Dos columnas de campos | Ninguno |
| 1366 | Tabla, siete columnas | Secciones con introducción lateral | Secciones con introducción lateral | Ninguno |
| 1920 | Tabla, siete columnas | Marco limitado a 1040 px | Marco limitado a 1040 px | Ninguno |

Transiciones inspeccionadas en ambos lados: 479/480 (filtros), 599/600 (campos), 767/768 (tarjetas y navegación), 991/992 (sidebar) y 1199/1200 (tabla y secciones). El umbral de tabla protege las siete columnas cuando el sidebar reduce el espacio disponible en un portátil.

Comprobaciones realizadas:

- Quince productos por página con activos/inactivos, cero stock, alertas y proveedor; tarjetas y tabla consumen exactamente los mismos datos y componentes de acciones/stock. No se elimina ninguna columna o acción.
- Nombre extenso, categoría y marca largas, precio `$987,654,321.99` y stock `123,456,789.125 kg` a 320, 768, 1200, 1366 y 1920 px: sin overflow ni precios partidos o cortados.
- Campos de 16 px, controles/enlaces de al menos 44 px. El checkbox tiene label completo como objetivo táctil. Sin campos visibles sin etiqueta.
- Recorrido por teclado buscador → categoría → stock bajo; nombre → categoría; stock mínimo → Cancelar → Guardar. Foco visible de 2 px. Drawer abre, Escape cierra y devuelve foco a Menú.
- Al final de los formularios móviles, el pie queda alrededor de y=807, por encima del inicio de la barra inferior en y=832. Paginación también queda por encima de ella.
- Cuatro errores simulados sobre el `useForm` ya montado: textos visibles, label enlazado, `aria-invalid` y destino `aria-describedby` correctos. Loading simulado: botón disabled, `aria-busy=true` e icono de carga. No se invoca enviar ni se envía un POST.
- Edición a granel conserva `12.375`, mínimo `2.5`, unidad `kg` y step `0.001`. La unidad antigua `costal` se conserva como opción. Esto verifica presentación/atributos, no reglas de inventario ni ventas.
- Cuatro paletas —granate, azul, amarillo y blanco—: 28 pares calculados desde estilos renderizados pasan el comprobador de contraste de la skill. Texto secundario sobre blanco 5.48:1, inactivo 4.85:1, alerta 6.38:1 y borde de control 3.47:1. No se guarda ninguna configuración.
- Sin excepciones JavaScript ni solicitudes de escritura durante la matriz. Reduced motion activado para evitar capturas en fotogramas intermedios; no se añadieron animaciones.
- Dashboard a 1366 px conserva captura idéntica byte a byte. POS conserva datos y composición; su comparación muestra únicamente diferencias de pintado en un borde de 2 px, sin cambios de fuente. Ninguno de sus archivos fue modificado.

## Archivos y componentes

| Archivo | Cambio |
|---|---|
| `resources/js/Pages/Productos/Index.vue` | Composición, filtros, tabla, tarjetas y estado vacío |
| `resources/js/Pages/Productos/Create.vue` | Activación premium y marco compartido; conserva useForm/enviar |
| `resources/js/Pages/Productos/Edit.vue` | Activación premium y marco compartido; conserva useForm/enviar |
| `resources/js/Components/ProductoForm.vue` | Dos secciones, controles, errores y pie; lógica de unidades intacta |
| `resources/js/Components/Paginacion.vue` | Variante premium optativa, sin afectar consumidores heredados |
| `resources/js/Components/Ui/UiFormField.vue` | Nuevo wrapper de campo con id, label y error accesible; sin validación propia |
| `resources/js/Components/Productos/ProductEditorFrame.vue` | Nuevo marco de creación/edición |
| `resources/js/Components/Productos/ProductActions.vue` | Nuevas acciones de presentación reutilizadas en tabla/tarjetas; emiten al handler original |
| `resources/js/Components/Productos/ProductStock.vue` | Nueva presentación compartida de existencias y alerta del servidor |
| `UI_DESIGN_SYSTEM.md` | Patrones y contratos de Fase 2.6 |
| Este informe | Resultados, evidencias y límites |

Reutilizados sin modificar: AppLayout, navegación, UiButton, UiIcon, UiEmptyState, tema y tokens. CampoForm queda intacto para otros módulos. No se añaden dependencias.

Los bloques de script desde props hasta el final de Index, Create, Edit y ProductoForm son idénticos a `ee01b43`, salvo imports de presentación. Filtros, debounce, paginación, confirmación de notificación, endpoints, datos de useForm, opciones y cálculos de granel permanecen intactos. No cambian controladores, modelos, migraciones, rutas ni configuración Docker/Vite.

## Entorno y limitaciones

El MCP predeterminado buscaba un Chrome ausente en `/opt/google/chrome/chrome`. Se utilizó el servidor Playwright MCP instalado con `--executable-path` al Chromium existente, en modo aislado y transporte stdio con socket Unix temporal. **No se abrió ningún puerto TCP auxiliar ni servidor de preview.**

Las rutas reales de Productos requieren autenticación y la sesión disponible había expirado. Las tres vistas se renderizaron con componentes reales y assets servidos por Nginx en `http://localhost`, sustituyendo únicamente respuestas de página en Playwright. No se modifica autenticación en Laravel ni se prueba autorización, persistencia, validación comercial o envío de correos. Los fixtures se retiraron del navegador al terminar.

La compilación frontend y `git diff --check` son correctos. Se actualizan solo los servicios locales `app` y `nginx` para publicar el frontend de esta fase; DB, Redis y sus volúmenes se conservan. Las capturas, logs, credenciales y artefactos no forman parte del commit técnico.

No se observaron nuevos defectos funcionales durante esta revisión de presentación. La venta fraccionaria y el descuento de inventario a granel continúan como pendiente previamente registrado, sin corrección ni pruebas de negocio en esta fase. No se validaron dispositivos físicos/teclado virtual real, Safari/Firefox ni lector de pantalla. La comprobación reducida a 320×500 con un campo enfocado no sustituye un teclado real.

El commit de esta fase no equivale a aprobación estética. El trabajo se detiene para revisión del propietario y no continúa hacia otros módulos.

Comprobación de publicación al cierre: `http://localhost` responde HTTP 200; los assets JS/CSS de las tres vistas responden 200. Manifest de app, Nginx y checkout idéntico: `9db16d2da81996a1ffa1a20db45115bdecad3d2ff2fa0c125177425cea3bf341`. Se retiraron los fixtures del navegador y se cerró el MCP temporal sin dejar puertos auxiliares. DB y Redis conservan sus contenedores anteriores.
