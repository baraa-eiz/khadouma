# ADR-008: Search Abstraction

* **Title:** Decoupled Search Engine Interface
* **Status:** Approved
* **Context:** The platform targets weak hosting initially, making advanced external engines (e.g., Elasticsearch) unnecessary and expensive. However, hardcoding basic SQL queries directly in controllers prevents future search optimization.
* **Alternatives Considered:** Direct SQL query binding, external search service API.
* **Final Decision:** Define a `SearchEngineInterface` class. In V1.0, implement it via `SimpleSearchEngine` using standard SQL queries and normalized text indexing. This allows future upgrades to FULLTEXT or external search tools by swapping class bindings.
* **Consequences:**
  * Zero runtime dependencies for V1.0, minimizing hosting costs.
  * Easy future migration path without editing controllers or views.
  * Search results are initially limited to simple keyword matches rather than fuzzy matching or relevance scoring.
