<?php

namespace Nicholas;

use Underpin\Abstracts\Underpin;


// Require the Theme's Underpin instance.
require_once( trailingslashit( dirname( __FILE__ ) ) . 'lib/Nicholas.php' );


/**
 * Fetches the instance of the theme.
 * This function makes it possible to access everything else in this theme.
 * It will automatically initiate the plugin, if necessary.
 * It also handles autoloading for any class in the plugin.
 *
 * Check out lib/Theme.php - most of the magic happens there.
 *
 * @since 1.0.0
 *
 * @return Underpin|Nicholas The bootstrap for this theme.
 */
function nicholas() {
	return ( new Nicholas )->get( __FILE__ );
}