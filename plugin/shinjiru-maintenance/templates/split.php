<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css' ); ?>">
  <style>
    body.tpl-split {
      background-color: #FFFFFF;
      color: #0F172A;
      min-height: 100vh;
      display: flex;
    }
    .split-container {
      display: flex;
      width: 100%;
      min-height: 100vh;
    }
    .split-visual {
      flex: 1;
      background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px;
      color: #FFFFFF;
      position: relative;
      overflow: hidden;
    }
    .split-visual-pattern {
      position: absolute;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(211,47,47,0.15) 10%, transparent 20%), radial-gradient(circle, rgba(2,132,199,0.12) 15%, transparent 25%);
      background-size: 60px 60px;
      opacity: 0.6;
    }
    .split-visual-content {
      position: relative;
      z-index: 2;
      max-width: 420px;
      text-align: center;
    }
    .split-visual-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 24px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .split-content {
      flex: 1.2;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: clamp(30px, 6vw, 80px);
      background: #FFFFFF;
    }
    .split-inner {
      max-width: 520px;
      width: 100%;
    }
    .split-logo {
      max-height: 48px;
      margin-bottom: 32px;
    }
    .split-heading {
      font-size: clamp(28px, 4vw, 40px);
      font-weight: 800;
      line-height: 1.2;
      margin: 0 0 16px;
      color: #0F172A;
    }
    .split-desc {
      font-size: 16px;
      color: #64748B;
      line-height: 1.6;
      margin: 0 0 28px;
    }
    .split-btn {
      display: inline-block;
      background: #D32F2F;
      color: #FFFFFF;
      padding: 13px 28px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 15px;
      transition: background 0.2s ease;
    }
    .split-btn:hover { background: #B71C1C; }
    .split-cd .shinjiru-cd-item {
      background: #F8FAFC;
      border: 1px solid #E2E8F0;
      color: #0F172A;
    }
    .split-footer {
      margin-top: 48px;
      font-size: 13px;
      color: #94A3B8;
    }
    @media (max-width: 860px) {
      .split-container {
        flex-direction: column;
      }
      .split-visual {
        padding: 50px 20px;
        min-height: 240px;
      }
      .split-content {
        padding: 40px 20px;
      }
    }
    <?php echo $custom_css; ?>
  </style>
</head>
<body class="tpl-split">
  <div class="split-container">
    <div class="split-visual" role="presentation">
      <div class="split-visual-pattern"></div>
      <div class="split-visual-content">
        <div class="split-visual-icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#D32F2F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h2 style="font-size:24px; font-weight:700; margin:0 0 10px; color:#F8FAFC;"><?php esc_html_e( 'Quality in Motion', 'shinjiru-maintenance' ); ?></h2>
        <p style="font-size:14px; color:#94A3B8; margin:0; line-height:1.5;"><?php esc_html_e( 'Enhancing platform performance, security hardening, and high-availability updates.', 'shinjiru-maintenance' ); ?></p>
      </div>
    </div>

    <main class="split-content" role="main">
      <div class="split-inner">
        <?php if ( $logo_url ) : ?>
          <img src="<?php echo $logo_url; ?>" alt="Logo" class="split-logo" />
        <?php endif; ?>

        <h1 class="split-heading"><?php echo $headline; ?></h1>
        <div class="split-desc"><?php echo $description; ?></div>

        <?php if ( $show_countdown && $countdown_target ) : ?>
          <div class="shinjiru-countdown-container split-cd" data-countdown-target="<?php echo $countdown_target; ?>" aria-label="<?php esc_attr_e( 'Countdown', 'shinjiru-maintenance' ); ?>">
            <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-days">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
            <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-hours">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
            <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-minutes">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Mins', 'shinjiru-maintenance' ); ?></span></div>
            <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-seconds">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Secs', 'shinjiru-maintenance' ); ?></span></div>
          </div>
        <?php endif; ?>

        <?php if ( $show_cta && $cta_url ) : ?>
          <div><a href="<?php echo $cta_url; ?>" class="split-btn"><?php echo $cta_text; ?></a></div>
        <?php endif; ?>

        <?php if ( $enable_password ) : ?>
          <form method="post" action="" class="shinjiru-gate-form">
            <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
            <div class="shinjiru-gate-group">
              <input type="password" name="shinjiru_password" placeholder="<?php esc_attr_e( 'Bypass password', 'shinjiru-maintenance' ); ?>" required class="shinjiru-gate-input" aria-label="<?php esc_attr_e( 'Password', 'shinjiru-maintenance' ); ?>" />
              <button type="submit" class="shinjiru-gate-btn split-btn"><?php esc_html_e( 'Access', 'shinjiru-maintenance' ); ?></button>
            </div>
          </form>
        <?php endif; ?>

        <footer class="split-footer">
          <?php if ( array_filter( $socials ) ) : ?>
            <ul class="shinjiru-social-list">
              <?php foreach ( $socials as $network => $surl ) : if ( empty( $surl ) ) continue; ?>
                <li><a href="<?php echo $surl; ?>" target="_blank" rel="noopener noreferrer" class="shinjiru-social-link" style="background:#F1F5F9; color:#0F172A;" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
                  &bull;
                </a></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <p style="margin-top:16px;"><?php echo $footer_text; ?></p>
        </footer>
      </div>
    </main>
  </div>
  <script src="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/js/countdown.js' ); ?>"></script>
</body>
</html>
