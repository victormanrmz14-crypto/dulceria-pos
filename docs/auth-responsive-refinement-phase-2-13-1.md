# Fase 2.13.1 · Refinamiento responsive de autenticación

24 de noviembre de 2026. Rama `refactor/premium-ui`; base `03a8c78`, árbol inicial limpio. Implementación visual pendiente de revisión estética. Logotipo e identidad actuales intactos; Figma queda para una iteración independiente.

## Diagnóstico medido antes de editar

Playwright MCP sobre `http://localhost`, con fuentes cargadas y scroll al inicio. El problema era el tamaño mínimo del contenido, no una altura fija de tarjeta ni un bloqueo de overflow:

- A 1366 × 768, el login medía **805 px**. El panel lateral imponía esa altura al grid: historia de 668 px, incluidos 116 px de padding vertical, 52 px antes de la composición y 325 px de ilustración; más logo, pie y padding exterior. El formulario cabía, pero el documento excedía el viewport por 37 px.
- En el mismo viewport, el registro medía **1007 px**: encabezado de 106, cuerpo de 835 y pie de 66. Nombre/correo apilados, leyenda flotada, grupos espaciados y el límite de 460 px consumían altura. Incluso a 1440 × 900 aparecían 107 px de scroll.
- A 320 × 568, el login medía **745 px**: encabezado 100, cuerpo 579 y pie 66. Había 68 px de padding en main, además de separaciones del formulario y 48 px de padding en el pie. A 375 × 667 sobraban 34 px.
- No había overflow horizontal ni scroll dentro de tarjetas. `min-height:100svh` era un mínimo legítimo; el exceso procedía de contenido y espaciado acumulados. No se sustituyó por una altura fija ni se escondió el scroll.

## Correcciones

1. Ritmo compartido: padding vertical fluido de 16–32 px, campos separados 16 y secciones 20. Bajo 800 px de alto se usan 12/12/16 px y encabezado de 64; en móviles de hasta 600 px de alto, padding de 8, encabezado de 60 y título de 26.
2. Encabezado normal de 76 px y pie de 38 px, aproximadamente, con safe areas. El formulario conserva centrado vertical cuando cabe y crecimiento natural cuando necesita más altura.
3. Panel lateral con márgenes, padding y composición más contenidos. En escritorio de hasta 680 px de alto se oculta solo la ilustración: marca y mensaje permanecen. No cambia el asset ni el tamaño del logo.
4. Registro de hasta 520 px; nombre/correo y contraseñas en dos columnas desde 600 px, siempre una columna en los teléfonos probados. Fieldsets con separador entre grupos y sin leyenda flotada. Se conservan campos, ayudas, nota para cajeros y acceso al login.
5. `min-height:100dvh`, con fallbacks `100svh` y `100vh`: acompaña los cambios del viewport sin limitar la altura real. Scroll del documento, sin tarjetas con scroll interno, posiciones fijas, escala ni zoom CSS.

**Se mantienen** inputs de 48 px y texto de 16 px, botones principales de 50 px, control de contraseña de 44 px, etiquetas, contraste, foco de 2 px, orden de tabulación y separación mínima de 8 px entre contraseña y recuperación. No hay cambios de JavaScript, backend o reglas de autenticación.

## Resultados a tamaño normal

Estado sin errores, diez resoluciones por cada una de las cuatro pantallas públicas: **40 mediciones**. Valores de altura total del documento, en píxeles CSS. No se aplicó zoom CSS ni escala de interfaz; DPR 1 en el contexto de comprobación.

| Viewport | Login: alto antes → después | Registro: alto antes → después | Recuperación / reset finales |
|---|---|---|---|
| 320 × 568 | 745 → 568 | 1116 → 910 | Sin scroll / scroll natural de 95 px |
| 375 × 667 | 701 → 667 | 1116 → 931 | Sin scroll / sin scroll |
| 390 × 844 | 844 → 844 | 1116 → 997 | Sin scroll / sin scroll |
| 430 × 932 | 932 → 932 | 1116 → 979 | Sin scroll / sin scroll |
| 768 × 1024 | 1024 → 1024 | 1024 → 1024 | Sin scroll / sin scroll |
| 820 × 1180 | 1180 → 1180 | 1180 → 1180 | Sin scroll / sin scroll |
| 1024 × 768 | 772 → 768 | 1102 → 768 | Sin scroll / sin scroll |
| 1366 × 768 | 805 → 768 | 1007 → 768 | Sin scroll / sin scroll |
| 1440 × 900 | 900 → 900 | 1007 → 900 | Sin scroll / sin scroll |
| 1920 × 1080 | 1080 → 1080 | 1080 → 1080 | Sin scroll / sin scroll |

Login completo y botón visible sin scroll en las diez resoluciones. Registro completo sin scroll en tablets y los cuatro escritorios; los teléfonos conservan scroll natural de una columna. En 390 × 844 y 430 × 932 el botón ya es visible antes de desplazar, aunque la explicación, acceso al login y pie requieren más altura. La recuperación cabe en las diez; reset necesita scroll legítimo únicamente a 320 × 568.

En todas las mediciones: cero desbordamiento horizontal, campos completos de 48 px / 16 px y cero contenedores con scroll interior. Se revisaron visualmente las capturas de login, registro y recuperación, en móvil y laptop.

## Presión, errores y accesibilidad

- **Altura reducida:** a 1366 × 600 el login y la solicitud de recuperación caben completos. Registro y reset crecen naturalmente; sus botones se alcanzan por scroll.
- **Teclado emulado:** viewport reducido de 390 × 844 a 390 × 360, conservando el campo enfocado. Se comprobó que campo y acción pueden mostrarse mediante desplazamiento normal; cuerpo con `overflow-y:visible`. No acredita el desplazamiento automático del teclado Android.
- **Errores extensos:** fixtures exclusivos del DOM del navegador en login 390 px, registro 1366 px y reset 320 px. Incluyen aviso, error asociado y cadenas largas sin espacios; conservan toda la información, sin overflow horizontal y con botón alcanzable. Son pruebas de presentación, no respuestas de validación ni envíos al servidor.
- **Teclado:** Tab respeta nombre → correo → contraseña → mostrar contraseña → confirmación → mostrar confirmación; Enter alterna el control, sin perder foco. Foco visible de 2 px. Labels y atributos de accesibilidad existentes intactos.
- **125 % y 200 %:** se comprobó reflujo equivalente reduciendo el viewport de referencia 1366 × 768 a **1093 × 614** y **683 × 384**. Las cuatro pantallas conservan contenido, ausencia de overflow horizontal y botones alcanzables por scroll. DPR y zoom CSS permanecieron en 1. **No es zoom nativo del navegador ni prueba de magnificación real**; esa comprobación queda pendiente. No se usó `transform:scale()` ni zoom CSS para arreglar o verificar el layout.
- Confirmación de contraseña comparte el layout y las reglas revisadas. Se inspeccionó su fuente; no se abrió con una sesión autenticada ni se crearon usuarios para esta fase.

## Evidencia y comprobaciones

[Galería antes/después](../.playwright-mcp/auth-responsive-2-13-1/comparison.html), fuera de Git: ocho pares comparables (login laptop/móvil/320, registro laptop/móvil, recuperación laptop y reset laptop/320), cinco capturas de presión y tres de errores simulados. **24 capturas**, sin cientos de imágenes repetidas. `before.json` y `after.json` conservan las medidas. Las capturas de altura corta muestran el viewport visible; los pares principales son de página completa para hacer evidente el scroll eliminado.

- Compilación Vite y Blade correctas; `git diff --check` correcto.
- Revisión estática: los cinco campos del registro conservan exactamente atributos, nombres, valores, validaciones declarativas, formulario y CSRF. Único cambio Blade: contenedor visual de nombre/correo.
- Assets de marca, favicon, componentes de logo, JavaScript, rutas, controladores, modelos, permisos y módulos comerciales sin diff.
- Todas las comprobaciones visuales usan GET y fixtures en memoria del navegador, sin operaciones comerciales ni creación de cuentas. No se ejecutó testing funcional general.
- Localhost permanece operativo con las imágenes Docker actualizadas. Sin servidores auxiliares. Se conserva la advertencia preexistente de Browserslist desactualizado, sin actualizar dependencias.

## Archivos y límites

Archivos modificados: `resources/css/auth.css`, `resources/views/auth/register.blade.php`, reglas responsive de autenticación en `UI_DESIGN_SYSTEM.md` y este informe. Capturas, logs y mediciones quedan fuera del commit.

Pendientes: zoom nativo 125/200 %, teclado físico Android, barras móviles y safe areas físicas, WebView real y su `adjustResize`; las emulaciones no certifican esos comportamientos. La identidad definitiva se sigue desarrollando en Figma: no se utilizó Figma MCP, no se descargaron recursos y no se modificó la marca provisional.

Commit de entrega: `refactor(auth-ui): optimizar layouts responsive y scroll`. Sin push ni merge. La aprobación estética corresponde al usuario.
