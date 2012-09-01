<?php
/*
	Support class PWA+PHP plugin
	Copyright (c) 2011, 2012 by Scott M

	GNU General Public License version 3

	Copyright (c) 2011, 2012 Scott M

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('pwaplusphp-const.php');

#--------------------------------------------------------------
# Class pwa_dep
# Since: 1.0
# Define the Class
# Used in: -class
#--------------------------------------------------------------
if (!class_exists('pwa_dep')) {
	class pwa_dep {


		#--------------------------------------------------------------
		# _depreciated_shortcode
		# Since: 1.0
		# creates a depreciated notice
		# Used in: -self
		#--------------------------------------------------------------
		function _deprecated_shortcode( $shortcode, $version, $replacement = null ) {

			do_action( 'deprecated_shortcode_run', $shortcode, $replacement, $version );

			// Allow plugin to filter the output error trigger
			if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {
				if ( ! is_null($replacement) )
					trigger_error( sprintf( __('%1$s shortcode is <strong>deprecated</strong> since PWA+PHP version %2$s! Use %3$s instead.'), $shortcode, $version, $replacement ) );
				else
					trigger_error( sprintf( __('%1$s shortcode is <strong>deprecated</strong> since PWA+PHP version %2$s with no alternative available.'), $shortcode, $version ) );
			}
		}


		#--------------------------------------------------------------
		# Shortcode Album Random Photo 
		# Since: 0.1
		# Depreciated: 0.9.6
		# Deprecated: [pwaplusphp album="random_photo"]
		# Use: [pwaplusphp random_photos="1"]
		# Param: random_photos #
		# Used in: -self
		#--------------------------------------------------------------
		function shortcode_album_random_photo($overrides_array) {
			self::_deprecated_shortcode( 'album="random_photo"', '0.9.6', 'random_photos="1"' );
			$out = pwaplusphp::randomPhoto($overrides_array); 
			return $out;
		}

	} // End Class
} // End If
?>
