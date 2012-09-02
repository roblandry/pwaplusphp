<?php
/*
Plugin Name: 	PWA+PHP
Plugin URI: 	http://pwaplusphp.smccandl.net/
Description:	PWA+PHP allows you to display public and private (unlisted) Picasa albums within WordPress in your language using Fancybox, Shadowbox or Lightbox.	
Author: 	Scott McCandless
Contributors:	Rob Landry
Version:	1.0
Author URI: 	http://pwaplusphp.smccandl.net/
*/

/*
	GNU General Public License version 3

	Copyright (c) 2011, 2012 Scott McCandless

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

// Check PHP version
if (version_compare(PHP_VERSION, '5.0.0', '<'))
	die('PWA+PHP requires at least PHP 5, installed version is ' . PHP_VERSION);

// Auto load classs
if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
	function __autoload_PWA($class_name) {
		if ($class_name == 'pwa_int')
			require_once('pwaplusphp-int.php');
		else if ($class_name == 'PluginUpdateChecker')
			require_once('plugin-update-checker.php');
	}
	spl_autoload_register('__autoload_pwa');
}
else {
	if (function_exists('__autoload')) {
		// Another plugin is using __autoload too
		require_once('pwaplusphp-int.php');
		require_once('plugin-update-checker.php');
	}
	else {
		function __autoload($class_name) {
			if ($class_name == 'pwa_int')
				require_once('pwaplusphp-int.php');
			else if ($class_name == 'PluginUpdateChecker')
				require_once('plugin-update-checker.php');
		}
	}
}

// Include main class
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (is_plugin_active('pwaplusphp-pro/pwaplusphp-pro.php')) {
	require_once(WP_PLUGIN_DIR.'/pwaplusphp-pro/pwaplusphp-pro.php' );
	global $pwa_pro;
	if (empty($pwa_pro)) $pwa_pro = new pwa_pro();
}
require_once('pwaplusphp-class.php');
require_once('pwaplusphp-depreciated.php');



// Check pre-requisites
pwaplusphp::Check_prerequisites();

// Start plugin
global $wp_pwa;
if (empty($wp_pwa)) {
	$wp_pwa = new pwaplusphp();
	register_activation_hook(__FILE__, array(&$wp_pwa, 'Activate'));
}
$to_report = get_option(c_pwa_report_errors);
//pwaplusphp::report_errors($to_report);

// Pro version is not hosted on wordpress.org
//if (get_option('pwaplusphp_pro') == 'TRUE') {
	global $updates_pwa;
	if (empty($updates_pwa)) {
		$updates_url = "http://www.landry.me/extend/plugins/pwaplusphp/update/";
		$updates_pwa = new PluginUpdateChecker($updates_url, __FILE__, 'pwaplusphp', 1);
	}
//}

// Testing pointers

function pwaplusphp_pointer() {
	$pointer_content  = '<h3>' . __( 'New with PWA+PHP Pro', 'pwaplusphp-pro' ) . '</h3>';
	$pointer_content .= '<p>' . __( '<p>Completly Redesigned layout.<br>Pro Custom CSS.</p> ', 'pwaplusphp-pro' ) . '</p>';
?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	$('#adminmenu a.current').pointer({
		content: '<?php echo $pointer_content; ?>',
		position: {
			my: 'left top',
			at: 'center bottom',
			offset: '-25 0'
		},
		close: function() {
			setUserSetting( 'p1', '1' );
		}
	}).pointer('open');
});
//]]>
</script>
<?php
}
//add_action('admin_footer','pwaplusphp_pointer');

function  add_js_code(){
$jscode ="
jQuery(document).ready( function($) {
var options = {'content':'<h3>Title<\/h3><p>Contents<\/p>','position':{'edge':'left','align':'center'}};
if ( ! options )
return;
options = $.extend( options, {
close: function() {
//to do
}
});
$('#available-widgets').pointer( options ).pointer('open');
});
</script>
";
echo $jscode;
}
//add_action('admin_footer','add_js_code');


/**
* Setup CSS and Javascript
**/
add_action('wp_enqueue_scripts', 'pwaplusphp_scripts_method');

function pwaplusphp_scripts_method() {

// Javascripts ----------------------------------------------------------------
  # include latest jquery min
  wp_deregister_script( 'jquery' );
  wp_register_script( 'jquery', plugins_url('/js/jquery.min.js', __FILE__), array(), '1.7.1');
  wp_enqueue_script( 'jquery' );

  # include Cycle script
  wp_enqueue_script('jquery_cycle', plugins_url('/js/jquery.cycle.all.latest.js', __FILE__), array('jquery'), '2.88' );
  
  # include mbgallery script for slideshow link
  wp_enqueue_script('mbgallery', plugins_url('/js/mbGallery.js', __FILE__), array('jquery'), '2.0.2' );
  
// Stylesheets ----------------------------------------------------------------

  # include PWAplusPHP stylesheet
  if (!class_exists('pwa_pro')) wp_enqueue_style( 'pwaplusphp-style', plugins_url('/css/style.css', __FILE__), array(), '0.9.6' );

  # include mbgallery stylesheet
  wp_enqueue_style( 'mbgallery-style', plugins_url('/css/white.css', __FILE__), array(),'0.9.6' );
	
}

/**
* Add Admin header js 
* Added 0.9.7
*/


function pwaplusphp_admin_header() { ?>
<script type="text/javascript"> 

window.onload = OnLoadSelect;
function OnLoadSelect() {
  lazyimg=document.getElementById('lazyload_img').style
  select_value ='<?php echo get_option("pwaplusphp_lazyload","FALSE"); ?>';
    if(select_value != 'TRUE') {
      lazyimg.display = ( lazyimg.display != "none" ) ? "none" : "";//Hide Fields
    } else {
      lazyimg.display = "table-row";//Show Fields
    }
}

  function UpdateSelect() {
    select_value = document.form1.pwaplusphp_lazyload.value;
    var id = 'lazyload_img';
    var obj = '';
    obj = (document.getElementById) ? document.getElementById(id) : ((document.all) ? document.all[id] : ((document.layers) ? document.layers[id] : false));
    if(select_value != 'TRUE') {
      obj.style.display = ( obj.style.display != "none" ) ? "none" : "";//Hide Fields
    } else {
      obj.style.display = "table-row";//Show Fields
    }
  }
</script>
<?php }

/**
* Setup Custom Javascript
**/
add_action('wp_head', 'addHeaderCode');
function addHeaderCode() {
  $JQ_PAGINATION_STYLE = get_option("pwaplusphp_jq_pagination","fade");
?>
<script type="text/javascript"> 
// This function updated 2011-08-05 for Issue 106
// http://code.google.com/p/pwaplusphp/issues/detail?id=106
jQuery(function ($) {
	$(document).ready(function() {
		$("div.pwaplusphp_jq_thumb").mouseenter(function() {
    			$(this).find("p.pwaplusphp_captext_jq").show();
  		}).mouseleave(function() {
    			$(this).find("p.pwaplusphp_captext_jq").hide();
  		});
	});

	$(document).ready(function() {
		$('#slideshow')
		.cycle({ 
    			fx:     '<?php echo $JQ_PAGINATION_STYLE; ?>', 
    			speed:  'slow', 
    			timeout: 0, 
    			pager:  '#pwaplusphp_nav'
		});
		$('img.pwaplusphp_img_nf,img.pwaplusphp_img')
		.lazyload({ 
			event: "scrollstop"
		});

		$(function() {
			// OPACITY OF BUTTON SET TO 50%
			$(".pwaplusphp_img").css("opacity","1");
			// ON MOUSE OVER
			$(".pwaplusphp_img").hover(function () {
				// SET OPACITY TO 100%
				$(this).stop().animate({
					opacity: 0.3
				}, "slow");
			},
			// ON MOUSE OUT
			function () {
				// SET OPACITY BACK TO 50%
				$(this).stop().animate({
					opacity: 1
				}, "slow");
			});
		});
	});
})
</script>
<script type="text/javascript">
function OnloadFunction () {
  jQuery(function ($) {

		$("div.pwaplusphp_jq_thumb").mouseenter(function() {
    			$(this).find("p.pwaplusphp_captext_jq").show();
  		}).mouseleave(function() {
    			$(this).find("p.pwaplusphp_captext_jq").hide();
  		});

    $('img.pwaplusphp_img_nf,img.pwaplusphp_img')
    .lazyload({ event: "scrollstop" });

    // OPACITY OF BUTTON SET TO 50%
    $(".pwaplusphp_img").css("opacity","1");
    // ON MOUSE OVER
    $(".pwaplusphp_img").hover(function () {
      // SET OPACITY TO 100%
      $(this).stop().animate({
        opacity: 0.3
      }, "slow");
    },
	
    // ON MOUSE OUT
    function () {
      // SET OPACITY BACK TO 50%
      $(this).stop().animate({
        opacity: 1
      }, "slow");
    });

  })
}
</script>

<script>
(function($){
  $(function(){

    function drawSlideshow(){
      $('#g1').mbGallery({maskBgnd:'#ccc', overlayOpacity:.9,startFrom: 0});
    }
    $('span[id="slideshow_button"]').on('click',function(){   //onclick handler
      drawSlideshow();
    });

  });
}(jQuery));
</script>

<?php	
}


function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}

/**
* Add shortcode for embedding the albums in pages
*/

function pwaplusphp_shortcode( $atts, $content = null ) {
	global $wp_pwa;
	global $pwa_pro;

	$overrides_array=array(); // Rob - Fixes undefined.
	extract(shortcode_atts(array("album" => 'NULL'), $atts));
	extract(shortcode_atts(array("filter" => ''), $atts));
	extract(shortcode_atts(array("cover" => ''), $atts));
	extract(shortcode_atts(array("comments" => ''), $atts));
	extract(shortcode_atts(array("tag" => 'NULL'), $atts));
	extract(shortcode_atts(array("header" => 'NULL'), $atts));

	# Overrides
	extract(shortcode_atts(array("images_per_page" => 'NULL'), $atts));
	extract(shortcode_atts(array("image_size" => 'NULL'), $atts));
	extract(shortcode_atts(array("thumbnail_size" => 'NULL'), $atts));
	extract(shortcode_atts(array("picasaweb_user" => 'NULL'), $atts));
	extract(shortcode_atts(array("page_header" => 'NULL'), $atts));
	extract(shortcode_atts(array("hide_albums" => 'NULL'), $atts));
	extract(shortcode_atts(array("random_photos" => 'NULL'), $atts)); // Rob
	extract(shortcode_atts(array("specific_photo" => 'NULL'), $atts)); // Rob
	extract(shortcode_atts(array("flickr" => 'NULL'), $atts));  // Rob 
	
	if (($images_per_page != "") && ($images_per_page != "NULL"))
		$overrides_array["images_per_page"] = $images_per_page;
	if (($image_size) && ($image_size != "NULL"))
		$overrides_array["image_size"] = $image_size;
	if (($thumbnail_size != "") && ($thumbnail_size != "NULL")) // Rob 
		$overrides_array["thumbnail_size"] = $thumbnail_size;
	if (($picasaweb_user) && ($picasaweb_user != "NULL"))
			$overrides_array["picasaweb_user"] = $picasaweb_user;
	if (($page_header) && ($page_header != "NULL"))
			$overrides_array["page_header"] = $page_header;
	if (($hide_albums) && ($hide_albums != "NULL"))
			$overrides_array["hide_albums"] = $hide_albums;
	if (($random_photos) && ($random_photos != "NULL"))			//Rob
			$overrides_array["random_photos"] = $random_photos;	//
	if (($specific_photo) && ($specific_photo != "NULL"))			//Rob
			$overrides_array["specific_photo"] = $specific_photo;	//
	if (($flickr) && ($flickr != "NULL"))			//Rob
			$overrides_array["flickr"] = $flickr;	//

	# Search for Tag or Filter
	$search_str=''; $type_str='';
	if (array_key_exists('search',$_GET)) $search_str=$_GET["search"];
	if (array_key_exists('cat',$_GET)) $type_str=$_GET["cat"];
	if (($type_str == "filter") || (($type_str == "") && ($search_str != ""))) $filter = $search_str;
	if (($type_str == "tag") && ($search_str != "" )) $tag = $search_str;
			
	# End of overrides
	if ((isset($comments)) && ($comments != "") && (!empty($pwa_pro))) {
		//$out = getRecentComments($comments);
		require_once(PRO_DIR.'/pwaplusphp-comments.php');
		$out = getComment($comments);
		return($out);
	} else if ( ($cover == "TRUE") && ((!array_key_exists('album',$_GET)) || (isset($album))) ){
		$out = $wp_pwa::Draw_Albums($album,$cover);
		return($out);
	} else if (($album == "NULL") && (!array_key_exists('album',$_GET)) && ($random_photos == "NULL") && ($tag == "NULL") && ($flickr == 'NULL')) { // Rob - Prevents entering dAL if random_photos
                $out = $wp_pwa::Draw_Albums($filter,$overrides_array);
                return($out);
	} else if ($random_photos != "NULL") {		// Rob
		$out = $wp_pwa::randomPhoto($overrides_array);	//
                return($out);				//
        } else if ($flickr != "NULL") {
		$out = $wp_pwa::Draw_Photos($album,"FALSE",$tag,$overrides_array);
                return($out);				//
	} else {
		if ($album != "NULL") {
			if ($album == "random_photo") {
				$out = pwa_dep::shortcode_album_random_photo($overrides_array);
			} else if ($album == "random_album") {
				$out = $wp_pwa::Draw_Albums("RANDOM");
			} else if ($specific_photo != "NULL") {
				$out = $wp_pwa::singlePhoto($album,$overrides_array);
			} else {
				$out = $wp_pwa::Draw_Photos($album,"TRUE",$tag,$overrides_array);
			}
                } else if (array_key_exists('album',$_GET)) {
                        $album = $_GET["album"];
			$out = $wp_pwa::Draw_Photos($album,"FALSE",$tag,$overrides_array);
                } else if ($tag != "NULL") {
			$out = $wp_pwa::Draw_Photos($album,"FALSE",$tag,$overrides_array);
		}
	return($out);
        }

}
/**
* Add shortcode for embedding the albums in pages 
*/
add_shortcode('pwaplusphp', 'pwaplusphp_shortcode');

// Add settings link on plugin page
function pwaplusphp_settings_link($links) { 
  $settings_link = '<a href="upload.php?page=pwaplusphp-pro/pwaplusphp.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'pwaplusphp_settings_link' );

/*add_filter('option_rewrite_rules','pwa_rewrite_rules');
function pwa_rewrite_rules($rules){

	$page_id = get_option(c_pwa_main_photo_page);
	$pwa_base = basename(get_permalink($page_id)).'/';

	//list($pwa_current_url,$query) = explode('?',$_SERVER['REQUEST_URI']);
	# <your-picasa-page>/<your-album>/<your-album-page>/
	$pwa_rules[$pwa_base.'([^/]+)/?$'] = 'index.php?pageid='.$page_id.'&album=$matches[1]&pg=$matches[2]'; 

	$pwa_rules = apply_filters('pwa_rewrite_rules',$pwa_rules);
	
	// I want the MP rules to appear at the beginning - thereby taking precedence over other rules
	$rules = $pwa_rules + $rules;
	
	return $rules;
}*/
?>
