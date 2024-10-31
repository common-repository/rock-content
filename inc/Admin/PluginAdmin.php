<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rockcontent.com/
 * @since      1.0.0
 *
 * @package    Inc
 * @subpackage Inc/Admin
 */

namespace RockContent\Admin;

use RockContent\Integrations\Requirements;
use RockContent\Admin\NotificationAdmin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Inc
 * @subpackage Inc/Admin
 * @author     Rock Content <plugin@rockcontent.com>
 */
class PluginAdmin extends NotificationAdmin {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The RC2 platform endpoint
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string $rc2_endpoint The RC2 platform endpoint for integration.
	 */
	private $rc2_endpoint;

	/**
	 * The minimum php version
	 *
	 * @var string
	 */
	public $minimum_php_version;

	/**
	 * The requirements
	 *
	 * @var Requirements
	 */
	public $requirements;

	/**
	 * The notification utils
	 *
	 * @since    3.0.0
	 * @access   public
	 * @var      string $notification_admin.
	 */
	public $notification_admin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name ) {
		$this->minimum_php_version = '5.3.3';
		$this->plugin_name         = $plugin_name;
		$this->version             = RCP_VERSION;
		$this->requirements        = new Requirements( $this->minimum_php_version );
		$this->notification_admin  = new NotificationAdmin();
		$this->rc2_endpoint        = 'https://app.rockcontent.com';
		if ( RCP_DEBUG ) {
			$this->rc2_endpoint = 'http://localhost:3020';
		} else {
			$this->rc2_endpoint = 'https://app.rockcontent.com';
		}
	}

	/**
	 * Shows "activation not finished" flash message
	 *
	 * @since 1.0.0
	 */
	public function activation_notice() {
		$class   = 'notice notice-error';
		$message = __(
			'<strong>Atenção!</strong> Você ainda não integrou sua plataforma Rock Content.',
			'sample-text-domain'
		);
		$url     = admin_url( 'admin.php?page=rcp_plugin_options' );
		$link    = "<a href='$url'>Clique aqui</a> para integrar.";
		$html    = '<div class=' . $class . '><p>' . $message . ' ' . $link . '</p></div>';

		echo $html;
	}

	/**
	 * Show notices warning about the requirements necessaries for run the integration
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function requirements_notice() {
		$class = 'notice notice-error';

		if ( ! $this->requirements->valid_php_version() ) {
			$message = '<strong>Atenção!</strong> Você está utilizando a versão <strong>' . PHP_VERSION . '</strong> porém para integrar com a Rock Content ela precisa ser maior do que a versão <strong>' . $this->minimum_php_version . '</strong>';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}

		if ( ! $this->requirements->open_ssl_enabled() ) {
			$message = '<strong>Atenção!</strong> Seu blog não possui a extensão <strong>OpenSSL</strong>. Para integrar com a plataforma Rock Content é necessário habilitar esta extensão.';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}

	/**
	 * Function responsible for add the side menu
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function rcp_plugin_menu() {
		$tag_notification_count = "<p class='rcp-notification-noclient'> </p>";
		if ( get_option( 'rcp_stage_is_present' ) == true ) {
			$count                  = $this->get_notification_count();
			$tag_notification_count = "<p id='notification-count-side' class='rcp-notification-count'>$count</p>";
		}
		add_menu_page(
			'',
			'Rock Content' . $tag_notification_count,
			'administrator',
			'rcp_plugin_options',
			array(
				$this,
				'rcp_plugin_display',
			),
			RCP_NAME_URL . 'assets/admin/img/rockcontent.png'
		);
	}

	/**
	 * Return number of notifications
	 *
	 * @return int|string
	 *
	 * @since 3.0.0
	 */
	public function get_notification_count() {
		global $wpdb;
		$table_name         = $wpdb->prefix . 'rcp_notification';
		$notification_count = $wpdb->get_var( $wpdb->prepare( 'select count(*) from `%1$s` where already_read = 0', $table_name ) );
		$notification_count = $notification_count > 99 ? '+99' : $notification_count;
		return ! empty( $notification_count ) ? $notification_count : '';
	}

	/**
	 * Function responsible for add the top menu and submenus, notification and integration
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar object responsible for manipulate the admin bar.
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function rcp_plugin_menu_top( \WP_Admin_Bar $wp_admin_bar ) {
		$icon_dir               = esc_url( RCP_NAME_URL . 'assets/admin/img/rockcontent.png' );
		$tag_notification_count = '';
		$count                  = $this->get_notification_count();
		if ( get_option( 'rcp_stage_is_present' ) == true ) {
			$tag_notification_count = "<p class='rcp-notification-count'>$count</p>";
		}
		$icon_span          = "<span class='top-icon-rcp' style='background-image:url($icon_dir);' ></span>" .
		$tag_notification_count;
		$page_icon          = '?page=rcp_plugin_options&tab=notifications';
		$page_notifications = '?page=rcp_plugin_options&tab=notifications';
		$page_integration   = '?page=rcp_plugin_options&tab=integracao';
		$wp_admin_bar->add_menu(
			array(
				'id'    => 'rockcontent',
				'title' => $icon_span,
				'href'  => esc_url( $page_icon ),
				'meta'  => array(
					'target' => '_self',
					'title'  => __( 'Rock Content', 'rcp-wp_plugin' ),
					'class'  => 'top-icon-number-rcp',
				),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'rockcontent',
				'id'     => 'rockcontent-notifications',
				'title'  => 'Stage' . $tag_notification_count,
				'href'   => esc_url( $page_notifications ),
				'meta'   => array(
					'target' => '_self',
					'title'  => __( 'Notificações', 'rcp-wp_plugin' ),
					'class'  => 'top-icon-notifications-rcp',
				),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'rockcontent',
				'id'     => 'rockcontent-integration',
				'title'  => __( 'Studio ', 'rcp-wp_plugin' ),
				'href'   => esc_url( $page_integration ),
				'meta'   => array(
					'target' => '_self',
					'title'  => 'Studio',
					'class'  => 'top-icon-notifications-rcp',
				),
			)
		);

	}

	/**
	 * Queue the css and js which will execute in admin panel
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style(
			'rockcontent-styles',
			plugin_dir_url( __FILE__ ) . '../../dist/admin.css',
			array(),
			RCP_VERSION
		);
		wp_enqueue_script(
			'rcp_notifications_script',
			plugin_dir_url( __FILE__ ) . '../../dist/admin.js',
			array( 'jquery' ),
			RCP_VERSION
		);
		wp_localize_script(
			'rcp_notifications_script',
			'ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'rock_content_notification_nonce' ),
			)
		);
	}

	/**
	 * Queue the css and js which will execute in front pages
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function enqueue_front_scripts() {
		wp_enqueue_script(
			'rcp_rock_analytics_script',
			plugin_dir_url( __FILE__ ) . '../../assets/admin/scripts/rockAnalytics.js',
			array( 'jquery' ),
			RCP_VERSION
		);
	}

	/**
	 * Settings page container
	 *
	 * @since 1.0.0
	 *
	 * @since 1.0.4
	 */
	public function rcp_plugin_display() {

		if ( isset( $_GET['rcp_disconnect'] ) && '1' === $_GET['rcp_disconnect'] ) {
			$this->disconnect();
		}

		if ( isset( $_GET['rcp_analytics_enabled'] ) ) {
			if ( '1' === $_GET['rcp_analytics_enabled'] ) {
				$this->connect_rock_analytics();
			} else {
				$this->disconnect_rock_analytics();
			}
		}

		$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'notifications';
		$stage_present = get_option( 'rcp_stage_is_present' );
		?>

	<div class="wrap rcp-page <?php echo ! $stage_present ? 'rcp-page-noclient' : ''; ?>">
	  <h1>
		  <?php echo esc_html( get_admin_page_title() ); ?>
	  </h1>
	  <img 
			src="<?php echo esc_url( RCP_NAME_URL . 'assets/admin/img/logo-rockcontent-blue.png' ); ?>" 
			class="rcp-rock-logo" alt=""
		/> 
	  <h2 class="nav-tab-wrapper rcp-menu-internal">
		<a 
			href="<?php echo esc_url( '?page=rcp_plugin_options&tab=notifications' ); ?>" 
			class="nav-tab rcp-rock-stage <?php echo esc_attr( 'notifications' === $active_tab ? 'rcp-nav-tab-active' : '' ); ?>"
		>
			<?php
			  esc_html_e( 'Stage', 'rcp-wp_plugin' );
			if ( '1' === $stage_present ) {
				?>
					<h5 class='rcp_count_internal'></h5>
			<?php } ?>
		</a>
		<a 
			href="<?php echo esc_url( '?page=rcp_plugin_options&tab=integracao' ); ?>"
			class="nav-tab rcp-rock-stage <?php echo esc_attr( 'integracao' === $active_tab ? 'rcp-nav-tab-active' : '' ); ?>"
		>
			  <?php esc_html_e( 'Studio', 'rcp-wp_plugin' ); ?>
		</a>
		  <?php
			if ( 'integracao' === $active_tab ) {
				if ( ! $this->requirements->valid() ) {
					$this->requirements_notice();
				} else {
					do_settings_sections( 'rcp_integration_options' );

					if ( $this->integrated() ) {
						?>
				<a href="https://app.rockcontent.com/users/sign_in" class="rcp-noclient-button" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Acessar Studio', 'rcp-wp_plugin' ); ?>
				</a>
						<?php

						if ( $this->get_analytics_enabled() ) {
							$this->disconnect_analytics_button();
						} else {
							$this->connect_analytics_button();
						}

						$this->privacy_link();

						$this->disconnect_button();
					} else {
						$this->rc2_integration_form();
					}
				}
			} else {
				do_settings_sections( 'rcp_notifications' );
			}
			?>
	  </h2>
		<?php settings_errors(); ?>
	  <hr/>
	
	</div> 
		<?php
	}

	/**
	 * Checks if rcp_integrated_at is present
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function integrated() {
		$integrated_at = esc_attr( get_option( 'rcp_integrated_at' ) );

		return ! empty( $integrated_at );
	}

	/**
	 * Create the form of the integration with rc2
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function rc2_integration_form() {
		$blog_url = esc_url( get_bloginfo( 'url' ) );
		$token    = esc_attr( get_option( 'rcp_token' ) );
		?>
	<form method="post" action="<?php echo esc_html( $this->rc2_endpoint ); ?>/integrations/rcp_auth">
		<input type="hidden" name="content_integration_configuration[url]" value="<?php echo esc_url( $blog_url ); ?>"/>
		<input type="hidden" name="content_integration_configuration[token]" value="<?php echo esc_attr( $token ); ?>"/>
		<p>
			<input
				type="submit"
				name="submit"
				id="submit"
				class="button rcp-button-integration"
				value="<?php esc_attr_e( 'Integrar com Studio', 'rcp-wp_plugin' ); ?>"
			>
		</p>
	</form>
		<?php
	}

	/**
	 * Button of undo the integration with rc2
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function disconnect_button() {
		$url = admin_url( 'admin.php?page=rcp_plugin_options&rcp_disconnect=1' );
		?>

		<hr/>
		<br/>
		<a href="<?php echo esc_url( $url ); ?>">Desconectar</a>

		<?php
	}

	/**
	 * Button of connect with rock analytics
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function connect_analytics_button() {
		$url = admin_url( 'admin.php?page=rcp_plugin_options&rcp_analytics_enabled=1' );
		?>

		<hr/>
		<br/>
		<a href="<?php echo esc_url( $url ); ?>">Conectar Rock Analytics</a>

		<?php
	}

	/**
	 * Button of disconnect with rock analytics
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function disconnect_analytics_button() {
		$url = admin_url( 'admin.php?page=rcp_plugin_options&rcp_analytics_enabled=0' );
		?>

		<hr/>
		<br/>
		<a href="<?php echo esc_url( $url ); ?>">Desconectar Rock Analytics</a>

		<?php
	}

	/**
	 * Show the link with the privacy political
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function privacy_link() {
		?>

		<hr/>
		<br/>
		<a
			href="<?php echo esc_url( 'https://rockcontent.com/politica-de-privacidade/' ); ?>"
			target="_blank"
		>
		Política de Privacidade
		</a>
		<?php
	}

	/**
	 * Register plugin options for settings page
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function rcp_initialize_layout_options() {
		add_settings_section(
			'layout_settings_section',
			' ',
			array( $this, 'rcp_integration_options_callback' ),
			'rcp_integration_options'
		);

		add_settings_section(
			'layout_settings_section',
			' ',
			array( $this, 'rcp_notifications_callback' ),
			'rcp_notifications'
		);

		register_setting(
			'rcp_integration_options',
			'rcp_integration_options'
		);
	}

	/**
	 * Layout section text helper
	 *
	 * @since 1.0.0
	 */
	public function rcp_integration_options_callback() {
		?>
		<div class="rcp-studio">
			<div class="rcp-studio-integracao">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M6.98019 5.71873C6.85576 6.2773 6.80001 7.02343 6.80001 8.03704V8.68025C6.80001 9.05895 6.58609 9.40516 6.2474 9.57459C5.90871 9.74401 5.50338 9.70759 5.20033 9.48048C4.97062 9.30835 4.68629 9.20652 4.375 9.20652C3.6134 9.20652 3 9.82185 3 10.576C3 11.3301 3.6134 11.9454 4.375 11.9454C4.68629 11.9454 4.97062 11.8436 5.20033 11.6715C5.50338 11.4444 5.90871 11.4079 6.2474 11.5774C6.58609 11.7468 6.80001 12.093 6.80001 12.4717V13.1323C6.80001 14.1459 6.85576 14.892 6.98019 15.4506C7.10206 15.9977 7.27613 16.3019 7.4711 16.4964C7.66626 16.6911 7.97182 16.8652 8.52118 16.987C9.08178 17.1113 9.83025 17.1669 10.8464 17.1669H11.5334C11.9167 17.1669 12.2663 17.386 12.4334 17.7309C12.6005 18.0758 12.5558 18.4859 12.3182 18.7867C12.134 19.0199 12.025 19.312 12.025 19.6321C12.025 20.3863 12.6384 21.0016 13.4 21.0016C14.1616 21.0016 14.775 20.3863 14.775 19.6321C14.775 19.312 14.666 19.0199 14.4818 18.7867C14.2442 18.4859 14.1995 18.0758 14.3666 17.7309C14.5337 17.386 14.8833 17.1669 15.2666 17.1669H15.9536C16.9698 17.1669 17.7182 17.1113 18.2788 16.987C18.8282 16.8652 19.1338 16.6911 19.3289 16.4964C19.5239 16.3019 19.698 15.9977 19.8198 15.4506C19.9095 15.048 19.9635 14.5479 19.9867 13.9205C19.8517 13.937 19.7143 13.9454 19.575 13.9454C17.7132 13.9454 16.2 12.4391 16.2 10.576C16.2 8.71287 17.7132 7.20652 19.575 7.20652C19.714 7.20652 19.8512 7.21496 19.986 7.23135C19.9625 6.6121 19.9087 6.11759 19.8198 5.71873C19.698 5.17164 19.5239 4.86744 19.3289 4.67293C19.1338 4.47822 18.8282 4.30416 18.2788 4.18236C17.7182 4.05806 16.9698 4.00244 15.9536 4.00244H10.8464C9.83025 4.00244 9.08178 4.05806 8.52118 4.18236C7.97182 4.30416 7.66626 4.47822 7.4711 4.67293C7.27613 4.86744 7.10206 5.17164 6.98019 5.71873ZM8.08825 2.22978C8.86346 2.0579 9.78472 2.00244 10.8464 2.00244H15.9536C17.0153 2.00244 17.9366 2.0579 18.7118 2.22978C19.4982 2.40415 20.1945 2.71136 20.7415 3.25706C21.2886 3.80294 21.597 4.49828 21.772 5.28386C21.9444 6.05796 22 6.97766 22 8.03704V8.68025C22 9.05895 21.7861 9.40516 21.4474 9.57458C21.1087 9.74401 20.7034 9.70758 20.4003 9.48048C20.1706 9.30835 19.8863 9.20652 19.575 9.20652C18.8134 9.20652 18.2 9.82185 18.2 10.576C18.2 11.3301 18.8134 11.9454 19.575 11.9454C19.8863 11.9454 20.1706 11.8436 20.4003 11.6715C20.7034 11.4444 21.1087 11.4079 21.4474 11.5774C21.7861 11.7468 22 12.093 22 12.4717V13.1323C22 14.1917 21.9444 15.1114 21.772 15.8855C21.597 16.6711 21.2886 17.3664 20.7415 17.9123C20.1945 18.458 19.4982 18.7652 18.7118 18.9396C18.135 19.0674 17.4774 19.1309 16.7413 19.155C16.7635 19.3109 16.775 19.4702 16.775 19.6321C16.775 21.4952 15.2618 23.0016 13.4 23.0016C11.5382 23.0016 10.025 21.4952 10.025 19.6321C10.025 19.4702 10.0365 19.3109 10.0587 19.155C9.32258 19.1309 8.665 19.0674 8.08825 18.9396C7.30182 18.7652 6.60552 18.458 6.05854 17.9123C5.51138 17.3664 5.20304 16.6711 5.02804 15.8855C4.89968 15.3093 4.83607 14.6524 4.81194 13.9173C4.66882 13.9359 4.52297 13.9454 4.375 13.9454C2.51325 13.9454 1 12.4391 1 10.576C1 8.71287 2.51325 7.20652 4.375 7.20652C4.52317 7.20652 4.66921 7.2161 4.81251 7.23467C4.83708 6.50659 4.90069 5.85552 5.02804 5.28386C5.20304 4.49827 5.51138 3.80294 6.05854 3.25706C6.60552 2.71136 7.30182 2.40415 8.08825 2.22978Z" fill="#2D3648"/>
			</svg>
			<h1><?php esc_html_e( 'Integração', 'rcp-wp_plugin' ); ?></h1>
			</div>
			<p class="rcp-studio-description">
				<?php
					! $this->integrated() ?
					esc_html_e(
						'Integrando seu WordPress com o Studio, é possível automatizar
						a publicação dos seus conteúdos pela plataforma.',
						'rcp-wp_plugin'
					) :
					esc_html_e(
						'A integração foi finalizada! Acesse Studio para gerenciar seus conteúdos.',
						'rcp-wp_plugin'
					);
				?>
				<br>
				<?php
					! $this->integrated() ? esc_html_e( 'Clique abaixo para iniciar a integração.', 'rcp-wp_plugin' ) : '';
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Token field
	 *
	 * @param string $args the new token.
	 *
	 * @since 1.0.0
	 */
	public function rcp_change_token_callback( $args ) {
		$defaults = array(
			'token' => esc_attr( get_option( 'rcp_token' ) ),
		);
		$options  = wp_parse_args( get_option( 'rcp_integration_options' ), $defaults );
		$html     = '<input type="text" id="token" readonly size="35" value="' . $options['token'] . '" />';
		$html    .= '<label for="token"> ' . $args[0] . '</label>';
		echo esc_html( $html );
	}

	/**
	 * Saves integration time
	 *
	 * @since 1.0.0
	 *
	 * @return bool|string
	 */
	public function integrate() {
		$integrated_at = gmdate( 'Y-m-d H:i:s' );
		if ( update_option( 'rcp_integrated_at', $integrated_at ) ) {
			return $integrated_at;
		} else {
			return false;
		}
	}

	/**
	 * Disconnect of the integration with RC2
	 *
	 * @return date
	 *
	 * @since 1.0.0
	 */
	public function disconnect() {
		$disconnected_at = gmdate( 'Y-m-d H:i:s' );
		update_option( 'rcp_integrated_at', '' );
		update_option( 'rcp_activated_at', '' );
		update_option( 'rcp_disconnected_at', $disconnected_at );

		return $disconnected_at;
	}

	/**
	 * Get the token in the database
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function get_token() {
		return get_option( 'rcp_token' );
	}

	/**
	 * Get the url of the blog in the database
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function get_url() {
		return get_bloginfo( 'url' );
	}

	/**
	 * Return the option which say if analytics is enabled
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function get_analytics_enabled() {
		return get_option( 'rcp_rock_analytics_enabled' );
	}

	/**
	 * Update the option which say if analytics is enabled with true
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function connect_rock_analytics() {
		update_option( 'rcp_rock_analytics_enabled', true );
	}

	/**
	 * Update the option which say if analytics is enabled with false
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function disconnect_rock_analytics() {
		update_option( 'rcp_rock_analytics_enabled', false );
	}
}
