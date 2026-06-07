# ADR-010: Database Normalization Balance

* **Title:** Pragmatic Denormalization for Performance
* **Status:** Approved
* **Context:** Strictly normalizing all database records (e.g., calculating ratings and review counts via dynamic table scans on every request) degrades database performance, especially under high concurrent traffic on cheap hosting.
* **Alternatives Considered:** 100% database normalization, full denormalization.
* **Final Decision:** Implement a balanced relational model:
  * Normalize many-to-many tables (`provider_service_map`, `provider_area_map`).
  * Denormalize `average_rating` and `reviews_count` directly into the `providers` table, updating these values only when a new review is changed to `approved` status.
* **Consequences:**
  * O(1) reads for provider cards and rankings without running subqueries on the `reviews` table.
  * Writes require transactional hooks to update calculated provider fields when moderation status changes.
  * Discrepancies may arise if database updates bypass the Repository layer (avoidable by enforcing code standards).
