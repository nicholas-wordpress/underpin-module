<?php

namespace Nicholas\Rest_Endpoints;


use Nicholas\Abstracts\Nicholas_Endpoint;
use Nicholas\Nicholas;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Compatibility_Mode_Urls extends Nicholas_Endpoint {

	public $name                    = 'Compatibility Mode URLS';
	public $description             = 'Retrieves compatibility mode URLS';
	public $route                   = '/compatibility-mode-urls';

	function endpoint( WP_REST_Request $request ) {
		return rest_ensure_response( Nicholas::get_compatibility_mode_urls() );
	}


	function has_permission( WP_REST_Request $request ) {
		return true;
	}

}