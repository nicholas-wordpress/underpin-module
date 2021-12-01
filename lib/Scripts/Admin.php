<?php

namespace Nicholas\Scripts;


use Underpin\Scripts\Abstracts\Script;
use function Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin extends Script {

	public function __construct() {
		$this->handle      = 'admin';
		$this->src         = nicholas()->asset_url() . 'admin.js';
		$this->deps        = nicholas()->asset_dir() . 'admin.asset.php';
		$this->name        = 'Admin Script';
		$this->description = 'Admin Customizations';
		parent::__construct();
	}

	public function do_actions() {
		parent::do_actions();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( "admin_menu", [ $this, 'setup_admin_page' ] );
	}

	public function setup_admin_page() {
		add_submenu_page(
			'options-general.php',
			'Nicholas Settings',
			'Nicholas Settings',
			'administrator',
			'nicholas-settings',
			function () {
				echo '<div id="app"></div>';
			} );
	}

	public function enqueue() {
		$is_nicholas_settings = get_current_screen()->base === 'settings_page_nicholas-settings';

		if ( $is_nicholas_settings ) {
			parent::enqueue();
			// Root URL
			wp_add_inline_script(
				$this->handle,
				sprintf( 'admin.fetch.use( admin.fetch.createRootURLMiddleware( "%s" ) )', rest_url() )
			);

			// Nonce
			wp_add_inline_script(
				$this->handle,
				sprintf( 'admin.fetch.use( admin.fetch.createNonceMiddleware( "%s" ) )', wp_create_nonce( 'wp_rest' ) )
			);

		}
	}

}