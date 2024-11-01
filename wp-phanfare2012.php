<?php
/*
Plugin Name: WP-Phanfare
Plugin URI: http://TheMeyers.org/Projects/wordpress/wp-phanfare/
Description: Integrate your Phanfare galleries into your WordPress blog.
Author: Craig Meyer 
Version: 1.3.0
Author URI: http://TheMeyers.org/geek/
Code based on Adam Tow's wp-smugmug plugin from http://tow.com/ V2.0.3
*/

global $wpPhanfare_ok;
@define('WP_PHANFARE_DEBUG', true);     // Turn this on for logging to wp-content/plugins/wp-phanfare/error_log
@define('WP_PHANFARE_DEBUG_DIR', 'WP_PLUGIN_DIR/wp-phanfare');  // Debug file location
@define('WP_PHANFARE_QUERY_VAR', 'wpff');
@define('WP_PHANFARE_ACTION_QUERY_VAR', 'wpff-action');
@define('WP_PHANFARE_FILEPATH', '/wp-phanfare/wp-phanfare.php');
@define('WP_PHANFARE_COMMENT', "\n\n<!-- WP-Phanfare Plugin: http://TheMeyers.org/Projects/wordpress/ -->\n\n");
@define('WP_PHANFARE_VERSION', '1.3.0');
@define('WP_PHANFARE_CSS', 'div.wp-phanfare {
	clear:both;
	margin: 0 0 1em 0;
	padding: 0;
	width:100%;
}
div#wp-phanfare {
	margin: 5px 0 0 0;
	padding: 5px 0 0 0;
}
/* Centering CSS (http://www.brunildo.org/test/img_center.html) */

ul.thumbwrap {
	padding: 10px 0px 0 0px;
	margin: 0;
	text-indent: 0;
	text-align: center;
	width: 100%;
}
ul.thumbwrap li {
	display: -moz-inline-box;
	display: inline-block;
	/*\*/ vertical-align: top; /**/
	margin: 0 3px 15px 3px;
	padding: 0;
	
}
ul.thumbwrap li:before {
	content: "";
}
ul.thumbwrap li>div {
	/*\*/ display: table; /**/
	width: 157px;
}
ul.thumbwrap a {
	display: block;
	text-decoration: none;
	cursor: pointer;
}
/*\*/
ul.thumbwrap>li .wrimg {
	display: table-cell;
	vertical-align: middle;
	width: 157px;
	height: 157px;
}
/**/
ul.thumbwrap li .wrimg {

}
ul.thumbwrap img {
	vertical-align: middle;
	margin: 0;
	float: none;
	padding: 2px;
	background: #fff;
	border: 1px solid #ccc;
}
ul.thumbwrap a:hover img {
	background-color: #007fff;
	border: 1px solid #007fff
}
ul.thumbwrap a:hover {
	
}
/*\*//*/
* html ul.thumbwrap li .wrimg {
	display: block;
	font-size: 1px;
}
* html ul.thumbwrap .wrimg span {
	display: inline-block;
	vertical-align: middle;
	height: 157px;
	width: 1px;
}
/**/
ul.thumbwrap .caption {
	display: block;
	padding: .3em 5px;
	font-size: .9em;
	line-height: 1.1;
	w\idth: 141px;  /* Moz, IE6 */
}
/* top ib e hover Op < 9.5 */
@media all and (min-width: 0px) {
	html:first-child ul.thumbwrap a {
		display: inline-block;
		vertical-align: top;
	}
	html:first-child ul.thumbwrap {
		border-collapse: collapse;
		display: inline-block;
	}
}');

@define('WP_PHANFARE_CSS_IE', 'ul.thumbwrap li {
	width: 163px;
	w\idth: 161px;
	display: inline;
}
ul.thumbwrap {
	_height: 0;
	zoom: 1;
	display: inline;
}
ul.thumbwrap li .wrimg {
	display: block;
	width: auto;
	height: auto;
}
ul.thumbwrap .wrimg span {
	vertical-align: middle;
	height: 161px;
	zoom: 1;
}');

if ( !empty($_REQUEST[WP_PHANFARE_ACTION_QUERY_VAR]) ) {
	switch ( $_REQUEST[WP_PHANFARE_ACTION_QUERY_VAR] ) {
		case 'css':
			header("Content-type: text/css");
			echo WP_PHANFARE_CSS;
			die();
			break;
		case 'css_ie':
			echo WP_PHANFARE_CSS_IE;
			die();
			break;
		default:
			die();
			break;
	}
}


if ( !class_exists('OptionsClass') ) {
	require_once('OptionsClass.php');
}

function wpPhanfare_debug ($msg){
	static $log_file = 'not set';

	if( WP_PHANFARE_DEBUG ){
		$old_level = error_reporting(E_ERROR);  // Hide any Warnings from mkdir
		if( $log_file == 'not set' ){
		    clearstatcache();
		    $log_dir = WP_PHANFARE_DEBUG_DIR;
		    $log_dir = str_replace('WP_PLUGIN_DIR', WP_PLUGIN_DIR, $log_dir);
		    if( is_writable($log_dir) ){
		        $log_file = "$log_dir/debug_log";
		    } else {
			echo "LogDir [$log_file] not writable<br/>]\n";
		        if( is_writable('/tmp') ){
			    $log_file = '/tmp/debug_log';
		        } else {
			    echo "LogDir /tmp not writable<br/>]\n";
			    $log_file = '';
		        }
		    }
		}


		if( $log_file != '' ){
		    $stderr = fopen($log_file, "a");
	            if( $stderr ){
			fwrite($stderr, date(DATE_ATOM) . ' ' . $msg . "\n");
			fclose($stderr);
		    }
		} else {
		    echo "<pre>$msg</pre><br/>\n";
		}
	        error_reporting($old_level);
	}
} // wpPhanfare_debug


function wpPhanfare_display_plugin_error ($error) {

	$error = '<div class="error"><p>WP-Phanfare: ' . $error . '</p></div>';
	add_action('admin_notices', create_function('', "echo '$error';"), 0 );
} // wpPhanfare_display_plugin_error


function wpPhanfare_activate () {
        global $wp_version;

	// Verify we are in activation
	if ( ! (isset($_GET['activate']) && $_GET['activate'] == 'true') ) {
		return;
	}

	wpPhanfare_debug("Inside activate");
	$error = '';       
	if (isset($wp_version) && version_compare($wp_version, '2.6', '<')) {
		$error = 'WP-Phanfare depends on the WordPress version >= 2.6, please upgrade.<br>';
        }
	if ( ! class_exists('SimplePie') ) {
		$error .= 'WP-Phanfare relies on the <a href="http://wordpress.org/extend/plugins/simplepie-core">SimplePie Core</a> plugin to enable important functionality. Please download, install, and activate it<br>';
	}
	if ( ! class_exists('jQueryLightbox') ) {
		$error .= 'WP-Phanfare relies on the <a href="http://wordpress.org/extend/plugins/jquery-lightbox-balupton-edition/">jQuery LightBox</a> plugin to enable important functionality. Please download, install, and activate it<br>';
	}

	if( $error != '' ){
		$wpPhanfare_ok = false;
		wpPhanfare_display_plugin_error($error .
'WP-Phanfare is incorrectly installed. Please check the <a href="http://wordpress.org/extend/plugins/wp-phanfare/installation/">Installation notes</a>' );
//		deactivate_plugins(plugin_basename(__FILE__));
	}
} // wpPhanfare_activate

if( is_admin() ){
	add_action('plugins_loaded', 'wpPhanfare_activate' );
}

$wpPhanfare_ok = true;

if( $wpPhanfare_ok && !class_exists('wpPhanfare') ){
  class wpPhanfare {
	var $phanfare_domain = 'TheMeyers.org';
	var $wp_phanfare_is_setup = 0;
	var $settings;
	var $options;
	var $phanfare_root = 'http://albums.phanfare.com/';
									
	function wpPhanfare() {
		# Description:	Constructor for wpPhanfare class. Adds all the necessary
		#				hooks and filters into WordPress.
		#
		# History:	2008-12-02 - Initial version (adam)
		#
		
		if ( $this->wp_phanfare_is_setup ) return;

		$this->options = new OptionsClass( 'wp_phanfare',
			array(
			 'url' => ''
			,'title' => ''
			,'description' => ''
			,'thumbsize' => 'R'		// Default thumbnail size
			,'size' => 'M'			// Default image size
			,'imagecount' => 100		// Default retrieves 100 images (now ignored)
			,'start' => 1			// The starting index of the image to display
			,'num' => 100			// The number of images to display
			,'link' => 'phanfare'		// Default link goes to Phanfare album page
			,'titletag' => 'h4'
			,'captions' => 'true'		// Display Captions
			,'sort' => 'true'		// Sort images by EXIF date
			,'phanfare' => 'false'		// Display a link to the phanfare album
			,'window' => 'false'		// Open links in a new window
			,'installed' => 'true'
			,'css' => ''
			,'css_ie' => ''
			,'cache' => 3600
			,'cacheloc' => 'WP_PLUGIN_DIR/wp-phanfare/cache'
			,'stripimg' => 'true'
			,'footer_text' => 'View album at Phanfare'
		) );

		load_plugin_textdomain($this->wp_phanfare_domain,
					PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)),
					dirname(plugin_basename(__FILE__)));

	
		
		add_action('wp_head', array($this, 'wp_phanfare_wp_head'));

		add_action('admin_menu', array($this, 'wp_phanfare_admin_menu'));
		add_action('template_redirect', array($this, 'wp_phanfare_template_redirect'));
		add_action('init', array($this, 'wp_phanfare_flush_rewrite_rules'));
		add_action('admin_menu', array($this, 'wp_phanfare_admin_panel'));
		
		add_filter('post_rewrite_rules', array($this, 'wp_phanfare_post_rewrite_rules'), 0);
		add_filter('page_rewrite_rules', array($this, 'wp_phanfare_page_rewrite_rules'), 0);
		add_filter('the_content', array($this, 'wp_phanfare_the_content'));

		add_filter('query_vars', array($this, 'wp_phanfare_query_vars'));
		add_filter('admin_print_scripts', array($this, 'wp_phanfare_admin_print_scripts'));
		
		add_shortcode('phanfare', array($this, 'wp_phanfare_shortcode'));

		register_deactivation_hook(__FILE__, array($this, 'wp_phanfare_cleanup'));
		register_activation_hook(__FILE__, array($this, 'wp_phanfare_startup'));

		$this->wp_phanfare_is_setup = true;
	} // wpPhanfare


	function wp_phanfare_startup(){
		wpPhanfare_debug("Phanfare being Activated");
		wpPhanfare_debug("CWD: " . getcwd() . " FILE: " . __FILE__);

		$tmp = 'WP_PLUGIN_DIR/wp-phanfare/cache';
		wpPhanfare_debug("Setting cacheloc: " . $tmp);
		$this->options->set('cacheloc', $tmp);
	
		wpPhanfare_debug("Defaults: " . print_r($this->options, true) );
		wpPhanfare_debug("Cache Location: " . $this->validate_cache_loc() );

		$this->wp_phanfare_load_settings();

	} // wp_phanfare_startup()

	function wp_phanfare_cleanup(){
		wpPhanfare_debug("Doing cleanup");
		$keys = $this->options->get_names();
		array_push($keys, 'version', 'lightbox', 'slideshow', 'phanfare', 'css', 'css_ie');  // add these just in case
		foreach($keys as $k ){
			wpPhanfare_debug("Delete option $k");
			delete_option('wp_phanfare_' . $k);
		}
		delete_option('wp_phanfare');  // remove this serialized array also
	} // wp_phanfare_cleanup()

	function wp_phanfare_load_settings() {
		# Description:	Load the default settings to display in the WP-Phanfare editing pane.
		#		Also updates any new settings if a newer version of the plugin is
		#		installed.
		#
		# History:		2008-12-13 - Initial version (adam)
		#
		
		// Add the default settings if this is the first time WP-Phanfare is being loaded
		
		$version = $this->options->get('version');
		wpPhanfare_debug("Version in DB: [$version] vs " . WP_PHANFARE_VERSION );
		wpPhanfare_debug("Installed: " . $this->options->get('installed'));

		if ( ! $this->options->get('installed') || version_compare($version, WP_PHANFARE_VERSION, '<') ) {
			$this->wp_phanfare_cleanup();  // Clean up old options
			$this->options->set('version', WP_PHANFARE_VERSION);
		} else {
			wpPhanfare_debug("No cleanup needed");
		}
		
	} // wp_phanfare_load_settings

	function wp_phanfare_wp_head() {
		# Description:	Adds stylesheet link references and appends any custom CSS the user has added in the admin panel.
		#				We use a conditional comment to add all IE-specific styles.
		#
		# History:		2008-12-13 - Initial version (adam)
		#
		
		$wp = get_bloginfo('wpurl');
		$url = $wp . '/' . PLUGINDIR . '/' . plugin_basename(__FILE__);
		$query_var = WP_PHANFARE_ACTION_QUERY_VAR;
		$css    = $this->options->get('css');
		$css_ie = $this->options->get('css_ie');
		
		echo <<<EOF
<link rel="stylesheet" type="text/css" href="{$url}?{$query_var}=css" />

EOF;
		if ( !empty($css) ) {
			echo <<<EOF
<style type="text/css">
{$css}
</style>

EOF;
		}
		
		echo <<<EOF
<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="{$url}?{$query_var}=css_ie" />

EOF;
		
		if ( !empty($css_ie) ) {
			echo <<<EOF
<style type="text/css">
{$css_ie}
</style>

EOF;
		}
		echo "<![endif]-->\n";
	}

/**
 * Simple Debug Function
 *
 * @param mixed $var
 * @param boolean $visible
 * @param boolean $return
 * @return mixed
 */
function varDebug(&$var, $visible = true, $return = false)
{
        $containers = array(
                // $visible == false
                0 => array(
                        'head' => "\n<!--\n\nDEBUG:\n---------------------------------------\n\n",
                        'foot' => "\n\n---------------------------------------\n\n-->\n"
                ),
                
                // $visible == true
                1 => array(
                        'head' => '<hr /><h1>Debug</h1><p>',
                        'foot' => '</p><hr />'
                )
        );
        
        $r = var_export($var, true);
        if ($visible) $r = str_replace(array(' ', "\n"), array(' ', "<br />\n"), $r);
        
        $container = intval($visible);
        $r = $containers[$container]['head'] . $r . $containers[$container]['foot'];
        
	error_log($r,0);
//        if ($return) return $r;
//        echo $r;
} // varDebug

    function validate_cache_val () {
	$val = $this->options->get('cache');
	if( $val <= 0 ){
	    $val = 0;
	    update_option('wp_phanfare_cache', $val);
	}
	return $val;	
    } // validate_cache_val

    function recursive_mkdir($path, $mode = 0777) {
        $dirs = explode(DIRECTORY_SEPARATOR , $path);
	$count = count($dirs);
	$path = '.';
	for ($i = 0; $i < $count; ++$i) {
	   $path .= DIRECTORY_SEPARATOR . $dirs[$i];
	   if (!is_dir($path) && !mkdir($path, $mode)) {
	        return false;
	   }
	}
	return true;
    } // recursive_mkdir

    function recursive_check_priv ($path, $verbose){
        $dirs = explode(DIRECTORY_SEPARATOR , $path);
	$nelem = count($dirs);
	$path = '.';
        foreach($dirs as $dir ){
	    $path .= DIRECTORY_SEPARATOR . $dir;
	    if( !is_dir($path) || !is_writable($path) ){
                wpPhanfare_debug("Dir: $path, is not writable");
                return( false );
	    }
        }
	return( true );
    } // recursive_check_priv

    function validate_cache_loc () {
	$cache_dir = $this->options->get('cacheloc');
	$cache_dir = str_replace('WP_PLUGIN_DIR', WP_PLUGIN_DIR, $cache_dir);
	wpPhanfare_debug("CWD: " . getcwd() . " Cache dir: $cache_dir");
	clearstatcache();
	if( ! is_dir( $cache_dir ) ){
	    $parent_dir = join(DIRECTORY_SEPARATOR,
                               array_slice(explode(DIRECTORY_SEPARATOR, $cache_dir), 0, -1));
            if( ! is_writable($parent_dir) ){
               wpPhanfare_debug("Can't create Cache dir [$cache_dir]");
               wpPhanfare_debug("Parent directory [$parent_dir] not writable");
	       wpPhanfare_debug("Run cmd: chmod ugo+w $parent_dir");
	       $cache_dir = '';
            } else if( ! mkdir($cache_dir) ){
	       // Warn can't create cache dir
	       wpPhanfare_debug("Warning: Did not create cache dir: [$cache_dir]");
	       $cache_dir = '';
	    }
        }
	clearstatcache();
	if( $cache_dir != '' && is_dir($cache_dir) ){
            if(  ! is_writable($cache_dir) ){
		if( chmod($cache_dir, 0775) ){
		    $cache_dir = '';
		}
	    }
	}

	return( $cache_dir );
    } // validate_cache_loc


    function check_image_caption ($title, $flag){
	if( $flag &&  preg_match("/IMG_[0-9]+.jpg$/i", $title) ||
		preg_match("/.jpg$/i", $title) ){
		$title = '';
	}
	return($title);
    } // check_image_caption

    function wp_phanfare_shortcode($atts, $content = null) {
	# Description:	Processes the shortcode text for WP-Phanfare
	#
	# Parameters:	atts - array of attribute names/value pairs
	#				content - the text between the shortcode tags (optional)
	#
	# History:	2008-12-02 - Initial version (adam)
	#		2008-12-31 - html_entity_decode fix for PHP4. Added #wp_phanfare html fragment to paged galleries
	#
	#
		
	global $post;
		
	// Allow plugins/themes to override the default gallery template.
			
	$output = apply_filters('wp-phanfare', '', $attr);
	if ( $output != '' ) return $output;
		
	$settings = $this->options->get_option_array();
//	wpPhanfare_debug("shortcode, settings: ");
//	wpPhanfare_debug(print_r($settings, true) );

	$attrs = shortcode_atts($settings, $atts);

	array_walk($attrs, array($this, 'fixTrueFalseAndEncoding'));

	wpPhanfare_debug('Just before extract: ' . print_r($attrs, true) );

	extract($attrs);  // populate local symbol table

	if( $thumbsize == 'Ti' ){
		$thumbsize = 'T';
	} else if( $thumbsize == 'Th' ){
		$thumbsize = 'R';
	} else if( $thumbsize == '' ){
		$thumbsize = 'R';
	}
	if( $size == '' ){
		$size = 'M';
	}

	wpPhanfare_debug("before scan: [$thumbsize] [$size]");
	if ( !empty($url) ) {
			
/*
			if( SIMPLEPIE_BUILD < 20081219 ){
				echo 'Seems that SimplePie-Core's (version of simplepie.inc) is out of date. ' .
					'The Version should be newer than 1.3.1,' .
					'and the current version is ' . SIMPLEPIE_VERSION . '?';
				return '';
			}
*/
			if ( !empty($num) && (int) $num < 1 )
				$num = 100;

			if ( $window )
				$target = ' target="wp-phanfare-' . $post->ID . '"';

				// HTML Entity Decode that's PHP4 and PHP5 compatible
				
			$url = $this->decode_entities($url);
			$feed = new SimplePie();
			$feed->enable_cache(false); // Assume no cache at first
			$val = $this->validate_cache_val();
			if( $val > 0 ){
			    $cache_dir = $this->validate_cache_loc();
			    if( $cache_dir != '' ){
				$feed->set_cache_location($cache_dir);
				$feed->set_cache_duration($val); // In Seconds
				$feed->enable_cache(true); // In Seconds
			    }
			}
			$feed->enable_order_by_date(false);
			$feed->set_feed_url($url);
			$feed->init();

			// If the RSS feed is empty for whatever reason, display a link to the gallery.

			preg_match("/\/([0-9]+)_([0-9]+)_([0-9]+)_[a-fA-F0-9]+/", $url, $url_matches);
			$user_id  = $url_matches[1];
			$album_id = $url_matches[3];
//			echo 'User[' . $user_id . '] Album[' . $album_id . ']';
			$phanfare_album_url = $this->phanfare_root . "$user_id/$album_id";

			if ( empty($feed) || $feed->error() || count($feed->get_item_quantity()) == 0 ) {

				$output .= 'Error: ' . $feed->error();
							
				$output .= '<p><a href="' .
					$phanfare_album_url . '"' . $target . '>' .
					 __('View album at Phanfare', $this->wp_phanfare_domain) .
					'</a></p>';
				
			} else {
					
				$start = intval($start);
				
				if ( $start < 1 )
					$start = 1;
					
				$start--;
				$items = $feed->get_items($start, $num);
					
					// Generate the HTML code
	
				$output = apply_filters('wp_phanfare_style', WP_PHANFARE_COMMENT . "<div class='wp-phanfare'>\n\n");
				
				if ( !empty($title) )
					$output .= '<center><' . $this->options->get('titletag') . '>' . wptexturize($title) . '</' . $this->options->get('titletag') . '></center>' . "\n\n";
				
				if ( !empty($description) )
					$output .= '<center>' . wpautop(wptexturize($description)) . "</center>\n";

				$output .= '<ul class="thumbwrap">';
				
				foreach($items as $item) {
	
					$output .= '<li><div>';
					
					// Retrieve the URLs for the image and thumbnail images
					
					
					$image_url = $thumb_url = '';
					$t_width = $t_height = '';
					foreach ($item->get_enclosures() as $enclosure){
						$tmp_url = $enclosure->get_link();
//	wpPhanfare_debug("Enclosure:[$tmp_url]");
						if( $thumbsize == 'R' && strpos($tmp_url, '_Thumbnail_') ){
							$thumb_url = $tmp_url;

						} else if( $thumbsize == 'T' && strpos($tmp_url, '_ThumbnailSmall_') ){
							$thumb_url = $tmp_url;

						} else if( $thumbsize == 'L' && strpos($tmp_url, '_WebSmall_') ){
							$thumb_url = $tmp_url;
							$t_width = 'width="179"';
							$t_height = 'height="119"';
						}

						if( $size == 'S' && strpos($tmp_url, '_WebSmall_') ){
							$image_url = $tmp_url;
						} else if( $size == 'M' && strpos($tmp_url, '_Web_') ){
							$image_url = $tmp_url;
						} else if( $size == 'L' && strpos($tmp_url, '_WebLarge_') ){
							$image_url = $tmp_url;
						}
					}

//					wpPhanfare_debug("After image scan thumb [$thumbsize] [$thumb_url] [$t_width] [$t_height]");
//			wpPhanfare_debug("Image: [$size][$link] [$image_url]");
					$verified_item_caption = $this->check_image_caption($item->get_title(), $stripimg);
					if( $verified_item_caption == '' ){
						$clean_image_caption = '';
						$title_tag = $alt_tag   = '';
						// $title_tag = 'title="&nbsp;&nbsp;&nbsp;"';
                                                // $alt_tag = 'alt="&nbsp;&nbsp;&nbsp;"';
					} else {
						$clean_image_caption = htmlspecialchars(strip_tags($verified_item_caption));
						$title_tag = "title=\"$clean_image_caption\"";
						$alt_tag = "alt=\"$clean_image_caption\"";
					}
					
//					wpPhanfare_debug("[$verified_item_caption] [$clean_image_caption]<br/>");
	
					$rel = '';
					
					// Link variable determines what happens when the user clicks on an image link
					
					switch ( $link ) {
	
						case 'lightbox':
							$the_link = $image_url;
							$rel = ' rel="lightbox[wp-phanfare-' . $post->ID . ']"';
							break;

						case 'wordpress':
						
							// Get the image and album IDs
					
							preg_match("/\/photos\/(.*?)-(.*?)\.jpg/", $item->get_id(), $matches);
							$image_id = $matches[1];
							
							preg_match("/.*?Data=(.*?)&.*/", $url, $rss_matches);
							$album_id = $rss_matches[1];
							
							$the_link = trailingslashit(get_permalink()) . WP_PHANFARE_QUERY_VAR . '/' . $album_id . '-' . $image_id . '-' . $size . '/#wp-phanfare';
							break;
							
						case 'slideshow':
							// show a Thumbnail, click starts the slideshow on Phanfare
							$the_link = $this->phanfare_root . 'slideshow.aspx?s=1&u=' .
								$user_id . '&a_id=' . $album_id;
							break;

						case 'image':
						default:
							$the_link = $image_url;
							break;
					}
					
					$anchor_tag = '<a href="' . $the_link . '" ' . $title_tag . $rel . $target . '>';
					
					$output .= $anchor_tag;
					$output .= '<span class="wrimg"><span></span>';
					$output .= join(' ', array("<img src=\"$thumb_url\"", $t_width,
                                                                  $t_height, $alt_tag, '/>'));
					$output .= '</span>';
										
					// Captions
					
					if ( $captions && $verified_item_caption != '' ){
						$output .= '<span class="caption">';
						$output .= wptexturize($verified_item_caption);
						$output .= '</span>';
					}
					$output .= '</a>';
					$output .= '</div></li>';
				}
				$output .= '</ul>';
				
// CRAIG NOTE *** Fix this for customized footer text
				if ( $phanfare )
					$output .= '<p style="text-align: center;"><a href="' .
						$phanfare_album_url . '"' . $target . '>' .
						__($this->options->get('footer_text'), $this->wp_phanfare_domain) .
						'</a></p>';
			
				$output .= '<div style="clear: both;"></div>';
				$output .= '</div>';
				$output .= '<div style="clear: both;"></div>';
			}
		}
			
		return $output;
	}
	
	function wp_phanfare_the_content($content = ''){

	 # Description:	Displays a paged gallery for advertisers. If the wpimage variable is
	 #				set in the URL string, we don't display the normal content and display
	 #				our paged gallery page instead
	 #
	 # Parameters:	content - the original content
	 #
	 #
	 # History:	2008-12-04 - Initial version (adam)
	 #		2008-12-19 - Backward compatibility with previous version of plugin
	 #		2008-12-31 - Added missing </a> tags to next/prev links. Added #wp-phanfare class id
	 #
	 #

		global $post;
		global $wp;
		global $more;
		global $wp_query;
		
		$query_options = array();
		$feed_url = get_post_meta($post->ID, 'wp-phanfare', true);
		
		$wpimage = $wp_query->query_vars[WP_PHANFARE_QUERY_VAR];
		
		$output = '';
		
		if ( !empty($wpimage) ) {
// CRAIG modified here
			// phanfare parts user_album_image_size
			$parts = explode('_', $wpimage);
			$user_id  = $parts[0];
			$album_id = $parts[1];
			$image_id = $parts[2];
			$size     = $parts[3];
			
				// Create the feed URL given the album ID
			
			$url = "http://www.phanfare.com/hack/feed.mg?Type=gallery&Data={$album_id}&format=rss200";
			
			if ( ! class_exists('SimplePie') ) {
				echo 'This plugin relies on the <a href="http://wordpress.org/extend/plugins/simplepie-core">SimplePie Core</a> plugin to enable important functionality. Please download, install, and activate it, or upgrade the plugin if you\'re not using the latest version.';
			}

				
	
			$url = html_entity_decode($url);
			$feed = new SimplePie($url);
/*
			$feed = new SimplePie();
			$feed->set_cache_duration(10 * 60);  // In seconds
			$feed->set_feed_url($url);
			$feed->enable_order_by_date(false);
			$feed->init();
*/
			if ( $feed ) {
				$count = $feed->get_item_quantity();
				
				for ( $i = 0; $i < $count; $i++ ) {
					$prev_item = false;
					$next_item = false;
					$found = false;
			
					$item = $feed->get_item($i);
					
					if ( strpos($item->get_id(), $image_id) ) {
					
						if ( $i == 0 ) {
							$prev_item = false;
						} else {
							$prev_item = $feed->get_item($i - 1);
							$parts = parse_url($prev_item->get_id());
							preg_match("/\/images\/(.*?)-(.*?)\.jpg/", $prev_item->get_id(), $matches);
							$prev_item['image_id'] = $matches[1];
						}
				
						if ( $i == $count - 1 ) {
							$next_item = false;
						} else {
							$next_item = $feed->get_item($i + 1);
							$parts = parse_url($next_item['guid']);
							preg_match("/\/images\/(.*?)-(.*?)\.jpg/", $next_item['guid'], $matches);
							$next_item['image_id'] = $matches[1];
						}
						
						$found = true;
						break;
					}
				}
				
				$verified_item_caption = $this->check_image_caption($item->get_title(), $stripimg);
				$clean_item_caption = strip_tags($verified_item_caption);

				if ( $found ) {
					
					$output .= '<div id="wp-phanfare">';
					if( $verified_item_caption != '' ){
						$alt_clean = 'alt="' . $clean_item_caption . '"';
						$cap_html = join('', '<span class="caption">',
                                                                 $verified_item_caption, '</span>');
					} else {
						$alt_clean = '';
						$cap_html  = '';
					}
					$output .= '<p style="text-align: center;"><a href="' . $item->get_link() . '"><img src="http://www.phanfare.com/images/' . $image_id . '-' . $size . '.jpg" ' . $alt_clean . '" class="aligncenter" /></a><br />' . $cap_html. '</p>';
					
					$output .= '<br style="clear: both;" /><p>';
					
					if ( $prev_item ) {
						$output .= '<a href="' . trailingslashit(get_permalink()) . WP_PHANFARE_QUERY_VAR . '/' . $album_id . '-' . $prev_item['image_id'] . '-' . $size . '/#wp-phanfare" title="' . $clean_item_caption . '">' . '<img src="' . $prev_item['guid'] . '" alt="' . strip_tags($prev_item['title']) . '" class="alignleft" /></a>';
					}
					
					if ( $next_item ) {
						$output .= '<a href="' . trailingslashit(get_permalink()) . WP_PHANFARE_QUERY_VAR . '/' . $album_id . '-' . $next_item['image_id'] . '-' . $size . '/#wp-phanfare" title="' . $clean_item_caption . '">' . '<img src="' . $next_item['guid'] . '" alt="' . strip_tags($next_item['title']) . '" class="alignright" /></a>';
					}
					
					$output .= '</p><p><br style="clear: both;" /></p></div>';
				}
			}
	
			return $output;
			
		} else if ( !empty($feed_url) && $more == 1) {
		
			// This handles the legacy WP-Phanfare plugin
		
			$query = get_post_meta($post->ID, 'wp-phanfare-options', true);
			parse_str($query, $query_options);
		
			$atts = $this->defaults;
			$atts['url'] = $feed_url;
			
			if ( $query_options['sort'] == 0 ) 
				$atts['sort'] == 'false';
				
			if ( !empty($query_options['title']) )
				$atts['title'] == urldecode($query_options['title']);
			
			if ( !empty($query_options['limit']) ) 
				$atts['imagecount'] = $query_options['limit'];
			
			$output = $this->wp_phanfare_shortcode($atts);
			$content .= $output;
		
		}
		
		return $content;
		
	} // wp_phanfare_the_content

	function wp_phanfare_admin_menu(){
		# Description:	Adds the WP-Phanfare forms to the Post/Page editing screens
		#
		# History:		2008-12-02 - Initial version (adam)
		#
		#
		
		add_meta_box('wp_phanfare', 'WP-Phanfare', array($this, 'wp_phanfare_insert_form'), 'post', 'normal');
		add_meta_box('wp_phanfare', 'WP-Phanfare', array($this, 'wp_phanfare_insert_form'), 'page', 'normal');
	}

	function wp_phanfare_insert_form(){
		# Description:	Inserts the WP-Phanfare form in the Post/Page edit screen
		#
		# History:		2008-12-02 - Initial version (adam)
		#
		#
	?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_url"><?php _e('Phanfare RSS URL (<I>required</I>):', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
					<input type="text" size="40" style="width: 95%;" name="wpPhanfare[url]" id="wpPhanfare_url" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_title"><?php _e('Title:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
					<input type="text" size="40" style="width: 95%;" name="wpPhanfare[title]" id="wpPhanfare_title" value="<?php echo $this->options->get('title'); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_description"><?php _e('Description:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
					<input type="text" size="40" style="width: 95%;" name="wpPhanfare[description]" id="wpPhanfare_description" value="<?php echo $this->options->get('description'); ?>" />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_start"><?php _e('Start with image #:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
					<input type="text" size="5" name="wpPhanfare[start]" value="<?php echo $this->options->get('start'); ?>" id="wpPhanfare_start" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_num"><?php _e('Number of images to display:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
					<input type="text" size="5" name="wpPhanfare[num]" value="<?php echo $this->options->get('num'); ?>" id="wpPhanfare_num" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_thumbsize"><?php _e('Thumbnail size:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
					<input type="radio" name="wpPhanfare[thumbsize]" value="R" id="wpPhanfare_thumbsize_regular"<?php if ( $this->options->get('thumbsize') == 'R' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_thumbsize_regular"><?php _e('Regular (119x79)', $this->wp_phanfare_domain); ?></label>
					<br />
					<input type="radio" name="wpPhanfare[thumbsize]" value="T" id="wpPhanfare_thumbsize_tiny"<?php if ( $this->options->get('thumbsize') == 'T' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_thumbsize_tiny"><?php _e('Tiny (45x30)', $this->wp_phanfare_domain); ?></label>
					<br />
					<input type="radio" name="wpPhanfare[thumbsize]" value="L" id="wpPhanfare_thumbsize_large"<?php if ( $this->options->get('thumbsize') == 'L' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_thumbsize_large"><?php _e('Large (179x119)', $this->wp_phanfare_domain); ?></label>
					<br />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_size"><?php _e('Image size:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
 					<select name="wpPhanfare[size]" id="wpPhanfare_size">
						<option value="S"<?php if ( $this->options->get('size') == 'S' ) echo ' selected="selected"'; ?>><?php _e('Small', $this->wp_phanfare_domain); ?></option>
						<option value="M"<?php if ( $this->options->get('size') == 'M' ) echo ' selected="selected"'; ?>><?php _e('Medium', $this->wp_phanfare_domain); ?></option>
						<option value="L"<?php if ( $this->options->get('size') == 'L' ) echo ' selected="selected"'; ?>><?php _e('Large', $this->wp_phanfare_domain); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpPhanfare_link"><?php _e('Clicking on Image, displays:', $this->wp_phanfare_domain) ?></label>
				</th>
				<td>
<?php /*
				<input type="radio" name="wpPhanfare[link]" value="wordpress" id="wpPhanfare_link_page"<?php if ( $this->options->get('link') == 'wordpress' ) echo ' checked="checked"'; ?> />
				<label for="wpPhanfare_link_page"><?php _e('Wordpress Gallery', $this->wp_phanfare_domain); ?></label>
				<br />
      */ ?>
				<input type="radio" name="wpPhanfare[link]" value="image" id="wpPhanfare_link_image"<?php if ( $this->options->get('link') == 'image' ) echo ' checked="checked"'; ?> />
				<label for="wpPhanfare_link_image"><?php _e('Image', $this->wp_phanfare_domain); ?></label>
				<br />
				<input type="radio" name="wpPhanfare[link]" value="lightbox" id="wpPhanfare_link_lightbox"<?php if ( $this->options->get('link') == 'lightbox' ) echo ' checked="checked"'; ?> />
				<label for="wpPhanfare_link_lightbox"><a href="http://wordpress.org/extend/plugins/jquery-lightbox-balupton-edition/">Lightbox</a></label>
				<br />
				<input type="radio" name="wpPhanfare[link]" value="slideshow" id="wpPhanfare_link_slideshow"<?php if ( $this->options->get('link') == 'slideshow' ) echo ' checked="checked"'; ?> />
				<label for="wpPhanfare_link_slideshow"><?php _e('Phanfare Slideshow', $this->wp_phanfare_domain); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e('Options:', $this->wp_phanfare_domain) ?>
				</th>
				<td>
					<input type="hidden" name="wpPhanfare[captions]" id="wpPhanfare_captions_" value="false" />
					<input type="checkbox" name="wpPhanfare[captions]" id="wpPhanfare_captions" value="true"<?php if ( $this->options->get('captions') == 'true' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_captions"><?php _e('Display captions', $this->wp_phanfare_domain); ?></label>
					<br />

					<input type="hidden" name="wpPhanfare[stripimg]" id="wpPhanfare_stripimg_" value="false" />
					<input type="checkbox" name="wpPhanfare[stripimg]" id="wpPhanfare_stripimg" value="true"<?php if ( $this->options->get('stripimg') == 'true' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_stripimg"><?php _e('Strip captions of form "IMG_n+.jpg" and "*.jpg"', $this->wp_phanfare_domain); ?></label>
					<br />

<?php /*
					<input type="hidden" name="wpPhanfare[sort]" id="wpPhanfare_sort_" value="false" />
					<input type="checkbox" name="wpPhanfare[sort]" id="wpPhanfare_sort" value="true"<?php if ( $this->options->get('sort') == 'true' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_sort"><?php _e('Sort images by date', $this->wp_phanfare_domain); ?></label>
					<br />
      */ ?>
					<input type="hidden" name="wpPhanfare[window]" id="wpPhanfare_window_" value="false" />
					<input type="checkbox" name="wpPhanfare[window]" id="wpPhanfare_window" value="true"<?php if ( $this->options->get('window') == 'true' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_window"><?php _e('Open links in a new window', $this->wp_phanfare_domain); ?></label>
					<br />
					<input type="hidden" name="wpPhanfare[phanfare]" id="wpPhanfare_phanfare_" value="false" />
					<input type="checkbox" name="wpPhanfare[phanfare]" id="wpPhanfare_phanfare" value="true"<?php if ( $this->options->get('phanfare') == 'true' ) echo ' checked="checked"'; ?> />
					<label for="wpPhanfare_phanfare"><?php _e('Display Phanfare album link at bottom', $this->wp_phanfare_domain); ?></label>
					<br />
				</td>
			</tr>
			<tr>
				<th colspan="2" class="submit">
					<input type="button" onclick="return wpPhanfareAdmin.sendToEditor(this.form);" value="<?php _e('Send to Editor &raquo;', $this->wp_phanfare_domain) ?>" />
				</th>
			</tr>
		</table>
	<?php 
	}

	 
	function plugin_settings( $links, $file ){
	    static $this_plugin;
	    if ( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
	    if ( $file == $this_plugin ) $links = array_merge( array( '<a href="' . attribute_escape( 'options-general.php?page=wp-phanfare.php' ) . '">Settings</a>' ), $links );
	    return $links;
	}

	function wp_phanfare_admin_panel() {
	 # Description:	Registers the admin panel to WordPress
	 #
	 # History:		2006-12-13 - Initial version (atow)
	 #

	  if (function_exists('add_options_page')) {
	    add_filter( 'plugin_action_links', array($this, 'plugin_settings'), 10, 2 );
	    add_options_page('WP-Phanfare', 'WP-Phanfare', 'manage_options', basename(__FILE__), array($this, 'wp_phanfare_panel'));
	  }
        }
	 
	function wp_phanfare_panel() { 
		 # Description:	The administrative panel for the WP-Phanfare plugin
		 #
		 #
		 # History:		2006-12-13 - Initial version (atow)
		 #
	?>
		<div class=wrap>
			<form method="post" action="options.php">
				<?php wp_nonce_field('update-options');Ê?>
				<h2>WP-Phanfare</h2>
				<p>
				<?php _e('Integrate your <a href="http://www.phanfare.com/">Phanfare</a> galleries into your WordPress posts and pages.', $this->wp_phanfare_domain); ?>
				</p>
			
				<h3><?php _e('Default Settings', $this->wp_phanfare_domain); ?></h3>
				<p>
				<?php _e('Customize the default settings shown in the WP-Phanfare panel in the Write Post or Write Page SubPanel.'); ?>
				</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_title"><?php _e('Title:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="40" style="width: 95%;" name="wp_phanfare_title" value="<?php echo $this->options->get('title'); ?>" id="wp_phanfare_title" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_description"><?php _e('Description:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="40" style="width: 95%;" name="wp_phanfare_description" value="<?php echo $this->options->get('description'); ?>" id="wp_phanfare_description" />
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_start"><?php _e('Start with image #:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="5" name="wp_phanfare_start" value="<?php echo $this->options->get('start'); ?>" id="wp_phanfare_start" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_num"><?php _e('Number of images to display:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="5" name="wp_phanfare_num" value="<?php echo $this->options->get('num'); ?>" id="wp_phanfare_num" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_thumbsize"><?php _e('Thumbnail size:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="radio" name="wp_phanfare_thumbsize" value="R" id="wp_phanfare_thumbsize_regular"<?php if ( $this->options->get('thumbsize') == 'R' ) echo ' checked="checked"'; ?> />
							<label for="wpPhanfare_thumbsize_regular"><?php _e('Regular (119x79)', $this->wp_phanfare_domain); ?></label>
							<br />
							<input type="radio" name="wp_phanfare_thumbsize" value="T" id="wp_phanfare_thumbsize_tiny"<?php if ( $this->options->get('thumbsize') == 'T' ) echo ' checked="checked"'; ?> />
							<label for="wpPhanfare_thumbsize_tiny"><?php _e('Tiny (45x30)', $this->wp_phanfare_domain); ?></label>
							<br />
							<input type="radio" name="wp_phanfare_thumbsize" value="L" id="wp_phanfare_thumbsize_large"<?php if ( $this->options->get('thumbsize') == 'L' ) echo ' checked="checked"'; ?> />
							<label for="wpPhanfare_thumbsize_large"><?php _e('Large (179x119)', $this->wp_phanfare_domain); ?></label>
							<br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_size"><?php _e('Image size:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<select name="wp_phanfare_size" id="wp_phanfare_size">
								<option value="S"<?php if ( $this->options->get('size') == 'S' ) echo ' selected="selected"'; ?>><?php _e('Small', $this->wp_phanfare_domain); ?></option>
								<option value="M"<?php if ( $this->options->get('size') == 'M' ) echo ' selected="selected"'; ?>><?php _e('Medium', $this->wp_phanfare_domain); ?></option>
								<option value="L"<?php if ( $this->options->get('size') == 'L' ) echo ' selected="selected"'; ?>><?php _e('Large', $this->wp_phanfare_domain); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wpPhanfare_link"><?php _e('Clicking on Image displays:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
<?php /*
							<input type="radio" name="wp_phanfare_link" value="wordpress" id="wp_phanfare_wordpress_gallery"<?php if ( $this->options->get('link') == 'wordpress' ) echo ' checked="checked"'; ?> />
							<label for="wp_phanfare_wordpress_gallery"><?php _e('Wordpress Gallery', $this->wp_phanfare_domain); ?></label>
							<br />
      */ ?>
							<input type="radio" name="wp_phanfare_link" value="image" id="wp_phanfare_link_image"<?php if ( $this->options->get('link') == 'image' ) echo ' checked="checked"'; ?> />
							<label for="wp_phanfare_link_image"><?php _e('Image', $this->wp_phanfare_domain); ?></label>
							<br />
							<input type="radio" name="wp_phanfare_link" value="lightbox" id="wp_phanfare_link_lightbox"<?php if ( $this->options->get('link') == 'lightbox' ) echo ' checked="checked"'; ?> />
							<label for="wp_phanfare_link_lightbox"><a href="http://wordpress.org/extend/plugins/jquery-lightbox-balupton-edition/">Lightbox</a></label>
							<br />
							<input type="radio" name="wp_phanfare_link" value="slideshow" id="wp_phanfare_link_slideshow"<?php if ( $this->options->get('link') == 'slideshow' ) echo ' checked="checked"'; ?> />
							<label for="wp_phanfare_link_slideshow">Phanfare Slideshow</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e('Options:', $this->wp_phanfare_domain) ?>
						</th>
						<td>
						<input type="checkbox" name="wp_phanfare_captions" id="wp_phanfare_captions" value="true"<?php if ( $this->options->get('captions') == 'true' ) echo ' checked="checked"'; ?> />
						<label for="wpPhanfare_captions"><?php _e('Display captions', $this->wp_phanfare_domain); ?></label>
						<br />
						<input type="checkbox" name="wp_phanfare_stripimg" id="wp_phanfare_stripimg" value="true"<?php if ( $this->options->get('stripimg') == 'true' ) echo ' checked="checked"'; ?> />
						<label for="wpPhanfare_stripimg"><?php _e('Strip captions of form "IMG_n+.jpg" and "*.jpg"', $this->wp_phanfare_domain); ?></label>
						<br />
<?php /*
						<input type="checkbox" name="wp_phanfare_sort" id="wp_phanfare_sort" value="true"<?php if ( $this->options->get('sort') == 'true' ) echo ' checked="checked"'; ?> />
						<label for="wpPhanfare_sort"><?php _e('Sort images by date', $this->wp_phanfare_domain); ?></label>
						<br />
       */ ?>
						<input type="checkbox" name="wp_phanfare_window" id="wp_phanfare_window" value="true"<?php if ( $this->options->get('window') == 'true' ) echo ' checked="checked"'; ?> />
						<label for="wpPhanfare_window"><?php _e('Open links in a new window', $this->wp_phanfare_domain); ?></label>
						<br />
						<input type="checkbox" name="wp_phanfare_phanfare" id="wp_phanfare_phanfare" value="true"<?php if ( $this->options->get('phanfare') == 'true' ) echo ' checked="checked"'; ?> />
						<label for="wpPhanfare_phanfare"><?php _e('Display Phanfare album link at bottom', $this->wp_phanfare_domain); ?></label>
						<br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_cacheloc"><?php _e('RSS Feed Cache Location:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="60" name="wp_phanfare_cacheloc" value="<?php echo $this->options->get('cacheloc'); ?>" id="wp_phanfare_cacheloc" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_cache"><?php _e('SimplePie Cache Duration (0 is no cache):', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="5" name="wp_phanfare_cache" value="<?php echo $this->options->get('cache'); ?>" id="wp_phanfare_cache" />
						</td>
					</tr>
				</table>
				
				<h3><?php _e('Appearance'); ?></h3>
				<p>
				<?php _e('Customize the look and feel of your WP-Phanfare galleries with some custom CSS.'); ?>
				</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_titletag"><?php _e('XHTML tag used to<br />enclose the title:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<input type="text" size="2" name="wp_phanfare_titletag" value="<?php echo $this->options->get('titletag'); ?>" id="wp_phanfare_titletag" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_css"><?php _e('Custom CSS:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<textarea name="wp_phanfare_css" id="wp_phanfare_css" style="width: 95%;" rows="5" cols="50"><?php echo $this->options->get('css'); ?></textarea>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wp_phanfare_css_ie"><?php _e('Custom CSS for Internet Explorer 7 and below:', $this->wp_phanfare_domain) ?></label>
						</th>
						<td>
							<textarea name="wp_phanfare_css_ie" id="wp_phanfare_css_ie" style="width: 95%;" rows="5" cols="50"><?php echo $this->options->get('css_ie'); ?></textarea>
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="wp_phanfare_title,wp_phanfare_description,wp_phanfare_phanfare,wp_phanfare_window,wp_phanfare_sort,wp_phanfare_captions,wp_phanfare_link,wp_phanfare_size,wp_phanfare_thumbsize,wp_phanfare_num,wp_phanfare_start,wp_phanfare_css,wp_phanfare_titletag,wp_phanfare_css_ie,wp_phanfare_cache,wp_phanfare_cacheloc,wp_phanfare_stripimg" />
					<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes');Ê?>" />
				</p>
			</form>
		</div>
	<?php
	} // wp_phanfare_panel

	function wp_phanfare_admin_print_scripts()
	{
		# Description:	Adds the necessary scripts to the editing page head
		#
		# History:		2008-12-02 - Initial version (adam)
		#

		if( $GLOBALS['editing']) {
			$wp = get_bloginfo('wpurl');		
			wp_enqueue_script('wpPhanfareAdmin', $wp . '/' . PLUGINDIR . '/'  . dirname(plugin_basename(__FILE__)) . '/wp-phanfare.js', array('jquery'), '1.0.0');
		}
	}
	
	function wp_phanfare_template_redirect ()
	{
		# Description:	Handles redirecting to the gallery template. Looks for
		#				a page-wp-phanfare.php or single-wp-phanfare.php template.
		#				If not found, does nothing and uses the default WP templates.
		#
		# History:		2008-12-04 - Initial version (adam)
		#
		
		global $wp_query, $post;
		
		$wpff = $wp_query->query_vars[WP_PHANFARE_QUERY_VAR];
		
		if ( !empty($wpff) ) {
			if ( is_page() && file_exists(TEMPLATEPATH . '/page-wp-phanfare.php')) {
				include_once(TEMPLATEPATH . '/page-wp-phanfare.php');
				exit;			
			} else if ( is_single() && file_exists(TEMPLATEPATH . '/single-wp-phanfare.php')) {
				include_once(TEMPLATEPATH . '/single-wp-phanfare.php');
				exit;
			}
		}
	}
		
	function wp_phanfare_post_rewrite_rules($rewrite)
	{
		# Description:	Creates the new rewrite rules for post permalinks
		#
		# History:		2008-12-07 - Initial version (adam)
		#
		
		global $wp_rewrite;

		$wp_phanfare_token = '%' . WP_PHANFARE_QUERY_VAR . '%';
		$wp_rewrite->add_rewrite_tag($wp_phanfare_token, '(.+)', WP_PHANFARE_QUERY_VAR . '=');		
		$wp_phanfare_structure = $wp_rewrite->permalink_structure . WP_PHANFARE_QUERY_VAR . "/$wp_phanfare_token";
		$wp_phanfare_rewrite = $wp_rewrite->generate_rewrite_rules($wp_phanfare_structure);

		return ( $rewrite + $wp_phanfare_rewrite );
	}
	
	function wp_phanfare_page_rewrite_rules($rewrite)
	{
		# Description:	Creates the new rewrite rules for page permalinks
		#
		# History:		2008-12-07 - Initial version (adam)
		#
		
		global $wp_rewrite;

		$wp_phanfare_token = '%' . WP_PHANFARE_QUERY_VAR . '%';
		$wp_rewrite->add_rewrite_tag($wp_phanfare_token, '(.+)', WP_PHANFARE_QUERY_VAR . '=');
		$wp_phanfare_structure = $wp_rewrite->page_structure . '/' . WP_PHANFARE_QUERY_VAR . "/$wp_phanfare_token";
		$wp_phanfare_rewrite = $wp_rewrite->generate_rewrite_rules($wp_phanfare_structure);

		return ( $wp_phanfare_rewrite + $rewrite );
	}

	function wp_phanfare_query_vars($vars)
	{
		$vars[] = WP_PHANFARE_QUERY_VAR;
		return $vars;
	}
		
	function wp_phanfare_flush_rewrite_rules() 
	{
	   global $wp_rewrite;
	   $wp_rewrite->flush_rules();
	}
	
	function fixTrueFalseAndEncoding(&$value, $key) {
		$value = urldecode($value);

		if ($value == 'false') {
			$value = false;
		} elseif ($value == 'true') {
			$value = true;
		}
	}
	
	function decode_entities($text, $quote_style = ENT_COMPAT)
	{
		# Description:	Handles UTF-8 decoding in PHP4
		#
		# History:		http://us2.php.net/manual/en/function.html-entity-decode.php#68536
		#
		
		if ( function_exists('html_entity_decode') ) {
			$text = html_entity_decode($text, $quote_style, 'ISO-8859-1');
		} else { 
			$trans_tbl = get_html_translation_table(HTML_ENTITIES, $quote_style);
			$trans_tbl = array_flip($trans_tbl);
			$text = strtr($text, $trans_tbl);
		}
		$text = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text); 
		$text = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $text);
		
		return $text;
	}
	
	function wp_phanfare_compare_exif($x, $y)
	{
		# Description:	Compare the exif date taken when sorting the array. This is used
		#				because the order of the RSS feed is not always by date.
		#
		# Parameters:	x - first item
		#				y - second item
		#
		# Returns:		comparison result
		#
		# History:		2006-12-13 - Initial version (atow)
		#
	
		if( $x['exif']['datetimeoriginal'] && $y['exif']['datetimeoriginal'] ) {
			$xt = strtotime($x['exif']['datetimeoriginal']);
			$yt = strtotime($y['exif']['datetimeoriginal']);
		
			if( $xt == $yt ) {
				return 0;
			} else if( $xt < $yt ) {
				return -1;
			} else {
				return 1;
			}
		 }
		 return 0;
	}
   } // class wpPhanfare
}

$wpPhanfare = new wpPhanfare;

?>
