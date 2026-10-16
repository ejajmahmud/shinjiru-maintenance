<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="<?php echo esc_url( SHINJIRU_MAINTENANCE_URL . 'assets/css/templates.css' ); ?>">
  <style>
    body.tpl-editorial {
      background-color: #FDFBF7;
      color: #2C2825;
      font-family: Georgia, Cambria, "Times New Roman", Times, serif;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 50px 20px;
    }
    .edit-wrap {
      max-width: 620px;
      width: 100%;
      text-align: center;
    }
    .edit-divider {
      width: 48px;
      height: 2px;
      background: #D32F2F;
      margin: 24px auto;
    }
    .edit-logo {
      max-height: 50px;
      margin: 0 auto 30px;
    }
    .edit-heading {
      font-size: clamp(30px, 5.5vw, 46px);
      font-weight: 400;
      font-style: italic;
      line-height: 1.2;
      margin: 0 0 16px;
      color: #1A1816;
    }
    .edit-desc {
      font-size: 17px;
      color: #5C554E;
      line-height: 1.7;
      margin: 0 0 32px;
      font-family: var(--shinjiru-font);
    }
    .edit-btn {
      display: inline-block;
      background: #2C2825;
      color: #FDFBF7;
      padding: 12px 28px;
      border-radius: 4px;
      text-decoration: none;
      font-family: var(--shinjiru-font);
      font-weight: 600;
      font-size: 14px;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      transition: background 0.2s ease;
    }
    .edit-btn:hover { background: #5C554E; }
    .edit-cd {
      font-family: var(--shinjiru-font);
    }
    .edit-cd .shinjiru-cd-item {
      background: #F3EFEA;
      color: #2C2825;
      border: 1px solid #E5DFD7;
    }
    .edit-footer {
      margin-top: 52px;
      font-size: 13px;
      color: #8C827A;
      font-family: var(--shinjiru-font);
    }
    <?php echo $custom_css; ?>
  </style>
</head>
<body class="tpl-editorial">
  <main class="edit-wrap" role="main">
    <?php if ( $logo_url ) : ?>
      <img src="<?php echo $logo_url; ?>" alt="Logo" class="edit-logo" />
    <?php endif; ?>

    <h1 class="edit-heading"><?php echo $headline; ?></h1>
    <div class="edit-divider" role="presentation"></div>
    <div class="edit-desc"><?php echo $description; ?></div>

    <?php if ( $show_countdown && $countdown_target ) : ?>
      <div class="shinjiru-countdown-container edit-cd" style="justify-content: center;" data-countdown-target="<?php echo $countdown_target; ?>" aria-label="<?php esc_attr_e( 'Countdown', 'shinjiru-maintenance' ); ?>">
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-days">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-hours">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-minutes">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Mins', 'shinjiru-maintenance' ); ?></span></div>
        <div class="shinjiru-cd-item"><span class="shinjiru-cd-num cd-seconds">00</span><span class="shinjiru-cd-label"><?php esc_html_e( 'Secs', 'shinjiru-maintenance' ); ?></span></div>
      </div>
    <?php endif; ?>

    <?php if ( $show_cta && $cta_url ) : ?>
      <div><a href="<?php echo $cta_url; ?>" class="edit-btn"><?php echo $cta_text; ?></a></div>
    <?php endif; ?>

    <?php if ( $enable_password ) : ?>
      <div style="display:flex; justify-content:center; font-family:var(--shinjiru-font);">
        <form method="post" action="" class="shinjiru-gate-form">
          <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
          <div class="shinjiru-gate-group">
            <input type="password" name="shinjiru_password" placeholder="<?php esc_attr_e( 'Reader access key', 'shinjiru-maintenance' ); ?>" required class="shinjiru-gate-input" aria-label="<?php esc_attr_e( 'Password', 'shinjiru-maintenance' ); ?>" />
            <button type="submit" class="shinjiru-gate-btn edit-btn"><?php esc_html_e( 'Open', 'shinjiru-maintenance' ); ?></button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <footer class="edit-footer">
      <?php if ( array_filter( $socials ) ) : ?>
        <ul class="shinjiru-social-list" style="justify-content: center;">
          <?php foreach ( $socials as $network => $surl ) : if ( empty( $surl ) ) continue; ?>
            <li><a href="<?php echo $surl; ?>" target="_blank" rel="noopener noreferrer" class="shinjiru-social-link" style="background:#F3EFEA; color:#2C2825;" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
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
