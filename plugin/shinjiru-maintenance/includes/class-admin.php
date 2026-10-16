<?php
/**
 * Admin Interface & Settings Screens.
 *
 * @package Shinjiru\Maintenance
 */

namespace Shinjiru\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	const MENU_SLUG = 'shinjiru-maintenance';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'register_admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_actions' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_action( 'admin_bar_menu', [ __CLASS__, 'add_admin_bar_indicator' ], 100 );
	}

	/**
	 * Add menu item under Settings.
	 */
	public static function register_admin_menu() {
		add_options_page(
			__( 'Shinjiru Maintenance', 'shinjiru-maintenance' ),
			__( 'Shinjiru Maintenance', 'shinjiru-maintenance' ),
			'manage_options',
			self::MENU_SLUG,
			[ __CLASS__, 'render_admin_page' ]
		);
	}

	/**
	 * Enqueue admin scripts & styles exclusively on plugin settings page.
	 *
	 * @param string $hook_suffix
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( 'settings_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'shinjiru-maintenance-admin',
			SHINJIRU_MAINTENANCE_URL . 'assets/css/admin.css',
			[ 'wp-color-picker' ],
			SHINJIRU_MAINTENANCE_VERSION
		);

		wp_enqueue_script(
			'shinjiru-maintenance-admin',
			SHINJIRU_MAINTENANCE_URL . 'assets/js/admin.js',
			[ 'jquery', 'wp-color-picker' ],
			SHINJIRU_MAINTENANCE_VERSION,
			true
		);

		wp_localize_script( 'shinjiru-maintenance-admin', 'shinjiruMaintData', [
			'mediaTitle'       => __( 'Select or Upload Logo', 'shinjiru-maintenance' ),
			'mediaButton'      => __( 'Use this Logo', 'shinjiru-maintenance' ),
			'confirmReset'     => __( 'Are you sure you want to reset all settings to defaults? This action cannot be undone.', 'shinjiru-maintenance' ),
			'confirmRegen'     => __( 'Regenerating the secret token will revoke all existing bypass URLs. Continue?', 'shinjiru-maintenance' ),
		] );
	}

	/**
	 * Add Admin Bar status indicator.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 */
	public static function add_admin_bar_indicator( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = Config::get_settings();
		$status = $settings['status'];
		if ( 'off' === $status && ! empty( $settings['schedule_enabled'] ) ) {
			if ( Scheduler::is_schedule_active( $settings ) ) {
				$status = 'maintenance';
			}
		}

		if ( 'off' === $status ) {
			return;
		}

		$label = ( 'maintenance' === $status ) ? __( 'Maintenance Active', 'shinjiru-maintenance' ) : __( 'Coming Soon Active', 'shinjiru-maintenance' );
		$class = ( 'maintenance' === $status ) ? 'shinjiru-bar-maintenance' : 'shinjiru-bar-comingsoon';

		$wp_admin_bar->add_node( [
			'id'    => 'shinjiru-maint-status',
			'title' => '<span class="ab-icon dashicons dashicons-shield"></span> <span class="ab-label ' . esc_attr( $class ) . '">' . esc_html( $label ) . '</span>',
			'href'  => admin_url( 'options-general.php?page=' . self::MENU_SLUG ),
			'meta'  => [
				'title' => __( 'Manage Shinjiru Maintenance Mode', 'shinjiru-maintenance' ),
			],
		] );
	}

	/**
	 * Handle admin POST/GET actions.
	 */
	public static function handle_actions() {
		if ( ! isset( $_GET['page'] ) || self::MENU_SLUG !== $_GET['page'] ) {
			return;
		}

		// Handle Settings Export
		if ( isset( $_GET['action'] ) && 'export_settings' === $_GET['action'] ) {
			Portability::export_json();
			return;
		}

		// Handle Settings Import
		if ( isset( $_POST['shinjiru_import_submit'] ) ) {
			$result = Portability::import_json();
			set_transient( 'shinjiru_maint_notice', [ 'type' => $result[0], 'msg' => $result[1] ], 45 );
			wp_safe_redirect( admin_url( 'options-general.php?page=' . self::MENU_SLUG . '&tab=tools' ) );
			exit;
		}

		// Handle Reset to Defaults
		if ( isset( $_POST['shinjiru_reset_defaults'] ) ) {
			check_admin_referer( 'shinjiru_maint_reset', 'shinjiru_reset_nonce' );
			if ( current_user_can( 'manage_options' ) ) {
				Config::update_settings( Config::get_defaults() );
				set_transient( 'shinjiru_maint_notice', [ 'type' => 'success', 'msg' => __( 'All settings have been restored to default values.', 'shinjiru-maintenance' ) ], 45 );
			}
			wp_safe_redirect( admin_url( 'options-general.php?page=' . self::MENU_SLUG ) );
			exit;
		}

		// Handle Save Settings
		if ( isset( $_POST['shinjiru_save_settings'] ) ) {
			check_admin_referer( 'shinjiru_maint_save', 'shinjiru_save_nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Unauthorized.', 'shinjiru-maintenance' ), 403 );
			}

			$raw_data = isset( $_POST['shinjiru_settings'] ) ? (array) $_POST['shinjiru_settings'] : [];
			if ( isset( $_POST['regenerate_token'] ) ) {
				$raw_data['regenerate_token'] = 1;
			}
			if ( ! empty( $_POST['new_password'] ) ) {
				$raw_data['new_password'] = sanitize_text_field( wp_unslash( $_POST['new_password'] ) );
			}

			Config::update_settings( $raw_data );

			$current_tab = isset( $_POST['current_tab'] ) ? sanitize_key( $_POST['current_tab'] ) : 'general';
			set_transient( 'shinjiru_maint_notice', [ 'type' => 'success', 'msg' => __( 'Settings updated successfully.', 'shinjiru-maintenance' ) ], 45 );
			wp_safe_redirect( admin_url( 'options-general.php?page=' . self::MENU_SLUG . '&tab=' . $current_tab ) );
			exit;
		}
	}

	/**
	 * Render main admin settings screen.
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = Config::get_settings();
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		$tabs = [
			'general'   => [ 'label' => __( 'Status & Mode', 'shinjiru-maintenance' ), 'icon' => 'dashicons-admin-generic' ],
			'design'    => [ 'label' => __( 'Design & Templates', 'shinjiru-maintenance' ), 'icon' => 'dashicons-art' ],
			'content'   => [ 'label' => __( 'Content & Messaging', 'shinjiru-maintenance' ), 'icon' => 'dashicons-edit' ],
			'access'    => [ 'label' => __( 'Access & Security', 'shinjiru-maintenance' ), 'icon' => 'dashicons-lock' ],
			'schedule'  => [ 'label' => __( 'Schedule', 'shinjiru-maintenance' ), 'icon' => 'dashicons-calendar-alt' ],
			'tools'     => [ 'label' => __( 'Import / Export & Reset', 'shinjiru-maintenance' ), 'icon' => 'dashicons-database-export' ],
			'help'      => [ 'label' => __( 'Help & Docs', 'shinjiru-maintenance' ), 'icon' => 'dashicons-editor-help' ],
		];

		if ( ! array_key_exists( $current_tab, $tabs ) ) {
			$current_tab = 'general';
		}

		$notice = get_transient( 'shinjiru_maint_notice' );
		if ( $notice ) {
			delete_transient( 'shinjiru_maint_notice' );
		}

		$logo_path = SHINJIRU_MAINTENANCE_URL . 'assets/images/shinjiru-logo.png';
		$site_url = home_url( '/' );
		$bypass_url = add_query_arg( 'shinjiru_bypass', $settings['bypass_token'], $site_url );
		$preview_url = add_query_arg( 'shinjiru_preview', $settings['template'], $site_url );

		?>
		<div class="wrap shinjiru-wrap">
			<div class="shinjiru-header">
				<div class="shinjiru-header-brand">
					<img src="<?php echo esc_url( $logo_path ); ?>" alt="Shinjiru Logo" class="shinjiru-header-logo" />
					<div class="shinjiru-header-title">
						<h1><?php esc_html_e( 'Shinjiru Maintenance', 'shinjiru-maintenance' ); ?> <span class="shinjiru-version">v<?php echo esc_html( SHINJIRU_MAINTENANCE_VERSION ); ?></span></h1>
						<p class="shinjiru-tagline"><?php esc_html_e( 'Enterprise-grade website maintenance, coming-soon, and access management.', 'shinjiru-maintenance' ); ?></p>
					</div>
				</div>
				<div class="shinjiru-header-actions">
					<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="button button-secondary shinjiru-btn-preview">
						<span class="dashicons dashicons-external"></span> <?php esc_html_e( 'Live Preview', 'shinjiru-maintenance' ); ?>
					</a>
				</div>
			</div>

			<?php if ( ! empty( $notice ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
					<p><?php echo esc_html( $notice['msg'] ); ?></p>
				</div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper shinjiru-tabs">
				<?php foreach ( $tabs as $tab_key => $tab_data ) : ?>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . self::MENU_SLUG . '&tab=' . $tab_key ) ); ?>" class="nav-tab <?php echo ( $current_tab === $tab_key ) ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons <?php echo esc_attr( $tab_data['icon'] ); ?>"></span>
						<?php echo esc_html( $tab_data['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="shinjiru-content-card">
				<?php if ( 'tools' === $current_tab ) : ?>
					<?php self::render_tab_tools( $settings ); ?>
				<?php elseif ( 'help' === $current_tab ) : ?>
					<?php self::render_tab_help( $settings ); ?>
				<?php else : ?>
					<form method="post" action="" id="shinjiru-settings-form">
						<?php wp_nonce_field( 'shinjiru_maint_save', 'shinjiru_save_nonce' ); ?>
						<input type="hidden" name="current_tab" value="<?php echo esc_attr( $current_tab ); ?>" />

						<?php
						switch ( $current_tab ) {
							case 'general':
								self::render_tab_general( $settings, $bypass_url );
								break;
							case 'design':
								self::render_tab_design( $settings );
								break;
							case 'content':
								self::render_tab_content( $settings );
								break;
							case 'access':
								self::render_tab_access( $settings, $bypass_url );
								break;
							case 'schedule':
								self::render_tab_schedule( $settings );
								break;
						}
						?>

						<div class="shinjiru-footer-bar">
							<button type="submit" name="shinjiru_save_settings" class="button button-primary button-hero">
								<?php esc_html_e( 'Save Changes', 'shinjiru-maintenance' ); ?>
							</button>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Tab: General / Mode.
	 */
	protected static function render_tab_general( array $settings, $bypass_url ) {
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Mode & Interception Status', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Select whether your website is currently live, undergoing maintenance, or in pre-launch coming soon mode.', 'shinjiru-maintenance' ); ?></p>

			<div class="shinjiru-mode-grid">
				<label class="shinjiru-mode-card <?php echo ( 'off' === $settings['status'] ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="shinjiru_settings[status]" value="off" <?php checked( $settings['status'], 'off' ); ?> />
					<div class="shinjiru-mode-content">
						<span class="dashicons dashicons-yes-alt shinjiru-mode-icon text-success"></span>
						<h3><?php esc_html_e( 'Disabled (Live)', 'shinjiru-maintenance' ); ?></h3>
						<p><?php esc_html_e( 'The site is accessible normally to all public visitors and search engine bots.', 'shinjiru-maintenance' ); ?></p>
					</div>
				</label>

				<label class="shinjiru-mode-card <?php echo ( 'maintenance' === $settings['status'] ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="shinjiru_settings[status]" value="maintenance" <?php checked( $settings['status'], 'maintenance' ); ?> />
					<div class="shinjiru-mode-content">
						<span class="dashicons dashicons-hammer shinjiru-mode-icon text-warning"></span>
						<h3><?php esc_html_e( 'Maintenance Mode', 'shinjiru-maintenance' ); ?></h3>
						<p><?php esc_html_e( 'Returns HTTP 503 Service Unavailable with Retry-After header. Informs search engines the site is temporarily down for maintenance.', 'shinjiru-maintenance' ); ?></p>
					</div>
				</label>

				<label class="shinjiru-mode-card <?php echo ( 'coming_soon' === $settings['status'] ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="shinjiru_settings[status]" value="coming_soon" <?php checked( $settings['status'], 'coming_soon' ); ?> />
					<div class="shinjiru-mode-content">
						<span class="dashicons dashicons-flag shinjiru-mode-icon text-info"></span>
						<h3><?php esc_html_e( 'Coming Soon Mode', 'shinjiru-maintenance' ); ?></h3>
						<p><?php esc_html_e( 'Returns HTTP 200 OK. Ideal for pre-launch announcements, teaser landing pages, and early audience building.', 'shinjiru-maintenance' ); ?></p>
					</div>
				</label>
			</div>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Search Engine & HTTP Response Settings', 'shinjiru-maintenance' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="shinjiru_retry_after"><?php esc_html_e( 'Maintenance Retry-After (seconds)', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="number" id="shinjiru_retry_after" name="shinjiru_settings[retry_after]" value="<?php echo esc_attr( $settings['retry_after'] ); ?>" min="60" max="86400" step="60" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Sent as the Retry-After header when in Maintenance Mode (default: 3600 seconds / 1 hour).', 'shinjiru-maintenance' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Coming Soon Indexing', 'shinjiru-maintenance' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="shinjiru_settings[coming_soon_index]" value="1" <?php checked( ! empty( $settings['coming_soon_index'] ) ); ?> />
							<?php esc_html_e( 'Allow search engines to index the Coming Soon page (removes noindex meta tag)', 'shinjiru-maintenance' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Recommended if you want Google to index your pre-launch page and brand title.', 'shinjiru-maintenance' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Tab: Design & Templates.
	 */
	protected static function render_tab_design( array $settings ) {
		$templates = Template_Engine::get_templates();
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Choose a Designer Template', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Select one of our 6 original, fully responsive, and accessible templates. Click Preview to view any template in real-time.', 'shinjiru-maintenance' ); ?></p>

			<div class="shinjiru-template-grid">
				<?php foreach ( $templates as $id => $tpl ) : 
					$is_active = ( $settings['template'] === $id );
					$preview_url = add_query_arg( 'shinjiru_preview', $id, home_url( '/' ) );
				?>
					<div class="shinjiru-tpl-card <?php echo $is_active ? 'is-active' : ''; ?>">
						<div class="shinjiru-tpl-preview-header">
							<span class="shinjiru-tpl-badge"><?php echo esc_html( $tpl['name'] ); ?></span>
							<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Preview', 'shinjiru-maintenance' ); ?></a>
						</div>
						<div class="shinjiru-tpl-body">
							<p><?php echo esc_html( $tpl['description'] ); ?></p>
							<label class="button <?php echo $is_active ? 'button-primary' : 'button-secondary'; ?> shinjiru-tpl-select-btn">
								<input type="radio" name="shinjiru_settings[template]" value="<?php echo esc_attr( $id ); ?>" <?php checked( $is_active ); ?> style="display:none;" />
								<span class="shinjiru-select-label"><?php echo $is_active ? esc_html__( 'Active Template', 'shinjiru-maintenance' ) : esc_html__( 'Use This Template', 'shinjiru-maintenance' ); ?></span>
							</label>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Brand Logo & Custom Styling', 'shinjiru-maintenance' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Brand Logo', 'shinjiru-maintenance' ); ?></th>
					<td>
						<div class="shinjiru-logo-uploader">
							<input type="hidden" id="shinjiru_logo_url" name="shinjiru_settings[logo_url]" value="<?php echo esc_attr( $settings['logo_url'] ); ?>" />
							<input type="hidden" id="shinjiru_logo_id" name="shinjiru_settings[logo_id]" value="<?php echo esc_attr( $settings['logo_id'] ); ?>" />
							<div id="shinjiru_logo_preview_wrap" class="<?php echo empty( $settings['logo_url'] ) ? 'is-empty' : ''; ?>">
								<?php if ( ! empty( $settings['logo_url'] ) ) : ?>
									<img src="<?php echo esc_url( $settings['logo_url'] ); ?>" alt="Logo Preview" id="shinjiru_logo_img" />
								<?php else : ?>
									<img src="" alt="Logo Preview" id="shinjiru_logo_img" style="display:none;" />
									<span class="shinjiru-logo-placeholder"><?php esc_html_e( 'No logo selected (defaulting to clean text branding)', 'shinjiru-maintenance' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="shinjiru-logo-controls">
								<button type="button" class="button button-secondary" id="shinjiru_upload_logo_btn">
									<?php esc_html_e( 'Choose from Media Library', 'shinjiru-maintenance' ); ?>
								</button>
								<button type="button" class="button button-link-delete" id="shinjiru_remove_logo_btn" style="<?php echo empty( $settings['logo_url'] ) ? 'display:none;' : ''; ?>">
									<?php esc_html_e( 'Remove Logo', 'shinjiru-maintenance' ); ?>
								</button>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_custom_css"><?php esc_html_e( 'Custom CSS', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<textarea id="shinjiru_custom_css" name="shinjiru_settings[custom_css]" rows="6" class="large-text code"><?php echo esc_textarea( $settings['custom_css'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optional. Add custom CSS rules to fine-tune typography, colors, or layout. Tags will be stripped automatically.', 'shinjiru-maintenance' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Tab: Content & Messaging.
	 */
	protected static function render_tab_content( array $settings ) {
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Headings & Messaging', 'shinjiru-maintenance' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="shinjiru_page_title"><?php esc_html_e( 'Browser Tab Title', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="text" id="shinjiru_page_title" name="shinjiru_settings[page_title]" value="<?php echo esc_attr( $settings['page_title'] ); ?>" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_headline"><?php esc_html_e( 'Main Heading', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="text" id="shinjiru_headline" name="shinjiru_settings[headline]" value="<?php echo esc_attr( $settings['headline'] ); ?>" class="large-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_description"><?php esc_html_e( 'Body Message', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<textarea id="shinjiru_description" name="shinjiru_settings[description]" rows="4" class="large-text"><?php echo esc_textarea( $settings['description'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Explain the purpose of the maintenance or upcoming launch. Basic formatting is allowed.', 'shinjiru-maintenance' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_footer_text"><?php esc_html_e( 'Footer Text', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="text" id="shinjiru_footer_text" name="shinjiru_settings[footer_text]" value="<?php echo esc_attr( $settings['footer_text'] ); ?>" class="large-text" />
					</td>
				</tr>
			</table>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Call-to-Action (CTA) Button', 'shinjiru-maintenance' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable CTA Button', 'shinjiru-maintenance' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="shinjiru_settings[show_cta]" value="1" <?php checked( ! empty( $settings['show_cta'] ) ); ?> id="shinjiru_show_cta" />
							<?php esc_html_e( 'Display an action button on the maintenance page', 'shinjiru-maintenance' ); ?>
						</label>
					</td>
				</tr>
				<tr class="shinjiru-cta-row">
					<th scope="row"><label for="shinjiru_cta_text"><?php esc_html_e( 'Button Text', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="text" id="shinjiru_cta_text" name="shinjiru_settings[cta_text]" value="<?php echo esc_attr( $settings['cta_text'] ); ?>" class="regular-text" />
					</td>
				</tr>
				<tr class="shinjiru-cta-row">
					<th scope="row"><label for="shinjiru_cta_url"><?php esc_html_e( 'Button Destination URL', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="url" id="shinjiru_cta_url" name="shinjiru_settings[cta_url]" value="<?php echo esc_attr( $settings['cta_url'] ); ?>" class="large-text" placeholder="https://example.com/contact" />
					</td>
				</tr>
			</table>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Launch Countdown Timer', 'shinjiru-maintenance' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Countdown', 'shinjiru-maintenance' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="shinjiru_settings[show_countdown]" value="1" <?php checked( ! empty( $settings['show_countdown'] ) ); ?> id="shinjiru_show_countdown" />
							<?php esc_html_e( 'Display an active countdown timer counting down to launch', 'shinjiru-maintenance' ); ?>
						</label>
					</td>
				</tr>
				<tr class="shinjiru-countdown-row">
					<th scope="row"><label for="shinjiru_countdown_target"><?php esc_html_e( 'Target Date & Time', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="datetime-local" id="shinjiru_countdown_target" name="shinjiru_settings[countdown_target]" value="<?php echo esc_attr( $settings['countdown_target'] ); ?>" class="regular-text" />
						<p class="description">
							<?php 
							$tz_name = wp_timezone_string();
							/* translators: %s: site timezone string */
							printf( esc_html__( 'Calculated according to your WordPress site timezone: %s', 'shinjiru-maintenance' ), '<strong>' . esc_html( $tz_name ) . '</strong>' ); 
							?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Social Media Profiles', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Optionally display links to your official social profiles in the footer.', 'shinjiru-maintenance' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="shinjiru_social_x">X / Twitter</label></th>
					<td><input type="url" id="shinjiru_social_x" name="shinjiru_settings[social_x]" value="<?php echo esc_attr( $settings['social_x'] ); ?>" class="regular-text" placeholder="https://x.com/username" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_social_linkedin">LinkedIn</label></th>
					<td><input type="url" id="shinjiru_social_linkedin" name="shinjiru_settings[social_linkedin]" value="<?php echo esc_attr( $settings['social_linkedin'] ); ?>" class="regular-text" placeholder="https://linkedin.com/company/profile" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_social_facebook">Facebook</label></th>
					<td><input type="url" id="shinjiru_social_facebook" name="shinjiru_settings[social_facebook]" value="<?php echo esc_attr( $settings['social_facebook'] ); ?>" class="regular-text" placeholder="https://facebook.com/page" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_social_instagram">Instagram</label></th>
					<td><input type="url" id="shinjiru_social_instagram" name="shinjiru_settings[social_instagram]" value="<?php echo esc_attr( $settings['social_instagram'] ); ?>" class="regular-text" placeholder="https://instagram.com/profile" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="shinjiru_social_github">GitHub</label></th>
					<td><input type="url" id="shinjiru_social_github" name="shinjiru_settings[social_github]" value="<?php echo esc_attr( $settings['social_github'] ); ?>" class="regular-text" placeholder="https://github.com/organization" /></td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Tab: Access & Security.
	 */
	protected static function render_tab_access( array $settings, $bypass_url ) {
		$all_roles = wp_roles()->roles;
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Secret Bypass URL', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Share this secure URL with clients, stakeholders, or reviewers so they can view the live website without having a WordPress account.', 'shinjiru-maintenance' ); ?></p>

			<div class="shinjiru-bypass-box">
				<div class="shinjiru-bypass-input-group">
					<input type="text" readonly value="<?php echo esc_url( $bypass_url ); ?>" id="shinjiru_bypass_url_field" class="large-text code" />
					<button type="button" class="button button-secondary" id="shinjiru_copy_bypass_btn"><?php esc_html_e( 'Copy Link', 'shinjiru-maintenance' ); ?></button>
				</div>
				<div class="shinjiru-bypass-actions">
					<label>
						<input type="checkbox" name="regenerate_token" value="1" id="shinjiru_regen_token_chk" />
						<?php esc_html_e( 'Revoke and regenerate secret bypass token upon saving', 'shinjiru-maintenance' ); ?>
					</label>
				</div>
			</div>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Front-End Password Protection', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Provide a visitor password gate directly on the maintenance screen.', 'shinjiru-maintenance' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Password Gate', 'shinjiru-maintenance' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="shinjiru_settings[enable_password]" value="1" <?php checked( ! empty( $settings['enable_password'] ) ); ?> />
							<?php esc_html_e( 'Show a password entry form on the maintenance page', 'shinjiru-maintenance' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="new_password"><?php esc_html_e( 'Set New Password', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="password" id="new_password" name="new_password" value="" class="regular-text" autocomplete="new-password" />
						<p class="description">
							<?php if ( ! empty( $settings['password_hash'] ) ) : ?>
								<span class="dashicons dashicons-yes text-success"></span> <?php esc_html_e( 'A secure hashed password is currently active. Leave blank to keep existing password.', 'shinjiru-maintenance' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'No password set yet.', 'shinjiru-maintenance' ); ?>
							<?php endif; ?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Allowed WordPress Roles', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Logged-in users with these roles will automatically bypass the maintenance screen and view the live website.', 'shinjiru-maintenance' ); ?></p>
			<fieldset>
				<?php foreach ( $all_roles as $role_key => $role_details ) : 
					$is_checked = in_array( $role_key, (array) $settings['allowed_roles'], true );
					$is_admin = ( 'administrator' === $role_key );
				?>
					<label style="display:block; margin-bottom: 6px;">
						<input type="checkbox" name="shinjiru_settings[allowed_roles][]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( $is_checked || $is_admin ); ?> <?php disabled( $is_admin ); ?> />
						<strong><?php echo esc_html( translate_user_role( $role_details['name'] ) ); ?></strong>
						<?php if ( $is_admin ) : ?>
							<em>(<?php esc_html_e( 'Always allowed to prevent administrator lockouts', 'shinjiru-maintenance' ); ?>)</em>
						<?php endif; ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'IP Address Allowlist', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Enter IP addresses or CIDR subnet blocks (one per line) that should always bypass maintenance mode. Supports IPv4 and IPv6.', 'shinjiru-maintenance' ); ?></p>
			<textarea name="shinjiru_settings[ip_allowlist]" rows="5" class="large-text code" placeholder="192.168.1.1&#10;10.0.0.0/24&#10;2001:db8::1"><?php echo esc_textarea( $settings['ip_allowlist'] ); ?></textarea>
			<p class="description">
				<?php 
				$current_ip = Access_Manager::get_visitor_ip();
				/* translators: %s: visitor current IP */
				printf( esc_html__( 'Your detected IP is: %s', 'shinjiru-maintenance' ), '<code>' . esc_html( $current_ip ) . '</code>' ); 
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Tab: Schedule.
	 */
	protected static function render_tab_schedule( array $settings ) {
		$tz_name = wp_timezone_string();
		$now_formatted = current_datetime()->format( 'Y-m-d H:i:s' );
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Automated Maintenance Schedule', 'shinjiru-maintenance' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Optionally schedule maintenance mode to activate and deactivate automatically between specified dates and times.', 'shinjiru-maintenance' ); ?></p>

			<div class="shinjiru-notice-box">
				<p>
					<strong><?php esc_html_e( 'Current Site Time:', 'shinjiru-maintenance' ); ?></strong> <code><?php echo esc_html( $now_formatted ); ?></code> (<?php echo esc_html( $tz_name ); ?>)
				</p>
			</div>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Schedule', 'shinjiru-maintenance' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="shinjiru_settings[schedule_enabled]" value="1" <?php checked( ! empty( $settings['schedule_enabled'] ) ); ?> id="shinjiru_schedule_enabled" />
							<?php esc_html_e( 'Activate schedule-based maintenance', 'shinjiru-maintenance' ); ?>
						</label>
					</td>
				</tr>
				<tr class="shinjiru-schedule-row">
					<th scope="row"><label for="shinjiru_schedule_start"><?php esc_html_e( 'Start Datetime', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="datetime-local" id="shinjiru_schedule_start" name="shinjiru_settings[schedule_start]" value="<?php echo esc_attr( $settings['schedule_start'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Maintenance begins automatically at this time.', 'shinjiru-maintenance' ); ?></p>
					</td>
				</tr>
				<tr class="shinjiru-schedule-row">
					<th scope="row"><label for="shinjiru_schedule_end"><?php esc_html_e( 'End Datetime', 'shinjiru-maintenance' ); ?></label></th>
					<td>
						<input type="datetime-local" id="shinjiru_schedule_end" name="shinjiru_settings[schedule_end]" value="<?php echo esc_attr( $settings['schedule_end'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Maintenance ends and the site returns live automatically after this time.', 'shinjiru-maintenance' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Tab: Tools & Portability.
	 */
	protected static function render_tab_tools( array $settings ) {
		$export_url = wp_nonce_url( admin_url( 'options-general.php?page=' . self::MENU_SLUG . '&action=export_settings' ), 'shinjiru_maint_export', 'shinjiru_export_nonce' );
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Export Settings', 'shinjiru-maintenance' ); ?></h2>
			<p><?php esc_html_e( 'Download a clean, versioned JSON backup of your Shinjiru Maintenance settings. Sensitive secrets (passwords and active bypass tokens) are excluded for safety.', 'shinjiru-maintenance' ); ?></p>
			<a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Settings (JSON)', 'shinjiru-maintenance' ); ?>
			</a>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Import Settings', 'shinjiru-maintenance' ); ?></h2>
			<p><?php esc_html_e( 'Upload a previously exported Shinjiru Maintenance JSON configuration file.', 'shinjiru-maintenance' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="">
				<?php wp_nonce_field( 'shinjiru_maint_import', 'shinjiru_import_nonce' ); ?>
				<input type="file" name="shinjiru_import_file" accept=".json" required />
				<p>
					<button type="submit" name="shinjiru_import_submit" class="button button-secondary">
						<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import Settings', 'shinjiru-maintenance' ); ?>
					</button>
				</p>
			</form>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Reset to Defaults', 'shinjiru-maintenance' ); ?></h2>
			<p><?php esc_html_e( 'Restore all settings and templates back to factory default values. This will disable maintenance mode immediately.', 'shinjiru-maintenance' ); ?></p>
			<form method="post" action="" onsubmit="return confirm(shinjiruMaintData.confirmReset);">
				<?php wp_nonce_field( 'shinjiru_maint_reset', 'shinjiru_reset_nonce' ); ?>
				<button type="submit" name="shinjiru_reset_defaults" class="button button-link-delete">
					<?php esc_html_e( 'Reset All Settings to Defaults', 'shinjiru-maintenance' ); ?>
				</button>
			</form>
		</div>

		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Uninstallation Data Retention Policy', 'shinjiru-maintenance' ); ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'shinjiru_maint_save', 'shinjiru_save_nonce' ); ?>
				<input type="hidden" name="current_tab" value="tools" />
				<label>
					<input type="checkbox" name="shinjiru_settings[delete_on_uninstall]" value="1" <?php checked( ! empty( $settings['delete_on_uninstall'] ) ); ?> />
					<?php esc_html_e( 'Completely delete all Shinjiru Maintenance settings and data when this plugin is uninstalled', 'shinjiru-maintenance' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'By default, WordPress preserves plugin options upon uninstallation. Check this box if you want a 100% clean uninstall that leaves no database traces.', 'shinjiru-maintenance' ); ?></p>
				<p>
					<button type="submit" name="shinjiru_save_settings" class="button button-secondary">
						<?php esc_html_e( 'Update Data Policy', 'shinjiru-maintenance' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render Tab: Help & Docs.
	 */
	protected static function render_tab_help( array $settings ) {
		?>
		<div class="shinjiru-section">
			<h2><?php esc_html_e( 'Frequently Asked Questions & Usage Guide', 'shinjiru-maintenance' ); ?></h2>
			
			<div class="shinjiru-faq-item">
				<h3><?php esc_html_e( 'How do I let my clients preview the site while it is under maintenance?', 'shinjiru-maintenance' ); ?></h3>
				<p><?php esc_html_e( 'Go to the "Access & Security" tab and copy your unique Secret Bypass URL. Anyone who visits that link will be granted a temporary authenticated access session without having to log in.', 'shinjiru-maintenance' ); ?></p>
			</div>

			<div class="shinjiru-faq-item">
				<h3><?php esc_html_e( 'Will Maintenance Mode hurt my Google rankings?', 'shinjiru-maintenance' ); ?></h3>
				<p><?php esc_html_e( 'No. When in Maintenance Mode, Shinjiru Maintenance transmits an HTTP 503 Service Unavailable header along with a Retry-After directive and noindex meta tags. This informs search engine crawlers that the site is temporarily down for maintenance and prevents your existing search index from being replaced or de-indexed.', 'shinjiru-maintenance' ); ?></p>
			</div>

			<div class="shinjiru-faq-item">
				<h3><?php esc_html_e( 'What is the difference between Maintenance and Coming Soon modes?', 'shinjiru-maintenance' ); ?></h3>
				<p><?php esc_html_e( 'Coming Soon mode returns an HTTP 200 OK status, making it ideal for newly registered domains and pre-launch teaser sites where you want search engines to discover and index your brand. Maintenance Mode returns HTTP 503, which tells crawlers not to cache or index the temporary maintenance notice.', 'shinjiru-maintenance' ); ?></p>
			</div>

			<div class="shinjiru-faq-item">
				<h3><?php esc_html_e( 'Can I get locked out of my WordPress dashboard?', 'shinjiru-maintenance' ); ?></h3>
				<p><?php esc_html_e( 'Never. Shinjiru Maintenance is engineered with strict lockout prevention: the WordPress login page (wp-login.php) and wp-admin areas are never intercepted. In addition, users with the manage_options capability always retain bypass rights.', 'shinjiru-maintenance' ); ?></p>
			</div>

			<div class="shinjiru-faq-item">
				<h3><?php esc_html_e( 'Does this plugin load external fonts or send telemetry?', 'shinjiru-maintenance' ); ?></h3>
				<p><?php esc_html_e( 'No. In full compliance with WordPress.org security, GDPR, and privacy best practices, Shinjiru Maintenance does not load external Google Fonts, does not make external CDN calls, and does not collect or transmit telemetry of any kind.', 'shinjiru-maintenance' ); ?></p>
			</div>
		</div>
		<?php
	}
}
