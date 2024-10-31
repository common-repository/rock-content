<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
/**
 * Access the database
 *
 * @link       https://rockcontent.com/
 * @since      3.0.0
 *
 * @package    Inc
 * @subpackage Inc/Admin
 */

namespace RockContent\Admin;

use RockContent\Core\Utils;

/**
 * Class for access the database
 *
 * Access the database
 *
 * @package    Inc
 * @subpackage Inc/Admin
 * @author     Rock Content <plugin@rockcontent.com>
 */
class ModelNotification {

	/**
	 * Will update in the database the notifications already read and return an array
	 * updated of the all notifications
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function hide_notification_callback() {
		if ( isset( $_POST['rock_content_notification_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['rock_content_notification_nonce'] ) ),
			'rock_content_notification_nonce'
		)
		) {
			global $wpdb;
			$table = $wpdb->prefix . 'rcp_notification';

			if ( isset( $_POST['id_notification'] ) ) {
				$id_notification        = sanitize_text_field( wp_unslash( $_POST['id_notification'] ) );
				$link_read_notification = get_option( 'rcp_link_api' ) . urlencode( CHORUS_USER_ID ) . '/notifications/' . $id_notification . '/read';
				$request                = \RockContent\Core\Utils::create_request( 'PATCH' );
				@file_get_contents( $link_read_notification, false, $request );

				$wpdb->update( $table, array( 'already_read' => 1 ), array( 'ID' => $id_notification ) );
				$notifications_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `%1$s` ', $table ) );
				echo json_encode( $notifications_data );
			}
			die();
		}
	}

	/**
	 * Will update in the database all notifications not read and return an array
	 * updated of the all notifications
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function hide_all_notification_callback() {
		if ( isset( $_POST['rock_content_notification_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['rock_content_notification_nonce'] ) ),
			'rock_content_notification_nonce'
		)
		) {
			global $wpdb;
			$read_and_data            = array();
			$table                    = $wpdb->prefix . 'rcp_notification';
			$notifications_read       = $wpdb->get_results( $wpdb->prepare( 'SELECT id FROM `%1$s` where already_read = 0', $table ) );
			$notifications_read_array = array();
			foreach ( $notifications_read as $key => $notifications_id ) {
				array_push( $notifications_read_array, $notifications_id->id );
			}
			$wpdb->update( $table, array( 'already_read' => 1 ), array( 'already_read' => 0 ) );
			$notifications_data     = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `%1$s` ', $table ) );
			$link_read_notification = get_option( 'rcp_link_api' ) . urlencode( CHORUS_USER_ID ) . '/notifications/read';
			$request                = \RockContent\Core\Utils::create_request( 'PATCH' );
			@file_get_contents( $link_read_notification, false, $request );
			array_push( $read_and_data, $notifications_data, $notifications_read_array );
			echo wp_json_encode( $read_and_data );
			die();
		}
	}

	/**
	 * Create the table of notifications in the database.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	public static function create_table_rcp_notification() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'rcp_notification';
		$sql             = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL,
      date datetime NOT NULL,
      type tinytext NOT NULL,
      title tinytext NOT NULL,
      already_read BIT NOT NULL,
      message text NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Save in the database the new notifications.
	 *
	 * @param array $place_holders array with place holders.
	 *
	 * @param array $values array with values for insert.
	 *
	 * @return void
	 *
	 * @since    3.0.0
	 */
	public static function save_new_notifications( $place_holders, $values ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rcp_notification';
		$query      = "INSERT INTO $table_name (`id`, `date`, `type`, `title`, `message`) VALUES ";
		$query     .= implode( ', ', $place_holders );
		$sql        = $wpdb->prepare( $query, $values );// phpcs:ignore
		$wpdb->query( $sql );// phpcs:ignore
	}

	/**
	 * Get the notifications which not exists in database.
	 *
	 * @param array $notifications notifications.
	 *
	 * @return array
	 *
	 * @since    3.0.0
	 */
	public static function get_ids( $notifications ) {
		global $wpdb;
		$table_name   = $wpdb->prefix . 'rcp_notification';
		$ids          = $wpdb->get_results( $wpdb->prepare( 'SELECT id FROM `%1$s` ', $table_name ) );
		$existing_ids = array_map(
			function( $id ) {
				return $id->id;
			},
			$ids
		);
		if ( ! empty( $notifications ) ) {
			$notifications = array_filter(
				$notifications,
				function( $notification ) use ( $existing_ids ) {
					return ! in_array( $notification->id, $existing_ids, false );
				}
			);
			return $notifications;
		}

		return null;

	}

}
