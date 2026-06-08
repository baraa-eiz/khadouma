# Khadomeh Admin Platform Documentation

The Khadomeh Admin Platform is a reusable, lightweight, configuration-driven dashboard shell tailored for Damascus/Syrian local directory management operations. It aligns with the "premium warm light" design aesthetic, utilizing semantic CSS variables without heavy dependencies or CSS frameworks.

---

## 1. Directory Structure

```
├── config/
│   ├── app.php              # App constants (includes admin session timeout config)
│   ├── routes.php           # Admin route mappings (login, logout, dashboard)
│   └── navigation.php       # Configuration-driven sidebar menu items
├── app/
│   ├── Middleware/
│   │   ├── AdminAuth.php    # Session validity and inactivity timeouts
│   │   └── AdminGuest.php   # Redirects active sessions away from auth screens
│   ├── Repositories/
│   │   └── AdminRepository.php # admin_users database table controller
│   └── Services/
│       └── AdminService.php # Business logic, password verification, audit logging
├── views/
│   ├── components/          # Reusable design system UI component widgets
│   ├── layouts/
│   │   └── admin.php        # Unified sidebar/header master shell page
│   └── admin/
│       ├── login.php        # Access authentication page view
│       └── dashboard.php    # Overview system status index view
└── public/
    └── assets/
        ├── css/admin.css    # Core layout styles, variables, cards, forms, buttons
        └── js/admin.js      # Actions, alert dismissals, sidebar responsiveness
```

---

## 2. Shared UI Component Library

The platform includes six native components located in `views/components/` that can be included in any view template using raw PHP `include`:

| Component | Path | Description / Usage |
| :--- | :--- | :--- |
| **Breadcrumbs** | `views/components/breadcrumb.php` | Renders a trail. Expects `$breadcrumbs = [['label' => 'الرئيسية', 'url' => '/path'], ['label' => 'الفرعي']]`. |
| **Flash Alerts** | `views/components/flash.php` | Display dismissible state changes (`success`, `error`, `warning`, `info`) read from sessions. |
| **Confirm Dialog** | `views/components/confirm_dialog.php` | Intercepts elements with `data-confirm` attribute, showing a clean alert before form submission. |
| **Empty State** | `views/components/empty_state.php` | A neat graphic when lists are empty. Variables: `$empty_title`, `$empty_desc`, `$empty_action_url`, `$empty_action_label`. |
| **Loading Skeleton** | `views/components/loading_state.php` | Clean pulse animation block representing data list loader. |
| **Pagination** | `views/components/pagination.php` | Renders paginated selector controls. Variables: `$current_page`, `$total_pages`, `$total_records`, `$per_page`, `$base_url`. |

---

## 3. Extending the Platform with new CRUD Modules

To build a new CRUD module (e.g. "Services" or "Providers") that inherits this infrastructure, follow these three steps:

### Step A: Configure Navigation
Open `config/navigation.php` and map your new sub-URL. For example, updating the services item:
```php
    [
        'key' => 'services',
        'label' => 'الخدمات',
        'icon' => 'briefcase',
        'url' => '/admin/services',
        'active_pattern' => '/admin/services*'
    ],
```

### Step B: Create a Protected Route
In `config/routes.php`, register the new route and apply `App\Middleware\AdminAuth` middleware:
```php
$router->get('/admin/services', [App\Controllers\Admin\ServicesController::class, 'index'], [App\Middleware\AdminAuth::class]);
```

### Step C: Build Controller and View
In your controller, fetch data, render your view, and wrap it inside the `layouts/admin` layout:
```php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class ServicesController extends Controller
{
    public function index(Request $request): Response
    {
        $services = $this->serviceRepo->all();
        
        $content = View::render('admin/services/index', [
            'services' => $services
        ]);
        
        return $this->render('layouts/admin', [
            'title' => 'إدارة الخدمات',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => '/admin/dashboard'],
                ['label' => 'الخدمات']
            ]
        ]);
    }
}
```

In your view `views/admin/services/index.php`, use standard design system classes from `admin.css` (e.g. `.card`, `.table`, `.btn`, `.form-control`):
```html
<div class="section-header">
    <h1 class="section-title">إدارة الخدمات</h1>
    <a href="/admin/services/create" class="btn btn-primary">إضافة خدمة جديدة</a>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>اسم الخدمة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= e($service['name']) ?></td>
                        <td><span class="badge badge-success">نشط</span></td>
                        <td>
                            <a href="/admin/services/<?= $service['id'] ?>/edit" class="btn btn-secondary">تعديل</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
```
