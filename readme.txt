=== PWA+PHP ===
Contributors: smccandl, rob.a.landry
Donate link: http://pwaplusphp.smccandl.net/
Tags: picasa, picasa web albums, pwa, lightbox, fancybox, shadowbox, highslide, picasa comments, wptouch
Requires at least: 2.9.1
Tested up to: 3.3.1 
Stable tag: trunk

PWA+PHP allows you to display public and private (unlisted) Picasa albums within WordPress in your language using Fancybox, Shadowbox or Lightbox.

== Description ==
**v0.9.6 Update:** *New features: select number of photos to show on the blog list (Issue 120), random and comment widget (Issue 137), shortcode buttons in the post/page editor (Issue 29), slideshow link in album (Issue 108), variable number of random photos with optional thumb size (Issue 126), and various bug fixes.

**v0.9.3 Update:** *New features: paginate the albums page and override image size, thumbnail size and images per page in the shortcode.  Bugs fixed in back to albums link.*

[PWA+PHP](http://pwaplusphp.smccandl.net) is a lightweight solution for displaying your private (unlisted) and public Picasa Albums within Wordpress in your language using Fancybox, Shadowbox or Lightbox. The plugin provides a guided installer that helps you generate your gdata token and set display options for your albums. 

PWA+PHP extends the capabilites of Picasa albums, allowing you to [group albums by keywords](http://code.google.com/p/pwaplusphp/wiki/Album_Filtering) in the title.  Using this capability, you can create several photo pages in WordPress and show different groups of albums on each page. You can even allow users to download full-size copies of your images.

PWA+PHP's configuration options allow you to customize the look and feel of your albums, including thumbnail and image size, images per page, caption settings and display language, without modifying any code. The included CSS file can also be tweaked to your liking for an exact match with your existing website.  The div-based layout is fluid and adjusts automatically to fit your theme.

Check out [the demo](http://pwaplusphp.smccandl.net/pwademo.php) to see the code in action.

== Installation ==

1. Extract the archive within the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Open the Settings section and click on PWA+PHP
1. Complete the setup (token generation and options)
1. Create a new page with contents `[[pwaplusphp]]` or `[[pwaplusphp lbum="album_name"]]` or `[[pwaplusphp lbum="random_photo"]]` or `[[pwaplusphp filter="keyword"]]` or `[[pwaplusphp tag="sometag"]]`
1. Check out the [full guide](http://code.google.com/p/pwaplusphp/wiki/WordPressPluginHelp) and [report bugs](http://code.google.com/p/pwaplusphp/issues/entry?template=Defect%20report%20from%20user) as necessary.

== Frequently Asked Questions ==

= No photos, just 'Gallery photos in Albums' =

This indicates an issue with your webserver's ability to connect to Picasa.  See [Issue 23](http://code.google.com/p/pwaplusphp/issues/detail?id=23)

= What if I don't want to display private (unlisted) albums? =

After the token generation step, simply set the "Public Albums Only" option to TRUE.

= How do I filter albums? =

See [our wiki](http://code.google.com/p/pwaplusphp/w/list)

= PWA+PHP is not working with Lightbox, why? =

Please use [Lightbox 2 plugin by Rupert Morris](http://wordpress.org/extend/plugins/lightbox-2/), it provides the Lightbox effect and works as expected with PWA+PHP.

= PWA+PHP is not working with Highslide, why? =

Install [Auto Highslide](http://wordpress.org/extend/plugins/auto-highslide/), it works as expected with PWA+PHP.

= When I hover over an album, the detail overlay is either too big or too small. Why? =

The CSS file that ships with PWA+PHP assumes an album thumbnail of 160px.  If you change that size via the settings page, you may need to adjust the CSS settings by changing the 90% and 100% values on the a.overlay:hover line.

== Screenshots ==

1. Main gallery view, description on mouse over 
2. View of images in album
3. Full-size image in a Shadowbox
4. Settings page
5. Hover display option 
6. Overlay display option

== Changelog ==
=v0.9.6=
* New features: select number of photos to show on the blog list (Issue 120), random and comment widget (Issue 137), shortcode buttons in the post/page editor (Issue 29), slideshow link in album (Issue 108), variable number of random photos with optional thumb size (Issue 126).
* Bug fix: various.

= 0.9.3 =
* Added ability to paginate the albums page for users with large number of albums
* Added ability to override image size, images per page and thumbnail size within the shortcode to allow for individual albums to have specific settings.
* Fixed <a href='http://code.google.com/p/pwaplusphp/issues/detail?id=63'>Issue 63</a>.

= 0.9.2 =
* Bug fix: fixed issue where username is displayed as title when using tags

= 0.9.1 = 
* Bug fix: removed debug URL echo from the top the album page. User can also delete line: echo $file from dumpAlbumList.php.

= 0.9 =
* Added new shortcode option, 'tag', to display photos matching tags in all albums or in a specific album when paired with 'album' option.
* Added 'Settings' link underneath the plugin name on the Plugins page for easier access to the settings after install
* Updated the settings page target so that the user is returned to the settings page after clicking Update button
* Fixed problem with pagination so that the Pages div is not displayed unless there are multiple pages of results

= 0.8 =
* Added new display mode 'Custom Style' with no hardcoded CSS style in the code. Users have complete control of the look and feel via style.css.
* Renamed "Show Photo Caption" option on settings page to "Display Style"
* Fixed display bug for captions with apostrophes.

= 0.7 =
* Display tweaks for enhanced compatibility with WPtouch iPhone Theme in mobile browsers
* Fixed display bug with Overlay caption mode in Firefox
* Fixed Language variable issue in album cover shortcode when used in sidebar
* Fixed several layout issues for better theme compatibility
* Moved some remaining CSS in PHP files to CSS file for easier customization
* Pro version: Added links to full image in recent comments block
* Pro version: Fixed bug with comment image display

= 0.6 =
* Slick new settings page with WP look and feel
* Option to upgrade to Pro Version for comments, caching and new shortcodes
* Fixed broken v0.5 Hide Video Option
* Added ability to regenerate gdata token w/o remove re-add

= 0.5 =
* New option allows for use of uncropped thumbnails
* New "Overlay" display option, using CSS from [MyPHPDropBoxGallery](http://wiki.dropbox.com/DropboxAddons/MyPHPDropBoxGallery)
* New option to specify [date format](http://php.net/manual/en/function.date.php) on album page
* Updated images per page option to allow any integer value

= 0.4 =
* New option allows for different size album and photo thumbnails
* New options to set trim length for descriptions and captions via settings panel
* Re-designed install panel with settings in groups
* Forced "always" caption mode for IE6 users when using "hover" caption mode

= 0.3 =
* Beta version - added filter functionality to shortcode and caption display options in gallery view

= 0.2 =
* Alpha version - initial release.

== Upgrade Notice ==
= 0.9 =
* New 'tag' option in shortcode, easier access to settings via plugins page, improved settings page behavior, and pagination bug fix.

= 0.8 =
* New "Custom Style" display mode for easy look and feel tweaking.  Caption display bug fix.

= 0.7 =
* Improved WPtouch compatibility, various display-related bug fixes

= 0.6 =
* New settings page, fixed broken Hide Video option, new Pro version upgrade available

= 0.5 =
* Support for uncropped thumbnails, new display option, new flexible date format, ability to hide videos

= 0.4 =
* New config option for setting the album thumbnail size, redesigned settings page and ability to set trim lengths on settings page

= 0.3 =
* Upgrade to use filter in shortcode and for caption options in gallery view

= 0.2 =
* Initial release.

== Basic Version Features ==

* Embed all your public, private and unlisted Picasa web albums on any website
* Group and filter albums [using keywords](http://code.google.com/p/pwaplusphp/wiki/Album_Filtering) in the album title
* Uploaded images via email, web interface or desktop app and see them instantly
* Display image totals for the entire gallery and by album
* Configure caption settings with 4 options
* Allow users to download full-size copies of your images
* Install script guides setup and token generation for private albums
* Interface configuration via [16 variables](http://code.google.com/p/pwaplusphp/wiki/ConfigurationOptions) (image size, thumbnail size, pagination, etc)
* Modify included CSS file to match your site exactly
* Available in 8 languages and extensible to others!
* Hide Video: Option to suppress display of video thumbnails
* Improved compatibility with WPtouch iPhone theme in v0.7

== Additional Pro Version Features ==

* Comments: Show photo's comments in Shadowbox, allow users to add comments to photos, and show recent comments in widget
* Cache Thumbnails: Save thumbnails on server for faster page loads
* Album Cover Shortcode: Show album cover thumbnail in a post instead of album contents
* Random Album Shortcode: Show random album, instead of random photo
