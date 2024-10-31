<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rockcontent.com/
 * @since             1.0.0
 * @package           PluginInit
 *
 * @wordpress-plugin
 * Plugin Name:       Rock Content
 * Plugin URI:        https://rockcontent.com/
 * Description:       Este fantástico plugin permite integrar o seu blog WordPress com a plataforma Rock Content.
 * Version:           3.0.3
 * Author:            Rock Content
 * Author URI:        https://rockcontent.com/
 * Text Domain:       rcp-wp_plugin
 * Domain Path:       /languages
 */

namespace RockContent;

use RockContent\Admin\Notification;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'RCP_NAME', 'rock-content' );

define( 'RCP_VERSION', '3.0.3' );

define( 'RCP_NAME_DIR', plugin_dir_path( __FILE__ ) );

define( 'RCP_NAME_URL', plugin_dir_url( __FILE__ ) );

define( 'RCP_BASENAME', plugin_basename( __FILE__ ) );

define( 'RCP_TEXT_DOMAIN', 'rock-convert' );

define( 'RCP_DEBUG', false );

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Core\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Core\Deactivator', 'deactivate' ) );
/**
 * Class responsible to initialize the config of the plugin
 */
class RockContent {
	/**
	 * Static Init.
	 *
	 * @var $init
	 */
	protected static $init;

	/**
	 * Loads the plugin
	 *
	 * @access public
	 */
	public static function init() {
		if ( ! get_option( 'rcp_link_api' ) ) {
			$rcp_notification = new Notification();
			$rcp_notification->get_notification_api();
		}

		if ( null === self::$init ) {
			self::$init = new Core\PluginInit();
			self::$init->run();
		}

		return self::$init;
	}
}

/**
 * Initialize method.
 *
 * @return RockContent;
 */
function rock_content_init() {
	return RockContent::init();
}

rock_content_init();
