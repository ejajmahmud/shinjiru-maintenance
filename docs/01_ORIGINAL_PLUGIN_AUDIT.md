# Audit Report: hide-my-site (v2.2)

## 1. Provenance, Identity & Licensing
- **Plugin Name:** Hide My Site
- **Version:** 2.2
- **Author:** Justin Saad (clevelandwebdeveloper)
- **License:** GPLv2 (stated in header and readme)
- **Source Archive SHA-256:** `765C318A82FE0874398678AD78B89FB6036637A8A035352B1550EB89BACD0514`
- **Inspected Location:** `E:\Projects\Shinjiru Maintenance\inspected-source\hide-my-site\`

## 2. Architecture & File Structure
- **Monolithic Flat Structure:**
  - `index.php` (~70KB): Encompasses procedural and semi-OOP code mixed with inline HTML forms, promotional banners, CSS, and hook registrations.
  - `includes/security.php` (~4.2KB): A raw database table creation script and rudimentary IP tracker.
  - `templates/`: 7 basic PHP templates (`hmsclassic.php`, `hmscobalt.php`, `hmsbinder.php`, `hmsdiscreet.php`, `hmsice.php`, `hmsiris.php`, `hmslockandkey.php`) that render fixed login forms with legacy skeuomorphic styling.
  - `images/`: Skeuomorphic buttons, ribbons, and commercial upsell graphics.
  - Global variable declarations (`$plugin_label`, `$plugin_slug`) at the top of `index.php` that pollute PHP global scope.

## 3. Security Weaknesses & Vulnerabilities Found
1. **Critical SQL Injection Vulnerability:**
   - In `includes/security.php`:
     ```php
     $userip = $_SERVER['REMOTE_ADDR'];
     $previousRecord = $wpdb->get_row("SELECT * FROM $dbName WHERE ip = '$userip'");
     ```
     The query interpolates `$userip` directly without `$wpdb->prepare()`. If server configurations pass forwarded headers or unvalidated values into `REMOTE_ADDR`, or during header spoofing under certain reverse proxies, arbitrary SQL injection is possible.
2. **Insecure Cookie Authentication:**
   - In `index.php`:
     ```php
     setcookie($this->get_cookie2_name(), 1, $this->get_cookie_duration(), '/');
     ```
     The cookie value is a hardcoded literal `1`! If an attacker knows or discovers the cookie name (which is predictable based on options), setting `cookie_name=1` immediately grants total access without ever having to submit a valid password.
   - Missing `httponly`, `secure`, and `samesite` parameters, making session tokens vulnerable to XSS theft and CSRF.
3. **Plaintext Password Storage:**
   - Stored in the `wp_options` table (`hide_my_site_password`) in unencrypted plaintext. Anyone with read access to the database or options table can immediately view the visitor gate password.
4. **Improper HTTP Status & SEO Handling:**
   - The plugin does not distinguish between Maintenance Mode (which requires HTTP `503 Service Unavailable` with `Retry-After`) and Coming Soon mode (which requires HTTP `200 OK`).
   - Search engines visiting during updates receive standard 200 responses with login markup, which can lead to search engine de-indexing of content or indexing of login placeholder copy.
5. **Database Pollution & Premature Initialization:**
   - Creates a custom table `wp_hidemysiteSecure` dynamically during frontend `init` checks rather than during standard plugin activation.
   - Fails to index columns cleanly (`ip` is typed as `text`), creating performance bottlenecks on busy sites.
   - No auto-pruning mechanism for expired failure records, leading to unbounded table growth.
6. **Commercial Upsell Intrusion:**
   - Aggressive promotional banners ("Multipass", "Bypass URL", "Redirect") embedded directly into admin screens and persistent nag banners in the WordPress dashboard.

## 4. Architectural Directives for Shinjiru Maintenance
1. **Modern PSR-4 Compliant OOP Architecture:**
   - Root namespace: `Shinjiru\Maintenance`
   - Structured modules:
     - `Core\Plugin`: Bootstrapper and lifecycle hooks.
     - `Core\Config`: Typed option manager with sanitization and secure defaults.
     - `Http\Interceptor`: Routing decision engine hooked to `template_redirect` handling SEO headers (`503 Service Unavailable` with `Retry-After` for Maintenance, `200 OK` for Coming Soon).
     - `Security\AccessManager`: Role-based capability checks (`read_private_posts` / `manage_options`), IP allowlisting (IPv4 & IPv6 with CIDR support), and cryptographically signed bypass tokens using HMAC-SHA256 with expiration and timing-safe comparison (`hash_equals`).
     - `Security\PasswordManager`: Hashed storage using `wp_hash_password` / constant-time verification, HMAC-signed session cookies with `HttpOnly`, `Secure`, and `SameSite=Lax` flags.
     - `Admin\AdminPage`: Native WordPress Settings API, tabbed clean UI without ads or upsells, integrating the official Shinjiru logo cleanly.
     - `Rendering\TemplateEngine`: 6+ high-fidelity, responsive, accessible, dependency-free original templates.
     - `Lifecycle\Activator` & `Deactivator` & `Uninstaller`: Safe activation, schedule cleanup, and optional complete data cleanup policy.
