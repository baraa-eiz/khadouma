# ADR-007: Upload Architecture

* **Title:** Media Segmentation and Verification Guarding
* **Status:** Approved
* **Context:** Mixing private files (e.g., ID scans, business licenses) with public assets (avatars, galleries) in a public folder creates serious privacy risks. Uploaded images must also be optimized for low-bandwidth networks.
* **Alternatives Considered:** Store all media in public folder with obscure naming, store all media in private folders and route all images through PHP scripts.
* **Final Decision:** Segment uploads:
  * **Public media:** Store in `/public/uploads-public/` for direct, fast web serving.
  * **Private media:** Store in `/storage/secure_uploads/` above the web root. Serve files to authorized admins only via a session-guarded PHP script.
  * **Processing:** Convert all images to WebP format, strip EXIF metadata, and generate fixed-width thumbnails on upload.
* **Consequences:**
  * Sensitive data is isolated and protected.
  * WebP and pre-generated thumbnails minimize VPS CPU spikes and user page-load times.
  * Admin file downloads require a secure PHP execution wrapper, adding small processing overhead for administrators.
