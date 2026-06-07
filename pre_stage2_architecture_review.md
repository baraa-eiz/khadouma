# Pre-Stage 2 Architecture Review: Khadomeh Platform

This document presents the technical architecture review and design recommendations before proceeding with the Stage 2 (CRUD) implementation.

---

## QUESTION 1: Internal Architecture Layer
We should introduce a **lightweight Controller layer** (micro-MVC structure). 

**Why:**
Currently, the `Router` resolves requests directly to view files (e.g., `pages/home.php`), which makes these scripts act as both controllers and templates. As Stage 2 CRUD and form handling are introduced, these files will quickly descend into spaghetti code with interleaved PHP logic, SQL maps, and HTML.

**Description:**
* **Router Upgrade:** Allow the router to resolve to a controller class and action method (e.g., `['HomeController', 'index']`).
* **Controllers:** Standard PHP classes under the `App\Controllers` namespace. They accept request data, instantiate repositories, perform validation, and pass clean data arrays to a rendering engine.
* **Views:** Pure template scripts containing only HTML, CSS, and basic output loops (using `e()`), completely stripped of database queries or processing logic.

---

## QUESTION 2: Database Table Optimization

| Current Table | Recommendation | Reason |
| :--- | :--- | :--- |
| `seo_metadata` | **Remove / Merge** | Storing SEO tags in a separate 1-to-1 table requires complex JOINs on every page load. Merging these as `meta_title` and `meta_description` columns directly into `services`, `cities`, and `static_pages` simplifies queries and increases indexing efficiency. |
| `provider_images` | **Rename / Partition** | Sensitive verification documents (ID cards, licenses) and public gallery images are mixed in a single table. Partition this data or rename/split it to prevent public enumeration of private files. |
| `provider_service_map` | **Add Column / Modify** | Add an `is_primary` boolean column to the mapping table and remove `primary_service_id` from `providers`. This normalizes service mapping and eases future migrations to multi-category providers. |

---

## QUESTION 3: Folder Structure Modification

**Current Structure:**
```text
/
├── admin/
├── app/
├── config/
├── includes/
├── pages/
├── public/
└── index.php
```

**Suggested Change:**
Expose only the `public/` folder to the web server's Document Root. Move all operational PHP code files outside the public directory.
```text
/
├── config/
├── database/
├── src/ (or app/)
│   ├── Controllers/
│   ├── Repositories/
│   └── Views/
└── public/  <-- Document Root
    ├── assets/
    └── index.php
```

**Reason:**
Prevents direct URL access and unauthorized execution of standalone PHP files (e.g., `/includes/auth.php` or `/config/database.php`) if the web server configuration is accidentally altered or bypassed.

---

## QUESTION 4: Missing High-Value Provider Fields
Adding these fields later would require costly data backfills and schema rewrites:
1. **`verification_status`** (`ENUM('unverified', 'pending_verification', 'verified', 'rejected')`): Essential for Stage 4's registration workflow.
2. **`latitude` / `longitude`** (`DECIMAL(10, 8)` / `DECIMAL(11, 8)`): Crucial for proximity searches. Radius-based filtering cannot be derived from a simple text area mapping later.
3. **`availability_state`** (`TINYINT` / `ENUM`): Toggle switch for providers to set themselves as active, busy, or offline to prevent stale search results.

---

## QUESTION 5: URL Canonical Strategy
Keep the hierarchical structure: `/damascus/plumbing/abu-ahmad` but append a unique identifier: **`/damascus/plumbing/abu-ahmad-p109`**.

**Why:**
Relying entirely on name slugs (`abu-ahmad`) leads to lookup collisions in database searches for popular names. Appending the ID allows O(1) query matching, simplifies route resolution, and prevents SEO links from breaking if a provider alters their display name.

---

## QUESTION 6: Media Architecture Optimization
* **Storage Separation:** Store public gallery files in `/public/uploads/` partitioned by date (`/uploads/gallery/YYYY/MM/`). Store sensitive documents in a private folder (e.g., `/app/storage/secure_uploads/`) served only via authentication guards.
* **Format:** Force WebP conversion on upload. Generate standard and thumbnail versions (`_thumb.webp`) asynchronously or at upload time to prevent dynamic image resizing from draining VPS CPU resources.
* **Drafts:** Use a `/public/uploads/tmp/` directory for incomplete wizard uploads, cleared daily via cron.

---

## QUESTION 7: Search Architecture for V1.0
Use **MySQL FULLTEXT indexing** on normalized columns alongside the custom `normalize_arabic()` utility.

**Why:**
Simple SQL `LIKE '%term%'` queries cannot use standard indexes, triggering slow full table scans that degrade performance on low-spec hosting. FULLTEXT indexes provide fast, indexed searching without the memory overhead of external search engines like Elasticsearch.

---

## QUESTION 8: High Volume Scalability (500K Users)
The current architecture can survive this scale with these minimal tweaks:
1. **PHP OPcache:** Enable OPcache on the VPS to cache precompiled bytecode in memory.
2. **Reverse Proxy Cache:** Configure Nginx to cache static landing pages (e.g., `/damascus/plumbing`) for 1 hour, offloading database reads for anonymous traffic.
3. **Write Offloading:** Run database writes (such as tracking impressions in `contact_events`) using buffered writes or Redis, avoiding direct disk writes on every click.

---

## QUESTION 9: Top 10 Architectural Risks
1. **Logic Pollution (Direct Views):** Embedding repository logic directly in presentation templates leads to maintenance bottlenecks.
2. **Web-Accessible Core Directories:** Keeping configuration, system scripts, and credentials in folders open to direct browser execution.
3. **Public Verification Uploads:** Storing ID cards and private licenses in web-accessible asset directories.
4. **Lack of Environment Variable Abstraction:** Hardcoding environment variables in `config.php` makes CI/CD and production staging error-prone.
5. **No Database Transactions:** Performing multi-table inserts (e.g., provider registration across three tables) without transactions, leading to orphan records.
6. **Global Function Pollution:** Global helper naming collisions (e.g., `e()`) with third-party libraries or future PHP versions.
7. **Single VPS Session State:** Using standard file sessions prevents future horizontal scaling.
8. **Manual Database Syncs:** Lack of migration tooling leads to inconsistencies between local XAMPP and VPS production databases.
9. **No Rate Limiting:** Exposing public reviews, searches, and SMS configurations to automated spam scripts.
10. **Synchronous Image Processing:** Resizing high-resolution images synchronously during requests triggers PHP timeouts.

---

## QUESTION 10: Standard Patterns Violations
1. **SOLID (Single Responsibility Principle):** View templates handling both HTTP parameters and HTML generation.
2. **SOLID (Open/Closed Principle):** The core `Router` needs modification to support dynamic path parameters.
3. **KISS/YAGNI Violation:** The separate `seo_metadata` table adds unnecessary complexity to the database schema.
4. **Maintainability Violation:** Global helpers lack namespaces, hindering unit testing and code safety.

---

## FINAL ASSESSMENTS

### Scores
* **Architecture Score:** 7/10
* **Database Score:** 8/10
* **Performance Score:** 9/10
* **Maintainability Score:** 6/10
* **Simplicity Score:** 9/10
* **Scalability Score:** 6/10

### Top 5 Recommendations
1. **Introduce a Controller Layer:** Separate page routing and business logic from HTML views before implementing Stage 2 CRUD.
2. **Isolate the Document Root:** Move source files (`app/`, `config/`, `includes/`) outside of the public web root; expose only the `/public` directory.
3. **Secure Verification Uploads:** Store sensitive ID photos in a private, non-web-accessible folder served only via authenticated PHP scripts.
4. **Flatten SEO Data:** Drop the `seo_metadata` table and merge its fields directly into the primary content tables.
5. **Use Environment Files:** Replace environment-switching conditions in `config.php` with a standard `.env` configuration pattern.
