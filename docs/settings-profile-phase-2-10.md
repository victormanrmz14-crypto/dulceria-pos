# Fase 2.10 · Configuración, perfil y navegación de cuenta

Fecha: 23 de noviembre de 2026, America/Mexico_City. Rama: `refactor/premium-ui`. Base: `fd37584`. Implementación lista para revisión estética; no se avanza a Superadministración.

## Resultado

Se modernizaron Apariencia, Mi negocio y Tickets con el sistema premium existente: DM Sans, Lucide, superficies neutras, navegación secundaria compartida, etiquetas asociadas, errores por campo, estados de carga/guardado y vistas previas locales explícitas. Se conservaron campos, máximos, payloads, endpoints y validaciones administrativas. El controlador de Configuración permanece idéntico a la base.

Mi perfil permite seleccionar, previsualizar, descartar, guardar, reemplazar y eliminar una fotografía opcional. La imagen pertenece al usuario. Sin fotografía, o si falla su carga, se representan las iniciales. El borrador no cambia el avatar del navbar hasta guardarse. La eliminación pide confirmación y restituye las iniciales; los errores y la finalización de la eliminación restauran el foco en el selector.

El navbar concentra fotografía/iniciales, nombre cuando hay espacio y menú de cuenta con identidad, rol, Mi perfil y Cerrar sesión. Se mantiene el formulario nativo POST `/logout` con CSRF que antes usaba el sidebar. El menú es un disclosure con enlaces y botones nativos; no utiliza roles ARIA de menú que requieran otro modelo de teclado.

El sidebar conserva todos sus destinos y grupos; desaparecen su bloque de identidad y logout. La barra inferior y su composable de visibilidad permanecen sin cambios. SuperAdmin conserva su layout y accesos.

## Inventario de archivos

| Archivo | Cambio |
|---|---|
| `app/Http/Controllers/ProfileController.php` | Reutilizado: añade vista personal y operaciones de avatar; conserva métodos heredados. |
| `app/Models/User.php` | URL pública segura, comprobación del directorio propio y ruta oculta de la serialización. |
| `app/Http/Middleware/HandleInertiaRequests.php` | Añade `auth.user.avatar_url`. |
| `routes/web.php` | Tres rutas personales dentro de `auth` + `usuario.activo`. |
| `database/migrations/2026_11_23_000001_add_avatar_path_to_users_table.php` | Nueva columna opcional, sin modificar migraciones históricas. |
| `tests/Feature/ProfileAvatarTest.php` | Pruebas exclusivas del avatar y sus límites de acceso. |
| `resources/js/Pages/Configuracion/Index.vue` | Rediseño de los tres formularios existentes. |
| `resources/js/Pages/Perfil/Index.vue` | Nueva página personal. |
| `resources/js/Components/Settings/SettingsWorkspace.vue` | Nuevo marco compartido de configuración/perfil. |
| `resources/js/Components/Ui/UserAvatar.vue` | Nuevo avatar reutilizable con fallback y recorte circular uniforme. |
| `resources/js/Components/AccountMenu.vue` | Nuevo menú de cuenta compartido por los módulos del tenant. |
| `resources/js/Components/Admin/AdminDialog.vue` | `focusFallback` opcional, compatible con consumidores existentes. |
| `resources/js/Layouts/AppLayout.vue` | Integra el menú de cuenta. |
| `resources/js/Components/SidebarNav.vue` | Elimina únicamente identidad/logout duplicados. |
| `resources/js/Navigation/tenantNavigation.js` | Breadcrumb personal para `/perfil`; mismos destinos y permisos de navegación. |
| `resources/css/premium.css` | Retira exclusivamente estilos sin consumidores de la identidad anterior. |
| `UI_DESIGN_SYSTEM.md` | Documenta los contratos y límites de esta fase. |
| `docs/settings-profile-phase-2-10.md` | Este informe. |

Se reutilizan `AppLayout`, `UiFormField`, `UiButton`, `UiIcon`, `AdminDialog`, `UiOverlay`, los tokens y `premiumTheme`. No se añadieron dependencias.

## Backend, permisos y migración

| Ruta | Acceso / comportamiento |
|---|---|
| GET `/perfil` | Usuario autenticado activo del tenant; muestra el perfil propio. |
| POST `/perfil/avatar` | Valida y reemplaza exclusivamente el avatar del usuario de la sesión. |
| DELETE `/perfil/avatar` | Elimina exclusivamente su referencia y su archivo autorizado. |
| `/configuracion` y sus tres POST existentes | Permanecen dentro de `solo.admin`; cajeros reciben 403. |

Se rechazan `user_id` y `avatar_path` enviados por el cliente. Roles, contraseñas y `tenant_id` no se asignan desde estas operaciones. No existe una ruta para editar fotografías de terceros ni un segundo sistema de identidad.

Validación del servidor: `file`, `image`, MIME real JPEG/PNG/WebP y `max:2048`; SVG, GIF y contenido disfrazado de PNG se rechazan. Storage genera el nombre hash. La columna guarda únicamente una ruta relativa; Vue recibe su URL `/storage/avatars/…`, sin rutas del sistema operativo. La ausencia de imagen es compatible con usuarios existentes.

Reemplazo: almacenar primero, bloquear y actualizar el usuario dentro de una transacción, confirmar y retirar después el archivo anterior. Si falla la persistencia, se limpia el archivo nuevo. La eliminación de archivos requiere ruta hash válida dentro de `avatars/{id}/`; rutas ajenas, traversal y logos se conservan. Si falla retirar un archivo antiguo después de confirmar, se registra el fallo y la referencia vigente continúa siendo válida.

El entorno Docker indicó `local`; todas las migraciones previas estaban aplicadas. Primero se ejecutó `--pretend` para la nueva migración y se comprobó que el único SQL añadía `avatar_path varchar(255) null`. Después se aplicó únicamente:

```sh
docker compose -f docker-compose.yml exec -T app php artisan migrate \
  --path=database/migrations/2026_11_23_000001_add_avatar_path_to_users_table.php \
  --force --no-ansi
```

Resultado: aplicada en lote 2. No se ejecutó un refresco ni borrado sobre la base de datos del proyecto. No se editaron fotografías, registros de empleados o datos comerciales reales.

## Evidencia técnica del avatar

PHPUnit 11.5.55 / PHP 8.3.37: **15 pruebas, 150 aserciones, sin fallos**. Se ejecutó únicamente `tests/Feature/ProfileAvatarTest.php`.

La copia temporal de prueba excluyó `.env`, vendor de producción, cachés, almacenamiento y base SQLite del proyecto. Las dependencias de desarrollo ya declaradas se instalaron desde `composer.lock` con `--no-scripts`. La ejecución fue en un contenedor sin red, `APP_ENV=testing`, SQLite `:memory:` y `Storage::fake('public')`. El propio archivo de pruebas rechaza arrancar en otra conexión/entorno antes de preparar la base. No se instaló PHPUnit en la aplicación local ni se cambió el lockfile.

| Condición | Evidencia |
|---|---|
| Subida autenticada | JPEG, PNG y WebP aceptados; archivo existente y URL compartida, ruta interna oculta. |
| Invitado | Lectura, POST y DELETE redirigen a login; ningún archivo creado. |
| Formato | SVG, GIF y archivo real de texto/PHP con nombre y MIME cliente PNG rechazados. |
| Tamaño | 2049 KB rechazados; caso válido de 2048 KB aceptado con UploadedFile de prueba. |
| Reemplazo | Nuevo hash y archivo; desaparece únicamente el anterior. Nombre original con traversal no determina la ruta. |
| Eliminación | Referencia `null`, archivo propio eliminado y URL compartida `null`. Iniciales verificadas además en Vue. |
| Persistencia | Ruta consultada desde DB; GET, logout y login real de Laravel mantienen URL y archivo. |
| Propiedad | No se puede indicar otro usuario ni una ruta; atributos de identidad no cambian. |
| Directorio de eliminación | Logos, otra carpeta de usuario y rutas con traversal no se borran. |
| Fallos | Fallo simulado de escritura conserva el anterior; fallo de DB conserva referencia y limpia el nuevo. |
| Roles | Cajero accede a Perfil, pero GET y POST de Configuración siguen en 403; admin accede a ambos. |

Además, un proceso Laravel aislado almacenó un JPEG mediante la configuración real del disco público, con visibilidad `public`. Un Nginx temporal con la imagen/configuración del proyecto y ese directorio montado en modo lectura respondió **200, `image/jpeg`**. `cmp` confirmó bytes idénticos. No publicó puertos, no montó el volumen real de fotografías y se retiró al terminar. Esta comprobación de Storage/Nginx es independiente de los fixtures visuales y de las pruebas autenticadas de PHPUnit.

## Playwright MCP y responsive

Se utilizó `http://localhost`, con assets compilados reales y respuestas Inertia simuladas exclusivamente en el navegador. Las peticiones de escritura visuales se interceptaron; ninguna llegó a los empleados del sistema. Las confirmaciones de guardado y errores de estas capturas son simulaciones para inspeccionar Vue, no pruebas de persistencia.

El MCP predeterminado no encontraba Chrome. Se usó el MCP instalado con Chromium ya disponible, contexto aislado y transporte stdio mediante socket Unix temporal. No se abrió otro servidor HTTP.

Altura de viewport: 900 px. Cuatro vistas × ocho anchuras = **32 estados base**, sin desbordamiento horizontal. Se revisaron además tamaños de controles, columnas, sidebar/drawer/barra inferior y menú de cuenta en las ocho anchuras.

| Ancho | Apariencia | Negocio | Tickets | Perfil | Navegación general |
|---|---|---|---|---|---|
| 320 | Correcto, paletas 1 columna | 1 columna | Vista previa debajo | Iniciales y selector | Barra inferior + drawer |
| 375 | Correcto, paletas 2 columnas | 1 columna | Vista previa debajo | Correcto | Barra inferior + drawer |
| 390 | Correcto | 1 columna | Vista previa debajo | Correcto | Barra inferior + drawer |
| 430 | Correcto | 1 columna | Vista previa debajo | Correcto | Barra inferior + drawer |
| 768 | Correcto | 2 columnas | Vista previa debajo | Correcto | Drawer |
| 1024 | Paletas y miniatura en paralelo | 2 columnas | Formulario y ticket en paralelo | Correcto | Sidebar |
| 1366 | Navegación interna lateral | 2 columnas | Dos áreas | Correcto | Sidebar |
| 1920 | Contenido limitado a 1200 px | 2 columnas | Dos áreas | Correcto | Sidebar |

Comprobaciones adicionales: **71 aserciones de cuenta/avatar/roles/estados**, más cinco comprobaciones de formularios, sin fallos ni excepciones JavaScript. Cubren teclado, cierre exterior, foco al salir y tras eliminar, enlaces a apartados concretos, conservación de todos los destinos anteriores, fotografía cuadrada/vertical/horizontal, reemplazo y eliminación visuales, loading, errores asociados, foto ausente/rota, cajero, logo presente/ausente, textos extensos y paletas claras/oscuras.

Los pies se alcanzan por scroll normal; se comprobó expresamente que el botón de perfil puede quedar completamente visible sobre la barra inferior. No se añadieron barras fijas. Las imágenes verticales tienen dimensiones circulares fijas y `object-fit: cover`, evitando que el tamaño intrínseco del archivo desplace el encuadre.

### Accesibilidad y contraste

Nombres accesibles en botones, etiquetas asociadas, mensajes por campo, indicadores no dependientes solo del color y foco visible. Tab/Shift+Tab recorren las acciones nativas; ArrowDown entra al enlace de perfil y Escape cierra/restaura el disparador. La confirmación reutiliza el diálogo modal existente. Las acciones nuevas revisadas alcanzan 44 px; los campos de texto conservan 46 px.

Se calcularon **38 pares de contraste** con valores obtenidos del navegador: nueve paletas existentes, amarillo claro y gris. Texto ≥4.5:1; límites de controles ≥3:1. Ejemplos: texto principal 16.07:1, ayuda sobre canvas 5.16:1, iniciales 6.57:1, borde neutro 3.47:1. La selección usa blanco y `--p-brand-ink`; los botones conservan `--p-on-brand` calculado. No se afirma certificación WCAG ni validación con lector de pantalla.

### Capturas antes/después

Galería local: [comparison.html](../.playwright-mcp/settings-profile/comparison.html). Archivos ignorados por Git, conservados exclusivamente para revisión.

- Antes: `before-appearance-1366.png`, `before-business-1366.png`, `before-tickets-1366.png`, `before-tickets-375.png`.
- Después equivalente: `after-appearance-1366.png`, `after-business-1366.png`, `after-tickets-1366.png`, `after-tickets-375.png`.
- Matriz: capturas de los cuatro apartados en las ocho anchuras.
- Estados: menú con iniciales/fotografía, cajero y drawer, previsualizaciones por orientación, guardado/error/eliminación, logotipo y paleta local.

## Verificación de alcance y límites

`npm run build` compila la aplicación completa. `git diff --check` no presenta errores. Se compararon con `fd37584` el controlador de Configuración, autenticación, middleware de roles, barra inferior, composable móvil, SuperAdmin y las páginas comerciales anteriores: permanecen sin cambios. La navegación conserva sus destinos; únicamente añade el nombre de sección de `/perfil`.

Se reconstruyeron y actualizaron exclusivamente app/nginx con `docker-compose.yml` explícito; no se recrearon MySQL ni Redis. Localhost responde HTTP 200. La migración está aplicada; otro entorno deberá aplicarla antes de permitir subidas de avatar.

Límites: persistencia cubierta con SQLite y filesystem aislados, sin prueba integral MySQL/concurrencia. Las capturas usan datos simulados y un navegador Chromium; no equivalen a pruebas de negocio ni a validación en dispositivos físicos. Se conserva el flujo heredado del logotipo y las nueve paletas, sin editor individual de colores. No se realizaron auditorías generales, pruebas comerciales integrales, animaciones ni cambios de SuperAdmin. Vite conserva el aviso existente de Browserslist desactualizado; no se actualizaron dependencias fuera del alcance. No hay errores bloqueantes conocidos dentro del alcance.

Los temporales, procesos de inspección y contenedores auxiliares se retiran al cerrar. Las capturas y la galería local permanecen fuera de Git. La fase se agrupa en dos commits: avatar/perfil y configuración/navegación; los hashes y el estado final de Git se entregan en el cierre. Sin push ni merge.
