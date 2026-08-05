# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: assets — compila los assets de Vue/Vite.
# Node solo vive en esta etapa; nunca llega a la imagen final.
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app

# Instalar dependencias con la lockfile para builds reproducibles.
COPY package.json package-lock.json ./
RUN npm ci

# Copiar el resto del proyecto y compilar. laravel-vite-plugin
# escribe el manifest y los assets en public/build.
COPY . .
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: vendor — instala dependencias PHP de produccion.
# La imagen de composer puede traer otra version de PHP, por eso
# --ignore-platform-reqs; las extensiones reales se validan en la etapa final.
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY . .
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-dist \
        --ignore-platform-reqs \
        --optimize-autoloader

# ---------------------------------------------------------------------------
# Stage 3: production — imagen final de la app (php-fpm sobre alpine).
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS production

# UID/GID configurables para alinear permisos con el host en desarrollo.
ARG UID=1000
ARG GID=1000

WORKDIR /var/www/html

# Librerias de runtime (se quedan) + libs de compilacion (paquete virtual
# que se elimina al terminar para no engordar la imagen final).
RUN apk add --no-cache \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
    && apk del .build-deps

# Configuracion de opcache orientada a produccion.
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Usuario no-root que ejecutara php-fpm.
RUN addgroup -g "${GID}" dulceria \
    && adduser -u "${UID}" -G dulceria -D -H dulceria

# Codigo de la app (con vendor) y los assets compilados.
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# Permisos correctos sobre las rutas escribibles ANTES de bajar privilegios.
RUN chown -R dulceria:dulceria storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

USER dulceria

EXPOSE 9000
CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# Stage 4: web — nginx con los assets compilados dentro.
# Construir nginx con los assets adentro (en lugar de compartir un volumen
# nombrado con la app) evita servir assets viejos tras un rebuild.
# ---------------------------------------------------------------------------
FROM nginx:alpine AS web
WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=assets /app/public /var/www/html/public
