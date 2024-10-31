<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://rockcontent.com/
 * @since      1.0.0
 * @package    Inc
 * @subpackage Inc/core
 * @author     Rock Content <plugin@rockcontent.com>
 */

namespace RockContent\Core;

/**
 * Class called in the plugin activation
 *
 * @package    Inc
 * @subpackage Inc/Core
 * @author     Rock Content <plugin@rockcontent.com>
 */
class Activator {

	/**
	 * Method called in the plugin activation
	 *
	 * @since   1.0.0
	 */
	public static function activate() {
		self::setup_config_data();
	}

	/**
	 * Configure the options of the plugin
	 *
	 * @since   1.0.3
	 */
	private static function setup_config_data() {
		if ( ! get_option( 'rcp_token' ) ) {
			update_option( 'rcp_token', self::generate_token() );
			update_option( 'rcp_activated_at', gmdate( 'Y-m-d H:i:s' ) );
			update_option( 'rcp_timestamp', 1 );
			update_option( 'rcp_integrated_at', null );
			update_option( 'rcp_deactivated_at', null );
		} else {
			if ( ! get_option( 'rcp_integrated_at' ) ) {
				update_option( 'rcp_integrated_at', gmdate( 'Y-m-d H:i:s' ) );
			}
			update_option( 'rcp_updated_at', gmdate( 'Y-m-d H:i:s' ) );
		}
	}

	/**
	 * Generate a unique and random token
	 *
	 * @return string
	 *
	 * @since   1.0.0
	 */
	private static function generate_token() {
		return md5( microtime() );
	}

}
