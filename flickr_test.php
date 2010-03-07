<?php

// error_reporting(E_ALL);

include 'app/config.inc.php';
include 'app/requestcore.class.php';
include 'app/flickr.class.php';
include 'app/flickrcache.class.php';

/**
* My Flickr Folio
*/
class Folio
{
	var $api;
	var $tree;
	var $breadcrumb;
	var $page;
	
	// var $key = FLICKR_KEY;
	// var $secret_key = FLICKR_SECRET_KEY;
	var $user_id = FLICKR_USER_ID;
	var $collection_id = FLICKR_COLLECTION_ID;
	
	
	function __construct()
	{
		$this->api = new FlickrCache();
		// $this->api->cache_mode(true, 6400, './app/_cache/');
		
		if (isset($_GET['page']))
			$this->page = preg_replace('/[^0-9\-]/', '', $_GET['page']);
		
		if (strpos($this->page, '-')) {
			
			
		}
		
		$this->get_tree();
		$this->get_breadcrumb();
	}
	
	public function get_tree()
	{
		$response = $this->api->collections->get_tree(array(
			'collection_id' => $this->collection_id,
			'user_id'       => $this->user_id,
		));
		
		$this->tree = $response->collections->collection[0];
		
		if(!property_exists($this->tree,'collection') && !property_exists($this->tree,'set'))
		{
			$this->tree = null;
			return false;
		}
		
		return true;
	}
	
	public function get_breadcrumb($tree=false, $parent=array())
	{
		if (!$tree) $tree = & $this->tree;
		$function = __FUNCTION__;
		
		if(!is_object($tree) || (!property_exists($tree,'collection') && !property_exists($tree,'set')))
			return false;
		
		$parent[$tree->id] = true;
		
		if (property_exists($tree,'collection'))
		{
			foreach ($tree->collection as $collection)
				$this->$function($collection, $parent);
		}
		elseif (property_exists($tree,'set'))
		{
			foreach ($tree->set as $set)
				$this->breadcrumb[$set->id] = $parent;
		}
		
		return true;
	}
	

	public function get_menu($tree=false, $parent=array(), $level=0)
	{
		if (!$tree) $tree = & $this->tree;
		$function = __FUNCTION__;
		$output   = '';
		$tab      = '';
		$level++;
		
		if(!is_object($tree) || (!property_exists($tree,'collection') && !property_exists($tree,'set')))
			return false;
		
		// Add a tab for every level we go down
		for ($i=1; $i < $level; $i++)
			$tab .= "\t";
		
		// Skip the parent collection
		if ($level > 1 || property_exists($tree,'set'))
		{
			if (!empty($this->page) && isset($this->breadcrumb[$this->page][$tree->id]) || $this->page == $tree->id)
				$output .= "$tab<li class=\"collection active\">";
			else
				$output .= "$tab<li class=\"collection\">";

			$output .= "<a href=\"?page=$tree->id\">$tree->title</a>\n";
			$output .= "$tab\t<ul>\n";
		}

		if (property_exists($tree,'collection'))
		{
			foreach ($tree->collection as $collection)
				 $output .= $this->$function($collection, $parent, $level);
		}
		elseif (property_exists($tree,'set'))
		{
			foreach ($tree->set as $set)
			{
				if (!empty($this->page) && isset($this->breadcrumb[$this->page][$set->id]) || $this->page == $set->id)
					$output .= "$tab\t\t<li class=\"set active\">";
				else
					$output .= "$tab\t\t<li class=\"set\">";
				
				$output .= "<a href=\"?page=$set->id\">$set->title</a></li>\n";
			}
		}
		
		// Skip the parent collection
		if ($level > 1 || property_exists($tree,'set'))
		{
			$output .= "$tab\t</ul>\n";
			$output .= "$tab</li>\n";
		}
		
		return $output;
	}
	
}


$folio = new Folio;
// var_dump($folio);

?>
<style type="text/css" media="screen">
	.active > a{
		background-color: red;
	}
</style>
	
<?php

echo "<ul id=\"nav\">\n";
echo $folio->get_menu();
echo "</ul>";

?>