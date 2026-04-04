# API REST de Contactos

API REST para gestionar contactos construida en PHP 8.2 sin framework, con arquitectura en capas.

## Requisitos

- XAMPP con Apache y MySQL activos
- PHP 8.2+
- mod_rewrite habilitado en Apache

## Instalación

### 1. Crear la base de datos

```sql
source C:/xampp/htdocs/prueba/contacts-api/database/schema.sql
```

### 2. Configurar conexión

Editar `config/database.php` con los datos de conexión:

```php
return [
    'host'    => 'localhost',
    'dbname'  => 'contacts_api',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];
```

### 3. Acceder a la API

```
http://localhost/prueba/contacts-api/contacts
```

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| GET | /contacts | Listar todos los contactos |
| GET | /contacts/{id} | Obtener un contacto por ID |
| POST | /contacts | Crear un nuevo contacto |
| DELETE | /contacts/{id} | Eliminar un contacto |

## Ejemplos para Postman

### Crear contacto (POST)

URL: `http://localhost/prueba/contacts-api/contacts`
Headers: `Content-Type: application/json`
Body:

```json
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@ejemplo.com",
    "phones": [
        { "phone_number": "+1234567890", "label": "mobile" },
        { "phone_number": "+0987654321", "label": "work" }
    ]
}
```

### Response 201

```json
{
    "success": true,
    "data": {
        "id": 1,
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan@ejemplo.com",
        "phones": [
            { "id": 1, "phone_number": "+1234567890", "label": "mobile" },
            { "id": 2, "phone_number": "+0987654321", "label": "work" }
        ],
        "created_at": "2026-03-30 17:30:00",
        "updated_at": "2026-03-30 17:30:00"
    }
}
```

### Response 400

```json
{
    "success": false,
    "error": "Error de validación.",
    "details": [
        "El campo 'first_name' es requerido.",
        "El campo 'email' tiene un formato inválido."
    ]
}
```

## Arquitectura

```
Presentation   → Controller, Router, JsonResponse
Application    → ContactService, ContactValidator
Domain         → ContactDTO, PhoneDTO, Interfaces
Infrastructure → ContactRepository, Database, Config
```

```
    ┌──────────────────────┐
    │     Presentation     │  Controller / Http
    └──────────┬───────────┘
               │
    ┌──────────▼───────────┐
    │     Application      │  Services / Validator
    └──────────┬───────────┘
               │
    ┌──────────▼───────────┐
    │       Domain         │  DTO / Interfaces
    └──────────┬───────────┘
               │
    ┌──────────▼───────────┐
    │    Infrastructure    │  Repositories / Config
    └──────────────────────┘
```

## Estructura de Archivos

```
contacts-api/
├── .htaccess
├── index.php
├── autoload.php
├── router.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
└── src/
    ├── Presentation/
    │   ├── Controller/
    │   │   └── ContactController.php
    │   └── Http/
    │       ├── Router.php
    │       └── JsonResponse.php
    ├── Application/
    │   ├── Services/
    │   │   └── ContactService.php
    │   └── Validator/
    │       └── ContactValidator.php
    ├── Domain/
    │   ├── DTO/
    │   │   ├── ContactDTO.php
    │   │   └── PhoneDTO.php
    │   └── Interfaces/
    │       ├── ContactRepositoryInterface.php
    │       └── ContactValidatorInterface.php
    └── Infrastructure/
        ├── Config/
        │   └── Database.php
        └── Repositories/
            └── ContactRepository.php
```

## Tests

El proyecto utiliza PHPUnit 10 para tests unitarios.

### Instalar dependencias

```bash
php composer.phar install
```

### Ejecutar tests

```bash
php vendor/bin/phpunit --testdox
```

### Cobertura de tests

| Componente | Tests | Tipo |
|---|---|---|
| ContactValidator | 15 | Unitario |
| ContactService | 14 | Unitario (con mocks) |
| Router | 9 | Unitario |
| **Total** | **38 tests, 63 assertions** | |

