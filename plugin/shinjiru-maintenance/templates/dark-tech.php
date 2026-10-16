<?php
/**
 * After Hours / Dark Tech Template.
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
<body class="dark-tech">
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
            <p class="demo-note"><?php esc_html_e( 'Expected service launch / maintenance completion time', 'shinjiru-maintenance' ); ?></p>
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

    <footer class="bottom">
      <p><?php echo wp_kses_post( $footer_text ); ?></p>
      <p><?php esc_html_e( 'Thank you for your patience.', 'shinjiru-maintenance' ); ?></p>
    </footer>
  </div>
  <?php wp_footer(); ?>
</body>
</html>
