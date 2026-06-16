-- Programmatically generated SQLite Schema

CREATE TABLE IF NOT EXISTS "schema_versions" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "migration_name" VARCHAR(255) NOT NULL UNIQUE,
  "executed_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "admin_users" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "name" VARCHAR(100) NOT NULL,
  "email" VARCHAR(150) NOT NULL UNIQUE,
  "password_hash" VARCHAR(255) NOT NULL,
  "role" VARCHAR(50) NOT NULL DEFAULT 'admin', -- 'superadmin', 'admin', 'moderator',
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "last_login_at" TEXT NULL DEFAULT NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "settings" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "setting_key" VARCHAR(100) NOT NULL UNIQUE,
  "setting_value" TEXT NULL,
  "setting_type" VARCHAR(50) NOT NULL DEFAULT 'text', -- 'text', 'textarea', 'boolean', 'number',
  "label_ar" VARCHAR(255) NOT NULL,
  "description_ar" TEXT NULL,
  "is_public" INTEGER NOT NULL DEFAULT 1,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "services" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "public_id" VARCHAR(36) NOT NULL UNIQUE,
  "key" VARCHAR(100) NOT NULL UNIQUE,
  "slug" VARCHAR(100) NOT NULL UNIQUE,
  "display_name_ar" VARCHAR(150) NOT NULL,
  "short_name_ar" VARCHAR(50) NOT NULL,
  "description_ar" TEXT NULL,
  "icon" VARCHAR(100) NULL,
  "sort_order" INTEGER NOT NULL DEFAULT 0,
  "meta_title_ar" VARCHAR(255) NULL,
  "meta_description_ar" TEXT NULL,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "is_deleted" INTEGER NOT NULL DEFAULT 0,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "cities" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "public_id" VARCHAR(36) NOT NULL UNIQUE,
  "key" VARCHAR(100) NOT NULL UNIQUE,
  "slug" VARCHAR(100) NOT NULL UNIQUE,
  "display_name_ar" VARCHAR(150) NOT NULL,
  "display_name_en" VARCHAR(150) NULL,
  "governorate_ar" VARCHAR(100) NULL,
  "sort_order" INTEGER NOT NULL DEFAULT 0,
  "meta_title_ar" VARCHAR(255) NULL,
  "meta_description_ar" TEXT NULL,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "is_deleted" INTEGER NOT NULL DEFAULT 0,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "areas" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "public_id" VARCHAR(36) NOT NULL UNIQUE,
  "city_id" INTEGER NOT NULL,
  "key" VARCHAR(100) NOT NULL,
  "slug" VARCHAR(100) NOT NULL,
  "display_name_ar" VARCHAR(150) NOT NULL,
  "display_name_en" VARCHAR(150) NULL,
  "sort_order" INTEGER NOT NULL DEFAULT 0,
  "meta_title_ar" VARCHAR(255) NULL,
  "meta_description_ar" TEXT NULL,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "is_deleted" INTEGER NOT NULL DEFAULT 0,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "providers" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "slug" VARCHAR(150) NOT NULL UNIQUE,
  "display_name_ar" VARCHAR(200) NOT NULL,
  "normalized_name" VARCHAR(200) NOT NULL, -- Name normalized without diacritics for searching,
  "business_type" VARCHAR(50) NOT NULL DEFAULT 'individual', -- 'individual', 'company',
  "phone" VARCHAR(30) NOT NULL,
  "whatsapp" VARCHAR(30) NULL,
  "email" VARCHAR(150) NULL,
  "city_id" INTEGER NOT NULL,
  "primary_service_id" INTEGER NOT NULL,
  "short_description_ar" VARCHAR(255) NULL,
  "description_ar" TEXT NULL,
  "years_experience" INTEGER NOT NULL DEFAULT 0,
  "starting_price" REAL NULL,
  "price_unit" VARCHAR(50) DEFAULT 'hour', -- 'hour', 'job', 'day',
  "rating" REAL NOT NULL DEFAULT 0.00,
  "reviews_count" INTEGER NOT NULL DEFAULT 0,
  "verified" INTEGER NOT NULL DEFAULT 0,
  "verification_status" VARCHAR(50) NOT NULL DEFAULT 'unverified',
  "verification_document_path" VARCHAR(255) NULL,
  "verification_rejection_reason" TEXT NULL,
  "phone_verified" INTEGER NOT NULL DEFAULT 0,
  "identity_verified" INTEGER NOT NULL DEFAULT 0,
  "has_work_photos" INTEGER NOT NULL DEFAULT 0,
  "available_today" INTEGER NOT NULL DEFAULT 1,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "is_featured" INTEGER NOT NULL DEFAULT 0,
  "sort_weight" INTEGER NOT NULL DEFAULT 0, -- Higher weight means shows up higher in lists,
  "platform_score" INTEGER NOT NULL DEFAULT 0,
  "status" VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected', 'suspended',
  "website" VARCHAR(255) NULL,
  "working_hours" VARCHAR(255) NULL,
  "social_links" TEXT NULL, -- JSON string for social accounts,
  "notes_internal" TEXT NULL, -- Internal notes visible to admins only,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "provider_service_map" (
  "provider_id" INTEGER NOT NULL,
  "service_id" INTEGER NOT NULL,
  PRIMARY KEY ("provider_id", "service_id")
);

CREATE TABLE IF NOT EXISTS "provider_area_map" (
  "provider_id" INTEGER NOT NULL,
  "area_id" INTEGER NOT NULL,
  PRIMARY KEY ("provider_id", "area_id")
);

CREATE TABLE IF NOT EXISTS "provider_images" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "provider_id" INTEGER NOT NULL,
  "image_path" VARCHAR(255) NOT NULL, -- Relative path in filesystem,
  "thumbnail_path" VARCHAR(255) NULL,
  "alt_text_ar" VARCHAR(255) NULL,
  "caption_ar" VARCHAR(255) NULL,
  "image_type" VARCHAR(50) NOT NULL DEFAULT 'work_photo', -- 'profile', 'work_photo', 'verification',
  "source_type" VARCHAR(50) NOT NULL DEFAULT 'uploaded', -- 'uploaded', 'licenced_stock',
  "source_url" VARCHAR(255) NULL, -- For stock image attribution,
  "attribution_text" VARCHAR(255) NULL,
  "license_note" VARCHAR(255) NULL,
  "sort_order" INTEGER NOT NULL DEFAULT 0,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "reviews" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "provider_id" INTEGER NOT NULL,
  "reviewer_name" VARCHAR(100) NOT NULL,
  "reviewer_phone" VARCHAR(30) NULL,
  "rating" INTEGER NOT NULL, -- 1 to 5 stars,
  "service_id" INTEGER NULL, -- The specific service reviewed,
  "comment" TEXT NOT NULL,
  "status" VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected',
  "is_approved" INTEGER NOT NULL DEFAULT 0, -- Duplicate representation for fast queries,
  "ip_hash" VARCHAR(64) NULL,
  "user_agent_hash" VARCHAR(64) NULL,
  "admin_notes" TEXT NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "reports" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "provider_id" INTEGER NOT NULL,
  "reporter_name" VARCHAR(100) NOT NULL,
  "reporter_phone" VARCHAR(30) NOT NULL,
  "reason" VARCHAR(255) NOT NULL, -- Short reason category,
  "details" TEXT NOT NULL,
  "status" VARCHAR(50) NOT NULL DEFAULT 'open', -- 'open', 'investigating', 'resolved', 'dismissed',
  "admin_notes" TEXT NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "contact_events" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "provider_id" INTEGER NOT NULL,
  "service_id" INTEGER NULL,
  "city_id" INTEGER NULL,
  "area_id" INTEGER NULL,
  "method" VARCHAR(50) NOT NULL, -- 'phone_call', 'whatsapp_message',
  "source_page" VARCHAR(150) NULL, -- 'provider_profile', 'search_results',
  "user_ip_hash" CHAR(64) NULL, -- Privacy-safe IP hashing (SHA-256),
  "user_agent_hash" CHAR(64) NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "static_pages" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "slug" VARCHAR(100) NOT NULL UNIQUE,
  "title_ar" VARCHAR(255) NOT NULL,
  "content_ar" LONGTEXT NOT NULL,
  "meta_title_ar" VARCHAR(255) NULL,
  "meta_description_ar" TEXT NULL,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "faq_entries" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "service_id" INTEGER NULL, -- Optional mapping to service pages,
  "city_id" INTEGER NULL, -- Optional mapping to city landing pages,
  "area_id" INTEGER NULL, -- Optional mapping to area landing pages,
  "question_ar" TEXT NOT NULL,
  "answer_ar" TEXT NOT NULL,
  "sort_order" INTEGER NOT NULL DEFAULT 0,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP ,
  "deleted_at" TEXT NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "trust_badges" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "key_name" VARCHAR(100) NOT NULL UNIQUE,
  "label_ar" VARCHAR(150) NOT NULL,
  "description_ar" TEXT NULL,
  "icon_key" VARCHAR(100) NULL,
  "sort_order" INTEGER NOT NULL DEFAULT 0,
  "is_active" INTEGER NOT NULL DEFAULT 1,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "provider_trust_badge_map" (
  "provider_id" INTEGER NOT NULL,
  "trust_badge_id" INTEGER NOT NULL,
  PRIMARY KEY ("provider_id", "trust_badge_id")
);

CREATE TABLE IF NOT EXISTS "seo_metadata" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "entity_type" VARCHAR(50) NOT NULL, -- 'service', 'city', 'area', 'provider', 'static_page', 'general',
  "entity_id" INTEGER NULL, -- NULL for general pages like Home,
  "canonical_url" VARCHAR(255) NULL,
  "meta_title_ar" VARCHAR(255) NOT NULL,
  "meta_description_ar" TEXT NOT NULL,
  "og_title_ar" VARCHAR(255) NULL,
  "og_description_ar" TEXT NULL,
  "og_image_path" VARCHAR(255) NULL,
  "structured_data_json" TEXT NULL, -- JSON-LD schema,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "audit_logs" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "admin_user_id" INTEGER NULL, -- NULL if system action or login failure,
  "action" VARCHAR(100) NOT NULL, -- 'login_success', 'login_failed', 'create_provider', etc.,
  "entity_type" VARCHAR(50) NULL, -- 'providers', 'reviews', etc.,
  "entity_id" INTEGER NULL,
  "old_value_json" TEXT NULL,
  "new_value_json" TEXT NULL,
  "ip_hash" CHAR(64) NOT NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "provider_accounts" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "email" VARCHAR(150) NOT NULL UNIQUE,
  "google_id" VARCHAR(255) NULL,
  "display_name" VARCHAR(150) NOT NULL,
  "avatar_url" VARCHAR(255) NULL,
  "provider_id" INTEGER NULL, -- NULL until link to providers table is established,
  "status" VARCHAR(50) NOT NULL DEFAULT 'active', -- 'active', 'suspended',
  "last_login_at" TEXT NULL DEFAULT NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "provider_drafts" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "provider_id" INTEGER NULL, -- NULL for new registrations,
  "provider_account_id" INTEGER NOT NULL,
  "display_name_ar" VARCHAR(200) NULL,
  "slug" VARCHAR(150) NULL,
  "business_type" VARCHAR(50) NULL DEFAULT 'individual',
  "phone" VARCHAR(30) NULL,
  "whatsapp" VARCHAR(30) NULL,
  "email" VARCHAR(150) NULL,
  "city_id" INTEGER NULL,
  "primary_service_id" INTEGER NULL,
  "short_description_ar" VARCHAR(255) NULL,
  "description_ar" TEXT NULL,
  "years_experience" INTEGER NULL DEFAULT 0,
  "starting_price" REAL NULL,
  "price_unit" VARCHAR(50) NULL DEFAULT 'hour',
  "website" VARCHAR(255) NULL,
  "working_hours" VARCHAR(255) NULL,
  "social_links" TEXT NULL, -- JSON formatted social URLs,
  "logo_path" VARCHAR(255) NULL,
  "work_photos_json" TEXT NULL, -- JSON formatted array of paths,
  "secondary_services_json" TEXT NULL, -- JSON formatted array of service IDs,
  "coverage_areas_json" TEXT NULL, -- JSON formatted array of area IDs,
  "meta_title_ar" VARCHAR(255) NULL,
  "meta_description_ar" TEXT NULL,
  "status" VARCHAR(50) NOT NULL DEFAULT 'draft', -- 'draft', 'pending_review', 'approved', 'rejected',
  "admin_notes" TEXT NULL,
  "created_at" TEXT DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TEXT DEFAULT CURRENT_TIMESTAMP
);
