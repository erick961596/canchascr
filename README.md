# SuperCancha — SaaS de Gestión de Canchas Deportivas

## Stack
- **Laravel 11** (PHP 8.2+)
- **MySQL 8+**
- **Bootstrap 5** + Chart.js + FullCalendar 6 + Leaflet.js
- **AWS S3** para imágenes y comprobantes
- **Mailtrap** para emails (desarrollo)
- **ONVO Pay** para suscripciones con tarjeta
- **Google OAuth** via Socialite
- **SweetAlert2** + Axios para UI reactiva

---

## Instalación paso a paso

### 1. Requisitos previos
- PHP 8.2+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer 2
- MySQL 8+

### 2. Descomprimir y configurar
```bash
cd supercancha
cp .env.example .env
composer install
php artisan key:generate
```

### 3. Completar el .env
```
APP_URL=http://localhost:8000

DB_DATABASE=supercancha
DB_USERNAME=root
DB_PASSWORD=tu_password

# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=supercancha-prod

# Mailtrap (copiar credenciales desde mailtrap.io)
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

# Google OAuth (console.cloud.google.com)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# ONVO Pay
ONVO_PUBLIC_KEY=
ONVO_SECRET_KEY=
ONVO_WEBHOOK_SECRET=
```

### 4. Base de datos y seeders
```bash
# Crear la base de datos en MySQL
mysql -u root -p -e "CREATE DATABASE supercancha CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Copiar el archivo de provincias/cantones/distritos
cp /ruta/a/tu/data.json database/data.json

# Correr migraciones + seeders
php artisan migrate --seed
```

### 5. Iniciar
```bash
php artisan serve
# Acceder en http://localhost:8000
```

---

## Credenciales iniciales

| Rol   | Email                 | Password |
|-------|-----------------------|----------|
| Admin | admin@supercancha.com | admin123 |

> Después del primer login como admin, cambiá la contraseña desde el panel.

---

## Rutas del sistema

| Panel       | URL              | Middleware             |
|-------------|------------------|------------------------|
| Login       | `/login`         | guest                  |
| Registro    | `/registro`      | guest                  |
| Owner signup| `/registro-sede` | guest                  |
| Player App  | `/app`           | auth + role:user       |
| Owner Panel | `/owner`         | auth + role:owner      |
| Admin Panel | `/admin`         | auth + role:admin      |
| Webhook ONVO| `/webhooks/onvo` | sin CSRF               |

---

## Flujo de suscripción (Owner)

```
Registro owner → /owner/suscripcion → Elegir plan
  ├── Tarjeta (ONVO)  → Pago inmediato → Webhook activa suscripción
  └── SINPE           → Sube comprobante → Admin aprueba → Suscripción activa
```

## Flujo de reserva (Player)

```
Explorar canchas → Ver cancha → Elegir fecha → Elegir slot
  → Crear reserva (status: pending)
  → Subir comprobante SINPE
  → Owner verifica → Confirma reserva
  → Email de confirmación al usuario
```

---

## AWS S3 - Estructura de carpetas

```
bucket/
├── venues/
│   ├── logos/
│   └── images/
├── courts/
│   └── images/{venue_id}/
├── reservations/
│   └── proofs/{reservation_id}/
└── subscriptions/
    └── proofs/{user_id}/
```

---

## WhatsApp (futuro)

El sistema tiene una tabla `notification_logs` y un `NotificationService` preparado.
Para activar WhatsApp con Twilio, solo agregar en `.env`:
```
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```
Y extender `NotificationService::sendWhatsApp()`.

---

## Usuarios de prueba (seeders)

Solo se crean si `APP_ENV=local` (el default después de `laravel new`).

| Rol    | Email               | Password  |
|--------|---------------------|-----------|
| Admin  | admin@supercancha.com | admin123 |
| Owner  | owner1@test.com     | password  |
| Owner  | owner2@test.com     | password  |
| Owner  | owner3@test.com     | password  |
| Player | player1@test.com    | password  |
| Player | player2@test.com    | password  |

### Datos incluidos en los seeders de prueba
- 3 sedes con 2-3 canchas cada una (fútbol, tenis, pádel, volleyball, básquetbol)
- Horarios Mon-Sun 07:00-22:00 en todas las canchas
- ~25 reservas con estados: confirmed, pending, cancelled
- 1 pago SINPE pendiente para aprobar en `/admin/pagos-pendientes`
- Suscripciones activas para cada owner

### Correr solo los seeders de prueba (sin resetear todo)
```bash
php artisan db:seed --class=TestUserSeeder
php artisan db:seed --class=TestVenueSeeder
php artisan db:seed --class=TestReservationSeeder
```

---

## Gestión de planes (Admin)

El admin puede crear/editar/desactivar planes desde `/admin/planes`.

Los planes activos son los que aparecen en la página de suscripción de los owners.
Si eliminás un plan con suscripciones activas, el sistema lo rechaza.
