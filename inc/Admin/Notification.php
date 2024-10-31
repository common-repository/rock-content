<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace RockContent\Admin;

/**
 * The notification class
 *
 * Create and update the notifications table and connect to notification api
 *
 * @package    Inc
 * @subpackage Inc/Admin
 * @author     Rock Content <plugin@rockcontent.com>
 */
class Notification {

	/**
	 * The link API.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $link_api The link API.
	 */
	public $link_api;

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      resource $request The request to link API.
	 */
	public $request;

	/**
	 * The return of the API.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      object $result The return of the API.
	 */
	public $result;

	/**
	 * Return the API link and case not exists update the link in the option,
	 * and call the function to create the notifications table.
	 *
	 * @return string
	 *
	 * @since    3.0.0
	 */
	public function get_link_api() {
		if ( ! get_option( 'rcp_link_api' ) ) {
			$this->link_api = \RockContent\Core\Utils::link_api();
			update_option( 'rcp_link_api', $this->link_api );
			\RockContent\Admin\ModelNotification::create_table_rcp_notification();
		}
		return get_option( 'rcp_link_api' );
	}

	/**
	 * Connect with the notification API, get the notifications,
	 * and call the method which will save the notifications in the database.
	 *
	 * @param bool $retry this is util for verify if the call is a retry.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	public function connect_to_notification_api( $retry ) {
		$this->link_api .= urlencode( CHORUS_USER_ID ) . '/notifications';
		$this->request   = \RockContent\Core\Utils::create_request( 'GET' );
		$this->result    = @file_get_contents( $this->link_api, false, $this->request );
		$this->result    = json_decode( $this->result );
		if ( ! empty( $this->result ) ) {
			$this->result = \RockContent\Admin\ModelNotification::get_ids( $this->result->notifications );
			if ( ! empty( $this->result ) ) {
				$this->prepare_save();
			}
		} elseif ( ! $retry ) {
			$this->connect_to_notification_api( true );
			$this->link_api = \RockContent\Core\Utils::link_api();
			update_option( 'rcp_link_api', $this->link_api );
		}
	}

	/**
	 * Prepare data for save in the database.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	public function prepare_save() {
		$notifications_obj = $this->result;
		$values            = array();
		$place_holders     = array();
		foreach ( $notifications_obj as $data ) {
			array_push( $values, $data->id, $data->date, $data->type, $data->title, $data->message );
			$place_holders[] = '( %s, %s, %s, %s, %s)';
		}

		\RockContent\Admin\ModelNotification::save_new_notifications( $place_holders, $values );
	}

	/**
	 * Verify if is in stage and update option with true and after do the connection with API.
	 *
	 * @return bool
	 *
	 * @since    3.0.0
	 */
	public function get_notification_api() {
		if ( \RockContent\Core\Utils::stage_is_present() && CHORUS_USER_ID ) {
			update_option( 'rcp_stage_is_present', true );
			$this->get_link_and_connect();

			return true;
		}

		update_option( 'rcp_stage_is_present', false );
		return false;
	}

	/**
	 * Get the link and connect to API.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	public function get_link_and_connect() {
		$this->link_api = $this->get_link_api();
		$this->connect_to_notification_api( false );
	}

}
