<?php
/*
Plugin Name: Wordpress Theme Partials (WTP)
Plugin URI: http://semanticthoughts.com/wordpress/theme/partials
Description: WTP loads theme partials based on namespace constructed from posts/pages/category hierarchy.
Author: Viorel Cojocaru (vio@beanon.com)
Version: 0.5.1
Author URI: http://semanticthoughts.com/
*/

/* wp theme partials object */
$wtp =  null;


/* include requested files */
include "classes.php";
include "functions.php";


/* init wp theme partial object */
function wtp_init() {
	global $wtp;
	$wtp=new wtpClass;
	}

/* add filters/actions to wordpress */
add_action('wp','wtp_init');
add_filter('set_current_user','wtp_debug');

?>
