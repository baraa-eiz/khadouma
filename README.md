# منصة خدومة | Khadomeh Platform
### Enterprise-Grade Native PHP Micro-MVC Architecture

Khadomeh is a high-performance, lightweight web application and service catalog mediator designed to connect homeowners and businesses with local home maintenance and service providers (cleaners, plumbers, electricians, painters, and carpenters) in Syria.

This repository implements a bespoke **Native PHP Micro-MVC Framework** engineered from scratch to guarantee sub-millisecond execution, complete data security, and compliance with the **Controller ➔ Service ➔ Repository ➔ DTO ➔ View** design pattern. It features an Apple-inspired administrative dashboard design system and a "Golden Reference" Services CRUD module.

---

## 🚀 Key Framework Features

*   **Native PHP Core (PHP 8.1+)**: Zero bulky external dependencies, ensuring maximum speed, security, and portability.
*   **Structured Micro-MVC Pattern**: Absolute decoupling of routing, controllers, transactional business logic (Services), database transactions (Repositories), strong data modeling (DTOs), input parsing (Validation), and visual templates (Views).
*   **Production Security System**: Out-of-the-box CSRF token validation, SQL Injection prevention via strict PDO parameter binding, XSS mitigation through output escaping helpers, and IP-hashed administrative action tracking.
*   **Apple/Stripe Aesthetics**: A clean, warm-light, high-speed administrative interface utilizing vanilla CSS/JS and built-in CSS typography variables.
*   **Arabic Text Normalization**: Automated matching filters via normalization utilities (unifying character shapes, removing diacritics, and sanitizing Arabic input fields).
*   **Complete Audit Trail Logs**: Track data alterations with automated delta comparisons. The show views feature an interactive delta diff engine that displays old vs. new values using `<del>` and `<ins>` tags.
*   **Zero-Loss Soft Deletion**: Compliance with soft-deletion standards (`is_deleted` and `deleted_at` timestamps) across tables to preserve analytical database continuity.

---

## 📂 Project Structure

```text
/khadomeh
├── app/                              # Core Application Logic
│   ├── Core/                         # Micro-MVC Framework Engine
│   │   ├── Bootstrap.php             # System bootloader (env, paths, routes)
│   │   ├── Config.php                # Immutable Configuration loader
│   │   ├── Controller.php            # Base controller (views rendering, CSRF, redirects)
│   │   ├── CSRF.php                  # Cryptographic CSRF token manager
│   │   ├── Database.php              # Singleton PDO wrapper supporting Transactions
│   │   ├── DTO.php                   # Data Transfer Object template
│   │   ├── Env.php                   # Secure environment (.env) parser
│   │   ├── ErrorHandler.php          # Custom exception catcher (Prod vs Dev modes)
│   │   ├── Flash.php                 # Temporary session notification state
│   │   ├── Logger.php                # PSR-compliant file logging utility
│   │   ├── Request.php               # Unified HTTP request wrapper
│   │   ├── Response.php              # HTTP response compiler (headers, body, status)
│   │   ├── Router.php                # Route registration and dispatching engine
│   │   └── Session.php               # Secure Session lifecycle manager
│   ├── Helpers/                      # Universal helper functions (Arabic normalizer, slugs, URLs)
│   ├── Middleware/                   # Request Interceptors
│   │   └── AdminAuth.php             # Route access guard ensuring active admin session
│   ├── Modules/                      # Business Modules
│   │   ├── Services/                 # Services Module (Golden CRUD Reference)
│   │   │   ├── Routes.php            # Services routes registration
│   │   │   ├── ServicesController.php# Handles request parsing & redirect logic
│   │   │   ├── ServicesService.php   # Business rules, audit logs & transactions
│   │   │   ├── ServicesRepositoryInterface.php # Data access contract
│   │   │   ├── ServicesRepository.php# Dynamic sql building & PDO binding
│   │   │   ├── ServiceDTO.php        # Sanitized DTO model
│   │   │   ├── ServicesValidation.php# Trim, character check & Arabic normalization
│   │   │   └── Views/                # Front-end templates (list, show, create, edit)
│   │   └── Locations/                # Locations Module (Cities & Areas CRUD)
│   │       ├── Routes.php            # Locations routes registration
│   │       ├── CitiesController.php  # Handles request parsing & redirect logic for Cities
│   │       ├── AreasController.php   # Handles request parsing & redirect logic for Areas
│   │       ├── CitiesService.php     # Business rules, audit logs & transactions for Cities
│   │       ├── AreasService.php      # Business rules, audit logs & transactions for Areas
│   │       ├── CitiesRepositoryInterface.php # Cities data access contract
│   │       ├── AreasRepositoryInterface.php # Areas data access contract
│   │       ├── CitiesRepository.php  # Dynamic sql building & PDO binding for Cities
│   │       ├── AreasRepository.php   # Dynamic sql building & PDO binding for Areas
│   │       ├── CityDTO.php           # Sanitized DTO model for Cities
│   │       ├── AreaDTO.php           # Sanitized DTO model for Areas
│   │       ├── CitiesValidation.php  # Trim, character check & Arabic normalization for Cities
│   │       ├── AreasValidation.php   # Trim, character check & Arabic normalization for Areas
│   │       └── Views/                # Front-end templates for Cities and Areas
│   └── Repositories/                 # Database repositories wrapper (legacy compatibility)
├── config/                           # System Configurations (app.php, database.php, navigation.php)
├── database/                         # Database Migration & Seeds
│   ├── migrations/                   # Sequential schema updates
│   ├── schema.sql                    # Production tables schema
│   └── seed.sql                      # Demo seed data (Admin credentials, services, cities, areas)
├── docs/                             # In-depth Technical Documentation
├── public/                           # Web-Accessible Document Root
│   ├── assets/                       # Static UI Assets
│   │   ├── css/                      # Custom admin design system stylesheet
│   │   └── js/                       # Interactive AJAX, CSRF, and Modal scripts
│   ├── uploads-public/               # Uploaded files directory (excluded from repository)
│   └── index.php                     # Front Controller Entry Point
├── storage/                          # Non-accessible file storage (logs, cache, sessions)
├── test_smoke.php                    # Framework component verification script
├── test_services_crud.php            # Automated Services CRUD Integration test suite
└── test_locations_crud.php           # Automated Locations CRUD Integration test suite
```

---

## 🛠️ Tech Stack & Database Architecture

*   **Back-End**: PHP 8.1+ (Native OOP)
*   **Database**: MySQL / MariaDB (InnoDB engine, `utf8mb4_unicode_ci` collation)
*   **Front-End**: Native HTML5, Vanilla CSS3 (Custom design tokens), Native ES6 JavaScript
*   **Production Deployment**: Nginx + PHP-FPM, environment variables managed via `.env` file outside the public web root.

---

## 🚀 Setup & Execution Guide

### 1. Database Setup
Create a MySQL database and run the schema and seed files:
```bash
mysql -u root -p database_name < database/schema.sql
mysql -u root -p database_name < database/seed.sql
```

### 2. Configure Environment Properties
Create a `.env` file in the root folder of the project (copy from `.env.example`):
```ini
APP_NAME="خدومة"
APP_ENV=development  # Change to production on live server
APP_URL=http://localhost:8000
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=khadomeh
DB_USER=root
DB_PASS=your_password
```

### 3. Run Web Server
Serve the application using the PHP built-in server from the `public` directory:
```bash
php -S localhost:8000 -t public/
```

Access the Administration dashboard via: `http://localhost:8000/admin/login`

### 4. Admin Access Credentials
*   **Email**: `admin@khadomeh.local`
*   **Password**: `Admin@123456`

---

## 🧪 Automated Testing Suite

To ensure framework stability and prevent regression, the project includes three automated test suites:

1.  **Smoke Tests** (Verifies core class loading, database transactions, cache, file storage, and session lifecycles):
    ```bash
    php test_smoke.php
    ```
2.  **Services CRUD Integration Tests** (Validates the validation pipeline, DTO integrity, transactional service layers, soft-deletion, restoration, and audit logging for Services):
    ```bash
    php test_services_crud.php
    ```
3.  **Locations CRUD Integration Tests** (Validates the validation pipeline, city-scoped area slug uniqueness, DTO integrity, transactional service layers, soft-deletion, restoration, and audit logging for Cities and Areas):
    ```bash
    php test_locations_crud.php
    ```ion, the project includes two automated test suites:

1.  **Smoke Tests** (Verifies core class loading, database transactions, cache, file storage, and session lifecycles):
    ```bash
    php test_smoke.php
    ```
2.  **Services CRUD Integration Tests** (Validates the validation pipeline, DTO integrity, transactional service layers, soft-deletion, restoration, and audit logging):
    ```bash
    php test_services_crud.php
    ```

---

## 🔒 Security Compliance

*   **CSRF Protection**: Non-GET routes validate unique session tokens generated per-request.
*   **SQL Injection Prevention**: Active variables are bound using PHP PDO parameters.
*   **Output Sanitization**: Double escaping guards all strings before rendering in views.
*   **Admin Guard**: Unauthorized requests targeting administrative directories are routed back to the login gateway.
