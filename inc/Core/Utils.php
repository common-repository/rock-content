<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
/**
 * Class with util functions
 *
 * @since 1.0.0
 */

namespace RockContent\Core;

/**
 * Class with util functions
 *
 * @package    Inc
 * @subpackage Inc/Core
 * @author     Rock Content <plugin@rockcontent.com>
 */
class Utils {

	/**
	 * Return http response with message
	 *
	 * @param int $code http code.
	 *
	 * @since    1.0.0
	 */
	public static function http_response_code( $code = null ) {
		if ( $code ) {
			switch ( $code ) {
				case 100:
					$text = 'Continue';
					break;
				case 101:
					$text = 'Switching Protocols';
					break;
				case 200:
					$text = 'OK';
					break;
				case 201:
					$text = 'Created';
					break;
				case 202:
					$text = 'Accepted';
					break;
				case 203:
					$text = 'Non-Authoritative Information';
					break;
				case 204:
					$text = 'No Content';
					break;
				case 205:
					$text = 'Reset Content';
					break;
				case 206:
					$text = 'Partial Content';
					break;
				case 300:
					$text = 'Multiple Choices';
					break;
				case 301:
					$text = 'Moved Permanently';
					break;
				case 302:
					$text = 'Moved Temporarily';
					break;
				case 303:
					$text = 'See Other';
					break;
				case 304:
					$text = 'Not Modified';
					break;
				case 305:
					$text = 'Use Proxy';
					break;
				case 400:
					$text = 'Bad Request';
					break;
				case 401:
					$text = 'Unauthorized';
					break;
				case 402:
					$text = 'Payment Required';
					break;
				case 403:
					$text = 'Forbidden';
					break;
				case 404:
					$text = 'Not Found';
					break;
				case 405:
					$text = 'Method Not Allowed';
					break;
				case 406:
					$text = 'Not Acceptable';
					break;
				case 407:
					$text = 'Proxy Authentication Required';
					break;
				case 408:
					$text = 'Request Time-out';
					break;
				case 409:
					$text = 'Conflict';
					break;
				case 410:
					$text = 'Gone';
					break;
				case 411:
					$text = 'Length Required';
					break;
				case 412:
					$text = 'Precondition Failed';
					break;
				case 413:
					$text = 'Request Entity Too Large';
					break;
				case 414:
					$text = 'Request-URI Too Large';
					break;
				case 415:
					$text = 'Unsupported Media Type';
					break;
				case 500:
					$text = 'Internal Server Error';
					break;
				case 501:
					$text = 'Not Implemented';
					break;
				case 502:
					$text = 'Bad Gateway';
					break;
				case 503:
					$text = 'Service Unavailable';
					break;
				case 504:
					$text = 'Gateway Time-out';
					break;
				case 505:
					$text = 'HTTP Version not supported';
					break;
				default:
					exit( 'Unknown http status code "' . esc_html( htmlentities( $code ) ) . '"' );
				break;
			}

			$protocol = ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : 'HTTP/1.0' );

			header( $protocol . ' ' . $code . ' ' . $text );

			$GLOBALS['http_response_code'] = $code;

		} else {

			$code = ( isset( $GLOBALS['http_response_code'] ) ? sanitize_text_field( $GLOBALS['http_response_code'] ) : 200 );

		}

		return $code;

	}

	/**
	 * Return API link
	 *
	 * @since    3.0.0
	 */
	public static function link_api() {
		return 'https://wp-notification.prod.stage.rock.works/';
	}

	/**
	 * Recursive sanitation for an array
	 *
	 * @param array $array array to sanitize.
	 *
	 * @return mixed
	 */
	public static function sanitize_array( $array ) {

		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$v[ $key ] = self::sanitize_array( $value );
			} else {
				$v[ $key ] = sanitize_text_field( $value );
			}
		}

		return $v;
	}

	/**
	 * Verify if Stage is present
	 *
	 * @return bool
	 *
	 * @since    3.0.0
	 */
	public static function stage_is_present() {
		if ( ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_mu_plugins();

		$stage = isset( $plugins['chorus-core.php'] );

		return $stage;
	}

	/**
	 * Verify if Stage is present
	 *
	 * @param array $array data for debug.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	public static function debug( $array ) {
		echo '<pre>';
		print_r( $array );
		echo '</pre>';
	}

	/**
	 * Create the request to connect in notification API
	 *
	 * @param string $type requisition type.
	 *
	 * @return resource
	 *
	 * @since    3.0.0
	 */
	public static function create_request( $type ) {
		$postdata = '';
		$opts     = array(
			'http' =>
			array(
				'method'  => $type,
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata,
			),
		);
		$context  = stream_context_create( $opts );
		return $context;
	}

}

