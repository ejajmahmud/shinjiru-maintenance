<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css' ); ?>">
  <style>
    body.tpl-calm-gradient {
      background: linear-gradient(135deg, #FFF1F2 0%, #F5F3FF 50%, #ECFEFF 100%);
      color: #1E293B;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 40px 20px;
    }
    .calm-card {
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.8);
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
      max-width: 660px;
      width: 100%;
      padding: clamp(32px, 6vw, 60px);
      text-align: center;
    }
    .calm-logo {
      max-height: 52px;
      margin: 0 auto 30px;
    }
    .calm-heading {
      font-size: clamp(28px, 5vw, 40px);
      font-weight: 700;
      color: #1E293B;
      letter-spacing: -0.02em;
      line-height: 1.25;
      margin: 0 0 16px;
    }
    .calm-desc {
      font-size: 16px;
      color: #64748B;
      line-height: 1.6;
      margin: 0 0 32px;
    }
    .calm-btn {
      display: inline-block;
      background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%);
      color: #FFFFFF;
      padding: 13px 32px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      font-size: 15px;
      box-shadow: 0 4px 14px rgba(225, 29, 72, 0.3);
      transition: all 0.2s ease;
    }
    .calm-btn:hover {
      box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45);
      transform: translateY(-1px);
    }
    .calm-cd .shinjiru-cd-item {
      background: rgba(255, 255, 255, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.8);
      color: #1E293B;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }
    .calm-footer {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid rgba(226, 232, 240, 0.6);
      font-size: 13px;
      color: #94A3B8;
    }
    <?php echo $custom_css; ?>
  </style>
</head>
<body class="tpl-calm-gradient">
  <main class="calm-card" role="main">
    <?php if ( $logo_url ) : ?>
      <img src="<?php echo $logo_url; ?>" alt="Logo" class="calm-logo" />
    <?php endif; ?>

    <h1 class="calm-heading"><?php echo $headline; ?></h1>
    <div class="calm-desc"><?php echo $description; ?></div>

    <?php if ( $show_countdown && $countdown_target ) : ?>
      <div class="shinjiru-countdown-container calm-cd" style="justify-content: center;" data-countdown-target="<?php echo $countdown_target; ?>" aria-label="<?php esc_attr_e( 'Countdown', 'shinjiru-maintenance' ); ?>">
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-days">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-hours">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-minutes">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Mins', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-seconds">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Secs', 'shinjiru-maintenance' ); ?></span></div>
      </div>
    <?php endif; ?>

    <?php if ( $show_cta && $cta_url ) : ?>
      <div><a href="<?php echo $cta_url; ?>" class="calm-btn"><?php echo $cta_text; ?></a></div>
    <?php endif; ?>

    <?php if ( $enable_password ) : ?>
      <div style="display:flex; justify-content:center;">
        <form method="post" action="" class="shinjiru-gate-form">
          <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
          <div class="shinjiru-gate-group">
            <input type="password" name="shinjiru_password" placeholder="<?php esc_attr_e( 'Enter access password', 'shinjiru-maintenance' ); ?>" required class="shinjiru-gate-input" style="background:rgba(255,255,255,0.8);" aria-label="<?php esc_attr_e( 'Password', 'shinjiru-maintenance' ); ?>" />
            <button type="submit" class="shinjiru-gate-btn calm-btn"><?php esc_html_e( 'Unlock', 'shinjiru-maintenance' ); ?></button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <footer class="calm-footer">
      <?php if ( array_filter( $socials ) ) : ?>
        <ul class="shinjiru-social-list" style="justify-content: center;">
          <?php foreach ( $socials as $network => $surl ) : if ( empty( $surl ) ) continue; ?>
            <li><a href="<?php echo $surl; ?>" target="_blank" rel="noopener noreferrer" class="shinjiru-social-link" style="background:rgba(255,255,255,0.7); color:#E11D48;" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
              &bull;
            </a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <p style="margin-top:16px;"><?php echo $footer_text; ?></p>
    </footer>
  </main>
  <script src="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/js/countdown.js' ); ?>"></script>
</body>
</html>
