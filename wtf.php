<?php
/*
Plugin Name: WP Theme Fragments (WTF)
Plugin URI: 
Description: WTF loads theme fragments(partials) based on namespace constructed from posts/pages/category hierarchy.
Author: Viorel Cojocaru (vio@beanon.com)
Version: 0.1
Author URI: http://semanticthoughts.com/

*/

include "classes.php";

/*
 * Add WTF css
 *
 * inserting link to particular css styles required by WTF
 *
 * @todo $_url shouldn't rely on absolute values
 * @todo must add checking for debug mode
*/
function wtf_add_css() {
	$_url	= get_bloginfo("home")."/wp-content/plugins/wtf/wtf.css";
	echo "<style type=\"text/css\">@import \"{$_url}\";</style>\n";
}

/*
 * Init WTF
 *
 */
function wtf_init() {
	global $wtf;
	$wtf=new WordpressThemeFragment;
	}

add_action('wp','wtf_init');
add_filter('wp_head','wtf_add_css');
