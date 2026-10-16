<?php
$logo_b64 = trim(file_get_contents(__DIR__ . '/../assets-wp-org/logo_base64.txt'));
$logo_data_uri = 'data:image/png;base64,' . $logo_b64;

$templates = ['minimal', 'corporate', 'dark-tech', 'editorial', 'split', 'calm-gradient'];
$css_core = file_get_contents(__DIR__ . '/../plugin/shinjiru-maintenance/assets/css/templates.css');
$js_cd = file_get_contents(__DIR__ . '/../plugin/shinjiru-maintenance/assets/js/countdown.js');

if (!function_exists('language_attributes')) {
    function language_attributes() { echo 'lang="en"'; }
    function bloginfo($v) { echo 'UTF-8'; }
    function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function esc_url($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function esc_attr_e($s) { echo htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function esc_html_e($s) { echo htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function esc_html__($s, $d = 'default') { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function esc_attr__($s, $d = 'default') { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    function wp_kses_post($s) { return $s; }
    function wp_nonce_field($a, $n) { echo '<input type="hidden" name="'.$n.'" value="preview_nonce" />'; }
}

if (!defined('SHINJIRU_MAINTENANCE_URL')) {
    define('SHINJIRU_MAINTENANCE_URL', '');
}

$previews = [];

foreach ($templates as $tpl) {
    $title = 'Preview: ' . ucfirst($tpl);
    $headline = 'We will be back shortly.';
    $description = 'We are performing scheduled maintenance and updates to improve your experience. Thank you for your patience!';
    $footer_text = '&copy; 2026 Shinjiru. All rights reserved.';
    $logo_url = $logo_data_uri;
    $custom_css = '';
    $show_cta = true;
    $cta_text = 'Contact Support';
    $cta_url = '#';
    $show_countdown = true;
    $countdown_target = '2026-12-31T23:59';
    $enable_password = true;
    $status = 'maintenance';
    $socials = [
        'x' => '#',
        'linkedin' => '#',
        'facebook' => '#',
        'instagram' => '#',
        'github' => '#'
    ];

    ob_start();
    include __DIR__ . '/../plugin/shinjiru-maintenance/templates/' . $tpl . '.php';
    $html = ob_get_clean();

    // Inline assets so previews are 100% self-contained
    $html = preg_replace('/<link rel="stylesheet"[^>]+>/', '<style>' . $css_core . '</style>', $html);
    $html = preg_replace('/<script src="[^"]+countdown\.js"><\/script>/', '<script>' . $js_cd . '</script>', $html);

    file_put_contents(__DIR__ . '/preview_' . $tpl . '.html', $html);
    $previews[$tpl] = $html;
    echo "Generated preview: $tpl\n";
}

// Generate an all-in-one showcase page
$showcase = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shinjiru Maintenance - All Skins Live Showcase</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      margin: 0;
      padding: 0;
      background: #0f172a;
      color: #f8fafc;
    }
    .header {
      background: #1e293b;
      padding: 24px 32px;
      border-bottom: 1px solid #334155;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .header h1 {
      margin: 0;
      font-size: 22px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .header-nav {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .header-nav a {
      background: #334155;
      color: #f8fafc;
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .header-nav a:hover {
      background: #d32f2f;
    }
    .container {
      max-width: 1400px;
      margin: 40px auto;
      padding: 0 20px;
    }
    .skin-section {
      margin-bottom: 60px;
      background: #1e293b;
      border-radius: 12px;
      border: 1px solid #334155;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    }
    .skin-bar {
      padding: 16px 24px;
      background: #273549;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #334155;
    }
    .skin-bar h2 {
      margin: 0;
      font-size: 18px;
      color: #ffffff;
    }
    .skin-bar span {
      font-size: 13px;
      color: #94a3b8;
    }
    .skin-frame-wrap {
      width: 100%;
      height: 720px;
      background: #ffffff;
      position: relative;
    }
    iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>
      <img src="' . $logo_data_uri . '" style="height:32px; filter:brightness(0) invert(1);" alt="Shinjiru Logo" />
      Template & Skin Live Inspector
    </h1>
    <nav class="header-nav">
      <a href="#minimal">1. Minimal Professional</a>
      <a href="#corporate">2. Corporate Enterprise</a>
      <a href="#dark-tech">3. Dark Cyber Tech</a>
      <a href="#editorial">4. Modern Editorial</a>
      <a href="#split">5. Split Visual Hero</a>
      <a href="#calm-gradient">6. Calm Aurora Gradient</a>
    </nav>
  </div>
  <div class="container">';

$names = [
    'minimal' => '1. Minimal Professional (Distraction-free typographic simplicity)',
    'corporate' => '2. Corporate Enterprise (Structured corporate layout with official system notice badge)',
    'dark-tech' => '3. Dark Cyber Technology (Sleek dark theme with cyber accents and countdown cards)',
    'editorial' => '4. Modern Editorial (Refined serif aesthetic with classic margins and dividers)',
    'split' => '5. Split Visual Hero (Modern two-column layout with architectural visual hero)',
    'calm-gradient' => '6. Calm Aurora Gradient (Frosted glassmorphism with soft modern gradient)'
];

foreach ($templates as $tpl) {
    $showcase .= '
    <div class="skin-section" id="' . $tpl . '">
      <div class="skin-bar">
        <h2>' . $names[$tpl] . '</h2>
        <span>File: templates/' . $tpl . '.php</span>
      </div>
      <div class="skin-frame-wrap">
        <iframe src="preview_' . $tpl . '.html" title="' . $tpl . ' preview"></iframe>
      </div>
    </div>';
}

$showcase .= '
  </div>
</body>
</html>';

file_put_contents(__DIR__ . '/all_skins_showcase.html', $showcase);
echo "Generated all_skins_showcase.html\n";
