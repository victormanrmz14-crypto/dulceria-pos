# Fase 2.4 · Refactor visual del Punto de Venta

> Archivo de referencia: las capturas, resultados de diagnóstico y vistas previas temporales se retiraron en la Fase 2.5. La aplicación actual se consulta en [http://localhost](http://localhost). Se conservan aquí los patrones, resultados históricos y limitaciones.

**Estado:** implementado; pendiente de revisión y aprobación visual del propietario. La Fase 2.3 se conserva como base aprobada.

**Fecha:** 23 de noviembre de 2026, America/Mexico_City. **Rama:** `refactor/premium-ui`. Sin commits.

## Resultado y revisión

- Vista previa del POS con datos simulados (vista previa temporal retirada).
- Comparativa antes/después (evidencia temporal retirada), también disponible como archivo local (evidencia temporal retirada).
- [Patrones implementados en UI_DESIGN_SYSTEM.md](../UI_DESIGN_SYSTEM.md#fase-24--punto-de-venta-implementada-pendiente-de-revisión-y-aprobación-visual).

El catálogo separa nombre, existencias, precio y unidad en columnas de lectura estable. El buscador tiene etiqueta y jerarquía propia. El carrito se distingue como región de cobro: cantidades con label, subtotales tabulares, total dominante, selección de método por borde/icono/texto y confirmación con resumen. Se eliminan emojis funcionales en el POS y se reutilizan botones, vacíos, iconos y overlays premium.

En móvil, un acceso sticky bajo el buscador muestra productos distintos y total; abre el mismo carrito en un panel inferior. Evita recorrer todo el catálogo para cobrar. En tablet se usa un dialog centrado y en escritorio un panel lateral sticky. No se incorporan pasos ni métodos comerciales nuevos.

## Alcance y preservación

Antes de editar se registró la rama y el diff existente. Había cambios sin commit en package/lockfile, SidebarNav, StatCard, WelcomeModal, AppLayout, Dashboard y Blade, además de componentes y documentación sin seguimiento. El diff de esos ocho archivos versionados se conservó idéntico al registro inicial. No se tocaron fuentes de navegación, estilos compartidos, backend, rutas, contratos Inertia, permisos ni autenticación.

Se comparó el texto del bloque de `Pos.vue` desde `defineProps` hasta `procesar` con la copia anterior: **idéntico**. Solo cambian imports, template y CSS. `PosCheckout` emite los valores a los handlers originales y conserva `v-model.number`, min/max y eventos de cantidad. No contiene lógica de inventario, totales comerciales ni persistencia.

## Matriz de resoluciones

Modo estándar adaptado a las ocho anchuras solicitadas, una sesión Chromium redimensionada secuencialmente, altura 900 px. Herramienta: **Playwright MCP**, ejecutada localmente con el Chromium ya instalado. El conector predeterminado buscaba un Chrome inexistente en `/opt/google/chrome/chrome`; se usó el mismo servidor MCP con `--executable-path`, sin instalar navegadores ni modificar su configuración global.

En cada anchura: catálogo vacío; carrito vacío; catálogo con diez productos; carrito con dos productos; efectivo; tarjeta; confirmación abierta y cancelada. Los estados proceden de fixtures aisladas, sin operaciones reales.

| Ancho | Estado visual | Composición | Overflow horizontal | Capturas |
|---|---|---|---|---|
| 320 | Correcto | Compacta + panel inferior | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 375 | Correcto | Compacta + panel inferior | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 390 | Correcto | Compacta + panel inferior | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 430 | Correcto | Compacta + panel inferior | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 768 | Correcto | Una columna + dialog de carrito | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 1024 | Correcto | Catálogo + carrito de 320 px | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 1366 | Correcto | Catálogo + carrito de 360 px | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |
| 1920 | Correcto | Contenido limitado a 1440 px + carrito | Ninguno | Catálogo (evidencia temporal retirada), efectivo (evidencia temporal retirada), confirmación (evidencia temporal retirada) |

El ancho del documento coincide con el viewport en las ocho resoluciones; el contenido interno de los dialogs tampoco desborda horizontalmente. Con dos productos, total, métodos y cobro están dentro del viewport de 900 px en las ocho anchuras. No se detectaron controles del POS menores de 44 × 44 px; el icono Agregar pertenece a la fila completa, no es un botón separado.

### Transiciones observadas

| Límite | Cambio | Resultado |
|---|---|---|
| 359/360 | Se conserva el nombre y precio; aparece indicador Agregar desde 360 | Comprobado a 320/375; regla CSS explícita |
| 767/768 | Barra inferior → botón de drawer; panel inferior → dialog centrado | Comprobado en ambos lados, sin overflow |
| 991/992 | Drawer → sidebar; acceso móvil → carrito inline | Comprobado en ambos lados, cierre del panel al cambiar |
| 1199/1200 | Carrito 320 → 360 px; reaparecen iconos de catálogo | Comprobado en ambos lados, sin overflow |

## Evidencia adicional

- Tab y Shift+Tab permanecen dentro del carrito y de confirmación. Escape cierra y devuelve foco a Cobrar o Ver carrito. El drawer conserva el retorno a Menú. Recorrido móvil buscador → carrito → primer producto y foco visible de 2 px comprobados.
- Nombre extenso, stock de nueve cifras e importe `$987,654,321.99` a 320 px: textos completos, cifras sin partir y sin overflow del documento ni del dialog. Catálogo (evidencia temporal retirada), carrito (evidencia temporal retirada).
- Diez líneas: scroll local del carrito de 306 px con contenido de 1474 px; total y métodos fuera de ese scroll. Captura (evidencia temporal retirada).
- Altura reducida 390 × 500 con campo enfocado: dialog limitado al viewport, desplazamiento interno y sin overflow horizontal. El botón de cobro requiere scroll en esa altura. Esto aproxima espacio disponible; **no** prueba un teclado físico/virtual real. Captura (evidencia temporal retirada).
- Paletas granate, azul, amarilla y blanca, sin guardar configuración: 24 pares de contraste calculados sobre estilos renderizados. Textos de acción ≥6.70:1; texto secundario 5.48:1; borde de input 3.47:1. Pares (evidencia temporal retirada), resultado (evidencia temporal retirada).
- Cambio en efectivo y resumen recibido/cambio capturados sin confirmar venta. Cambio (evidencia temporal retirada), confirmación (evidencia temporal retirada).
- Mensajes flash de éxito/error simulados y rol cajero simulado sin enlace administrativo al historial. Mensajes (evidencia temporal retirada), cajero (evidencia temporal retirada). No constituyen pruebas de autenticación o autorización.
- Dashboard vacío a 390 y 1366 px: capturas antes/después **idénticas byte a byte**, incluida navegación. Se mantiene la fuente compartida de destinos y selección de Vender.
- Compilación `npm run build` correcta; `git diff --check` correcto. Advertencia existente de Browserslist desactualizado; no se actualizan dependencias. Sin excepciones JavaScript durante la matriz y sin solicitudes de escritura registradas. No se ejecutaron ventas, pruebas funcionales de negocio, auditorías de seguridad ni tareas de animación.

Resultados medidos: matriz JSON (evidencia temporal retirada), presentación y teclado (evidencia temporal retirada).

## Archivos de esta fase

| Archivo | Cambio |
|---|---|
| `resources/js/Pages/Ventas/Pos.vue` | Composición premium, buscador, catálogo, confirmación y CSS scoped |
| `resources/js/Components/Pos/PosCartPanel.vue` | Nuevo contenedor responsive del carrito; estado exclusivamente de presentación |
| `resources/js/Components/Pos/PosCheckout.vue` | Nuevo componente de presentación de líneas, errores y cobro, contratos TypeScript |
| `UI_DESIGN_SYSTEM.md` | Patrones de Fase 2.4 y registro de aprobación previa de navegación |
| `docs/responsiveness-check-2026-11-23.md` | Este informe |
| `.playwright-mcp/pos-premium/` | Capturas, galería, copias de referencia, fixtures, servidor de preview y scripts de revisión MCP; no se importan en la aplicación |

Reutilizados sin modificar: `UiButton`, `UiEmptyState`, `UiIcon`, `UiOverlay`, tema y `AppLayout`. `Search` se importa directamente de la dependencia Lucide existente. `ModalPos` permanece intacto para las demás vistas.

## Límites y pendientes

La vista previa de fases anteriores no estaba ejecutándose. Docker/Laravel no están disponibles en este entorno actual; la preview usa los componentes reales compilados y respuestas Inertia simuladas en un servidor separado. Sirve GET y rechaza escrituras; no conecta con base de datos ni crea sesiones. El botón final de Confirmar no registra operaciones en esta preview. No se valida persistencia, inventario, impresión, latencia o errores reales del servidor. En la aplicación normal se conserva el endpoint original.

No se comprobaron Safari/Firefox, lector de pantalla, dispositivos físicos, safe area real, teclado virtual real ni sesión autenticada de cajero. Reduced motion estuvo activado durante las capturas para evitar fotogramas intermedios; no se añadieron animaciones. La revisión visual técnica no equivale a certificación de accesibilidad ni aprobación del diseño.

Observaciones funcionales por lectura del código, **sin corregir ni ejecutar pruebas de negocio**:

1. Cantidades a granel: el handler existente usa `parseInt` y conserva `min="1"`; la fracción no tiene tratamiento consistente. Se mantiene como pendiente conocido.
2. El error `errores.monto` participa en `hayErrores`, que deshabilita Cobrar; editar el monto o cambiar de método no limpia ese error directamente. Se documenta para una fase funcional posterior, sin modificar sus condiciones de validación.

La Fase 2.4 queda pendiente de que el propietario revise la interfaz y confirme que le gusta. No se continúa con historial, tickets, productos, caja, usuarios ni otros módulos.
