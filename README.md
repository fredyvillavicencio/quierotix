# Sistema de Gestión de Tickets con QR

Sistema completo para la gestión de eventos, asignación de asientos, generación de PDFs con códigos QR, validación en tiempo real y envío de emails.

## Características Principales

### ✅ Funcionalidades Implementadas

1. **Gestión de Eventos**
   - CRUD completo de eventos
   - Configuración de fechas, horarios y códigos únicos
   - Mapa de asientos personalizable

2. **Gestión de Participantes**
   - Importación masiva desde archivos CSV
   - Asignación automática de asientos
   - Exportación de datos
   - Plantilla CSV para descarga

3. **Sistema de Asientos**
   - Algoritmo inteligente de asignación
   - 5 bloques con 7 asientos cada uno
   - 20 filas (A a S)
   - Total: 700 asientos disponibles
   - Asignación secuencial por orden de registro

4. **Generación de PDFs con QR**
   - 7 PDFs por participante (uno por asiento)
   - Códigos QR únicos para cada ticket
   - Diseño profesional con información del evento
   - Almacenamiento en sistema de archivos
   - Generación masiva por evento

5. **Validación QR en Tiempo Real**
   - Interfaz móvil optimizada
   - Validación con sonidos de confirmación
   - Prevención de validaciones duplicadas
   - Estadísticas de validación
   - Historial de escaneos

6. **Sistema de Emails**
   - Integración con Brevo (Sendinblue)
   - Envío masivo de tickets por email
   - Adjunto de PDFs automático
   - Plantillas HTML personalizadas
   - Seguimiento de envíos

7. **Panel de Administración**
   - Dashboard con estadísticas
   - CRUD para todas las entidades
   - Sistema de roles (admin/usuario)
   - Interfaz responsive con Bootstrap 5
   - Acciones rápidas

8. **Seguridad y Autenticación**
   - Sistema de login con Laravel Breeze
   - Middleware para administradores
   - Protección de rutas
   - Usuarios de prueba preconfigurados

## Requisitos del Sistema

- PHP 8.1 o superior
- MySQL 5.7 o superior
- Composer 2.0 o superior
- Node.js y NPM (opcional, para assets)
- Extensión GD para PHP (para QR codes)
- Extensión ZIP para PHP

## Instalación Paso a Paso

### 1. Clonar y Configurar el Proyecto

```bash
# Clonar el proyecto
git clone [url-del-repositorio] tickets-system
cd tickets-system

# Instalar dependencias de PHP
composer install

# Instalar dependencias de JavaScript (opcional)
npm install
npm run build

# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 2. Configurar Base de Datos

Editar el archivo `.env` con tus credenciales:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tickets
DB_USERNAME=usuario
DB_PASSWORD=fred
```

Crear la base de datos:

```bash
mysql -u usuario -pfred -e "CREATE DATABASE IF NOT EXISTS tickets;"
```

### 3. Ejecutar Migraciones y Seeders

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (crea usuarios de prueba)
php artisan db:seed
```

### 4. Configurar Almacenamiento

```bash
# Crear enlaces simbólicos para almacenamiento
php artisan storage:link

# Configurar permisos (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

### 5. Configurar Email (Opcional)

Para usar Brevo (Sendinblue), agregar al `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=tu-usuario
MAIL_PASSWORD=tu-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Sistema de Tickets"

# Configuración de Brevo
BREVO_API_KEY=tu-api-key-de-brevo
```

### 6. Iniciar el Servidor

```bash
# Servidor de desarrollo
php artisan serve

# O con host y puerto específicos
php artisan serve --host=127.0.0.1 --port=8000
```

## Usuarios de Prueba

El sistema incluye usuarios preconfigurados:

### Administrador
- **Email:** admin@tickets.com
- **Contraseña:** admin123
- **Acceso:** Panel completo de administración

### Usuario Regular
- **Email:** user@tickets.com
- **Contraseña:** user123
- **Acceso:** Dashboard básico

## Estructura del Proyecto

```
tickets-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/          # Controladores de administración
│   │   └── Middleware/         # Middleware personalizados
│   ├── Models/                 # Modelos Eloquent
│   └── Services/              # Servicios de negocio
│       ├── SeatAssignmentService.php
│       ├── CsvImportService.php
│       ├── PdfGenerationService.php
│       ├── EmailService.php
│       └── QrValidationService.php
├── database/
│   ├── migrations/            # Migraciones de base de datos
│   └── seeders/              # Seeders para datos de prueba
├── resources/
│   └── views/
│       ├── admin/            # Vistas de administración
│       ├── pdfs/             # Plantillas para PDFs
│       └── qr-validation.blade.php
├── storage/
│   └── app/
│       ├── pdfs/             # PDFs generados
│       └── zips/             # Archivos ZIP
└── public/
    └── storage -> ../storage/app/public
```

## Flujo de Trabajo Típico

### 1. Crear un Evento
1. Iniciar sesión como administrador
2. Ir a "Eventos" → "Nuevo Evento"
3. Completar información del evento
4. Guardar y activar el evento

### 2. Importar Participantes
1. Seleccionar el evento creado
2. Ir a "Importar CSV"
3. Descargar plantilla CSV
4. Llenar con datos de participantes
5. Subir archivo CSV
6. Verificar importación

### 3. Generar PDFs
1. En la vista del evento, hacer clic en "Generar PDFs"
2. El sistema generará 7 PDFs por participante
3. Los PDFs se almacenan en `storage/app/pdfs/`
4. Se pueden descargar individualmente o en ZIP

### 4. Enviar Emails
1. Verificar que los PDFs estén generados
2. Hacer clic en "Enviar Emails"
3. El sistema enviará los tickets a cada participante
4. Verificar estado de envíos en el panel

### 5. Validar Tickets en el Evento
1. Acceder a `/qr-validation` desde un dispositivo móvil
2. Escanear código QR del ticket
3. El sistema validará y registrará la entrada
4. Sonidos de confirmación para feedback auditivo

## Configuración de Asientos

El sistema está configurado para:
- **Filas:** A a S (19 filas)
- **Asientos por fila:** 35
- **Bloques:** 5 bloques
- **Asientos por bloque:** 7
- **Total asientos:** 665 (19 × 35)

### Distribución de Bloques:
- Bloque 1: Asientos 1-7
- Bloque 2: Asientos 8-14
- Bloque 3: Asientos 15-21
- Bloque 4: Asientos 22-28
- Bloque 5: Asientos 29-35

## API Endpoints

### Validación QR
```
GET /tickets/validate/{codigo_qr}
```

### Panel de Administración
```
GET /admin/dashboard
GET /admin/events
GET /admin/participants
GET /admin/tickets
```

### Generación de PDFs
```
POST /admin/events/{id}/generate-pdfs
```

### Envío de Emails
```
POST /admin/events/{id}/send-emails
```

## Solución de Problemas

### Error 500 al iniciar
```bash
# Generar clave de aplicación
php artisan key:generate

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Problemas con PDFs
```bash
# Instalar dependencias de PDF
composer require barryvdh/laravel-dompdf

# Verificar permisos de almacenamiento
chmod -R 775 storage
```

### Problemas con QR Codes
```bash
# Instalar dependencias de QR
composer require simplesoftwareio/simple-qrcode

# Verificar extensión GD
php -m | grep gd
```

### Problemas de Base de Datos
```bash
# Recrear base de datos
php artisan migrate:fresh --seed

# Verificar conexión
php artisan db:show
```

## Personalización

### Cambiar Configuración de Asientos
Editar `app/Services/SeatAssignmentService.php`:
- `ROWS`: Cambiar letras de filas
- `SEATS_PER_ROW`: Cambiar número de asientos
- `BLOCKS`: Cambiar configuración de bloques

### Cambiar Diseño de PDFs
Editar `resources/views/pdfs/ticket.blade.php`

### Cambiar Plantilla de Emails
Editar `app/Services/EmailService.php` métodos:
- `getEmailHtmlContent()`
- `getEmailTextContent()`

## Seguridad

### Recomendaciones de Producción
1. Cambiar credenciales por defecto
2. Usar HTTPS en producción
3. Configurar firewall adecuado
4. Realizar backups regulares
5. Monitorear logs de aplicación

### Variables de Entorno Sensibles
- `APP_KEY`
- `DB_PASSWORD`
- `BREVO_API_KEY`
- `MAIL_PASSWORD`

## Licencia

Este proyecto está bajo la licencia MIT. Ver archivo `LICENSE` para más detalles.

## Soporte

Para reportar problemas o solicitar características:
1. Revisar la sección de Solución de Problemas
2. Verificar logs en `storage/logs/laravel.log`
3. Crear un issue en el repositorio

## Créditos

Desarrollado con:
- Laravel 10
- Bootstrap 5
- DomPDF
- Simple QRCode
- Brevo API

---

**Nota:** Este es un sistema de demostración. Para uso en producción, se recomienda realizar pruebas exhaustivas y configurar medidas de seguridad adicionales.