<?php

namespace Nicholas\Abstracts;


use Underpin\Rest_Endpoints\Abstracts\Rest_Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Nicholas_Endpoint extends Rest_Endpoint{
	public $rest_namespace = 'nicholas/v1';

}