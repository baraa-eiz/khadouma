# Implementation Plan: Admin Platform (Authentication + Shell + Design System)

This plan outlines the architecture, components, and files required to implement the reusable shared Admin Platform for **Khadomeh (خدومة)**. Once approved, this will serve as the frozen visual and logical foundation that all subsequent business modules (Providers, Services, Cities, etc.) will inherit without modification.

---

## User Review Required

> [!IMPORTANT]
> **No External CSS Frameworks**: The styling will be custom-built using CSS variables in `/public/assets/css/admin.css` to align with the *Apple/Stripe/Linear/GitHub* light theme design language. No Tailwind, Bootstrap, or AdminLTE libraries will be used.
>
> **CSRF Validation on Logout**: Logout will be a POST-only action to prevent CSRF logout exploits.
>
> **Implicit Session Timeouts**: We will track timeouts (`30 minutes`) utilizing a last activity timestamp in the Session layer instead of relying solely on server PHP session cookie settings, ensuring consistent behavior across host configurations.

---

## Open Questions

> [!NOTE]
> **No open questions**: The requirements are fully detailed, and the database schema already supports `admin_users` table structures.

---

## Proposed Changes

### Component 1: Routing & Configuration
Configure system-wide constants, navigation registries, and admin endpoints.

#### [MODIFY] [app.php](file:///C:/Users/pc/Desktop/service/config/app.php)
- Add `admin.session_timeout` configuration (default to `1800` seconds / 30 minutes).

#### [MODIFY] [routes.php](file:///C:/Users/pc/Desktop/service/config/routes.php)
- Register GET and POST routes for `/admin/login`.
- Register POST route for `/admin/logout`.
- Register GET route for `/admin/dashboard`.

#### [NEW] [navigation.php](file:///C:/Users/pc/Desktop/service/config/navigation.php)
- Return an array of sidebar navigation links (Dashboard, Services, Cities, Areas, Providers, Reviews, Reports, Settings) matching URLs, active path patterns, and icons.

---

### Component 2: Middleware Layer
Route filtering and access controls.

#### [NEW] [AdminAuth.php](file:///C:/Users/pc/Desktop/service/app/Middleware/AdminAuth.php)
- Protects pages; checks if user is logged in, validates session timeouts, updates last activity time, and redirects to `/admin/login` on failure.

#### [NEW] [AdminGuest.php](file:///C:/Users/pc/Desktop/service/app/Middleware/AdminGuest.php)
- Prevents authenticated users from viewing the login page; redirects them to `/admin/dashboard`.

---

### Component 3: Data Access & Business Logic (MVC)
Database queries and business operations for admin authentication.

#### [NEW] [AdminRepository.php](file:///C:/Users/pc/Desktop/service/app/Repositories/AdminRepository.php)
- Implements `RepositoryInterface` to find admins by email, update last login timestamps, and retrieve user information.

#### [NEW] [AdminService.php](file:///C:/Users/pc/Desktop/service/app/Services/AdminService.php)
- Authenticates credentials via `password_verify` against `admin_users`, log details, and handle login metrics.

---

### Component 4: Controllers
HTTP handlers and health integration.

#### [NEW] [AuthController.php](file:///C:/Users/pc/Desktop/service/app/Controllers/Admin/AuthController.php)
- Displays login form view, performs post validation, sets session parameters, handles flash alerts, and destroys sessions upon logout.

#### [NEW] [DashboardController.php](file:///C:/Users/pc/Desktop/service/app/Controllers/Admin/DashboardController.php)
- Renders the basic dashboard page container populated with environment and application versions.

#### [MODIFY] [HealthController.php](file:///C:/Users/pc/Desktop/service/app/Controllers/HealthController.php)
- Expand dashboard list to include checks for:
  - Authentication status (verifies database `admin_users` seed availability)
  - Session status (verifies session state and configuration parameters)
  - Middleware registration (verifies presence of `AdminAuth` and `AdminGuest`)
  - Layout loading (verifies `admin.php` layout availability)
  - Component registration (verifies UI library files existence)

---

### Component 5: Reusable Views & Layouts
The structural design templates.

#### [NEW] [admin.php](file:///C:/Users/pc/Desktop/service/views/layouts/admin.php)
- Base HTML5 shell supporting responsive sidebar, dynamic breadcrumbs, notification slot, content viewport, and footer.

#### [NEW] [login.php](file:///C:/Users/pc/Desktop/service/views/admin/login.php)
- Clean, central login box showing logo, inputs, error lists, and submission trigger.

#### [NEW] [dashboard.php](file:///C:/Users/pc/Desktop/service/views/admin/dashboard.php)
- Displays general system metadata, health check shortcut, and fast path actions.

---

### Component 6: UI Component Library
Reusable layout blocks and design fragments.

#### [NEW] [breadcrumb.php](file:///C:/Users/pc/Desktop/service/views/components/breadcrumb.php)
- Renders dynamic navigation trail based on current URI.

#### [NEW] [flash.php](file:///C:/Users/pc/Desktop/service/views/components/flash.php)
- Renders success/error/warning alerts from session storage.

#### [NEW] [confirm_dialog.php](file:///C:/Users/pc/Desktop/service/views/components/confirm_dialog.php)
- Clean overlay dialog for validating delete actions.

#### [NEW] [empty_state.php](file:///C:/Users/pc/Desktop/service/views/components/empty_state.php)
- Reusable empty tables/views graphic with prompt actions.

#### [NEW] [loading_state.php](file:///C:/Users/pc/Desktop/service/views/components/loading_state.php)
- Clean SVG skeleton placeholder representing tables/cards loading.

#### [NEW] [pagination.php](file:///C:/Users/pc/Desktop/service/views/components/pagination.php)
- Navigation bar for paged tabular views.

---

### Component 7: Assets & Theme Styles
Custom visual styles.

#### [NEW] [admin.css](file:///C:/Users/pc/Desktop/service/public/assets/css/admin.css)
- Unified CSS rules: layout, buttons, forms, tables, card borders, and responsive styling.

#### [NEW] [admin.js](file:///C:/Users/pc/Desktop/service/public/assets/js/admin.js)
- Micro JavaScript logic (confirm overlay control, alerts auto-dismiss, sidebar responsive toggler).

---

### Component 8: Documentation

#### [NEW] [admin_platform.md](file:///C:/Users/pc/Desktop/service/docs/admin_platform.md)
- Walkthrough for developers showing how to add pages, register sidebar items, make forms/tables, use component styles, and protect endpoints.

---

## Verification Plan

### Automated/CLI Tests
1. **Local Integration Smoke Test**: Run local test scripts to ensure all classes compile and load successfully.
2. **VPS Remote Verification**: Run `test_smoke.php` on the VPS to verify files, database access, and directory parameters.

### Manual Verification
1. **Login Test**: Visit `/admin/login`, submit empty, wrong, and correct credentials (`admin@khadomeh.local` / `Admin@123456`). Verify error prompts and dashboard access.
2. **Session Timeout**: Log in, wait or manually set session timestamp back by 30 minutes, confirm auto-redirect to login with "انتهت الجلسة" message.
3. **Health Dashboard**: Access `/health` in development mode, ensuring all 5 new diagnostic checks report **PASS**.
4. **Responsive Check**: Scale browser size to confirm layout transitions to tablet and mobile sidebar view cleanly.
