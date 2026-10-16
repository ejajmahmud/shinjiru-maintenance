<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css' ); ?>">
  <style>
    body.tpl-corporate {
      background-color: #F8FAFC;
      color: #0F172A;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 40px 20px;
    }
    .corp-card {
      background: #FFFFFF;
      max-width: 680px;
      width: 100%;
      border-radius: 12px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
      padding: clamp(30px, 6vw, 56px);
      text-align: center;
    }
    .corp-badge {
      display: inline-block;
      background: #FEE2E2;
      color: #991B1B;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 4px 12px;
      border-radius: 20px;
      margin-bottom: 24px;
    }
    .corp-logo {
      max-height: 56px;
      margin: 0 auto 28px;
    }
    .corp-heading {
      font-size: clamp(26px, 4.5vw, 36px);
      font-weight: 700;
      color: #1E293B;
      line-height: 1.25;
      margin: 0 0 16px;
    }
    .corp-desc {
      font-size: 16px;
      color: #64748B;
      line-height: 1.6;
      margin: 0 0 28px;
    }
    .corp-btn {
      display: inline-block;
      background: #D32F2F;
      color: #FFFFFF;
      padding: 13px 30px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 15px;
      transition: background 0.2s ease;
    }
    .corp-btn:hover { background: #B71C1C; }
    .corp-cd .shinjiru-cd-item {
      background: #F1F5F9;
      color: #1E293B;
      border: 1px solid #E2E8F0;
    }
    .corp-footer {
      margin-top: 40px;
      padding-top: 24px;
      border-top: 1px solid #F1F5F9;
      font-size: 13px;
      color: #94A3B8;
    }
    <?php echo $custom_css; ?>
  </style>
</head>
<body class="tpl-corporate">
  <main class="corp-card" role="main">
    <div class="corp-badge"><?php esc_html_e( 'System Notice', 'shinjiru-maintenance' ); ?></div>

    <?php if ( $logo_url ) : ?>
      <img src="<?php echo $logo_url; ?>" alt="Company Logo" class="corp-logo" />
    <?php endif; ?>

    <h1 class="corp-heading"><?php echo $headline; ?></h1>
    <div class="corp-desc"><?php echo $description; ?></div>

    <?php if ( $show_countdown && $countdown_target ) : ?>
      <div class="shinjiru-countdown-container corp-cd" style="justify-content: center;" data-countdown-target="<?php echo $countdown_target; ?>" aria-label="<?php esc_attr_e( 'Countdown', 'shinjiru-maintenance' ); ?>">
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-days">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-hours">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-minutes">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Mins', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-seconds">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Secs', 'shinjiru-maintenance' ); ?></span></div>
      </div>
    <?php endif; ?>

    <?php if ( $show_cta && $cta_url ) : ?>
      <div><a href="<?php echo $cta_url; ?>" class="corp-btn"><?php echo $cta_text; ?></a></div>
    <?php endif; ?>

    <?php if ( $enable_password ) : ?>
      <div style="display:flex; justify-content:center;">
        <form method="post" action="" class="shinjiru-gate-form">
          <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
          <div class="shinjiru-gate-group">
            <input type="password" name="shinjiru_password" placeholder="<?php esc_attr_e( 'Staff & reviewer password', 'shinjiru-maintenance' ); ?>" required class="shinjiru-gate-input" aria-label="<?php esc_attr_e( 'Password', 'shinjiru-maintenance' ); ?>" />
            <button type="submit" class="shinjiru-gate-btn corp-btn"><?php esc_html_e( 'Verify', 'shinjiru-maintenance' ); ?></button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <footer class="corp-footer">
      <?php if ( array_filter( $socials ) ) : ?>
        <ul class="shinjiru-social-list" style="justify-content: center;">
          <?php foreach ( $socials as $network => $surl ) : if ( empty( $surl ) ) continue; ?>
            <li><a href="<?php echo $surl; ?>" target="_blank" rel="noopener noreferrer" class="shinjiru-social-link" style="background:#F1F5F9; color:#475569;" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
              &bull;
            </a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <p style="margin-top:14px;"><?php echo $footer_text; ?></p>
    </footer>
  </main>
  <script src="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/js/countdown.js' ); ?>"></script>
</body>
</html>
