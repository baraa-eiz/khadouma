# Database Notes: Khadomeh (خدومة)

This document explains the technical choices, structure, and constraints of the database design for Stage 1.

---

## 1. Engine & Character Set

* **Engine**: InnoDB.
  * Ensures support for ACID transactions.
  * Standardizes foreign key constraint checks.
* **Charset**: `utf8mb4`
  * Fully supports Arabic character encoding, including emojis and special characters.
* **Collation**: `utf8mb4_unicode_ci`
  * Offers proper Arabic sorting, search indexing, and character matching.

---

## 2. CloudPanel Naming Constraints

> [!IMPORTANT]
> **Alphanumeric Database & User Names**
> During setup, we discovered that CloudPanel's validation rules strictly require database names and database usernames to be **alphanumeric only** (no underscores or hyphens allowed).
> Therefore, we created:
> * **Database Name**: `khadomeh` (not `khadomeh_db`)
> * **Database User**: `khadomeh` (not `khadomeh_user`)

---

## 3. Important Tables & Relationships

* **Providers & Mappings**:
  * A provider has a single `city_id` and `primary_service_id`.
  * For serving multiple services or areas, the database utilizes many-to-many relationship mapping tables: `provider_service_map` and `provider_area_map`.
* **Soft Deletes**:
  * Tables like `services`, `cities`, `areas`, `providers`, `provider_images`, `reviews`, `reports`, `static_pages`, and `faq_entries` feature a nullable `deleted_at` timestamp.
  * This prevents accidental loss of data and allows administrative recovery.
* **Audit Logging**:
  * Admin logins, dashboard events, and eventually CRUD operations are logged in `audit_logs` for compliance.
  * Has a foreign key pointing to `admin_users.id` set to `ON DELETE SET NULL` to keep the logs safe even if an admin user is deleted.

---

## 4. How to Reset and Seed the Database

To completely clear all tables and re-import the schema and seed data on the VPS, you can run:

```bash
mysql -h 127.0.0.1 -u khadomeh -p'kH3d0M3h_db_p@ss_2026!' khadomeh < database/schema.sql
mysql -h 127.0.0.1 -u khadomeh -p'kH3d0M3h_db_p@ss_2026!' khadomeh < database/seed.sql
```
