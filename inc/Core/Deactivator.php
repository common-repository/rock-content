<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://rockcontent.com/
 * @since      1.0.0
 * @package    Inc
 * @subpackage Inc/core
 * @author     Rock Content <plugin@rockcontent.com>
 */

namespace RockContent\Core;

/**
 * Class called in the plugin deactivation
 *
 * @package    Inc
 * @subpackage Inc/Core
 * @author     Rock Content <plugin@rockcontent.com>
 */
class Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		self::store_deactivation_date();
		self::delete_rewrite_rules_flag();
	}

	/**
	 * Record the deactivation time
	 *
	 * @since 1.0.0
	 */
	public static function store_deactivation_date() {
		update_option( 'rcp_deactivated_at', gmdate( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Delete the option rcp_rewrite_rules_were_flushed
	 *
	 * @since 2.4.0
	 */
	public static function delete_rewrite_rules_flag() {
		delete_option( 'rcp_rewrite_rules_were_flushed' );
	}
}
