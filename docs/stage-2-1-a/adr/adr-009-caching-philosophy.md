# ADR-009: Caching Philosophy

* **Title:** Zero-Dependency Caching Strategy
* **Status:** Approved
* **Context:** Adding runtime dependencies like Redis or Memcached in V1.0 violates KISS and increases server administration overhead on cheap Syrian VPS nodes.
* **Alternatives Considered:** Redis caching, Database query-caching, File-based caching.
* **Final Decision:** Rely on PHP OPcache for compiled code performance, Nginx configuration for static assets, and a simple custom file-based cache helper for public pages.
* **Consequences:**
  * Zero external server dependencies for V1.0.
  * Easy deployment on shared hostings and XAMPP.
  * Cache invalidation is handled via simple file deletion rather than complex pub-sub mechanisms.
  * Under extremely high write-heavy traffic, disk-based cache files may experience I/O locks, which would require migrating to Redis in a later stage.
