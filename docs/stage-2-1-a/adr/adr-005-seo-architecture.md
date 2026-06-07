# ADR-005: SEO Architecture

* **Title:** In-Table SEO Metadata Merging
* **Status:** Approved
* **Context:** Separating SEO tags into a 1-to-1 mapping table (`seo_metadata`) adds query complexity and results in redundant JOIN operations on page loads. High performance on weak hosting requires minimizing database joins.
* **Alternatives Considered:** Separate 1-to-1 `seo_metadata` table, dynamic generation without manual edits.
* **Final Decision:** Place core SEO attributes (`meta_title`, `meta_description`) directly within primary content tables (`providers`, `services`, `cities`, `static_pages`).
* **Consequences:**
  * Faster page load times due to fewer table joins.
  * Simplified data models and insert/update routines.
  * Custom meta overrides can still be managed in the admin dashboard alongside primary data records.
