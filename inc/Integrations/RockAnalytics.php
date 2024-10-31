<?php // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace RockContent\Integrations;

/**
 * Add Rock analytics.
 *
 * This class defines all code necessary to insert rock analytics code into pages.
 *
 * @link       https://rockcontent.com/
 * @since      2.5.0
 * @package    PluginInit
 * @subpackage PluginInit/includes
 * @author     Rock Content <plugin@rockcontent.com>
 */
class RockAnalytics {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( ! defined( 'ROCK_ANALYTICS_ENDPOINT' ) ) {
			define( 'ROCK_ANALYTICS_ENDPOINT', 'https://ra.rockcontent.com' );
		}
		if ( ! defined( 'ROCK_ANALYTICS_SCRIPT' ) ) {
			define( 'ROCK_ANALYTICS_SCRIPT', 'https://cdn-ra.rockcontent.com/ra.js' );
		}
	}

	/**
	 * Get post analytics data.
	 *
	 * @param object $post the post.
	 *
	 * @since    1.0.0
	 */
	public function get_post_analytics_data( $post ) {
		return array(
			'post_id'             => $post->ID,
			'post_type'           => $post->post_type,
			'post_date'           => strtotime( $post->post_date ),
			'post_author'         => get_the_author_meta( 'display_name', $post->post_author ),
			'categories_json'     => wp_json_encode( self::setup_chorus_analytics_get_post_categories( $post->ID ) ),
			'tags_json'           => wp_json_encode( self::setup_chorus_analytics_get_post_tags( $post->ID ) ),
			'word_count'          => self::setup_chorus_analytics_get_post_word_count( $post->ID ),
			'published_by_studio' => ( get_post_meta( $post->ID, 'published_by_studio', true ) ? 'true' : 'false' ),
		);
	}

	/**
	 * Get post categories.
	 *
	 * @param object $post_id the post id.
	 *
	 * @since    1.0.0
	 */
	public static function setup_chorus_analytics_get_post_categories( $post_id ) {
		$categories = get_the_category( $post_id );
		if ( ! $categories ) {
			return '';
		}
		$cat_names = array_map(
			function ( $cat ) {
				return $cat->name;
			},
			$categories
		);
		return wp_json_encode( $cat_names );
	}

	/**
	 * Get post tag.
	 *
	 * @param object $post_id the post id.
	 *
	 * @since    1.0.0
	 */
	public static function setup_chorus_analytics_get_post_tags( $post_id ) {
		$tags = get_the_tags( $post_id );
		if ( ! $tags ) {
			return '';
		}
		$tag_names = array_map(
			function ( $tag ) {
				return $tag->name;
			},
			$tags
		);
		return wp_json_encode( $tag_names );
	}

	/**
	 * Get post word count.
	 *
	 * @param object $post_id the post id.
	 *
	 * @since    1.0.0
	 */
	public static function setup_chorus_analytics_get_post_word_count( $post_id ) {
		$content = get_post( $post_id )->post_content;
		if ( ! $content ) {
			return 0;
		}
		return str_word_count( strip_tags( $content ) );
	}

	/**
	 * Inject analytics amp.
	 *
	 * @param null $amp_template amp template.
	 *
	 * @since    1.0.0
	 */
	public static function inject_analytics_amp( $amp_template ) {

		if ( ! get_option( 'rcp_rock_analytics_enabled' ) ) {
			return;
		}

		if ( \RockContent\Core\Utils::stage_is_present() ) {
			return;
		}

		$post = get_post();
		$data = self::get_post_analytics_data( $post );

		$ip_address = ( ! empty( $_REQUEST['X-Forwarded-For'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) ?
			sanitize_text_field( wp_unslash( $_REQUEST['X-Forwarded-For'] ) ) :
			sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		$ip_address = wp_unslash( $ip_address );

		$querystring = isset( $_SERVER['QUERY_STRING'] ) ?
			sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) :
			null;
		if ( $querystring ) {
			$querystring = '?' . $querystring;
		}

		$amp_tag = '
            <amp-analytics>
            <script type="application/json">
            {
                "vars": {
                "anonymousid": "${clientId(cid,,ajs_anonymous_id)}",
                "userid":      "${clientId(ajs_user_id)}",
                "ip":          "' . $ip_address . '",
                "authorname":  "' . $data['post_author'] . '",
                "type":        "' . $data['post_type'] . '",
                "date":        "' . $data['post_date'] . '",
                "querystring": "' . $querystring . '",
                "categories":   ' . $data['categories_json'] . ',
                "tags":         ' . $data['tags_json'] . ',
                "content":     "' . $data['word_count'] . '",
                "studio":      "' . $data['published_by_studio'] . '"
                },
                "requests": {
                "host": "' . ROCK_ANALYTICS_ENDPOINT . '",
                "base": "?amp=1&context.library.name=amp&locale=${browserLanguage}&path=${canonicalPath}&url=${canonicalUrl}&referrer=${documentReferrer}&title=${title}&hostname=${ampdocHostname}&agent=${userAgent}&viewportwidth=${viewportWidth}&viewportheight=${viewportHeight}",
                "page": "${host}/page${base}&studio=${studio}&name=${name}&ip=${ip}&postAuthor=${authorname}&postCategories=${categories}&postTags=${tags}&postType=${type}&postDate=${date}&postWordCount=${content}&sentAt=${timestamp}&querystring=${querystring}&anonymousid=${anonymousid}&userid=${userid}&contentLoadTime=${contentLoadTime}&domainLookupTime=${domainLookupTime}&domInteractiveTime=${domInteractiveTime}&navRedirectCount=${navRedirectCount}&navType=${navType}&pageDownloadTime=${pageDownloadTime}&pageLoadTime=${pageLoadTime}&redirectTime=${redirectTime}&serverResponseTime=${serverResponseTime}&tcpConnectTime=${tcpConnectTime}&navigationStart=${navTiming(navigationStart)}&unloadEventStart=${navTiming(unloadEventStart)}&unloadEventEnd=${navTiming(unloadEventEnd)}&redirectStart=${navTiming(redirectStart)}&redirectEnd=${navTiming(redirectEnd)}&fetchStart=${navTiming(fetchStart)}&domainLookupStart=${navTiming(domainLookupStart)}&domainLookupEnd=${navTiming(domainLookupEnd)}&connectStart=${navTiming(connectStart)}&connectEnd=${navTiming(connectEnd)}&secureConnectionStart=${navTiming(secureConnectionStart)}&requestStart=${navTiming(requestStart)}&responseStart=${navTiming(responseStart)}&responseEnd=${navTiming(responseEnd)}&domLoading=${navTiming(domLoading)}&domInteractive=${navTiming(domInteractive)}&domContentLoadedEventStart=${navTiming(domContentLoadedEventStart)}&domContentLoadedEventEnd=${navTiming(domContentLoadedEventEnd)}&domComplete=${navTiming(domComplete)}&loadEventStart=${navTiming(loadEventStart)}&loadEventEnd=${navTiming(loadEventEnd)}"
                },
                "triggers": {
                "page": {
                "on": "visible",
                "request": "page"
                }
                }
            }
            </script>
            </amp-analytics>
        ';

		echo $amp_tag;
	}

	/**
	 * Inject analytics default
	 *
	 * @since 2.5.0
	 */
	public static function inject_analytics_default() {
		$data = array();

		if ( ! get_option( 'rcp_rock_analytics_enabled' ) ) {
			return;
		}

		if ( \RockContent\Core\Utils::stage_is_present() ) {
			return;
		}

		$blog_name = get_bloginfo( 'name' );

		if ( is_single() ) {
			$post = get_post();
			$data = self::get_post_analytics_data( $post );
		}

		wp_localize_script(
			'rcp_rock_analytics_script',
			'ajax_object',
			array(
				'blog_name'             => $blog_name,
				'data'                  => $data,
				'rock_analytics_script' => ROCK_ANALYTICS_SCRIPT,
			)
		);

	}

}
