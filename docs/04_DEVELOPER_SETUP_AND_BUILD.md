# Shinjiru Maintenance - Developer Guide & Build Instructions

## 1. Environment Requirements
- **PHP:** 7.4 or later (tested up to PHP 8.2)
- **WordPress:** 6.0 or later (tested up to WordPress 6.7)
- **Web Server:** Apache (with mod_rewrite) or Nginx
- **Database:** MySQL 5.7+ or MariaDB 10.4+

## 2. Directory Layout
```text
E:\Projects\Shinjiru Maintenance\
├── assets-wp-org\          // WordPress.org banners, icons, and screenshots
├── docs\                   // Architecture, audit, competitor & test reports
├── plugin\                 // Full plugin source directory
│   └── shinjiru-maintenance\
├── tests\                  // Automated test suites
├── dist\                   // Final installable release package
├── THIRD_PARTY_NOTICES.md  // Attribution and license notices
└── README.md               // Project overview
```

## 3. Building the Release ZIP
To build a clean, production-ready, installable ZIP package suitable for WordPress dashboard upload:
1. Ensure all PHP files pass linting:
   ```powershell
   Get-ChildItem -Path "plugin\shinjiru-maintenance" -Recurse -Filter "*.php" | ForEach-Object { php -l $_.FullName }
   ```
2. Compress the plugin directory into `dist/shinjiru-maintenance.zip`:
   ```powershell
   Compress-Archive -Path "plugin\shinjiru-maintenance" -DestinationPath "dist\shinjiru-maintenance.zip" -Force
   ```

## 4. Running the Automated Test Suite
Execute the CLI test suite with PHP:
```powershell
$env:PATH = "E:\xampp\php;" + $env:PATH
php tests/run-tests.php
```
