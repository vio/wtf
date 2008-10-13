<?php
/*
Plugin Name: Wordpress Theme Partials (wp-partials)
Plugin URI: 
Description: wp-partials loads theme partials based on namespace constructed from posts/pages/category hierarchy.
Author: Viorel Cojocaru
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
