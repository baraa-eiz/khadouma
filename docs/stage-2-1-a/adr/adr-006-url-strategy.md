# ADR-006: URL Strategy

* **Title:** Hierarchical SEO URLs with Public IDs
* **Status:** Approved
* **Context:** Exposing database primary keys (e.g., `/provider/42`) invites automated data scraping, exposes system IDs, and lacks SEO context. Relying purely on names (e.g., `/damascus/plumbing/abu-ahmad`) causes collisions for common names.
* **Alternatives Considered:** Raw IDs in URLs, purely name-based slugs, hash-based URLs.
* **Final Decision:** Use hierarchical, SEO-friendly structures that append a unique, stable alphanumeric public ID to the name slug:
  * Pattern: `/{citySlug}/{serviceSlug}/{providerSlug}-{publicId}`
  * Example: `/damascus/plumbing/abu-ahmad-p8kf2`
* **Consequences:**
  * Prevent database key exposure and easy data scraping.
  * O(1) database queries (querying directly by public ID).
  * High SEO optimization by including city, category, and name keywords in the path.
  * Router must parse routes with dynamic slugs, handling old slug lookup redirects if names change.
