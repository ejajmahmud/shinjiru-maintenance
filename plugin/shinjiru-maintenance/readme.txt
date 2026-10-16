=== Shinjiru Maintenance ===
Contributors: ejajmahmud
Tags: maintenance, maintenance mode, coming soon, under construction, site offline
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A professional, secure, and lightweight maintenance and coming soon plugin with 6 original templates, robust access controls, and schedule management.

== Description ==

Shinjiru Maintenance is a modern, high-performance maintenance mode and coming-soon solution crafted for WordPress. It provides complete control over site downtime while ensuring search engine preservation and seamless client review.

Whether you are performing server updates, redesigning an active WooCommerce store, or launching a brand-new digital brand, Shinjiru Maintenance delivers a cohesive, premium experience without bloat, subscriptions, or external tracking.

### Why Shinjiru Maintenance?

* **Dual-Mode SEO Protection:**
  * **Maintenance Mode:** Returns HTTP 503 (Service Unavailable) with a configurable `Retry-After` header and `noindex, nofollow` directives, ensuring search engines do not de-index your site during routine maintenance.
  * **Coming Soon Mode:** Returns HTTP 200 (OK) with configurable indexing options, ideal for pre-launch teasers and early search presence.
* **6 High-Fidelity Original Templates:**
  * Minimal Professional (Distraction-free typographic simplicity)
  * Corporate Enterprise (Structured corporate layout with official system notice badge)
  * Dark Cyber Technology (Sleek dark theme with cyber accents and countdown cards)
  * Modern Editorial (Refined serif aesthetic with timeless layout)
  * Split Visual Hero (Two-column layout with architectural visual branding)
  * Calm Aurora Gradient (Frosted glassmorphism with soft modern gradient)
* **Hardened Enterprise Access Controls:**
  * Role-based bypass for authorized WordPress user roles (Administrators, Editors, Authors).
  * Secret Bypass URL with cryptographically secure HMAC tokens and one-click revocation.
  * Front-end Password Protection powered by standard WordPress cryptographic hashing (`wp_hash_password`).
  * Granular IP Allowlist with IPv4, IPv6, and CIDR subnet support (`10.0.0.0/8`, `192.168.1.0/24`).
* **Timezone-Aware Scheduling:**
  * Automated activation and deactivation based on your WordPress site timezone.
  * Evaluated at request time to ensure reliability without relying on WP-Cron.
* **100% Privacy & Zero Bloat:**
  * No remote Google Fonts tracking.
  * No external CDN calls.
  * No telemetric tracking or data collection.
  * No ads, upsells, or locked buttons.
* **Safe Portability & Clean Lifecycle:**
  * Versioned JSON settings export and import with sensitive secrets excluded.
  * Complete uninstallation data policy (choose between retaining or purging settings).

== Installation ==

1. Upload the `shinjiru-maintenance` folder to the `/wp-content/plugins/` directory, or install the ZIP file via **Plugins > Add New > Upload Plugin** in your WordPress dashboard.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **Settings > Shinjiru Maintenance** to configure your mode, choose a template, and activate maintenance.

== Frequently Asked Questions ==

= How do I let clients preview the site while it is in maintenance mode? =
Navigate to **Settings > Shinjiru Maintenance > Access & Security**. Copy your unique Secret Bypass URL and share it with your clients. Visiting this link grants an authenticated bypass session without requiring a WordPress login account.

= Will Maintenance Mode harm my Google search rankings? =
No. Shinjiru Maintenance outputs an HTTP 503 Service Unavailable header along with a Retry-After directive. This informs search engine crawlers that the downtime is temporary and instructs them to return later rather than de-indexing your pages.

= Can I get locked out of my WordPress administration panel? =
No. The plugin is built with lockout prevention: `wp-login.php`, `wp-admin`, and users with `manage_options` permissions are never intercepted.

= Does this plugin send data or load external scripts? =
No. In strict adherence to WordPress.org guidelines and privacy principles, all styles, scripts, and templates are bundled locally without external dependencies.

== Screenshots ==

1. Main Mode & Status configuration panel.
2. Designer Template selector with live preview.
3. Content and launch countdown settings.
4. Access controls with Secret Bypass URL and IP allowlisting.
5. Automated timezone-aware schedule configuration.
6. Settings export, import, and reset tools.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Added Maintenance Mode (503 Service Unavailable) and Coming Soon Mode (200 OK).
* Added 6 original, fully responsive, and accessible templates.
* Added Secret Bypass URL with HMAC token validation and one-click regeneration.
* Added IP allowlisting with IPv4, IPv6, and CIDR block support.
* Added front-end password protection with secure hashing and HMAC session cookies.
* Added timezone-aware automated scheduling.
* Added JSON settings export and import tools.
* Added Admin Bar status indicator with direct settings shortcut.
