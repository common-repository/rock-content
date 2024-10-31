<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

/**
 * Custom exception to use in RCP Plugin
 *
 * The only difference between this exception and a common exception is that you can pass an array as a message
 * in the last parameter.
 *
 * @since      1.0.0
 * @package    Inc
 * @subpackage Inc/Integrations
 * @author     Rock Content <plugin@rockcontent.com>
 */

namespace RockContent\Integrations;

/**
 * Class with content exception
 *
 * @package    Inc
 * @subpackage Inc/Core
 * @author     Rock Content <plugin@rockcontent.com>
 */
class ContentException extends \Exception {

	/**
	 * Params of the exception.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private $_options;

	/**
	 * Constructor of the class
	 *
	 * @param string     $message the message.
	 * @param int        $code the code.
	 * @param \Exception $previous the previous.
	 * @param array      $options the params.
	 *
	 * @since 1.0.0
	 */
	public function __construct(
	$message,
	$code = 0,
	\Exception $previous = null,
	$options = array( 'params' )
	) {
		parent::__construct( $message, $code, $previous );

		$this->_options = $options;
	}

	/**
	 * Return the variable _options
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function GetOptions() {
		return $this->_options;
	}
}
