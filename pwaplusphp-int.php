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
# Class pwa_int
# Since: 1.0
# Define the Class
# Used in: -admin, -album, -photo
#--------------------------------------------------------------
if (!class_exists('pwa_int')) {
	class pwa_int {

		#--------------------------------------------------------------
		# Class variables
		# Since: 1.0
		# Define the Class Variables
		# Used in: -self
		#--------------------------------------------------------------
		static $php_error = '';


		#--------------------------------------------------------------
		# Execute cURL
		# Since: 1.0
		# A function to Curl code to store XML data from PWA in a variable
		# Used in: -
		#--------------------------------------------------------------
		static function doCurlExec($file,$local_xml = 0, $skip_parse = 0) {
			$PUBLIC_ONLY = "FALSE";
			$access = get_option(c_pwa_access);
			$protected = strrpos($access, 'protected');
			$private = strrpos($access, 'private');
			if (($protected === false) && ($private === false)) { 
				$PUBLIC_ONLY = "TRUE";
			}

			# Get the gallery data from picasa
			if ($local_xml == 0) {

				# Curl code to store XML data from PWA in a variable
				$ch = curl_init();
				$timeout = 0; # set to zero for no timeout
				curl_setopt($ch, CURLOPT_URL, $file);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

				# Display only public albums if PUBLIC_ONLY=TRUE in config.php
				if ($PUBLIC_ONLY == "FALSE") {
					$GDATA_TOKEN = get_option("pwaplusphp_gdata_token");
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: AuthSub token="' . $GDATA_TOKEN . '"'
					));
				}


				$addressData=curl_exec($ch);
				curl_close($ch);
			}

			// Parse it into the object
			if ($skip_parse == 0) {

			#----------------------------------------------------------------------------
			# Parse the XML data into an array
			#----------------------------------------------------------------------------
			$p = xml_parser_create();
			xml_parse_into_struct($p, $addressData, $vals, $index);
			xml_parser_free($p);
			return ($vals);
		
			} else {

				return($addressData);

			}
		} # End doCurlExec


		#--------------------------------------------------------------
		# Setup the Caption
		# Since: 1.0
		# A function to Setup the Caption
		# Used in: -photo
		#--------------------------------------------------------------
		static function setupCaption($caption,$lightbox) {
			if ($lightbox == "HIGHSLIDE") {
				$return = "onclick=\"return hs.expand(this, { captionText: '$caption' } )\" alt='$caption' title='$caption'";
			} else {
				$return = "alt=\"$caption\" title=\"$caption\"";
			}
			return($return);
		} # End Setup the Caption


		#--------------------------------------------------------------
		# Check Permalink Structure
		# Since: 1.0
		# A function to
		# Used in: -album, photo
		#--------------------------------------------------------------
		static function check_permalink_structure($pwa_options) {
			$uri = $_SERVER["REQUEST_URI"];
			if ( get_option('permalink_structure') != '' ) { 
				# permalinks enabled
				$pwa_options['PERMALINKS_ON'] = 1;
				if (strrpos($uri,'?')) list($pwa_options['back_link'],$trash) = explode('?',$uri);
				$pwa_options['urlchar'] = '?';
				//$pwa_options['splitchar'] = '\?';
			} else {
				$pwa_options['PERMALINKS_ON'] = 0;
				if (strrpos($uri,'&')) list($pwa_options['back_link'],$trash) = explode('&',$uri);
				$pwa_options['urlchar'] = '&';
				//$pwa_options['splitchar'] = $pwa_options['urlchar'];
			}
			return $pwa_options;
		} # End Check Permalink Structure


		#--------------------------------------------------------------
		# Check For Wptouch
		# Since: 1.0
		# A function to Check if wptouch is enabled and adjust format
		# Used in: -album, -photo
		#--------------------------------------------------------------
		static function check_for_wptouch($pwa_options) {
			if (class_exists('WPtouchPlugin')) {
				global $wptouch_plugin;
				if ($wptouch_plugin->applemobile == "1") {
					$pwa_options['IMGMAX'] = "912";
					$pwa_options['SHOW_ALBUM_DETAILS'] = "FALSE";
					$pwa_options['PERMIT_IMG_DOWNLOAD'] = "FALSE";
					$pwa_options['CAPTION_LENGTH'] = "15";
					$pwa_options['SHOW_COMMENTS'] = "FALSE";
					$pwa_options['SHOW_EXIF'] = "FALSE";
					//$pwa_options['LAZY_LOAD'] = "FALSE";
				}
			}
			return $pwa_options;
		} # End Check For Wptouch


		#--------------------------------------------------------------
		# Get Gdata Token
		# Since: 1.0
		# A function to Get the Gdata Token
		# Used in: -admin
		#--------------------------------------------------------------
		static function get_gdata_token() {
			# Get main file name
			$main_file = str_replace('-int', '', __FILE__);
			$site = get_bloginfo('url');
			$port = ($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '';
			$self  = $_SERVER['PHP_SELF'];
			$loc  = urlencode($site . $port . $self . "?page=" . plugin_basename($main_file) ."&pwa_action=return");
			$next = "https://www.google.com/accounts/AuthSubRequest?scope=http%3A%2F%2Fpicasaweb.google";
			$next .= ".com%2Fdata%2F&session=1&secure=0&next=$loc";
			echo "<div id='message' class='updated fade pwa_notice'><h2>Install Step 1: Token Generation</h2>";
			echo "<p>Generating this Google \"GData\" token is a one-time step that allows PWA+PHP to";
			echo " access to your private (unlisted) Picasa albums. Click the link below to continue";
			echo " if you wish to set up PicasaWeb tokens for site: <strong>$site</strong></p>";
			echo "<p>If this is correct, <a href='$next'>";
			echo "Login to your Google Account</a></p></div>"; 
			echo "</body>\n</html>";
		} # End Get Gdata Token


		#--------------------------------------------------------------
		# Exchange Token
		# Since: 1.0
		# A function to Exchange the Gdata Token
		# Used in: -admin
		#--------------------------------------------------------------
		static function exchangeToken($single_use_token) {

			$ch = curl_init("https://www.google.com/accounts/AuthSubSessionToken");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: AuthSub token="' . $single_use_token . '"'
			));

			$result = curl_exec($ch);  # Execute the HTTP command.

			# See if there has been a curl error
			if(curl_errno($ch)) {
				echo "<div id='message' class='updated fade pwa_notice'><p><strong>Error: ";
				echo "Could not generate session token! Exiting...</strong></p></div>";
				die ('Curl error: ' . curl_error($ch));
			}

			curl_close($ch);
			$splitStr = explode("=", $result);

			if (strlen($splitStr[1]) > 14) {
				return trim($splitStr[1]);
			} else {
				echo "<div id='message' class='updated fade pwa_notice'><p><strong>Error: ";
				echo "Could not generate session token! Exiting...</strong></p></div>";
				die('Unexpected value returned to exchangeToken(): ' . $splitStr[1]);
			}
		} # End Exchange Token


		#--------------------------------------------------------------
		# Set Gdata Token
		# Since: 1.0
		# A function to Set the Gdata Token
		# Used in: -admin
		#--------------------------------------------------------------
		static function set_gdata_token() {

			$token = $_GET['token'];
			$newToken = self::exchangeToken($token);

			update_option("pwaplusphp_gdata_token",$newToken);

			echo "<div id='message' class='updated fade pwa_notice'><p><h2>Install Step 1: Complete!</h2>";
			echo "<p>Token retrieved and saved in WordPress configuration database. Value is '$newToken'.</p>";

			$uri = $_SERVER["REQUEST_URI"];
			list($back_link,$trash) = explode('&',$uri);

			echo "<p>Continue to <a href='$back_link'>Step 2</a>...</p></div>";

		} # End Set Gdata Token


		#--------------------------------------------------------------
		# In Array
		# Since: 1.0
		# A function to determine if the specific album/photo is in the array
		# Used in: -single
		#--------------------------------------------------------------
		static function in_arrayr( $needle, $haystack ) {
			foreach( $haystack as $v ){
				if( $needle == $v )
					return true;
				elseif( is_array( $v ) )
					if( in_arrayr( $needle, $v ) )
						return true;
			}
			return false;
		} # End In Array



	} # End Class
} # End IF
?>
