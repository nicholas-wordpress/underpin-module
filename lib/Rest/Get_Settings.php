<?php

namespace Nicholas\Rest;


use Nicholas\Abstracts\Nicholas_Endpoint;
use WP_REST_Request;
use function Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_Settings extends Nicholas_Endpoint {

	public $name           = 'Get Settings Endpoint';
	public $description    = 'Fetches Nicholas settings';
	public $route          = '/settings';

	function endpoint( WP_REST_Request $request ) {
		return rest_ensure_response( [
			'nicholas_last_updated'      => nicholas()->options()->get( 'nicholas_last_updated' )->get(),
			'compatibility_mode_urls' => nicholas()->options()->get( 'compatibility_mode_urls' )->get(),
		] );
	}

	function has_permission( WP_REST_Request $request ) {
		return current_user_can( 'administrator' );
	}

}