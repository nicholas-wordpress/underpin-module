<?php

namespace Nicholas\Scripts;


use Underpin_Scripts\Abstracts\Script;
use function Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Editor extends Script {

	public function __construct() {
		$this->handle      = 'theme-editor';
		$this->src         = get_template_directory_uri() . '/build/editor.js';
		$this->deps        = nicholas()->dir() . 'build/editor.asset.php';
		$this->name        = 'Editor Script';
		$this->description = 'Admin Editor Customizations';
		parent::__construct();
	}

	public function do_actions() {
		parent::do_actions();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		if ( get_current_screen()->is_block_editor() ) {
			parent::enqueue();
		}
	}

}