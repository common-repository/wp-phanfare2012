=== WP-Phanfare2012 ===
Contributors: Craig Meyer
Donate link: http://TheMeyers.org/
Tags: Phanfare, WordPress, Galleries, Albums, Photos, Images, Lightbox, SimplePie, jQuery, RSS
Requires at least: 2.6
Tested up to: 3.4.2
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This WordPress plugin integrates your Phanfare albums and images into your Posts and Pages on your WordPress blog.

== Description ==

WP-Phanfare2012 integrates your Phanfare albums and images into your Posts and Pages on your WordPress blog. Phanfare is a popular online Photo sharing website at [http://www.Phanfare.com/](http://www.Phanfare.com/).

Visit the [WP-Phanfare2012 homepage](http://TheMeyers.org/geek/wordpress/wp-phanfare/) for more information on how to incorporate the plugin into your blog.

== Installation ==

WP-Phanfare plugin depends on the following WordPress Plugins

* SimplePie Core (at least V1.1.1)
* jQuery LightBox (at least V0.9)

1. Download the plugin archive and expand it

2. Place the 'wp-phanfare2012' folder into your wp-content/plugins/ directory. Your folder structure should look like this:

* wp-content/plugins/wp-phanfare2012/wp-phanfare2012.php
* wp-content/plugins/wp-phanfare2012/wp-phanfare.js
* and the readme.txt and various screenshot-#.jpg images

3. Go to the Plugins page in your WordPress Administration area and click 'Activate' for WP-Phanfare.

NOTE: If you have installed a previous version of WP-Phanfare in the wp-content/plugins/ directory, please delete it before installing this version of WP-Phanfare.

**Usage**

WP-Phanfare installs a new option in the Post and Page edit subpanel. Fill out the form with information about your Phanfare gallery:

1. Phanfare RSS URL - the URL to your Phanfare album RSS feed.
When using the Phanfare Web Client, *Edit* the Album, and click on *Share Album*, then *RSS Feed*

2. Title (optional) - a title to be displayed above the album thumbnails.

3. Description (optional) - a description of the album to be displayed below the title and above the album thumbnails.

4. Start at image #: the index from which your album will begin displaying thumbnails. 

5. Number of images to display: the number of images to display from the album.

6. Thumbnail size: choose between the tiny 100x100 or regular 150x150 sized thumbnails.

7. Image size: the size of the image to display when the viewer clicks on a thumbnail.

8. Clicking on Image, Displays: where the browser will go when the viewer clicks on a thumbnail. Choices are:
* The Phanfare album page
* The image file itself
* Display your image using jQuery Lightbox
* Any page on your WordPress blog (not yet)

9. Options: Configure additional options for your Phanfare album

Click on the Send to Editor button to insert the WP-Phanfare shortcode into your post.

**Changing Defaults**

In the WordPress Administration area, click Settings->WP-Phanfare. On this page, you can adjust the default settings shown in the WP-Phanfare options panel when editing a Post or Page. You can also add your own custom CSS by entering CSS styles on this page.

== Frequently Asked Questions ==

= I updated a caption on my Phanfare album, but WP-Phanfare is still showing the old caption. = 

WP-Phanfare uses SimplePie (http://simplepie.org/) to retrieve feeds.
The default duration is 3600 seconds, or 60 minutes.
Adjust the Cache duration in the Admin  Settings, WP-Phanfare panel


== Screenshots ==

1. This is the WP-Phanfare Panel which appears at the bottom of Post or Page edit screen.

2. This is the WP-Phanfare Admin Settings Panel which appears for configuring defaults.

3. This is a capture of the Phanfare Web Client showing the *Share Album* option, with the *RSS Feed* option.


== Changelog ==

= future =
* Expose the cache directory location in the Settings panel
* Allow for customization of optional text at bottom of thumbnail display
* (harder, but possible) Preload lightbox slideshow with all Feed images, even if the User only chooses to display a few thumbnails
* Allow for customization of thumbnail size...

= 1.3.0 =
* Upgraded plugin for new Wordpress Plugin support model.

= 1.2.18 =
* Wow, embarrassing, I am still learning the proper use of svn

= 1.2.17 =
* Undo, last change, fixup order in readme.txt, trying to get new version to showup on wordpress plugin site

= 1.2.16 =
* PanelClass.php was not in the release, fixed.

= 1.2.15 =
* Still struggling to create a proper stable version w/o fprintf

= 1.2.14 =
* Bug Fix release:  syntax error in PanelClass.php

= 1.2.13 =
* No release,  superstitious

= 1.2.12 =
* Bug Fix release:  fixed problem with no fprintf() function in php4.  replaced with fwrite()

= 1.2.11 =
* Not released, working on new features, need more testing before releasing

= 1.2.10 = 
* Another oops! (Teaches me to do updates just before dinner!) Didn't handle older thumbsize option values properly

= 1.2.9 =
* Ooops! forgot to include the new OptionsClass.php file!

= 1.2.8 =
* Properly fixed problem with creation of cache directory.  Now relative to wordpress installation, should more gracefully handle not being able to create or write to the directory.
* Removed imagename caption, in Lightbox display (now shows untitled)
* Created a new thumbnail size L (179x119), the choices are Tiny, Small, Large
* Cleaned up the original code to NOT create multiple wp-phanfare options in wordpress DB.  Instead create a single multi-value option record.  This makes the code cleaner, removes database clutter, allows for easier expansion in the future.

= 1.2.7 =
* Fixed a problem reporting cache directory creation errors (on the actual display page)
* Noticed that the RSS feed was being sorted (by date of each entry "image"). Removed any sorting, so images will appear in "album" order.
* The Phanfare RSS feed seems to use the image filename "IMG_1234.jpg" when there is no other caption.  This clutters the display, so for now, when captions are desired, the plugin only displays non-image-filename captions.  (Meaning: If the caption matches "IMG_n+.jpg" or just ends with ".jpg" it is assumed to be a filename, and not displayed as a caption).
* Thanks to Jeff for letting me know about these problems
* I plan to roll out a version (in the future) with an updated Settings panel to allow more control of cache location, sorting of RSS feeds, and display of Image filenames in captions.

= 1.2.6 =
* Fixed reported bug with wp-phanfare_cleanup line 268 (Thank you Jeff.)

= 1.2.5 =
* Fixed footer "View album at Phanfare" to be correct url!
* Cleaned up cache duration field validation.

= 1.2.0 =
* Added Checking for WP version & requisite plugins (SimplePie & jQuery Lightbox) at Activation with proper error message
* Updated screenshot for Admin Settings Panel (shows Cache Duration)

= 1.1.0 =
* Get the screen shots working (hopefully)

= 1.0.2 =
 - Added in notice of requirement for SimplePie Core, and jQuery Lightbox

= 1.0.1 =
 - Added in the screenshots

= 1.0.0 =
 - first release

== Upgrade Notice ==

= 1.3.0 =
* Have verified that WP-Phanfare2012 works with new Wordpress Plugin support model.




