<?php
/**
 * Architectural / Split Template.
 *
 * @package Shinjiru\Maintenance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?php echo esc_html( $title ); ?></title>
  <?php wp_head(); ?>
</head>
<body class="split">
  <div class="page">
    <header class="masthead">
      <img class="logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
      <div class="notice">
        <span class="dot" aria-hidden="true"></span>
        <?php 
        if ( 'locked' === $status ) {
          esc_html_e( 'Private / Password Protected', 'shinjiru-maintenance' );
        } elseif ( 'coming_soon' === $status ) {
          esc_html_e( 'Coming Soon / In Development', 'shinjiru-maintenance' );
        } else {
          esc_html_e( 'Scheduled Maintenance', 'shinjiru-maintenance' );
        }
        ?>
      </div>
    </header>

    <main class="content" id="main">
      <div class="layout">
        <div>
          <p class="eyebrow">
            <?php 
            if ( 'locked' === $status ) {
              esc_html_e( 'Restricted Access', 'shinjiru-maintenance' );
            } elseif ( 'coming_soon' === $status ) {
              esc_html_e( 'Something New is Coming', 'shinjiru-maintenance' );
            } else {
              esc_html_e( 'We’ll be right back', 'shinjiru-maintenance' );
            }
            ?>
          </p>
          <h1><?php echo esc_html( $headline ); ?></h1>
          <div class="description"><?php echo wp_kses_post( $description ); ?></div>

          <?php if ( $show_countdown && $countdown_target ) : ?>
            <div class="countdown" data-countdown-target="<?php echo esc_attr( $countdown_target ); ?>" aria-label="<?php esc_attr_e( 'Countdown', 'shinjiru-maintenance' ); ?>">
              <div class="unit"><span class="num cd-days">00</span><span class="label"><?php esc_html_e( 'Days', 'shinjiru-maintenance' ); ?></span></div>
              <div class="unit"><span class="num cd-hours">00</span><span class="label"><?php esc_html_e( 'Hours', 'shinjiru-maintenance' ); ?></span></div>
              <div class="unit"><span class="num cd-minutes">00</span><span class="label"><?php esc_html_e( 'Minutes', 'shinjiru-maintenance' ); ?></span></div>
              <div class="unit"><span class="num cd-seconds">00</span><span class="label"><?php esc_html_e( 'Seconds', 'shinjiru-maintenance' ); ?></span></div>
            </div>
          <?php endif; ?>

          <div class="actions">
            <?php if ( $show_cta && $cta_url ) : ?>
              <a href="<?php echo esc_url( $cta_url ); ?>" class="button"><?php echo esc_html( $cta_text ); ?> <span class="arrow" aria-hidden="true">↗</span></a>
            <?php endif; ?>
            <?php if ( $enable_password ) : ?>
              <button class="button" type="button" onclick="var d=document.querySelector('details.access');if(d){d.open=true;}var inp=document.getElementById('password');if(inp){inp.focus();}"><?php esc_html_e( 'Enter Password', 'shinjiru-maintenance' ); ?> <span class="arrow" aria-hidden="true">↗</span></button>
            <?php endif; ?>
          </div>

          <?php if ( $enable_password ) : ?>
            <details class="access" <?php echo ( 'locked' === $status ) ? 'open' : ''; ?>>
              <summary><?php esc_html_e( 'Have an access password?', 'shinjiru-maintenance' ); ?></summary>
              <form class="gate" method="post" action="">
                <?php wp_nonce_field( 'shinjiru_pass_action', 'shinjiru_pass_nonce' ); ?>
                <label for="password"><?php esc_html_e( 'Access password', 'shinjiru-maintenance' ); ?></label>
                <div class="field">
                  <input id="password" name="shinjiru_password" type="password" autocomplete="current-password" required placeholder="<?php esc_attr_e( 'Enter site password', 'shinjiru-maintenance' ); ?>">
                  <button type="submit"><?php esc_html_e( 'Continue', 'shinjiru-maintenance' ); ?></button>
                </div>
                <p class="hint" role="status"><?php esc_html_e( 'Enter the assigned password to unlock access for 24 hours.', 'shinjiru-maintenance' ); ?></p>
              </form>
            </details>
          <?php endif; ?>
        </div>

        <aside class="side-note">
          <p><?php esc_html_e( 'Taking a moment to make things better.', 'shinjiru-maintenance' ); ?></p>
          <p><?php esc_html_e( 'We appreciate you stopping by.', 'shinjiru-maintenance' ); ?></p>
        </aside>
      </div>
    </main>

    <aside class="art" aria-label="<?php esc_attr_e( 'Abstract architectural illustration', 'shinjiru-maintenance' ); ?>">
      <svg viewBox="0 0 600 900" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <rect width="600" height="900" fill="#d6dece"/>
        <path d="M110 900V360a190 190 0 0 1 380 0v540" fill="#b5c1ac"/>
        <path d="M175 900V365a125 125 0 0 1 250 0v535" fill="#80957e"/>
        <path d="M235 900V370a65 65 0 0 1 130 0v530" fill="#344d40"/>
        <path d="M0 735h600M0 790h600M0 845h600" stroke="#eef0e7" stroke-width="1"/>
        <path d="M300 900V435" stroke="#b9c6ae"/>
      </svg>
      <p><span><?php esc_html_e( 'A work in progress.', 'shinjiru-maintenance' ); ?></span><span>01 / 06</span></p>
    </aside>

    <footer class="bottom">
      <p><?php echo wp_kses_post( $footer_text ); ?></p>
      <p><?php esc_html_e( 'Thank you for your patience.', 'shinjiru-maintenance' ); ?></p>
    </footer>
  </div>
  <?php wp_footer(); ?>
</body>
</html>
