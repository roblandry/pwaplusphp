<?php
/*
	PWA+PHP Constants
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
global $pwa_pro;
if (!empty($pwa_pro)) $pwa_pro::Define_Pro_Constants();

  // Define constants
  define('c_pwa_text_domain', 'pwaplusphp');
  define('c_pwa_min_cap', 'manage_options');
  define('c_pwa_version', 'pwaplusphp_version');
  define('c_pwa_nonce_form','pwaplusphp-nonce-form');
  define('PWA_DIR', plugin_dir_path(__FILE__));
  define('PWA_URL', plugin_dir_url(__FILE__));

  // Config Options
  define('c_pwa_use_lightbox', 'TRUE');
  define('c_pwa_standalone_mode', 'TRUE');
  define('c_pwa_gdata_token', 'pwaplusphp_gdata_token');
  define('c_pwa_username', 'pwaplusphp_picasa_username');
  define('c_pwa_image_size', 'pwaplusphp_image_size'); //IMGMAX
  define('c_pwa_photo_thumbsize', 'pwaplusphp_photo_thumbsize');
  define('c_pwa_album_thumbsize', 'pwaplusphp_album_thumbsize');
  define('c_pwa_require_filter', 'pwaplusphp_require_filter');
  define('c_pwa_images_per_page', 'pwaplusphp_images_per_page');
  define('c_pwa_albums_per_page', 'pwaplusphp_albums_per_page');
  define('c_pwa_access', 'pwaplusphp_access');
  define('c_pwa_show_album_details',  'pwaplusphp_album_details');
  #define('c_pwa_updates', 'pwaplusphp_updates');
  define('c_pwa_show_dropbox', 'pwaplusphp_show_dropbox');
  define('c_pwa_truncate_name', 'pwaplusphp_truncate_name');
  define('c_pwa_language', 'pwaplusphp_language');
  define('c_pwa_permit_download', 'pwaplusphp_permit_download');
  define('c_pwa_show_footer', 'pwaplusphp_show_footer');
  define('c_pwa_show_caption', 'pwaplusphp_show_caption');
  define('c_pwa_caption_length', 'pwaplusphp_caption_length');
  define('c_pwa_description_length', 'pwaplusphp_description_length');
  define('c_pwa_crop_thumbs', 'pwaplusphp_crop_thumbs');
  define('c_pwa_date_format', 'pwaplusphp_date_format');
  define('c_pwa_hide_video', 'pwaplusphp_hide_video');
  define('c_pwa_report_errors', 'pwaplusphp_report_errors');
  define('c_pwa_option_clean', 'pwaplusphp_option_clean');
  define('c_pwa_header_in_post', 'pwaplusphp_header_in_post');
  define('c_pwa_main_photo_page', 'pwaplusphp_main_photo');
  define('c_pwa_jq_pagination', 'pwaplusphp_jq_pagination');

  // Combined Options
  define('c_pwa_truncate_from', 'pwaplusphp_caption_length');
  define('c_pwa_truncate_to', 'pwaplusphp_caption_length - 3');

  // Album Options
  define('c_pwa_tzpp', 'pwaplusphp_album_thumbsize + 45');
  define('c_pwa_description_length_to', 'pwaplusphp_description_length - 3');
  #define('TW20']	= define('ALBUM_THUMBSIZE'] + round(define('ALBUM_THUMBSIZE'] * .1);
  define('c_pwa_twm10', 'pwaplusphp_album_thumbsize - 8');

  // Photo Options
  define('c_pwa_thumbnail_size', 'pwaplusphp_thumbnail_size');

  // Pro Options
  /*define('c_pwa_pro', 'pwaplusphp_pro');
  define('c_pwa_flickr_username', 'pwaplusphp_flickr_username');
  define('c_pwa_cache_thumbs', 'pwaplusphp_cache_thumbs');
  define('c_pwa_show_comments', 'pwaplusphp_show_comments');


  define('c_pwa_images_on_front', 'pwaplusphp_images_on_front');
  define('c_pwa_show_button', 'pwaplusphp_show_button');
  define('c_pwa_add_widget', 'pwaplusphp_add_widget');
  define('c_pwa_lazyload', 'pwaplusphp_lazyload');
  define('c_pwa_lazyload_img', 'pwaplusphp_lazyload_img');
  define('c_pwa_show_exif', 'pwaplusphp_show_exif');
  define('c_pwa_photo_widget', 'pwaplusphp_photo_widget');
  define('c_pwa_comments_widget', 'pwaplusphp_comments_widget');
  define('c_pwa_search_widget', 'pwaplusphp_search_widget');
  define('c_pwa_cover_widget', 'pwaplusphp_cover_widget');
  define('c_pwa_show_badge', 'pwaplusphp_show_badge');*/
//  define('c_pwa_css', 'pwaplusphp_css');
?>
