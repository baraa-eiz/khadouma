# ADR-001: Native PHP Selection

* **Title:** Native PHP Implementation
* **Status:** Approved
* **Context:** The application is targeting Damascus/Syria. The local infrastructure features weak hosting packages and slow internet speeds. High dependency overhead from heavy PHP frameworks (such as Laravel or Symfony) introduces overhead (routing, dependency injection, heavy vendors) that significantly degrades latency and memory usage.
* **Alternatives Considered:** Laravel, Symfony, Slim Framework.
* **Final Decision:** Use Native PHP (version 8.x) without any heavy external frameworks. Focus on object-oriented programming (OOP), standard PHP namespaces, and autoloading.
* **Consequences:** 
  * Extreme performance gains (latency under 50ms).
  * Very small memory footprint (less than 2MB per request).
  * Full developer control over request flow.
  * Increased responsibility to implement routing, database wrappers, validation, and security features manually.
