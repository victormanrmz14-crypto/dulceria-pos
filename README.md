# 🍬 Dulcería POS

> Sistema de punto de venta especializado para dulcerías y tiendas de abarrotes.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/Licencia-MIT-green?style=flat-square)

---

## 📋 Descripción

**Dulcería POS** es una aplicación web de punto de venta desarrollada con Laravel 12 y Livewire 3. Permite gestionar ventas en tiempo real, controlar el inventario, administrar movimientos de caja, generar cortes de turno y visualizar reportes de ventas con gráficas interactivas.

El sistema distingue dos roles: **administrador** (acceso total) y **cajero** (acceso al punto de venta y caja), con autenticación segura mediante Laravel Breeze.

---

## ✨ Características principales

- 🛒 **Punto de venta reactivo** — búsqueda de productos en tiempo real con Livewire, carrito dinámico y cálculo de cambio automático
- 💵 **Control de caja** — registro de ingresos y retiros con validación de saldo disponible
- 🧾 **Cortes de turno** — cierre de caja por turno con resumen de efectivo, tarjeta y movimientos
- 📦 **Gestión de inventario** — CRUD de productos con alertas de stock bajo y filtro persistente
- 📊 **Reportes y gráficas** — ventas por día/semana/mes con Chart.js
- 🏷️ **Catálogos** — categorías, marcas y proveedores
- 👥 **Gestión de usuarios** — alta, edición y desactivación de cajeros por el administrador
- 📧 **Recuperación de contraseña** — correo en español con plantilla personalizada
- 🔒 **Autorización por rol** — middleware `SoloAdmin` para rutas sensibles
- 🇲🇽 **Zona horaria México** — todas las fechas en `America/Mexico_City`

---

## 🛠️ Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP | 8.2+ |
| Framework | Laravel | 12.x |
| Componentes reactivos | Livewire | 3.x |
| Base de datos | MySQL | 8.0+ |
| Frontend interactivo | Alpine.js | 3.x (CDN) |
| Gráficas | Chart.js | 4.x (CDN) |
| Autenticación | Laravel Breeze | 2.x |
| Correos | Laravel Mail (SMTP) | — |
| Tipografía | Playfair Display + DM Sans | Google Fonts |

---

## 📦 Requisitos previos

- PHP **8.2** o superior con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `bcmath`
- **Composer** 2.x
- **Node.js** 18+ y **npm** 9+
- **MySQL** 8.0+
- Servidor local: **Laravel Herd**, **XAMPP**, **Laragon** o similar

---

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/dulceria-pos.git
cd dulceria-pos
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias Node

```bash
npm install
```

### 4. Configurar variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurar la base de datos

Edita `.env` con tus credenciales MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dulceria_pos
DB_USERNAME=root
DB_PASSWORD=tu_password
```

Configura también el correo para recuperación de contraseñas:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu@correo.com
MAIL_PASSWORD=tu_password
MAIL_FROM_ADDRESS=tu@correo.com
MAIL_FROM_NAME="Dulcería POS"
```

### 6. Ejecutar migraciones con seeders

```bash
php artisan migrate --seed
```

### 7. Compilar assets

```bash
npm run build
```

### 8. Levantar el servidor

```bash
php artisan serve
```

Accede en: [http://localhost:8000](http://localhost:8000)

---

## 🔑 Credenciales de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@dulceria.com | password |
| Cajero | cajero@dulceria.com | password |

> Las credenciales se crean automáticamente al ejecutar `php artisan migrate --seed`.

---

## 📁 Estructura del proyecto

```
dulceria-pos/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # CajaController, VentaController, CorteController…
│   │   ├── Middleware/         # SoloAdmin.php
│   │   └── Requests/           # Form Requests de validación
│   ├── Livewire/               # PuntoVenta.php, ProductoIndex.php
│   ├── Models/                 # Producto, Venta, CorteCaja, MovimientoCaja…
│   └── Providers/              # AppServiceProvider (locale, paginación, mail)
├── database/
│   ├── migrations/             # Esquema completo de la BD
│   └── seeders/                # Usuarios y datos de prueba
├── resources/
│   ├── views/
│   │   ├── layouts/            # app.blade.php (nav lateral)
│   │   ├── auth/               # Login, reset-password
│   │   ├── caja/               # index.blade.php
│   │   ├── cortes/             # index, show
│   │   ├── dashboard.blade.php
│   │   ├── livewire/           # punto-venta, producto-index
│   │   ├── productos/          # CRUD
│   │   ├── reportes/
│   │   ├── usuarios/
│   │   └── vendor/mail/        # Plantillas de correo personalizadas
│   └── css/                    # app.css (estilos propios, sin Tailwind en vistas)
├── routes/
│   ├── web.php
│   └── auth.php
└── lang/en/                    # Mensajes de auth y passwords en español
```

---

## 📚 Módulos del sistema

### 🛒 Punto de Venta
Componente Livewire con búsqueda en tiempo real, carrito persistente en sesión, cálculo de cambio automático y bloqueo pesimista (`lockForUpdate`) para evitar sobreventas concurrentes. Soporta pago en efectivo y tarjeta.

### 💵 Caja
Registro de ingresos y retiros de efectivo independientes de las ventas. Valida que el monto de retiro no supere el efectivo disponible en turno. El resumen muestra efectivo esperado en caja y ventas con tarjeta.

### 🧾 Cortes de turno
Cierre de caja por turno. Calcula automáticamente el total de ventas en efectivo y tarjeta, suma ingresos, resta retiros y registra el monto real contado. El administrador puede ver el historial completo; el cajero solo sus propios cortes.

### 📦 Productos
CRUD completo con filtro de stock bajo persistente en sesión. Alerta visual cuando el stock está bajo el mínimo configurado. Gestión de imagen, categoría, marca, proveedor y precio.

### 📊 Reportes
Gráficas interactivas de ventas por período (día/semana/mes) con Chart.js. Tabla detallada con filtros por fecha y método de pago.

### 🏷️ Catálogos
Gestión de categorías, marcas y proveedores. Solo accesible para administradores.

### 👥 Usuarios
Alta y edición de usuarios con asignación de rol (`admin`/`cajero`). Desactivación lógica sin borrado físico. Solo accesible para administradores.

### 🔐 Autenticación
Login y recuperación de contraseña con correo en español. La ruta `/forgot-password` redirige al login con el modal de recuperación abierto.


---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor abre un issue primero para discutir los cambios que deseas realizar.

1. Haz fork del repositorio
2. Crea una rama: `git checkout -b feature/nueva-funcionalidad`
3. Realiza tus cambios y haz commit: `git commit -m 'feat: nueva funcionalidad'`
4. Sube la rama: `git push origin feature/nueva-funcionalidad`
5. Abre un Pull Request

---


## 👨‍💻 Autor

**Victor Ramirez Mendoza**
- GitHub: [@tu-usuario](https://github.com/victormanrmz14-crypto)
- Email: victorman.rmz14@gmail.com

---

<p align="center">Hecho con ❤️ en México 🇲🇽</p>