# Fase 2.13 · Marca y autenticación

Fecha: 24 de noviembre de 2026. Rama: `refactor/premium-ui`. Base real: `44a713e` (`refactor(ui): unificar experiencia visual de cajeros`). Árbol inicial limpio. **Implementada, pendiente de revisión estética de marca y pantallas.** No se inicia Superadministración.

## Dirección visual e identidad

La D de envoltura combina una inicial amplia con dos pliegues laterales. Geometría propia, sin emoji, stock ni icono de catálogo reutilizado como marca. Se diseñó primero como silueta monocromática; la aplicación usa cereza `#8B243A`, borgoña `#6F1D30`, grafito `#202126` y superficies neutras. DM Sans mantiene continuidad con el panel premium.

| Concepto original | Evaluación | Decisión |
|---|---|---|
| D de envoltura | Inicial reconocible, vínculo con dulcería, contraforma legible en pequeño | Seleccionado; pliegues contenidos para un tono comercial |
| Bolsa modular | Comercio y organización claros; menor diferenciación frente a iconos de tienda | Alternativa descartada |
| Monograma DP | Compacto y profesional; letras competidoras en favicon y menor vínculo con dulcerías | Alternativa descartada |

Los tres bocetos, variantes y reglas de uso están en [la lámina de marca](../public/brand/identity.html), disponible también en [localhost](http://localhost/brand/identity.html). La lámina es un entregable de identidad, no una nueva pantalla del producto.

| Asset | Archivo |
|---|---|
| Principal horizontal, letras trazadas | `public/brand/logo-horizontal.svg` |
| Horizontal monocromático y para oscuro | `public/brand/logo-mono.svg`, `logo-dark.svg` |
| Símbolo y sus variantes | `symbol.svg`, `symbol-mono.svg`, `symbol-dark.svg` |
| Favicon | `favicon.svg`, `favicon-16.png`, `favicon-32.png`, `favicon-48.png`, `public/favicon.ico` |
| Icono adaptable, maestro y exportaciones | `app-icon.svg`, `app-icon-192.png`, `app-icon-512.png` |
| Primer plano para Android futuro | `android-foreground.svg`; fondo previsto `#8B243A` |
| Icono de acceso de Apple | `apple-touch-icon.png` (180 px) |
| Tokens, fuente y licencia | `tokens.css`, `fonts/dm-sans-latin-variable.ttf`, `fonts/OFL.txt` |
| Generación de SVG | `scripts/generate-brand.py` |

SVG técnicamente validados con ElementTree: nueve archivos con namespace/viewBox y sin elementos de texto dependientes de fuentes. Los horizontales están trazados con DM Sans 700 a partir de la fuente OFL incluida. El generador requiere fontTools solo para regenerar assets: se ejecutó desde una extracción temporal, sin añadir paquetes al proyecto. PNG exportados con Chromium desde el SVG original; ICO empaqueta las exportaciones de 16/32/48 px. Fuente original: [Google Fonts, DM Sans](https://github.com/google/fonts/tree/main/ofl/dmsans). No hay assets de terceros salvo la fuente con su licencia y los contornos de interfaz Lucide existentes (ISC).

La marca de plataforma permanece separada de todos los logos y colores configurados por los tenants. Solo se incorpora su favicon al documento Inertia; no se modifican layouts o pantallas de SuperAdmin ni se sustituyen identidades de negocio.

## Login, registro y recuperación

Login dividido desde 992 px: panel neutro de identidad y formulario blanco. La composición original representa Ventas, Inventario y Caja sin cifras, fechas ni supuestas estadísticas reales; está oculta a tecnologías asistivas y desaparece por completo en móvil. El formulario conserva `name=email` con `type=text`: admite usuario o correo. Incluye enlace al registro, recuperación directa, etiquetas visibles, contraseña revelable y estados de envío.

El registro conserva sus cinco campos. `negocio` y `nombre` siguen siendo opcionales (máximos 200/150); correo y contraseña confirmada siguen siendo obligatorios. Dos fieldsets: «Datos de tu negocio» y «Crea tu cuenta de administrador». No hay wizard. El botón «Crear mi dulcería» mantiene el POST original, la creación de tenant + primer administrador, sesión, bienvenida y redirección a dashboard. Se explica que los cajeros reciben una cuenta de su administrador.

`GET /forgot-password` usa el método `PasswordResetLinkController::create()` que ya existía, en la misma ruta y con el mismo nombre. Sustituye exclusivamente el redirect al reverso 3D. El marcador histórico `/login#forgot` sigue funcionando con un redirect de presentación en JavaScript. POST, broker, correo, tokens, sesiones y redirecciones por rol no cambian. Restablecimiento mantiene token oculto y correo recibido por URL. Confirmación de contraseña y verificación de correo comparten el nuevo marco; esta última conserva reenvío y logout, sin activar nuevos requisitos de verificación.

Componentes Blade compartidos: `auth/layout`, `auth/field`, `auth/feedback`, `auth/submit`, `auth/icon` y `platform-logo`. CSS y JS tienen entradas Vite específicas; no se carga la aplicación Vue/Inertia en las páginas Blade. No se cambiaron dependencias de runtime, controladores, modelos, migraciones ni validaciones. La única modificación de ruta es la presentación GET descrita.

## Accesibilidad, móvil y WebView

- Labels asociados, autocomplete, email con teclado apropiado; identificador de login conserva teclado de texto para admitir usuarios.
- Campos de 48 px y texto de 16 px; controles de contraseña de 44 px y botones principales de 50 px renderizados.
- Control de contraseña con nombre accesible, `aria-controls`, `aria-pressed` y cambio de icono; funciona con Enter y conserva foco.
- Errores cercanos con prefijo «Error», `aria-invalid` y `aria-describedby`; resumen navegable y foco en primer campo inválido. Se conservan los mensajes exactos del backend.
- Estado de éxito anunciado con `role=status`. Envío deshabilita el botón, muestra texto de carga y `aria-busy`; `pageshow` restaura el control al volver desde historial. Indicador de espera estático con reduced motion, sin animaciones decorativas ni trabajo de microinteracciones.
- Con JavaScript desactivado siguen disponibles formularios y recuperación directa; el control de revelado permanece oculto.
- Documento desplazable, sin alturas fijas de tarjetas, scroll interior ni barras flotantes. `min-height:100svh` con fallback; safe areas en ambos lados, arriba y abajo; sin desactivar zoom.
- `viewport-fit=cover` e `interactive-widget=resizes-content` preparan el viewport para teclados compatibles. No sustituyen la configuración nativa `adjustResize` de un futuro WebView.

Contraste: 16 pares revisados con el script de la skill y valores calculados en navegador para los estados normales. Texto ≥4.5:1, límites/foco ≥3:1. Marca sobre blanco 8.71:1, hover 11.09:1, secundario sobre blanco 5.48:1, secundario sobre panel 4.91:1, borde de campo 3.54:1. Estados semánticos y hover adicionales contrastados desde sus valores CSS explícitos. No constituye certificación de accesibilidad.

## Responsive y evidencias

Playwright MCP con Chromium disponible en el equipo. Se corrigió localmente la ruta esperada de Chrome para que el MCP pudiera utilizar ese binario; no se instaló navegador ni dependencia en la aplicación.

[Galería antes/después](../.playwright-mcp/auth-branding-2-13/comparison.html). Evidencia fuera de Git en `.playwright-mcp/auth-branding-2-13/` para evitar versionar diagnósticos. Baselines de login y registro tomados de localhost antes de editar, a 390 y 1366 px; finales sobre el mismo servicio. Capturas completas de cuatro pantallas en ocho anchos, a 900 px de alto.

| Ancho CSS | Login / registro / recuperación / reset | Panel de identidad | Final del botón de registro, desde arriba |
|---|---|---|---|
| 320 | Sin overflow; controles completos | Oculto | 892 px |
| 375 | Sin overflow; controles completos | Oculto | 892 px |
| 390 | Sin overflow; controles completos | Oculto | 892 px |
| 430 | Sin overflow; controles completos | Oculto | 892 px |
| 768 | Sin overflow; controles completos | Oculto | 776 px |
| 1024 | Sin overflow; controles completos | Visible | 877 px |
| 1366 | Sin overflow; controles completos | Visible | 782 px |
| 1920 | Sin overflow; controles completos | Visible | 782 px |

Transiciones declaradas: panel de identidad a 992 px; contraseñas de registro en dos columnas desde 600 px, con una columna entre 992–1199 por presión del panel dividido, y dos desde 1200. Se revisan además 599/600, 991/992 y 1199/1200. El documento puede crecer para mantener ayudas, errores y acciones; el pie del registro requiere scroll en móvil a 900 px de alto.

Evidencias adicionales:

- `after-login-*.png`, `after-register-*.png`, `after-forgot-password-*.png`, `after-reset-password-*.png`.
- `state-login-error-390.png`, `state-register-error-390.png`: respuestas reales de Laravel aislado, con old input y foco de error.
- `simulated-long-error-320.png`: mensaje ampliado únicamente en navegador, incluidas cadenas sin espacios; sin desbordamiento a 320 px. No es una respuesta adicional del servidor.
- `state-recovery-sent-390.png`: envío real al mailer log de la copia aislada; sin correo externo.
- `state-reset-invalid-token-390.png`: token inválido con mensaje real del broker.
- `state-login-loading-390.png`: handler real de envío con navegación detenida únicamente en el navegador para capturar el estado; no acredita latencia de red.
- `mobile-short-keyboard-focus.png`, `mobile-short-register-submit.png`: 390 × 360, foco visible y botón alcanzable por scroll natural.
- `zoom-200-*.png`: zoom **CSS** al 200 % en las cuatro pantallas, sin overflow ni controles recortados. Chromium headless no cambió su zoom nativo mediante atajos; no se presenta esta emulación como zoom nativo.
- `android-emulated-login.png`: contexto móvil/táctil Android emulado a 390 × 844, escala 2, campo enfocado y panel oculto. No reproduce teclado de un Android físico.
- `after-confirm-password-390.png`: pantalla autenticada real de la copia aislada.
- Lámina de identidad y capturas de encabezado público a 390/1366 px en la galería.

## Comprobaciones técnicas y aislamiento

Copia temporal en `/tmp/dulceria-phase213/app`, sin `.env` ni storage de la aplicación principal. Clave nueva y cookie de sesión propia. Base de navegador `database/phase213.sqlite`; PHPUnit usa SQLite `:memory:`, sesiones/cache array y notificaciones falsas. Dependencias de desarrollo ya declaradas en `composer.lock` se instalaron solo en la copia temporal. No se crearon tenants ni usuarios de prueba en MySQL principal, ni se cambiaron credenciales reales.

**PHPUnit: 20 pruebas, 69 aserciones, sin fallos.** Solo AuthenticationTest, RegistrationTest, PasswordResetTest, PasswordConfirmationTest y LoginRoleRedirectTest. Comprueban login por usuario/correo, destinos existentes de admin/cajero/SuperAdmin, registro con nombre explícito y con nombres opcionales, relación tenant/primer administrador, recuperación y reset con token válido, contraseña incorrecta y confirmación. Comprobar el destino de SuperAdmin no implica continuar su rediseño.

```sh
docker run --rm --network none --user 1000:1000 --entrypoint php \
  -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
  -e SESSION_DRIVER=array -v /tmp/dulceria-phase213/app:/var/www/html \
  dulceria-pos-app vendor/bin/phpunit \
  tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/RegistrationTest.php \
  tests/Feature/Auth/PasswordResetTest.php tests/Feature/Auth/PasswordConfirmationTest.php \
  tests/Feature/Auth/LoginRoleRedirectTest.php
```

El navegador comprobó además el registro completo, redirección a dashboard, cierre de sesión, login por username y solicitud de recuperación contra Laravel de la copia temporal. Sin operaciones comerciales. Los tests iniciales requirieron generar una clave en la copia temporal; las aserciones nuevas de SuperAdmin se corrigieron al destino real `/superadmin/dashboard`. Los resultados finales anteriores corresponden a la ejecución corregida.

Compilación Vite, compilación Blade, `git diff --check`, cargas HTTP de assets y pantallas, XML de SVG y estructura ICO verificados. Vite conserva la advertencia preexistente de Browserslist desactualizado; no se actualizan dependencias ajenas a esta fase. Las imágenes Docker locales se reconstruyen para que localhost sirva el checkout; `.dockerignore` excluye ahora las evidencias locales.

## Página pública y pendientes

Landing conserva estructura, secciones y lógica comercial. Integra logo principal blanco en encabezado/pie, favicon, DM Sans local, cereza/borgoña y acciones de acceso/registro con contraste. Los logos de maquetas comerciales siguen representando negocios, no la plataforma.

Pendientes de contenido comercial, sin alterar su lógica ni sus textos en esta fase:

- «Registrarse gratis»: no se verificaron las condiciones comerciales o duración de gratuidad.
- «Cuadrada al centavo, todos los días»: afirmación de resultado no acreditada.
- «En unos minutos»: tiempo de configuración no medido.
- «Efectivo o tarjeta»: revisar que se comprenda como registro de método de pago, sin inferir integración con una pasarela.

## Limitaciones

La aprobación estética depende de la revisión del usuario, tanto de la identidad como de login/registro. Pendientes: teclado físico de Android, WebView real, áreas seguras físicas, zoom nativo, Safari/Firefox y lector de pantalla. La emulación de altura reducida y zoom CSS aporta evidencia parcial. El panel de identidad es una composición conceptual, no una captura operativa. Los errores genéricos de validación pueden llegar en inglés desde la configuración actual de Laravel; se preservan sus mensajes y reglas, sin iniciar una traducción global del producto. Los assets Android son originales de exportación, no recursos nativos compilados. No se añade seguridad, testing funcional comercial integral ni animaciones decorativas.

## Inventario de archivos

- Marca: `public/brand/*`, `public/favicon.ico`, `scripts/generate-brand.py`, `resources/views/components/platform-logo.blade.php`.
- Autenticación: `resources/views/auth/{login,register,forgot-password,reset-password,confirm-password,verify-email}.blade.php`; `resources/views/components/auth/{layout,field,feedback,icon,submit}.blade.php`; `resources/css/auth.css`; `resources/js/auth.js`; `vite.config.js`.
- Integración: `resources/views/landing.blade.php`, `resources/views/app.blade.php`, `routes/auth.php` (solo GET recuperación), `.dockerignore`.
- Evidencia mantenida: `tests/Feature/Auth/{PasswordResetTest,RegistrationTest,LoginRoleRedirectTest}.php`.
- Documentación: `UI_DESIGN_SYSTEM.md` y este informe.

Commits de entrega: consultar `git log -2 --oneline` y la entrega final. No se hace push ni merge. Al cierre se retira el servidor de prueba y la copia con datos aislados; se conserva la galería local fuera de Git.
