# Fase 2.14.1 — Activación y seguimiento operativo

Fecha: 24 de noviembre de 2026. Rama: `refactor/premium-ui`.

## Resultado y pantallas

- **Vista general**: conserva total, habilitadas, actividad reciente y nuevas. Añade pendientes de primera venta y enlaza atención al nuevo centro. El resumen de activación reemplaza la gráfica diaria de negocios; conserva las gráficas de altas y ventas y el volumen comercial. Los criterios de atención se despliegan bajo demanda.
- **Centro de atención** (`/superadmin/atencion`): nueva vista de lectura, 20 negocios por página; filtro por tipo de situación, evidencia concreta, estado administrativo y enlaces al detalle. Orden por fecha de registro e ID. Cada negocio aparece una sola vez aunque tenga varias señales. El filtro selecciona negocios que cumplen la situación y conserva todas sus señales para dar contexto.
- **Detalle de dulcería**: identidad y estado, fecha de registro, productos actuales, primera/última venta, ventas de 7/30 días, primer corte, hitos de activación y señales operativas. Conserva notas, operaciones recientes y acciones administrativas existentes.
- **Directorio y Monitoreo**: reciben los nuevos criterios mediante el servicio compartido, sin cambiar sus flujos. Navegación de escritorio/drawer y breadcrumb incorporan Centro de atención.

No se añadieron mecanismos de cobro, suscripciones, tickets, notificaciones, tareas persistidas, telemetría, migraciones ni dependencias. Las ventas, el inventario, la Caja y los Cortes conservan su lógica. El catálogo administrativo de planes existente permanece; su vencimiento deja de ser una señal de atención operativa. El módulo Planes sigue mostrando ese dato.

## Inspección y fuentes reales

Se comprobó la rama y el estado inicial. Se preservaron cambios ajenos en `.github/workflows/deploy.yml`, `README.md`, `docker-compose.override.yml`, `tests/Feature/Auth/RegistrationTest.php` y el archivo sin seguimiento `docker/check-assets.php`; no forman parte del commit de esta fase.

Se revisaron el Design System, el informe 2.14, controladores SuperAdmin, `Analytics`, modelos, migraciones, `HasTenant`, middleware `SoloSuperAdmin`, rutas e información compartida por Inertia. `tenantConfig` sigue siendo `null` para SuperAdmin y se resuelve desde la cuenta efectiva para un tenant; no se añadió información global a las props compartidas.

Fuentes: `tenants.created_at/activo`, productos asociados por `tenant_id`, `ventas.created_at`, `cortes_caja.fecha_corte` y cuentas `users.rol/activo/tenant_id`. El esquema actual **no contiene estado de venta, marca de cancelación ni borrado lógico**. No se inventa un filtro de canceladas ni se cambia cómo se registran ventas. Si se incorpora cancelación en otra fase, deberá actualizarse la fuente común `Analytics::sales()` y sus pruebas.

La configuración del negocio puede incluir valores predeterminados y no dispone de una evidencia inequívoca de configuración completada por el usuario. Se declara **no verificable**, sin hito ni formulario manual.

## Definiciones y umbrales

Todas las fechas siguen la convención existente de la aplicación: timestamps en `America/Mexico_City`, sin conversión SQL de zona. Un instante `ahora` se captura por instancia de `Analytics` y se comparte entre cálculos de la petición.

**Venta válida V**: fila de `ventas` con `tenant_id` no nulo correspondiente a un negocio existente y `created_at <= ahora`. Incluye efectivo y tarjeta; no requiere un importe positivo para inferir actividad. Fechas nulas o futuras quedan fuera. No equivale a cobro bancario, utilidad ni ingreso de plataforma.

**P30**: desde las 00:00 de hace 29 días hasta la consulta, ambos límites incluidos. Son 30 fechas de calendario, no un mes ni 720 horas exactas. **P7**: desde las 00:00 de hace 6 días hasta la consulta. Ventas antiguas se consultan en el historial completo de V, sin recorte mensual.

| Indicador | Definición exacta |
|---|---|
| Total | Todos los registros actuales de `tenants`. |
| Habilitado | `tenants.activo = true`; es autorización administrativa, independiente de ventas y administrador disponible. |
| Actividad comercial reciente | Existe al menos una V dentro de P30, cualquiera que sea el estado administrativo. |
| Sin primera venta | No existe ninguna V en su historial hasta la consulta; incluye negocios nuevos. |
| Sin ventas recientes | Existe V histórica y ninguna en P30. Se distingue de nunca haber vendido. |
| Nuevo en el período | `tenants.created_at` dentro de P30. |
| Requiere atención | Cumple al menos una de las cuatro señales siguientes; conteo de negocios, sin duplicados. |
| Productos | Conteo de registros actuales asociados, habilitados o deshabilitados; no stock ni unidades vendidas. |
| Primera / última venta | `MIN` / `MAX(ventas.created_at)` de V del negocio. |
| Primer corte | `MIN(fecha_corte)` del negocio entre cortes con `fecha_corte <= ahora`; no usa `created_at` como fecha comercial de corte. |
| Ventas 7/30 | Conteos de V del negocio dentro de P7/P30. |
| Importe 30 | Suma de `ventas.total` de V dentro de P30, redondeada a dos decimales para presentación, MXN según el sistema existente. Pertenece a las dulcerías. |

Los parámetros están centralizados en `config/superadmin.php`: `activity_days=30`, `short_activity_days=7`, `without_products_days=7`, `without_first_sale_days=7`. Las etiquetas de las ventanas de la interfaz corresponden a los criterios iniciales 7/30; cambiar la duración comercial requiere revisar también esas etiquetas. Los umbrales de incorporación y sus textos se resuelven directamente desde configuración.

| Señal | Condición y evidencia |
|---|---|
| Sin productos | Registro hace **al menos 7 días completos** (`created_at <= ahora - 7 días`) y cero productos actuales. Muestra registro y conteo. |
| Sin primera venta | Registro hace al menos 7 días completos y ausencia histórica de V. Muestra registro y ausencia hasta la consulta. |
| Sin ventas recientes | Existe V, pero su última fecha es anterior al inicio de P30. Muestra última venta e inicio del período. |
| Sin administrador habilitado | No existe ninguna cuenta asociada con `rol=admin` y `activo=true`. Se revisan todas; un cajero habilitado no satisface el criterio. |

Las señales incluyen negocios habilitados y deshabilitados. No prueban abandono, fraude, impago ni fallo técnico. Un negocio sin fecha de registro no se clasifica por antigüedad; esa fecha se presenta como dato insuficiente.

## Estado de activación

Los cuatro hitos son independientes y se recalculan al consultar el detalle:

1. **Registro**: fecha de creación disponible; se muestra la fecha. Si es nula, «Datos insuficientes».
2. **Productos registrados**: al menos un producto actual, con conteo verificable. No se afirma conocer la fecha del primer producto histórico, pues pudo haberse eliminado.
3. **Primera venta**: existe V, con su primera fecha; en caso contrario, pendiente.
4. **Primer corte**: existe un corte no futuro, con su primera `fecha_corte`; en caso contrario, pendiente.

Un corte sin ventas no completa la primera venta. Los hitos no son una secuencia obligatoria ni un porcentaje de adopción. Un negocio puede tener productos y ventas con configuración inicial no verificable.

## Consultas, rendimiento y autorización

`Analytics::tenants()` añade subconsultas correlacionadas por `tenant_id`: `COUNT(productos)`, `MIN/MAX(ventas.created_at)`, conteo P30 y conteos de cuentas/administradores. La cuenta administrativa preferida se carga mediante eager loading acotado. `row()` y `signals()` solo transforman esos resultados; no consultan por fila.

`attention()` construye una unión lógica OR de las cuatro condiciones sobre el query de tenants. Usa subconsultas `IN/NOT IN` para existencia de ventas/productos, excluyendo `tenant_id` nulo, y `whereDoesntHave(users)` para administrador. El filtro se aplica **antes** de paginar. Los indicadores globales usan `COUNT`, `COUNT(DISTINCT tenant_id)` y `SUM` en SQL. La lista resumida del dashboard se limita a seis negocios.

En el detalle, el resumen comercial combina en una consulta:

```sql
SELECT COUNT(*) AS historicas,
       COALESCE(SUM(CASE WHEN created_at >= :inicio7 THEN 1 ELSE 0 END), 0) AS ventas_7,
       COALESCE(SUM(CASE WHEN created_at >= :inicio30 THEN total ELSE 0 END), 0) AS importe_30
FROM ventas
WHERE tenant_id = :tenant
  AND tenant_id IS NOT NULL
  AND tenant_id IN (SELECT id FROM tenants)
  AND created_at <= :ahora;
```

Las fechas son parámetros enlazados. Primera/última venta y conteo P30 se reutilizan del agregado del tenant; primer corte usa una consulta `MIN(fecha_corte)`. Se cargan hasta ocho ventas recientes para presentación. Las series existentes agrupan por `DATE(created_at)` y devuelven hasta 30 filas. Nunca se carga el historial de ventas en PHP para contarlo.

Se reutilizan las claves/índices existentes por `tenant_id`; no se añadieron índices sin evidencia de carga. Una prueba con uno y nueve negocios confirma **dos consultas** para lista enriquecida y transformación, sin N+1. Probe adicional sobre el fixture visual: cuatro negocios con señales, **dos consultas / 0.73 ms de SQL** en SQLite local. Es una medición puntual, no un benchmark de producción. [SQL y bindings del probe](../.playwright-mcp/superadmin-2-14-1/performance.json).

La nueva ruta pertenece al grupo existente `auth` + `solo.superadmin`. No cambian middlewares, permisos, modelos ni políticas. Se conservan scopes de tenant. Pruebas HTTP verifican 403 para una cuenta tenant aun manipulando `tenant_id` o la ruta de otro negocio; invitados se redirigen a login.

## Pruebas reproducibles y resultados

```bash
php artisan test --compact --filter='SuperAdmin(Analytics|Operations)Test'
npm run build
git diff --check
```

Resultado: **35 pruebas / 634 aserciones**. `SuperAdminOperationsTest` aporta 21 casos y la suite de analítica previa conserva 14. Ambas bloquean ejecución si no es entorno `testing`, conexión SQLite y base `:memory:`. No se ejecutan contra MySQL comercial. Las dependencias de desarrollo ya estaban disponibles; no se instalaron paquetes.

Cobertura focalizada: negocio recién creado; productos sin ventas; primera venta; ventas recientes; última venta fuera de P30; nunca vendió; dos tenants; fechas futuras; límites exactos y segundo anterior; cambio de mes; primer corte y corte futuro; corte sin venta; registro sin fecha; todos los administradores deshabilitados; administrador habilitado adicional; cajero que no satisface el criterio; conteo deduplicado; paginación; filtro inválido; umbrales configurados compartidos entre SQL/evidencia; ausencia real de estado de cancelación; autorización; error real de consulta y ausencia de N+1.

**Ejemplo SQL real de prueba, tiempo congelado al 1 de diciembre de 2026, 10:00:** negocio A con ventas de 50.50 el 1/oct, 20.20 el 2/nov a las 00:00, 30.30 el 25/nov a las 00:00 y 40.40 el 1/dic a las 10:00. Una venta de 999.99 un segundo después de la consulta queda excluida. Resultado: **4 históricas, 3 en P30, 2 en P7, importe P30 90.90**; primera venta 1/oct, última 1/dic. El primer corte es del 20/nov aunque se inserta después de otro del 25/nov. Las ventas y cortes de B en septiembre no se mezclan con A. Ventana P30: 2/nov–1/dic; una venta del 1/nov a las 23:59:59 queda fuera y conserva la clasificación histórica.

**Fixture visual persistido en SQLite aislado:** seis negocios, seis habilitados, uno con ventas en P30, cuatro sin primera venta, una alta y cuatro negocios con atención. Filtros: dos sin productos, tres sin primera venta por antigüedad, uno sin ventas recientes y uno sin administrador. Una venta reciente de 125.50: P7=1, P30=1 e importe=125.50. El negocio nuevo cuenta como pendiente de primera venta, pero no como señal por antigüedad. Los datos proceden de respuestas reales de Laravel tras login nativo, sin interceptar Inertia ni sustituir props: [evidencia del servidor](../.playwright-mcp/superadmin-2-14-1/server-data.json).

La copia visual está en `/tmp/dulceria-2141`, con SQLite de archivo exclusivo y correo/cache de prueba. Se usaron servidores 8186 y 8187, este último con una segunda base sin negocios. Los fallos de consulta se probaron renombrando temporalmente `ventas` **solo en la base aislada**, con restauración posterior; reintentar recuperó la vista. El registro sin fecha se probó con otro cambio temporal de fixture, también restaurado. No se alteraron datos comerciales reales, se enviaron correos ni se ejecutaron flujos del POS.

## Evidencia responsive y accesibilidad

Playwright MCP con sesiones auténticas y español, marca Figma ya integrada, tema claro actual. **54 capturas principales**: dashboard, atención, detalle con nombre largo, detalle activo, directorio y monitoreo × nueve resoluciones. **18 capturas adicionales**: dashboard sin negocios y atención vacía × nueve. Se añaden seis capturas de error y dos de dato insuficiente a 320/1440. [Galería local](../.playwright-mcp/superadmin-2-14-1/comparison.html), [mediciones principales](../.playwright-mcp/superadmin-2-14-1/responsive-results.json) y [mediciones de vacíos](../.playwright-mcp/superadmin-2-14-1/empty-responsive.json). Las capturas y resultados se conservan fuera de Git.

| Resolución | Resultado | Composición |
|---|---|---|
| 320 × 568 | Sin overflow documental ni interno visible | KPI en una columna, drawer, tablas en tarjetas, estado del hito en otra línea. |
| 375 × 667 | Sin overflow | Dos columnas KPI; contenido de seguimiento en una. |
| 390 × 844 | Sin overflow | Dos columnas KPI; nombres completos con reflow. |
| 430 × 932 | Sin overflow | Misma composición móvil. |
| 768 × 1024 | Sin overflow | Drawer, grillas intermedias y tarjetas adaptadas. |
| 1024 × 768 | Sin overflow | Sidebar; tablas adaptadas al contenedor. |
| 1366 × 768 | Sin overflow | Cuatro KPI y tablas de escritorio. |
| 1440 × 900 | Sin overflow | Secciones operativas alineadas al inicio. |
| 1920 × 1080 | Sin overflow | Contenido acotado y navegación permanente. |

Transiciones existentes conservadas: una/dos columnas KPI a 360 px, sidebar a 992, cuatro KPI a 1200; tablas según contenedor de 920 px. `SaActivation` mueve su estado debajo de la evidencia bajo 480 px. Se espera a que los canvas existentes se ajusten al viewport antes de capturar. Se excluyen de overflow interno únicamente encabezados/captions intencionalmente recortados para accesibilidad; conservan su semántica.

Comprobaciones: etiquetas de controles; objetivos principales ≥44 px; foco visible de 2 px; Tab/Enter en filtro; URL/resultados/selector sincronizados al volver atrás; drawer con Tab contenido, Escape y foco restaurado; enlaces al detalle; nombres largos, breadcrumbs y estados expresados con texto e iconos. Cero errores JavaScript en el barrido. Cuatro contrastes de roles existentes: texto 16.07:1, secundario 5.48:1, acción 8.84:1 y completado 6.55:1. [Pares comprobados](../.playwright-mcp/superadmin-2-14-1/contrast.json).

Capturas representativas: [dashboard escritorio](../.playwright-mcp/superadmin-2-14-1/dashboard-1440.png), [atención escritorio](../.playwright-mcp/superadmin-2-14-1/atencion-1440.png), [atención móvil](../.playwright-mcp/superadmin-2-14-1/atencion-320.png), [activación móvil](../.playwright-mcp/superadmin-2-14-1/detalle-activo-320.png), [dashboard vacío](../.playwright-mcp/superadmin-2-14-1/empty-dashboard-320.png), [error](../.playwright-mcp/superadmin-2-14-1/error-dashboard-320.png) y [dato insuficiente](../.playwright-mcp/superadmin-2-14-1/insufficient-320.png).

## Docker, localhost y límites

Se compilaron frontend e imágenes locales `app`/`nginx`, y se recrearon esos dos servicios con `docker compose -f docker-compose.yml`, conservando bases y volúmenes. La forma explícita usa la configuración de producción existente y deja intacto el override de desarrollo que ya estaba modificado. `db` y `redis` siguen activos; MySQL saludable. `http://localhost` responde 200 y `/superadmin/atencion` sin sesión responde 302 al login. Se verifica la ruta dentro del contenedor y que el manifest usado por PHP coincide con el servido por nginx. El contenedor de producción no incluye el comando de pruebas de desarrollo; PHPUnit se ejecutó con las dependencias existentes del host, no se presenta como una ejecución dentro del contenedor.

Limitaciones: pruebas de agregados en SQLite, sin un nuevo probe MySQL ni prueba de carga en esta fase; muestras pequeñas; sin certificación WCAG, lector de pantalla ni dispositivo físico. La disponibilidad de una consulta no prueba funcionamiento general del negocio. Una caída total de DB puede impedir autenticación, resolución de rutas o props de Inertia antes de mostrar el estado local de error. No se puede recuperar información histórica borrada ni demostrar una acción de configuración con valores predeterminados. No se afirma que los hitos permanezcan completos tras borrar sus evidencias.

El build finaliza correctamente con el aviso previo de Browserslist desactualizado; no se actualizan dependencias ajenas. Los servidores auxiliares se detienen al terminar y la evidencia local queda disponible. Sin push ni merge; se detiene para revisión.

## Archivos de la fase

Nuevos: `app/Http/Controllers/SuperAdmin/AtencionController.php`, `config/superadmin.php`, `resources/js/Components/SuperAdmin/SaActivation.vue`, `resources/js/Components/SuperAdmin/SaQueryError.vue`, `resources/js/Pages/SuperAdmin/Atencion.vue`, `tests/Feature/SuperAdminOperationsTest.php` y este informe.

Modificados: `app/Services/SuperAdmin/Analytics.php`, controladores `DashboardController.php` y `TenantDetalleController.php`, `resources/js/Pages/SuperAdmin/Dashboard.vue` y `TenantDetalle.vue`, `resources/js/Components/SuperAdmin/SaNavigation.vue`, `resources/js/Layouts/SuperAdminLayout.vue`, `routes/web.php`, `tests/Feature/SuperAdminAnalyticsTest.php` y `UI_DESIGN_SYSTEM.md`.

## Siguientes fases propuestas

Revisar los umbrales con casos de uso reales y volumen suficiente; medir planes de ejecución en MySQL con tamaño representativo antes de proponer índices; evaluar si hace falta un responsable y resultado de seguimiento antes de crear tareas; definir evidencia explícita de configuración completada solo si existe un flujo que la produzca. Ninguna propuesta está implementada en esta fase.
