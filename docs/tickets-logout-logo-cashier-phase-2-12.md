# Fase 2.12 · Tickets, logout, logotipo y cajeros

Fecha de trabajo: 23–24 de noviembre de 2026, America/Mexico_City. Rama exclusiva: `refactor/premium-ui`. Base aprobada: `e7407a9` y `1100b54`. Implementación entregada para revisión estética; no equivale a aprobación.

## A. Tickets

Se moderniza `resources/js/Pages/Ventas/Ticket.vue` con DM Sans, Lucide, tokens del tenant, encabezado Detalle de venta, regreso a Historial, resumen y vista previa. Conserva Imprimir mediante `window.print()` y Nueva venta. Desde 1200 px hay dos columnas; debajo la composición es vertical. Los controles tienen foco visible y altura mínima de 44 px. A 320 px las acciones se apilan.

`SaleReceipt.vue` contiene el mismo comprobante que se muestra e imprime. No duplica cálculos: utiliza el payload original, tipado en `receipt.ts`, sin modificar `VentaController`. Mantiene folio, fecha/hora, cajero, productos, cantidades, precios unitarios, importes, total, método de pago, recibido y cambio cuando existen. Respeta los datos comerciales y opciones ya resueltas por el backend, sin inventar RFC ni otros datos fiscales. La cantidad y el precio aparecen debajo del nombre para conservar espacio en papel estrecho.

El selector local de 80/58 mm controla el ancho de la vista previa y del comprobante impreso. CSS de impresión limitado a esta página oculta toda la navegación y acciones, usa negro sobre blanco, elimina superficies decorativas y protege filas/parejas de importes frente a saltos de página. Conserva logo y mensajes habilitados. No modifica la impresión de Cortes ni introduce exportación, QR, CFDI o impresión externa.

**Evidencia local:** [galería de comparación](../.playwright-mcp/tickets-logout-logo-cashier/comparison.html). Incluye ticket antes/después en escritorio y móvil, dashboard de cajero, matrices responsive, estados del logo y PDFs [58 mm](../.playwright-mcp/tickets-logout-logo-cashier/receipt-58mm.pdf), [80 mm](../.playwright-mcp/tickets-logout-logo-cashier/receipt-80mm.pdf) y [A4](../.playwright-mcp/tickets-logout-logo-cashier/receipt-a4.pdf). Las capturas usan negocios y operaciones ilustrativos de la base aislada. El folio DEMO-212 se conserva en la comparación del ticket; los saltos de línea del pie del fixture se normalizaron durante la preparación.

Se comparó el texto del recibo con media screen/print: idéntico contenido. Anchos medidos 302.36 px y 219.20 px, correspondientes a 80/58 mm CSS; sin navegación visible ni overflow interno. Los PDFs térmicos normales tienen una página y se inspeccionaron visualmente y con `pdftotext`. Los escenarios adicionales de nombres extensos e importes de nueve cifras se identifican como **simulados en la presentación**; no prueban operaciones comerciales.

**Límite:** ningún modelo de impresora física fue probado. El navegador no configura automáticamente rollo, márgenes, escala ni corte. El usuario debe seleccionar papel y márgenes apropiados y desactivar encabezados/pies del navegador. A4 conserva la tira del ancho elegido. Una fila excepcionalmente más alta que la página requiere ajuste manual; no se promete compatibilidad de drivers ni hardware.

## B. Logout

### Causa y evidencia

`AccountMenu.vue` tenía un formulario POST `/logout` con token CSRF correcto, pero `@submit="close()"` cambiaba `open=false`. El panel estaba condicionado con `v-if="open"`, por lo que Vue retiraba el formulario antes de que el navegador enviara su acción nativa.

Reproducción en sesión Laravel real de administrador, antes de la corrección:

- Clic Cerrar sesión: desaparecía el panel.
- Ninguna petición a `/logout`; la URL seguía en el ticket y el usuario permanecía autenticado.
- Chromium registró **“Form submission canceled because the form is not connected”**.
- Rutas y controlador ya utilizaban POST, `Auth::logout()`, invalidación de sesión, regeneración de CSRF y redirección `/`; no se modificaron.

Se conserva montado el formulario y se cambia únicamente su estado de envío: botón deshabilitado y «Cerrando sesión…». La navegación pública es una navegación nativa completa, compatible con la landing Blade existente.

La prueba de volver atrás encontró además que Chromium podía restaurar la vista privada desde caché aun después de invalidar la sesión. `HandleInertiaRequests` añade `Cache-Control: no-store, private` a respuestas autenticadas del tenant. Con ese encabezado, la repetición volvió a login sin restaurar `.tenant-shell`. SuperAdmin queda fuera del nuevo encabezado. No se cambió la política de autenticación ni ningún permiso.

### Resultado real por rol

Administrador y cajero completaron inicio de sesión → menú → POST logout → `/` público → petición privada redirigida a `/login` → nuevo inicio de sesión. La matriz repitió **16 ciclos**, dos roles × ocho anchos: 320, 375, 390, 430, 768, 1024, 1366 y 1920 px.

ArrowDown enfocó Mi perfil, Tab alcanzó logout, Enter envió el POST y Escape restauró el foco del disparador. En ambos roles, volver atrás después del logout quedó sin contenido privado. No se sustituyó la invalidación por un simple cambio de URL. Las pruebas de PHPUnit comprueban también cambio de ID de sesión, renovación de CSRF y desaparición de un dato privado de sesión.

Archivos: `AccountMenu.vue`, `HandleInertiaRequests.php`, `tests/Feature/Auth/NavbarLogoutTest.php`.

## C. Logotipo

### Diagnóstico y límite de reproducción

Se inspeccionaron Configuración, `useForm`/`forceFormData`, POST real, Storage public, configuración de disco, Docker y Nginx. PHP de producción local usa `upload_max_filesize=2M` y `post_max_size=8M`; Nginx permite cuerpo de hasta 20M. Laravel aplica `nullable|image|max:2048`. No se aumentó ninguno de esos límites.

**56658.jpg no estaba adjunto ni disponible.** No se pueden afirmar su tamaño, MIME, dimensiones, integridad o causa individual de rechazo. La vista previa local no demuestra aceptación del servidor.

Se reprodujo exactamente el mensaje anterior con un JPG válido equivalente:

| Archivo de prueba | Bytes | MIME real | Dimensiones / resultado |
|---|---:|---|---|
| JPG válido | 933 | image/jpeg | 160 × 90; aceptado |
| PNG válido | 309 | image/png | 160 × 90; aceptado |
| WebP válido | 112 | image/webp | 160 × 90; aceptado |
| GIF válido | 177 | image/gif | 160 × 90; aceptado |
| BMP válido | 43 254 | image/bmp | 160 × 90; aceptado |
| JPG mayor al límite | 6 420 449 | image/jpeg | 1800 × 1800, íntegro; PHP lo rechaza |
| Texto con extensión JPG | 12 | text/plain | Sin dimensiones de imagen; rechazado |

Antes: el JPG de 6 420 449 bytes mostró una vista previa blob y luego «The logo failed to upload». PHP había marcado la subida con `UPLOAD_ERR_INI_SIZE`, antes de ejecutar la regla `max` de Laravel. Esta es una causa **demostrada para el archivo equivalente**, no una conclusión sobre 56658.jpg.

Además, la interfaz anunciaba PNG/JPG/WebP, usaba `accept="image/*"` y el backend aceptaba también GIF/BMP. Ahora el selector y su ayuda enumeran los cinco formatos ya admitidos, manteniendo el límite. No se restringieron formatos existentes ni se habilitó cualquier archivo.

### Corrección y persistencia

- Errores específicos en español para límite PHP/Laravel, imagen inválida, carga parcial y almacenamiento fallido.
- Comprobación local de tamaño/MIME disponible; el servidor sigue validando el archivo real.
- Se distinguen imagen pendiente, imagen guardada y ausencia de logo. Descartar vuelve al logo persistido. Se revocan object URLs.
- Un error muestra «No se guardaron los cambios» y mantiene identificado el borrador. No se muestra una confirmación de guardado de la imagen rechazada.
- Antes se borraba el archivo anterior antes de almacenar el nuevo. Ahora se escribe primero, se confirma la referencia en transacción con bloqueo y se retira después el anterior. Si falla persistir, se limpia la nueva subida y se conserva el anterior.
- La eliminación confirma primero la referencia; respeta el mismo endpoint existente. Un fallo al retirar archivos se registra y no rompe la referencia guardada. La limpieza se limita a `logos/{tenant}/`.

Playwright con autenticación real realizó JPG/PNG/WebP/GIF/BMP: POST 302 de retorno, archivo servido con HTTP 200 y MIME correspondiente, nueva URL de servidor y persistencia tras recargar. El archivo inválido produjo mensaje de formato en español y, al recargar, reapareció la URL anterior. El exceso de tamaño se rechazó localmente sin éxito ambiguo. Un POST multipart real adicional, que omite solo la validación del cliente, confirmó la respuesta de PHP: 302 con error «El logotipo debe pesar como máximo 2 MB», conservando la URL anterior. Se comprobó eliminación confirmada y ausencia de logo después de recargar.

Los fallos controlados de escritura y DB se verificaron con Storage falso y SQLite en memoria. Ninguna de estas pruebas cambió logotipos comerciales reales ni avatares personales.

Archivos: `ConfiguracionController.php`, `Configuracion/Index.vue`, `tests/Feature/BusinessLogoTest.php`.

## D. Cajeros reales

### Preparación aislada

Se verificó primero el logout del administrador. Después, un administrador descartable creó una cuenta cajero desde `/usuarios/create`, utilizando el formulario y POST existentes. Tras cerrar sesión, se inició sesión con las credenciales recién creadas. Laravel devolvió `rol=cajero`, ID 2 en la base de prueba. No se reemplazó `auth.user.rol` ni se interceptaron respuestas para probar autenticación o autorización.

Las cuentas y operaciones ilustrativas existieron únicamente en `/tmp/dulceria-phase212/app/database/phase212.sqlite`, dentro de una copia sin `.env`, credenciales ni storage del proyecto principal. Las contraseñas fueron aleatorias y quedaron fuera de Git, capturas e informes. No se creó ninguna cuenta en la base principal ni se modificaron contraseñas existentes.

El único servidor auxiliar de aplicación utilizó `http://localhost:8182`, PHP 8.3.37 en contenedor, sesiones Laravel de archivo y Storage separado. El MCP predeterminado no encontró Chrome; se utilizó **Playwright MCP 0.0.86 instalado**, con Chromium disponible, contexto aislado y transporte stdio a través de un socket Unix temporal. No se abrió otro servidor HTTP de navegador.

Productos, ventas y un corte de demostración se insertaron por ORM exclusivamente en la base aislada para poblar vistas. No se ejecutó POST `/ventas`, un cobro real, un movimiento de caja ni un corte de caja real. Los GET de las pantallas y las restricciones de acceso sí se comprobaron contra Laravel, sin mocks.

### Pantallas y navegación

| Pantalla | Evidencia de sesión real | Resultado |
|---|---|---|
| Inicio | GET `/dashboard`, vacío y con fixtures propios | Cuatro métricas, caja esperada, último corte y ventas; premium para cajero |
| POS | GET `/ventas`, producto de prueba | Presentación premium existente, sin completar ventas |
| Historial | GET `/ventas/historial`, vacío y con fixtures | Solo ventas propias; enlaces a tickets |
| Ticket | GET `/ventas/2/ticket` | Ticket propio premium; ticket de administrador 403 |
| Caja | GET `/caja`, vacío/con fixtures | Métricas y desglose premium; ingreso/retiro/corte abiertos y cerrados con Escape, sin enviar |
| Cortes | GET `/cortes` y `/cortes/1` | Listado y detalle propios con estilos premium existentes |
| Mi perfil | Navegación real desde cuenta y GET `/perfil` | Acceso personal; ninguna sección administrativa |
| Menú de cuenta | Teclado, clic, logout y reingreso | Funciona en escritorio y móvil |
| Barra inferior/drawer | Enlaces reales y Escape | Inicio/Vender/Ventas/Menú; Caja desde el grupo existente; foco restaurado |

Se corrigió el dashboard heredado: emojis, tarjetas de color fijo, encabezado y tabla antiguos. `CashierDashboard.vue` utiliza métricas premium, acciones POS/Caja, estado vacío y tabla desde 768 px. En móvil utiliza lista para conservar el total a la vista, sin desplazamiento horizontal. Se conservan exactamente los props y significados de las métricas. La bienvenida premium omite inventario/reportes para cajero; no añade capacidades.

Se preservó `tenantNavigation.js`: no se agregó un destino nuevo. Cortes ya permite GET de cajero con filtro propio, aunque la navegación histórica lo muestra solo a administrador; se revisó mediante sus rutas existentes, sin cambiar esa decisión. Usuarios, Productos, Reportes, Configuración, Categorías, Marcas y Proveedores devolvieron **403** a la sesión de cajero. El ticket de otro usuario también devolvió 403. Ocultar enlaces no se usó como sustituto de autorización.

### Responsive y accesibilidad observada

[Informe responsive detallado](responsiveness-check-2026-11-24.md). Altura: 900 px. Anchos obligatorios: **320, 375, 390, 430, 768, 1024, 1366 y 1920 px**.

| Área | Estados | Resultado |
|---|---:|---|
| Ocho pantallas de cajero con datos | 64 | HTTP 200, DM Sans, sin overflow documental |
| Inicio, Historial, Caja, Cortes y Perfil sin operaciones propias | 40 | Sin overflow documental; estados vacíos legibles |
| Login/logout por ambos roles | 16 ciclos | POST e invalidación reales, reingreso y teclado; sin contenido privado al volver atrás |
| Apariencia/logotipo | 8 | Sin overflow; etiqueta y ayudas asociadas; controles revisados ≥44 px |
| Ticket con nombres extensos y nueve cifras | 16 | Simulación visual explícita, ocho anchos × dos papeles; sin overflow interno o del documento |

Navegación: barra inferior <768 px, drawer 768–991.98 px, sidebar ≥992 px. Ticket: una columna <1200 px, dos desde 1200 px. Caja conserva grupos secundarios y sus permisos. Se comprobó Escape y restauración de foco en cuenta, drawer y diálogos de Caja; navegación real de barra inferior a POS/Historial y de cuenta a Perfil. Las captions y textos `visually-hidden` no se consideran contenido visual recortado.

Se midieron colores calculados en navegador con reduced motion para evitar muestrear una transición a medio completar: nueve paletas existentes y amarillo claro adicional simulado. `check-contrast.mjs` pasó **11 pares**: botones ≥5.02:1, ayuda neutra 5.48:1. Foco visible, importes tabulares, controles nativos y textos de estado que no dependen solo del color. Esto no equivale a certificación WCAG ni prueba con lector de pantalla.

## Verificación técnica y entorno

Se aplicaron las seis skills solicitadas: frontend-design, ui-ux-pro-max, ux-ui-system-refactor, vue-best-practices, web-design-guidelines y responsiveness-check; también las guías focalizadas de PHPUnit, pruebas y verificación antes de completar. Se preservó el diseño premium existente, sin fuentes/dependencias nuevas ni animaciones decorativas.

**PHPUnit 11.5.55 / PHP 8.3.37: 17 pruebas, 215 aserciones, sin fallos.** Solo `BusinessLogoTest.php` y `Auth/NavbarLogoutTest.php`. Ambos rechazan arrancar fuera de `APP_ENV=testing`, SQLite `:memory:` antes de preparar DB. Storage del logo usa `Storage::fake('public')`. La ejecución final se realizó en contenedor **sin red**, con la copia temporal y dependencias de desarrollo ya declaradas en `composer.lock`; no se instaló PHPUnit en producción ni se cambió el lockfile.

```sh
docker run --rm --network none --user 1000:1000 --entrypoint php \
  -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
  -e SESSION_DRIVER=array -v /tmp/dulceria-phase212/app:/var/www/html \
  dulceria-pos-app vendor/bin/phpunit \
  tests/Feature/BusinessLogoTest.php tests/Feature/Auth/NavbarLogoutTest.php
```

La copia temporal de este comando se retiró al cerrar; no debe sustituirse por un montaje del `.env`/storage principal. Los tests permanecen en el repositorio para ejecutarse en un entorno equivalente con dependencias de desarrollo.

Cobertura focalizada: cinco formatos válidos, tamaño exacto/por encima de 2048 KiB, imagen inválida, errores de PHP y carga parcial, reemplazo, conservación ante fallos, eliminación, persistencia, paleta sin reemplazo, inicio/logout por rol, sesión/CSRF y creación/acceso de cajero. Los fallos de escritura y DB son deliberadamente simulados; las sesiones del navegador son reales.

`npm run build` compila; Vite mantiene únicamente el aviso previo de Browserslist desactualizado. `git diff --check` pasa. No hay cambios en migraciones, rutas, `VentaController`, `DashboardController`, middleware de roles, cálculos, inventario, Caja, Cortes, Reportes o métodos de pago. Los archivos de Superadministración permanecen idénticos a la base.

Se reconstruyeron y actualizaron **solo app y nginx**, usando `docker-compose.yml` explícito y `--no-deps`; MySQL, Redis y volúmenes existentes se conservaron. Localhost responde HTTP 200. El recuento principal antes/después de actualizar contenedores permaneció: **5 usuarios, 3 tenants, 1 producto, 2 ventas, 1 corte**. Ninguno corresponde a cuentas creadas por esta validación.

Al cierre se detuvo y retiró el contenedor auxiliar de 8182 y el proceso MCP/socket temporal. Se conservaron únicamente capturas/PDF/JSON útiles en la galería local ignorada por Git; no contiene credenciales ni datos comerciales reales.

## Archivos modificados

- `app/Http/Middleware/HandleInertiaRequests.php`: caché privada del tenant al finalizar sesión.
- `app/Http/Controllers/ConfiguracionController.php`: errores de carga y persistencia segura del logo.
- `resources/js/Components/AccountMenu.vue`: envío nativo sin desmontar formulario.
- `resources/js/Pages/Configuracion/Index.vue`: formatos, validación local y estados de logo.
- `resources/js/Pages/Ventas/Ticket.vue`: detalle y composición/impresión.
- `resources/js/Components/Ventas/SaleReceipt.vue`, `receipt.ts`: comprobante compartido y contrato existente.
- `resources/js/Components/Ui/UiIcon.vue`: icono Lucide de impresora.
- `resources/js/Pages/Dashboard.vue`, `Components/Dashboard/CashierDashboard.vue`: dashboard premium del cajero.
- `resources/js/Components/WelcomeModal.vue`: presentación de bienvenida según rol existente.
- `tests/Feature/BusinessLogoTest.php`, `tests/Feature/Auth/NavbarLogoutTest.php`: regresiones focalizadas.
- `UI_DESIGN_SYSTEM.md`, este informe y `docs/responsiveness-check-2026-11-24.md`.

## Límites y pendientes

1. Falta el archivo original 56658.jpg para reproducir su error exacto; tamaño/MIME/integridad de ese archivo siguen desconocidos.
2. No se probaron impresoras físicas, otros motores de navegador, dispositivos físicos ni lectores de pantalla.
3. Persistencia automatizada cubierta en SQLite; no se hicieron pruebas de concurrencia o fallos de disco/DB sobre MySQL real. La retirada de archivos puede fallar por infraestructura: se registra el fallo y se conserva la referencia vigente; una limpieza posterior podría ser necesaria.
4. Logout/caché comprobados en Chromium disponible. Otras políticas de historial de navegador requieren validación específica si se incorporan como plataforma soportada.
5. Los fixtures de operaciones solo sirven para presentación y permisos de lectura. No se verificaron cálculos de negocio, cobros, movimientos ni cortes nuevos.
6. No se realizó auditoría general de seguridad, testing funcional integral, trabajo dedicado de animaciones ni avance a Superadministración.
7. Pendiente la aprobación estética del usuario del ticket digital, impresión y experiencia de cajero.

Commits de la fase: `b1f2cf8` (logout), `901013f` (logotipo), `4948400` (ticket) y el commit final de experiencia de cajero/documentación, indicado en la entrega. Sin push ni merge.
