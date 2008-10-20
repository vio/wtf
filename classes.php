<?php 

/*
 * wtpClass
 * @todo partials directory should be a property
*/

class wtpClass {
	public $current_section = 0;
	
	private $wtf_dir = '';
	private $tree= array();

	/* 
	 *	Pre-def directories where we search for partials 
	 *	global - for main templates ( index, home, single, 40 )
	 *	shared - partials used across all templates ( ex: head, header, footer, etc )
	 *
	 */
	public $dirs = array('global','shared');
	
	/* all directories where we should search */
	public $namespaces = array();		
	
	/* all directories paths */
	public $paths = array();			

	/* all files names for a content partial */	
	public $files = array();			


	public function wtpCLass() {
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




	/* Creats page/post/category structure */
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

		/* 404 */
		elseif(is_404()):
			$this->load_item(array('Not found','404'));

		elseif(is_search()):
			$this->load_item(array('Search results','search'));
				
		elseif(is_tag()):
			$this->load_item(array('Tag','tag'));
			
		endif;

	}

	/* load_item : add item to tree */
	private function load_item($item,$type="",$siblings=0) {
		/* cat */
		if($type=='cat') :
			$this->tree[]=array($item->cat_ID,$item->cat_name,$item->slug,$type,$siblings);	
		/* post */	
		elseif($type=='post'):
			$this->tree[]=array($item->ID,$item->post_title,$item->post_name,$type,$siblings);	
		else:
			$this->tree[]=array(0,$item[0],$item[1],'',$siblings);
		endif;
	}

	/* namespaces */
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

	/* files */
	private function set_files() {
		$this->files[] = 'index';	
	
		/* switching response type based on WP if_* function */
		
		/* single page */
		if(is_single()):
			global $post;
			$this->files[]= 'single';
			
			$this->files[]= 'single-'.$post->ID;
			$this->files[]= $post->post_name;
			
		/* is_category */	
		elseif(is_category()):
			global $cat;
			$this->files[]= 'category';

			$this->files[]= 'category-'.$cat;
			$this->files[]= get_category($cat)->slug; 
			
		/* is_page */
		elseif(is_page()):
			global $post;
			$this->files[]= 'page';
			
			$this->files[]= 'page-'.$post->ID;
			$this->files[]= $post->post_name;

		/* homepage */
		elseif(is_home()):
			$this->files[]= 'home';

		/* 404 */
		elseif(is_404()):
			$this->files[]= '404';

		/* search results */
		elseif(is_search()):
			$this->files[]= 'search';

		/* tag results */
		elseif(is_tag()):
			$this->files[]= 'tag';


		else:
			'other types of content';
		endif;	
	}
	
	public function get_files() {
		return $this->files;
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
	 *	get_breadcrumbs 
	 *	Get parent tree with filters and home link
	 *	addHome - add homepage link 
	 *  removePws - remove parents with simblings ( if a post is under multiple categorie, will remove parent from list ) 
	 *
	 */
	public function get_breadcrumbs( $addHome, $removePws ) {
		$_tree = $this->tree;

		/* add root item */
		if( $addHome ) :
			$_home[] = array(0, 'Home', '', 'home', 1);	
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

	/* set current section */
	private function set_current_section() {
		if(sizeof($this->tree)>0):
			$this->current_section=$this->tree[0][0];
		else:
			$this->current_section= 0;
		endif;
		}

}
?>
