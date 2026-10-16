<?php
if (!extension_loaded('gd')) {
    echo "GD not loaded\n";
    exit;
}

$logo_path = 'C:/Users/EJAJ/Documents/logo_shinjiru_red .png.png';
$dir = __DIR__ . '/../assets-wp-org/';

function create_wp_asset($path, $w, $h, $logo_path, $subtitle = '', $font_size = 14) {
    $img = imagecreatetruecolor($w, $h);
    // Background #1E293B
    $bg = imagecolorallocate($img, 30, 41, 59);
    imagefill($img, 0, 0, $bg);

    $logo = imagecreatefrompng($logo_path);
    $orig_w = imagesx($logo);
    $orig_h = imagesy($logo);

    // Target logo width
    $target_w = (int)($w * 0.55);
    $target_h = (int)($orig_h * ($target_w / $orig_w));
    if ($target_h > ($h * 0.45)) {
        $target_h = (int)($h * 0.45);
        $target_w = (int)($orig_w * ($target_h / $orig_h));
    }

    $dest_x = (int)(($w - $target_w) / 2);
    $dest_y = (int)(($h - $target_h) / 2) - ($subtitle ? 15 : 0);

    imagecopyresampled($img, $logo, $dest_x, $dest_y, 0, 0, $target_w, $target_h, $orig_w, $orig_h);

    if ($subtitle) {
        $text_color = imagecolorallocate($img, 241, 245, 249);
        // Center text using basic GD string
        $text_x = (int)(($w - (strlen($subtitle) * 9)) / 2);
        $text_y = $dest_y + $target_h + 20;
        imagestring($img, 5, $text_x, $text_y, $subtitle, $text_color);
    }

    imagepng($img, $path);
    imagedestroy($img);
    imagedestroy($logo);
    echo "Created: " . basename($path) . "\n";
}

create_wp_asset($dir . 'banner-772x250.png', 772, 250, $logo_path, 'MAINTENANCE & COMING SOON');
create_wp_asset($dir . 'banner-1544x500.png', 1544, 500, $logo_path, 'ENTERPRISE MAINTENANCE & COMING SOON');
create_wp_asset($dir . 'icon-128x128.png', 128, 128, $logo_path);
create_wp_asset($dir . 'icon-256x256.png', 256, 256, $logo_path);
