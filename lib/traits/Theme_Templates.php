<?php
/**
 * Template Loader Trait
 * Handles template loading and template inheritance.
 *
 * @since   1.0.0
 * @package Underpin\Traits
 */

namespace Underpin_Nicholas\Traits;

use Underpin\Traits\Templates;
use function Underpin_Nicholas\nicholas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme-specific Template Trait.
 * Creates templates based off of the location of Underpin.
 *
 * @since   1.0.0
 * @package underpin\traits
 */
trait Theme_Templates {
	use Templates;

	protected function get_template_root_path() {
		return nicholas()->template_dir();
	}

	/**
	 * Checks to see if the template file exists.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to check.
	 *
	 * @return bool True if the template file exists, false otherwise.
	 */
	public function template_file_exists( $template_name ) {
		return strlen( $this->locate_template( $template_name ) ) > 0;
	}


	/**
	 * Specify the override directory for other themes and plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_override_dir() {
		return 'templates/';
	}

}