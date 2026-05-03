# 🔐 Auth Service - Microservicio de Autenticación

## 📋 Descripción

Microservicio Laravel dedicado a la autenticación y gestión de usuarios con JWT (JSON Web Tokens). Proporciona endpoints seguros para registro, login, refresh de tokens y logout.

## 🏗️ Arquitectura

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend     │◄──►│  Auth Service   │◄──►│  PostgreSQL    │
│   React/TS     │ JWT │   (Laravel)    │ SQL │   Supabase     │
│   Port: 5173    │    │   Port: 8000    │    │   Port: 5432    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 📂 Estructura del Proyecto

```
auth-service/
├── 📁 app/
│   ├── Http/
│   │   ├── Controllers/V1/
│   │   │   └── AuthController.php     # Endpoints de autenticación
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php       # Validación de login
│   │   │   ├── RegisterRequest.php    # Validación de registro
│   │   │   └── LogoutRequest.php       # Validación de logout
│   │   └── Middleware/
│   │       └── JwtMiddleware.php     # Middleware de autenticación
│   ├── Services/
│   │   └── AuthService.php          # Lógica de negocio JWT
│   ├── Models/
│   │   └── User.php                 # Modelo de usuario
│   └── Repositories/
│       └── UserRepository.php       # Acceso a datos de usuarios
│
├── 📁 database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   └── 2026_05_01_140016_create_refresh_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
│
├── 📁 config/
│   └── hashing.php                 # Configuración de encriptación
│
├── 📄 .env.supabase              # Configuración de base de datos
├── 📄 composer.json              # Dependencias PHP
└── 📄 README.md                  # Este archivo
```

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 11** - Framework PHP moderno
- **PostgreSQL** - Base de datos via Supabase
- **JWT** - Autenticación sin estado
- **Bcrypt** - Encriptación de contraseñas
- **Eloquent ORM** - Mapeo objeto-relacional

### Seguridad
- **JWT con Refresh Tokens** - Tokens de acceso y renovación
- **Bcrypt Hashing** - Encriptación segura de contraseñas
- **Middleware JWT** - Protección de rutas
- **Validaciones Robustas** - Input sanitization

## 📡 API Endpoints

### Autenticación Pública
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/v1/register` | Registrar nuevo usuario |
| `POST` | `/api/v1/login` | Iniciar sesión |
| `POST` | `/api/v1/refresh` | Renovar access token |

### Autenticación Privada
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/v1/logout` | Cerrar sesión |
| `GET` | `/api/v1/profile` | Obtener perfil del usuario |

## 🔧 Configuración del Entorno

### Variables de Entorno Requeridas

```env
# Configuración de la Aplicación
APP_NAME="Pieces Auth Service"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:GENERAR_NUEVA_APP_KEY

# Base de Datos - Supabase PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=tu-proyecto.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=tu-password-de-supabase

# Configuración JWT
JWT_SECRET=tu-jwt-secret-de-32-caracteres-minimo

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Archivo de Configuración

```bash
# Copiar configuración de Supabase
cp .env.supabase .env

# Editar con tus credenciales reales
nano .env
```

## 🚀 Instalación y Ejecución

### Prerrequisitos
- **PHP 8.2+**
- **Composer**
- **PostgreSQL** (via Supabase)
- **Cuenta Supabase** activa

### Instalación

```bash
# 1. Instalar dependencias
composer install

# 2. Generar APP_KEY
php artisan key:generate

# 3. Configurar entorno
cp .env.supabase .env
# EDITAR .env con credenciales de Supabase

# 4. Migrar base de datos
php artisan migrate:fresh --seed

# 5. Iniciar servicio
php artisan serve --port=8000
```

### Verificación

```bash
# Verificar conexión a BD
php artisan tinker
DB::connection()->getPdo()  # Debe retornar conexión exitosa

# Verificar usuario creado
User::count()  # Debe retornar > 0
```

## 📊 Base de Datos

### Tablas Principales

#### users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### refresh_tokens
```sql
CREATE TABLE refresh_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🔐 Flujo de Autenticación

### 1. Registro de Usuario
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "password": "contraseña123",
  "password_confirmation": "contraseña123"
}
```

**Respuesta**:
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 2,
      "name": "Juan Pérez",
      "email": "juan@ejemplo.com",
      "created_at": "2024-01-01T12:00:00.000000Z"
    },
    "access_token": "eyJ...",
    "refresh_token": "abc...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### 2. Login
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "juan@ejemplo.com",
  "password": "contraseña123"
}
```

### 3. Refresh Token
```http
POST /api/v1/refresh
Content-Type: application/json

{
  "refresh_token": "abc..."
}
```

### 4. Logout
```http
POST /api/v1/logout
Content-Type: application/json

{
  "refresh_token": "abc..."
}
```

## 🛡️ Seguridad Implementada

### Encriptación de Contraseñas
- **Algoritmo**: Bcrypt
- **Cost Factor**: 12 rounds
- **Formato**: `$2y$12$N9qo8uLOickgx2ZMRZoMy...`

### JWT Tokens
- **Access Token**: 1 hora de validez
- **Refresh Token**: 30 días de validez
- **Algoritmo**: HMAC SHA-256
- **Secret**: Compartido entre servicios

### Validaciones
- **Email**: Formato válido y único
- **Password**: Mínimo 8 caracteres
- **Confirmación**: Password debe coincidir

## 🧪 Testing

### Ejecutar Tests
```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter AuthServiceTest
```

### Tests Implementados
- ✅ Registro de usuario
- ✅ Login con credenciales válidas
- ✅ Login con credenciales inválidas
- ✅ Refresh token válido
- ✅ Refresh token inválido
- ✅ Logout exitoso
- ✅ Validación de inputs

## 📝 Logs y Debugging

### Ver Logs en Tiempo Real
```bash
tail -f storage/logs/laravel.log
```

### Logs de Autenticación
```php
// Logs generados automáticamente
AuthService::login - Attempt started
AuthService::login - User found
AuthService::login - Hash check result
AuthService::login - Authentication successful
```

## 🔄 Integración con Otros Servicios

### Configuración para Pieces Service
```env
# En pieces-service/.env
JWT_SECRET=mismo-secret-que-auth-service
```

### Middleware para Proteger Rutas
```php
// En routes/api.php
Route::middleware('jwt')->group(function () {
    Route::get('/protected-endpoint', [Controller::class, 'method']);
});
```

## 🚨 Manejo de Errores

### Códigos de Error Comunes
- **400**: Error de validación o lógica de negocio
- **401**: No autenticado o token inválido
- **404**: Recurso no encontrado
- **422**: Error de validación de inputs
- **500**: Error interno del servidor

### Ejemplos de Respuestas de Error
```json
{
  "success": false,
  "message": "Los datos de acceso son incorrectos",
  "errors": {
    "email": ["El correo electrónico ya está registrado"]
  }
}
```

## 📈 Monitoreo y Performance

### Métricas Importantes
- **Tiempo de respuesta**: < 200ms para login
- **Tasa de éxito**: > 95% para operaciones válidas
- **Uso de memoria**: < 64MB por request
- **Conexiones BD**: Pool de 10-20 conexiones

### Optimizaciones
- **Índices en BD**: `users.email`, `refresh_tokens.token`
- **Cache de configuración**: `php artisan config:cache`
- **Queries optimizadas**: Eager loading donde sea necesario

## 🚀 Despliegue

### Producción
```bash
# Optimizar para producción
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Variables de entorno producción
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

### Docker (Opcional)
```dockerfile
FROM php:8.2-fpm
WORKDIR /var/www
COPY . .
RUN composer install --no-dev
RUN php artisan config:cache
EXPOSE 9000
CMD ["php-fpm"]
```

## 🔧 Mantenimiento

### Tareas Comunes
```bash
# Limpiar cache
php artisan cache:clear

# Verificar estado de migraciones
php artisan migrate:status

# Respaldar base de datos
php artisan db:dump --database=postgresql

# Verificar colas
php artisan queue:monitor
```

## 🚨 Soporte y Troubleshooting

### Problemas Comunes

#### Error: "Connection refused"
```bash
# Verificar que el servicio esté corriendo
php artisan serve --port=8000

# Verificar que el puerto esté libre
netstat -an | grep 8000
```

#### Error: "Database connection failed"
```bash
# Verificar conexión a BD
php artisan tinker
DB::connection()->getPdo()

# Verificar variables de entorno
php artisan env
```

#### Error: "JWT token invalid"
```bash
# Verificar JWT_SECRET compartido
grep JWT_SECRET .env

# Verificar formato del token
# Debe ser: "Bearer eyJ..."
```

## 📄 Licencia

MIT License - Uso libre con atribución.

---

**Auth Service** es el corazón del sistema de autenticación, proporcionando seguridad y escalabilidad para toda la aplicación Pieces Management System.

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
