<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace RockContent\Integrations;

/**
 * This class has all code necessary to create json responses
 *
 * @since      1.0.0
 * @package    PluginInit
 * @subpackage PluginInit/includes
 * @author     Rock Content <plugin@rockcontent.com>
 */
class Response {

	/**
	 * Http response
	 *
	 * @param int   $status status code.
	 * @param array $body body.
	 *
	 * @throws ContentException Content exception.
	 *
	 * @since 1.0.0
	 */
	public static function respond_with( $status, $body = array() ) {
		self::set_response_header( $status );
		self::respond_as_json( $body );
	}

	/**
	 * Set response header
	 *
	 * @param int $status status code.
	 *
	 * @since 1.0.0
	 *
	 * @since 2.0.0
	 */
	public static function set_response_header( $status ) {
		header( 'Content-type:application/json;charset=utf-8' );
		header( 'Responded-By:rcp-plugin' );
		header( 'Wordpress-Version: ' . get_bloginfo( 'version' ) );
		header( 'X-PHP-Response-Code: ' . $status, true, $status );
	}

	/**
	 * Transform body in json
	 *
	 * @param array $body body.
	 *
	 * @since 1.0.0
	 */
	public static function respond_as_json( $body ) {
		echo json_encode( $body );
	}
}
