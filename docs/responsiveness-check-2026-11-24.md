# Responsive · Fase 2.12

Revisión 23–24 de noviembre de 2026, Chromium mediante Playwright MCP. Rama `refactor/premium-ui`. Altura de viewport 900 px. [Informe técnico completo](tickets-logout-logo-cashier-phase-2-12.md) y [galería local](../.playwright-mcp/tickets-logout-logo-cashier/comparison.html).

## Matriz

Sesión auténtica de Laravel con cajero creado mediante el formulario existente en una base aislada. Datos de ventas/productos/cortes son fixtures de presentación de esa base; no se ejecutaron cobros ni se simularon roles.

| Ancho | Navegación | Ticket | Dashboard de cajero | Logo / login | Overflow documental |
|---:|---|---|---|---|---|
| 320 | Barra inferior + drawer | Vertical; acciones apiladas | Métricas en filas, ventas en lista | Formulario refluye | Ninguno |
| 375 | Barra inferior + drawer | Vertical | Métricas en filas, ventas en lista | Correcto | Ninguno |
| 390 | Barra inferior + drawer | Vertical | Métricas en filas, ventas en lista | Correcto | Ninguno |
| 430 | Barra inferior + drawer | Vertical | Métricas en filas, ventas en lista | Correcto | Ninguno |
| 768 | Drawer | Vertical | Métricas en 2 columnas, tabla | Correcto | Ninguno |
| 1024 | Sidebar | Vertical | Métricas en 2 columnas, tabla | Correcto | Ninguno |
| 1366 | Sidebar | Resumen y comprobante en columnas | Métricas en 4 columnas, tabla | Correcto | Ninguno |
| 1920 | Sidebar | Dos columnas, contenido limitado | Métricas en 4 columnas, tabla | Correcto | Ninguno |

Se revisaron Dashboard, POS, Historial, Ticket, Caja, Cortes, detalle de corte y Perfil: 64 estados con datos. Inicio/Historial/Caja/Cortes/Perfil se revisaron también sin operaciones propias: 40 estados. Apariencia agregó 8 estados. No hubo textos visuales recortados detectados; las captions y textos para lector de pantalla ocultos deliberadamente se excluyen del diagnóstico. Nombres e importes grandes del ticket se comprobaron en 16 escenarios adicionales de presentación simulada, con ambos anchos de papel.

## Transiciones comprobadas

| Transición | Antes | Después | Umbral |
|---|---|---|---|
| Barra inferior → drawer de tablet | 767 px: barra visible | 768 px: barra oculta | 768 px |
| Drawer → sidebar | 991 px: sin sidebar | 992 px: sidebar visible | 992 px |
| Ticket vertical → dos columnas | 1199 px: una columna | 1200 px: dos columnas | 1200 px |
| Lista móvil de últimas ventas → tabla | <768 px | ≥768 px | 768 px |

En la última revisión se sustituyó la tabla móvil de ventas recientes por una lista para conservar el total visible sin desplazarse horizontalmente. El documento y el comprobante no desbordan en ninguno de los ocho anchos.

## Interacción y resultado

16 ciclos de login/logout por administrador/cajero, con POST real, CSRF e invalidación; teclado ArrowDown, Tab, Enter y Escape; foco restaurado y ausencia de vista privada al volver atrás. Se comprobó login con Enter desde contraseña. Drawer móvil y de tablet, navegación real a Caja/POS/Historial/Perfil y Escape en los tres diálogos de Caja sin enviar operaciones. El selector de papel tiene foco visible de 2 px y puede quedar completamente visible sobre la barra inferior.

Los controles revisados de Configuración conservan al menos 44 px. El recibo imprime sin navegación a 58/80 mm; los archivos PDF y los casos largos están en la galería. Las paletas se comprobaron con colores calculados: 11 pares de contraste aprobados. Estos controles de accesibilidad complementan la revisión de layout; no constituyen certificación WCAG.

Sin hallazgos críticos/altos/medios de layout pendientes en los estados revisados. Límites: Chromium únicamente, sin dispositivo o impresora física. La revisión estética permanece pendiente del usuario.
