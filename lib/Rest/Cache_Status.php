<?php


namespace Nicholas\Rest;


use Nicholas\Abstracts\Nicholas_Endpoint;
use WP_REST_Request;
use function Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cache_Status extends Nicholas_Endpoint {

	public $name           = 'Cache Status';
	public $description    = 'Fetches info about the cache';
	public $route          = '/cache-status';

	function endpoint( WP_REST_Request $request ) {

		$last_updated = new \WP_Query( [
			'posts_per_page'      => 1,
			'post_type'           => get_post_types(),
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'modified',
		] );


		return rest_ensure_response( [
			'nicholas_last_updated' => nicholas()->options()->get( 'nicholas_last_updated' )->get(),
			'post_last_updated'  => date( 'U', strtotime( $last_updated->posts[0]->post_modified_gmt ) ),
		] );
	}

	function has_permission( WP_REST_Request $request ) {
		return true;
	}

}