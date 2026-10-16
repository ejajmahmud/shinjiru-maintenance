<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css' ); ?>">
  <style>
    body.tpl-dark-tech {
      background-color: #0B0F19;
      color: #E2E8F0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 40px 20px;
    }
    .tech-wrap {
      max-width: 650px;
      width: 100%;
      text-align: center;
    }
    .tech-logo {
      max-height: 52px;
      margin: 0 auto 32px;
      filter: drop-shadow(0 0 12px rgba(211, 47, 47, 0.4));
    }
    .tech-tag {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 13px;
      color: #38BDF8;
      letter-spacing: 0.1em;
      margin-bottom: 16px;
      display: inline-block;
      text-transform: uppercase;
    }
    .tech-heading {
      font-size: clamp(28px, 5vw, 44px);
      font-weight: 800;
      color: #F8FAFC;
      letter-spacing: -0.02em;
      margin: 0 0 16px;
      line-height: 1.2;
    }
    .tech-desc {
      font-size: 16px;
      color: #94A3B8;
      line-height: 1.6;
      margin: 0 0 32px;
    }
    .tech-btn {
      display: inline-block;
      background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
      color: #FFFFFF;
      padding: 13px 30px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 700;
      font-size: 15px;
      box-shadow: 0 0 20px rgba(2, 132, 199, 0.35);
      transition: all 0.2s ease;
    }
    .tech-btn:hover {
      box-shadow: 0 0 30px rgba(2, 132, 199, 0.6);
      transform: translateY(-1px);
    }
    .tech-cd .shinjiru-cd-item {
      background: #1E293B;
      color: #F8FAFC;
      border: 1px solid #334155;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .tech-cd .shinjiru-cd-num {
      color: #38BDF8;
    }
    .tech-gate-input {
      background: #1E293B;
      border: 1px solid #334155;
      color: #F8FAFC;
    }
    .tech-footer {
      margin-top: 48px;
      font-size: 13px;
      color: #64748B;
    }
    <?php echo $custom_css; ?>
  </style>
</head>
<body class="tpl-dark-tech">
  <main class="tech-wrap" role="main">
    <div class="tech-tag">// <?php echo ( 'coming_soon' === $status ) ? esc_html__( 'SYSTEM LAUNCH IN PROGRESS', 'shinjiru-maintenance' ) : esc_html__( 'SCHEDULED SYSTEM MAINTENANCE', 'shinjiru-maintenance' ); ?></div>

    <?php if ( $logo_url ) : ?>
      <img src="<?php echo $logo_url; ?>" alt="Logo" class="tech-logo" />
    <?php endif; ?>

    <h1 class="tech-heading"><?php echo $headline; ?></h1>
    <div class="tech-desc"><?php echo $description; ?></div>

    <?php if ( $show_countdown && $countdown_target ) : ?>
      <div class="shinjiru-countdown-container tech-cd" style="justify-content: center;" data-countdown-target="<?php echo $countdown_target; ?>" aria-label="<?php esc_attr_e( 'System Countdown', 'shinjiru-maintenance' ); ?>">
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-days">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-hours">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-minutes">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Mins', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-seconds">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Secs', 'shinjiru-maintenance' ); ?></span></div>
      </div>
    <?php endif; ?>

    <?php if ( $show_cta && $cta_url ) : ?>
      <div><a href="<?php echo $cta_url; ?>" class="tech-btn"><?php echo $cta_text; ?></a></div>
    <?php endif; ?>

    <?php if ( $enable_password ) : ?>
      <div style="display:flex; justify-content:center;">
        <form method="post" action="" class="shinjiru-gate-form">
          <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
          <div class="shinjiru-gate-group">
            <input type="password" name="shinjiru_password" placeholder="<?php esc_attr_e( 'Access Token / Password', 'shinjiru-maintenance' ); ?>" required class="shinjiru-gate-input tech-gate-input" aria-label="<?php esc_attr_e( 'Password', 'shinjiru-maintenance' ); ?>" />
            <button type="submit" class="shinjiru-gate-btn tech-btn"><?php esc_html_e( 'Authenticate', 'shinjiru-maintenance' ); ?></button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <footer class="tech-footer">
      <?php if ( array_filter( $socials ) ) : ?>
        <ul class="shinjiru-social-list" style="justify-content: center;">
          <?php foreach ( $socials as $network => $surl ) : if ( empty( $surl ) ) continue; ?>
            <li><a href="<?php echo $surl; ?>" target="_blank" rel="noopener noreferrer" class="shinjiru-social-link" style="background:#1E293B; color:#38BDF8;" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
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
