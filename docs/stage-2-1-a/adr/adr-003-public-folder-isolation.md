# ADR-003: Public Folder Isolation

* **Title:** Web-Accessible Document Root Isolation
* **Status:** Approved
* **Context:** Exposing config, database backups, logs, and core source code files in a web-accessible directory makes the application vulnerable to credential theft, codebase leakage, or arbitrary execution if server configurations fail.
* **Alternatives Considered:** Single folder root with `.htaccess` / Nginx deny rules blocking files individually.
* **Final Decision:** Restrict the web server's Document Root exclusively to the `/public` folder. All operational directories (`/app`, `/config`, `/storage`, `/views`) are placed one level above this root, outside direct web access.
* **Consequences:**
  * Drastically reduced attack surface: core configuration and script files cannot be directly read or requested by users.
  * A single entry point (`public/index.php`) handles all requests.
  * Local virtual hosts or subfolder paths must be properly configured (e.g., in XAMPP) to target the `/public` folder instead of the root folder.
