# Shinjiru Maintenance — refined skin collection

Open index.html directly in a browser. All assets are local; no build or internet connection is required.

Six complete responsive front-end skins, shared CSS and JavaScript, and the supplied logo (trimmed transparent padding only). Modern Editorial opens by default.

## Integration
These files are static front-end templates, not a complete WordPress plugin. Replace static strings with escaped WordPress settings; enqueue the shared assets; use a Media Library logo URL. The access form deliberately does not authenticate visitors. Connect it to a nonce-protected, rate-limited server-side handler. Do not implement authentication in JavaScript.

Countdowns are explicitly illustrative and start at 2 days, 6 hours, 24 minutes on page load. Replace the demo clock with a configured ISO 8601 target including timezone before production. They do not automatically reopen the site. Remove the countdown and its demo note if no restoration time is known.

No invented support email or social destinations are included. Add these only from verified settings. All layout styling is in assets/skins.css; behaviour is in assets/skins.js. The architectural artwork is original inline SVG. No external fonts, frameworks or image requests.

## Verification
Local HTML asset/link references checked. Shared JavaScript syntax checked. Browser layout testing could not be completed because the Chromium download failed in this environment. Inspect target browsers before production integration.
