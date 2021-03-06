<?php


namespace Nicholas\Scripts;


use Underpin\Scripts\Abstracts\Script;
use function Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Theme extends Script {

	/**
	 * Data to preload
	 *
	 * @var array
	 */
	protected $preload_data = [];

	public function __construct() {
		$this->handle        = 'theme';
		$this->localized_var = 'theme_vars';
		$this->src           = nicholas()->asset_url() . 'theme.js';
		$this->deps          = nicholas()->asset_dir() . 'theme.asset.php';
		$this->name          = 'Theme Object';
		$this->in_footer     = true;
		$this->description   = 'The global theme object';
		parent::__construct();
	}

	public function enqueue() {
		$this->preload_query();

		// Enqueue Script
		parent::enqueue();

		//Setup inline scripts
		wp_add_inline_script(
			$this->handle,
			sprintf( 'theme.fetch.use( theme.fetch.createRootURLMiddleware( "%s" ) );', rest_url() )
		);


		if ( ! empty( $this->preload_data ) ) {
			wp_add_inline_script(
				$this->handle,
				sprintf( 'theme.fetch.use( theme.fetch.createPreloadingMiddleware( %s ) )', wp_json_encode( $this->preload_data ) )
			);
		}
	}

	/**
	 * Fetches data, and attempts to preload as well.
	 *
	 * @since 1.0.0
	 *
	 * @param $endpoint
	 * @param $params
	 *
	 * @return array|\WP_Error|null
	 */
	public function preload_request( $endpoint, $params = [] ) {
		$request = \Nicholas\Nicholas::request( 'GET', $endpoint, $params );

		if ( ! is_wp_error( $request ) ) {
			$this->preload_data[ add_query_arg( $params, $endpoint ) ] = $request;
		}

		return $request;
	}

	/**
	 * Preload Query.
	 * Preload data retrieved from the database for JS consumption.
	 *
	 * @since 1.0.0
	 *
	 * WordPress automatically retrieves a WP_Query object for every page. This function is intended to
	 * leverage the object cache to preload this data for the REST API.
	 */
	public function preload_query() {
		$endpoint = '/nicholas/v1/page-info';
		$url      = wp_parse_url( $_SERVER['REQUEST_URI'] );
		$args     = [ 'path' => $url['path'] ];
		$this->preload_request( $endpoint, $args );

		$this->set_param( 'preloaded_endpoint', add_query_arg( $args, $endpoint ) );
	}

}