<?php 
/**
 *	WTF - Wordpress Theme Fragments
 *	
 *	Loading fragments based on name space and wp page type
 *
 **/


/* WordpressThemeFragment */
class WordpressThemeFragment {
	public $current_section = 0;
	
	private $wtf_dir = '';
	private $tree= array();
	private $dirs = array('global','shared');
	
	private $namespaces = array();
	private $files = array();
	private $paths = array();

	public function WordpressThemeFragment() {
		$this->load_from_wp();
		
		$this->set_tree();
		$this->tree= array_reverse($this->tree);
		
		$this->set_namespaces();
		$this->set_files();
		$this->set_paths();
		$this->set_current_section();
	}

	/* Load all information from Wordpress */
	private function load_from_wp() {
		$this->wtf_dir= TEMPLATEPATH."/fragments/";

	}

	private function set_tree() {

		/* Post*/
		if(is_single()):
			global $post;
			
			$this->load_item($post,'post');
			$_categories = get_the_category();
			if(sizeof($_categories)>0):
				// adding category to tree

				$_cat = $_categories[0];
				
				$this->load_item($_cat,'cat',sizeof($_categories));
				while($_cat->category_parent!=0):
					$_cat = get_category($_cat->category_parent);	
					$this->load_item($_cat,'cat');
				endwhile;
				
			else:
				echo 'This post doesn\'t have category parent ?!!!';
			endif;
			
		/* Category */	
		elseif(is_category()):
			global $cat;
			
			$_cat= get_category($cat); 
			$this->load_item($_cat,'cat');
			
			while($_cat->category_parent!=0):
				$_cat = get_category($_cat->category_parent);	
				$this->load_item($_cat,'cat');
			endwhile;

		/* Page */
		elseif(is_page()):
			global $post;
			$this->load_item($post,'post');
			
			$_post= $post;
			while($_post->post_parent!=0):
				$_post= get_post($_post->post_parent);
				$this->load_item($post,'post');
			endwhile;
		
		endif;

	}

	/* load_item : add item to tree */
	private function load_item($item,$type,$siblings=0) {
		/* cat */
		if($type=='cat') :
			$this->tree[]=array($item->cat_ID,$item->cat_name,$item->slug,$type,$siblings);	
		/* post */	
		else:
			$this->tree[]=array($item->ID,$item->post_title,$item->post_name,$type,$siblings);	
		endif;
	}

	private function set_namespaces() {
		$_dirs= array();
		$_dirs[]= 'global/';
		$_dirs[]= 'shared/';

		for($i=0;$i<sizeof($this->tree);$i++):
			$_dir_path= "";
			for($j=0;$j<=$i;$j++):
				$_dir_path= $_dir_path.$this->tree[$j][2]."/";
			endfor;
			$_dirs[]= $_dir_path;
		endfor;

		$this->namespaces= $_dirs;
	}
	
	public function get_namespaces() {
		return $this->namespaces;	
	}

	public function debug_namespaces() {
		echo "<div class=\"debug-wtf\">";
		foreach($this->namespaces as $_ns):
			echo $_ns."<br />";
		endforeach;
		echo "</div>";
	}
	
	/* fragment files */
	private function set_files() {
		$this->files[] = 'index';	
		if(is_single()):
			global $post;
			$this->files[]= 'single';
			
			$this->files[]= 'single-'.$post->ID;
			$this->files[]= $post->post_name;
			
		elseif(is_category()):
			global $cat;
			$this->files[]= 'category';

			$this->files[]= 'category-'.$cat;
			$this->files[]= get_category($cat)->slug; 
			
		elseif(is_page()):
			global $post;
			$this->files[]= 'page';
			
			$this->files[]= 'page-'.$post->ID;
			$this->files[]= $post->post_name;

		elseif(is_home()):
			$this->files[]= 'home';

		else:
			'other types of content';
		endif;	
	}
	
	public function get_files() {
		return $this->files;
	}
	
	public function debug_files() {
		echo "<div class=\"debug-wtf\">";
		foreach($this->files as $_file):
			echo $_file."<br />";
		endforeach;
		echo "</div>";
	}
	
	/* paths */
	private function set_paths() {
		foreach($this->namespaces as $_ns):
			foreach($this->files as $_file):
				$this->paths[] = "$_ns$_file.php";
			endforeach;
		endforeach;
	}

	public function get_paths() {
		return $this->paths;
	}

	public function debug_paths() {
		echo "<div class=\"debug-wtf\">";
		foreach($this->paths as $_path):
			echo $_path."<br />";
		endforeach;
		echo "</div>";
	}

	/* Load a layout fragment */
	private function load_fragment($fragment) {
		$_namespaces= array_reverse($this->namespaces);

		foreach($_namespaces as $_ns):
			$_fragment = "{$this->wtf_dir}{$_ns}/{$fragment}.php";
			if(file_exists($_fragment)):
				include($_fragment);
				break;
			endif;
		endforeach;
	}

	/* Load content */
	private function load_content() {
		$_paths= array_reverse($this->paths);

		foreach($_paths as $_path):
			$_file=$this->wtf_dir.$_path;
			if(file_exists($_file)):
				include($_file);
				break;
			endif;
		endforeach;
	}

	/* Load fragments */
	public function load($fragment) {
		if($fragment==="content"):
			$this->load_content();
		else:
			$this->load_fragment($fragment);
		endif;
	}
	
	/*
	 * get_breadcrumbs 
	 * Get parent tree with filters and home link
	 *
	 */
	public function get_breadcrumbs( $hasHome, $removePws) {
		$_tree = $this->tree;

		/* add root item */
		if( $hasHome ) :
			$_home[] = array(0,'Home','','home',1);	
			$_tree = array_merge($_home,$_tree);
		endif;

		/* filter parents with siblings */
		if( $removePws ) :
			function _filter_items($var) {
				if($var[4]>1) : 
					return false;
				else :
					return true;
				endif;
			}
			$_tree = array_filter($_tree,"_filter_items");
		endif;

		return $_tree;
	}

	/* 
	 *  get_greadcrumbs_html
	 *	Get breadcrumbs as html order list
	 *
	 *  params
	 *		hasHome		- boolean - if we need to add home/root link at begining
	 *		removePws	- boolean - if we will remove parents with sibling(s)
	 *  
	 */
	public function get_breadcrumbs_html( $hasHome = true, $removePws = true ) {

		$_tree = $this->get_breadcrumbs($hasHome,$removePws);

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
				endif;
				
				$_breadcrumb = "\t<li>{$_breadcrumb}</li>\n";
				$_breadcrumbs .= $_breadcrumb;
				$_cnt++;
			endforeach;
			
			$_breadcrumbs = "<ol>\n{$_breadcrumbs}</ol>\n";
		endif;

		return $_breadcrumbs;
	}

	/* set current section */
	private function set_current_section() {
		if(sizeof($this->tree)>0):
			$this->current_section=$this->tree[0][0];
		else:
			$this->current_section= 0;
		endif;
		}

	public function debug() {
		echo "<div id=\"wtf-debug\">";
		foreach($this->namespaces as $_ns):
			echo "$_ns<br />";
		endforeach;
		echo "</div>";
	}

}
?>
