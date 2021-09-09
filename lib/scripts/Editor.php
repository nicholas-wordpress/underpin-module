<?php

namespace Nicholas\Scripts;


use Underpin_Scripts\Abstracts\Script;
use function Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Editor extends Script {

	public function __construct() {
		$this->handle      = 'nicholas-editor';
		$this->src         = nicholas()->asset_url() . 'editor.js';
		$this->deps        = nicholas()->asset_dir() . 'editor.asset.php';
		$this->name        = 'Editor Script';
		$this->description = 'Admin Editor Customizations';
		parent::__construct();
	}

	public function do_actions() {
		parent::do_actions();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		// Only load this on block editor pages that have a CPT.
		if ( get_current_screen()->is_block_editor() && get_post_type() ) {
			parent::enqueue();
		}
	}

}