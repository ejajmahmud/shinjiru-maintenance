# Product Specification & Architecture Document

**Plugin Name:** Shinjiru Maintenance  
**Plugin Slug:** `shinjiru-maintenance`  
**Text Domain:** `shinjiru-maintenance`  
**Author:** Ejaj Mahmud  
**Target Architecture:** PHP 7.4+ (fully tested on PHP 8.2), WordPress 6.0+ (tested up to 6.7)  
**License:** GPL-2.0-or-later  

---

## 1. Feature Specification & Release Boundary

### In Scope for Initial Release (v1.0.0):
1. **Operating Modes:**
   - **Disabled:** Normal site operation.
   - **Maintenance Mode:** Returns HTTP `503 Service Unavailable`, outputs `Retry-After` header, sets `noindex, nofollow`, displays maintenance layout.
   - **Coming Soon Mode:** Returns HTTP `200 OK`, allows meta robots customization (index/noindex), displays pre-launch layout.
2. **Access & Bypass Controls:**
   - Role-based bypass (Administrator, Editor, Author, Contributor).
   - Secret Bypass URL with token validation and one-click token regeneration / revocation.
   - Front-end Password Protection: Optional gate with bcrypt/phpass password hashing (`wp_hash_password` / `wp_check_password`) and HMAC-signed session cookies.
   - IP Allowlist: Multi-line textarea supporting IPv4, IPv6, and CIDR ranges (`10.0.0.0/8`, `192.168.1.0/24`).
   - Admin Bar Status Indicator: Live indicator showing active mode with direct shortcut to settings.
3. **Template Collection (6 Original Templates):**
   - `minimal`: Minimal Professional
   - `corporate`: Corporate Enterprise
   - `dark-tech`: Dark Technology
   - `editorial`: Modern Editorial
   - `split`: Split Visual Hero
   - `calm-gradient`: Calm Aurora Gradient
4. **Customization & Media:**
   - Logo upload via native WordPress Media Library (`wp.media`).
   - Custom headline, subtext, footer notice.
   - Call-to-action button (label + link).
   - Social media profiles (X/Twitter, LinkedIn, Facebook, Instagram, GitHub, YouTube).
   - Launch Countdown timer with site timezone support, dynamic units (days, hours, minutes, seconds), and graceful zero-state handling.
   - Custom CSS injection field with strict sanitization (`wp_strip_all_tags`).
5. **Timezone-Aware Scheduling:**
   - Start and End datetime inputs.
   - Evaluated at request time using `wp_timezone()` and `current_datetime()`.
   - Automatic activation upon reaching start time, automatic deactivation upon passing end time.
6. **Portability & Lifecycle:**
   - JSON Settings Export (secrets redacted by default).
   - JSON Settings Import with strict validation, nonce verification, and capability checks.
   - Reset to Defaults button with confirm prompt.
   - Uninstallation cleanup toggle (choose whether to retain or purge options on uninstall).

### Deferred to Roadmap (v1.1+):
- Native Email Lead Capture / Newsletter database storage with CSV export (deferred to ensure zero privacy violations or half-baked forms in v1.0).
- Third-party webhook notifications (Slack / Discord alerts on status changes).
- Per-page whitelist / blacklist rules (full site maintenance is core).

---

## 2. Technical Architecture & File Layout

```text
plugin/shinjiru-maintenance/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── templates.css
│   ├── js/
│   │   ├── admin.js
│   │   └── countdown.js
│   └── images/
│       ├── shinjiru-logo.png
│       └── previews/
│           ├── minimal.svg
│           ├── corporate.svg
│           ├── dark-tech.svg
│           ├── editorial.svg
│           ├── split.svg
│           └── calm-gradient.svg
├── includes/
│   ├── class-plugin.php          // Main orchestrator & hook registration
│   ├── class-config.php          // Settings schema, sanitization & defaults
│   ├── class-interceptor.php     // Request interception & HTTP status handling
│   ├── class-access-manager.php  // Role, IP, Token & Password verification
│   ├── class-scheduler.php       // Timezone-aware date/time schedule calculation
│   ├── class-template-engine.php // Template resolution and HTML rendering
│   ├── class-admin.php           // Admin settings page, tabs & asset enqueueing
│   ├── class-portability.php     // JSON export and import handling
│   └── class-lifecycle.php       // Activation, deactivation & uninstall routines
├── templates/
│   ├── minimal.php
│   ├── corporate.php
│   ├── dark-tech.php
│   ├── editorial.php
│   ├── split.php
│   └── calm-gradient.php
├── languages/
│   └── shinjiru-maintenance.pot
├── shinjiru-maintenance.php      // Main plugin bootstrap file
├── uninstall.php                 // Dedicated secure uninstall routine
└── readme.txt                    // WordPress.org compliant readme
```

---

## 3. Threat Model & Security Posture

1. **Denial of Service & Cache Pollution:**
   - When serving maintenance pages, headers `Cache-Control: no-cache, no-store, must-revalidate, max-age=0` and `Pragma: no-cache` are transmitted.
   - For 503 responses, standard `Retry-After: 3600` is sent to guide crawlers.
2. **Preventing Admin Lockout:**
   - The interceptor explicitly skips:
     - `is_admin()` requests
     - `wp-login.php` and `wp-signup.php`
     - WordPress REST API endpoints (`/wp-json/`) unless explicitly locked, preserving Gutenberg and app health checks
     - WP-Cron (`wp-cron.php`) and WP-CLI commands (`defined('WP_CLI') && WP_CLI`)
     - XML-RPC if needed
     - Async upload requests
3. **Password Security:**
   - Passwords stored using WordPress core's native cryptographic hashing (`wp_hash_password()`).
   - Comparison uses `wp_check_password()`.
   - Authenticated sessions receive a cookie named `shinjiru_maint_access` with a value consisting of `token_id . '|' . hmac_signature`.
   - The HMAC signature uses `AUTH_SALT` and `hash_hmac('sha256', ...)`, verified using `hash_equals()` to prevent timing attacks.
4. **CSRF & Capability Checks:**
   - All admin actions and form submissions protected by `check_admin_referer('shinjiru_maint_settings')`.
   - All settings require `manage_options` capability.
5. **Settings Import Safety:**
   - Export excludes password hash and bypass tokens.
   - Import parses JSON with schema validation and sanitizes all incoming keys through `Config::sanitize_settings()`. No arbitrary objects or executable code can be deserialized.
