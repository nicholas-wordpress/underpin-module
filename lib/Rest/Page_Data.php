<?php

namespace Nicholas\Rest;


use Nicholas\Abstracts\Nicholas_Endpoint;
use Nicholas\Nicholas;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Page_Data extends Nicholas_Endpoint {

	public $name        = 'Page Data Endpoint';
	public $description = 'Fetches data about the specified page';
	public $route       = '/page-info';

	public function get_postdata( $url ) {
		global $wp_query;

		$post_type = get_post_type();
		$path      = '/wp/v2/';

		switch ( $post_type ) {
			case 'post':
				$path .= 'posts';
				break;
			case 'page':
				$path .= 'pages';
				break;
			default:
				$path .= $post_type;
		}

		$post_ids = wp_list_pluck( $wp_query->posts, 'ID' );

		$preload_route = [ 'path' => $path, 'args' => [ 'include' => implode( ',', $post_ids ) ] ];

		$posts = Nicholas::request( 'GET', $preload_route['path'], $preload_route['args'] );

		// Move on to the next one if this URL does not have posts
		if ( ! is_wp_error( $posts ) ) {
			$posts = $posts['body'];
		} else {
			$posts = [];
			$type  = '404';
		}

		// Get things specific to this query.
		$body_class = array_values( get_body_class() );

		if ( ! isset( $type ) ) {
			switch ( true ) {
				case is_singular():
					$type = 'singular';
					break;
				case is_archive():
					$type = 'archive';
					break;
				case is_date():
					$type = 'date';
					break;
				case is_search():
					$type = 'search';
					break;
				case is_paged():
					$type = 'paged';
					break;
				case is_404():
					$type = '404';
					break;
				default:
					$type = 'archive';
			}
		}

		$pagination = Nicholas::get_buffer( 'the_posts_pagination' );
		$result     = [
			'post_type'     => $post_type,
			'comments_open' => comments_open(),
			'posts'         => $posts,
			'body_class'    => $body_class,
			'type'          => $type,
			'pagination'    => $pagination,
		];

		/**
		 * Filters the page info inside Nicholas.
		 *
		 * @since 1.0.1
		 *
		 * @param                 $result  The processed result thus-far.
		 * @param WP_REST_Request $request The submitted request
		 */
		$result = apply_filters( 'nicholas/rest_endpoints/page_info', $result, $url );

		return $result;
	}

	public function endpoint( WP_REST_Request $request ) {
		$result = [];

		if ( ! empty( $request->get_param( 'path' ) ) ) {
			$paths = [ $request->get_param( 'path' ) ];
		} else {
			$paths = explode( ',', $request->get_param( 'paths' ) );
		}

		foreach ( $paths as $path ) {
			$result[] = Nicholas::with_path( $path, [ $this, 'get_postdata' ] );
		}

		return rest_ensure_response( $result );
	}

	function has_permission( WP_REST_Request $request ) {
		return true;
	}

}