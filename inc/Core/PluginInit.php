<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://rockcontent.com/
 * @since      1.0.0
 * @package    PluginInit
 * @subpackage PluginInit/includes
 * @author     Rock Content <plugin@rockcontent.com>
 */

namespace RockContent\Core;

use RockContent\Admin\ModelNotification;
use RockContent\Integrations\RockAnalytics;
use RockContent\Integrations\APIStudio;
use RockContent\Core\PluginLoader;
use RockContent\Admin\PluginAdmin;
use RockContent\Admin\NotificationAdmin;

/**
 * Call the methods necessary for plugin working
 *
 * @package    Inc
 * @subpackage Inc/Core
 * @author     Rock Content <plugin@rockcontent.com>
 */
class PluginInit {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PluginLoader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The rest client that's responsible for responding to rest calls made to this plugin from outside WordPress
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      APIStudio $rest_client
	 */
	protected $rest_client;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'rcp-wp_plugin';
		$this->version     = RCP_VERSION;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_rest_endpoints();
		$this->define_rock_analytics_hook();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - PluginLoader. Orchestrates the hooks of the plugin.
	 * - Rcp_Wp_plugin_i18n. Defines internationalization functionality.
	 * - PluginAdmin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * Libs required to upload files
		 */
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$this->loader = new PluginLoader();
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $pagenow;
		$plugin_integration  = new PluginAdmin( $this->get_plugin_name() );
		$plugin_notification = new NotificationAdmin();
		$model_notification  = new ModelNotification();

		$this->loader->add_action( 'admin_menu', $plugin_integration, 'rcp_plugin_menu' );
		$this->loader->add_action( 'admin_bar_menu', $plugin_integration, 'rcp_plugin_menu_top', 120 );
		$this->loader->add_action( 'admin_init', $plugin_integration, 'rcp_initialize_layout_options' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_integration, 'enqueue_admin_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_integration, 'enqueue_front_scripts' );
		$this->loader->add_action( 'wp_ajax_hide_notification', $model_notification, 'hide_notification_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_hide_notification', $model_notification, 'hide_notification_callback' );
		$this->loader->add_action( 'wp_ajax_hide_all_notification', $model_notification, 'hide_all_notification_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_hide_all_notification', $model_notification, 'hide_all_notification_callback' );
		$this->loader->add_action( 'wp_ajax_rcpLoadNotifications', $plugin_notification, 'rcp_load_notifications' );
		$this->loader->add_action( 'wp_ajax_nopriv_rcpLoadNotifications', $plugin_notification, 'rcp_load_notifications' );
		$this->loader->add_action( 'wp_ajax_rcpShowSearchedNotifications', $plugin_notification, 'show_searched_notifications' );
		$this->loader->add_action( 'wp_ajax_nopriv_rcpShowSearchedNotifications', $plugin_notification, 'show_searched_notifications' );
		$this->loader->add_action( 'wp_login', $plugin_notification, 'rcp_fetch_notifications' );

		if ( ! $plugin_integration->integrated() && 'plugins.php' == $pagenow ) {
			$this->loader->add_action( 'admin_notices', $plugin_integration, 'activation_notice' );
		}
	}

	/**
	 * Register hooks for inject rock analytics
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_rock_analytics_hook() {
		$analytics = new RockAnalytics();
		$this->loader->add_action( 'wp_head', $analytics, 'inject_analytics_default' );
		$this->loader->add_action( 'amp_post_template_footer', $analytics, 'inject_analytics_amp' );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return RCP_VERSION;
	}

	/**
	 * Define rest endpoints used by this plugin
	 *
	 * @since 1.0.0
	 */
	protected function define_rest_endpoints() {
		$rest_client = new APIStudio( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $rest_client, 'rcp_define_endpoints' );

		$this->loader->add_action( 'template_redirect', $rest_client, 'intercept_request' );
	}

	/**
	 * Helper function to rename array keys.
	 *
	 * @param string $oldkey old key.
	 *
	 * @param string $newkey new key.
	 *
	 * @param array  $arr the array.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function _rename_arr_key( $oldkey, $newkey, array &$arr ) {
		if ( array_key_exists( $oldkey, $arr ) ) {
			$arr[ $newkey ] = $arr[ $oldkey ];
			unset( $arr[ $oldkey ] );

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PluginLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}
