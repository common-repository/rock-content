<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace RockContent\Integrations;

/**
 * Class Requirements
 *
 * @since 1.0.3
 */
class Requirements {

	/**
	 * Minimum php version
	 *
	 * @var string
	 */
	public $minimum_php_version;

	/**
	 * Requirements constructor.
	 *
	 * @param string $minimum_php_version minimum php version.
	 */
	public function __construct( $minimum_php_version ) {
		$this->minimum_php_version = $minimum_php_version;
	}

	/**
	 * Verify if the php version is equal or bigger than minimum php version
	 *
	 * @return bool
	 */
	public function valid_php_version() {
		return ( version_compare( PHP_VERSION, $this->minimum_php_version ) >= 0 );
	}

	/**
	 * Return a bool saying if openssl is enabled
	 *
	 * @return bool
	 */
	public function open_ssl_enabled() {
		return extension_loaded( 'openssl' );
	}

	/**
	 * Return a bool saying if all requirements is valid
	 *
	 * @return bool
	 */
	public function valid() {
		return $this->valid_php_version() && $this->open_ssl_enabled();
	}

}
