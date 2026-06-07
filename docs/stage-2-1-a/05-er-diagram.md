# 05. Entity-Relationship (ER) Diagram

يوضح هذا المخطط العلاقات البينية لجداول قاعدة البيانات، ومفاتيح الربط الأساسية والثنائية لضمان سلامة البيانات المرجعية.

---

## 1. مخطط الكيانات والعلاقات (Mermaid ER Diagram)

```mermaid
erDiagram
    cities ||--o{ areas : "contains"
    cities {
        int id PK
        string name_ar
        string slug
        tinyint status
    }

    areas {
        int id PK
        int city_id FK
        string name_ar
        string slug
    }

    providers ||--o{ provider_gallery : "owns public pictures"
    providers ||--o{ secure_documents : "uploads identity documents"
    providers ||--o{ reviews : "receives"
    providers ||--o{ reports : "reported by"
    providers ||--o{ contact_events : "tracked by"
    
    providers {
        int id PK
        string public_id UK
        string full_name
        string slug UK
        string phone
        string whatsapp_number
        string avatar_url
        string bio
        decimal average_rating
        int reviews_count
        string status
        string meta_title
        string meta_description
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    services ||--o{ provider_service_map : "maps to"
    providers ||--o{ provider_service_map : "maps to"
    services {
        int id PK
        string name_ar
        string slug UK
        string icon_svg
        tinyint status
    }
    provider_service_map {
        int provider_id PK, FK
        int service_id PK, FK
        tinyint is_primary
    }

    areas ||--o{ provider_area_map : "covers"
    providers ||--o{ provider_area_map : "covers"
    provider_area_map {
        int provider_id PK, FK
        int area_id PK, FK
    }

    provider_gallery {
        int id PK
        int provider_id FK
        string file_name
        string file_path
        int file_size
        timestamp created_at
    }

    secure_documents {
        int id PK
        int provider_id FK
        string file_name
        string file_path
        string document_type
        timestamp created_at
    }

    reviews {
        int id PK
        int provider_id FK
        string client_name
        int rating
        string comment_text
        string status
        timestamp created_at
    }

    reports {
        int id PK
        int provider_id FK
        string reporter_name
        string reporter_phone
        string reason
        string details
        string status
        timestamp created_at
    }

    contact_events {
        int id PK
        int provider_id FK
        int city_id FK
        int service_id FK
        string action_type
        string user_ip
        timestamp created_at
    }

    admin_users {
        int id PK
        string email UK
        string password_hash
        string full_name
        string role
        timestamp created_at
    }

    audit_logs {
        int id PK
        int admin_id FK
        string action
        string entity_type
        int entity_id
        text before_value
        text after_value
        timestamp created_at
    }

    settings {
        int id PK
        string setting_key UK
        text setting_value
        timestamp updated_at
    }

    old_slugs {
        int id PK
        string old_slug UK
        string entity_type
        int entity_id
        timestamp created_at
    }
```

---

## 2. قواعد السلامة المرجعية (Referential Integrity Rules)

* **الربط الحاسم (Strict Cascades):** يتم مسح السجلات الوسيطة في جداول الخرائط (`provider_service_map`, `provider_area_map`) تلقائياً في حال حذف الحرفي أو الخدمة/المنطقة.
* **الحذف الناعم (Soft Delete):** الحرفيون لا يتم حذفهم فعلياً من قاعدة البيانات عند تفعيل خيار الحذف، بل يتم تعيين الحقل `deleted_at`. بالتالي، لا يتم كسر العلاقات المرجعية التاريخية في جداول الأحداث والمراجعات (`contact_events`, `reviews`).
* **فصل وثائق الأمان:** وثائق الهوية والشهادات (`secure_documents`) تنعزل تماماً في جدول خاص بها لمنع الخلط بينها وبين المعرض العام للصور ولتسهيل منح أو حجب الصلاحيات البرمجية على هذا الجدول.
