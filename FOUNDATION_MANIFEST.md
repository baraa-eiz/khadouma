# Khadomeh Platform — Foundation Architecture Manifest (v0.3.0)

This manifest indexes the frozen components, database entities, routes, and layout configurations of the Khadomeh Platform v0.3.0 release.

---

## 1. Frozen Core Components (`app/Core/`)
These files constitute the custom micro-MVC engine of the platform. Any changes to their interfaces are considered breaking changes.

| Component Path | Class / Interface | Responsibility |
|---|---|---|
| `app/Core/Bootstrap.php` | `App\Core\Bootstrap` | Bootloader, handles environments, class autoloading, and routing. |
| `app/Core/Config.php` | `App\Core\Config` | Immutable system configuration registry. |
| `app/Core/Controller.php` | `App\Core\Controller` | Master controller providing layouts, CSRF verification, and helpers. |
| `app/Core/CSRF.php` | `App\Core\CSRF` | Security utility generating and validating request tokens. |
| `app/Core/Database.php` | `App\Core\Database` | PDO Singleton wrapper with transactional scope. |
| `app/Core/DTO.php` | `App\Core\DTO` | Typed contract for safe data transfers. |
| `app/Core/Env.php` | `App\Core\Env` | Lightweight parser for `.env` environment variables. |
| `app/Core/ErrorHandler.php` | `App\Core\ErrorHandler` | Renders human-friendly exception screens (dev) or secure logs (prod). |
| `app/Core/Flash.php` | `App\Core\Flash` | Manages temporary, session-backed user notifications. |
| `app/Core/Logger.php` | `App\Core\Logger` | Thread-safe, single-file diagnostic logger. |
| `app/Core/Request.php` | `App\Core\Request` | Captures HTTP headers, input streams, and client IPs. |
| `app/Core/Response.php` | `App\Core\Response` | Formats and sends HTTP status codes, headers, and payload. |
| `app/Core/Router.php` | `App\Core\Router` | Processes dynamic URIs and dispatches to modules. |
| `app/Core/Session.php` | `App\Core\Session` | Sets session cookie configurations and limits lifetimes. |

---

## 2. Frozen Reference Modules

### Services Module (`app/Modules/Services/`)
The Golden Reference CRUD implementation.

- **Controller**: `App\Modules\Services\ServicesController`
- **Validation**: `App\Modules\Services\ServicesValidation`
- **DTO**: `App\Modules\Services\ServiceDTO`
- **Service**: `App\Modules\Services\ServicesService`
- **Repository Contract**: `App\Modules\Services\ServicesRepositoryInterface`
- **Repository Implementation**: `App\Modules\Services\ServicesRepository`
- **Views**:
  - `list.php`: Renders pagination and uses empty state components.
  - `show.php`: Renders details and operational logs (deltas).
  - `create.php`: Form layout supporting validation arrays.
  - `edit.php`: Pre-populated form fields.

### Locations Module (`app/Modules/Locations/`)
Standardized Locations (Cities & Areas) implementation.

- **Controllers**: 
  - `App\Modules\Locations\CitiesController`
  - `App\Modules\Locations\AreasController`
- **Validation**: 
  - `App\Modules\Locations\CitiesValidation`
  - `App\Modules\Locations\AreasValidation`
- **DTOs**: 
  - `App\Modules\Locations\CityDTO`
  - `App\Modules\Locations\AreaDTO`
- **Services**: 
  - `App\Modules\Locations\CitiesService`
  - `App\Modules\Locations\AreasService`
- **Repository Contracts**: 
  - `App\Modules\Locations\CitiesRepositoryInterface`
  - `App\Modules\Locations\AreasRepositoryInterface`
- **Repository Implementations**: 
  - `App\Modules\Locations\CitiesRepository`
  - `App\Modules\Locations\AreasRepository`
- **Views**: Located in `app/Modules/Locations/Views/cities/` and `app/Modules/Locations/Views/areas/`.

---

## 3. Frozen Database Schemas & Constraints
Standard database table configurations, columns, indexes, and soft-delete fields.

### Cities Table (`cities`)
- **Key Columns**: `id` (PK), `public_id` (UUID, UNIQUE), `key` (VARCHAR, UNIQUE), `slug` (VARCHAR, UNIQUE), `display_name_ar` (VARCHAR), `governorate_ar` (VARCHAR), `sort_order` (INT), `is_active` (TINYINT), `is_deleted` (TINYINT), `deleted_at` (DATETIME).
- **SEO Columns**: `meta_title_ar`, `meta_desc_ar`, `meta_title_en`, `meta_desc_en`.

### Areas Table (`areas`)
- **Key Columns**: `id` (PK), `city_id` (FK ➔ `cities.id`), `public_id` (UUID, UNIQUE), `key` (VARCHAR), `slug` (VARCHAR), `display_name_ar` (VARCHAR), `sort_order` (INT), `is_active` (TINYINT), `is_deleted` (TINYINT), `deleted_at` (DATETIME).
- **SEO Columns**: `meta_title_ar`, `meta_desc_ar`, `meta_title_en`, `meta_desc_en`.
- **Composite Index**: `uq_area_city_slug` UNIQUE on `(city_id, slug)`.

---

## 4. Frozen Routes Mapping (`config/routes.php`)
These route entries link URIs to frozen controllers.

```php
// Admin Login Routes
Router::add('GET', 'admin/login', 'App\Controllers\AdminAuthController@showLogin');
Router::add('POST', 'admin/login', 'App\Controllers\AdminAuthController@login');
Router::add('POST', 'admin/logout', 'App\Controllers\AdminAuthController@logout');

// Admin Dashboard & Health Check
Router::add('GET', 'admin/dashboard', 'App\Controllers\DashboardController@index', ['AdminAuth']);
Router::add('GET', 'health', 'App\Controllers\HealthController@index');

// Services CRUD Routes (examples)
Router::add('GET', 'admin/services', 'App\Modules\Services\ServicesController@index', ['AdminAuth']);
Router::add('GET', 'admin/services/create', 'App\Modules\Services\ServicesController@create', ['AdminAuth']);
Router::add('POST', 'admin/services/store', 'App\Modules\Services\ServicesController@store', ['AdminAuth']);
Router::add('GET', 'admin/services/{id}', 'App\Modules\Services\ServicesController@show', ['AdminAuth']);
Router::add('GET', 'admin/services/{id}/edit', 'App\Modules\Services\ServicesController@edit', ['AdminAuth']);
Router::add('POST', 'admin/services/{id}/update', 'App\Modules\Services\ServicesController@update', ['AdminAuth']);
Router::add('POST', 'admin/services/{id}/delete', 'App\Modules\Services\ServicesController@delete', ['AdminAuth']);
Router::add('POST', 'admin/services/{id}/restore', 'App\Modules\Services\ServicesController@restore', ['AdminAuth']);

// Cities CRUD Routes (examples)
Router::add('GET', 'admin/cities', 'App\Modules\Locations\CitiesController@index', ['AdminAuth']);
Router::add('GET', 'admin/cities/create', 'App\Modules\Locations\CitiesController@create', ['AdminAuth']);
Router::add('POST', 'admin/cities/store', 'App\Modules\Locations\CitiesController@store', ['AdminAuth']);
Router::add('GET', 'admin/cities/{id}', 'App\Modules\Locations\CitiesController@show', ['AdminAuth']);
Router::add('GET', 'admin/cities/{id}/edit', 'App\Modules\Locations\CitiesController@edit', ['AdminAuth']);
Router::add('POST', 'admin/cities/{id}/update', 'App\Modules\Locations\CitiesController@update', ['AdminAuth']);
Router::add('POST', 'admin/cities/{id}/delete', 'App\Modules\Locations\CitiesController@delete', ['AdminAuth']);
Router::add('POST', 'admin/cities/{id}/restore', 'App\Modules\Locations\CitiesController@restore', ['AdminAuth']);

// Areas CRUD Routes (examples)
Router::add('GET', 'admin/areas', 'App\Modules\Locations\AreasController@index', ['AdminAuth']);
Router::add('GET', 'admin/areas/create', 'App\Modules\Locations\AreasController@create', ['AdminAuth']);
Router::add('POST', 'admin/areas/store', 'App\Modules\Locations\AreasController@store', ['AdminAuth']);
Router::add('GET', 'admin/areas/{id}', 'App\Modules\Locations\AreasController@show', ['AdminAuth']);
Router::add('GET', 'admin/areas/{id}/edit', 'App\Modules\Locations\AreasController@edit', ['AdminAuth']);
Router::add('POST', 'admin/areas/{id}/update', 'App\Modules\Locations\AreasController@update', ['AdminAuth']);
Router::add('POST', 'admin/areas/{id}/delete', 'App\Modules\Locations\AreasController@delete', ['AdminAuth']);
Router::add('POST', 'admin/areas/{id}/restore', 'App\Modules\Locations\AreasController@restore', ['AdminAuth']);
```
