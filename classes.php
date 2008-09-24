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
				
				$this->load_item($_cat,'cat');
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
	private function load_item($item,$type) {
		/* cat */
		if($type=='cat') :
			$this->tree[]=array($item->cat_ID,$item->cat_name,$item->slug,$type);	
		/* post */	
		else:
			$this->tree[]=array($item->ID,$item->post_title,$item->post_name,$type);	
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
	
	/* Breadcrumbs */
	public function get_breadcrumbs() {
		return $this->tree;
	}

	/* Breadcrumbs html */
	public function get_breadcrumbs_html() {
		$_items= $this->get_breadcrumbs();
		$_cnt=1;
		if(sizeof($_items)>0):
			echo "<ul>\n";
			foreach($_items as $_item):
				/* permalink */
				$_permalink;
				if($_item[3]=='post'):
					$_permalink=get_permalink($_item[0]);
				else:
					$_permalink=get_category_link($_item[0]);
				endif;
	
				/* writting html item  */
				if($_cnt==sizeof($_items)):
					echo "<li>{$_item[1]}</li>\n";
				else:
					echo "<li><a href=\"{$_permalink}\">{$_item[1]}</a></li>\n";
				endif;

				$_cnt++;
			endforeach;
			echo "</ul>\n";
		endif;
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
