<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;

class BrandsThemeResolver{

	private $brand;

	public static function instance(){
		return new BrandsThemeResolver();
	}

	public function get_exception_routes(){
		return [
			'routes.to.exclude',
			'en-ca.pepsibrands.quaker.landing',
			'fr-ca.pepsibrands.quaker.landing',
			'en-ca.pepsibrands.realjoy.landing',
			'fr-ca.pepsibrands.realjoy.landing',
			'en-ca.pepsibrands.stacys.rise',
			'fr-ca.pepsibrands.stacys.rise',
			// 'en-ca.pepsibrands.quaker.thankyou',
			// 'fr-ca.pepsibrands.quaker.thankyou',
		];
	}

	public function is_brands_page(RouteMatchInterface $route_match){
		$brand = 'Tastyrewards';
	    $route = $route_match->getRouteObject();
	    $current_path = \Drupal::service('path.current')->getPath();
	    $alias = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
	    if ($this->is_node_path($current_path) || $this->is_taxonomy_path($current_path) ){
	    		// debug_var($current_path, 1);
	    	$entity_id = basename($current_path);
	    	if ($this->is_node_path($current_path)){
	    		$brand = $this->get_node_brand($entity_id);
	    	} elseif($this->is_taxonomy_path($current_path)){
	    		$brand = $this->get_taxonomy_brand($entity_id);
	    	}
	    	$brands_to_exclude = ['Tastyrewards', 'gameday', 'spookysnacklab'];
	    	if ( !in_array($brand, $brands_to_exclude) && !empty($brand) ){
	    		// This means it's not a TR content
	    		return true;
	    	} else{
	    		return false;
	    	}
	    }
	
		$routename = $route_match->getRouteName();
		$exceptions_route = $this->get_exception_routes();

		if (in_array($routename, $exceptions_route)){
			return false;
		}

	    if (strpos($route->getPath(), '/brands/') !== false 
	    	|| strpos($route->getPath(), '/marques/') !== false 
	        || strpos($alias, "/brands/") !== false  
	        || strpos($alias, "/marques/") !== false  
	    ){
	      return TRUE;
	    }

	    return false;
	}
	function get_taxonomy_brand($tid){
    	$term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    	$vocabulary = $term->bundle();
    	$this->brand = '';
    	if ($vocabulary == 'tostitos_occasions' || $vocabulary == 'brands_occasions'){
    		$val = $term->get('field_brand')->getValue();
    		$this->brand = !empty($val[0]['value'])? $val[0]['value'] : "";

    	}
    	return $this->brand;

	}

	function get_node_brand($nid){
    	$node = \Drupal\node\Entity\Node::load($nid);
    	$node_brand = '';
    	if (empty($node))
    		return ;
    	if($node->getType() == 'recipe'){
    		$val = $node->get('field_brand_website')->getValue();
    		$node_brand = !empty($val[0]['value'])? $val[0]['value'] : "";
    	} elseif($node->getType() == 'product'){
    		$val = $node->get('field_brand')->getValue();
    		$node_brand = !empty($val[0]['value'])? $val[0]['value'] : "";
    	}

    	// All the fields in the CMS has Fritolayvarietypacks as key whereas 
    	// the templates and libraries in brandstheme have fritolay as key
    	if ($node_brand === 'Fritolayvarietypacks')
    		return 'fritolay';
    	if ($node_brand === 'enflamme')
    		return 'flaminhot';

    	if ($node_brand === 'capitainecrounche')
    		return 'capncrunch';

    	
    	return $node_brand;

	}

	public function is_taxonomy_path($current_path){
	    if (strpos($current_path, "/taxonomy/term/") !== false){
	    	return true;
	    }
	    return false;
	}

	public function is_node_path($current_path){
		if (strpos($current_path, "/node/") !== false){
	    	return true;
	    }
	    return false;	
	}

}