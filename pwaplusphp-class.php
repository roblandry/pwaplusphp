<?php
/*
	Support class PWA+PHP plugin

	Copyright (c) 2011, 2012 by Scott McCandless
	Rewritten by Rob Landry

	GNU General Public License version 3

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
# Class pwaplusphp
# Since: 1.0
# Define the Class
# Used in: -admin, -album, -photo
#--------------------------------------------------------------
if (!class_exists('pwaplusphp')) {
	class pwaplusphp {

		#--------------------------------------------------------------
		# Class variables
		# Since: 1.0
		# Define the Class Variables
		# Used in: -self
		#--------------------------------------------------------------
		var $main_file = null;
		var $debug = null;
		var $site_id = '';
		var $blog_id = '';
		var $version = '1.0';

		#--------------------------------------------------------------
		# Constructor
		# Since: 1.0
		# A function to Construct Plugin
		# Used in: -self
		#--------------------------------------------------------------
		function __construct() {
			global $wp_version, $blog_id, $pwa_pro;

			# Get main file name
			$this->main_file = str_replace('-class', '', __FILE__);

			# register for de-activation
			register_deactivation_hook($this->main_file, array(&$this, 'Deactivate'));

			# remove pro on delete file
			//if ((get_option(c_pwa_pro)) && (!class_exists('pwa_pro'))) {
			//	self::Deactivate_Pro();
			//}
			# Register actions
			add_action('init', array(&$this, 'Init'), 0);
			if (is_admin()) {
				add_action('admin_menu', array(&$this, 'Admin_menu'));
				add_action('admin_notices', array(&$this, 'Admin_notices'));
			}

		} # End Construct


		#--------------------------------------------------------------
		# Activate
		# Since: 1.0
		# A function to Handle plugin activation
		# Used in: -self
		#--------------------------------------------------------------
		function Activate() {
			global $this_version;
			$my_version = get_option(c_pwa_version);
			if (empty($my_version)) self::default_options($this->version);
			if ($this->version > $my_version) self::Upgrade($my_version);

		} # End Activate


		#--------------------------------------------------------------
		# Upgrade
		# Since: not_yet_implemented
		# A function to upgrade plugin
		# Used in: -self
		#--------------------------------------------------------------
		function Upgrade($my_version) {
			global $wpdb, $pwa_pro;
			update_option(c_pwa_version, $this->version);
			if ($my_version < '1.0') {
				delete_option('pwaplusphp_public_only');
				update_option(c_pwa_access,"public");
			}
			if (!empty($pwa_pro)) {
				$source = plugin_dir_path( __FILE__ ). 'cache';
				$destination = $pwa_pro::get_Upload_Dir() .'cache';
				if (is_dir($source)) {
					$pwa_pro::copy_directory($source,$destination);
					$pwa_pro::rrmdir($source);
				}
				if (!is_dir($destination)) {
					mkdir ($destination);
				}
			}
		} # End Upgrade



		#--------------------------------------------------------------
		# Deactivate
		# Since: 1.0
		# A function to Handle plugin deactivation
		# Used in: -self
		#--------------------------------------------------------------
		function Deactivate() {
			# Cleanup data
			if (get_option(c_pwa_option_clean) == 'TRUE') {
				global $wpdb;
				# Delete options
				$rows = $wpdb->get_results("SELECT option_name FROM " . $wpdb->options . " WHERE option_name LIKE 'pwaplusphp_%'");
				foreach ($rows as $row)
					delete_option($row->option_name);
			}

		} # End Deactivate


		#--------------------------------------------------------------
		# Deactivate Pro
		# Since: 1.0
		# A function to Handle plugin deactivation
		# Used in: -self
		#--------------------------------------------------------------
		function Deactivate_Pro() {
			delete_option('pwaplusphp_flickr_username');
			delete_option('pwaplusphp_cache_thumbs');
			//delete_option('pwaplusphp_main_photo_page');
			delete_option('pwaplusphp_show_comments');
			//delete_option('pwaplusphp_jq_pagination');
			delete_option('pwaplusphp_images_on_front');
			delete_option('pwaplusphp_show_button');
			delete_option('pwaplusphp_show_exif');
			delete_option('pwaplusphp_lazyload');
			delete_option('pwaplusphp_lazyload_img');
			delete_option('pwaplusphp_add_widget');
			delete_option('pwaplusphp_option_clean');
		} # End Pro Deactivate

		#--------------------------------------------------------------
		# Initialization
		# Since: 1.0
		# A function to Initialize the plugin
		# Used in: -self
		#--------------------------------------------------------------
		function Init() {
			# I18n
			load_plugin_textdomain(c_pwa_text_domain, false, PWA_DIR . '/lang/');
		} # end init


		#--------------------------------------------------------------
		# Admin_notices
		# Since: 1.0
		# A function to Display admin messages
		# Used in: -self
		#--------------------------------------------------------------
		function Admin_notices() {


			# Check for Proper Env
			//self::Check_config();

			# Check user capability
			if (current_user_can(c_pwa_min_cap)) {
				# Get current user
				global $user_ID;
				get_currentuserinfo();

				# Check actions
				if (isset($_REQUEST['pwa_action'])) {
					# Configuration
					if ($_REQUEST['pwa_action'] == 'config')
						self::Action_config();

					# Authorization
					else if ($_REQUEST['pwa_action'] == 'authorize')
						self::Action_authorize();

					# Mail debug info
					else if ($_REQUEST['pwa_action'] == 'mail')
						self::Action_mail();

					# Reset Options
					else if ($_REQUEST['pwa_action'] == 'default')
						self::default_options();

					# Get Gdata Token
					else if ($_REQUEST['pwa_action'] == 'gdata')
						pwa_int::get_gdata_token();

					# Set Gdata Token
					else if ($_REQUEST['pwa_action'] == 'return')
						pwa_int::set_gdata_token();

				}
				# Check for Gdata Token
				if ((get_option(c_pwa_gdata_token) == '')){
					if (!(isset($_REQUEST['pwa_action'])))
					pwa_int::get_gdata_token(); }
			}
		} # end admin notices


		#--------------------------------------------------------------
		# Action_config
		# Since: 1.0
		# A function to Save Admin Settings
		# Used in: -self
		#--------------------------------------------------------------
		function Action_config() {

			global $pwa_pro;
			# Security check
			//check_admin_referer(c_pwa_nonce_form);
			if ( !empty($_POST) || wp_verify_nonce($_POST['pwaplusphp_nonce'],'pwaplusphp_nonce') ) {

				# Default values
				$consts = get_defined_constants(true);

				# Update admin options

				if (!empty($pwa_pro)) $pwa_pro::Update_Pro_Settings($_POST);
				$access = $_POST['pwa_access']; $pwa_access = '';
				if (is_array($access)) { 
					foreach ($access as $key=>$val) {
						if ($val=="public") $pwa_access .= $val;
						if ($val=="protected") $pwa_access .= $val;
						if ($val=="private") $pwa_access .= $val;
					}
				} else { $pwa_access = $_POST['pwa_access']; }
				update_option(c_pwa_username, $_POST['c_pwa_username']);
				update_option(c_pwa_image_size, $_POST['c_pwa_image_size']);
				update_option(c_pwa_photo_thumbsize, $_POST['c_pwa_photo_thumbsize']);	
				update_option(c_pwa_album_thumbsize, $_POST['c_pwa_album_thumbsize']);
				update_option(c_pwa_require_filter, $_POST['c_pwa_require_filter']);
				update_option(c_pwa_images_per_page, $_POST['c_pwa_images_per_page']);
				update_option(c_pwa_albums_per_page, $_POST['c_pwa_albums_per_page']);
				update_option(c_pwa_access, $pwa_access);
				update_option(c_pwa_show_album_details, $_POST['c_pwa_show_album_details']);
				update_option(c_pwa_show_dropbox, $_POST['c_pwa_show_dropbox']);
				update_option(c_pwa_truncate_name, $_POST['c_pwa_truncate_name']);
				update_option(c_pwa_language, $_POST['c_pwa_language']);
				update_option(c_pwa_permit_download, $_POST['c_pwa_permit_download']);
				#update_option(c_pwa_show_footer, $_POST['c_pwa_show_footer']);
				update_option(c_pwa_show_caption, $_POST['c_pwa_show_caption']);
				update_option(c_pwa_description_length, $_POST['c_pwa_description_length']);
				update_option(c_pwa_caption_length, $_POST['c_pwa_caption_length']);
				update_option(c_pwa_crop_thumbs, $_POST['c_pwa_crop_thumbs']);
				update_option(c_pwa_date_format, $_POST['c_pwa_date_format']);
				update_option(c_pwa_hide_video, $_POST['c_pwa_hide_video']);
				update_option(c_pwa_report_errors, $_POST['c_pwa_report_errors']);
				update_option(c_pwa_option_clean, $_POST['c_pwa_option_clean']);
				update_option(c_pwa_header_in_post, $_POST['c_pwa_header_in_post']);
				update_option(c_pwa_main_photo_page, $_POST['page_id']);

				# Show result
				echo '<div id="message" class="updated fade pwa_notice"><p>' . __('Configuration is complete and PWA+PHP is ready for use. Create a page with contents "[pwaplusphp]" to see your albums.', c_pwa_text_domain) . '</p></div>';
			} else {
				echo '<div id="error" class="error fade pwa_error"><p>Sorry, your nonce did not verify or you did not save anything.</p></div>';
			}

		} # end action config


		#--------------------------------------------------------------
		# Admin_menu
		# Since: 1.0
		# A function to Register options page
		# Used in: -self
		#--------------------------------------------------------------
		function Admin_menu() {
			# Get current user
			global $user_ID;
			global $pwa_pro;
			get_currentuserinfo();

			$title = 'PWA+PHP';
			if (!empty($pwa_pro)) $title = 'PWA+PHP PRO';
			$page = add_submenu_page('upload.php',				
				__($title, c_pwa_text_domain) . ' ' . __('Administration', c_pwa_text_domain),
				__($title, c_pwa_text_domain),
				current_user_can(c_pwa_min_cap),
				'pwaplusphp',
				array(&$this, 'Administration'));
			add_action( 'admin_print_styles-' . $page, array(&$this, 'Admin_Styles' ));
			add_action( 'admin_print_scripts-' . $page, array(&$this, 'Admin_Scripts' ));
			

		} # end Admin_menu


		#--------------------------------------------------------------
		# Administration
		# Since: 1.0
		# A function to Handle option page
		# Used in: -self
		#--------------------------------------------------------------
		function Administration() {
			# Security check
			if (!current_user_can(c_pwa_min_cap))
				die('Unauthorized');

			require_once('pwaplusphp-admin.php');
			pwa_render_admin($this);

			global $updates_pwa;
			if (isset($updates_pwa))
				$updates_pwa->checkForUpdates();

		} # end Administration


		#--------------------------------------------------------------
		# Admin Styles
		# Since: 1.0
		# A function to style the admin menu
		# Used in: -
		#--------------------------------------------------------------
		function Admin_Styles() {
			echo "<style type='text/css'>";
			echo "div#pwa_banner{float:right;}div#pwa_banner img{width:200px;}";
			echo "</style>";
			wp_enqueue_style('wp-pointer');
		}


		#--------------------------------------------------------------
		# Admin Scripts
		# Since: 1.0
		# A function to js the admin menu
		# Used in: -
		#--------------------------------------------------------------
		function Admin_Scripts() {
			echo "<script type='text/javascript'>";
				echo "window.name = 'PWAplusPHP_Admin'";
			echo "</script>";
			wp_enqueue_script('admin-widgets');
			wp_enqueue_script('wp-pointer');

		}

		#--------------------------------------------------------------
		# Check environment
		# Since: 1.0
		# A function to Check if environment will support this plugin
		# Used in: -
		#--------------------------------------------------------------
		static function Check_prerequisites() {
			# Check WordPress version
			global $wp_version;
			if (version_compare($wp_version, '3.0') < 0)
				die('PWA+PHP requires at least WordPress 3.0');

			# Check basic prerequisities
			self::Check_function('add_action');
			self::Check_function('add_filter');
			self::Check_function('wp_register_style');
			self::Check_function('wp_enqueue_style');
			self::Check_function('file_get_contents');
			self::Check_function('json_decode');
			self::Check_function('md5');
		} # end Check_prerequisites


		#--------------------------------------------------------------
		# Check_function
		# Since: 1.0
		# A function to Check if wordpress function exists
		# Used in: -self
		#--------------------------------------------------------------
		static function Check_function($name) {
			if (!function_exists($name))
				die('Required WordPress function "' . $name . '" does not exist');
		} # end Check_function



		#--------------------------------------------------------------
		# Check For Updates
		# Since: 1.0
		# A function to
		# Used in: -
		#--------------------------------------------------------------
		function check_for_updates($my_version) {
			$version = file_get_contents('http://pwaplusphp.smccandl.net/wp-pro-ver.html');
			if ($version !== false) {
				$version=trim($version);
				if ($version > $my_version) {
					return("<table><tr class='plugin-update-tr'><td class='plugin-update'><div class='update-message'>New Version Available.  <a href='http://code.google.com/p/pwaplusphp/downloads/list'>Get v$version!</a></div></td></tr></table>");
				} else {
					return("Thanks for your donation!");
				}
			} else {
				# We had an error, fake a high version number so no message is printed.
				$version = "9999";
			}
		} # End Check For Updates


		#--------------------------------------------------------------
		# Error Reporting 
		# Since: 1.0
		# A function to Report Errors
		# Used in: -album, -photo
		#--------------------------------------------------------------
		function report_errors($option) {

		// Turn off all error reporting
		//error_reporting(0);

		// Report all PHP errors
		//error_reporting(-1);
			//if ($option == 0) {
			//	$level = 0;
			//} else {
			//	$level = -1;
			//}
			//error_reporting($level);
		} # End Error Reporting 


		#--------------------------------------------------------------
		# Setup Variables
		# Since: 1.0
		# A function to Setup Variables
		# Used in: -album, -photo
		#--------------------------------------------------------------
		function setup_variables($pwa_options) {
			# Default values
			$consts = get_defined_constants(true);
			global $pwa_pro;
			//$pwa_options['PRO'] 			= 'FALSE';
			if (!empty($pwa_pro)) $pwa_options = $pwa_pro::Setup_Pro_Options($pwa_options);
			$pwa_options['USE_LIGHTBOX']		= c_pwa_use_lightbox;
			$pwa_options['ACTIVE_LIGHTBOX']		= 'NONE';
			$pwa_options['STANDALONE_MODE']		= c_pwa_standalone_mode;
			$pwa_options['GDATA_TOKEN']		= get_option(c_pwa_gdata_token);
			$pwa_options['PICASAWEB_USER']		= get_option(c_pwa_username);
			$pwa_options['IMGMAX']			= get_option(c_pwa_image_size);
			$pwa_options['PHOTO_THUMBSIZE']		= get_option(c_pwa_photo_thumbsize);
			$pwa_options['ALBUM_THUMBSIZE']		= get_option(c_pwa_album_thumbsize);
			$pwa_options['REQUIRE_FILTER']		= get_option(c_pwa_require_filter);
			$pwa_options['IMAGES_PER_PAGE']		= get_option(c_pwa_images_per_page);
			$pwa_options['ALBUMS_PER_PAGE']		= get_option(c_pwa_albums_per_page);
			$pwa_options['PUBLIC_ONLY']		= get_option(c_pwa_access);
			$pwa_options['JQ_PAGINATION']		= get_option(c_pwa_jq_pagination);
			$pwa_options['MAIN_PHOTO_PAGE']		= get_option(c_pwa_main_photo_page);
			//$pwa_options['SHOW_ALBUM_DETAILS']	= get_option(c_pwa_show_album_details);
			//$pwa_options['CHECK_FOR_UPDATES']	= get_option(c_pwa_updates);
			$pwa_options['SHOW_DROP_BOX']		= get_option(c_pwa_show_dropbox);
			$pwa_options['TRUNCATE_ALBUM_NAME']	= get_option(c_pwa_truncate_name);
			$pwa_options['THIS_VERSION']		= get_option(c_pwa_version);
			$pwa_options['SITE_LANGUAGE']		= get_option(c_pwa_language); // not used in alb/pho
			$pwa_options['PERMIT_IMG_DOWNLOAD']	= get_option(c_pwa_permit_download);
			$pwa_options['SHOW_FOOTER']		= get_option(c_pwa_show_footer);
			$pwa_options['SHOW_IMG_CAPTION']	= get_option(c_pwa_show_caption);
			$pwa_options['CAPTION_LENGTH']		= get_option(c_pwa_caption_length);
			$pwa_options['DESCRIPTION_LENGTH']	= get_option(c_pwa_description_length); //not used in alb
			$pwa_options['CROP_THUMBNAILS']		= get_option(c_pwa_crop_thumbs);
			$pwa_options['DATE_FORMAT']		= get_option(c_pwa_date_format);
			$pwa_options['HIDE_VIDEO']		= get_option(c_pwa_hide_video);
			$pwa_options['SHOW_HEADER_IN_POST']	= get_option(c_pwa_header_in_post);
			$pwa_options['TRUNCATE_FROM']		= $pwa_options['CAPTION_LENGTH']; //not used in alb
			$pwa_options['TRUNCATE_TO']		= $pwa_options['CAPTION_LENGTH'] - 3; # not used in alb
			$pwa_options['OPEN']			= 0;
			$pwa_options['ALBUMS_TO_HIDE']		= (array_key_exists('ALBUMS_TO_HIDE',$pwa_options)) ? $pwa_options['ALBUMS_TO_HIDE'] : array(); # added due to is_array error
			$pwa_options['TZPP'] 			= $pwa_options['ALBUM_THUMBSIZE'] + 45;
			$pwa_options['DESCRIPTION_LENGTH_TO']	= $pwa_options['DESCRIPTION_LENGTH'] - 3;
			//$pwa_options['TW20']	= $pwa_options['ALBUM_THUMBSIZE'] + round($pwa_options['ALBUM_THUMBSIZE'] * .1);
			$pwa_options['TWM10']			= $pwa_options['ALBUM_THUMBSIZE'] - 8;
			$pwa_options['PLUGIN_URI']		= plugin_dir_path( __FILE__ );
			$pwa_options['PLUGIN_URL']		= plugin_dir_url( __FILE__ );
			return $pwa_options;
		} # End Setup Variables


		#--------------------------------------------------------------
		# Set Default options
		# Since: 1.0
		# A function to Set the default options
		# Used in: -album, -photo
		#--------------------------------------------------------------
		function default_options() {
			global $this_version;
			# Default values
			$consts = get_defined_constants(true);
			update_option(c_pwa_image_size,"640");
			update_option(c_pwa_photo_thumbsize,160);
			update_option(c_pwa_album_thumbsize,160);
			update_option(c_pwa_require_filter,"FALSE");
			update_option(c_pwa_images_per_page,0);
			update_option(c_pwa_albums_per_page,0);
			update_option(c_pwa_access,"public");
			//update_option(c_pwa_show_album_details,"TRUE");
			//update_option(c_pwa_updates,"TRUE");
			update_option(c_pwa_show_dropbox,"FALSE");
			update_option(c_pwa_truncate_name,"TRUE");
			update_option(c_pwa_version, $this->version);
			update_option(c_pwa_language,"en_us");
			update_option(c_pwa_permit_download,"FALSE");
			update_option(c_pwa_show_footer,"FALSE");
			update_option(c_pwa_show_caption,"HOVER");
			update_option(c_pwa_caption_length,"23");
			update_option(c_pwa_description_length,"120");
			update_option(c_pwa_crop_thumbs,"TRUE");
			update_option(c_pwa_date_format,"Y-m-d");
			update_option(c_pwa_hide_video,"FALSE");
			update_option(c_pwa_report_errors,"FALSE");
			update_option(c_pwa_option_clean, "FALSE");
			update_option(c_pwa_header_in_post, "FALSE");
			update_option(c_pwa_jq_pagination, none);

		} # End Set Default options


		#--------------------------------------------------------------
		# Public Only
		# Since: 1.0
		# 
		# Used in: -self
		#--------------------------------------------------------------
		function Public_Only() {
			$PUBLIC_ONLY = "FALSE";
			$access = get_option(c_pwa_access);
			$protected = strrpos($access, 'protected');
			$private = strrpos($access, 'private');
			if (($protected === false) && ($private === false)) { 
				return "TRUE";
			} else {
				return "FALSE";
			}
		} # End Public Only 


		#--------------------------------------------------------------
		# Picasa Album Data
		# Since: 1.0
		# xmlns='http://www.w3.org/2005/Atom' 
		# xmlns:gphoto='http://schemas.google.com/photos/2007' 
		# xmlns:media='http://search.yahoo.com/mrss/' 
		# xmlns:openSearch='http://a9.com/-/spec/opensearchrss/1.0/
		# Used in: -album, -random
		#--------------------------------------------------------------
		function gdata_album($pwa_options, $test=0) {

/*			if (!empty($pwa_pro)) {
				//$pwa_options = pwa_pro::cache_xml($pwa_options);
				$album = pwa_pro::gdata_album_pro($pwa_options, $test);
				return $album;
			}*/

			$GDATA_TOKEN = get_option(c_pwa_gdata_token);
			$temp_file=$pwa_options['FILE'];
			if (self::Public_Only() == "FALSE") $temp_file .= "&access_token=". $GDATA_TOKEN;
			$file = (array_key_exists('SAVEFILE',$pwa_options)) ? $pwa_options['SAVEFILE'] : $temp_file;
			$feed = simplexml_load_file($file);
			$namespaces = $feed->getNamespaces(true);
			$album = array();
			$i=0;
			$opensearch = $feed->children($namespaces['openSearch']);
			$total = $opensearch->totalResults;

			foreach ($feed->entry as $entry) {
				$access	= trim($entry->rights);
				$public = strrpos(get_option(c_pwa_access), 'public');
				$protected = strrpos(get_option(c_pwa_access), 'protected');
				$private = strrpos(get_option(c_pwa_access), 'private');

				if ((($public !== false) && ($access == 'public')) ||
					(($protected !== false) && ($access == 'protected')) ||
					(($private !== false) && ($access == 'private'))) {

					$album[$i]['total'] = $total;
					$album[$i]['id'] = trim($entry->id);
					$album[$i]['published'] = trim($entry->published);
					$album[$i]['updated'] = trim($entry->updated);
					$album[$i]['title'] = trim($entry->title);
					$album[$i]['summary'] = trim($entry->summary);
					$album[$i]['rights'] = $access;
					$album[$i]['link'] = trim($entry->link->attributes()->href);

					# Gphoto
					$gphoto = $entry->children($namespaces['gphoto']);
					$album[$i]['gphoto:name'] = trim($gphoto->name);
					$album[$i]['gphoto:location'] = trim($gphoto->location);
					$album[$i]['gphoto:access'] = trim($gphoto->access);
					$album[$i]['gphoto:timestamp'] = trim($gphoto->timestamp);
					$album[$i]['gphoto:numphotos'] = trim($gphoto->numphotos);

					# Media:Group
					$media = $entry->children($namespaces['media'])
						->group
						->children($namespaces['media']);
					$album[$i]['media:description'] = trim($media->description);
					$album[$i]['media:thumbnail'] = trim($media->thumbnail->attributes()->url);
					$album[$i]['media:title'] = trim($media->title);
					$i++;
				}
			}

			if ($test==1) { echo "File: $file<br>"; self::test_xml($album); }
			return $album;
		} # End Picasa Album Data


		#--------------------------------------------------------------
		# Picasa Photo Data
		# Since: 1.0
		# xmlns='http://www.w3.org/2005/Atom' 
		# xmlns:gphoto='http://schemas.google.com/photos/2007' 
		# xmlns:media='http://search.yahoo.com/mrss/' 
		# xmlns:openSearch='http://a9.com/-/spec/opensearchrss/1.0/
		# xmlns:exif="http://schemas.google.com/photos/exif/2007"
		# Used in: -photo, -random
		#--------------------------------------------------------------
		function gdata_photo($pwa_options, $test=0) {
/*			if (!empty($pwa_pro)) {
			//	$pwa_options = pwa_pro::cache_xml($pwa_options);
				$photos = pwa_pro::gdata_photo_pro($pwa_options, $test);
				return $photos;
			}*/
			$GDATA_TOKEN = get_option(c_pwa_gdata_token);

			$temp_file=$pwa_options['FILE'];
			if (self::Public_Only() == "FALSE") $temp_file .= "&access_token=". $GDATA_TOKEN;
			
			$file = (array_key_exists('SAVEFILE',$pwa_options)) ? $pwa_options['SAVEFILE'] : $temp_file;
			$feed = simplexml_load_file($file);
			$namespaces = $feed->getNamespaces(true);

			# OpenSearch
/*			$opensearch=$feed->children($namespaces['openSearch']);
			$total_photos = trim($opensearch->totalResults);
			if (($pwa_options['IMAGES_ON_FRONT'] != 0 ) && 
				($pwa_options['IMAGES_ON_FRONT'] < $total_photos) &&
				(is_home() || is_author() || is_archive())) {
				$total_photos =  $pwa_options['IMAGES_ON_FRONT'];
			}*/
			$i=0;

			$photos = array();
			$gphoto = $feed->children($namespaces['gphoto']);
			$albumid= trim($gphoto->id);
			$title = trim($feed->title);
			$rights = trim($feed->rights);
			$link = trim($feed->link->attributes()->href);
			$opensearch = $feed->children($namespaces['openSearch']);
			$total = $opensearch->totalResults;

			foreach ($feed->entry as $entry) {
				$photos[$i]['total'] = $total;
				$access	= trim($entry->rights);
				$public = strrpos(get_option(c_pwa_access), 'public');
				$protected = strrpos(get_option(c_pwa_access), 'protected');
				$private = strrpos(get_option(c_pwa_access), 'private');


				$photos[$i]['id'] = $albumid;
				$photos[$i]['published'] = trim($entry->published);
				$photos[$i]['updated'] = trim($entry->updated);
				$photos[$i]['title'] = $title;
				$photos[$i]['summary'] = trim($entry->summary);
				$photos[$i]['rights'] = $rights;
				$photos[$i]['link'] = $link;

				# Gphoto
				$gphoto = $entry->children($namespaces['gphoto']);
				$photos[$i]['gphoto:id'] = trim($gphoto->id);
				$photos[$i]['gphoto:name'] = trim($gphoto->name);
				$photos[$i]['gphoto:location'] = trim($gphoto->location);
				$photos[$i]['gphoto:access'] = trim($gphoto->access);
				$photos[$i]['gphoto:timestamp'] = trim($gphoto->timestamp);
				$photos[$i]['gphoto:numphotos'] = trim($gphoto->numphotos);
				$photos[$i]['gphoto:commentcount'] = trim($gphoto->commentCount);

				# Media:Group
				$media = $entry->children($namespaces['media'])
					->group
					->children($namespaces['media']);
				$photos[$i]['media:content'] = trim($media->content->attributes()->url);
				$photos[$i]['media:description'] = trim($media->description);
				$photos[$i]['media:thumbnail'] = trim($media->thumbnail->attributes()->url);
				$photos[$i]['media:title'] = trim($media->title);

				# Exif:Tags
				if (array_key_exists('exif',$namespaces)) {
					if ($entry->children($namespaces['exif'])->tags){
						$exif = $entry->children($namespaces['exif'])
							->tags
							->children($namespaces['exif']);
						$photos[$i]['exif:fstop'] = trim($exif->fstop);
						$photos[$i]['exif:make'] = trim($exif->make);
						$photos[$i]['exif:model'] = trim($exif->model);
						$photos[$i]['exif:distance'] = trim($exif->distance);
						$photos[$i]['exif:exposure'] = trim($exif->exposure);
						$photos[$i]['exif:flash'] = trim($exif->flash);
						$photos[$i]['exif:focallength'] = trim($exif->focallength);
						$photos[$i]['exif:iso'] = trim($exif->iso);
						$photos[$i]['exif:time'] = trim($exif->time);
					}
				} # End Exif:Tags 
			$i++;
			}

			# Test Output
			if ($test==1) { echo "File: $file<br>"; self::test_xml($photos); }
			return $photos;
		} # End Picasa Photo



		#--------------------------------------------------------------
		# Flickr Photo Data
		# Since: 1.0
		# xmlns:media='http://search.yahoo.com/mrss/' 
		# xmlns:dc="http://purl.org/dc/elements/1.1/" 
		# xmlns:creativeCommons="http://cyber.law.harvard.edu/rss/creativeCommonsRssModule.html"
		# Used in: -photo
		#--------------------------------------------------------------
		function flickr_photo($pwa_options, $test=0) {

			#$file = ($pwa_options['SAVEFILE']) ? $pwa_options['SAVEFILE'] : $pwa_options['FILE'];
			$flickruser = $pwa_options['flickr'];
			$file="http://api.flickr.com/services/feeds/photos_public.gne?id=". $flickruser ."&lang=en-us&format=rss_200";
			$feed = simplexml_load_file($file);
			$namespaces = $feed->getNamespaces(true);
			$photos = array();

			$photos[0]['title'] = trim($feed->channel->title);
			$photos[0]['link'] = trim($feed->channel->link->attributes()->href);
			$photos[0]['description'] = trim($feed->channel->description);
			$photos[0]['published'] = trim($feed->channel->pubDate);
			$photos[0]['img:url'] = trim($feed->channel->image->url);
			$photos[0]['img:title'] = trim($feed->channel->image->title);
			$photos[0]['img:link'] = trim($feed->channel->image->link);
			$i=0;

			foreach ($feed->channel->item as $feeditem) {
				$photos[$i]['title'] = $photos[0]['title'];
				$photos[$i]['link'] = $photos[0]['link'];
				$photos[$i]['description'] = $photos[0]['description'];
				$photos[$i]['published'] = $photos[0]['published'];
				$photos[$i]['img:url'] = $photos[0]['img:url'];
				$photos[$i]['img:title'] = $photos[0]['img:title'];
				$photos[$i]['img:link'] = $photos[0]['img:link'];

  
				# Flickr
				$photos[$i]['item:title'] = trim($feeditem->title);
				$photos[$i]['item:link'] = trim($feeditem->link);
				$photos[$i]['item:description'] = trim($feeditem->description);
				$photos[$i]['item:pubDate'] = trim($feeditem->pubDate);

				# dc
				$dc = $feeditem->children($namespaces['dc']);
				//$photos[$i]['item:date.Taken'] = trim($dc->date.Taken);
				#<author flickr:profile="http://www.flickr.com/people/46223468@N04/">nobody@flickr.com (bigrob8181)</author>

				# Media:Group
				$media = $feeditem->children($namespaces['media']);
				$photos[$i]['media:content'] = trim($media->content->attributes()->url);
				$photos[$i]['media:height'] = trim($media->content->attributes()->height);
				$photos[$i]['media:width'] = trim($media->content->attributes()->width);
				$photos[$i]['media:title'] = trim($media->title);
				$photos[$i]['media:description'] = trim($media->description, '</p>');
				#75x75
				$photos[$i]['media:thumbnail'] = trim($media->thumbnail->attributes()->url);
				$photos[$i]['media:credit'] = trim($media->credit);
				$photos[$i]['media:category'] = trim($media->category);

				$i++;
			}

			# Test Output
			if ($test==1) self::test_xml($photos);
			return $photos;
		} # End Flickr Photo Data


		#--------------------------------------------------------------
		# Test XML Array Output
		# Since: 1.0
		# A function to Test XML Array Output
		# Used in: -self
		#--------------------------------------------------------------
		function test_xml($arr){
			for ($i=0; $i < count($arr); $i++) {
				echo "<pre>";
				foreach ($arr[$i] as $key => $value) {
					echo $key .": ". $value ."<br />\n";
				}
				echo "</pre>";
			}
		} # End Test XML Array Output


		#--------------------------------------------------------------
		# Activate Pro
		# Since: 1.0
		# A function to Activate Pro
		# Used in: -
		#--------------------------------------------------------------
		function Activate_Pro($pid,$code) {
				add_option('pwaplusphp_pro_pid', $pid);
				add_option('pwaplusphp_pro_code', $code);
				$activated='TRUE';
				return $activated;
		} # End Activate Pro


		#--------------------------------------------------------------
		# Get the Run Time of each function
		# Since: 1.0
		# A function to 
		# Used in: -
		#--------------------------------------------------------------
		function Function_Run_Time(){
			//$start=microtime();
			//call function
			//$end=microtime();
			//$stime=$end-$start;
			//echo 'cacheThumbnails: '. $stime .'<br>';
		} # End Function_Run_Time


		#--------------------------------------------------------------
		# Draw Albums
		# Since: 1.0
		# A function to Draw the Albums
		# Used in: -self
		#--------------------------------------------------------------
		function Draw_Albums( $FILTER,$COVER = "FALSE" ) {
			global $pwa_pro;
			$consts = get_defined_constants(true);

			#------------------------------------------------------
			# Setup Variables 
			#------------------------------------------------------
			$pwa_options = array();
			$pwa_options = self::setup_variables($pwa_options);
			$pwa_options['container_nav_name'] = "slideshow";
			$pwa_options['TYPE'] = 'album';
			if (!empty($pwa_pro)) $pwa_options = $pwa_pro::Album_Cover($pwa_options,$COVER);

			#------------------------------------------------------
			# Check Permalink Structure 
			#------------------------------------------------------
			$pwa_options = pwa_int::check_permalink_structure($pwa_options);

			#------------------------------------------------------
			# Check if wptouch is enabled and adjust format 
			#------------------------------------------------------
			$pwa_options = pwa_int::check_for_wptouch($pwa_options);

			#------------------------------------------------------
			# Load Language File 
			#------------------------------------------------------
			require(PWA_DIR."/lang/". get_option(c_pwa_language) .".php");

			#------------------------------------------------------
			# Check for required variables from config file
			#------------------------------------------------------
			if (!isset($pwa_options['GDATA_TOKEN'], $pwa_options['PICASAWEB_USER'], 
				$pwa_options['ALBUM_THUMBSIZE'], $pwa_options['USE_LIGHTBOX'], 
				$pwa_options['REQUIRE_FILTER'], $pwa_options['STANDALONE_MODE'])) {
				echo "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
				exit;
			}

			#------------------------------------------------------
			# VARIABLES
			#------------------------------------------------------
			if ($pwa_options['REQUIRE_FILTER'] != "FALSE") {
				if ((!isset($FILTER)) || ($FILTER == "")) {
					die($LANG_PERM_FILTER);
				}
			}

			#------------------------------------------------------
			# Request URL for Album list
			#------------------------------------------------------
			$pwa_options['FILE'] = "http://picasaweb.google.com/data/feed/api/user/" . 
				$pwa_options['PICASAWEB_USER'] . "?kind=album&thumbsize=" . 
				$pwa_options['ALBUM_THUMBSIZE'] . "c";

			#------------------------------------------------------
			# Pagination for Album list
			#------------------------------------------------------
			$pwa_options = self::Paginate_File($pwa_options);

			#------------------------------------------------------
			# Cache and use saved xml
			#------------------------------------------------------
			if (!empty($pwa_pro)) $pwa_options = $pwa_pro::cache_xml($pwa_options);

			#------------------------------------------------------
			# Create the output
			#------------------------------------------------------
			$out = "<div id='pwaplusphp'>";
			$out .= "<div id='$pwa_options[container_nav_name]'>\n";
			$out .= "<div style='width: 100%'>\n";

			$albums = self::gdata_album($pwa_options,0);
			$pwa_options['TOTAL_ALBUMS'] = $albums[0]['total'];
			$pwa_options['SELECTED_ALBUMS'] = count($albums);
			$total_albums = 0;
			$total_images = 0;
			$jq_count=0;

			for ($i=0; $i < $pwa_options['SELECTED_ALBUMS']; $i++) {

				$gphoto_name = $albums[$i]['gphoto:name'];
				$media_thumbnail = $albums[$i]['media:thumbnail'];
				$is_box = strrpos($albums[$i]['media:title'],"Drop Box");
				$is_filter = false;
				$is_hide_tag = strrpos($albums[$i]['media:title'],"_hide");

				$filter_arr = explode(" ",strtolower($FILTER));
				if (count($filter_arr) > 1 ) {
					for ($x=0; $x<count($filter_arr); $x++) {
						if (strrpos(strtolower($albums[$i]['media:title']),$filter_arr[$x])) {
							$is_filter = true;
							break;
						}
					}
				} else {
					$is_filter = strrpos(strtolower($albums[$i]['media:title']),strtolower($FILTER));
				}

				if (($FILTER != "") && ($FILTER != "RANDOM")) {
					if ($is_filter !== false) {
						$hide = false;
					} else if (($is_box !== false) && 
						($pwa_options['SHOW_DROP_BOX'] == "TRUE")) {
						$hide = false;
					} else {
						$hide = true;
					}
					if ($FILTER == $albums[$i]['gphoto:name']) { $hide = false; }
				} else {
					if (($is_box !== false) && ($pwa_options['SHOW_DROP_BOX'] == "FALSE")) {
						$hide = true; 
					} else if (in_array($albums[$i]['media:title'],$pwa_options['ALBUMS_TO_HIDE'])) {
						$hide = true;
					} else if ($is_hide_tag !== false) {
						$hide = true;
					} else {
						$hide = false;
					}
				}

				if ($hide == false) {
					$jq_count++;
					if ((($FILTER == "RANDOM") && (mt_rand(0,$pwa_options['TOTAL_ALBUMS']-1) == $i)) || 
						($FILTER != "RANDOM")) {

						$out .= "<div class='pwaplusphp_albumcont' style='width: ";
						$out .=	$pwa_options['ALBUM_THUMBSIZE'] . "px; height: " . $pwa_options['TZPP'];
						$out .= "px;'>";

						if ((strrpos($albums[$i]['media:title'],'_')) !== false) { 
							list($disp_name,$tags) = explode('_',$albums[$i]['media:title']);

						} else {$disp_name = $albums[$i]['media:title'];}

						#------------------------------------------------------
						# Added via issue 7, known problem: long names can break div layout
						#------------------------------------------------------
						if ((strlen($disp_name) > get_option(c_pwa_caption_length)) && 
							($pwa_options['TRUNCATE_ALBUM_NAME'] == "TRUE")) {
						$disp_name = substr($disp_name,0,(get_option(c_pwa_caption_length)-3)) . "...";
						}

						# Make sure we fade only at the right times
						$total_images = $total_images + $albums[$i]['gphoto:numphotos'];
						if ($COVER == "TRUE") { $img_class = "pwaplusphp_img_nf"; }
						else if ($albums[$i]['media:description'] != "") { $img_class="pwaplusphp_img"; }
						else { $img_class = "pwaplusphp_img_nf"; }

						$out .= "<div class='pwaplusphp_imgcont' style=\"width: ";
						$out .= $pwa_options['ALBUM_THUMBSIZE'] . "px;\"'>\n";

						$out .= "<div class='pwaplusphp_albdesc' style='height: " . $pwa_options['TZPP'];
						$out .= "px; width: " . $pwa_options['ALBUM_THUMBSIZE'] . "px;'>";
						$out .= substr($albums[$i]['media:description'],0,(get_option(c_pwa_description_length)-3)) . "</div><!--end pwaplusphp_albdesc-->";

						$uri = $_SERVER["REQUEST_URI"];
						if (strrpos($uri,'?')) {
							list($back_link,$trash) = explode('?',$uri);
						} else {$back_link=$uri;}

						if (($FILTER == "RANDOM") || ($COVER == "TRUE")) {
							$href_str = get_bloginfo('url') . "/?page_id=";
							$href_str .= $pwa_options['MAIN_PHOTO_PAGE'] ."&";
						} else {
							if (strrpos($uri,'?')) {
								list($href_str,$trash) = explode('?',$_SERVER['REQUEST_URI']);
							} else {$href_str=$_SERVER['REQUEST_URI'];}
							if ($pwa_options['PERMALINKS_ON']) {
								$href_str .= $pwa_options['urlchar'];
							} else {
								$href_str = $_SERVER['REQUEST_URI'] . $pwa_options['urlchar'];
							}
						}

						$out .= "<a style='width: " . $pwa_options['TWM10'] ."px' ";
						$out .= "class='pwaplusphp_imglink' href='". $href_str ."album=$gphoto_name'>";
						$out .= "<img class='$img_class' alt='$gphoto_name' title='$gphoto_name' ";
						$out .= "src='$media_thumbnail' width='". $pwa_options['ALBUM_THUMBSIZE'] ."' ";
						$out .= "height='". $pwa_options['ALBUM_THUMBSIZE'] ."' /></a>";
			
						$trim_epoch = substr($albums[$i]['gphoto:timestamp'],0,10);
						$published = date(get_option(c_pwa_date_format), $trim_epoch);

						$out .= "</div><!--end pwaplusphp_imgcont-->"; # end pwaplusphp_imgcont

						$out .= "<div class='pwaplusphp_galdata'>";

						if (($FILTER == "RANDOM") || ($COVER == "TRUE")) {
							if ($COVER != "TRUE") {
							$href_str = $back_link . "?page_id=";
							$href_str .= $pwa_options['MAIN_PHOTO_PAGE'] ."&";
							}
						} else {
							if (strrpos($_SERVER['REQUEST_URI'],'?')) {
								list($href_str,$trash) = explode('?',$_SERVER['REQUEST_URI']);
							} else {$href_str=$_SERVER['REQUEST_URI'];}
							$extra = "<br /><span class='pwaplusphp_albstat'>" . $published;
							$extra .= ", " . $albums[$i]['gphoto:numphotos'] . " ";
							$extra .= $LANG_IMAGES . "</span>";
							if ($pwa_options['PERMALINKS_ON']) { 
								$href_str .= $pwa_options['urlchar']; 
							} else { 
								$href_str = $_SERVER['REQUEST_URI'] . $pwa_options['urlchar'];
							}
						}
						if ($COVER != "TRUE") {
							$out .= "<a class='album_link' href='" . $href_str;
							$out .= "album=$gphoto_name'>$disp_name</a>$extra\n";
						}

						$out .= "</div><!--end pwaplusphp_galdata-->"; # end pwaplusphp_galdata
						$out .= "\n</div><!--end pwaplusphp_albumcont-->\n"; # end pwaplusphp_albumcont

					} # end if filter is random and at random, or if filter is not random (2 divs still open)

					if ($pwa_options['ALBUMS_PER_PAGE'] > 0) {
						if ((($jq_count % $pwa_options['ALBUMS_PER_PAGE']) == 0) && ($i != 0)) {
							$out .= "</div><!--For Pagination-->\n";
							$out .= "<div style='width: 100%'>\n";
						}
					}

				} # end if hide

			} # End for loop

			$out .= "</div><!--end pagination div--></div><!--end slideshow-->\n";
			//$pwa_options['TOTAL_ALBUMS'] = $total_albums;

			# start header
			if ( ($FILTER != "RANDOM") && ($COVER != "TRUE")) {
				//if (!empty($pwa_pro)) $header = $pwa_pro::Show_Badge(get_option(c_pwa_show_badge));

				$header = "<div id='pwaheader'>";
				if (class_exists('WPtouchPlugin') && $wptouch_plugin->applemobile == "1") {
					$header .= "<span class='total_images_wpt'>$total_images $LANG_PHOTOS_IN ";
					$header .= $pwa_options['TOTAL_ALBUMS'] ." $LANG_ALBUMS</span>\n";
				} else { 
					$header .= "<span class='lang_gallery'>$FILTER $LANG_GALLERY</span>";
					$header .= "<span class='total_images'>$total_images $LANG_PHOTOS_IN ";
					$header .= $pwa_options['TOTAL_ALBUMS'] ." $LANG_ALBUMS</span>\n";
				}

				$header .= self::Paginate($pwa_options);

				$header .= "</div><!--end header-->";
				$out = $header . $out;
		
				if ($pwa_options['SHOW_FOOTER'] == "TRUE") {
					$out .= "<div id='pwafooter'>$LANG_GENERATED <a href='http://code.google.com/p/";
					$out .= "pwaplusphp/'>PWA+PHP</a> v" . $pwa_options['THIS_VERSION'] . ".</div>";
				}
			}# End header

			$out .= "<div style='clear: both'></div>"; # Ensure PWA+PHP doesn't break theme layout
			$out .= "</div><!--end pwaplusphp-->";
			return $out;

		} # End Draw Albums


		#--------------------------------------------------------------
		# Draw Photos
		# Since: 1.0
		# A function to Draw the Photos
		# Used in: -self
		#--------------------------------------------------------------
		function Draw_Photos( $ALBUM,$IN_POST = null,$TAG,$overrides_array ) {

			#------------------------------------------------------
			# Setup Variables 
			#------------------------------------------------------
			global $pwa_pro;
			$pwa_options = array();
			$pwa_options = self::setup_variables($pwa_options);
			$pwa_options['ALBUM'] = $ALBUM;
			$pwa_options['TYPE'] = 'photo';

			if (array_key_exists('images_per_page',$overrides_array)) { 
				$pwa_options['IMAGES_PER_PAGE'] = $overrides_array["images_per_page"]; }
			if (array_key_exists('image_size',$overrides_array)) {
				$pwa_options['IMGMAX'] = $overrides_array["image_size"]; }
			if (array_key_exists('thumbnail_size',$overrides_array)) {
				$pwa_options['PHOTO_THUMBSIZE'] = $overrides_array["thumbnail_size"]; }
			if (array_key_exists('picasaweb_user',$overrides_array)) {
				$pwa_options['PICASAWEB_USER'] = $overrides_array["picasaweb_user"]; }
			if (array_key_exists('page_header',$overrides_array)) {
				$pwa_options['PAGE_HEADER'] = $overrides_array["page_header"]; }
			else { $pwa_options['PAGE_HEADER'] = 'on';}
			if (array_key_exists('flickr',$overrides_array)) {
				$pwa_options['flickr'] = $overrides_array["flickr"]; }

			#------------------------------------------------------
			# Check Permalink Structure 
			#------------------------------------------------------
			$pwa_options = pwa_int::check_permalink_structure($pwa_options);

			#------------------------------------------------------
			# Check if wptouch is enabled and adjust format 
			#------------------------------------------------------
			$pwa_options = pwa_int::check_for_wptouch($pwa_options);

			#------------------------------------------------------
			# Load Language File 
			#------------------------------------------------------
			require(PWA_DIR."/lang/". get_option(c_pwa_language) .".php");

			#------------------------------------------------------
			# VARIABLES 
			#------------------------------------------------------
			global $TZM30, $TZM10;
			$PHOTO_THUMBSIZE = $pwa_options['PHOTO_THUMBSIZE'];
			$TZ10 = $PHOTO_THUMBSIZE + round($PHOTO_THUMBSIZE * .06);
			$TZ20 = $PHOTO_THUMBSIZE + round($PHOTO_THUMBSIZE * .15);
			$TZ30 = $PHOTO_THUMBSIZE + round($PHOTO_THUMBSIZE * .25);
			$TZM10 = $PHOTO_THUMBSIZE - round($PHOTO_THUMBSIZE * .06);
			$TZM20 = $PHOTO_THUMBSIZE - round($PHOTO_THUMBSIZE * .09);
			$TZM30 = $PHOTO_THUMBSIZE - round($PHOTO_THUMBSIZE * .22);
			$TZM2 = $PHOTO_THUMBSIZE - 2;
			$TZP10 = $PHOTO_THUMBSIZE + 10;
			$out2='';
			$image_count=0;
			$picasa_title="NULL";

			$uri = $_SERVER["REQUEST_URI"];
			$useragent = $_SERVER['HTTP_USER_AGENT']; # Check useragent to suppress hover for IE6
			if(strchr($useragent,"MSIE 6.0")) { $USING_IE_6 = "TRUE"; }
			$gphotoid="1234678";

			if (!empty($pwa_pro) && ($pwa_options['SHOW_EXIF'] == "TRUE")) { 
				$imgClass = 'pwaplusphp_img' ; 
			} else { $imgClass = 'pwaplusphp_img_nf'; }

			#------------------------------------------------------
			# Ensure cache directory exists 
			#------------------------------------------------------
			if (!empty($pwa_pro)) $pwa_options = $pwa_pro::check_cache_dir($pwa_options);

			#------------------------------------------------------
			# Figure out which lighbox we're using
			#------------------------------------------------------
			$pwa_options['ACTIVE_LIGHTBOX'] = self::getActiveLightbox();

			#------------------------------------------------------
			# Grab album data from URL
			#------------------------------------------------------
			# Reformat the album title for display
			if (strrpos($pwa_options['ALBUM'],'_')) {
				list($pwa_options['ALBUM_TITLE'],$tags) = explode('_',$pwa_options['ALBUM']);
			} else {$pwa_options['ALBUM_TITLE']=$pwa_options['ALBUM'];}
			#------------------------------------------------------
			# Check for required variables from config file
			#------------------------------------------------------
			if (!isset($pwa_options['GDATA_TOKEN'], $pwa_options['PICASAWEB_USER'],
				$pwa_options['PHOTO_THUMBSIZE'], $pwa_options['USE_LIGHTBOX'],
				$pwa_options['REQUIRE_FILTER'], $pwa_options['STANDALONE_MODE'],
				$pwa_options['IMGMAX'],  $pwa_options['IMAGES_PER_PAGE'])) {
				echo "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
				exit;
			}
			$meta_tag = "";

			#------------------------------------------------------
			# VARIABLES FOR PAGINATION
			#------------------------------------------------------
			if ($IN_POST == "TRUE") {
				$pwa_options['IMAGES_PER_PAGE'] = 0;
				$container_nav_name = "slideshow-sidebar";
			} else if ($IN_POST == "SLIDESHOW") {
				$IMGMAX = "d";	
				$SHOW_IMG_CAPTION = "SLIDESHOW";
			} else {
				$container_nav_name = 'slideshow';
			}

			if ($pwa_options['CROP_THUMBNAILS'] == "TRUE") {
				$CROP_CHAR = "c";
				$crop_styles = "";
			} else {
				$CROP_CHAR = "u";
				$crop_styles = "height: " . $TZ30 . "px;";
			}

			$pwa_options['FILE']= "http://picasaweb.google.com/data/feed/api/user/" . $pwa_options['PICASAWEB_USER'];

			if ($pwa_options['ALBUM'] != "NULL") { $pwa_options['FILE'] .= "/album/" . $pwa_options['ALBUM']; }

			$pwa_options['FILE'].= "?kind=photo&thumbsize=" . $pwa_options['PHOTO_THUMBSIZE'] . 
			$CROP_CHAR . "&imgmax=" . $pwa_options['IMGMAX'] ."&full-exif=true";	//added for exif

			if ($TAG != "NULL") { 
				$tag_s = str_replace(' ', ',', $TAG, $tag_s_count);
				$tag_s .= ',' .str_replace(' ', '+', $TAG);
				$pwa_options['FILE'] .= "&tag=$tag_s"; 
			}
//echo $pwa_options['FILE'];
			#------------------------------------------------------
			# Pagination for Album list
			#------------------------------------------------------
			$pwa_options = self::Paginate_File($pwa_options);

			# Rob - Check if page is home, author, archive and write file 
			if (!empty($pwa_pro)) $pwa_options = $pwa_pro::Img_On_Front_File($pwa_options); 

			#------------------------------------------------------
			# Cache and use saved xml (PRO)
			#------------------------------------------------------
			if (!isset($pwa_options['flickr'])) {
				if (!empty($pwa_pro)) $pwa_options = $pwa_pro::cache_xml($pwa_options);
			}

			#------------------------------------------------------
			# Output Photos
			#------------------------------------------------------
			$out = "<div id='pwaplusphp'>";

			if (isset($pwa_options['flickr'])) {
				$photos = self::flickr_photo($pwa_options,0);
			} else {
				$photos = self::gdata_photo($pwa_options,0);
			}

			$pwa_options['TOTAL_PHOTOS'] = $photos[0]['total'];
			$pwa_options['SELECTED_PHOTOS'] = count($photos);

			if (!empty($pwa_pro) && ($pwa_options['IMAGES_ON_FRONT'] != 0 ) && 
				($pwa_options['IMAGES_ON_FRONT'] < $pwa_options['SELECTED_PHOTOS']) &&
				(is_home() || is_author() || is_archive())) {
				$pwa_options['SELECTED_PHOTOS'] =  $pwa_options['IMAGES_ON_FRONT'];
			}
			$jq_count=0;

			for ($i=0; $i < $pwa_options['SELECTED_PHOTOS']; $i++) {
				$thumb = $photos[$i]['media:thumbnail'];
				if (isset($pwa_options['flickr'])) { $text = $photos[$i]['media:description']; }
				else { $text = $photos[$i]['summary']; }
				$href = $photos[$i]['media:content'];
				$filename = basename($href);
				$IMGMAX=$pwa_options['IMGMAX'];
				$orig_href = str_replace("s$IMGMAX","d",$href);

				$add_s = "";

				# Cache thumbnails (PRO)
				if ((!empty($pwa_pro)) && ($pwa_options['CACHE_THUMBNAILS'] =='TRUE')) {
					$nthumb = $pwa_pro::cacheThumbnails($pwa_options,$thumb);
					if ($nthumb != '') { $thumb = $nthumb; }
				}

				# Grab the album title once
				if ($i == 0) {
					if (strrpos($photos[$i]['title'],'_')) {
						list($AT,$tags) = explode('_',$photos[$i]['title']);
					} else {$AT=$photos[$i]['title'];}
					$AT = str_replace("\"", "", $AT);
					$AT = str_replace("'", "",$AT);

					// Rob - Added 2012-03-13 via Issue 108 Enhancement
					if (($IN_POST == "TRUE") && !(is_home() || is_author() || is_archive()) && ($pwa_options['SHOW_HEADER_IN_POST'] == 'TRUE')) {
						$slide_link ="<span id='slideshow_button' class='back_to_list' ";
						$slide_link .="style='cursor:pointer;'><img src='";
						$slide_link .= $pwa_options['PLUGIN_URL'] . "images/control_play.png' />";
						$slide_link .="Slideshow</span>";
						//if (!empty($pwa_pro)) 
						//	$out .= pwa_pro::Show_Badge(get_option(c_pwa_show_badge));
						$out .= "$slide_link\n";
					} // End in post

					if (($IN_POST != "TRUE") && ($IN_POST != "SLIDESHOW")) {

						// Added 2011-04-01 via Issue 81
						if ($pwa_options['PAGE_HEADER'] != "off") {
							if (($TAG == "") || ($TAG == "NULL")) {
								$out .= "<div id='title'><h2>$AT</h2>";
							} else {
								$out .= "<div id='title'><h2>Photos tagged '$TAG'</h2>";
							}
							// Rob - Added 2012-03-13 via Issue 108 Enhancement

							$slide_link ="<span id='slideshow_button' class='back_to_list' ";
							$slide_link .="style='cursor:pointer;'><img src='";
							$slide_link .= $pwa_options['PLUGIN_URL'];
							$slide_link .="images/control_play.png' />Slideshow</span>";
							//if (!empty($pwa_pro)) 
							//	$out .= pwa_pro::Show_Badge(get_option(c_pwa_show_badge));
							$out .="<span><a class='back_to_list' href='";
							$out .= $pwa_options['back_link'] . "'>";
							$out .= "<img src='" . $pwa_options['PLUGIN_URL'];
							$out .= "images/control_rewind.png' />$LANG_BACK</a></span>";
							$out .= "&nbsp;| $slide_link</div>\n";

						} // End page header not off

						$out .= self::Paginate($pwa_options);

					} // End not in post
				} // End Get Title Once

				# Set image caption
				if ($text != "") { $caption = htmlentities( $text , ENT_QUOTES );
				} else { $caption = $AT . " - " . $filename; } // End Set Caption

				# Shorten caption as necessary
				if ( (strlen($caption) > $pwa_options['TRUNCATE_FROM']) &&
					($pwa_options['TRUNCATE_ALBUM_NAME'] == "TRUE")) {
					$short_caption = substr($caption,0,$pwa_options['TRUNCATE_TO']) . "...";
					if (strlen($short_caption) > $pwa_options['TRUNCATE_FROM']) { 
						$short_caption = substr($filename,0,$pwa_options['TRUNCATE_FROM']);
					}
				} else { $short_caption = $caption; } // End Shorten Caption


				# Hide Videos
				$is_video = strrpos($href, "googlevideo");

				if (($is_video === false) || ($pwa_options['HIDE_VIDEO'] == "FALSE")) {

					$jq_count++;

					if ($jq_count == 1) {
						$out .= "<div id='$container_nav_name'>\n";
						$out .= "<div style='width: 100%' style='height: ";
						$out .= $pwa_options['PHOTO_THUMBSIZE'] . "px;'>\n";
						# Added for Gallery
						$out2 ="<div id=wrapper><div id=g1 title='$AT'>";
					}

					$caption_link_tweak = pwa_int::setupCaption($caption,$pwa_options['ACTIVE_LIGHTBOX']);
					$out .= "<div class='pwaplusphp_jq_thumb' style='" . $crop_styles . " width: "; 					$out .= $pwa_options['PHOTO_THUMBSIZE'] . "px; height: ";
					$out .= ($pwa_options['PHOTO_THUMBSIZE'] + 15). "px;'>";

					$out .= "<div class='pwaplusphp_albdesc' style='height: 205px; width: 160px;'>";
					# Rob - Added via Issue 67 Enhancement
					if (!empty($pwa_pro)) {
						if ($pwa_options['SHOW_EXIF'] == "TRUE") {
							$out .= $pwa_pro::Display_Exif($photos, $i);
						}
					}
					$out .= "</div>";

					# Added for lazy loading (PRO)
					if (!empty($pwa_pro)) {
						if ($pwa_options['LAZYLOAD'] == 'TRUE') { 
							$CHECK_IF_LAZY = $pwa_pro::Display_Lazy($pwa_options,$thumb); 
						} else {
							$CHECK_IF_LAZY ="src='$thumb'";
						}
					} else {
						$CHECK_IF_LAZY ="src='$thumb'"; }
					$out .= "<a $caption_link_tweak class='pwaplusphp_imglink' href='$href'>";
					$out .= "<img class='$imgClass' $CHECK_IF_LAZY alt='$caption' ";
					$out .= "width='". $pwa_options['ALBUM_THUMBSIZE'] ."' ";
					$out .= "height='". $pwa_options['ALBUM_THUMBSIZE'] ."'></a>\n";

					# Added for Gallery
					$out2 .="<a class='imgThumb' href='$thumb' rel='nobox'></a>";
					$out2 .= "<a class='imgFull' href='$href' rel='nobox'></a>";
					$out2 .="<div class='imgDesc'>$short_caption</div>";

					# Show Download Icon (PRO?)
					if ($pwa_options['PERMIT_IMG_DOWNLOAD'] == "TRUE") {
						$out .= "\t<div class='pwaplusphp_jq_save'>";
						$out .= "<a rel='nobox' 'Save $filename' title='Save $filename' ";
						$out .= "href='$orig_href'>\n";
						$out .= "\t<img border=0 class='pwaplusphp_jq_saveimg' src='";
						$out .= $pwa_options['PLUGIN_URL'] . "images/disk_bw.png' /></a></div>\n";
					}

					# Show comments icon (PRO)
					if (!empty($pwa_pro)) $out .= $pwa_pro::Display_Comment_Box($pwa_options,$photos,$i); 

					# Show caption on hover
					if ($pwa_options['SHOW_IMG_CAPTION'] == "HOVER") {
						$out .= "\t<div class='pwaplusphp_caption'><p style='display: none;'";
						$out .= " class='pwaplusphp_captext_jq'>$short_caption</p></div>\n";
					# Always show caption
					} else if ($pwa_options['SHOW_IMG_CAPTION'] == "ALWAYS") {
						$out .= "\t<div class='pwaplusphp_caption'>";
						$out .= "<p class='pwaplusphp_captext'>$short_caption</p></div>\n";
					# Never show caption
					} else {
						$out .= "\t<div class='pwaplusphp_caption'>";
						$out .= "<p class='pwaplusphp_captext'></p></div>\n";
					}
                        
					$out .= "</div> <!--end photo-->\n";

					if ($pwa_options['IMAGES_PER_PAGE'] > 0) {
						if ((($jq_count % $pwa_options['IMAGES_PER_PAGE']) == 0) &&
							($jq_count < $pwa_options['SELECTED_PHOTOS'])) {
							$out .= "</div> <!--end start-->\n";
							$out .= "<div style='width: 100%'>\n";
						}
					}
					if ($jq_count >= $pwa_options['SELECTED_PHOTOS']) { $out .= "</div></div> <!--end slideshow-->\n"; }

				} // End Vidpos and Hide Video
			} // End Output For Loop

			# Rob - Display the More photos link (PRO)
			if (!empty($pwa_pro)) $out .= $pwa_pro::Img_On_Front_Link($pwa_options); 

			if ($pwa_options['TOTAL_PHOTOS'] == 0) { 
				echo "<div><div><div>";
				echo "Sorry... There were no photos tagged \"$TAG\"";
				//    echo "</div>";
			}

			//if ($STANDALONE_MODE == "TRUE") {
			//}
//if ($IN_POST = "TRUE") $out .= "</div></div>";
			# Ensure PWA+PHP doesn't break theme layout
			$out .= "<div style='clear: both;'></div>";
			$out .= "</div>";

			if (array_key_exists('type',$_GET)) {
				if (($_GET["type"] == 'tag') && ($pwa_options['TOTAL_PHOTOS'] != 0)) $out .= "</div>";
			}

			$out2 .="</div></div> <!--end g1-->\n";

			if ( !(is_home() || is_author() || is_archive() )) $out = $out . $out2;

			return($out);
		} // End Function

		#--------------------------------------------------------------
		# Draw Random Photos
		# Since: 1.0
		# A function to Draw random Photos
		# Used in: -self
		#--------------------------------------------------------------
		function randomPhoto($overrides_array) {
			global $pwa_pro;
			#------------------------------------------------------
			# CONFIGURATION
			#--------------------------Paginate----------------------------
			$pwa_options['PLUGIN_URI']		= plugin_dir_path( __FILE__ );
			$pwa_options['PLUGIN_URL']		= plugin_dir_url( __FILE__ );
			$pwa_options = self::setup_variables($pwa_options);
			$pwa_options['OVERRIDE_SIZE']	= (isset($overrides_array["thumbnail_size"])) ? 
				$overrides_array["thumbnail_size"] : $pwa_options['PHOTO_THUMBSIZE'];

			$pwa_options['RANDOM_PHOTOS']		= (isset($overrides_array["random_photos"])) ? 
				$overrides_array["random_photos"] : 1; // Rob

			#------------------------------------------------------
			# Load Language File
			#------------------------------------------------------
			$album_array = array();
			$photo_array = array();

			#------------------------------------------------------
			# Check for required variables from config file
			#------------------------------------------------------
			if (!isset($pwa_options['GDATA_TOKEN'], $pwa_options['PICASAWEB_USER'], 
				$pwa_options['IMGMAX'], $pwa_options['USE_LIGHTBOX'], 
				$pwa_options['OVERRIDE_SIZE'])) {
				$out = "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
				return($out);
			}

			#------------------------------------------------------
			# The Albums
			#------------------------------------------------------

			# Get the Album XML
			$pwa_options['FILE'] = "http://picasaweb.google.com/data/feed/api/user/" . 
			$pwa_options['PICASAWEB_USER'] . "?kind=album&thumbsize=" . 
			$pwa_options['ALBUM_THUMBSIZE'] . "c";

			# Store XML in Album Array
			$album_array=self::gdata_album($pwa_options);

			# If has album array, count number of albums
			if (isset($album_array)) {
				$album_count=count($album_array); 
			} else { return("No Albums!"); }

			# Rob - Allow random gallery for each photo.
			//  for ($i = 1; $i <= intval($random_photos); $i++) {	

			# Pick a random album
			if ($album_count != 0 ) {
				$random_int = mt_rand(0,$album_count-1); 
			} else {
				$random_int = 0;
			}

			# Rob - select which gallery to test.
			//$random_int = 18; 
			$pwa_options['ALBUM'] = $album_array[$random_int]['gphoto:name'];

			$cache_path = $pwa_options['PLUGIN_URL'] . "cache/" . md5($pwa_options['PICASAWEB_USER']) .
			"/" . $pwa_options['ALBUM'] . "/" . $pwa_options['OVERRIDE_SIZE'] . "/";

			# Check if cache dir exists and mkdir if not
			if ($pwa_options['OVERRIDE_SIZE'] != $pwa_options['PHOTO_THUMBSIZE']) {
				$pwa_options['CACHE_THUMBNAILS'] ='FALSE';
				$pwaimg_class = 'pwaimg_resize';     // Rob fix for js grow on my site
			} else {
				if (!empty($pwa_pro)) $pwa_options = $pwa_pro::check_cache_dir($pwa_options);
				$pwaimg_class = 'pwaimg';     // Rob fix for js grow on my site
			}

			#------------------------------------------------------
			# The Photos
			#------------------------------------------------------
			# Get the Photo XML
			$pwa_options['FILE'] = "http://picasaweb.google.com/data/feed/api/user/" . 
			$pwa_options['PICASAWEB_USER'] . "/album/" . $pwa_options['ALBUM'] . "?kind=photo&thumbsize=" . 
			$pwa_options['OVERRIDE_SIZE'] . "c&imgmax=" . $pwa_options['IMGMAX'];

			# Store XML in Photo Array
			$photo_array=self::gdata_photo($pwa_options);
			$out='';
			# Rob - Start the loop for multiple random photos.
			for ($i = 0; $i < intval($pwa_options['RANDOM_PHOTOS']); $i++) {

				# Count number of photos
				if (isset($photo_array)) $image_count=count($photo_array); 

				# Pick a random photo
				if (($image_count != 0) && (empty($image_list))) {
					$image_list = range(0,$image_count-1);
					while (count($image_list) < $pwa_options['RANDOM_PHOTOS']) {
						$image_list=array_merge($image_list,$image_list);
					}
					shuffle($image_list);  
					$random_image = $image_list[$i];
				} else if (!(empty($image_list))) {
					$random_image = $image_list[$i];
				} else {
					$random_image = 0;
				}
				//$random_image = 0; // Rob - select which photo to test.

				# Store photo array in vars
				$href=$photo_array[$random_image]['media:content'];
				$thumb=$photo_array[$random_image]['media:thumbnail'];
				$picasa_title=$photo_array[$random_image]['media:title'];
				$text = $photo_array[$random_image]['summary'];

				# Cache thumbnails
				if (!empty($pwa_pro) && $pwa_options['CACHE_THUMBNAILS'] =='TRUE') {
					$nthumb = $pwa_pro::cacheThumbnails($pwa_options,$thumb);
					if ($nthumb != '') { $thumb = $nthumb; }
				}

				# Rob - fix for missing photos. Displays an included photo to prevent breaking layout.
				if (($href=="") || ($thumb=="")) {
					echo 'Broken random_int:'. $random_int .' | '. $pwa_options['ALBUM'] .
					' | random_image:' .$random_image .' | '. $picasa_title .'<BR>'; 
					$href=$pwa_options['PLUGIN_URL'] .'images/missing.jpg';
					$thumb=$pwa_options['PLUGIN_URL'] .'images/missing.jpg';
					$picasa_title="Missing Photo";
				}

				# Rob - Add styling for first and last
				if (($i == 0) && ($i < intval($pwa_options['RANDOM_PHOTOS'])-1)) { 
					$out = "<div class='first thumbnail'>"; 
					$lightboxGroup = 'Random-'.$pwa_options['RANDOM_PHOTOS']; }
				else if (($i > 0) && ($i < intval($pwa_options['RANDOM_PHOTOS'])-1)) { 
					$out .= "<div class='middle thumbnail'>"; }
				else if (($i == intval($pwa_options['RANDOM_PHOTOS'])-1) && ($i != 0)) { 
					$out .= "<div class='last thumbnail'>"; }
				else { $out .= "<div class='thumbnail'>"; 
					$lightboxGroup = 'Random'; }

				if ($pwa_options['USE_LIGHTBOX'] == "TRUE") {
					$text = addslashes($text);
					list($AT) = explode('_',$picasa_title);
					if($text != "") {
						$out .= "<a href=\"$href\" class=\"lightbox\" rel=\"lightbox[$lightboxGroup]\" title=\"$text\"><img class='$pwaimg_class' src='$thumb' alt='image_from_picasa'></img></a>\n";
					} else {
						$out .= "<a href=\"$href\" class=\"lightbox\" rel=\"lightbox[$lightboxGroup]\" title=\"$AT\"><img class='$pwaimg_class' src='$thumb' alt='image_from_picasa'></img></a>\n";
					}

				}
 
				$out .= "</div>";
				$out .= "<div style='clear: right;'></div>";
				# Commented out for the album for loop
				//  $photo_array = array();
			}
			return($out);
		} # End Draw Random Photos


		#--------------------------------------------------------------
		# Draw Single Photo
		# Since: 1.0
		# A function to Draw single Photo
		# Used in: -self
		#--------------------------------------------------------------
		function singlePhoto($ALBUM,$overrides_array) {
			#------------------------------------------------------
			# CONFIGURATION
			#------------------------------------------------------
			global $pwa_pro;
			$pwa_options['PLUGIN_URI']	= plugin_dir_path( __FILE__ );
			$pwa_options['PLUGIN_URL']	= plugin_dir_url( __FILE__ );
			$pwa_options 			= self::setup_variables($pwa_options);
			$pwa_options['ALBUM'] 		= $specific_album = $ALBUM;
			$specific_photo          		= (isset($overrides_array["specific_photo"])) ? 
								$overrides_array["specific_photo"] : 'none';
			$pwa_options['OVERRIDE_SIZE']		= (isset($overrides_array["thumbnail_size"])) ? 
							$overrides_array["thumbnail_size"] : $pwa_options['PHOTO_THUMBSIZE'];

			#------------------------------------------------------
			# Load Language File 
			#------------------------------------------------------
			require(PWA_DIR."/lang/". get_option(c_pwa_language) .".php");

			$album_array = array();
			$photo_array = array();

			#------------------------------------------------------
			# Check for required variables from config file
			#------------------------------------------------------
			if (!isset($pwa_options['GDATA_TOKEN'], $pwa_options['PICASAWEB_USER'], 
				$pwa_options['IMGMAX'], $pwa_options['USE_LIGHTBOX'], 
				$pwa_options['OVERRIDE_SIZE'])) {
				$out = "<h1>" . $LANG_MISSING_VAR_H1 . "</h1><h3>" . $LANG_MISSING_VAR_H3 . "</h3>";
				return($out);
			}

			#------------------------------------------------------
			# The Albums
			#------------------------------------------------------
			# Get the Album XML
			$pwa_options['FILE'] = "http://picasaweb.google.com/data/feed/api/user/" . 
			$pwa_options['PICASAWEB_USER'] . "?kind=album";

			# Store XML in Album Array
			$album_array=self::gdata_album($pwa_options);

			# If has album array, count number of albums
			if (isset($album_array)) $album_count=count($album_array); 

			# Check if $Album is in Array
			if (self::in_arrayr($specific_album, $album_array)) { $ALBUM=$specific_album; }
			if ($pwa_options['ALBUM'] == '') {
				$out='<font color=red>Album not found</font>';
				return $out;
			}

			$cache_path = $pwa_options['PLUGIN_URL'] . "cache/" . 
			md5($pwa_options['PICASAWEB_USER']) . "/" . $pwa_options['ALBUM'] . 
			"/" . $pwa_options['OVERRIDE_SIZE'] . "/";

			# Check if cache dir exists and mkdir if not
			if (isset($overrides_array["thumbnail_size"])) {
				$pwa_options['CACHE_THUMBNAILS']='FALSE';
			} else {
				if (!empty($pwa_pro)) $pwa_options = $pwa_pro::check_cache_dir($pwa_options);
			}

			#------------------------------------------------------
			# The Photos
			#------------------------------------------------------
			# Get the Photo XML
			$pwa_options['FILE'] = "http://picasaweb.google.com/data/feed/api/user/" . 
			$pwa_options['PICASAWEB_USER'] . "/album/" . $pwa_options['ALBUM'] . 
			"?kind=photo&thumbsize=" . $pwa_options['OVERRIDE_SIZE'] . "c&imgmax=" . 
			$pwa_options['IMGMAX'];

			# Store XML in Photo Array
			$photo_array=self::gdata_photo($pwa_options);

			# Store photo array in vars when specific photo found
			for ($i = 0; $i < count($photo_array); $i++) {
				$href=$photo_array[$i]['media:content'];
				$thumb=$photo_array[$i]['media:thumbnail'];
				$picasa_title=$photo_array[$i]['media:title'];
				$text = $photo_array[$i]['summary'];
				if (strpos($href,$specific_photo)) $i = count($photo_array);
			}

			# If specific photo was not found, output error
			if (!strpos($href,$specific_photo)) {
				$out='<font color=red>Photo not found</font><br>';
				return $out;
			}

			# Cache thumbnails
			if (!empty($pwa_pro) && $pwa_options['CACHE_THUMBNAILS'] =='TRUE') {
					$nthumb = $pwa_pro::cacheThumbnails($pwa_options,$thumb);
					if ($nthumb != '') { $thumb = $nthumb; }
			}

			# Rob - fix for missing photos. Displays an included photo to prevent breaking layout.
			if (($href=="") || ($thumb=="")) {
				echo 'Broken '. $random_int .' | '. $pwa_options['ALBUM'] .' | ' .
				$random_image .' | '. $picasa_title .'<BR>'; 
				$href=plugin_dir_url(__FILE__) .'images/missing.jpg';
				$thumb=plugin_dir_url(__FILE__) .'images/missing.jpg';
				$picasa_title="Missing Photo";
			}

			# Rob - Add styling for first and last
			$out = "<div class='thumbnail'>"; 
			$lightboxGroup = 'Single';

			if ($pwa_options['USE_LIGHTBOX'] == "TRUE") {
				$text = addslashes($text);
				list($AT) = explode('_',$picasa_title);
				if($text != "") {
					$out .= "<a href=\"$href\" class=\"lightbox\" rel=\"lightbox[$lightboxGroup]\" title=\"$text\"><img class='pwaimg' src='$thumb' alt='image_from_picasa'></img></a>\n";
				} else {
					$out .= "<a href=\"$href\" class=\"lightbox\" rel=\"lightbox[$lightboxGroup]\" title=\"$AT\"><img class='pwaimg' src='$thumb' alt='image_from_picasa'></img></a>\n";
				}
			} 

			$out .= "</div>";
			$out .= "<div style='clear: right;'></div>";
			return($out);
		} # End Draw Single Photo


		#--------------------------------------------------------------
		# In Array
		# Since: 1.0
		# A function to check if string is in array
		# Used in: -self (Draw Single Photo)
		#--------------------------------------------------------------
		function in_arrayr( $needle, $haystack ) {
			foreach( $haystack as $v ) {
				if( $needle == $v )
					return true;
				elseif( is_array( $v ) )
					if( self::in_arrayr( $needle, $v ) )
						return true;
			}
			return false;
		} # End In Array


		#--------------------------------------------------------------
		# Admin Left Top
		# Since: 1.0
		# A function to Draw left top of admin display
		# Used in: -admin
		#--------------------------------------------------------------
		function Admin_Left_Top($options) {
			$out = "<div id='available-widgets' class='widgets-holder-wrap'>
			<div class='sidebar-name'><div class='sidebar-name-arrow'><br /></div><h3>". $options[1] ."</h3></div>
			<!--form id='". $options[3] ."' action='". $options[0] ."&pwa_action=config' method='post'-->
			<table class='widefat' cellspacing=5 width=700>";
			return $out;
		} # End Admin Left Top


		#--------------------------------------------------------------
		# Admin Left Bottom
		# Since: 1.0
		# A function to Draw left bottom of admin display
		# Used in: -admin
		#--------------------------------------------------------------
		function Admin_Left_Bottom() {
			$out = "</table>
			<div class='widget-control-actions'>
			<div class='alignright'>
			<input class='button-primary' type='submit' name='Submit' value='Save' /></div>
			<br class='clear' />
			</div><!--/form-->
			<br class='clear' />
			</div>";
			return $out;
		} # End Admin Left Bottom


		#--------------------------------------------------------------
		# Admin Right Top
		# Since: 1.0
		# A function to Draw Right top of admin display
		# Used in: -admin
		#--------------------------------------------------------------
		function Admin_Right_Top($title) {
			$out = "<div class='widgets-holder-wrap'>
			<div class='sidebar-name'><div class='sidebar-name-arrow'><br />
			</div><h3>$title</h3><span></span></div>
			<table class='widefat' style='width: 100%;'>";
			return $out;
		} # End Admin Right Top


		#--------------------------------------------------------------
		# Admin Right Bottom
		# Since: 1.0
		# A function to Draw Right bottom of admin display
		# Used in: -admin
		#--------------------------------------------------------------
		function Admin_Right_Bottom() {
			$out = '</table></div>';
			return $out;
		} # End Admin Right Bottom


function Get_News() {
	$out='';
	// Get RSS Feed(s) 
	include_once(ABSPATH . WPINC . '/feed.php'); 
	// Get a SimplePie feed object from the specified feed source. 
	$dateu = date("U");
	$rss = fetch_feed("http://wordpress.org/support/rss/tags/pwaplusphp&$dateu");
 	if (!is_wp_error( $rss ) ) {
 		// Checks that the object is created correctly      
		// Figure out how many total items there are, but limit it to 5.
		$count=0;      
		$maxitems = $rss->get_item_quantity(50);      
		
		// Build an array of all the items, starting with element 0 (first element).     
		$rss_items = $rss->get_items(0, $maxitems);  

			if ($maxitems == 0) {
				$out = "<tr><td>No items.</td></tr>";     
			} else {     
				// Loop through each feed item and display each item as a hyperlink.     
				foreach ( $rss_items as $item ) {
					$title = $item->get_title();
					$author = substr($title,0,8);
					$title = substr($title,36);
					$title = substr($title,0,-6);	// Removes &quote; from the end
					$news = substr($title,-6);
					$title = substr($title,0,-6);
					if (($author == "smccandl") && ($count <= 5) && ($news == "[News]")) { 
						$count++;
						$out .= "<tr><td>";
						$out .= "<a target='_BLANK' href='". $item->get_permalink() ."'  title='Posted ". $item->get_date('j F Y | g:i a') ."'>";
				       		$out .= "$title</a>";
						$out .= "</td></tr>";
					} 
				}
			 }
		return $out;
	}
}

		#--------------------------------------------------------------
		# Paginate
		# Since: 1.0
		# A function to paginate
		# Used in: -class
		#--------------------------------------------------------------
		function Paginate($pwa_options) {
			$header= '';

			if ($pwa_options['TYPE'] == 'album') {
				$total = $pwa_options['TOTAL_ALBUMS']; 
				$per_page = $pwa_options['ALBUMS_PER_PAGE'];
			}
			if ($pwa_options['TYPE'] == 'photo') { 
				$total = $pwa_options['TOTAL_PHOTOS']; 
				$per_page = $pwa_options['IMAGES_PER_PAGE'];
			}
			require(PWA_DIR."/lang/". get_option(c_pwa_language) .".php");

			if ($total > $per_page) {
				if ($per_page > 0) {
					$paginate = floor(($total/$per_page) + 1);
				} else {
					$paginate = 1;
				}

				$nav_width = 110 + ($paginate * 15);
				if ($pwa_options['JQ_PAGINATION'] != "none") { # Start Pagination
					$header = "<div id='pwaplusphp_navh' style='width: " . $nav_width . "px;'>";
					$header .= "<strong>$LANG_PAGE: </strong>";
					$header .= "<div id='pwaplusphp_nav'>";
					$header .= "<noscript>&nbsp;1</noscript>";
					if ($paginate == 1) { $header .= "&nbsp;1"; }
					$header .= "</div><!--end pwaplusphp_nav--></div><!--end pwaplusphp_navh-->";
				} else {
					$header .= "<div id='pwaplusphp_pages' style='width: " . $nav_width . "px;'>";
					$header .= "<strong>$LANG_PAGE:</strong>";
			
					# List pages
					$uri = $_SERVER["REQUEST_URI"];
					if (strpos($uri,$pwa_options['urlchar'])) 
						list($uri,$tail) = explode($pwa_options['urlchar'],$_SERVER['REQUEST_URI']); #changed split to url
					for ($x=1; $x<=$paginate; $x++) {
						if ($pwa_options['TYPE'] == 'album') {
							$href = $uri . $pwa_options['urlchar'] . "pg=$x"; #changed from page
						}
						if ($pwa_options['TYPE'] == 'photo') {
							$href = $uri . $pwa_options['urlchar'] . "album=". $pwa_options['ALBUM'];
							$href .= "&pg=$x"; #changed from page
						}
						# Show current page
						if (array_key_exists('pg',$_GET) && $x == $_GET['pg']) {
							$header .= "<a class='activeSlide' href='$href'>$x</a> ";
						} else {
							$header .= "<a href='$href'>$x</a> ";
						}
					} # End for loop

					$header .= "</div><!--end pwaplusphp_pages-->";
				} # End if pagination
			} # End if total > per_page
			return $header;
		} # End Paginate


		#--------------------------------------------------------------
		# Paginate File
		# Since: 1.0
		# A function to get the pagined file
		# Used in: -class
		#--------------------------------------------------------------
		function Paginate_File($pwa_options) {
			if ($pwa_options['TYPE'] == 'album') $type_per_page = $pwa_options['ALBUMS_PER_PAGE'];
			if ($pwa_options['TYPE'] == 'photo') $type_per_page = $pwa_options['IMAGES_PER_PAGE'];
			if (($type_per_page != 0) && ($pwa_options['JQ_PAGINATION'] == "none")) {
				if (array_key_exists('pg',$_GET)) { $page = $_GET['pg']; } #changed from page
				if (!(isset($page))) { $page = 1; }
				if ($page > 1) {
					$start_image_index = (($page - 1) * $type_per_page) + 1;
				} else {
					$start_image_index = 1;
				}

				$pwa_options['FILE'] .= "&max-results=" . $type_per_page . 
					"&start-index=" . $start_image_index ."&full-exif=true";
			}
			return $pwa_options;
		} # End Paginate File

		#--------------------------------------------------------------
		# Get the Active LightBox
		# Since: 1.0
		# A function to Get the Active LightBox
		# Used in: -photo
		#--------------------------------------------------------------
		function getActiveLightbox() {
			$count=0;
			$plugins = get_option('active_plugins');
			$supported_lightboxes = array("auto-highslide/auto-highslide.php","shadowbox-js/shadowbox-js.php");
			$supported_lbox_codes = array("HIGHSLIDE","SHADOWBOX");
			# Check each supported LB
			foreach($supported_lightboxes as $lightbox) {
				if ( in_array( $lightbox , $plugins ) ) {
					return($supported_lbox_codes[$count]);
				}
				$count++;
			}
			# Return NONE if no match
			return("NONE");
		} # End Get the Active LightBox


		#--------------------------------------------------------------
		# Get from Rewrite
		# Since: 1.0
		# A function to Get the Album and Page from Rewrite
		# Used in: -
		#--------------------------------------------------------------
		function Get_from_Rewrite() {
			$return = '';
			$pwa_base = get_permalink(get_option(c_pwa_main_photo_page));
			$url = 'http://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			$pos = strpos($url,$pwa_base);
			if ($pos !== false) {
				$to_search = str_replace($pwa_base, '', $url);
				@list($album,$page) = explode('/',$to_search);
				$return['album'] = $album;
				$return['page'] = $page;
			}
			return $return;
		} # End Get from Rewrite

	} # end class
} # end if class name exist
?>
