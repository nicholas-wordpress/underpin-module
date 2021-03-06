<?php
/**
 * Nicholas
 *
 * Singleton instance of Underpin. Starts up the plugin, houses all loaders.
 * See https://github.com/underpin-WP/underpin
 *
 * @since 1.0.0
 */

namespace Nicholas;

use Underpin\Factories\Accumulator;
use Underpin\Factories\Observer;
use Underpin\Templates\Loaders\Templates;
use Underpin\Abstracts\Underpin;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Meta\Loaders\Meta;
use Underpin\Options\Loaders\Options;
use Underpin\Scripts\Loaders\Scripts;
use WP;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @method Scripts scripts() Script loader. All actions related to scripts goe through here.
 * @method Object_Registry rest_endpoints() Rest Endpoint loader. all actions related to REST go through here.
 * @method Templates templates() Template loader. All actions related to loading templates go through here.
 * @method Meta meta() Meta loader. All custom metadata is registered, and accessed through this.
 * @method Options options() Options loader. All options are registered, and accessed through this.
 */
class Nicholas extends Underpin {

	/**
	 * The namespace for loaders. Used for loader autoloading.
	 *
	 * @since 1.0.0
	 *
	 * @var string Complete namespace for all loaders.
	 */
	protected $root_namespace = 'Nicholas';

	/**
	 * Translation Text domain.
	 *
	 * Used by translation method for translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $text_domain = 'nicholas';

	/**
	 * Minimum PHP Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $minimum_php_version = '7.0';

	/**
	 * Minimum WordPress Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $minimum_wp_version = '5.8';

	/**
	 * Current Version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $version = '1.0.2';

	/**
	 * The current theme directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $asset_dir = '';

	/**
	 * The current theme url.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $asset_url = '';

	/**
	 * Setup plugin params using the provided __FILE__
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function _setup_params( $file ) {

		// Root file for this plugin. Used in activation hooks.
		$this->file = $file;

		// Root directory for this plugin.
		$this->dir = dirname($file);

		$this->url = plugin_dir_url( $this->file );

		// The CSS URL for this plugin. Used in asset loading.
		$this->css_url = $this->url . 'build';

		// The JS URL for this plugin. Used in asset loading.
		$this->js_url = $this->url . 'build';

		// The template directory. Used by the template loader to determine where templates are stored.
		$this->template_dir = $this->dir() . 'templates/';

		/**
		 * Filters the asset directory. Defaults to current theme root/build
		 *
		 * @since 1.0.0
		 * @param string $dir - The directory.
		 */
		$this->asset_dir = apply_filters( 'nicholas/asset_dir', trailingslashit( get_template_directory() ) . 'build' );

		/**
		 * Filters the asset directory. Defaults to current theme root/build
		 *
		 * @since 1.0.0
		 * @param string $dir - The url.
		 */
		$this->asset_url = apply_filters( 'nicholas/asset_url', trailingslashit( get_template_directory_uri() ) . 'build' );
	}

	/**
	 * Retrieve the theme's asset directory
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function asset_dir() {
		return trailingslashit( $this->asset_dir );
	}

	/**
	 * Retrieve the theme's asset url
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function asset_url() {
		return trailingslashit( $this->asset_url );
	}

	/**
	 * Simulates a REST API request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type     The type of request.
	 * @param string $endpoint The REST API endpoint.
	 * @param array  $params   List of params to pass to the endpoint.
	 *
	 * @return array|WP_Error|null
	 */
	public static function request( string $type, string $endpoint, array $params = [] ) {
		$type    = strtoupper( $type );
		$request = new \WP_REST_Request( $type, $endpoint );
		$request->set_query_params( $params );
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();

			nicholas()->logger()->log_wp_error( 'error', $error, [
				'ref'     => $endpoint,
				'context' => 'endpoint',
				'params'  => $params,
			] );

			return $error;
		}

		return [ 'body' => $response->get_data(), 'headers' => $response->get_headers() ];
	}

	/**
	 * Forces Nicholas' cache to be flushed on all devices.
	 *
	 * @since 1.0.2
	 *
	 * @return bool True if flushed, otherwise false.
	 */
	public static function flush_cache() {
		return nicholas()->options()->get( 'nicholas_last_updated' )->update( current_time( 'U', 1 ) );
	}

	/**
	 * Instructs the client to flush the cache on-load.
	 *
	 * @since 1.0.2
	 */
	public static function flush_session_cache() {
		if ( ! isset( $_COOKIE["nicholas_flush_cache"] ) ) {
			setcookie( 'nicholas_flush_cache', true );
		}
	}

	/**
	 * Returns true if this page should be loaded using compatibility mode.
	 *
	 * @since 1.0.0
	 *
	 * @return bool true if compatibility mode should be used, otherwise false.
	 */
	public function use_compatibility_mode() {
		return true === $this->apply_filters( 'use_compatibility_mode', new Accumulator( [
				'default'        => false,
				'url'            => wp_parse_url( $_SERVER['REQUEST_URI'] ),
				'valid_callback' => function ( $state ) {
					return is_bool( $state );
				},
			] ) );
	}

	/**
	 * Retrieves the list of compatibility mode URLs.
	 *
	 * @since 1.0.0
	 *
	 * @return false|mixed
	 */
	public static function get_compatibility_mode_urls() {
		$urls = wp_cache_get( 'nicholas_compatibility_mode_urls' );

		if ( false === $urls ) {
			$compat_mode_args = [
				'post_type'  => 'any',
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => 'use_compatibility_mode',
						'value'   => true,
						'compare' => '=',
					],
				],
			];

			$compat_mode_urls = self::get_urls_for_query( $compat_mode_args );

			$urls = nicholas()->options()->get( 'compatibility_mode_urls' )->get();

			if ( empty( $urls ) ) {
				$urls = [];
			}

			/**
			 * Filters the resulting list of URLs to force compatibility mode.
			 *
			 * @since 1.0.0
			 *
			 * @param [string] $urls list of URLs to enforce compatibility mode
			 */
			$urls = apply_filters( 'nicholas/compatibility_mode_urls', array_merge( $compat_mode_urls, $urls ) );

			// No need to send repeated URLs
			$urls = array_unique( $urls );

			// Cache this so we don't have to-do it again.
			wp_cache_add( 'nicholas_compatibility_mode_urls', maybe_serialize( $urls ) );
		} else {
			$urls = maybe_unserialize( $urls );
		}

		// Reset keys. This ensures REST responses don't mistake this for an object instead of an array.
		return array_values( $urls );
	}

	/**
	 * Retrieves the URLs from a WP_Query result set.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments. See WP_Query.
	 *
	 * @return array list of permalink URLs.
	 */
	public static function get_urls_for_query( $args ) {
		$defaults = [
			'fields'         => 'ids',
			'posts_per_page' => -1,
		];

		$args      = wp_parse_args( $args, $defaults );
		$query     = new \WP_Query( $args );
		$post_urls = [];

		foreach ( $query->posts as $post_id ) {
			$post_urls[] = get_the_permalink( $post_id );
		}

		return $post_urls;
	}

	/**
	 * Fetches an echo'd callback as a string.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The function to call
	 *
	 * @return string The output
	 */
	public static function get_buffer( callable $callback ) {
		ob_start();
		$callback();
		$result = ob_get_clean();

		return false === $result ? '' : $result;
	}

	/**
	 * Runs a callback in the context of the specified path.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $path     The local URL path to call.
	 * @param callable $callback The callback to run.
	 *
	 * @return mixed The result of callback, in the context of the specified path.
	 */
	public static function with_path( string $path, callable $callback ) {
		global $wp, $post, $wp_query;

		// Store the original WP instance and request URI to reset later
		$old_wp      = $wp;
		$old_post    = $post;
		$request_uri = $_SERVER['REQUEST_URI'];

		// Trick WordPress into thinking we're on a different URL
		$uri                    = trailingslashit( $path );
		$_SERVER['REQUEST_URI'] = $uri;
		$wp                     = new WP();

		foreach ( get_post_types( [], 'objects' ) as $post_type ) {
			$post_type->add_rewrite_rules();
		}

		foreach( get_taxonomies([],'objects') as $taxonomy ){
			$taxonomy->add_rewrite_rules();
		}

		$wp->parse_request();
		query_posts( $wp->query_vars );
		$post = get_post( $wp_query->posts[0] );

		$result = $callback( $path );

		// Put everything back. Nothing to see here!
		$wp                     = $old_wp;
		$_SERVER['REQUEST_URI'] = $request_uri;
		$post                   = $old_post;
		wp_reset_query();

		return $result;
	}

	/**
	 * Function to setup this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function _setup() {
		/**
		 * Register default scripts.
		 */
		$this->scripts()->add( 'theme', 'Nicholas\Scripts\Theme' );
		$this->scripts()->add( 'editor', 'Nicholas\Scripts\Editor' );
		$this->scripts()->add( 'admin', 'Nicholas\Scripts\Admin' );
		$this->scripts()->add( 'session_manager', [
			'handle'      => 'sessionManager',
			'src'         => $this->asset_url() . 'sessionManager.js',
			'deps'        => $this->asset_dir() . 'sessionManager.asset.php',
			'name'        => 'Nicholas Session Manager',
			'description' => 'Clears the current session data when the server instructs it to-do so.',
			// Enqueue Session manager on admin and login screens
			'middlewares' => [
				new \Underpin\Scripts\Factories\Enqueue_Admin_Script('admin_enqueue'),
				new \Underpin\Scripts\Factories\Enqueue_Login_Script('login_enqueue')
			]
		] );

		/**
		 * Register REST Endpoints
		 */
		$this->rest_endpoints()->add( 'page_data', 'Nicholas\Rest_Endpoints\Page_Data' );
		$this->rest_endpoints()->add( 'compatibility_mode_urls', 'Nicholas\Rest_Endpoints\Compatibility_Mode_Urls' );
		$this->rest_endpoints()->add( 'get_settings', 'Nicholas\Rest_Endpoints\Get_Settings' );
		$this->rest_endpoints()->add( 'update_settings', 'Nicholas\Rest_Endpoints\Update_Settings' );
		$this->rest_endpoints()->add( 'last_updated', 'Nicholas\Rest_Endpoints\Cache_Status' );
		$this->rest_endpoints()->add( 'last_updated', 'Nicholas\Rest_Endpoints\Comment_Output' );

		/**
		 * Register Options
		 */
		$this->options()->add( 'compatibility_mode_urls', 'Nicholas\Options\Compatibility_Mode_Urls' );
		$this->options()->add( 'nicholas_last_updated', [
			'key'           => 'nicholas_last_updated',
			'default_value' => '',
		] );

		/**
		 * Register Meta
		 */
		$this->meta()->add( 'use_compatibility_mode', [
			'key'                     => 'use_compatibility_mode',
			'description'             => 'Determines if this page should be loaded using compatibility mode',
			'name'                    => 'Use Compatibility Mode',
			'default_value'           => false,
			'type'                    => 'post',
			'field_type'              => 'boolean',
			'show_in_rest'            => true,
			'has_permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'sanitize_callback'       => function ( $meta_value ) {
				settype( $meta_value, 'boolean' );

				return $meta_value;
			},
		] );

		/**
		 * Decide on compatibility mode.
		 */
		self::attach( 'use_compatibility_mode', new Observer( 'compatibility_mode_forced', [
			'name'           => 'Compatibility Mode Forced',
			'description'    => 'Returns true when the compatibility-mode query param is set',
			'valid_callback' => function ( $state, Accumulator $accumulator ) {
				if ( isset( $_REQUEST['compatibility-mode'] ) ) {
					$accumulator->update( true );
				}
			},
		] ) );

		self::attach( 'use_compatibility_mode', new Observer( 'is_compatibility_mode_url', [
			'name'           => 'Compatibility Mode Forced',
			'description'    => 'Returns true when the compatibility-mode query param is set',
			'valid_callback' => function ( $state, Accumulator $accumulator ) {
				// Get the current path.
				$current_path = $accumulator->url['path'];

				foreach ( self::get_compatibility_mode_urls() as $url ) {
					$url = wp_parse_url( $url )['path'];

					// If the paths match, this should use compatibility mode.
					if ( $url === $current_path ) {
						$accumulator->update( true );
						return;
					}
				}
			},
		] ) );

		// Maybe enqueue extra scripts for the app
		add_action( 'wp_enqueue_scripts', function () {
			if ( ! $this->use_compatibility_mode() ) {
				if ( get_option( 'thread_comments' ) ) {
					wp_enqueue_script( 'comment-reply' );
				}

				/**
				 * Fires when a page is not loaded using compatibility mode.
				 * Use this hook to enqueue styles and scripts and reduce the number of compatibility mode pages
				 * on your site.
				 *
				 *
				 * @since 1.0.0
				 */
				do_action( 'nicholas/enqueue_app_scripts' );
			}
		} );

		// Force this session to clear the cache when a user logs in, or logs out.
		add_action( 'wp_logout', [ $this, 'flush_session_cache' ] );
		add_action( 'wp_login', [ $this, 'flush_session_cache' ] );
	}


}