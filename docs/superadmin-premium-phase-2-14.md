# Fase 2.14 · Superadministración premium y analítica verificable

Fecha: 24 de noviembre de 2026. Rama: `refactor/premium-ui`. Base inspeccionada: `2ff593f`; incluye `a216583`. El árbol de trabajo estaba limpio al comenzar. No se presupuso equivalencia con GitHub. No se añadió ni eliminó ningún ZIP de Figma.

## Arquitectura visual y alcance

Centro de operaciones de **Dulcería POS**, con identidad Figma de plataforma, DM Sans local, iconos Lucide y acento `#960A2C`. Canvas `#F7F8FA`, paneles blancos, texto `#202126`, texto secundario `#666973`, borde `#DEE0E5`, controles `#858892`. No recibe la paleta o el nombre comercial de un tenant.

La tesis visual es separar el estado de las cuentas, la actividad comercial y las incidencias técnicas. Cuatro tarjetas responden cuántas dulcerías existen, cuántas están habilitadas, cuántas registraron ventas y cuántas son nuevas. La atención queda en una sección secundaria con motivos y acceso al detalle. Las curvas diarias representan negocios distintos con ventas, registros y cantidad de ventas. El importe comercial agregado se presenta separado de cualquier facturación de plataforma.

`SuperAdminLayout` es dueño de la navegación, el drawer y los mensajes de servidor. `SaNavigation` conserva los ocho destinos existentes y el logout nativo. Las páginas coordinan sus consultas/filtros y componen `SaHeading`, `SaMetric`, `SaTable`, `SaPagination` y `SaChart`. `SaPlanForm` y `SaCommunicationForm` poseen sus formularios. Los modales reutilizan `UiOverlay`, incluido el manejo de foco, Escape y Tab. `focusInvalid` lleva el foco al error del servidor después de renderizarlo.

El CSS queda limitado a clases de superadministración; los tokens tenant y sus módulos no cambian. No hay animaciones, contadores animados, tendencias ficticias ni dependencias nuevas de producción.

| Módulo | Resultado |
|---|---|
| Vista general | Cuatro KPIs, tres series diarias, volumen comercial y atención deduplicada; límites y definiciones visibles. |
| Dulcerías | Búsqueda de negocio, nombre/apellido/correo del administrador; filtros de estado, atención y actividad; estado real, plan, usuarios, administrador y última venta histórica; enlace al detalle. |
| Detalle | Estado, plan, administrador, usuarios y productos; ventas históricas y de 30 días; importe del negocio, dos gráficas, últimas ocho operaciones y notas. Se conservan toggle e impersonación. |
| Monitoreo | Comprobaciones actuales de DB/caché/disco, jobs fallidos y actividad comercial independiente del estado administrativo. |
| Usuarios | Nombre, correo y username agrupados en Cuenta; rol, negocio, estado, registro y las dos acciones existentes. Búsqueda/filtros en servidor. |
| Auditoría | Historia paginada, filtros globales, fecha, acción, descripción, usuario, negocio, IP y metadatos existentes desplegables. |
| Soporte | Acceso claro a las herramientas ya existentes; no se añaden mecanismos ni permisos. |
| Comunicación | Anuncios, vigencia/expiración, autor, fechas, formularios y correo administrativo existente; errores asociados y estados de envío. |
| Planes | Distribución global, planes vencidos, catálogo nominal y edición por negocio. Se retira el MRR sin cobros verificables. |

## Diagnóstico de indicadores anteriores

| Origen anterior | Significado real / defecto encontrado | Corrección |
|---|---|---|
| Dashboard `Tenant::count()` | Registros, correcto. | Se conserva. |
| Dashboard `tenantsActivos`, `whereHas(users admin activo)` | Tenants con algún administrador habilitado; no medía `tenants.activo` ni ventas. La UI decía «Operando correctamente». | Estado administrativo mediante `tenants.activo`; uso comercial mediante ventas distintas por tenant. |
| Dashboard `totalTenants - tenantsActivos` | Solo ausencia de administrador habilitado; no medía toda la atención. | Unión explícita de criterios, sin duplicar negocios. |
| Dashboard `tenantsMes` | Altas del mes calendario, junto a gráfica de 30 días con otro período. | Período común de 30 fechas y límite superior explícito. |
| Dashboard `totalUsuarios` | Cuentas con tenant, aunque la etiqueta sugería toda la plataforma. | Se retira de las cuatro tarjetas; Usuarios ofrece el total global con definición. |
| Directorio «Operando bien» / «Tasa activación» | Derivados de presencia del administrador, sin evidencia de operación. | Se sustituyen por habilitación y atención. |
| Monitoreo `MAX(created_at)` después de filtrar al mes | Una venta antigua desaparecía; negocios sin ventas este mes parecían no haber vendido nunca. | Máximo histórico hasta ahora; último período contado por separado. |
| Monitoreo DB `ok=true` | Constante sin comprobación. | Se ejecuta `SELECT 1` y se maneja el fallo. |
| Monitoreo «Caché / Redis» | Podía medir otro driver con una etiqueta incorrecta. Clave compartida. | Nombre del store configurado; clave UUID temporal con escritura/lectura y limpieza. |
| Monitoreo disco `/` | Filesystem del proceso, sin explicar contenedor/volumen; fallo podía parecer 0 %. | Mide `storage_path()`, explica el alcance y distingue indisponibilidad. |
| SaaS `mrr = basico*299 + pro*599` | Valor nominal incluyendo cuentas deshabilitadas/vencidas; no hay evidencia de cobro. | Se elimina el MRR. Distribución, vigencia y tarifas del catálogo permanecen. |
| Detalle ventas/importe del mes y gráfica de 14 días | Períodos distintos y etiqueta de ingresos ambigua. | Período compartido de 30 días; «importe comercial registrado». |
| Formularios de planes | La fecha de presentación se usaba como valor de `input[type=date]`. | Campo ISO `Y-m-d` separado de la fecha legible. |
| Listados cargados completos / auditoría limitada a 200 | Filtros locales y cargas crecientes; auditoría no permitía buscar toda la historia. | Paginación y filtros de servidor sobre todos los registros pertinentes. |

## Convenciones de fecha, fuentes y exclusiones

`Analytics` captura un único instante por petición con `CarbonImmutable`. **P30** = desde las 00:00 de hace 29 días hasta ese instante, ambos inclusive, en `config('app.timezone')`: `America/Mexico_City`. Son 30 fechas de calendario, incluido hoy parcial; no una ventana exacta de 720 horas. Se devuelve `inicio`, `fin`, `zona` y una etiqueta legible. No hay variaciones porcentuales ni comparaciones implícitas.

**V** = registros de `ventas` con `tenant_id` no nulo, tenant existente y `created_at <= fin`. Se conserva el scope `HasTenant`. No se usan `withoutGlobalScopes`, cambios de contexto de tenant ni nuevas reglas de autorización. Las rutas mantienen `auth` y `solo.superadmin`. Los timestamps se consultan con la convención actual de almacenamiento de la aplicación; no se reconvierten ni se reescriben fechas históricas.

No existe un campo de cancelación/reembolso ni un registro de pagos SaaS en estas fuentes. Los importes son suma de `ventas.total`; no son utilidad, saldo de caja, liquidación bancaria ni ingresos de Dulcería POS. Registros sin tenant y ventas fechadas en el futuro quedan fuera de las métricas comerciales. Los ceros de las series representan fechas sin filas; no se interpolan ventas.

## Diccionario de KPIs

| Nombre | Definición y fuente | Fecha / período | Filtros y exclusiones | Interpretación |
|---|---|---|---|---|
| Dulcerías registradas | `COUNT(*)` de `tenants`. | Acumulado actual; no filtra fecha. | Todos los estados y planes. | Registros existentes, no uso del POS. |
| Dulcerías habilitadas | `COUNT(*)` de `tenants` con `activo=true`. | Foto actual; sin fecha. | Excluye deshabilitadas. | Habilitación administrativa; no garantiza administrador ni ventas. |
| Con ventas en 30 días | `COUNT(DISTINCT tenant_id)` de V dentro de P30. | `ventas.created_at`, P30. | Incluye habilitadas y deshabilitadas que vendieron; exclusiones V. | Negocios que registraron al menos una venta. |
| Nuevas en el período | `COUNT(*)` de `tenants` creados en P30. | `tenants.created_at`, P30. | Todos los estados; excluye fechas fuera del período. | Altas, sin inferir conversión ni permanencia. |
| Requieren atención | Conteo de tenants que cumplen al menos un criterio descrito abajo. | Foto actual + P30. | Unión, cada tenant una vez. | Señal operativa; no error técnico ni diagnóstico de abandono. |
| Ventas del período | `COUNT(*)` de V en P30. | `ventas.created_at`, P30. | Exclusiones V; detalle añade tenant seleccionado. | Operaciones registradas; no cantidad de productos. |
| Volumen / importe comercial | `SUM(ventas.total)` de V en P30, salida redondeada a dos decimales. | `ventas.created_at`, P30. | Exclusiones V; detalle añade tenant seleccionado. | Importe de negocios, expresado en MXN según el sistema existente. |
| Registros por día | Conteo de `tenants`, agrupado por fecha. | `tenants.created_at`, P30. | Todos los estados; cero cuando no hay filas. | Evolución diaria de altas. |
| Negocios con actividad por día | Distintos `tenant_id` de V en cada fecha. | `ventas.created_at`, P30. | Exclusiones V. | Un negocio puede aparecer en varios días; no sumar para obtener el KPI único de 30 días. |
| Ventas / importe por día | Conteo y suma de V por fecha. | `ventas.created_at`, P30. | Exclusiones V; detalle por tenant. | Evolución comercial; no serie de facturación SaaS. |
| Última venta | `MAX(ventas.created_at)` de V para el tenant. | Todo el historial hasta `fin`. | No limita al mes ni a P30. | `null` = nunca vendió en la fuente; una fecha anterior a P30 = sin ventas recientes. |
| Ventas históricas | Conteo de V del tenant. | Todo el historial hasta `fin`. | Tenant seleccionado, exclusiones V. | Total de registros históricos de ventas. |
| Usuarios asociados | Conteo de `users` por `tenant_id`. | Foto actual; sin fecha. | Todos los roles/estados asociados. | Cuentas, no sesiones ni usuarios conectados. |
| Productos | Conteo de `productos` del tenant. | Foto actual; sin fecha. | Scope existente, tenant seleccionado. | Registros, no existencias ni unidades vendidas. |
| Cuentas registradas | Conteo global de `users`. | Foto actual; sin fecha. | Incluye plataforma y todos los tenants. | Total global, independiente de filtros/página. |
| Cuentas habilitadas/deshabilitadas | `users.activo=true/false`. | Foto actual; sin fecha. | Incluye cuentas de plataforma. | Estado de cuenta, no actividad comercial. |
| Administradores | `users.rol=admin`, `es_super_admin=false`. | Foto actual; sin fecha. | Excluye superadministradores; incluye ambos estados. | Cuentas con rol admin. |
| Distribución de planes | Conteo de `tenants` agrupado por `plan`. | Foto actual; sin fecha. | Incluye habilitadas, deshabilitadas y vencidos. | Asignación de catálogo, no suscripción pagada. |
| Planes vencidos | Tenants con `plan_expira_en <= ahora`. | `tenants.plan_expira_en`, foto actual. | No incluye expiración nula. | Fecha administrativa alcanzada; no demuestra impago. |
| Anuncios vigentes | `anuncios.activo=true` y expiración nula o posterior a ahora. | `anuncios.expira_en`, foto actual. | Conteo global, no solo la página visible. | Publicación vigente según reglas existentes. |

Criterios de atención: **sin ningún administrador habilitado** (`users.rol=admin`, `activo=true`), **plan vencido**, o **negocio habilitado registrado antes de P30 sin ninguna venta en P30**. Un negocio nuevo sin ventas no se marca por falta de actividad durante su incorporación. La falta de ventas distingue «nunca ha registrado ventas» y «sin ventas en 30 días». La primera condición evalúa todos los administradores; una cuenta antigua deshabilitada no oculta otra habilitada. La tarjeta global puede incluir negocios deshabilitados con problemas de administrador/plan. La lista resumida muestra hasta seis, y el enlace lleva al directorio filtrado completo.

## Monitoreo técnico y límites de cada medición

| Indicador | Fuente y definición | Fecha / período | Exclusiones e interpretación |
|---|---|---|---|
| Base de datos | Ejecución de `SELECT 1` en la conexión configurada. | Petición actual. | Éxito/fallo real; no prueba réplica, backups, integridad o disponibilidad histórica. |
| Caché | `put/get/forget` de clave UUID con TTL de 10 s en `cache.default`. | Petición actual. | No afirma medir Redis si el store es otro. Store `array` se etiqueta como memoria de esta petición. |
| Jobs fallidos | Conteo de `failed_jobs`; lista de diez recientes. | Acumulado conservado; orden por `failed_at`. | Consulta fallida = no disponible, no cero. No mide workers activos, trabajos purgados ni fallos nunca persistidos. |
| Espacio libre | `disk_free_space(storage_path()) / disk_total_space(...) * 100`. | Petición actual. | Libre ≤15 % requiere revisión. Fallo de medición = desconocido. Mide el filesystem visible desde PHP que contiene `storage`; en Docker no representa por sí solo el servidor, el volumen MySQL ni montajes hijos como `storage/app/public`. |

El panel muestra hora y zona de comprobación y permite recargar. No existen sondeos periódicos: no se publica uptime, SLA ni una serie de disponibilidad. Una caída completa de DB puede impedir autenticación, sesiones o datos compartidos de Inertia antes de renderizar el panel: **no es un endpoint externo de salud**.

## Validación de consultas y sesiones auténticas

No se modificaron datos del negocio. Las pruebas Laravel bloquean su ejecución si el entorno no es `testing` con SQLite `:memory:`. Las dependencias de desarrollo se instalaron desde el lockfile en `/tmp/dulceria-phase-2-14`; no se añadieron dependencias al proyecto.

`SuperAdminAnalyticsTest`: **14 pruebas, 251 aserciones**. Comprueban cero negocios, distinción habilitación/uso, atención sin duplicados, varios administradores, inicio exacto y exclusión anterior al período, cambio de mes, fechas futuras, último histórico, nunca vendió, agregaciones por tenant, distribución/expiración de planes sin MRR, paginación/búsqueda global, cuentas de tenants diferentes, auditoría, vigencia de anuncios y salud real/desconocida/fallida. Un control negativo reintrodujo el recorte mensual de la última venta únicamente en la copia aislada: la prueba detectó el fallo, se restauró el servicio y las 14 pruebas volvieron a pasar. [Resultado del control](../.playwright-mcp/superadmin-2-14/regression-results.txt). Las simulaciones de fallos se limitan a los adaptadores de DB/caché en pruebas de `HealthChecks`; las agregaciones consultan tablas reales de la base aislada.

Comando ejecutado dentro de la copia aislada:

```bash
php artisan test --compact --filter=SuperAdminAnalyticsTest
```

Se ejecutó además un probe en **MySQL 8.0.46 / PHP 8.3.37** de un contenedor desechable independiente, esquema exclusivo `phase214_test`, sin conexión al MySQL de negocio. Escenario congelado al 1 de diciembre de 2026 a las 10:00: seis tenants, cinco habilitados, dos con ventas en P30, una alta, cuatro con atención, tres ventas del período e importe **60.60**. Ocho comprobaciones coinciden, incluidos límite del 2 de noviembre, último de octubre, actividad diaria distinta y exclusión de venta futura. [Código local del probe](../.playwright-mcp/superadmin-2-14/mysql-probe.php) y [resultado](../.playwright-mcp/superadmin-2-14/mysql-results.json).

Playwright MCP usó **login real** de un superadministrador sintético, formularios y respuestas de Laravel en `127.0.0.1:8184`, con SQLite de archivo aislado. Fixture: cinco negocios, cuatro habilitados, dos con ventas en 30 días, dos altas, dos con atención, cuatro ventas e importe 5002.00. Se comprobó el documento Inertia entregado por el servidor, sin interceptarlo: [evidencia de datos](../.playwright-mcp/superadmin-2-14/browser-server-data.json). Una segunda base vacía y sesión independiente en `8185` permitió comprobar el dashboard real sin negocios. Los servidores auxiliares se detienen al finalizar.

Se probaron errores reales de validación de contraseña corta y expiración de anuncio pasada: no se cambió ninguna contraseña ni se publicó el anuncio. Se mantuvieron las reglas y se localizaron los mensajes de estos formularios en español. No se ejecutaron envíos de correo, impersonaciones, ventas, inventario, caja ni pruebas funcionales comerciales.

## Rendimiento

- Paginación en servidor: 20 registros en dulcerías, usuarios, planes y comunicación; 25 en auditoría; monitoreo 20 negocios y diez fallos. Los filtros se aplican antes de paginar; no se filtra solo la página actual.
- Eager loading acotado para administradores, tenants de usuarios, autores, usuarios/tenants de auditoría y cajeros de operaciones recientes. Solo se seleccionan los campos necesarios del administrador y del tenant asociado a cuentas.
- Series: hasta 30 filas por agregación de fecha, sin cargar el historial de ventas en PHP. El detalle no vuelve a consultar altas globales para sus gráficas comerciales.
- Probe MySQL: dashboard, **10 consultas / 5.75 ms de SQL** en seis tenants; consulta de tenants con administrador, **dos consultas** tanto para una fila como para seis (límite 20). No son tiempos de respuesta ni un benchmark de producción. [Medición local](../.playwright-mcp/superadmin-2-14/mysql-performance.json).
- `EXPLAIN` utiliza los índices existentes por `tenant_id` para ventas/usuarios; la ordenación de tenants hace scan/filesort en la muestra pequeña. No se añadieron migraciones ni cachés globales de métricas. La agrupación temporal y subconsultas deben reevaluarse con el volumen real; índices compuestos requieren evidencia de carga y una revisión propia.

## Evidencia responsive y accesibilidad

[Galería local antes/después](../.playwright-mcp/superadmin-2-14/comparison.html): 18 capturas iniciales (nueve módulos × 375/1440) y **81 capturas finales** (nueve módulos × nueve resoluciones). Mismos datos base, sesión auténtica; revisión visual en escritorio/móvil. [Mediciones del barrido](../.playwright-mcp/superadmin-2-14/responsive-results.json).

| Resolución | Nueve páginas: overflow documental/visible interno | Composición |
|---|---|---|
| 320 × 568 | Ninguno | KPIs en una columna; filas como tarjetas; drawer. |
| 375 × 667 | Ninguno | KPIs en dos columnas; tarjetas y drawer. |
| 390 × 844 | Ninguno | Dos columnas de KPIs; tarjetas y drawer. |
| 430 × 932 | Ninguno | Dos columnas de KPIs; tarjetas y drawer. |
| 768 × 1024 | Ninguno | Grillas intermedias; tarjetas de dos columnas internas; drawer. |
| 1024 × 768 | Ninguno | Sidebar; KPIs en dos columnas; filas adaptadas al contenedor. |
| 1366 × 768 | Ninguno | Sidebar, cuatro KPIs y tablas. |
| 1440 × 900 | Ninguno | Composición de escritorio y tablas. |
| 1920 × 1080 | Ninguno | Contenido acotado, cuatro KPIs y tablas. |

Transiciones por CSS: sidebar desde 992 px; cuatro tarjetas desde 1200; una tarjeta bajo 360; secciones de dos columnas desde 640. `SaTable` responde al **ancho de su contenedor**, no al dispositivo: filas en tarjetas hasta 920 px, dos columnas de campos desde 461. Todos los datos se conservan. Los encabezados/caption permanecen accesibles aunque su presentación visual móvil cambie. El chequeo de overflow excluye solamente los elementos recortados de forma intencional para accesibilidad.

Estados adicionales: modal en las nueve resoluciones, cierre con Escape y restauración del foco; directorio sin resultados en las nueve; dashboard sin negocios a 320 y 1440. Nombre largo real en fixture de negocio. Un importe de `$9,999,999,999,999.99` se probó **solo mediante sustitución de texto del DOM**, a 320 px, sin overflow: [captura visual](../.playwright-mcp/superadmin-2-14/visual-only-large-amount-320.png). No se usa esa sustitución como evidencia de exactitud de consultas ni se guarda en el negocio.

Accesibilidad comprobada: controles con label o nombre accesible, ninguna omisión detectada en el barrido; objetivos principales medidos ≥44 px de alto; foco visible; salto al contenido; jerarquía de títulos; encabezados de tabla; estados con texto; errores asociados con `aria-invalid`/`aria-describedby`; foco en primer inválido; contención de Tab/Shift+Tab en drawer y modal; Escape y restauración al origen. Cada gráfica tiene título, explicación y tabla de datos por fecha; no depende de hover o color. Se mantiene el zoom permitido.

**11 pares de contraste** verificados: texto 16.07:1, secundario 5.48:1, blanco/borgoña 8.84:1, éxito 6.55:1, atención 5.92:1, error 5.93:1 y contorno de campos 3.54:1. [Pares medidos](../.playwright-mcp/superadmin-2-14/contrast.json). Sin errores JavaScript ni advertencias de consola en el barrido. Esto no es certificación WCAG ni prueba con lector de pantalla o dispositivo físico.

## Métricas no disponibles e instrumentación pendiente

No disponibles: MRR cobrado, facturación SaaS, pagos/facturas, churn de suscripción, usuarios conectados, última sesión fiable, disponibilidad histórica, SLA, estado de workers y tendencias de capacidad. La ausencia de ventas solo permite hablar de actividad registrada; no demuestra abandono, impago ni un error de servicio.

Para revisión futura, sin instalar nada: sondeos periódicos aportarían historia de disponibilidad; heartbeat de workers distinguiría una cola sin fallos de un worker detenido; contabilidad de pagos sería necesaria para ingresos recurrentes cobrados. Estas propuestas requieren aprobación propia. No se instalaron Pulse, Prometheus, Grafana ni integraciones externas.

## Hallazgos fuera de alcance

Documentados para una auditoría posterior, sin alterar su lógica: reenvío de bienvenida cambia la contraseña antes de confirmar entrega y puede mostrarla en un flash si el correo falla; impersonación selecciona el primer administrador según su implementación existente, que puede diferir del administrador habilitado preferido para presentación; manejo/restauración de impersonación y metadatos sensibles de auditoría requieren su revisión específica. No se hizo auditoría general ni prueba de explotación. Los permisos, autenticación y aislamiento existentes permanecen.

Limitaciones: pruebas focalizadas, datasets pequeños, sin carga de producción ni evaluación de planes con integraciones de pago inexistentes. Las pruebas de SQLite y el probe MySQL son complementarios; ninguna captura demuestra por sí sola corrección SQL. Revisión estética final pendiente del usuario.

## Compilación, entrega y archivos

`npm run build` compila todos los módulos, incluidos los anteriores; única advertencia del build: datos de Browserslist antiguos, sin actualizar dependencias fuera del alcance. `git diff --check` sin incidencias. Resultado focalizado: 14 pruebas / 251 aserciones. No se ejecutó la suite funcional comercial ni auditoría de seguridad.

Archivos nuevos: `app/Services/SuperAdmin/{Analytics,HealthChecks}.php`; `resources/css/superadmin.css`; nueve componentes/helper en `resources/js/Components/SuperAdmin/`; `tests/Feature/SuperAdminAnalyticsTest.php`; este informe. Modificados: nueve controladores SuperAdmin (Soporte solo cambia presentación de errores), nueve páginas SuperAdmin, `SuperAdminLayout.vue` y `UI_DESIGN_SYSTEM.md`. Sin cambios en rutas, modelos, migraciones, lógica de ventas/inventario/caja ni archivos de dependencias. Las capturas, probes y bases temporales quedan fuera de Git.

Estado local al cierre: app reconstruida y recreada con esta implementación; `app`, `nginx`, `redis` y `db` en ejecución, MySQL saludable. `http://localhost` devuelve 200; `/superadmin/dashboard` sin sesión devuelve 302 a autenticación. El manifest compilado de PHP y el servido desde el workspace tienen el mismo SHA-256. Una comprobación real desde el contenedor verificó `SELECT 1`, escritura/lectura en **Redis**, cero jobs fallidos conservados y 93.7 % libre en el filesystem que contiene `storage` (valor puntual; no capacidad histórica del servidor). No se consultaron ventas de negocio para las pruebas ni se alteraron datos comerciales.

Servidores auxiliares 8184/8185 detenidos; contenedor MySQL de prueba detenido y eliminado con su almacenamiento anónimo. Capturas y resultados locales conservados fuera de Git. Commit de consultas y pruebas: `065ddb6`; la presentación y este documento se entregan en el siguiente commit. Sin push ni merge.

Inventario de componentes nuevos: `SaChart.vue`, `SaCommunicationForm.vue`, `SaHeading.vue`, `SaMetric.vue`, `SaNavigation.vue`, `SaPagination.vue`, `SaPlanForm.vue`, `SaTable.vue` y `focusInvalid.js`, todos bajo `resources/js/Components/SuperAdmin/`.

### Inventario completo de archivos

| Cambio | Archivo |
|---|---|
| Modificado | `UI_DESIGN_SYSTEM.md` |
| Modificado | `app/Http/Controllers/SuperAdmin/AuditoriaController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/ComunicacionController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/DashboardController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/MonitoreoController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/SaasController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/SoporteController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/TenantController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/TenantDetalleController.php` |
| Modificado | `app/Http/Controllers/SuperAdmin/UsuarioController.php` |
| Nuevo | `app/Services/SuperAdmin/Analytics.php` |
| Nuevo | `app/Services/SuperAdmin/HealthChecks.php` |
| Nuevo | `docs/superadmin-premium-phase-2-14.md` |
| Nuevo | `resources/css/superadmin.css` |
| Nuevo | `resources/js/Components/SuperAdmin/SaChart.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaCommunicationForm.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaHeading.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaMetric.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaNavigation.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaPagination.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaPlanForm.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/SaTable.vue` |
| Nuevo | `resources/js/Components/SuperAdmin/focusInvalid.js` |
| Modificado | `resources/js/Layouts/SuperAdminLayout.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Auditoria.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Comunicacion.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Dashboard.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Dulcerias.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Monitoreo.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Saas.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Soporte.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/TenantDetalle.vue` |
| Modificado | `resources/js/Pages/SuperAdmin/Usuarios.vue` |
| Nuevo | `tests/Feature/SuperAdminAnalyticsTest.php` |
