# ADR-004: Controller-Service-Repository Pattern

* **Title:** Layered Architecture Boundary Design
* **Status:** Approved
* **Context:** Mixing database queries, transaction control, validation logic, and presentation markup results in brittle, unmaintainable, and hard-to-test code.
* **Alternatives Considered:** Active Record pattern, Controller-Model pattern.
* **Final Decision:** Implement the Controller-Service-Repository pattern:
  * **Controllers:** Handle HTTP requests, simple validation, and responses.
  * **Services:** Manage transaction controls, business rules, and external integrations.
  * **Repositories:** Interact with the database only, abstracting raw SQL away from business layers.
* **Consequences:**
  * Strict division of labor.
  * Easy integration of database transactions across multiple repository calls.
  * Ability to replace or decorate repositories (e.g., for caching) without modifying service logic.
  * Small increase in boilerplate classes (Controllers, Services, Repositories) for new entities.
