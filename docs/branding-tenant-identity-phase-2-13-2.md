# Fase 2.13.2 · Branding Figma e identidad dinámica del tenant

24 de noviembre de 2026. Rama exclusiva `refactor/premium-ui`; base `d050081`. Fases anteriores consolidadas, árbol inicial sin cambios salvo el ZIP aportado. Implementación lista para revisión visual; no se realiza push ni merge ni se continúa con Superadministración.

## A. Identidad visual

### Fuente aprobada y selección

Se inspeccionaron el ZIP `dulceria-pos-figma-assets-seleccionados.zip`, su README y los doce SVG incluidos. XML válido; elementos `svg`, `g`, `path`, `rect`; sin scripts, eventos, DOCTYPE, referencias externas ni fuentes enlazadas. La composición tipográfica está trazada. Los recursos integrados coinciden byte por byte con el ZIP, sin modificar geometría, colores, proporciones o espacios negativos.

| Archivo final en `public/brand/` | Dimensiones originales | Uso |
|---|---|---|
| `logo-primary.svg` | 300 × 65 | Autenticación, fondo claro, con descriptor |
| `logo-compact.svg` | 300 × 65 | Encabezado de autenticación bajo 992 px, sin descriptor |
| `logo-white.svg` | 301 × 65 | Landing: header, footer y maquetas de producto sobre fondo oscuro |
| `symbol-color.svg` | 168 × 99 | Sello de autenticación y componente de logo heredado |
| `favicon-16.svg` | 16 × 16 | Favicon reducido específico |
| `favicon-32.svg` | 32 × 32 | Favicon reducido específico |
| `favicon-64.svg` | 64 × 64 | Favicon SVG general |
| `favicon-16.png`, `favicon-32.png`, `favicon-64.png` | 16/32/64 px | Fallbacks originales y touch icon de 64 px |
| `android-foreground.svg`, `android-background.svg` | 108 × 108 | Fuentes reservadas para la fase Android; no se cargan en web |

`public/favicon.ico` se reconstruyó como contenedor de los tres PNG originales, sin conversión ni remuestreo. `platform-favicons.blade.php` comparte las referencias entre autenticación, landing y la plantilla Inertia. Los PNG reemplazados y el ICO usan versión de URL `figma-2132`; las nuevas rutas SVG evitan reutilizar el favicon provisional. Los doce assets y el ICO se comprobaron por HTTP en localhost. No quedan referencias activas a `brand/favicon.svg` o al wordmark provisional.

No se publican `logo-graphite.svg` ni `symbol-burgundy.svg`: no hay consumidor que los necesite. `android-composite-reference.svg` es referencia de diseño y queda en el ZIP. Tampoco se publica el README del paquete ni el ZIP. Se retiraron los logos, símbolos, favicons, iconos de aplicación y lámina HTML provisionales, además de `scripts/generate-brand.py`, que generaba la identidad anterior. Se conservan fuente local DM Sans, licencia y tokens.

### Cambios de presentación

- Login, registro, recuperación, restablecimiento, confirmación de contraseña y verificación de correo comparten las variantes definitivas mediante el layout Blade existente. Landing usa la variante blanca; sus maquetas también dejan de mostrar el emoji como logotipo de plataforma.
- `platform-logo.blade.php` declara 300/301 × 65 y CSS mantiene altura automática. El ancho existente se conserva: 206 px, 184 px en teléfono. El símbolo independiente usa 62 px y altura automática; su proporción 168:99 no se fuerza a un cuadrado. Se retiraron rotación y sombra del contenedor del sello.
- El token de acción pública se alinea con el borgoña Figma `#960A2C`. Contrastes comprobados: blanco/borgoña 8.84:1 y foco sobre panel claro 7.92:1. Los colores de tenants permanecen configurables.
- No se duplica el nombre junto al wordmark. Formularios, campos, rutas, envíos y comportamiento existentes se conservan. No se usa escalado global, zoom CSS ni bloqueo de scroll.
- Android conserva únicamente las dos fuentes aprobadas; fondo `#F7F3F2`. No se implementan manifiesto, APK, splash, XML nativo ni máscaras.

### Evidencia antes/después

[Galería local completa](../.playwright-mcp/branding-tenant-2-13-2/comparison.html). Capturas y diagnósticos están excluidos de Git según lo solicitado. Las comparaciones anteriores proceden de `d050081`; las posteriores usan la implementación de esta fase. Páginas públicas verificadas en `http://localhost`, después de actualizar las imágenes Docker.

| Pantalla | Antes | Después |
|---|---|---|
| Login, 1366 px | [Captura](../.playwright-mcp/branding-tenant-2-13-2/before-login-1366.png) | [Captura](../.playwright-mcp/branding-tenant-2-13-2/after-login-1366.png) |
| Login, 320 px | [Captura](../.playwright-mcp/branding-tenant-2-13-2/before-login-320.png) | [Captura](../.playwright-mcp/branding-tenant-2-13-2/after-login-320.png) |
| Registro, 1366 px | [Captura](../.playwright-mcp/branding-tenant-2-13-2/before-register-1366.png) | [Captura](../.playwright-mcp/branding-tenant-2-13-2/after-register-1366.png) |
| Recuperación, 1366 px | [Captura](../.playwright-mcp/branding-tenant-2-13-2/before-forgot-password-1366.png) | [Captura](../.playwright-mcp/branding-tenant-2-13-2/after-forgot-password-1366.png) |
| Restablecimiento, 320 px | [Captura](../.playwright-mcp/branding-tenant-2-13-2/before-reset-password-320.png) | [Captura](../.playwright-mcp/branding-tenant-2-13-2/after-reset-password-320.png) |
| Landing, 1366 px | [Captura](../.playwright-mcp/branding-tenant-2-13-2/before-landing-1366.png) | [Captura](../.playwright-mcp/branding-tenant-2-13-2/after-landing-1366.png) |

### Responsive comprobado con Playwright MCP

Cinco páginas públicas × nueve resoluciones: **45 mediciones**, todas sin overflow horizontal, con SVG cargados y proporciones correctas. Alturas del documento en píxeles CSS:

| Viewport | Login | Registro | Recuperación | Restablecimiento |
|---|---:|---:|---:|---:|
| 320 × 568 | 568 | 910 | 568 | 663 |
| 375 × 667 | 667 | 930 | 667 | 667 |
| 390 × 844 | 844 | 997 | 844 | 844 |
| 430 × 932 | 932 | 979 | 932 | 932 |
| 768 × 1024 | 1024 | 1024 | 1024 | 1024 |
| 1024 × 768 | 768 | 768 | 768 | 768 |
| 1366 × 768 | 768 | 768 | 768 | 768 |
| 1440 × 900 | 900 | 900 | 900 | 900 |
| 1920 × 1080 | 1080 | 1080 | 1080 | 1080 |

Login y recuperación completos sin scroll; registro con scroll legítimo en teléfono y sin scroll desde tablet. Restablecimiento conserva scroll legítimo a 320 × 568. Landing conserva su documento largo de marketing; no se intenta comprimirlo. Inputs de 48 px y texto de 16 px intactos. Comprobaciones adicionales de login a 1366 × 600, 390 × 360 y reflujo 683 × 384: sin overflow horizontal, scroll natural donde hace falta y foco visible de 2 px. El teclado virtual y el zoom nativo no se sustituyen por estas emulaciones.

## B. Identidad multi-tenant

### Causa y fuente única

Antes, `tenantConfig.nombre_mostrar` contenía exclusivamente `Tenant.configuracion.negocio.nombre_mostrar`, opcional. `AppLayout` y `SidebarNav` tenían por separado el fallback fijo «Dulcería POS», ignorando `Tenant.nombre`. Se reprodujo con login real del administrador A: «Dulcería POS / Inicio» aun existiendo «Dulcería La Estrella» en el registro.

Se añade únicamente **`tenantConfig.nombre`** a las propiedades compartidas de `HandleInertiaRequests::share()`. Se resuelve desde `$request->user()->tenant`, usando la relación Eloquent autorizada del usuario efectivo:

1. Nombre comercial configurado, cuando no está vacío tras `trim`.
2. Nombre registrado `Tenant.nombre`, cuando no está vacío.
3. «Dulcería POS» como fallback de presentación.

El dato no se modifica en base de datos ni se recorta. Los campos previos de `tenantConfig`, incluido `nombre_mostrar`, conservan sus contratos. No se añaden consultas desde componentes, un servicio de selección de tenant, caché global ni localStorage. La relación se carga para la solicitud actual; admin y cajero del mismo negocio reciben el mismo nombre. Para invitado, superadministrador o ausencia de relación, `tenantConfig` sigue siendo `null` y el componente aplica el fallback de plataforma.

`TenantIdentityName.vue` es la representación compartida: lectura reactiva de las propiedades Inertia y disclosure nativo del nombre completo. Sidebar de escritorio, drawer y breadcrumbs consumen el mismo componente. Los títulos de documento y atribuciones de plataforma en otras superficies no se reemplazan globalmente. No se modifica SuperAdminLayout.

### Breadcrumbs y sidebar

`AppLayout` corrige centralmente Inicio, Productos, Usuarios, Caja actual, Cortes históricos, Vender, Ventas, Reportes, Configuración, Mi perfil y Catálogos —Categorías, Marcas, Proveedores—, incluyendo las vistas de edición que utilizan el mismo layout. Se conserva `tenantNavigation.sectionLabel`, con región de navegación y sección actual accesibles.

Se preservan logotipo personalizado, URL de almacenamiento, colores y subtítulo contextual de negocio. Cuando falta logo continúa el recurso de respaldo existente del tenant; no se instala la marca Figma como logotipo de las dulcerías. El logo decorativo junto al nombre accesible usa `alt=""` para evitar lectura duplicada y mantiene `object-fit:contain`.

Nombre largo probado: **Dulcería y Distribuidora de Productos Regionales San Miguel**. Sidebar de hasta dos líneas y breadcrumb con elipsis. El usuario puede desplegar el nombre completo con clic, toque, Enter o Espacio; Escape o salida del foco lo cierran. `title`, nombre accesible completo, foco visible y objetivo mínimo de 44 px. Panel de 280 px dentro del viewport de 320 px, sin superponerse permanentemente a los controles. Header medido de 56 px en las nueve resoluciones.

### Sesiones reales y roles

Datos exclusivamente sintéticos en una copia aislada en `/tmp/dulceria-phase-2-13-2`, SQLite y sesiones de archivo, sin conexión a MySQL, Redis o almacenamiento del entorno principal. Dependencias PHP reutilizadas de la imagen existente, sin instalar paquetes. Servidor auxiliar `127.0.0.1:8182`, detenido al finalizar. Se usaron formularios reales de login y logout, sin `actingAs`, sustitución de respuestas ni simulación de propiedades Vue.

| Cuenta sintética | Datos del negocio | Resultado observado |
|---|---|---|
| Administrador A | `Tenant.nombre = Dulcería La Estrella`; nombre comercial nulo; sin logo | Nombre registrado en sidebar y breadcrumbs |
| Cajero A | Mismo tenant A | Mismo nombre registrado, subtítulo Punto de venta |
| Administrador B | Registro «Distribuidora San Miguel»; nombre comercial extenso; logo y paleta propios | Prevalece nombre comercial completo, con logo conservado |
| Cajero B | Mismo tenant B | Misma identidad comercial y logo del negocio |
| Superadministrador | Sin tenant | «Dulcería POS»; `tenantConfig = null` |
| Caso defensivo sin relación | Cajero sintético sin tenant | Fallback de plataforma; no conserva el negocio anterior |

Se comprobó la secuencia de entrada/salida A → cajero A → B → cajero B → superadministrador en el mismo contexto del navegador. Nunca se mostró la identidad de la cuenta previa. Ambos administradores, ambos cajeros y superadministrador se comprobaron en las nueve resoluciones, sin overflow horizontal; también el caso defensivo sin relación.

Navegación de lectura comprobada en los doce destinos de tenant anteriores, con enlaces Inertia donde estaban visibles. Comprobación móvil adicional de Usuarios, Configuración, Reportes, Cortes y Perfil a 320/768 px. Menú móvil → Usuarios cierra el drawer correctamente. Bottom Navigation permanece bajo 768 px; tablet usa drawer y escritorio sidebar desde 992 px. Al final del documento en 320 × 568, el último contenido queda en y=475.7 y la navegación inferior comienza en y=500: no lo cubre.

Probe PHP de la propiedad compartida: diez casos correctos —comercial «0», registrado «0», comercial, comercial nulo, vacío, solo espacios, ambos nombres vacíos, sin tenant, superadministrador con relación e invitado—. Los modelos de ese probe son transitorios; no se guardan. No se ejecuta testing funcional integral ni auditoría de seguridad.

| Caso visual | Antes | Después |
|---|---|---|
| Negocio A, 1366 px | [Nombre fijo incorrecto](../.playwright-mcp/branding-tenant-2-13-2/before-tenant-a-1366.png) | [Nombre real](../.playwright-mcp/branding-tenant-2-13-2/after-admin-a-1366.png) |
| Nombre extenso, 1366 px | [Nombre sin límite](../.playwright-mcp/branding-tenant-2-13-2/before-tenant-b-1366.png) | [Composición estable](../.playwright-mcp/branding-tenant-2-13-2/after-tenant-b-1366.png) |

[Nombre completo desplegado a 320 px](../.playwright-mcp/branding-tenant-2-13-2/long-name-expanded-320.png), [drawer con logo comercial](../.playwright-mcp/branding-tenant-2-13-2/long-name-drawer-320.png), [cajero A](../.playwright-mcp/branding-tenant-2-13-2/after-cashier-a-320.png), [cajero B](../.playwright-mcp/branding-tenant-2-13-2/after-cashier-b-320.png), [superadministrador](../.playwright-mcp/branding-tenant-2-13-2/after-superadmin-1366.png).

## Archivos y verificaciones

Fuentes de identidad visual: `resources/views/components/platform-logo.blade.php`, nuevo `platform-favicons.blade.php`, `components/application-logo.blade.php`, `components/auth/layout.blade.php`, `resources/views/app.blade.php`, `landing.blade.php`, `landing/mock-dashboard.blade.php`, `landing/mock-pos.blade.php`, `resources/css/auth.css`, `public/brand/tokens.css`, SVG/PNG de la tabla y `public/favicon.ico`. `.dockerignore` excluye el ZIP. Los recursos y generador retirados corresponden a la marca provisional descrita arriba.

Fuentes de identidad tenant: `app/Http/Middleware/HandleInertiaRequests.php`, nuevo `resources/js/Components/TenantIdentityName.vue`, `resources/js/Components/SidebarNav.vue`, `resources/js/Layouts/AppLayout.vue` y `resources/css/premium.css`. Documentación: `UI_DESIGN_SYSTEM.md` y este informe.

- `npm run build`: correcto en host y en la construcción Docker. Aviso preexistente de catálogo Browserslist desactualizado; no se actualizan dependencias.
- `php artisan view:cache`: correcto; PHP lint de `HandleInertiaRequests.php` correcto.
- `git diff --check` y revisión del staging: correctos; ZIP, capturas, diagnósticos y SQLite excluidos de los commits.
- Comprobación de doce assets: XML/bytes originales/HTTP 200 correctos. Favicon ICO recompuesto con PNG originales. Rutas activas sin referencias a recursos retirados. Sin errores JavaScript en las comprobaciones.
- Revisión de cambios: sin diff en controladores, modelos, rutas, migraciones, permisos, autenticación, configuración comercial persistida ni archivos de dependencias. El único cambio servidor es una propiedad compartida de presentación. `handle()` y la política de caché existente no cambian.
- Docker: imágenes `app` y `nginx` reconstruidas y contenedores actualizados; MySQL saludable y Redis activos, sin recrear volúmenes ni ejecutar migraciones en el entorno principal. `http://localhost` responde 200 con los recursos definitivos.

## Límites y observaciones pendientes

- Pendiente la revisión estética del usuario. La evidencia multi-tenant procede de sesiones reales con datos sintéticos aislados; no se crearon ni modificaron negocios reales. SQLite/PHP del host no sustituyen una validación comercial integral sobre MySQL/PHP de producción, fuera del alcance.
- Superadministración conserva su interfaz e identidad existentes; únicamente se verifica su comportamiento y recibe el favicon compartido. Impersonación permanece intacta por inspección del diff; no se ejecutó una sesión de impersonación.
- El caso defensivo mostró que el middleware existente permite abrir el dashboard a un cajero sintético sin tenant. Se documenta para futura auditoría de integridad/autorización; no se cambia esa política en una fase visual.
- WebView físico, teclado virtual real, zoom nativo, lectores de pantalla y máscaras Android quedan sin probar. No se declara certificación de accesibilidad. La exportación Apple de alta resolución queda pendiente; se reutiliza el PNG aprobado de 64 px.
- Los nombres de las vistas previas editables de Configuración y los fallbacks de recibos conservan sus contratos previos; la identidad efectiva de navegación se resuelve exclusivamente desde el servidor.

## Commits y estado de entrega

Identidad visual: `a216583` — `feat(brand): integrar identidad definitiva de Figma`.

Identidad tenant e informe: commit siguiente — `fix(ui): mostrar identidad dinamica de tenant en navegacion`; su hash se entrega en el mensaje final para no introducir una referencia circular en este archivo.

Árbol de entrega: cambios de fase consolidados; únicamente el ZIP original permanece sin seguimiento. Evidencia local ignorada por Git. Sin push ni merge. Servidor temporal de 8182 detenido; localhost permanece operativo para revisión visual.
