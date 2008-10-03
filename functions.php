<?php

	
/*
 *	Load requested partial.
 *	If none, 'content' will be used 
*/

function wtp_load($partial = "content" ) {
	global $wtp;
	$wtp->load($partial);
	}



/*
 *	get_breadcrumbs
 *	
 */

function wtp_get_breadcrumbs( $before = "<h3>You are here:</h3>\n<ol>\n<li>", $separator = "</li>\n<li>", $after = "</li>\n</ol>\n", $addHome = true, $removePws = true ) {
	global $wtp;

	$_tree = $wtp->get_breadcrumbs($addHome, $removePws);

	/* build link based on item type */
	function _get_permalink( $var ) {
		if($var[3]=='post'):
			$_permalink=get_permalink($var[0]);
		elseif($var[3]=='cat'):
			$_permalink=get_category_link($var[0]);
		else:
			$_permalink=get_bloginfo("home");
		endif;
		return $_permalink;
	}

	/* draw items */
	$_breadcrumbs = "";
	
	if(sizeof($_tree)>0):
		$_cnt = 1;
		foreach($_tree as $_item):
			$_breadcrumb = "";
			$_permalink	= _get_permalink( $_item );

			if(sizeof($_tree)==$_cnt):
				$_breadcrumb = $_item[1];
			else:
				$_breadcrumb = "<a href=\"{$_permalink}\">{$_item[1]}</a>";
				$_breadcrumb .= $separator;
			endif;
			
			$_breadcrumbs .= $_breadcrumb;
			$_cnt++;
		endforeach;
		
		$_breadcrumbs = "$before\n$_breadcrumbs\n$after\n";
	endif;
	
	echo $_breadcrumbs;
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
