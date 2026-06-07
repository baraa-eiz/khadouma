# ADR-002: Micro-MVC Architecture

* **Title:** Micro-MVC Architecture Layer
* **Status:** Approved
* **Context:** A flat-file system where routing goes directly to view scripts (e.g., `pages/home.php`) mixes request parsing, validation, database fetching, and rendering. As CRUD operations grow, these scripts become difficult to maintain and test.
* **Alternatives Considered:** Plain scripts (flat-file routing), full MVC framework wrapper.
* **Final Decision:** Implement a micro-MVC framework where a central `Router` matches URIs to dedicated `Controller` classes and methods, which then coordinate with `Services` and return data to standard PHP template `Views`.
* **Consequences:**
  * Clean separation of concerns (presentation separated from logic).
  * Highly structured routing table.
  * Slices file complexities: templates contain only simple layout loops and secure display helpers.
  * Requires a minor learning curve for developers to map controllers and views instead of writing single-file scripts.
