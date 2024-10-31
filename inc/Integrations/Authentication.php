<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace RockContent\Integrations;

/**
 * This class has all code necessary to authenticate requests to this plugin
 *
 * @since      1.0.0
 * @package    Inc
 * @subpackage Inc/Integrations
 * @author     Rock Content <plugin@rockcontent.com>
 */
class Authentication {

	/**
	 * Perform user authentication
	 *
	 * @param null $method method.
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public static function authenticate( $method = null ) {
		if ( empty( $_SERVER['HTTP_RCP_TOKEN'] ) ) {
			Response::respond_with(
				403,
				array(
					'error_code' => APIStudio::$errors['TOKEN_NOT_PROVIDED'],
					'error'      => 'RCP Token is required',
				)
			);
			die;
		}

		$token = self::get_token();

		$decrypted_data = self::decrypt_data( sanitize_text_field( wp_unslash( $_SERVER['HTTP_RCP_TOKEN'] ) ), $token );

		if ( ! self::valid( $decrypted_data ) ) {
			Response::respond_with(
				401,
				array(
					'error_code' => APIStudio::$errors['INVALID_TOKEN'],
					'error'      => 'Invalid token',
				)
			);
			die;
		} else {
			update_option( 'rcp_timestamp', $decrypted_data );
		}

		return self::preserved_data( $method );
	}

	/**
	 * Get option with value of the token
	 *
	 * @return mixed|void
	 *
	 * @since 1.0.0
	 */
	public static function get_token() {
		return get_option( 'rcp_token' );
	}

	/**
	 * Decrypt the data
	 *
	 * @param string $data the data to decrypt.
	 * @param string $key key.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private static function decrypt_data( $data, $key ) {
		$val = str_replace( array( '_', '-', '.' ), array( '+', '/', '=' ), $data );

		$data             = base64_decode( $val );
		$iv_length        = openssl_cipher_iv_length( 'AES-256-CBC' );
		$body_data        = substr( $data, $iv_length );
		$iv               = substr( $data, 0, $iv_length );
		$base64_body_data = base64_encode( $body_data );

		return openssl_decrypt( $base64_body_data, 'AES-256-CBC', $key, 0, $iv );
	}

	/**
	 * Validate the data
	 *
	 * @param int $data data to validate.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function valid( $data ) {
		return isset( $data ) && self::bigintval( $data ) > self::bigintval( get_option( 'rcp_timestamp' ) );
	}

	/**
	 * Format a number
	 *
	 * @param int $value value to format.
	 *
	 * @return string
	 */
	public static function bigintval( $value ) {
		return number_format( (float) $value, 0, '', '' );
	}

	/**
	 * Preserve the data
	 *
	 * @param string $method type of method.
	 *
	 * @return mixed
	 */
	public static function preserved_data( $method ) {
		$response = array();

		if ( 'post' == $method && isset( $_POST['data'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
			$response = array_map( 'wp_kses_post', $_POST['data'] ); // phpcs:ignore
		} elseif ( 'get' == $method && isset( $_GET['data'] ) ) { // phpcs:ignore
			$response = array_map( 'wp_kses_post', $_GET['data'] ); // phpcs:ignore
		}

		if ( ! isset( $_SERVER['HTTP_APP_VERSION'] ) ) {
			$response['application_version'] = 'RC';
		} else {
			$response['application_version'] = $_SERVER['HTTP_APP_VERSION']; // phpcs:ignore
		}

		return $response;
	}
}
