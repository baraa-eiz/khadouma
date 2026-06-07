# 06. Database Schema Plan (خطة جداول قاعدة البيانات)

يوضح هذا المستند التصميم التفصيلي لجداول قاعدة البيانات، أنواع البيانات، القيود، والمؤشرات (Indexes) لضمان الأداء العالي.

---

## 1. الجداول الأساسية والفرعية (Table Specifications)

### 1. جدول المشرفين (`admin_users`)
* **الغرض:** إدارة حسابات مديري النظام.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `email`: VARCHAR(150) NOT NULL UNIQUE
  * `password_hash`: VARCHAR(255) NOT NULL
  * `full_name`: VARCHAR(100) NOT NULL
  * `role`: ENUM('superadmin', 'moderator') NOT NULL DEFAULT 'moderator'
  * `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
* **المؤشرات (Indexes):**
  * فريد على `email`.

### 2. جدول المحافظات (`cities`)
* **الغرض:** المحافظات السورية المغطاة.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `name_ar`: VARCHAR(100) NOT NULL
  * `slug`: VARCHAR(100) NOT NULL UNIQUE
  * `status`: TINYINT(1) NOT NULL DEFAULT 1 (1: نشط، 0: معطل)
  * `meta_title`: VARCHAR(150) NULL
  * `meta_description`: VARCHAR(255) NULL
* **المؤشرات:**
  * فريد على `slug`.

### 3. جدول المناطق والأحياء (`areas`)
* **الغرض:** الأحياء التابعة للمحافظات.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `city_id`: INT UNSIGNED NOT NULL
  * `name_ar`: VARCHAR(100) NOT NULL
  * `slug`: VARCHAR(100) NOT NULL
* **المفاتيح الخارجية والمؤشرات:**
  * مفتاح خارجي `city_id` يربط بـ `cities.id` مع `ON DELETE CASCADE`.
  * مؤشر مركب فريد `city_id` + `slug`.

### 4. جدول الحرفيين ومزودي الخدمات (`providers`)
* **الغرض:** الجدول الرئيسي لبيانات الحرفيين.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `public_id`: VARCHAR(12) NOT NULL UNIQUE (مُعرّف عشوائي مستقر للروابط العامة)
  * `full_name`: VARCHAR(150) NOT NULL
  * `slug`: VARCHAR(150) NOT NULL UNIQUE
  * `phone`: VARCHAR(20) NOT NULL
  * `whatsapp_number`: VARCHAR(20) NULL
  * `avatar_url`: VARCHAR(255) NULL
  * `bio`: TEXT NULL
  * `average_rating`: DECIMAL(3,2) NOT NULL DEFAULT 0.00
  * `reviews_count`: INT UNSIGNED NOT NULL DEFAULT 0
  * `status`: ENUM('unverified', 'pending', 'verified', 'rejected') NOT NULL DEFAULT 'unverified'
  * `meta_title`: VARCHAR(150) NULL
  * `meta_description`: VARCHAR(255) NULL
  * `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  * `updated_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  * `deleted_at`: TIMESTAMP NULL (للـ Soft Delete)
* **المؤشرات:**
  * فريد على `public_id`.
  * فريد على `slug`.
  * مؤشر على `status` لتسريع التصفية والبحث للجمهور.
  * مؤشر على `deleted_at`.

### 5. جدول الخدمات (`services`)
* **الغرض:** الخدمات المنزلية المتاحة.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `name_ar`: VARCHAR(100) NOT NULL
  * `slug`: VARCHAR(100) NOT NULL UNIQUE
  * `icon_svg`: TEXT NULL
  * `status`: TINYINT(1) NOT NULL DEFAULT 1
  * `meta_title`: VARCHAR(150) NULL
  * `meta_description`: VARCHAR(255) NULL
* **المؤشرات:**
  * فريد على `slug`.

### 6. جدول ربط الحرفي بالخدمات (`provider_service_map`)
* **الأعمدة:**
  * `provider_id`: INT UNSIGNED NOT NULL
  * `service_id`: INT UNSIGNED NOT NULL
  * `is_primary`: TINYINT(1) NOT NULL DEFAULT 0
* **القيود والمؤشرات:**
  * مفتاح أساسي مركب `provider_id` + `service_id`.
  * مفتاح خارجي `provider_id` يربط بـ `providers.id` مع `ON DELETE CASCADE`.
  * مفتاح خارجي `service_id` يربط بـ `services.id` مع `ON DELETE CASCADE`.

### 7. جدول ربط الحرفي بالمناطق التغطية (`provider_area_map`)
* **الأعمدة:**
  * `provider_id`: INT UNSIGNED NOT NULL
  * `area_id`: INT UNSIGNED NOT NULL
* **القيود والمؤشرات:**
  * مفتاح أساسي مركب `provider_id` + `area_id`.
  * مفتاح خارجي `provider_id` يربط بـ `providers.id` مع `ON DELETE CASCADE`.
  * مفتاح خارجي `area_id` يربط بـ `areas.id` مع `ON DELETE CASCADE`.

### 8. جدول التقييمات والمراجعات (`reviews`)
* **الغرض:** تقييمات العملاء المقبولة والجديدة.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `provider_id`: INT UNSIGNED NOT NULL
  * `client_name`: VARCHAR(100) NOT NULL
  * `rating`: TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5)
  * `comment_text`: TEXT NULL
  * `status`: ENUM('pending', 'approved', 'spam', 'rejected') NOT NULL DEFAULT 'pending'
  * `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
* **المؤشرات:**
  * مفتاح خارجي `provider_id` يربط بـ `providers.id` مع `ON DELETE CASCADE`.
  * مؤشر مركب `provider_id` + `status` لتسريع عمليات حساب المتوسط.

### 9. جدول وثائق الحرفيين المؤمنة (`secure_documents`)
* **الغرض:** وثائق الهوية والشهادات الحساسة.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `provider_id`: INT UNSIGNED NOT NULL
  * `file_name`: VARCHAR(255) NOT NULL
  * `file_path`: VARCHAR(255) NOT NULL
  * `document_type`: VARCHAR(50) NOT NULL (e.g., 'national_id', 'certificate')
  * `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
* **القيود:**
  * مفتاح خارجي `provider_id` يربط بـ `providers.id` مع `ON DELETE CASCADE`.

### 10. جدول أحداث الاتصالات ومعدل الفائدة (`contact_events`)
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `provider_id`: INT UNSIGNED NOT NULL
  * `city_id`: INT UNSIGNED NOT NULL
  * `service_id`: INT UNSIGNED NOT NULL
  * `action_type`: ENUM('phone_call', 'whatsapp_click') NOT NULL
  * `user_ip`: VARCHAR(45) NULL
  * `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
* **المؤشرات:**
  * مؤشر على `provider_id` و `created_at` لبناء تقارير الفائدة والنشاط لاحقاً.

### 11. جدول الروابط القديمة والمعدلة (`old_slugs`)
* **الغرض:** تتبع الـ slugs القديمة للحرفيين لإجراء إعادة توجيه تلقائي (301) ومنع كسر محركات البحث.
* **الأعمدة:**
  * `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  * `old_slug`: VARCHAR(150) NOT NULL UNIQUE
  * `entity_type`: VARCHAR(50) NOT NULL (e.g., 'provider')
  * `entity_id`: INT UNSIGNED NOT NULL
  * `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
* **المؤشرات:**
  * فريد على `old_slug`.
