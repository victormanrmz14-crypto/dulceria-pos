# Configuración propuesta de Dulcería POS en Lightsail

Estado: creación autorizada; instancia `dulceria-pos-demo` creada e IP fija **13.184.138.10** asignada. Docker y Compose verificados; despliegue de la aplicación en curso. Región: Ohio, `us-east-2`. Perfil local autenticado: `dulceria.` (con punto final). Fecha de precios: 24 de noviembre de 2026.

## Máquina y costo

| Configuración | Propuesta |
|---|---|
| Servicio | Amazon Lightsail, instancia Linux de propósito general |
| Nombre | `dulceria-pos-demo` |
| Región / zona | `us-east-2` / `us-east-2a` |
| Sistema | Ubuntu 24.04 LTS, blueprint `ubuntu_24_04` |
| Paquete | `medium_3_0`, IPv4 incluida |
| CPU / memoria | 2 vCPU / 4 GB RAM |
| Disco incluido | 80 GB SSD, compartidos por sistema, imágenes, datos y archivos |
| Transferencia del paquete | 4096 GB mensuales (4 TB) |
| Tarifa del paquete | **USD 24.00 mensuales**, antes de impuestos y extras |
| IP fija | Sin cargo adicional mientras esté adjunta a la instancia |

La tarifa y disponibilidad se confirmaron con `lightsail get-bundles`, `get-blueprints` y `get-regions` en la cuenta y región seleccionadas. Coincide con la [tarifa pública de AWS](https://aws.amazon.com/lightsail/pricing/). No se descuentan promociones ni créditos cuya elegibilidad no se haya verificado.

El importe base cubre una máquina con su disco y transferencia incluidos. No se proponen RDS, balanceadores, NAT Gateway, CloudFront, discos adicionales ni servicios de correo de pago. El proxy TLS y MySQL/Redis se ejecutan en esa misma máquina. Para acceso por IP, Caddy solicita un certificado público Let's Encrypt con el perfil `shortlived`, disponible para IP desde 2026; no requiere comprar un dominio. [Disponibilidad oficial](https://letsencrypt.org/2026/01/15/6day-and-ip-general-availability).

**Posibles cargos adicionales:** impuestos, registro de dominio, excedentes de transferencia, snapshots o recursos que se agreguen posteriormente. La transferencia entrante y saliente consume la cuota; el exceso facturable se aplica a salida según las reglas de AWS. La tarifa base no es un límite para toda la cuenta. No hay una estimación de dominio porque todavía no se ha elegido uno.

Una IP fija desasociada durante más de una hora tiene tarifa de USD 0.005/h. Las instancias Lightsail normales generan cargos desde su creación hasta su eliminación, incluso detenidas. Eliminar una instancia no elimina necesariamente sus snapshots u otros recursos asociados: [facturación e IP fija](https://docs.aws.amazon.com/lightsail/latest/userguide/amazon-lightsail-frequently-asked-questions-faq-billing-and-account-management.html), [ciclo de cobro](https://docs.aws.amazon.com/lightsail/latest/userguide/getting-started.html).

## Archivos y alcance

- `instance.json`: argumentos utilizados para crear la máquina aprobada.
- `firewall.example.json`: reglas IPv4 TCP 80/443 públicas y SSH desde una sola IP. `192.0.2.1/32` es un marcador reservado: debe sustituirse por la IP pública real del operador inmediatamente antes de aplicar las reglas. No abre IPv6, MySQL, Redis, PHP-FPM ni Vite.
- `bootstrap.sh`: user data para la **instancia Ubuntu nueva**. Instala Docker desde su repositorio oficial, Compose y Git; limita los logs de contenedores cuando no hay configuración previa. No contiene claves, contraseñas, código comercial ni comandos de creación de recursos AWS.
- `production.env.example`: plantilla del entorno del servidor. `APP_KEY`, contraseñas, correo y dominio deben reemplazarse. No copiar la `.env` de la laptop al servidor ni publicar credenciales de la simulación.
- `Caddyfile`: HTTPS para un dominio con correo de contacto. `Caddyfile.ip`: HTTPS para la IP pública con emisor ACME explícito y perfil `shortlived`; no usa un certificado autofirmado. `CADDY_CONFIG_FILE` selecciona la variante.
- `prepare-env.py`: genera `.env` en el servidor con claves nuevas y permisos 600. Rechaza sobrescribir un entorno existente y no imprime secretos. Ejemplo: `python3 deploy/lightsail/prepare-env.py --host 13.184.138.10 --image-tag REVISION`.
- `../../docker-compose.lightsail.yml`: extensión explícita de Compose de producción; retira el puerto público de Nginx y publica solamente Caddy. Añade un worker Redis con la misma imagen de la aplicación. Conserva los volúmenes persistentes existentes y añade los de certificados.

La ruta del servidor será `/srv/dulceria-pos`. El proyecto Compose será `dulceria-prod`. Los seis servicios son app, Nginx, MySQL, Redis, Caddy y worker. Se mantiene la autenticación Laravel, sesiones Redis y aislamiento multi-tenant actuales.

## Secuencia después de autorizar la creación

Estos pasos describen la ejecución futura. La aprobación del precio de la máquina habilita crear la instancia; configurar DNS o comprar un dominio se tratará según el dominio elegido.

1. Comprobar cuenta, región, paquete y ausencia de una instancia con el nombre propuesto. Crear el par de despliegue `dulceria-pos-deploy` en Lightsail y guardar su clave privada solo en la estación del operador, con permisos 600, sin mostrarla. No copiar credenciales AWS a la máquina. La importación de claves locales fue rechazada por el servicio; se utilizó el par generado por AWS.
2. Crear la instancia con los argumentos revisados y el bootstrap:

   ```bash
   aws lightsail create-instances \
     --profile 'dulceria.' --region us-east-2 \
     --cli-input-json file://deploy/lightsail/instance.json \
     --user-data file://deploy/lightsail/bootstrap.sh
   ```

3. Aplicar inmediatamente el firewall con la IP real del operador mediante `put-instance-public-ports`. Reservar `dulceria-pos-demo-ip` y adjuntarla a `dulceria-pos-demo`. Mantener la IP asociada mientras se use. Esperar a que el bootstrap termine: `/var/lib/dulceria-pos/bootstrap-ready`.
4. Transferir una versión aprobada del código **incluidos estos archivos de configuración**. Usar Docker Compose con los dos `-f` explícitos para excluir el override de desarrollo.
5. Preparar `/srv/dulceria-pos/.env` con `prepare-env.py`. Genera una nueva `APP_KEY` Laravel (`base64:` y 32 bytes aleatorios) y contraseñas distintas para MySQL y root, directamente en el servidor. `APP_URL` será `https://` seguido del mismo `APP_DOMAIN`, que puede ser una IP pública. No ejecutar `key:generate` contra una imagen sin `.env` interna esperando que modifique la `.env` del host: las variables se inyectan desde Compose.
6. Para IP, seleccionar `Caddyfile.ip` mediante el generador; para dominio, crear el registro A hacia la IP fija y configurar el correo TLS. Construir/levantar el entorno:

   ```bash
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml build app nginx
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml up -d
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml exec -T app php artisan migrate --force
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml exec -T app php artisan config:cache
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml exec -T app php artisan route:cache
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml exec -T app php artisan view:cache
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml restart worker
   sudo docker compose -f docker-compose.yml -f docker-compose.lightsail.yml exec -T app php docker/check-assets.php http://nginx
   ```

7. Crear el SuperAdmin por el comando interactivo existente `platform-admin:create`, con contraseña nueva. Verificar HTTPS, cookies seguras, login/logout, permisos, archivos, assets y conectividad de la base. No usar `migrate:fresh`, restaurar la base local ni cargar la simulación automáticamente. SMTP continúa pendiente: `MAIL_MAILER=log` no entrega correos reales.

## Estado sin dominio y límites

La entrega inicial utiliza `https://13.184.138.10`, con certificado público para la IP y renovación automática a cargo de Caddy. La emisión y renovación requieren mantener abiertos 80/443 y conservar el volumen `caddy_data`. Los valores `pos.example.com` y `admin@example.com` de la plantilla son ejemplos y no se utilizan para solicitar certificados. Cuando se disponga de dominio se puede cambiar a `Caddyfile` y actualizar `APP_URL`/`APP_DOMAIN`.

Esta arquitectura inicial usa un único servidor: es un punto de partida para demo y validación de carga, no alta disponibilidad ni capacidad garantizada para un número de negocios. Los datos persisten ante recreación de contenedores mediante volúmenes, pero eso no es un respaldo externo y no protege ante pérdida del servidor. Antes de operar con datos comerciales debe acordarse respaldo y recuperación. No se han contratado ni configurado snapshots en esta propuesta.

El bootstrap, los JSON de instancia/firewall y el modelo combinado de Compose se validaron localmente. Se comprobó que solo Caddy publica 80/443, que el worker utiliza la imagen de la aplicación y que los volúmenes de datos se conservan. `caddy validate` confirmó las dos variantes; su imagen quedó fijada al digest validado. `prepare-env.py` se comprobó en un directorio temporal: permisos, secretos nuevos, rechazo de sobreescritura y entradas inválidas. El wrapper `/bin/sh` de Lightsail rechazó inicialmente `pipefail`; se corrigió el bootstrap para entrar explícitamente en Bash y se ejecutó por SSH. Docker 29.10.1 y Compose v5.8.0 verificados en la VM; el error inicial permanece en el historial de cloud-init. La verificación pública se completará al terminar el despliegue.

## Validación local sin crear recursos

```bash
bash -n deploy/lightsail/bootstrap.sh
docker compose --env-file deploy/lightsail/production.env.example \
  -f docker-compose.yml -f docker-compose.lightsail.yml config --quiet
git diff --check
```

La validación puede leer la `.env` local existente a través de `env_file`; no imprime sus valores. Para inspeccionar el modelo sin resolver sus secretos se utiliza `config --no-env-resolution --format json`. No ejecutar el bootstrap en la laptop.
