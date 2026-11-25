# GitHub CI/CD y midulcepos.shop

El flujo conserva GitHub Actions para pruebas, PHPStan y build/escaneo de Docker. `Deploy` usa un runner alojado en GitHub y se dispara cuando termina `CI` para `main`; verifica que el commit aprobado esté publicado y responda por HTTPS. Los pull requests y los pushes de otras ramas no despliegan.

La máquina AWS consulta la API pública de GitHub cada tres minutos mediante `dulceria-cd.timer`. Solo instala el último push de `main` cuyo workflow `ci.yml` haya terminado con éxito y cuyo SHA coincida con `origin/main`. Un fallo o una ejecución pendiente no autoriza instalar un commit anterior. El servicio serializa despliegues y cambios de dominio mediante un bloqueo de archivo.

No requiere registrar un runner en producción, abrir SSH a direcciones de GitHub ni agregar secretos AWS/SSH al repositorio. Este mecanismo depende de que el repositorio siga siendo público. Consulta como máximo unas 20 veces por hora; la API pública permite 60 solicitudes por hora por IP. [Límites de GitHub](https://docs.github.com/en/rest/using-the-rest-api/rate-limits-for-the-rest-api).

## Namecheap

En **Domain List → midulcepos.shop → Manage → Advanced DNS → Host Records**, con Namecheap BasicDNS:

| Tipo | Host | Valor | TTL |
|---|---|---|---|
| A Record | `@` | `13.184.138.10` | Automatic |
| CNAME Record | `www` | `midulcepos.shop` | Automatic |

Sustituir el CNAME de estacionamiento de `www` y el URL Redirect de `@` que entren en conflicto. Conservar los registros de correo. No agregar AAAA porque esta instalación solo abre acceso web IPv4. [Guía de Namecheap](https://www.namecheap.com/support/knowledgebase/article.aspx/319/2237/how-can-i-set-up-an-a-address-record-for-my-domain/).

Después de que ambos nombres resuelvan a la IP, ejecutar desde `/srv/dulceria-pos` como `ubuntu`:

```bash
python3 deploy/lightsail/activate-domain.py --domain midulcepos.shop
```

El script comprueba DNS, conserva `APP_KEY` y contraseñas, cambia las URL, verifica la configuración de Caddy, recrea únicamente app/Nginx/worker/Caddy y valida HTTPS. Caddy renueva automáticamente los certificados. `www` redirige al dominio raíz; la IP sigue funcionando para la verificación de CD. Un cambio de host requiere iniciar sesión nuevamente.

## GitHub

Los workflows versionados son la configuración: no se necesita crear un runner o secretos nuevos. En **Actions**, un push a `main` ejecuta `CI` y, si pasa, `Deploy`. Después de aprobar CI hay hasta unos tres minutos de espera más el build en AWS. Deploy solo pasa cuando el marcador público identifica el mismo SHA y `/up` y `/login` responden correctamente por HTTPS.

En **Settings → Actions → Runners**, retirar únicamente el runner correspondiente a la PC anterior cuando ya no se use para otros flujos. Las ejecuciones históricas que siguen esperando ese runner se pueden cancelar desde **Actions → Deploy → ejecución → Cancel workflow**. Cancelarlas no modifica el despliegue nuevo.

Los checks de PR siguen ejecutándose en GitHub. Para exigirlos antes de fusionar, configurar una regla de `main` que requiera los checks `test` y `docker`. No exigir `Deploy` como condición previa a fusionar: se ejecuta después de publicar `main`.

## Instalación del servicio en una máquina nueva

Con el código ya revisado en `/srv/dulceria-pos`, Docker operativo y `.env` existente, el administrador instala los archivos:

```bash
sudo install -d -m 755 /usr/local/lib/dulceria-pos
sudo install -d -o ubuntu -g ubuntu -m 700 /var/lib/dulceria-pos/cd
sudo install -m 755 deploy/lightsail/poll-deploy.py /usr/local/lib/dulceria-pos/poll-deploy.py
sudo install -m 644 deploy/lightsail/dulceria-cd.service deploy/lightsail/dulceria-cd.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now dulceria-cd.timer
sudo systemctl start --no-block dulceria-cd.service
```

Al migrar una aplicación ya publicada, inicializar `state.json` privado con el SHA completo de la versión actual antes de encender el timer. No cambiar el marcador de una versión real solo para poner checks en verde. El proceso crea `.deploy-status/release.json` público únicamente después de validar el despliegue; este archivo solo contiene SHA, ID de CI, estado y fecha, y se excluye de Git y del build. No contiene secretos ni identifica negocios.

## Operación y recuperación

```bash
sudo systemctl list-timers dulceria-cd.timer
sudo systemctl status dulceria-cd.service --no-pager
sudo journalctl -u dulceria-cd.service -n 30 --no-pager
sudo systemctl start --no-block dulceria-cd.service
```

Logs detallados y estado están en `/var/lib/dulceria-pos/cd/`, con directorio 700 y archivos privados. `.env` conserva permisos 600. El servicio corre como `ubuntu` y usa el acceso `sudo` de administración existente para Docker; los cambios aprobados de `main` son código confiable con capacidad de actualizar producción.

La compilación ocurre antes de reemplazar los contenedores. Las migraciones corren con la imagen nueva; después se recrean app/Nginx/worker/Caddy, se generan las cachés y se comprueban assets y HTTPS. MySQL, Redis, sus volúmenes y los certificados persisten. Si una actualización falla, se restauran el código y entorno anteriores y, cuando corresponde, los contenedores anteriores; el SHA fallido se reintenta después de 15 minutos. **Las migraciones de base no se revierten automáticamente:** deben ser compatibles con la versión anterior. El cambio de contenedores puede causar una breve interrupción; esta instalación no ofrece despliegue sin downtime.

No se borran imágenes antiguas automáticamente. Si el disco se llena tras muchos despliegues, revisar uso y retirar solo imágenes y caché sin uso, conservando la versión de recuperación. Para detener despliegues durante mantenimiento: `sudo systemctl stop dulceria-cd.timer`; comprobar también si el servicio ya está ejecutándose antes de editar el servidor. Activar el timer de nuevo al terminar.

Pruebas del mecanismo:

```bash
python3 -m unittest discover -s tests/deploy -p 'test_*.py' -v
git diff --check
```
