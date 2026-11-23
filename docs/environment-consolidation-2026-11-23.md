# Fase 2.5 · Consolidación de Git y recuperación de localhost

Fecha: 23 de noviembre de 2026, America/Mexico_City. Rama: `refactor/premium-ui`. No se hizo push, merge, rebase ni una nueva iteración visual.

## A. Commits

La rama y `main` partían de `11535cb` (`feat(pos): mejoras de ventas, tickets, productos y configuración`). No existían commits propios de las fases premium. El índice estaba vacío; todo el trabajo visual estaba modificado o sin seguimiento.

| Hash | Fase | Mensaje | Archivos |
|---|---|---|---|
| `e6db1d7` | Herramientas | `chore(dev): versionar skills del flujo de trabajo visual` | 120 archivos de las seis skills y `skills-lock.json`; sin cachés |
| `d7db6eb` | 2.1–2.2 consolidadas | `feat(ui): consolidar dashboard premium y refinamiento visual` | 15: package/lock, premium.css, Dashboard, AppLayout, SidebarNav, StatCard, WelcomeModal, cinco primitivas Ui/tema, design system e informe |
| `d1a2d64` | 2.3 | `feat(ui): implementar navegacion responsive movil` | 9: AppLayout, SidebarNav, premium.css, UiBottomNav, composable de visibilidad, tenantNavigation, Blade, design system e informe |
| `3128690` | 2.4 | `refactor(ui): modernizar interfaz del punto de venta` | 5: Pos.vue, PosCartPanel, PosCheckout, design system e informe |
| Commit de cierre de este informe | 2.5 | `chore(dev): retirar evidencias temporales y documentar recuperacion local` | .gitignore, documentación histórica, design system y este informe |

Todos son commits nuevos. Se agrupan 2.1 y 2.2 porque el estado completo de las primitivas compartidas ya incluía mejoras posteriores y no había una copia íntegra de todos sus estados iniciales. No se fabrican commits para aparentar una separación histórica exacta. Se usaron las copias de AppLayout, SidebarNav, CSS y design system previas a navegación para preparar el índice de forma selectiva, sin reemplazar los archivos finales del workspace.

Los árboles preparados de dashboard y navegación se exportaron fuera del repositorio y compilaron antes de sus commits. Una comparación SHA-256 confirmó que consolidar Git no cambió ningún archivo final de `resources/`, package/lock o skills-lock. Las modificaciones posteriores se limitan a limpieza y documentación.

## B. Limpieza controlada

Se inventariaron individualmente ruta, tamaño, SHA-256 y condición Git antes de borrar. Ningún archivo de `.playwright-mcp/` estaba versionado; 68 estaban ignorados y 254 sin seguimiento. Los archivos se eliminaron individualmente después de comprobar sus hashes y detener sus consumidores; solo después se retiraron directorios vacíos. No se utilizó `git clean`, `rm -rf` ni eliminación indiscriminada de directorios.

| Grupo homogéneo eliminado | Archivos | Motivo |
|---|---:|---|
| `.playwright-mcp/` raíz | 10 | Capturas, snapshots y logs de revisión |
| `.playwright-mcp/premium-pilot/` | 29 | Capturas y galería temporal de la primera iteración |
| `.playwright-mcp/premium-refinement/` | 44 | Capturas, resultados, galería y copias de referencia ya utilizadas para consolidar |
| `.playwright-mcp/bottom-navigation/` | 85 | Evidencias de navegación, resultados, logs y copias ya utilizadas |
| `.playwright-mcp/pos-premium/` | 154 | Evidencias, scripts de captura/MCP, fixtures, servidor de preview y copia de assets compilados |
| **Total de evidencias** | **322** | **17,268,746 bytes, aproximadamente 16.5 MiB** |
| `.agents/skills/ui-ux-pro-max/scripts/__pycache__/core.cpython-314.pyc` | 1 | Caché regenerable de Python |
| `.agents/skills/ui-ux-pro-max/scripts/__pycache__/design_system.cpython-314.pyc` | 1 | Caché regenerable de Python |
| `.agents/skills/ui-ux-pro-max/scripts/__pycache__/reasoning_contract.cpython-314.pyc` | 1 | Caché regenerable de Python |

Se preservan código, estilos, dependencias, Docker/Vite, backend, migraciones, `UI_DESIGN_SYSTEM.md`, informes históricos, skills y sus pruebas permanentes, `skills-lock.json` y `.vscode/mcp.json`. Este último continúa ignorado por Git porque contiene rutas locales; no se alteró. Los enlaces a evidencias retiradas se sustituyen por referencias históricas sin enlaces rotos. `.gitignore` evita incorporar nuevas evidencias y cachés.

Las copias de referencia exportadas para comprobar los commits estuvieron únicamente en `/tmp`, sin servidores TCP ni dependencias instaladas nuevas; no forman parte del repositorio.

## C. Puertos y procesos

El estado persistido de la ventana WSL de este workspace contenía **19 reenvíos**. No equivalían a 19 servidores activos: solo cinco de esos puertos escuchaban en WSL al comenzar; catorce eran entradas históricas sin listener. Se consultó el almacenamiento de VS Code en modo de solo lectura; no se editó su SQLite ni se reinició el editor.

Dirección local de los 19 reenvíos: `127.0.0.1`. La tabla diferencia el proceso real del registro de reenvío.

| Puerto | Propietario o finalidad identificada | Estado anterior | Estado final del servicio |
|---|---|---|---|
| 17803 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 27595 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 32979 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 37873 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 39383 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 39989 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 40403 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 41125 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 42679 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 46351 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 46671 | Registro histórico, sin propietario activo | Solo reenvío guardado | Sin listener; no se mató proceso |
| 8181 | Preview histórica del piloto | Solo reenvío guardado | Sin listener |
| 8182 | Servidor histórico de comparación | Solo reenvío guardado | Sin listener |
| 8183 | Galería histórica de refinamiento | Solo reenvío guardado | Sin listener |
| 8184 | Python PID 12465, preview del POS | Escuchaba en `0.0.0.0` | Detenido |
| 8185 | Python PID 12465, baseline del POS | Escuchaba en `0.0.0.0` | Detenido |
| 8931 | Node PID 12468, MCP temporal hijo del anterior | Escuchaba en `127.0.0.1` | Detenido |
| 38663 | VS Code Agent Host, PID 1120 | Activo | Conservado |
| 45173 | VS Code Agent Host, PID 1120 | Activo | Conservado |

Se cerró el browser de fixtures y se envió SIGTERM únicamente al MCP temporal, comprobando comando, PID y cwd del proyecto. Su supervisor cerró ambas previews. Los PIDs 12465 y 12468 desaparecieron; 8184, 8185 y 8931 dejaron de escuchar también desde Windows. La configuración MCP activa del editor se conserva.

Otros servicios identificados y preservados:

| Puerto / dirección | Propietario | Estado anterior → final |
|---|---|---|
| `80`, todas las interfaces | Docker Desktop publica Nginx | Sin listener → publicado, HTTP 200 |
| `5173`, `127.0.0.1` de Windows | Node PID 9828, Vite de `C:\Users\vicram\AndroidStudioProjects\openGym\frontend` | Activo en la inspección; no se intervino. En la consulta final ya no escuchaba por un cambio externo a esta tarea |
| `3306`, `127.0.0.1` WSL | MySQL nativo, PID 310, usuario mysql | Activo → conservado |
| `33060`, `127.0.0.1` WSL | Puerto X del mismo MySQL nativo | Activo → conservado |
| `24594`, `127.0.0.1` | VS Code Agent Host, PID 1120 | Activo → conservado |
| `38863`, `127.0.0.1` | VS Code Server, PID 816 | Activo → conservado |
| `53`, `127.0.0.53` y `127.0.0.54` | Resolución DNS de WSL/Linux | Activo → conservado |
| `53`, `10.255.255.254` | DNS de WSL | Activo → conservado |

También se observaron puertos normales de Windows, ADB, Android Studio y Java, que quedan fuera de este proyecto y no se tocaron.

### Reenvíos de VS Code pendientes de retirar en la UI

No hay una herramienta conectada que administre los reenvíos de esta ventana. Detener un servidor no garantiza que VS Code elimine su fila o su estado de restauración. No se declara que la lista del editor haya sido limpiada automáticamente.

1. En VS Code, abre **Ver → Abrir vista… → Puertos** (View → Open View… → Ports), o la pestaña **Puertos** del panel inferior.
2. En cada fila 8181–8185 y 8931, haz clic derecho → **Dejar de reenviar puerto** / **Stop Forwarding Port**.
3. Para las once filas históricas de puertos altos de la primera tabla, comprueba que siguen sin proceso y aplica la misma acción. Esta operación retira el reenvío; no termina un servicio.
4. Conserva 38663, 45173 y los puertos identificados como infraestructura del editor. No retires 5173 de otro proyecto si lo estás usando. El puerto 80 ya lo publica Docker Desktop y no requiere un reenvío adicional de VS Code.

## D. Docker

**Causa inicial:** Docker Desktop no se ejecutaba en Windows. No existía el named pipe `dockerDesktopLinuxEngine`, no había socket Docker integrado en WSL y los symlinks de CLI bajo `/usr/bin` apuntaban a un montaje Docker Desktop ausente. No era un conflicto con los puertos temporales.

Se inició el ejecutable instalado de Docker Desktop del usuario. Esto restauró el motor y sus cuatro contenedores locales existentes por sus políticas de reinicio. No se levantó otro stack ni se modificó el contexto Docker.

Se comprobó que las imágenes de app/Nginx contenían un manifest anterior. Después se ejecutaron exclusivamente:

```bash
docker compose -f docker-compose.yml build app nginx
docker compose -f docker-compose.yml up -d --no-deps app nginx
```

El `-f` conserva el modo de ejecución identificado en las etiquetas de los contenedores y evita arrancar el servicio Vite del override, que intentaría usar el 5173 ocupado por otro proyecto. No se editaron Dockerfile, Compose ni configuración Nginx.

| Servicio | Estado final | Puertos |
|---|---|---|
| `nginx` | Activo, imagen reconstruida | `0.0.0.0:80→80`, `[::]:80→80` |
| `app` | Activo, imagen reconstruida; PHP-FPM listo | `9000/tcp` interno |
| `db` | Activo y healthy; mismo contenedor | `3306/tcp`, `33060/tcp` internos, sin publicar |
| `redis` | Activo; mismo contenedor | `6379/tcp` interno, sin publicar |

Se conservaron `dulceria-pos_dbdata`, `dulceria-pos_redisdata` y `dulceria-pos_storage_public`, con sus montajes. DB y Redis conservan sus IDs anteriores: `9b96177cd6fd…` y `3c5fdff8ae14…`. Antes de recrear app se comprobó que no contenía archivos comerciales fuera del volumen público; sus modificaciones de filesystem eran cachés/vistas/logs de runtime. No se ejecutaron migraciones, limpiezas Docker, cambios de credenciales ni operaciones sobre usuarios o bases de datos.

## E. Localhost y assets

- Acceso habitual: **[http://localhost](http://localhost)**.
- HTTP desde WSL: **200**. HTTP desde PowerShell/Windows: **200**.
- PHP-FPM y Nginx arrancan sin errores que impidan servir la aplicación.
- Manifest de app, Nginx y checkout: SHA-256 idéntico `99d88b2f6f812ecef2479eb2fd761cc93369433e5148db3d5542f17636222f50`.
- JS/CSS de entrada, Dashboard y POS publicados por Nginx: **200** en los seis assets comprobados.
- Dashboard, POS y Productos sin autenticar redirigen **302** a login, comportamiento esperado de las rutas protegidas; no se elude autenticación.
- Se abrió la aplicación real en Chromium con Playwright, sin fixtures ni proxy. La sesión de la auditoría anterior había expirado, por lo que se solicitó al propietario iniciar sesión sin compartir su contraseña. La ventana de validación se cerró antes de recibir una sesión autenticada; no quedó un servidor de validación ni un nuevo puerto TCP.

**Revisión autenticada:** pendiente de la sesión del propietario para comprobar dashboard renderizado, navegación móvil y carga de módulos. La publicación de los bundles actuales está comprobada, pero no sustituye esa revisión autenticada.

## F. Estado de Git

Rama final: `refactor/premium-ui`. `main` y `origin/main` permanecen en `11535cb`; no se publicaron cambios. Los commits visuales están ordenados por dependencias y los árboles intermedios de dashboard/navegación compilan. La compilación Docker del estado completo también fue correcta.

El commit de cierre recoge las exclusiones de artefactos y la documentación de limpieza. Se comprueba el árbol final con `git status --short`, sin incorporar `.env`, credenciales, `.vscode/mcp.json`, assets compilados ni evidencias temporales. El diff de whitespace de las skills importadas conserva el formato upstream; sus fuentes no se reformatearon ni se alteraron sus hashes.

## G. Intervención pendiente

1. Retirar en la vista Puertos de VS Code los reenvíos temporales/históricos indicados arriba; sus servidores ya están detenidos.
2. Completar un acceso autenticado para cerrar la comprobación visual del dashboard, navegación y POS. No hacen falta cambios de cuentas o credenciales.

La consolidación no cambia el estado de aprobación visual de las fases ni autoriza avanzar a otra fase.
