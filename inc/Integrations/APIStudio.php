<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace RockContent\Integrations;

use RockContent\Admin\PluginAdmin;
use RockContent\Integrations\Response;

/**
 * This class has all code necessary to handle the endpoints defined in PluginInit
 *
 * @since      1.0.0
 * @package    Inc
 * @subpackage Inc/Integrations
 * @author     Rock Content <plugin@rockcontent.com>
 */
class APIStudio {

	/**
	 * List of errors used by this plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array $errors List of errors used by this plugin
	 */
	public static $errors = array(
		/**
		 * Token errors
		 */
		'INVALID_TOKEN'          => 'TK01',
		'TOKEN_NOT_PROVIDED'     => 'TK02',
		/**
		 * Integration errors
		 */
		'INTEGRATION_FAILED'     => 'IT01',
		/**
		 * Publish post errors
		 */
		'INVALID_POST_FIELDS'    => 'PP01',
		'INVALID_WP_POST_FIELDS' => 'PP02',
		/**
		 * List post errors
		 */
		'POST_STATUS_REQUIRED'   => 'LP01',
		/**
		 * Find post errors
		 */
		'POST_ID_REQUIRED'       => 'FP01',
		'POST_NOT_FOUND'         => 'FP02',
	);

	/**
	 * List of endpoints used by this plugin
	 *
	 * @var array
	 */
	public $endpoints = array(
		'ACTIVATE'          => array(
			'method'         => 'post',
			'endpoint'       => 'rcp-activate-plugin',
			'authentication' => 'rcp_authentication',
		),
		'PUBLISH_POST'      => array(
			'method'         => 'post',
			'endpoint'       => 'rcp-publish-content',
			'authentication' => 'rcp_authentication',
		),
		'DISCONNECT'        => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-disconnect-plugin',
			'authentication' => 'rcp_authentication',
		),
		'DISABLE_ANALYTICS' => array(
			'method'         => 'post',
			'endpoint'       => 'rcp-disable-analytics',
			'authentication' => 'rcp_authentication',
		),
		'ENABLE_ANALYTICS'  => array(
			'method'         => 'post',
			'endpoint'       => 'rcp-enable-analytics',
			'authentication' => 'rcp_authentication',
		),
		'GET_ANALYTICS'     => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-get-analytics',
			'authentication' => 'rcp_authentication',
		),
		'LIST_POSTS'        => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-list-posts',
			'authentication' => 'rcp_authentication',
		),
		'LIST_CATEGORIES'   => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-list-categories',
			'authentication' => 'rcp_authentication',
		),
		'LIST_USERS'        => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-list-users',
			'authentication' => 'rcp_authentication',
		),
		'FIND_POST'         => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-find-post',
			'authentication' => 'rcp_authentication',
		),
		'VERSION'           => array(
			'method'         => 'get',
			'endpoint'       => 'rcp-wp-version',
			'authentication' => 'rcp_authentication',
		),
	);

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->response    = new Response();
		$this->admin       = new PluginAdmin( $plugin_name, $version );
	}

	/**
	 * Define endpoints of the integration
	 *
	 * @since 1.0.0
	 */
	public function rcp_define_endpoints() {
		add_rewrite_endpoint( $this->endpoints['ACTIVATE']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['PUBLISH_POST']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['DISCONNECT']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['DISABLE_ANALYTICS']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['ENABLE_ANALYTICS']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['GET_ANALYTICS']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['LIST_CATEGORIES']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['LIST_POSTS']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['LIST_USERS']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['FIND_POST']['endpoint'], EP_ROOT );
		add_rewrite_endpoint( $this->endpoints['VERSION']['endpoint'], EP_ROOT );

		// Flag checking is necessary because flush_rewrite_rules() is an expensive operation.
		if ( ! get_option( 'rcp_rewrite_rules_were_flushed', false ) ) {
			flush_rewrite_rules();
			update_option( 'rcp_rewrite_rules_were_flushed', true );
		}
	}

	/**
	 * Intercept request
	 *
	 * @since 1.0.0
	 */
	public function intercept_request() {
		global $wp_query;
		foreach ( $this->endpoints as $name => $endpoint ) {

			if ( isset( $wp_query->query_vars[ $endpoint['endpoint'] ] ) ) {
				$data = Authentication::preserved_data( $endpoint['method'] );

				switch ( $endpoint['authentication'] ) {
					case 'rcp_authentication':
						$data = Authentication::authenticate( $endpoint['method'] );
						break;
					case 'public':
						break;
				}

				$func = 'handle_' . str_replace( '-', '_', $endpoint['endpoint'] ) . '_request';
				$this->$func( $data );

				exit;
			}
		}

		return;
	}

	/**
	 * Activate response
	 *
	 * @param date $activated_at date of activation.
	 *
	 * @since 2.0.0
	 */
	public function rcp_activate_response( $activated_at ) {
		Response::respond_with(
			200,
			array(
				'success'      => 'wordpress was successfully integrated',
				'activated_at' => $activated_at,
			)
		);
	}

	/**
	 * Activate plugin request
	 *
	 * @param array $data of the post.
	 *
	 * @since 2.0.0
	 */
	public function handle_rcp_activate_plugin_request( $data = null ) {
		if ( $activated_at = $this->admin->integrate() ) {// phpcs:ignore 
			if ( 'RC2' == $data['application_version'] ) {
				$this->rc2_activate_response( $activated_at );
			} else {
				$this->rcp_activate_response( $activated_at );
			}
		} else {
			Response::respond_with(
				500,
				array(
					'error_code' => self::$errors['INTEGRATION_FAILED'],
					'errors'     => array( 'integration failed' ),

				)
			);
		}
	}

	/**
	 * Request for disconnect the integration
	 *
	 * @param array $data data array.
	 *
	 * @since 2.0.0
	 */
	public function handle_rcp_disconnect_plugin_request( $data ) {
		$disconnected_at = $this->admin->disconnect();

		if ( 'RC2' == $data['application_version'] ) {
			Response::respond_with(
				200,
				array(
					'disconnected_at' => $disconnected_at,
				)
			);
		} else {
			Response::respond_with(
				200,
				array(
					'success' => 'wordpress disconnected successfully',
				)
			);
		}
	}

	/**
	 * Response returned when integration was successfully
	 *
	 * @param string $activated_at date of integration.
	 *
	 * @since 2.0.0
	 */
	public function rc2_activate_response( $activated_at ) {
		Response::respond_with(
			200,
			array(
				'credentials' => array(
					'url'   => $this->admin->get_url(),
					'token' => $this->admin->get_token(),
				),
				'data'        => array(
					'activated_at'      => $activated_at,
					'rcp_version'       => $this->version,
					'wordpress_version' => get_bloginfo( 'version' ),
					'php_version'       => PHP_VERSION,
				),
			)
		);
	}

	/**
	 * Publish content
	 *
	 * @param array $data data of the post.
	 *
	 * @since   2.0.0
	 */
	public function handle_rcp_publish_content_request( $data ) {

		$post = array(
			'post_title'   => sanitize_text_field( $data['post_title'] ),
			'post_content' => wp_kses_post( $data['post_content'] ),
			'post_status'  => sanitize_text_field( $data['post_status'] ),
			'post_author'  => sanitize_text_field( $data['post_author'] ),
		);

		if ( 'RC2' == $data['application_version'] ) {
			if ( isset( $data['post_category'] ) ) {
				$post['post_category'] = array( $data['post_category'] );
			}
		} else {
			if ( isset( $data['terms']['category'] ) ) {
				$post['post_category'] = $data['terms']['category'];
			}
		}

		if ( ! empty( $data['post_tags'] ) ) {
			$tags               = explode( ',', $data['post_tags'] );
			$post['tags_input'] = $tags;
		}

		if ( ! empty( $data['post_name'] ) ) {
			$post['post_name'] = $data['post_name'];
		}

		$featured_image      = $data['featured_image'];
		$featured_image_name = $data['featured_image_name'];

		try {
			$this->validate_post_content_request( $post );

			$post_id = $this->publish_post( $post );

			update_post_meta( $post_id, 'published_by_studio', true );

			if ( ! empty( $featured_image ) ) {
				$this->upload_featured_image( $featured_image, $post_id, $featured_image_name );
			}

			$post = $this->find_post( $post_id );

			Response::respond_with( 200, $post );

		} catch ( ContentException $e ) {
			Response::respond_with( $e->getCode(), $e->GetOptions() );
		}
	}

	/**
	 * Validate post content
	 *
	 * @param array $post data of the post.
	 *
	 * @throws \ContentException Content exception.
	 *
	 * @since 1.0.0
	 */
	private function validate_post_content_request( $post ) {
		$errors = array();
		if ( empty( $post['post_title'] ) ) {
			$errors['post_title'] = 'post_title is required';
		}
		if ( empty( $post['post_content'] ) ) {
			$errors['post_content'] = 'post_content is required';
		}
		if ( empty( $post['post_status'] ) ) {
			$errors['post_status'] = 'post_status is required';
		}

		if ( ! empty( $errors ) ) {
			throw new ContentException(
				null,
				403,
				null,
				array(
					'error_code' => self::$errors['INVALID_POST_FIELDS'],
					'errors'     => $errors,
				)
			);
		}
	}

	/**
	 * Publish the post
	 *
	 * @param array $post_attrs Attributes of the post.
	 *
	 * @return int|WP_Error
	 * @throws ContentException Content exception.
	 *
	 * @since 1.0.0
	 */
	private function publish_post( $post_attrs = array() ) {

		$post_id = wp_insert_post( $post_attrs );

		if ( is_wp_error( $post_id ) ) {
			$errors = $post_id->get_error_messages();

			$errors['error_code'] = self::$errors['INVALID_WP_POST_FIELDS'];
			throw new ContentException( null, 403, null, $errors );
		}

		return $post_id;
	}

	/**
	 * Upload featured image
	 *
	 * @param string $image_url url of the image.
	 * @param string $post_id id of the post.
	 * @param string $image_name name of the image.
	 *
	 * @since 1.0.0
	 *
	 * @since 1.1.0
	 *
	 * @since 1.2.0
	 *
	 * @since 1.2.2
	 */
	private function upload_featured_image( $image_url, $post_id, $image_name = null ) {
		$image_url = $this->remove_query_strings( $image_url );
		$src       = @media_sideload_image( $image_url, $post_id, $image_name, 'src' );
		$attach_id = $this->get_attatchment_id( $src );
		update_post_meta( $post_id, '_thumbnail_id', $attach_id );
	}

	/**
	 * Get attatchment id
	 *
	 * @param string $image_url url of the image.
	 *
	 * @return mixed
	 *
	 * @since 1.1.0
	 */
	public function get_attatchment_id( $image_url ) {
		global $wpdb;
		$table_name = $wpdb->posts;
		$attachment = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM `%1$s` WHERE guid = %2$s', $table_name, $image_url ) );

		return ! empty( $attachment ) ? $attachment[0] : attachment_url_to_postid( $image_url );
	}

	/**
	 * Remove query strings
	 *
	 * @param string $url url.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function remove_query_strings( $url ) {
		$pos = strpos( $url, '?' );

		if ( false !== $pos ) {
			$url = substr( $url, 0, $pos );
		}

		return $url;
	}

	/**
	 * Find a post
	 *
	 * @param int $id id of the post.
	 *
	 * @return array|bool
	 *
	 * @since 1.0.0
	 */
	private function find_post( $id ) {
		$post_object = get_post( $id );

		if ( ! $post_object ) {
			return false;
		}

		$post = get_object_vars( $post_object );
		$post = $this->parametrize_post_response( $post );

		return $post;
	}

	/**
	 * Parametrize post response
	 *
	 * @param array $post the post.
	 *
	 * @since 1.0.0
	 */
	private function parametrize_post_response( $post ) {
		\RockContent\Core\PluginInit::_rename_arr_key( 'ID', 'post_id', $post );
		\RockContent\Core\PluginInit::_rename_arr_key( 'post_author', 'author', $post );

		$post['featured_image'] = wp_get_attachment_url( get_post_thumbnail_id( $post['post_id'] ) );
		$post['terms']          = $this->get_post_categories( $post['post_id'] );
		$post['link']           = get_permalink( $post['post_id'] );

		return $post;
	}

	/**
	 * Get post categories
	 *
	 * @param int $post_id id of the post.
	 *
	 * @since 1.0.0
	 */
	private function get_post_categories( $post_id ) {
		$post_categories = wp_get_post_categories( $post_id );
		$cats            = array();

		foreach ( $post_categories as $c ) {
			$cat    = get_category( $c );
			$cats[] = $this->parametrize_category( $cat );
		}

		return $cats;
	}

	/**
	 * Parametrize category
	 *
	 * @param object $category the category.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function parametrize_category( $category ) {
		return array(
			'term_id'  => (int) $category->term_id,
			'name'     => $category->name,
			'slug'     => $category->slug,
			'taxonomy' => 'category',
		);
	}

	/**
	 * Parametrize user
	 *
	 * @param object $user the user.
	 *
	 * @return array
	 *
	 * @since 1.0.1
	 */
	private function parametrize_user( $user ) {
		return array(
			'user_id'      => (int) $user->data->ID,
			'display_name' => $user->data->user_login,
			'email'        => $user->data->user_email,
			'roles'        => $user->roles,
		);
	}

	/**
	 * Response with wp version.
	 *
	 * @since 1.0.0
	 */
	public function handle_rcp_wp_version_request() {

		Response::respond_with(
			200,
			array(
				'software_version' => array(
					'value' => get_bloginfo( 'version' ),
				),
				'rcp_version'      => array(
					'value' => $this->version,
				),
			)
		);
	}

	/**
	 * List posts request
	 *
	 * @param array $data of the post.
	 *
	 * @since 1.0.0
	 */
	public function handle_rcp_list_posts_request( $data = null ) {

		try {
			$this->validate_get_posts_request( $data );

			$posts = get_posts( $this->build_get_posts_params( $data ) );

			foreach ( $posts as $i => $post ) {
				$posts[ $i ] = $this->parametrize_post_response( get_object_vars( $post ) );
			}

			Response::respond_with( 200, $posts );
		} catch ( ContentException $e ) {
			Response::respond_with( $e->getCode(), $e->GetOptions() );
		}
	}

	/**
	 * Validate get posts request
	 *
	 * @param array $data data of the post.
	 *
	 * @throws ContentException Content exception.
	 *
	 * @since 1.0.0
	 */
	private function validate_get_posts_request( $data ) {
		$errors = array();

		if ( ! isset( $data['post_status'] ) ) {
			$errors[] = 'post_status parameter is required';
		}

		if ( ! empty( $errors ) ) {
			throw new ContentException(
				null,
				403,
				null,
				array(
					'error_code' => self::$errors['POST_STATUS_REQUIRED'],
					'errors'     => $errors,
				)
			);
		}
	}

	/**
	 * Get post params
	 *
	 * @param array $data the post.
	 *
	 * @since 1.0.0
	 */
	private function build_get_posts_params( $data ) {
		$params = array();

		$params['posts_per_page'] = isset( $data['number'] ) ? intval( $data['number'] ) : 20;
		$params['offset']         = isset( $data['offset'] ) ? intval( $data['offset'] ) : 0;
		$params['post_status']    = isset( $data['post_status'] ) ? $data['post_status'] : 'publish';
		$params['post_type']      = isset( $data['post_type'] ) ? $data['post_type'] : 'post';

		return $params;
	}

	/**
	 * List categories request
	 *
	 * @since 1.0.0
	 */
	public function handle_rcp_list_categories_request() {
		$categories = $this->get_filtered_categories();

		Response::respond_with( 200, $categories );
	}

	/**
	 * Enable Rock analytics
	 *
	 * @since 2 5.0
	 */
	public function handle_rcp_enable_analytics_request() {
		update_option( 'rcp_rock_analytics_enabled', true );
		Response::respond_with( 200, 'Rock analytics enabled successfully.' );
	}

	/**
	 * Disable Rock analytics
	 *
	 * @since 2.5.0
	 */
	public function handle_rcp_disable_analytics_request() {
		update_option( 'rcp_rock_analytics_enabled', false );
		Response::respond_with( 200, 'Rock analytics disabled successfully.' );
	}

	/**
	 * Responds if Rock analytics is enable
	 *
	 * @since 2.5.0
	 */
	public function handle_rcp_get_analytics_request() {
		$analytics_enabled = get_option( 'rcp_rock_analytics_enabled' );
		Response::respond_with(
			200,
			array(
				'enabled' => $analytics_enabled ? true : false,
				'msg'     => 'Rock analytics ' . ( $analytics_enabled ? 'is enabled' : 'is disabled' ),
			)
		);
	}

	/**
	 * Get filtered categories
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function get_filtered_categories() {
		$categories = get_categories();
		$filtered   = array();

		foreach ( $categories as $i => $category ) {
			array_push( $filtered, $this->parametrize_category( $category ) );
		}

		return $filtered;
	}

	/**
	 * List users request
	 *
	 * @since 1.0.0
	 */
	public function handle_rcp_list_users_request() {
		$users = $this->get_filtered_users();

		Response::respond_with( 200, $users );
	}

	/**
	 * Get filtered users
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 *
	 * @updated 1.0.1
	 */
	private function get_filtered_users() {
		$users    = get_users();
		$filtered = array();

		foreach ( $users as $i => $user ) {
			array_push( $filtered, $this->parametrize_user( $user ) );
		}

		return $filtered;
	}

}
