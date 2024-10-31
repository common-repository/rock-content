<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rockcontent.com/
 * @since      3.0.0
 *
 * @package    Inc
 * @subpackage Inc/Admin
 */

namespace RockContent\Admin;

use RockContent\Admin\Notification;

/**
 * The admin-specific functionality of the plugin.
 *
 * Return the screens of the plugin which will are rendered in the javascript
 * Realize queries in the database for get informations about notifications
 *
 * @package    Inc
 * @subpackage Inc/Admin
 * @author     Rock Content <plugin@rockcontent.com>
 */
class NotificationAdmin {

	/**
	 * This function create the html with informations of the notifications
	 *
	 * @param object $notifications object with notifications.
	 * @param string $type_notification the type of notification, this can are old or new.
	 *
	 * @return string
	 *
	 * @since 3.0.0
	 */
	public function show_notifications( $notifications, $type_notification ) {
		$show_notification_mob = '';
		$show_notification     = '';

		if ( ! empty( $notifications ) ) {
			foreach ( $notifications as $key => $notification ) {
				$notification_time  = $this->notification_time( $notification->date );
				$show_notification .=
				'
        <div class="rcp_notification_box">
          <div id="rcp_notification_content_olds" class="rcp_notification_content">
            <div class="rcp_notification_header">
              <div class="rcp_category_title">
        ';

				if ( 'success' === $notification->type ) {
					$show_notification .=
					'
						<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M7.00013 0.00244141C3.13407 0.00244141 0 3.13651 0 7.00257C0 10.8686 3.13407 14.0027 7.00013 14.0027C10.8662 14.0027 14.0003 10.8686 14.0003 7.00257C14.0003 3.13651 10.8662 0.00244141 7.00013 0.00244141ZM10.1779 5.74725C10.4708 5.45436 10.4708 4.97948 10.1779 4.68659C9.88504 4.3937 9.41016 4.3937 9.11727 4.68659L6.0761 7.72776L4.82067 6.47234C4.52778 6.17945 4.05291 6.17945 3.76001 6.47234C3.46712 6.76523 3.46712 7.24011 3.76001 7.533L5.54577 9.31875C5.83866 9.61165 6.31353 9.61165 6.60643 9.31875L10.1779 5.74725Z" fill="#127339"/>
						</svg>
					';
				} elseif ( 'ALERT' === $notification->type ) {
					$show_notification .=
					'
						<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M8.00101 0C7.60128 0 7.20831 0.103022 6.85999 0.299126C6.51168 0.495229 6.21977 0.777786 6.01247 1.11955L0.394993 10.4975L0.38687 10.5113C0.183794 10.863 0.0763415 11.2617 0.0752044 11.6678C0.0740674 12.0739 0.179285 12.4732 0.380389 12.826C0.581493 13.1789 0.871473 13.4729 1.22148 13.6788C1.57148 13.8848 1.96932 13.9955 2.3754 14L13.6156 14.0001L13.6266 13.9999C14.0327 13.9955 14.4305 13.8848 14.7805 13.6788C15.1305 13.4729 15.4205 13.1789 15.6216 12.826C15.8227 12.4732 15.9279 12.0739 15.9268 11.6678C15.9257 11.2617 15.8182 10.863 15.6151 10.5113L15.607 10.4975L9.99242 1.12431L9.98954 1.11956C9.78223 0.777794 9.49034 0.495229 9.14202 0.299126C8.79371 0.103022 8.40073 0 8.00101 0ZM9 5C9 4.44772 8.55228 4 8 4C7.44772 4 7 4.44772 7 5V7C7 7.55228 7.44772 8 8 8C8.55228 8 9 7.55228 9 7V5ZM7 10C7 9.44771 7.44772 9 8 9H8.00667C8.55895 9 9.00667 9.44771 9.00667 10C9.00667 10.5523 8.55895 11 8.00667 11H8C7.44772 11 7 10.5523 7 10Z" fill="#C76F04"/>
						</svg>
					';
				} elseif ( 'NOTIFICATION' === $notification->type ) {
					$show_notification .=
					'
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M8 0C3.58172 0 0 3.58172 0 8C0 12.4183 3.58172 16 8 16C12.4183 16 16 12.4183 16 8C16 3.58172 12.4183 0 8 0ZM8 6C8.55228 6 9 5.55228 9 5C9 4.44772 8.55228 4 8 4C7.44772 4 7 4.44772 7 5C7 5.55228 7.44772 6 8 6ZM6 8C6 7.44772 6.44772 7 7 7H7.99967C8.55196 7 8.99967 7.44772 8.99967 8V10C9.55196 10 10 10.4477 10 11C10 11.5523 9.55228 12 9 12H7.99967C7.44739 12 6.99967 11.5523 6.99967 11V9C6.44754 8.99982 6 8.55218 6 8Z" fill="#225ED8"/>
						</svg>
					';
				} elseif ( 'WARNING' === $notification->type ) {
					$show_notification .=
					'
						<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.5C3.58172 0.5 0 4.08172 0 8.5C0 12.9183 3.58172 16.5 8 16.5C12.4183 16.5 16 12.9183 16 8.5C16 4.08172 12.4183 0.5 8 0.5ZM9 5.5C9 4.94772 8.55228 4.5 8 4.5C7.44772 4.5 7 4.94772 7 5.5V8.5C7 9.05228 7.44772 9.5 8 9.5C8.55228 9.5 9 9.05228 9 8.5V5.5ZM8 12.5C8.55228 12.5 9 12.0523 9 11.5C9 10.9477 8.55228 10.5 8 10.5C7.44772 10.5 7 10.9477 7 11.5C7 12.0523 7.44772 12.5 8 12.5Z" fill="#C21F2D"/>
						</svg>
					';
				}

				$show_notification .=
				'
            <h2 class="rcp_notification_title"> ' . esc_html( $notification->title ) . ' </h2>
          </div>
          <h4 class="rcp_notification_date">' . esc_html( $notification_time ) . ' </h4>
        ';
				if ( 'new' === $type_notification ) {
					$show_notification     .=
					'
            <h4 class="rcp_check">
              <input type="checkbox" class="rcp_notification_close"
                data-id="' . esc_attr( $notification->id ) . '"
              >
              ' . esc_html( 'Lido' ) . '
            </h4>
          ';
					$show_notification_mob .=
					'
                <h4 id="rcp_check_mob" class="rcp_check">
                  <input type="checkbox" class="rcp_notification_close"
                    data-id="' . esc_attr( $notification->id ) . '"
                  >
                  ' . esc_html( 'Lido' ) . '
                </h4>
          ';
				}

				$show_notification .=
				'
          </div>
          <p class="rcp_notification" >' . esc_html( $notification->message ) . ' </p>
          ' . $show_notification_mob . '
            </div>
          </div>
        ';

			}
		}

		return $show_notification;
	}

	/**
	 * Will return for the javascript the old and new notifications
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function rcp_load_notifications() {
		if ( isset( $_POST['rock_content_notification_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['rock_content_notification_nonce'] ) ),
			'rock_content_notification_nonce'
		)
		) {
			global $wpdb;
			$table_name             = $wpdb->prefix . 'rcp_notification';
			$number_notifications   = isset( $_POST['number_notifications'] ) ?
				sanitize_text_field( wp_unslash( $_POST['number_notifications'] ) ) : 0;
			$type_notification      = isset( $_POST['type_notification'] ) ?
			sanitize_text_field( wp_unslash( $_POST['type_notification'] ) ) : '';
			$notification_data_html = array();
			$notifications_data     = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `%1$s` ', $table_name ) );
			$closed_ids             = isset( $_POST['closed_ids'] ) ?
			\RockContent\Core\Utils::sanitize_array( ( wp_unslash( $_POST['closed_ids'] ) ) ) : array(); // phpcs:ignore

			if ( empty( $closed_ids ) ) {
				$notifications_old = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM `%1$s` where already_read = 1 ORDER BY id LIMIT 5 OFFSET %2$s',
						$table_name,
						$number_notifications
					)
				);
			} else {
				$closed_ids        = implode( ', ', $closed_ids );
				$notifications_old = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM `%1$s` where already_read = 1 and id not in (%2$s) ORDER BY id LIMIT 5 OFFSET %3$s',
						$table_name,
						$closed_ids,
						$number_notifications
					)
				);
			}
				$content_old_notifications = $this->show_notifications( $notifications_old, 'old' );

			if ( 'new_and_old' === $type_notification || 'new_and_old_not_sum' === $type_notification ) {
				$notifications_new         = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM `%1$s` WHERE already_read=0',
						$table_name
					)
				);
				$content_new_notifications = $this->show_notifications( $notifications_new, 'new' );
				array_push( $notification_data_html, $content_new_notifications, $content_old_notifications, $notifications_data );
				echo wp_json_encode( $notification_data_html );
			} else {
				echo wp_json_encode( $content_old_notifications );
			}
		}
		die();
	}

	/**
	 * This function create a new notification API
	 *
	 * @return Notification
	 *
	 * @since 3.0.0
	 */
	public function create_new_api_notification() {
		$rcp_notification = new Notification();
		return $rcp_notification;
	}

	/**
	 * This function verify how long ago the notification was created
	 *
	 * @param string $notification_time current date of the system.
	 *
	 * @return string
	 *
	 * @since 3.0.0
	 */
	public function notification_time( $notification_time ) {
		$current_date      = new \DateTime( gmdate( 'Y-m-d H:i:s' ) );
		$notification_time = new \DateTime( $notification_time );
		$diff              = date_diff( $current_date, $notification_time );
		$diff_seconds      = (int) $diff->format( '%s' );
		$diff_minutes      = (int) $diff->format( '%i' );
		$diff_hours        = (int) $diff->format( '%h' );
		$diff_days         = (int) $diff->format( '%a' );
		$diff_months       = (int) $diff->format( '%m' );
		$diff_years        = (int) $diff->format( '%y' );
		$text              = '';

		if ( $diff_years > 0 ) {
			$text = $diff_years > 1 ? ' anos atrás' : ' ano atrás';
			return $diff_years . $text;
		} elseif ( $diff_months > 0 ) {
			$text = $diff_months > 1 ? ' meses atrás' : ' mês atrás';
			return $diff_months . $text;
		} elseif ( $diff_days > 0 ) {
			$text = $diff_days > 1 ? ' dias atrás' : ' dia atrás';
			return $diff_days . $text;
		} elseif ( $diff_hours > 0 ) {
			$text = $diff_hours > 1 ? ' horas atrás' : ' hora atrás';
			return $diff_hours . $text;
		} elseif ( $diff_minutes > 0 ) {
			$text = $diff_minutes > 1 ? ' minutos atrás' : ' minuto atrás';
			return $diff_minutes . $text;
		}

		$text = $diff_seconds > 1 ? ' segundos atrás' : ' segundo atrás';

		return $diff_seconds . $text;
	}

	/**
	 * Return the html of the searched notifications for the javascript
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function show_searched_notifications() {
		$show_notification = '';
		if ( isset( $_POST['rock_content_notification_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['rock_content_notification_nonce'] ) ),
			'rock_content_notification_nonce'
		)
		) {
			$_POST                  = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$show_notification_mob  = '';
			$searched_notifications = isset( $_POST['searched_notifications'] ) ?
				\RockContent\Core\Utils::sanitize_array( wp_unslash( $_POST['searched_notifications'] ) ) :// phpcs:ignore
				array();

			if ( ! empty( $searched_notifications ) ) {
				foreach ( $searched_notifications as $key => $notification ) {
					$notification_time  = $this->notification_time( $notification['date'] );
					$show_notification .=
					'
							<div class="rcp_notification_box">
								<div id="rcp_notification_content_olds" class="rcp_notification_content">
									<div class="rcp_notification_header">
										<div class="rcp_category_title">
							';

					if ( 'success' === $notification['type'] ) {
						$show_notification .=
						'
									<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M7.00013 0.00244141C3.13407 0.00244141 0 3.13651 0 7.00257C0 10.8686 3.13407 14.0027 7.00013 14.0027C10.8662 14.0027 14.0003 10.8686 14.0003 7.00257C14.0003 3.13651 10.8662 0.00244141 7.00013 0.00244141ZM10.1779 5.74725C10.4708 5.45436 10.4708 4.97948 10.1779 4.68659C9.88504 4.3937 9.41016 4.3937 9.11727 4.68659L6.0761 7.72776L4.82067 6.47234C4.52778 6.17945 4.05291 6.17945 3.76001 6.47234C3.46712 6.76523 3.46712 7.24011 3.76001 7.533L5.54577 9.31875C5.83866 9.61165 6.31353 9.61165 6.60643 9.31875L10.1779 5.74725Z" fill="#127339"/>
									</svg>
								';
					} elseif ( 'ALERT' === $notification['type'] ) {
						$show_notification .=
						'
									<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M8.00101 0C7.60128 0 7.20831 0.103022 6.85999 0.299126C6.51168 0.495229 6.21977 0.777786 6.01247 1.11955L0.394993 10.4975L0.38687 10.5113C0.183794 10.863 0.0763415 11.2617 0.0752044 11.6678C0.0740674 12.0739 0.179285 12.4732 0.380389 12.826C0.581493 13.1789 0.871473 13.4729 1.22148 13.6788C1.57148 13.8848 1.96932 13.9955 2.3754 14L13.6156 14.0001L13.6266 13.9999C14.0327 13.9955 14.4305 13.8848 14.7805 13.6788C15.1305 13.4729 15.4205 13.1789 15.6216 12.826C15.8227 12.4732 15.9279 12.0739 15.9268 11.6678C15.9257 11.2617 15.8182 10.863 15.6151 10.5113L15.607 10.4975L9.99242 1.12431L9.98954 1.11956C9.78223 0.777794 9.49034 0.495229 9.14202 0.299126C8.79371 0.103022 8.40073 0 8.00101 0ZM9 5C9 4.44772 8.55228 4 8 4C7.44772 4 7 4.44772 7 5V7C7 7.55228 7.44772 8 8 8C8.55228 8 9 7.55228 9 7V5ZM7 10C7 9.44771 7.44772 9 8 9H8.00667C8.55895 9 9.00667 9.44771 9.00667 10C9.00667 10.5523 8.55895 11 8.00667 11H8C7.44772 11 7 10.5523 7 10Z" fill="#C76F04"/>
									</svg>
								';
					} elseif ( 'NOTIFICATION' === $notification['type'] ) {
						$show_notification .=
						'
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M8 0C3.58172 0 0 3.58172 0 8C0 12.4183 3.58172 16 8 16C12.4183 16 16 12.4183 16 8C16 3.58172 12.4183 0 8 0ZM8 6C8.55228 6 9 5.55228 9 5C9 4.44772 8.55228 4 8 4C7.44772 4 7 4.44772 7 5C7 5.55228 7.44772 6 8 6ZM6 8C6 7.44772 6.44772 7 7 7H7.99967C8.55196 7 8.99967 7.44772 8.99967 8V10C9.55196 10 10 10.4477 10 11C10 11.5523 9.55228 12 9 12H7.99967C7.44739 12 6.99967 11.5523 6.99967 11V9C6.44754 8.99982 6 8.55218 6 8Z" fill="#225ED8"/>
									</svg>
								';
					} elseif ( 'WARNING' === $notification['type'] ) {
						$show_notification .=
						'
									<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.5C3.58172 0.5 0 4.08172 0 8.5C0 12.9183 3.58172 16.5 8 16.5C12.4183 16.5 16 12.9183 16 8.5C16 4.08172 12.4183 0.5 8 0.5ZM9 5.5C9 4.94772 8.55228 4.5 8 4.5C7.44772 4.5 7 4.94772 7 5.5V8.5C7 9.05228 7.44772 9.5 8 9.5C8.55228 9.5 9 9.05228 9 8.5V5.5ZM8 12.5C8.55228 12.5 9 12.0523 9 11.5C9 10.9477 8.55228 10.5 8 10.5C7.44772 10.5 7 10.9477 7 11.5C7 12.0523 7.44772 12.5 8 12.5Z" fill="#C21F2D"/>
									</svg>
								';
					}

					$show_notification .=
					'
									<h2 class="rcp_notification_title"> ' . esc_html( $notification['title'] ) . ' </h2>
								</div>
								<h4 class="rcp_notification_date">' . esc_html( $notification_time ) . ' </h4>
							';
					if ( '0' === $notification['already_read'] ) {
						$show_notification     .=
						'
									<h4 class="rcp_check">
										<input type="checkbox" class="rcp_notification_close"
											data-id="' . esc_attr( $notification['id'] ) . '"
										>
										' . esc_html( 'Lido' ) . '
									</h4>
								';
						$show_notification_mob .=
						'
											<h4 id="rcp_check_mob" class="rcp_check">
												<input type="checkbox" class="rcp_notification_close"
													data-id="' . esc_attr( $notification['id'] ) . '"
												>
												' . esc_html( 'Lido' ) . '
											</h4>
								';
					}

					$show_notification .=
					'
								</div>
								<p class="rcp_notification" >' . esc_html( $notification['message'] ) . ' </p>
								' . $show_notification_mob . '
									</div>
								</div>
							';

				}
			}
		}
		echo $show_notification; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		die();
	}

	/**
	 * This function call the method which create a new notification API
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function rcp_fetch_notifications() {
		$rcp_notification = $this->create_new_api_notification();
		$rcp_notification->get_notification_api();
	}

	/**
	 * This function call the method which create a new notification API
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function rcp_notifications_callback() {
		$rcp_notification = new Notification();
		$is_stage         = $rcp_notification->get_notification_api();
		if ( $is_stage ) {
			global $wpdb;
			$table                  = $wpdb->prefix . 'rcp_notification';
			$previous_notifications = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM `%1$s` WHERE already_read = 1 ORDER BY id LIMIT 5',
					$table
				)
			);
			?>
			<div id="rcp-general-box" class="rcp-box-notification">
			<div class="rcp-box-header">
				<div class="rcp-svg-search">
				<div class="svg-notification">
					<svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M6.00391 0.00390625C4.80187 0.00390625 3.65014 0.484572 2.80179 1.33844C1.95362 2.19213 1.47807 3.34887 1.47807 4.55391C1.47807 7.39982 0.662045 8.77849 0.222013 9.32043C-0.0702734 9.68041 -0.0172639 10.1118 0.0854693 10.3639C0.186817 10.6126 0.474493 11.0039 0.997834 11.0039H11.01C11.5333 11.0039 11.821 10.6126 11.9223 10.3639C12.0251 10.1118 12.0781 9.6804 11.7858 9.32043C11.3458 8.77849 10.5297 7.39982 10.5297 4.55391C10.5297 3.34887 10.0542 2.19213 9.20602 1.33844C8.35768 0.484572 7.20595 0.00390625 6.00391 0.00390625ZM3.86677 2.39536C4.43448 1.82395 5.20334 1.50391 6.00391 1.50391C6.80448 1.50391 7.57333 1.82395 8.14105 2.39536C8.70894 2.96695 9.02892 3.7433 9.02892 4.55391C9.02892 6.95227 9.55669 8.5221 10.1076 9.50391H1.90024C2.45112 8.5221 2.9789 6.95227 2.9789 4.55391C2.9789 3.7433 3.29888 2.96695 3.86677 2.39536Z" fill="#2D3648"/>
					<path d="M5.71345 12.3559C5.49514 12.0038 5.03259 11.8953 4.6803 12.1135C4.32801 12.3316 4.21939 12.7939 4.43769 13.146C4.60347 13.4134 4.8376 13.6294 5.11189 13.7772C5.38594 13.9249 5.6932 14.001 6.0034 14.001C6.31359 14.001 6.62085 13.9249 6.89491 13.7772C7.16919 13.6294 7.40332 13.4134 7.5691 13.146C7.7874 12.7939 7.67878 12.3316 7.32649 12.1135C6.9742 11.8953 6.51165 12.0038 6.29335 12.3559C6.27054 12.3927 6.234 12.4292 6.18261 12.4569C6.131 12.4847 6.06903 12.501 6.0034 12.501C5.93777 12.501 5.87579 12.4847 5.82418 12.4569C5.77279 12.4292 5.73625 12.3927 5.71345 12.3559Z" fill="#2D3648"/>
					</svg>
					<h2> <?php esc_html_e( 'Notificações', 'rcp-wp_plugin' ); ?> </h2>
				</div>
				<div class="rcp_search_notification">
					<svg class="rcp_search_icon" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M8.25739 9.6716C7.46696 10.1951 6.51908 10.5 5.5 10.5C2.73858 10.5 0.5 8.26142 0.5 5.5C0.5 2.73858 2.73858 0.5 5.5 0.5C8.26142 0.5 10.5 2.73858 10.5 5.5C10.5 6.51908 10.1951 7.46696 9.6716 8.25739L11.2073 9.79309C11.5978 10.1836 11.5978 10.8168 11.2073 11.2073C10.8168 11.5978 10.1836 11.5978 9.79309 11.2073L8.25739 9.6716ZM2.5 5.5C2.5 3.84315 3.84315 2.5 5.5 2.5C7.15685 2.5 8.5 3.84315 8.5 5.5C8.5 6.29846 8.18807 7.02407 7.67941 7.5616C7.65839 7.57942 7.63793 7.59825 7.61809 7.61809C7.59825 7.63793 7.57942 7.65839 7.5616 7.67941C7.02407 8.18807 6.29846 8.5 5.5 8.5C3.84315 8.5 2.5 7.15685 2.5 5.5Z" fill="#A0ABC0"/>
					</svg>
					<input 
						class="rcp_search"
						type="text"
						name="search_notification"
						placeholder="<?php esc_attr_e( 'Pesquisar', 'rcp-wp_plugin' ); ?>"
					>
				</div>
				</div>
				<hr>
				<div class="news_checkall">
				<h4 class="rcp_title_new"> <?php esc_html_e( 'Novas', 'rcp-wp_plugin' ); ?> </h4>
				<h4 class="rcp_check rcp_checkall_mob">
					<input id="rcp_checkall" type="checkbox" class="rcp_notification_close_all">
					<?php esc_html_e( 'Marcar todas como lida', 'rcp-wp_plugin' ); ?>
				</h4>
				</div>
			</div>
			<div class="rcp_box_whitout_notification">
				<img src="<?php echo esc_url( RCP_NAME_URL . 'assets/admin/img/empty_state.png' ); ?>" alt=""/> 
				<p class="rcp_without_notification"> <?php esc_html_e( 'Nada por aqui', 'rcp-wp_plugin' ); ?> </p>
			</div>
			<div class="rcp_loader_wrapper">
				<div class="rcp_loader"></div>
			</div>
			<div class="rcp_new_notifications">
			</div>
			<h4 class="rcp_previous_notifications
				<?php
					echo esc_attr(
						! empty( $previous_notifications ) ?
						'rcp_previous_notifications_show' : ''
					);
				?>
				"
			> <?php esc_html_e( 'Anteriores', 'rcp-wp_plugin' ); ?> </h4>
			<div class="rcp_previous_box_notifications">
			</div>
			</div>
				<?php
		} else {
			$this->show_screen_not_client();
		}
	}

	/**
	 * Show the screen of Stage for users which not are clients
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function show_screen_not_client() {
		?>
		<div class="rcp-box-noclient">
			<div class="rcp-noclient-text">
				<h1 class="rcp-noclient-title">
					<?php esc_html_e( 'A solução WordPress com hospedagem', 'rcp-wp_plugin' ); ?>
					<br>
					<span style="color:#225ED8;">
						<?php esc_html_e( 'simples, eficiente e otimizada.', 'rcp-wp_plugin' ); ?>
					</span>
				</h1>
				<h1 class="rcp-noclient-title-mob">
					<?php esc_html_e( 'A solução WordPress com', 'rcp-wp_plugin' ); ?>
					<br>
					<?php esc_html_e( 'hospedagem', 'rcp-wp_plugin' ); ?>
					<span style="color:#225ED8;">
						<?php esc_html_e( 'simples, eficiente e', 'rcp-wp_plugin' ); ?>
						<br>
						<?php esc_html_e( 'otimizada.', 'rcp-wp_plugin' ); ?>
					</span>
				</h1>
				<h2 class="rcp-noclient-description">
					<?php
						esc_html_e(
							'Stage é uma ferramenta que facilita o gerenciamento do seu site em  WordPress, 
							de layouts otimizados para conversão até uma hospedagem de alta velocidade. Sem se
							preocupar com processos técnicos! ',
							'rcp-wp_plugin'
						);
					?>
					<br> 
					<?php esc_html_e( 'Conheça nossa plataforma e veja como melhorar o seu negócio.', 'rcp-wp_plugin' ); ?>
				</h2>
				<a
					href="<?php echo esc_url( 'https://rockcontent.com/br/produtos/stage/' ); ?> "
					class="rcp-noclient-button"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Conheça o Stage', 'rcp-wp_plugin' ); ?>
				</a>
			</div>
			<img class="rcp_image_no_client"
				id="stage_illustration"
				src="<?php echo esc_url( RCP_NAME_URL . 'assets/admin/img/stage_Illustration.png' ); ?>"
				alt=""
			/>

	  </div>
		<?php
	}

}
