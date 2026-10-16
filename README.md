# Shinjiru Maintenance

A professional, secure, and lightweight WordPress maintenance mode and coming soon plugin under the **Shinjiru** brand, built to WordPress.org directory standards.

Developed by **Ejaj Mahmud**.

---

## 🌟 Key Features

- **Dual-Mode Operation:**
  - **Maintenance Mode:** Emits HTTP `503 Service Unavailable`, `Retry-After: 3600`, and `noindex, nofollow` headers to preserve SEO equity during updates.
  - **Coming Soon Mode:** Emits HTTP `200 OK` with customizable meta robots settings for pre-launch audience building.
- **6 Original Designer Templates:**
  - Minimal Professional
  - Corporate Enterprise
  - Dark Cyber Technology
  - Modern Editorial
  - Split Visual Hero
  - Calm Aurora Gradient
- **Advanced Access & Bypass Controls:**
  - Secret Bypass URL with cryptographically secure HMAC tokens and one-click revocation.
  - Role-based bypass for WordPress user roles.
  - Front-end password protection powered by `wp_hash_password` and HMAC session cookies.
  - Granular IP allowlist supporting IPv4, IPv6, and CIDR blocks (`192.168.1.0/24`).
- **Timezone-Aware Scheduling:**
  - Start and end datetime scheduling evaluated at request time using the site's local timezone.
- **Settings Portability:**
  - Safe JSON export/import with secret redaction and schema validation.
- **100% Privacy & Zero Bloat:**
  - No remote tracking, no Google Fonts calls, no CDN dependencies, and no upsells.

---

## 📂 Project Directory Structure

```text
E:\Projects\Shinjiru Maintenance\
├── assets-wp-org/              # WordPress.org banners, icons, and logo assets
├── dist/
│   └── shinjiru-maintenance.zip# Clean installable release package
├── docs/                       # Complete engineering and research documentation
│   ├── 01_ORIGINAL_PLUGIN_AUDIT.md
│   ├── 02_COMPETITOR_RESEARCH.md
│   ├── 03_PRODUCT_SPEC_AND_ARCHITECTURE.md
│   ├── 04_DEVELOPER_SETUP_AND_BUILD.md
│   ├── 05_TEST_AND_COMPATIBILITY_REPORT.md
│   ├── 06_ROADMAP_DEFERRED_FEATURES.md
│   └── 07_RELEASE_READINESS_REPORT.md
├── plugin/
│   └── shinjiru-maintenance/   # Shipped plugin source
├── tests/
│   └── run-tests.php           # Automated CLI verification suite
├── THIRD_PARTY_NOTICES.md      # Attribution and licensing disclosures
└── README.md
```

---

## 🚀 Installation & Testing

1. Download or locate `dist/shinjiru-maintenance.zip`.
2. In your WordPress administration panel, go to **Plugins > Add New > Upload Plugin**.
3. Choose `shinjiru-maintenance.zip` and click **Install Now**.
4. Activate the plugin and navigate to **Settings > Shinjiru Maintenance**.

To run the automated verification suite:
```powershell
$env:PATH = "E:\xampp\php;" + $env:PATH
php tests/run-tests.php
```
