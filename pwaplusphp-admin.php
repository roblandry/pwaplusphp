<?php
/*
	PWA+PHP Admin
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

function pwa_render_admin($pwa) {

	global $wp_pwa;
	global $pwa_pro;

	// Include Pro features if PRO
	if (!empty($pwa_pro)) {
		$PRO = 'TRUE';
	} else { $PRO = 'FALSE'; }

	if (array_key_exists('test',$_REQUEST)) { $test = 'TRUE'; } else { $test = 'FALSE'; };
 
//	if ($_REQUEST['default'] != '') $wp_pwa::default_options();
	// Get current user
	global $user_ID;
	get_currentuserinfo();

	// Get settings
	$charset = get_bloginfo('charset');
	if (is_multisite()) {
		global $blog_id;
		$options[0] = get_admin_url($blog_id, 'upload.php?page=pwaplusphp', 'admin');
	}
	else
		$options[0] = admin_url('upload.php?page=pwaplusphp');
	if (isset($_REQUEST['debug']))
		$options[0] .= '&debug=1';
	if (isset($_REQUEST['tabs']))
		$options[0] .= '&tabs=0';
	if (isset($_REQUEST['pid']) && isset($_REQUEST['code'])) {
		if ($wp_pwa::Activate_Pro($_REQUEST['pid'],$_REQUEST['code']) == 'TRUE') {
			echo '<div id="message" class="updated fade pwa_notice"><p>Pro Activated</p></div>';
		}
	}
	if (isset($_REQUEST['transaction'])) {
		if (update_option('pwa_pro_key',$_REQUEST['transaction'])) {
			echo '<div id="message" class="updated fade pwa_notice"><p>Pro Activated</p></div>';
		}
	}

	// Variables
	$loc = '';
	$ACTIVE_LIGHTBOX = $wp_pwa::getActiveLightbox();

	$hide_it = array();
	$hide_it['USE_LIGHTBOX'] = 'TRUE';
	$hide_it['STANDALONE'] = 'TRUE';
	$hide_it['CHECK_FOR_UPDATES_OLD'] = 'TRUE';
	//the page
	if  (!in_array  ('curl', get_loaded_extensions())) {
		echo "<div id='error' class='error fade pwa_error'><p>PWA+PHP requires cURL and it is not enabled on your webserver.  Contact your hosting provider to enable cURL support.";
		echo "<p><i>More info is available on the <a href='http://groups.google.com/group/pwaplusphp/browse_thread/thread/49a198c531019706'>PWA+PHP discussion group</a>.</p></div>";
		//exit;
	} 
	if ($pwa_pro == "TRUE") echo pwa_pro::Admin_Pro_Errors();

?>
	<div class="wrap">
	<div id="icon-upload" class="icon32"><br /></div>
	<h2>PWA+PHP Plugin Settings</h2>
	

	<div class="widget-liquid-left">
	<form name=form1 action='<?php echo $options[0]; ?>&pwa_action=config' method='post'>
	<div id="widgets-left">

<script type="text/javascript">var URWidgetListener = function (event) {  if (event.data.indexOf("redirect") == 0) {    found = event.data.match(/redirect:url\(([^\)]*)\)/);    if (found.length == 2) {      location.href = found[1];    }  }};if (window.addEventListener) {  window.addEventListener("message", URWidgetListener, false);} else {  window.attachEvent("onmessage", URWidgetListener);} var head  = document.getElementsByTagName("head")[0];var link  = document.createElement("link");link.rel  = "stylesheet";link.type = "text/css";link.href = "http://pwaplusphp.smccandl.net/support/public/themes/default/assets/css/widget.css";link.media = "all";head.appendChild(link);</script><script type="text/javascript">widget = {url:'http://pwaplusphp.smccandl.net/support/'}</script><script src="http://pwaplusphp.smccandl.net/support/public/assets/modules/system/js/widget.js" type="text/javascript"></script>
<a class="widget-tab widget-tab-right w-round" style="margin-top:-52px;background-color:#67A2B7;border-color:#FFFFFF;" title="Support" href="javascript:popup('widget', 'http://pwaplusphp.smccandl.net/support/widget', 765, 405);"  >
  <img width="15" alt="" src="http://pwaplusphp.smccandl.net/support/public/files/logo/widget-text-default.png" />
</a>


<?php
$options[1] = 'Picasa Access Settings';
$options[2] = 'These settings must be complete in order for the plugin to function.';
$options[3] = 'admin_required';
echo $wp_pwa::Admin_Left_Top($options);
#----------------------------------------------------------------------------
# Required Configuration
#----------------------------------------------------------------------------
?>
<!-- PWA USER -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Picasaweb User</strong></td>
<td valign=top style='padding-top: 7px;'>
	<input style='width: 150px;' type='text' name='c_pwa_username' value='<?php echo get_option(c_pwa_username); ?>'>
</td>
<td valign=top style='padding-top: 8px;'><i>Enter your Picasa username.</i></td>
</tr>
<!-- /PWA USER -->

<!-- GDATA TOKEN -->
<tr>
<td valign=top style='padding-top: 5px; width: 200px;'><strong>GData Token</strong></td>
<td valign=top style='padding-top: 5px;'><?php echo get_option(c_pwa_gdata_token); ?></td>
<td valign=top style='padding-top: 5px;'><i>Allows access to unlisted Picasa albums. <a href='<?php echo $options[0]; ?>&pwa_action=gdata'>Reset Token</a></i></td>
</tr>
<!-- /GDATA TOKEN -->

<!-- ALBUMS TO SHOW -->
<?php if ($PRO != 'TRUE') { 
        if (get_option(c_pwa_access) == "public") {
                $access_public = "selected";
                $access_all = "";
        } else {
                $access_public = "";
                $access_all = "selected";
        }
?>
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Albums to Show</strong></td>
<td valign=top style='padding-top: 7px;'><select name='pwa_access'>
<option value='public' <?php echo $access_public; ?>>Public</option>
<option value='public,private,protected' <?php echo $access_all; ?>>All</option>
</select>
<td valign=top style='padding-top: 8px;'><i>Select wether to show Public or All photos.</i></td>
</tr>
<?php } ?>
<!-- /ALBUMS TO SHOW -->
<?php
echo $wp_pwa::Admin_Left_Bottom();
#----------------------------------------------------------------------------
# Pro Features
#----------------------------------------------------------------------------
if ($PRO == 'TRUE') { $pwa_pro::Admin_Pro_Options($options); }
else { echo "<img src='http://pwaplusphp.smccandl.net/images/pwaplusphp-pro-features.png' title='pro-features' alt='pro-features' style='padding-bottom: 30px;'/>"; }

#----------------------------------------------------------------------------
# Basic Display Settings
#----------------------------------------------------------------------------
$options[1] = 'Basic Display Settings';
$options[2] = 'These settings provide basic display settings.';
$options[3] = 'admin_basic';
echo $wp_pwa::Admin_Left_Top($options);
?>
<!-- SITE LANGUAGE -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Site Language</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_language'>
<?php
	$dir = dirname(__FILE__)."/lang/";
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
    		if ($dh = opendir($dir)) {
        		while (($file = readdir($dh)) !== false) {
				list($fn,$ext) = explode('.',$file);
				if ($ext == "php") {
					if ($fn != get_option(c_pwa_language)) {
						echo "<option value='$fn'>$fn</option>";
					} else {
						echo "<option value='$fn' selected>$fn</option>";
					}
				}
        		}
        		closedir($dh);
    		}
	}
?>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Sets the display language.  More may be available <a href='http://code.google.com/p/pwaplusphp/downloads/list'>here</a>.</i></td>
</tr>
<!-- /SITE LANGUAGE -->

<!-- ALBUM DATE FORMAT -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Album Date Format</strong></td>
<td valign=top style='padding-top: 7px;'><input type='text' style='width: 50px;'  name='c_pwa_date_format' value='<?php echo get_option(c_pwa_date_format); ?>'/></td>
<td valign=top style='padding-top: 8px;'><i>Define the <a href='http://php.net/manual/en/function.date.php' target='_BLANK'>date format</a> for albums.  Default setting is Y-m-d, i.e. 2010-03-12. </i></td>
</tr>
<!-- /ALBUM DATE FORMAT -->

<!-- DISPLAY STYLE -->
<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Display Style</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_show_caption'>
<?php  if (get_option(c_pwa_show_caption) == "ALWAYS") {
                $caption_always = "selected";
                $caption_hover  = "";
                $caption_never  = "";
        } else if (get_option(c_pwa_show_caption) == "HOVER") {
                $caption_always = "";
                $caption_hover  = "selected";
                $caption_never  = "";
        } else {
                $caption_always = "";
                $caption_hover  = "";
                $caption_never  = "selected";
        } ?>
<option value='ALWAYS' <?php echo $caption_always; ?>>Always Show Caption</option>
<option value='HOVER' <?php echo $caption_hover; ?>>Caption On Hover</option>
<option value='NEVER' <?php echo $caption_never; ?>>Never Show Caption</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Set display style and placement of captions. Edit CSS for Custom Style.</i></td>
</tr>
<!-- /DISPLAY STYLE -->

<!-- PERMIT IMAGE DOWNLOAD -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Permit Image Download</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_permit_download'>
<?php        if (get_option(c_pwa_permit_download) == "FALSE") {
                $download_true = "";
                $download_false= "selected";
        } else {
                $download_true = "selected";
                $download_false= "";
        } ?>
<option value='TRUE' <?php echo $download_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $download_false; ?>>FALSE</option>
</select>
</td><td valign=top style='padding-top: 8px;'><i>Determines whether the user can download the original full-size image.</i></td>
</tr>
<!-- /PERMIT IMAGE DOWNLOAD -->

<!-- IMAGES PER PAGE -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Images Per Page</strong></td>
<td valign=top style='padding-top: 7px;'><input type='text' style='width: 50px;'  name='c_pwa_images_per_page' value='<?php echo  get_option(c_pwa_images_per_page); ?>'/></td>
<td valign=top style='padding-top: 8px;'><i>Thumbnails per page. Zero means don't paginate.</i></td></tr>
</tr>
<!-- /IMAGES PER PAGE -->

<!-- ALBUMS PER PAGE -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Albums Per Page</strong></td>
<td valign=top style='padding-top: 7px;'><input type='text' style='width: 50px;'  name='c_pwa_albums_per_page' value='<?php echo  get_option(c_pwa_albums_per_page); ?>'/></td>
<td valign=top style='padding-top: 8px;'><i>Album thumbnails per page. Zero means don't paginate.</i></td>
</tr>
<!-- /ALBUMS PER PAGE -->

<!-- IMAGE SIZE -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Image Size</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_image_size'>
<?php	$image_sizes = array("1600","1440","1280","1152","1024","912","800","720","640","576","512","400","320","288","200");
	foreach ($image_sizes as $size) {
		if (get_option(c_pwa_image_size) != $size) {
			echo "<option value='$size'>$size</option>";
		} else {
			echo "<option value='$size' selected>$size</option>";
		}
	} ?>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Sets the size of the image displayed in the Lightbox.</i></td></tr>
</tr>
<!-- /IMAGE SIZE -->

<!-- ALBUM DETAILS -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Album Details</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_show_album_details'>
<?php        if (get_option(c_pwa_show_album_details) == "FALSE") {
                $details_true = "";
                $details_false= "selected";
        } else {
                $details_true = "selected";
                $details_false= "";
        } ?>
<option value='TRUE' <?php echo $details_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $details_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Overlay album thumbnail with description on mouse hover?</i></td>
</tr>
<!-- /ALBUM DETAILS -->

<!-- USE LIGHTBOX --> <!-- Commented Out!!! -->
<?php if (($hide_it['USE_LIGHTBOX'] != 'TRUE') || ($test == 'TRUE')) { ?>
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Use Lightbox</strong></td>
<td valign=top style='padding-top: 7px;'><select name='ul'>
<option value='TRUE'>TRUE</option>
<option value='FALSE'>FALSE</option>
</select></td>
<td valign=top colspan=2><i>Choose whether or not to use <a href='http://www.huddletogether.com/projects/lightbox2/'>Lightbox v2</a>.  It must be installed for this to work. When set to FALSE, full size images are displayed in a pop-up window.</i></td>
</tr>
<?php } ?>
<!-- /USE LIGHTBOX -->

<!-- MAIN PHOTO PAGE -->
<?php $args = array('selected' => get_option(c_pwa_main_photo_page), 'show_option_none' => "None"); ?>
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Main Photo Page</strong></td>
<td valign=top style='padding-top: 7px;'><?php wp_dropdown_pages($args); ?></td>
<td valign=top style='padding-top: 7px;'><i>Create a page with [pwaplusphp] and select it. Required for album cover shortcode.</i></td></tr>
<!-- /MAIN PHOTO PAGE -->

<!-- STANDALONE --> <!-- Commented Out!!! -->
<?php if (($hide_it['STANDALONE'] != 'TRUE') || ($test == 'TRUE')) { ?>
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Standalone Mode</strong></td>
<td valign=top style='padding-top: 7px;'><select name='sm'>
<option value='TRUE' selected>TRUE</option>
<option value='FALSE'>FALSE</option>
</select></td>
<td valign=top colspan=2><i>This option allows you to specify whether this code will run within a CMS (FALSE) or whether the pages will exist outside a CMS (TRUE).  Selecting FALSE suppresses output of &lt;html&gt;, &lt;head&gt; and &lt;body&gt; tags in the source.</i></td>
</tr>
<?php } ?>
<!-- /STANDALONE -->

<!-- ALBUM THUMBNAIL SIZE -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Album Thumbnail Size</strong></td><td valign=top style='padding-top: 7px;'><select name='c_pwa_album_thumbsize'>
<?php        $thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if (get_option(c_pwa_album_thumbsize) != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        } ?>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Sets the album thumbnail size. May need to alter overlay CSS if value < 160.</i></td>
</tr>
<!-- ALBUM THUMBNAIL SIZE -->

<!-- PHOTO THUMBNAIL -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Photo Thumbnail Size</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_photo_thumbsize'>
<?php        $thumb_sizes = array("160","150","144","104","72","64","48","32");
        foreach ($thumb_sizes as $size) {
                if (get_option(c_pwa_photo_thumbsize) != $size) {
                        echo "<option value='$size'>$size</option>";
                } else {
                        echo "<option value='$size' selected>$size</option>";
                }
        } ?>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Sets the photo thumbnails size.</i></td>
</tr>
<!-- /PHOTO THUMBNAIL -->

<!-- CROP THUMBNAILS -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Crop Thumbnails</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_crop_thumbs'>
<?php        if (get_option(c_pwa_crop_thumbs) == "FALSE") {
                $crop_true = "";
                $crop_false= "selected";
        } else {
                $crop_true = "selected";
                $crop_false= "";
        } ?>
<option value='TRUE' <?php echo $crop_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $crop_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Crop image thumbnails to square size or use actual ratio</i></td>
</tr>
<!-- /CROP THUMBNAILS -->
<?php
echo $wp_pwa::Admin_Left_Bottom();

#----------------------------------------------------------------------------
# Advanced Display Settings
#----------------------------------------------------------------------------
$options[1] = 'Advanced Display Settings';
$options[2] = 'These settings provide advanced display settings.';
$options[3] = 'admin_advanced';
echo $wp_pwa::Admin_Left_Top($options);
?>

<!-- TRUNCATE ALBUM NAMES -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Truncate Album Names</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_truncate_name'>
<?php        if (get_option(c_pwa_truncate_name) == "FALSE") {
                $truncate_true = "";
                $truncate_false= "selected";
        } else {
                $truncate_true = "selected";
                $truncate_false= "";
        } ?>
<option value='TRUE' $truncate_true>TRUE</option>
<option value='FALSE'$truncate_false>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Shorten album name to ensure proper display of fluid layout?</i></td>
</tr>
<!-- /TRUNCATE ALBUM NAMES -->

<!-- DESCRIPTION LIMIT LENGTH -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Description Length Limit</strong></td>
<td valign=top style='padding-top: 7px;'><input style='width: 50px;' type='text' name='c_pwa_description_length' value='<?php echo get_option(c_pwa_description_length); ?>'></td>
<td valign=top style='padding-top: 8px;'><i>Trim display length of description to specific number of characters</i></td>
</tr>
<!-- /DESCRIPTION LIMIT LENGTH -->

<!-- CAPTION LIMIT LENGTH -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Caption Length Limit</strong></td>
<td valign=top style='padding-top: 7px;'><input style='width: 50px;' type='text' name='c_pwa_caption_length' value='<?php echo get_option(c_pwa_caption_length); ?>'></td>
<td valign=top style='padding-top: 8px;'><i>Trim display length of captions to specific number of characters</i></td>
</tr>
<!-- /CAPTION LIMIT LENGTH -->

<!-- REQUIRE FILTER -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Require Filter</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_require_filter'>
<option value='TRUE'>TRUE</option>
<option value='FALSE' selected>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Is filter required? Most users should select FALSE.</i></td>
</tr>
<!-- /REQUIRE FILTER -->

<!-- SHOW DROP BOX -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Show Drop Box</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_show_dropbox'>
<?php	if (get_option(c_pwa_show_dropbox) == "FALSE") {
                $dropbox_true = "";
                $dropbox_false= "selected";
        } else {
                $dropbox_true = "selected";
                $dropbox_false= "";
        } ?>
<option value='TRUE' <?php echo $dropbox_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $dropbox_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Show the <a target='_BLANK' href='http://picasa.google.com/support/bin/answer.py?hl=en&answer=73970'>Drop Box</a> on all pages?</i></td>
</tr>
<!-- /SHOW DROP BOX -->

<!-- HIDE VIDEO -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Hide Video</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_hide_video'>
<?php	if (get_option(c_pwa_hide_video) == "FALSE") {
                $hidevideo_true = "";
                $hidevideo_false= "selected";
        } else {
                $hidevideo_true = "selected";
                $hidevideo_false= "";
        } ?>
<option value='TRUE' <?php echo $hidevideo_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $hidevideo_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Determines whether your videos are displayed within albums</i></td>
</tr>
<!-- /HIDE VIDEO -->

<!-- DISPLAY HEADER IN POST -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Display Header in Post</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_header_in_post'>
<?php	if (get_option(c_pwa_header_in_post) == "FALSE") {
                $header_in_post_true = "";
                $header_in_post_false= "selected";
        } else {
                $header_in_post_true = "selected";
                $header_in_post_false= "";
        } ?>
<option value='TRUE' <?php echo $header_in_post_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $header_in_post_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Show the Slideshow and BackLink in posts.</i></td>
</tr>
<!-- /DISPLAY HEADER IN POST -->

<!-- DISPLAY ERRORS -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Display Errors</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_report_errors'>
<?php	if (get_option(c_pwa_report_errors) == "FALSE") {
                $report_error_true = "";
                $report_error_false= "selected";
        } else {
                $report_error_true = "selected";
                $report_error_false= "";
        } ?>
<option value='1' <?php echo $report_error_true; ?>>TRUE</option>
<option value='0' <?php echo $report_error_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Show error messages.</i></td>
</tr>
<!-- /DISPLAY ERRORS -->

<!-- DELETE SETTINGS -->
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Delete Settings</strong></td>
<td valign=top style='padding-top: 7px;'><select name='c_pwa_option_clean'>
<?php	if (get_option(c_pwa_option_clean) == "FALSE") {
                $clean_true = "";
                $clean_false= "selected";
        } else {
                $clean_true = "selected";
                $clean_false= "";
        } ?>
<option value='TRUE' <?php echo $clean_true; ?>>TRUE</option>
<option value='FALSE' <?php echo $clean_false; ?>>FALSE</option>
</select></td>
<td valign=top style='padding-top: 8px;'><i>Delete all PWA+PHP options on uninstall.</i></td>
</tr>
<!-- /DELETE SETTINGS -->

<!-- CHECK FOR UPDATES OLD -->
<?php if (($hide_it['CHECK_FOR_UPDATES_OLD'] != 'TRUE') || ($test == 'TRUE')) { ?>
<tr>
<td valign=top style='padding-top: 7px; width: 200px;'><strong>Check For Updates</strong></td>
<td valign=top style='padding-top: 7px;'><select name='pwaplusphp_check_updates'>
<?php	if ($CHECK_FOR_UPDATES == "FALSE") {
                $updates_true = "";
                $updates_false= "selected";
        } else {
                $updates_true = "selected";
                $updates_false= "";
        } ?>
<option value='TRUE' $updates_true>TRUE</option>
<option value='FALSE' $update_false>FALSE</option>
</select></td>
<td valign=top colspan=2><i>When TRUE, the script will check the server once per month and print a small message at the bottom of the page if a newer version of the code is available.  Set to FALSE to completely disable update checks.</i></td>
</tr>
<?php } ?>
<!-- /CHECK FOR UPDATES OLD -->
<?php echo $wp_pwa::Admin_Left_Bottom();

#----------------------------------------------------------------------------
# Pro CSS
#----------------------------------------------------------------------------
if ($PRO == 'TRUE') $pwa_pro::Admin_Pro_Options_Css($options);
	wp_nonce_field('pwaplusphp_nonce','pwaplusphp_nonce');
?>
<!--p class="submit">
<input class='button-primary' type="submit" name="Submit" value="<?php _e('Update Options', c_pwa_text_domain ) ?>" />
</p-->

	</div>
	</form>
	</div>
<!-- End Left Side -->

<!--right-->
	<div class="widget-liquid-right">
	<div id="widgets-right">

	<!-- PURCHASE PRO -->

	<?php if (($PRO == 'FALSE') || ($test == 'TRUE')) { 
	$title = 'Purchase Pro';
	echo $wp_pwa::Admin_Right_Top($title); ?>
	<tr><td>PWA+PHP Pro is available to purchase for $10.00<br>
		<form action='http://www.landry.me/extend/plugins/pwaplusphp-pro/purchase/?action=checkout' METHOD='POST' target='blank'>
		<input type='hidden' name='domain' value='<?php echo urlencode(get_site_url()); ?>'>
		<input type='hidden' name='user' value='<?php echo get_option('pwaplusphp_picasa_username'); ?>'>
		<input type='hidden' name='return' value='<?php echo urlencode("http://". $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>'>
		<div class='alignright'>
		<input class='button-primary' type='submit' name='Submit' value='Buy Now' /></div></form>
	</td></tr>
	<tr><td>The <a href='http://pwaplusphp.smccandl.net/pro/' target='_BLANK'>Pro Version</a> offers advanced features including: support for comments, thumbnail caching for faster page loads and additional short codes for new functionality.</p></td></tr>
	<?php } 
	if (($PRO == 'TRUE') && ($test != 'TRUE')) { 
	$title = 'Donate';
	echo $wp_pwa::Admin_Right_Top($title);  } ?>

	<tr><td><p>Just want to make a donation? <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHJwYJKoZIhvcNAQcEoIIHGDCCBxQCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBFKZBYbw+H9MDKy4TqW40G/j1Rvsy2qm4PD8M0wvHxdAMPKsav3zk35gvawetL0uzqyCHhAJgporlbgP/n8lktyB3t6nG7QZFOtdGfIp1lBgtA75u9JRWX4b8PJDpRPiGS7A2HMXjcWcvf0i1h5i+EYo9nHkexqLCbS+gAGftwwTELMAkGBSsOAwIaBQAwgaQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIaVV3gCujZsiAgYCpaEil1CoADg67BMsIQQ/7D/OBwEILHAV8JjYa0bKthWnReZz3kayMXeV1y7ka5MawWxN95mIJIFGvy2k8cxdwluXIPucnTBlSYiSgrbHNs84++NxRypZk5s5YmXiWEzQ38SLDVOCXEBn2hUxdjxyaJOikipCrA/gm/JdP5YvMlqCCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTEwMDEwOTAxMjgzOFowIwYJKoZIhvcNAQkEMRYEFLo5m+x2KyALScpN3sdkZtG2lPE7MA0GCSqGSIb3DQEBAQUABIGAdMIrMI2i30YZcDLkze/KtaiBIM9Zt88KdJY6v/Zx59TrKkljeIHDol8dv4SK8GdjZq6Zo6b8i05jw+RQ9b0RqDlKHrxiMxU0PcNZzoPbaVyGC4O/SI+GJLQRCeGC1eEo612NhTPULOGV1VMfLQl+7R7iUpnwTTX62iIS2/XaUrI=-----END PKCS7-----">
<input style='margin-top: 7px; float: right;' type='submit' value='Donate Now!' class='button-secondary' /></p>
</form></td></tr>
	<?php echo $wp_pwa::Admin_Right_Bottom(); ?>
	<!-- /PURCHASE PRO -->

	<!-- HELP & SUPPORT -->
	<?php $title = 'Help & Support';
	echo $wp_pwa::Admin_Right_Top($title); ?>
	<tr><td><p>If you encounter any issues, head to the <strong><a href="http://pwaplusphp.smccandl.net/support/" target="_BLANK">support site</a></strong> or click the feedback tab on the right side of this page.</p></td></tr>
	<?php echo $wp_pwa::Admin_Right_Bottom(); ?>
	<!-- /HELP & SUPPORT -->

	<!-- NEWS -->
	<?php $title = 'News & Announcements';
	echo $wp_pwa::Admin_Right_Top($title); ?>
	<?php echo $wp_pwa::Get_News(); ?>
	<?php echo $wp_pwa::Admin_Right_Bottom(); ?>
	<!-- /NEWS -->

	<!-- PRO FOOTER -->
	<?php if ($PRO == 'TRUE') { 
		$pwa_pro::Admin_Pro_Footer(); $pv = 'Pro'; } else { $pv = 'Basic'; } ?>
	<!-- /PRO FOOTER -->

	<!-- SERVER INFO -->
	<?php $title = 'Server Information';
	echo $wp_pwa::Admin_Right_Top($title); ?>
	<tr><th>PWA+PHP</th><td>v <?php echo get_option(c_pwa_version) . " ". $pv; ?></td></tr>
	<tr><th>Hostname</th><td><?php echo $_SERVER['HTTP_HOST']; ?></td></tr>
	<?php $curlver = curl_version(); ?>
	<tr><th valign=top>Webserver</th><td><?php echo $_SERVER['SERVER_SOFTWARE'] . " " . PHP_OS;?></td></tr>
	<tr><th valign=top>PHP/cURL</th><td>v <?php echo  phpversion() . " / v " . $curlver["version"]; ?></td></tr>
	<?php echo $wp_pwa::Admin_Right_Bottom(); ?>
	<!-- /SERVER INFO -->

	</div>
	<p><img src='http://code.google.com/apis/picasaweb/images/wwpicasa120x60.gif' /></p>
	</div>

<!-- end Right-->

</div>
<?php
}
?>
