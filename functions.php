<?php

	
/*
 *	Load requested partial.
 *	If none, 'content' will be used 
*/

function wtp_load($partial = "content" ) {
	global $wtp;
	$wtp->load($partial);
	}






/* get breadcrumbs html */
function wtp_get_breadcrumbs() {
	global $wtp;
	echo $wtp->get_breadcrumbs_html();
	}




/*
 * Add WTF css
 *
 * inserting link to particular css styles required by WTF
 *
 * @todo $_url shouldn't rely on absolute values
 * @todo must add checking for debug mode
*/
function wtp_add_css() {
	$_url	= get_bloginfo("home")."/wp-content/plugins/wtf/wtf.css";
	echo "<style type=\"text/css\">@import \"{$_url}\";</style>\n";
}


?>
