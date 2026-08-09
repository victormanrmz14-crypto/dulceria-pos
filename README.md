# Dulcería POS

> Sistema de punto de venta multiempresa para dulcerías y tiendas de abarrotes.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vue](https://img.shields.io/badge/Vue-3.x-4FC08D?style=flat-square&logo=vue.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white)

---

## Descripción

**Dulcería POS** es una aplicación web de punto de venta desarrollada con Laravel 12, Inertia.js y Vue 3. Permite gestionar ventas en tiempo real, controlar inventario, administrar movimientos de caja, generar cortes de turno y visualizar reportes con gráficas interactivas.

El sistema distingue tres roles: **super admin** (gestión de toda la plataforma), **administrador** (gestión de su dulcería) y **cajero** (punto de venta y caja).

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.3 + Laravel 12 |
| Frontend | Vue 3 + Inertia.js |
| Base de datos | MySQL 8.0 |
| Caché y sesiones | Redis 7 |
| Autenticación web | Laravel Breeze |
| Autenticación API | Laravel Sanctum |
| Servidor web | Nginx (Alpine) |
| Contenedores | Docker + Docker Compose |

---

## Instalación con Docker

### Requisitos

- [Docker](https://docs.docker.com/get-docker/) 24+
- [Docker Compose](https://docs.docker.com/compose/) v2+

### 1. Clonar el repositorio

```bash
git clone https://github.com/victormanrmz14-crypto/dulceria-pos.git
cd dulceria-pos
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita `.env` y establece al menos estas variables:

```env
DB_PASSWORD=una_contraseña_segura
DB_ROOT_PASSWORD=otra_contraseña_para_root
APP_KEY=          # se genera en el paso 4
```

### 3. Levantar los contenedores

```bash
docker compose up -d
```

Esto levanta cuatro servicios: `app` (PHP-FPM), `nginx`, `db` (MySQL 8) y `redis`.

### 4. Generar clave de la aplicación

```bash
docker compose exec app php artisan key:generate
```

### 5. Ejecutar migraciones

```bash
docker compose exec app php artisan migrate
```

### 6. Crear el primer super administrador

```bash
docker compose exec app php artisan platform-admin:create
```

### 7. Acceder a la aplicación

Abre [http://localhost](http://localhost) en tu navegador.

---

## Instalación local (sin Docker)

### Requisitos

- PHP 8.2+ con extensiones: `pdo_mysql`, `mbstring`, `bcmath`, `zip`, `gd`
- Composer 2.x
- Node.js 18+ y npm
- MySQL 8.0+

### Pasos

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configura DB_* en .env con tus credenciales locales
php artisan migrate
npm run build
php artisan serve
```

Accede en: [http://localhost:8000](http://localhost:8000)

---

## API REST

La plataforma expone una API con autenticación Bearer Token (Sanctum).

### Obtener token

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"tu@correo.com","password":"tu_password"}'
```

### Usar el token

```bash
curl http://localhost/api/productos \
  -H "Authorization: Bearer TU_TOKEN"
```

### Cerrar sesión

```bash
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer TU_TOKEN"
```

La colección completa con todos los endpoints y tests automatizados está en `dulceria-pos.postman_collection.json`. Impórtala en Postman con **File → Import**.

---

## Módulos del sistema

| Módulo | Descripción |
|--------|-------------|
| Punto de venta | Búsqueda de productos, carrito y registro de ventas |
| Caja | Ingresos y retiros de efectivo independientes de ventas |
| Cortes de turno | Cierre por turno con resumen de efectivo y tarjeta |
| Productos | CRUD con alertas de stock bajo |
| Reportes | Gráficas de ventas por día, semana y mes |
| Catálogos | Categorías, marcas y proveedores |
| Usuarios | Alta, edición y desactivación de cajeros |
| Configuración | Apariencia, datos del negocio y formato de tickets |
| Super Admin | Panel de gestión de toda la plataforma multiempresa |

---

## Autor

**Victor Ramirez Mendoza**
- GitHub: [@victormanrmz14-crypto](https://github.com/victormanrmz14-crypto)
- Email: victorman.rmz14@gmail.com

---

<p align="center">Hecho con ❤️ en México 🇲🇽</p>
