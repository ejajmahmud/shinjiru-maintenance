<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css' ); ?>">
  <style>
    body.tpl-minimal {
      background-color: #FAFAFA;
      color: #171717;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 30px 20px;
    }
    .minimal-wrap {
      max-width: 600px;
      width: 100%;
      text-align: center;
    }
    .minimal-logo {
      max-height: 52px;
      margin: 0 auto 36px;
    }
    .minimal-heading {
      font-size: clamp(28px, 5vw, 42px);
      font-weight: 800;
      letter-spacing: -0.03em;
      line-height: 1.15;
      margin: 0 0 16px;
    }
    .minimal-desc {
      font-size: 16px;
      color: #525252;
      line-height: 1.6;
      margin: 0 0 28px;
    }
    .minimal-btn {
      display: inline-block;
      background: #171717;
      color: #FFFFFF;
      padding: 12px 28px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 15px;
      transition: background 0.2s ease;
    }
    .minimal-btn:hover { background: #404040; }
    .minimal-cd .shinjiru-cd-item {
      background: #E5E5E5;
      color: #171717;
    }
    .minimal-footer {
      margin-top: 48px;
      font-size: 13px;
      color: #737373;
    }
    <?php echo $custom_css; ?>
  </style>
</head>
<body class="tpl-minimal">
  <main class="minimal-wrap" role="main">
    <?php if ( $logo_url ) : ?>
      <img src="<?php echo $logo_url; ?>" alt="Logo" class="minimal-logo" />
    <?php endif; ?>

    <h1 class="minimal-heading"><?php echo $headline; ?></h1>
    <div class="minimal-desc"><?php echo $description; ?></div>

    <?php if ( $show_countdown && $countdown_target ) : ?>
      <div class="shinjiru-countdown-container minimal-cd" style="justify-content: center;" data-countdown-target="<?php echo $countdown_target; ?>" aria-label="<?php esc_attr_e( 'Launch Countdown', 'shinjiru-maintenance' ); ?>">
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-days">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-hours">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-minutes">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Mins', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-seconds">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Secs', 'shinjiru-maintenance' ); ?></span></div>
      </div>
    <?php endif; ?>

    <?php if ( $show_cta && $cta_url ) : ?>
      <div><a href="<?php echo $cta_url; ?>" class="minimal-btn"><?php echo $cta_text; ?></a></div>
    <?php endif; ?>

    <?php if ( $enable_password ) : ?>
      <div style="display:flex; justify-content:center;">
        <form method="post" action="" class="shinjiru-gate-form">
          <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
          <div class="shinjiru-gate-group">
            <input type="password" name="shinjiru_password" placeholder="<?php esc_attr_e( 'Enter password to access', 'shinjiru-maintenance' ); ?>" required class="shinjiru-gate-input" aria-label="<?php esc_attr_e( 'Password', 'shinjiru-maintenance' ); ?>" />
            <button type="submit" class="shinjiru-gate-btn minimal-btn"><?php esc_html_e( 'Enter', 'shinjiru-maintenance' ); ?></button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <footer class="minimal-footer">
      <?php if ( array_filter( $socials ) ) : ?>
        <ul class="shinjiru-social-list" style="justify-content: center;">
          <?php foreach ( $socials as $network => $surl ) : if ( empty( $surl ) ) continue; ?>
            <li><a href="<?php echo $surl; ?>" target="_blank" rel="noopener noreferrer" class="shinjiru-social-link" style="background:#E5E5E5; color:#171717;" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
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
