<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;

use Drupal\node\Entity\Node;
use Drupal\pepsibrands\BrandsContentImport;
use Drupal\taxonomy\Entity\Term;

class BrandsContentFilters{

	private $slash = DIRECTORY_SEPARATOR;
	private $nb_recipes_per_query = 12;
	// private $time_options = [];

	public static function instance(){
		return new BrandsContentFilters();
	}



	public function fetch_occasions_by_recipe($brand, $nid){
		$occasions = [];
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode();
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'brands_occasions');
		$query->condition('field_brand', $brand);
		$query->condition('field_occasion_recipes.entity:node.nid', $nid, 'IN');
		$query->range(0, 3);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];
		foreach ($entity_ids as $tid) {
			$entity = [];
			$term = Term::load($tid);

		    $img = \Drupal\file\Entity\File::load($term->field_occasion_image->target_id);
		    if ($langcode != 'en'){
		    	if (!$term->hasTranslation('fr'))
					continue;
		    	$term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
		    }

		    $entity['title'] =  $term->field_subtitle->value;
		    $entity['image_url'] = file_create_url( $img->getFileUri() );
		    // @TODO: Figure out an alias for taxonomy
		    $entity['link'] = "/taxonomy/term/" . $tid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $tid, $langcode);
		    $entity['link'] = "/$lang_prefix" . $alias;

		    $entity['tid'] = $tid;
		    $occasions[] = $entity;
		}

		return $occasions;
	}



	public function fetch_occasions($brand, $is_featured = 0, $nb_occasions = null){
		$occasions = [];
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'brands_occasions');
		if ($is_featured)
			$query->condition('field_is_featured', 1);
		if ($nb_occasions !== null)
			$query->range(0, $nb_occasions);

		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		foreach ($entity_ids as $tid) {
			$entity = [];
			$term = Term::load($tid);
			$lang_prefix = get_current_langcode();

		    $img = \Drupal\file\Entity\File::load($term->field_occasion_image->target_id);
		    if ($langcode != 'en'){
		    	if (!$term->hasTranslation('fr'))
					continue;
		    	$term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
		    }

		    $entity['title'] =  $term->field_subtitle->value;
		    $entity['image_url'] = file_create_url( $img->getFileUri() );
		    $entity['link'] = "/$lang_prefix/taxonomy/term/" . $tid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $tid, $langcode);
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $entity['tid'] = $tid;
		    $occasions[] = $entity;
		}

		return $occasions;
	}

	public function compute_avg_rating($node){
		if ($node->getType() != 'recipe')
			return null;
		
		$total = intval($node->field_total_rating->value);
		$nb_voters = intval($node->field_rating_voters->value);
		if (empty($nb_voters))
			return 0;
		return round($total/$nb_voters);


	}

	public function get_adjacent_recipe($brand, $node, $offset){
		$recipe = [];
		$recipe['link'] = '';
		$order = $node->field_order->value;
		if (empty($order) || !is_numeric($order)){
			$searched_order = 1;
		} else {
			$searched_order = intval($order) + $offset;
		}

		$comparison_sign = $offset > 0 ? ">=" : "<=";
		$sort_order = $offset > 0 ? "ASC" : "DESC";
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		$query->condition('field_order', $searched_order, $comparison_sign);
		$query->sort('field_order', $sort_order);
		$query->range(0,1);
		$result = $query->execute();
		if (empty($result))
			return [];
		$entity_ids = array_values($result);
		$nid = $entity_ids[0];
		$node = \Drupal\node\Entity\Node::load(intval($nid)); 
		if ($langcode != 'en'){
			if (!$node->hasTranslation('fr')){
				return '';
			}
			$node = $node->getTranslation('fr');
		}
		$lang_prefix = get_current_langcode($with_prefix = true);
	    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
	    $recipe['link'] = "/$lang_prefix" . $alias;

	    $img_url = "";
	    if (!empty($node->field_recipe_image->target_id)){
			$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
		} else {
			if (!empty($node->field_recipe_image_detail->target_id)){
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
			}
		}

		if (!empty($img)){
			$img_url = file_create_url( $img->getFileUri() );
		}
		
	    $recipe['image_url'] = $img_url;
	    return $recipe;
	}


	public function search_recipes($brand, $number, $offset, $search_term, $lang_prefix = "en-ca"){
		$recipes = [];
		$nb_extra_recipes = 12;
		// $langcode = get_current_langcode($with_prefix = false);
		// langcode is "fr" or "en" so we simply remove "-ca" from en-ca/fr-ca
		$langcode = str_replace("-ca", "", $lang_prefix);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);

		$contains_term = $query->orConditionGroup()
			->condition('field_recipe_subtitle', '%' . $search_term. '%', 'LIKE')
			->condition('field_recipe_ingredients', '%' . $search_term. '%', 'LIKE')
			->condition('body', '%' . $search_term. '%', 'LIKE');
		$query->condition($contains_term);


		$query->sort('field_order', 'ASC');
		$query->sort('changed', 'DESC');
		$query->range($offset, $number + $nb_extra_recipes);
		$entity_ids = $query->execute();
		// debug_var($entity_ids, 1);
		if (empty($entity_ids))
			return [];
		foreach ($entity_ids as $nid) {
			$recipe = [];
			$node = \Drupal\node\Entity\Node::load(intval($nid)); 
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}
			$img_url = '';
		    // $img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
		    if (!empty($node->field_recipe_image->target_id)){
				// $img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
				$img_url = get_translated_image_url($node,"field_recipe_image", $langcode);
			} else {
				// $img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
				$img_url = get_translated_image_url($node,"field_recipe_image_detail", $langcode);
			}
		    $recipe['body'] =  $node->getTranslation($langcode)->body->value;
			
		    // $recipe['image_url'] = file_create_url( $img->getFileUri() );
		    $recipe['image_url'] = $img_url;

		    $recipe['title'] =  $node->field_recipe_subtitle->value;
		    $recipe['img_position'] =  $node->field_image_position->value;
		    $recipe['rating'] = $this->compute_avg_rating($node);
		    // $lang_prefix = get_current_langcode();
		    $recipe['link'] = "/$lang_prefix/node/" . $nid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    $recipe['link'] = "/$lang_prefix" . $alias;
		    // $recipe['link'] = $alias;
		    $recipe['nid'] = $nid;
		    $recipe['cta'] = $lang_prefix == 'en-ca'? "View Recipe" : "Voir la recette" ;
		    // Recipe ratings
		    $recipe['html_rating'] = '<div class="tostitos-stars tostitos-stars-corner">';
		    for($i = 1; $i<=5; $i++) {
		    	$recipe['html_rating'] .= '<span ';
		    	if ($recipe['rating']>= $i)
		    		$recipe['html_rating'] .= 'class="checked"';
		    	$recipe['html_rating'] .= '></span>';
			}
	    	$recipe['html_rating'] .= '</div>';

		    $recipes[] = $recipe;
		}

		$slice = array_slice($recipes, 0, $number);

		return $slice;
	}


	public function fetch_recipes($brand, $number, $offset, $lang_prefix = "en-ca"){
		$recipes = [];
		$nb_extra_recipes = 12;
		// $langcode = get_current_langcode($with_prefix = false);
		// langcode is "fr" or "en" so we simply remove "-ca" from en-ca/fr-ca
		$langcode = str_replace("-ca", "", $lang_prefix);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('status', 1);
		$query->condition('field_brand_website', $brand);
		$query->sort('field_order', 'ASC');
		$query->sort('changed', 'DESC');
		$query->range($offset, $number + $nb_extra_recipes);
		$entity_ids = $query->execute();
		// debug_var($entity_ids, 1);
		if (empty($entity_ids))
			return [];
		foreach ($entity_ids as $nid) {
			$recipe = [];
			$node = \Drupal\node\Entity\Node::load(intval($nid)); 
			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation($langcode))
					continue;
				$node = $node->getTranslation($langcode);
			}
			$img_url = '';
		    // $img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
		    if (!empty($node->field_recipe_image->target_id)){
				// $img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
				$img_url = get_translated_image_url($node,"field_recipe_image", $langcode);
			} else {
				// $img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
				$img_url = get_translated_image_url($node,"field_recipe_image_detail", $langcode);
			}
		    $recipe['body'] =  $node->getTranslation($langcode)->body->value;
			
		    // $recipe['image_url'] = file_create_url( $img->getFileUri() );
		    $recipe['image_url'] = $img_url;

		    $recipe['title'] =  $node->getTranslation($langcode)->field_recipe_subtitle->value;
		    $recipe['img_position'] =  $node->field_image_position->value;
		    $recipe['rating'] = $this->compute_avg_rating($node);
		    // $lang_prefix = get_current_langcode();
		    $recipe['link'] = "/$lang_prefix/node/" . $nid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    $recipe['link'] = "/$lang_prefix" . $alias;
		    // $recipe['link'] = $alias;
		    $recipe['nid'] = $nid;
		    $recipe['cta'] = $lang_prefix == 'en-ca'? "View Recipe" : "Voir la recette" ;
		    if ($lang_prefix == 'en-us' || $lang_prefix == 'es-us'){
		    	$recipe['cta'] = "View Recipe";
		    	if ($lang_prefix == 'es-us'){
		    		$recipe['cta'] = "Ver la receta";
		    	}

		    }
		    // Recipe ratings
		    $recipe['html_rating'] = '<div class="tostitos-stars tostitos-stars-corner">';
		    for($i = 1; $i<=5; $i++) {
		    	$recipe['html_rating'] .= '<span ';
		    	if ($recipe['rating']>= $i)
		    		$recipe['html_rating'] .= 'class="checked"';
		    	$recipe['html_rating'] .= '></span>';
			}
	    	$recipe['html_rating'] .= '</div>';

		    $recipes[] = $recipe;
		}

		$slice = array_slice($recipes, 0, $number);

		return $slice;
	}

	public function get_ingredients_by_brands($brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'ingredients');
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$occasions = [];

		$tids = array_values($entity_ids);
		foreach ($tids as $tid) {
			$term = Term::load($tid);
			$occasion = [];
			$occasion['tid'] = $tid;
			$occasion['title'] = $term->field_subtitle->value;
			$occasions[] = $occasion;
		}

		return $occasions;
	}


	public function get_product_group($brand){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_group');
		$query->condition('field_brand', $brand);
		$query->sort('field_order', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$categories = [];

		$tids = array_values($entity_ids);
		foreach ($tids as $tid) {
			$term = Term::load($tid);
			$category = [];
			// field_product_category_image, field_product_link, 
			if ($langcode != 'en'){
		    	if (!$term->hasTranslation('fr'))
					continue;
		    	$term = $term->getTranslation($langcode);
		    }

			$category['tid'] = $tid;
			$category['title'] = $term->field_subtitle->value;
			$basename = $term->field_basename->value;
			$category['link'] = "/$lang_prefix/brands/" . $brand . "/products-list/" . $basename;
			$category['link'] = strtolower($category['link']);
			// debug_var($tids, 1);
			$category['product_categories'] = $this->get_product_categories($brand, $tid);
			$categories[] = $category;
		}
		return $categories;
	}

	public function find_product_group_id($brand, $group_name){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_group');
		$query->condition('field_brand', $brand);
		$query->condition('field_basename', $group_name);

		$query->sort('field_order', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;

		$tids = array_values($entity_ids);
		return $tids[0];

	}


	public function get_product_categories($brand, $group_id = 0){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_brand', $brand);
		if (!empty($group_id)){
			$query->condition('field_product_group.entity:taxonomy_term.tid', $group_id);
		}
		$query->sort('field_order', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$categories = [];

		$tids = array_values($entity_ids);
		foreach ($tids as $tid) {
			$term = Term::load($tid);
			$term_en = $term;
			$category = [];
			// field_product_category_image, field_product_link, 
			if ($langcode != 'en'){
		    	if (!$term->hasTranslation('fr'))
					continue;
		    	$term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
		    }

		    $image_id = $term_en->field_product_category_image->target_id;
		    
		    if (!empty($term->field_product_category_image->target_id)){
		    	$image_id = $term->field_product_category_image->target_id;
		    }

			$img = \Drupal\file\Entity\File::load($image_id);
			$bg_img_url = "";
			if (!empty($term->field_background_image->target_id)){
				$bg_img = \Drupal\file\Entity\File::load($term->field_background_image->target_id);
				$bg_img_url = file_create_url( $bg_img->getFileUri() );
			}

	    	$category['image_url'] = file_create_url( $img->getFileUri() );
	    	$category['bg_image_url'] = $bg_img_url;
			$category['tid'] = $tid;
			$category['link'] = $term->field_product_link->getValue()[0]['uri'];
			$category['link'] = "/$lang_prefix" . str_replace("internal:", "", $category['link']);
			$category['link'] = strtolower($category['link']);
			$category['title'] = $term->field_subtitle->value;
			$category['body'] = $term->description->value;
			$category['background_color'] = $term->field_background_color->value;
			$categories[] = $category;
		}
		// debug_var($categories, 1);
		return $categories;
	}


	public function fetch_brands_occasions($brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'brands_occasions');
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$occasions = [];

		$tids = array_values($entity_ids);
		foreach ($tids as $tid) {
			$term = Term::load($tid);
			if ($langcode != 'en'){
		    	if (!$term->hasTranslation('fr'))
					continue;
		    	$term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
		    }
			$occasion = [];
			$occasion['tid'] = $tid;
			$occasion['title'] = $term->field_subtitle->value;
			$occasions[] = $occasion;
		}

		return $occasions;
	}

	public function fetch_recipes_by_ingredient($brand, $tid, $lang_prefix = "en-ca", $offset = 0){
		$term = Term::load($tid);
		\Drupal::logger('api')->info(' term ' . $term->field_subtitle->value , []);
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		$query->condition('field_recipe_ingredients', '%' . $term->field_subtitle->value . '%', 'LIKE');
		$query->range($offset, $this->nb_recipes_per_query);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (!empty($this->get_array_recipe_fields($node, $lang_prefix) ))
		    	$recipes[] = $this->get_array_recipe_fields($node, $lang_prefix);
		}

		return $recipes;
	}


	public function fetch_recipes_by_occasion($tid, $lang = 'en-ca', $offset = 0){
		$term = Term::load($tid);
		$recipes = [];
		$recipe_nodes =  $term->field_occasion_recipes->referencedEntities();
		if (empty($recipe_nodes))
			return [];

		foreach ($recipe_nodes as $key => $node) {
			if (!empty($this->get_array_recipe_fields($node, $lang) ))
		    	$recipes[] = $this->get_array_recipe_fields($node, $lang);
		}

		$slice = array_slice($recipes, $offset, $this->nb_recipes_per_query);
		return $slice;
	}


	public function fetch_recipes_by_time($brand, $time, $lang_prefix, $offset = 0){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		$query->condition('field_recipe_prep_time', $time);
		$query->range($offset, $number = $this->nb_recipes_per_query);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (!empty($this->get_array_recipe_fields($node, $lang_prefix)))
		    	$recipes[] = $this->get_array_recipe_fields($node, $lang_prefix);
		}
		return $recipes;
	}

	public function fetch_recipes_by_category($brand, $category, $lang_prefix, $offset = 0){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		$query->condition('field_recipe_filter', "%" .$category . "%", "LIKE");
		$query->range($offset, $number = $this->nb_recipes_per_query);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (!empty($this->get_array_recipe_fields($node, $lang_prefix))){
		    	$recipes[] = $this->get_array_recipe_fields($node, $lang_prefix);
			}
		}
		return $recipes;
	}


	public function fetch_recipes_with_videos($brand, $lang_prefix, $offset = 0){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		$query->condition('field_recipe_video', '', '<>');
		$query->range($offset, $number = $this->nb_recipes_per_query);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (!empty($this->get_array_recipe_fields($node, $lang_prefix))){
		    	$recipes[] = $this->get_array_recipe_fields($node, $lang_prefix);
			}
		}
		return $recipes;
	}

	public function fetch_recipes_with_chef($brand, $lang_prefix, $offset = 0){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		$query->condition('field_chef_filter', true);
		$query->range($offset, $number = $this->nb_recipes_per_query);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (!empty($this->get_array_recipe_fields($node, $lang_prefix))){
		    	$recipes[] = $this->get_array_recipe_fields($node, $lang_prefix);
			}
		}
		return $recipes;
	}

	private function get_array_recipe_fields($node, $lang_prefix = 'en-ca'){
		$recipe = [];
		// $langcode = substr($lang_prefix, 0, 2);
		// langcode is "fr" or "en" so we simply remove "-ca" from en-ca/fr-ca
		$langcode = str_replace("-ca", "", $lang_prefix);
		// $lang_prefix = get_current_langcode();

		$node_en = $node;
		if (trim($lang_prefix) != 'en-ca'){
			if ($node->hasTranslation('fr')){
				$node = $node->getTranslation('fr');
			} else {
				return;
			}
		}

		$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image_detail->target_id);

	    $recipe['title'] =  $node->field_recipe_subtitle->value;
	    $recipe['body'] =  $node->getTranslation($langcode)->body->value;
	   	if (empty($recipe['body'])){
	   		$recipe['body'] = "";
	   	}
	    // $recipe['rating'] =  $node->field_total_rating->value;
	    $recipe['rating'] = $this->compute_avg_rating($node);
	    $recipe['image_url'] = file_create_url( $img->getFileUri() );
	    $recipe['img_position'] =  $node->field_image_position->value;
	    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $node->id(), $langcode);
	    $recipe['link'] = "/$lang_prefix" . $alias;
	    // $recipe['link'] = "/$lang_prefix" . $alias;
	    $recipe['nid'] = $node->id();
	    $recipe['cta'] = $langcode == "en"? "View Recipe" : "Voir la recette" ;

	    // ratings
	    // Recipe ratings
		    $recipe['html_rating'] = '<div class="tostitos-stars tostitos-stars-corner">';
		    for($i = 1; $i<=5; $i++) {
		    	$recipe['html_rating'] .= '<span ';
		    	if ($recipe['rating']>= $i)
		    		$recipe['html_rating'] .= 'class="checked"';
		    	$recipe['html_rating'] .= '></span>';
			}
	    	$recipe['html_rating'] .= '</div>';

	    return $recipe;

	}

	public function get_product_category_basenames($brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;

		$tids = array_values($entity_ids);
		$basenames = [];
		foreach ($tids as $tid) {
			$term = Term::load($tid);
			$product_link = $term->field_product_link->getValue()[0]['uri'];
			$basenames[] = basename($product_link); 
		}

		return $basenames;
	}

	public function get_product_category_by_url($url, $brand = ""){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		if (strtolower($brand) == 'quaker'){
			$query->condition('field_product_link', "%/" .$url, 'LIKE');
		}
		else{
			$query->condition('field_product_link', "%" .$url, 'LIKE');
		}
		if (!empty($brand)){
			$query->condition('field_brand', $brand);
		}
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;

		$tids = array_values($entity_ids);
		if (count($tids) >= 1 ){
			return $tids[0];
		} else {
			return 0;
		}
	}

	public function product_category_ids_by_brand($brand){
		$arr = [];
		$content_import = new BrandsContentImport();
		// @TODO: find a more dynamic way to do this
		if ($brand == 'tostitos'){
			$arr = [
				'simply-tostitos' => $content_import->get_product_categoryID_by_title('Simply Tostitos®', 'tostitos'),
				'salsa-dips' => $content_import->get_product_categoryID_by_title('Salsa and Dips', 'tostitos'),
				'tostitos-chips' => $content_import->get_product_categoryID_by_title('Tostitos® Chips', 'tostitos'),
			];
		}
		return $arr;
	}

	public function fetch_related_products($brand, $product_id, $nb_products = 3){
		$langcode = get_current_langcode($with_prefix = false);
		$node = Node::load($product_id);
		$product_category_id = $node->field_product_category->target_id;


		// debug_var($categories, 1);
		$products = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('field_brand', $brand);
		$query->condition('nid', $product_id, '<>');
		$query->condition('field_product_category.entity:taxonomy_term.tid', $product_category_id);
		$query->range(0, $nb_products);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];
			$lang_prefix = get_current_langcode();

			$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);

			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['background_color'] =  $node->field_background_color->value;
		    $entity['body'] =  $node->body->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $entity['link'] = '/node/' . $node->id();
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();
		    // $entity['link'] = $alias;
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $products[] = $entity;
		}
		return $products;

	}



	public function get_related_recipes_entity_reference($nid, $is_brand_recipe = true){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$recipe_node = Node::load($nid);
		if (empty($recipe_node))
			return null;

		// $image_detail = true;
		$image_detail = $is_brand_recipe;
		// Yes, strangely, the related recipes is named field_test
		// $node->get("field_test")->referencedEntities(); 
		$refs = $recipe_node->getTranslation($langcode)->field_test->referencedEntities();
		foreach($refs as $key => $node){

			$nid = $node->id();
			$entity = [];
			$lang_prefix = get_current_langcode();
			// debug_var($nid);

			if ($image_detail){
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
			} else {
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
			}

			if ($langcode != 'en' && $is_brand_recipe){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

		    $entity['title'] =  $node->field_recipe_subtitle->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    // $entity['link'] = '/node/' . $node->id();
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();
		    // $entity['link'] = $alias;
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $recipes[] = $entity;
		}
		return $recipes;
	}


	public function get_related_products_entity_reference($nid){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$recipe_node = Node::load($nid);
		if (empty($recipe_node)){
			return null;
		}

		$refs = $recipe_node->field_related_products->referencedEntities();
		foreach($refs as $key => $node){

			$nid = $node->id();
			$entity = [];
			$lang_prefix = get_current_langcode();
			$img = "";
			if (!empty($node->field_recipe_image->target_id)){
				// product image field is called field_recipe_image
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
			}

			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    // $entity['link'] = '/node/' . $node->id();
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();
		    // $entity['link'] = $alias;
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $recipes[] = $entity;
		}
		return $recipes;
	}



	public function get_tastyrewards_recipes($brand, $image_detail = true){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('langcode', $langcode);
		$query->condition('field_brand', ucfirst($brand) );
		$query->orConditionGroup()
			->condition('field_brand_website', 'Tastyrewards' )
			->condition('field_brand_website',NULL,'=');
		$query->range(0, 3);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];
			$lang_prefix = get_current_langcode();

			if ($image_detail){
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
			} else {
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
			}

			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

		    $entity['title'] =  $node->field_recipe_subtitle->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $entity['rating'] =  $this->compute_avg_rating($node);
		    // $entity['link'] = '/node/' . $node->id();
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();
		    // $entity['link'] = $alias;
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $recipes[] = $entity;
		}
		return $recipes;
	}


	public function get_brands_recipes($brand, $image_detail = true, $count = 3){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('langcode', $langcode);
		$query->condition('field_brand_website', ucfirst($brand) );
		$query->range(0, $count);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];
			$lang_prefix = get_current_langcode();

			if ($image_detail){
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
			} else {
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
			}

			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

		    $entity['title'] =  $node->field_recipe_subtitle->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $entity['rating'] =  $this->compute_avg_rating($node);
		    // $entity['link'] = '/node/' . $node->id();
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();
		    // $entity['link'] = $alias;
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $recipes[] = $entity;
		}
		return $recipes;
	}

	public function fetch_featured_recipes($brand, $img_detail = 1, $featured_content = ''){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', $brand);
		
		if (empty($featured_content)){
			$query->condition('field_is_featured', 1);
		} else{
			$query->condition('field_featured_content', $featured_content);
		}
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];
			$lang_prefix = get_current_langcode();
			if ($img_detail){
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image_detail->target_id);
			} else {
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
			}
			
			/*
			if ($langcode != 'en'){
				// if (!$node->hasTranslation('fr'))
				if (!$node->hasTranslation($langcode))
					continue;
				// $node = $node->getTranslation('fr');
				$node = $node->getTranslation($langcode);
			}
			*/

			try {
				$node->hasTranslation($langcode);
				$node = $node->getTranslation($langcode);
			}
			catch (\InvalidArgumentException $e) 
			{
				continue;
			}			


		    $entity['title'] =  $node->field_recipe_subtitle->value;
		    $entity['body'] =  $node->body->value;
		    $entity['rating'] =  $this->compute_avg_rating($node);
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $entity['img_position'] =  $node->field_image_position->value;
		    // $entity['link'] = '/node/' . $node->id();
		    
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $recipes[] = $entity;
		}
		return $recipes;
	}

	public function fetch_featured_videos($brand, $number = 1){
		$langcode = get_current_langcode($with_prefix = false);
		$entities = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'videos');
		$query->condition('field_brand', $brand);
		$query->condition('field_is_featured', 1);
		$has_video = $query->orConditionGroup()
						->condition('field_recipe_video', '', '<>')
						->condition('field_youtube_video', '', '<>');
		$query->condition($has_video);
		$query->range(0, $number);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];
			$lang_prefix = get_current_langcode();

			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}


		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['body'] =  $node->body->value;
		    $video_field = !empty($node->field_youtube_video->value)? "field_youtube_video" : "field_recipe_video";
		    $entity['video'] = $this->embed_video($node->{$video_field}->value);
		    if (empty($entity['video']))
		    	continue;
		    $entity['key'] = str_replace("https://www.youtube.com/embed/", "", $entity['video']);
		    $entity['nid'] = $node->id();
		    $entities[] =$entity;
		}
		return $entities;

	}


	public function fetch_custom_content($brand, $type){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);
		$contents = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'custom_content');
		$query->condition('field_brand', $brand);
		$query->condition('field_custom_content_type', $type);
		if ($type == "moments")
			$query->sort('field_year', 'ASC');

		if ($type == "history")
			$query->sort('field_atc_order', 'ASC');

		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];

			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

			if (!empty($node_en->field_image->target_id)){
				$img = \Drupal\file\Entity\File::load($node_en->field_image->target_id);
			} else {
				$img = "";
			}

			$extra_img = "";
			if (!empty($node_en->field_extra_image2->target_id)){
				$extra_img = \Drupal\file\Entity\File::load($node_en->field_extra_image2->target_id);
			}
			
		    $entity['extra_image2_url'] = !empty($extra_img)? file_create_url( $extra_img->getFileUri()) : '';
		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['body'] =  $node->body->value;
		    $entity['year'] =  $node->field_year->value;
		    $entity['portion'] =  $node->field_portion->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';

		    $contents[] = $entity;
		}
		return $contents;
	}


	public function fetch_carousel_product($brand, $carousel_type){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);

		$products = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('field_brand', $brand);
		$query->condition('field_carousels', $carousel_type);
		$query->sort('field_weight', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];

			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

			if (!empty($node_en->field_recipe_image->target_id)){
				$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image->target_id);
			} else {
				// $img = \Drupal\file\Entity\File::load($node_en->field_recipe_image_detail->target_id);
				$img = "";
			}

			$featured_img = $img;
			$featured_img_url = !empty($featured_img)? file_create_url( $featured_img->getFileUri()) : '';
			$extra_img2 = "";

			if (!empty($node_en->field_recipe_image->target_id)
				&& !empty($node_en->field_extra_image1->target_id)) 
			{
				$featured_img = \Drupal\file\Entity\File::load($node_en->field_extra_image1->target_id);
				$featured_img_url = get_translated_image_url($node,"field_extra_image1", $langcode);

			}

			if (!empty($node_en->field_extra_image2->target_id) ){
				$_img2 = \Drupal\file\Entity\File::load($node_en->field_extra_image2->target_id);
				$extra_img2 = file_create_url( $_img2->getFileUri());
			}
			
		    // $entity['extra_image_url'] = !empty($featured_img)? file_create_url( $featured_img->getFileUri()) : '';
		    $entity['extra_image_url'] = $featured_img_url;
		    $entity['extra_image2_url'] = $extra_img2;
		    $entity['type'] =  'product';
		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['body'] =  $node->body->value;
		    $entity['extra_description'] =  $node->field_charcuterie->value;
		    $entity['bg_color'] =  $node->field_background_color->value;
		    $entity['background_color'] =  $node->field_background_color->value;
		    $entity['textbox_background_color'] =  $node->field_textbox_background_color->value;
		    $entity['entity_id'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();

		    $entity['link'] = "/$lang_prefix" . $alias;
		    $products[] = $entity;
		}
		return $products;
	}

	public function fetch_carousel_product_categories($brand, $carousel_type){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);

		$product_categories = [];
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_brand', $brand);
		$query->condition('field_carousels', $carousel_type);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$tids = array_values($entity_ids);
		foreach ($tids as $tid) {
			$term = Term::load($tid);
			$term_en = $term;
			$entity = [];

			// $node_en = $node;
			if ($langcode != 'en'){
				if (!$term->hasTranslation('fr'))
					continue;
				$term = $term->getTranslation('fr');
			}

			$image_target_id = $term_en->field_product_category_image->target_id;
		    
		    if (!empty($term->field_product_category_image->target_id)){
		    	$image_target_id = $term->field_product_category_image->target_id;
		    }

			if (!empty($image_target_id)){
				$img = \Drupal\file\Entity\File::load($image_target_id);
			} else {
				// $img = \Drupal\file\Entity\File::load($node_en->field_recipe_image_detail->target_id);
				$img = "";
			}
			
		    $entity['title'] =  $term->field_subtitle->value;
		    $entity['body'] =  $term->body->value;
		    $entity['type'] =  'product_categories';
		    $entity['extra_description'] =  $term->field_description->value;
		    $entity['entity_id'] = $term->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $entity['link'] = $term->field_product_link->getValue()[0]['uri'];
			$entity['link'] = "/$lang_prefix" . str_replace("internal:", "", $entity['link']);

		    $product_categories[] = $entity;
		}
		return $product_categories;
	}

	public function fetch_carousel_entities($brand, $carousel_type){
		$product_categories = $this->fetch_carousel_product_categories($brand, $carousel_type);
		$products = $this->fetch_carousel_product($brand, $carousel_type);
		return array_merge($product_categories, $products);
	}


	public function fetch_featured_products($brand, $product_category_basename, $featured_content = ''){

		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode($with_prefix = true);
		$product_category_id = 0;
		if (!empty($product_category_basename)){
			$product_category_id = $this->get_product_category_by_url($product_category_basename);
		}
		$products = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('field_brand', $brand);
		// nid 2523 & 2522 
		if (empty($featured_content)){
			$query->condition('field_is_featured', 1);
		} else{
			$query->condition('field_featured_content', $featured_content);
		}
		$query->sort('field_weight', 'ASC');
		// Uncoment this if the filter needs to take the product category
		// into account
		if (!empty($product_category_id))
			// @TODO: Check if this should be changed to Taxonomy term instead of node
			// $query->condition('field_product_category.entity:node.nid', $product_category_id);
			$query->condition('field_product_category.entity:taxonomy_term.tid', $product_category_id);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$entity = [];

			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

			if (!empty($node_en->field_recipe_image->target_id)){
				$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image->target_id);
			} else {
				// $img = \Drupal\file\Entity\File::load($node_en->field_recipe_image_detail->target_id);
				$img = "";
			}

			$featured_img = $img;
			// $featured_img_url = !empty($featured_img)? file_create_url( $featured_img->getFileUri()) : '';
			$featured_img_url = get_translated_image_url($node,"field_recipe_image", $langcode);
			$extra_img2 = "";

			if (!empty($node_en->field_recipe_image->target_id)
				&& !empty($node_en->field_extra_image1->target_id)) 
			{
				$featured_img = \Drupal\file\Entity\File::load($node_en->field_extra_image1->target_id);
				$featured_img_url = get_translated_image_url($node,"field_extra_image1", $langcode);
			}

			if (!empty($node_en->field_extra_image2->target_id) ){
				$_img2 = \Drupal\file\Entity\File::load($node_en->field_extra_image2->target_id);
				$extra_img2 = file_create_url( $_img2->getFileUri());
			}
			
		    // $entity['extra_image_url'] = !empty($featured_img)? file_create_url( $featured_img->getFileUri()) : '';
		    $entity['extra_image_url'] = $featured_img_url;
		    $entity['extra_image2_url'] = $extra_img2;
		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['body'] =  $node->body->value;
		    $entity['bg_color'] =  $node->field_background_color->value;
		    $entity['background_color'] =  $node->field_background_color->value;
            $entity['textbox_background_color'] =  $node->field_textbox_background_color->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = '/node/' . $node->id();

		    $entity['link'] = "/$lang_prefix" . $alias;
		    $products[] = $entity;
		}
		return $products;
	}

	public function fetch_products_by_shape($brand, $shape, $tid){
		// @TODO: Check if this needs to be changed for lays & Tostitos
		$brand = strcase_brand($brand);
		$langcode = get_current_langcode($with_prefix = false);
		$products = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('status', 1);
		$query->condition('field_brand', $brand);
		if ($shape == null){
			$empty_shape = $query->orConditionGroup()
				->notExists('field_shape')
				->condition('field_shape',NULL,'=');
			$query->condition($empty_shape);
		} else{
			$query->condition('field_shape', $shape);
		}
		if (!empty($tid)){
			 // $query->condition('field_product_category.entity:node.nid', $tid);
			$query->condition('field_product_category.entity:taxonomy_term.tid', $tid);
		}
		$query->sort('field_weight', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$lang_prefix = get_current_langcode();

		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (empty($node))
				continue;
			$entity = [];
			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}
			if (!$node->getTranslation($langcode)->status->value)
				continue;
			$extra_img = "";
			$extra_img1_url = "";
			$img_alt = "";
			$bg_img = "";
			$img_url = "";
			if (!empty($node_en->field_recipe_image->target_id)){
				$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image->target_id);
				$img_alt = $node->getTranslation($langcode)->field_recipe_image->alt;
				$img_url = get_translated_image_url($node,"field_recipe_image", $langcode);
			}
			if (!empty($node_en->field_extra_image1->target_id)){
				$extra_img = \Drupal\file\Entity\File::load($node_en->field_extra_image1->target_id);
				$extra_img1_url = get_translated_image_url($node,"field_extra_image1", $langcode);
			}
			if (!empty($node_en->field_background_image->target_id)){
				$bg_img = \Drupal\file\Entity\File::load($node_en->field_background_image->target_id);
			}
		    
		    $entity['charcuterie'] =  $node->field_charcuterie->value;
		    // $entity['extraimage1_url'] = !empty($extra_img)? file_create_url( $extra_img->getFileUri()) : '';
		    $entity['extraimage1_url'] = $extra_img1_url;
		    $entity['backgroundimage_url'] = !empty($bg_img)? file_create_url($bg_img->getFileUri()) : '';
		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['background_color'] =  $node->field_background_color->value;
		    $entity['body'] =  $node->body->value;
		    $entity['nid'] = $node->id();
		    $entity['image_alt'] = $img_alt;
		    $entity['image_url'] = $img_url;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = "/$lang_prefix/node/" . $node->id();
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $products[] = $entity;
		}
		return $products;
	}


	public function fetch_onepage_products($brand, $tid){
		$langcode = get_current_langcode($with_prefix = false);
		$products = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('field_brand', $brand);

		if (!empty($tid)){
			$query->condition('field_product_category.entity:node.nid', $tid);
		}
		$query->sort('field_weight', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$lang_prefix = get_current_langcode();

		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (empty($node))
				continue;
			$entity = [];
			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}
			if (!empty($node->field_recipe_image->target_id)){
				$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
			} elseif (!empty($node_en->field_recipe_image->target_id)){
				// if the FR version of the image is available, we retrieve it
				// otherwise pick the EN image for the FR content
				$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image->target_id);
			}

			if (!empty($node_en->field_extra_image1->target_id)){
				$extra_img = \Drupal\file\Entity\File::load($node_en->field_extra_image1->target_id);
		    	// $entity['extraimage1_url'] = !empty($extra_img)? file_create_url( $extra_img->getFileUri()) : '';
		    	$entity['extraimage1_url'] = get_translated_image_url($node,"field_extra_image1", $langcode);
			}

			if (!empty($node_en->field_extra_image2->target_id)){
				$extra_img = \Drupal\file\Entity\File::load($node_en->field_extra_image2->target_id);
		    	$entity['extraimage2_url'] = !empty($extra_img)? file_create_url( $extra_img->getFileUri()) : '';
			}


		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['html_title'] =  $node->field_html_title->value;
		    $entity['background_color'] =  $node->field_background_color->value;
		    $entity['nutrition'] =  $node->field_nutrition->value;
		    $entity['disclaimer'] =  $node->field_charcuterie->value;
		    $entity['ingredients'] =  $node->field_recipe_ingredients->value;
		    $entity['body'] =  $node->body->value;
		    $entity['nid'] = $node->id();

		    $claims = [];
		    $claims_keys = [];
	    	$entityManager = \Drupal::service('entity_field.manager');
			$fields = $entityManager->getFieldStorageDefinitions('node', 'product');
			$claim_options = options_allowed_values($fields['field_claim']);

		    foreach ($node->field_claim->getValue() as $key => $claim) {
	          $claims[] = t($claim_options[$claim['value']]);
	          $claims_keys[] = $claim['value'];
	        }
	        $entity['claims'] = $claims;
	        $entity['claims_keys'] = $claims_keys;
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $iframe_brand = $node->field_iframe_brand->value;
		    $entity['iframe_url'] = '';
		    if (!empty($iframe_brand)){
		    	$entity['iframe_url'] = get_brand_iframe_url($iframe_brand);
		    }
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = "/$lang_prefix/node/" . $node->id();
		    $entity['link'] = "/$lang_prefix" . $alias;
		    if (strpos(strtolower($brand), "fritolay") !== false || strpos(strtolower($brand), "flamin") !== false ){
			    $entity['brand_link'] = $node->field_brand_page_link->getValue()[0]['uri'];
				if (!empty($entity['brand_link'])){
					// $entity['brand_link'] = "/$lang_prefix" . str_replace("internal:", "", $entity['brand_link']);
					$entity['brand_link'] = str_replace("internal:", "", $entity['brand_link']);
					$entity['brand_link'] = strtolower($entity['brand_link']);
				}
		    }

		    $products[] = $entity;
		}
		return $products;
	}

	
	public function fetch_brand_slides($brand, $type = ""){
		$langcode = get_current_langcode($with_prefix = false);
		$slides = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'home_page_carousel2');
		if (empty($brand)){
			$empty_brand = $query->orConditionGroup()
				->condition('field_brand', 'Tastyrewards' )
				->notExists('field_brand')
				->condition('field_brand',NULL,'=');
			$query->condition($empty_brand);
		} else {
			// @TODO: add strcase_brand here
			$query->condition('field_brand', $brand);
		}
		$query->condition('status', 1);
		if (!empty($type))
			$query->condition('field_carousel_type', $type);
		$query->sort('field_carousel_position', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$lang_prefix = get_current_langcode();

		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (empty($node))
				continue;
			$entity = [];
			$node_en = $node;
			if (!$node->hasTranslation($langcode)){
				continue;
			}
			$node = \Drupal::service('entity.repository')->getTranslationFromContext($node, $langcode);
			//@TODO: The carousel needs to account for start_Date and end_date 
			if (!has_active_date_range($node) || $node->getTranslation($langcode)->status->getString() != 1){
				continue;
			}

			$media_uri = "";
			if (isset($node->field_carousel_media->target_id) ){
				$media_uri = $this->load_media($node->field_carousel_media->target_id);
			}

			if (!empty($node->field_carousel_image->target_id)){
				$img = \Drupal\file\Entity\File::load($node->field_carousel_image->target_id);
			}

			if (!empty($node->field_mobile_image->target_id)){
				$mobile_img = \Drupal\file\Entity\File::load($node->field_mobile_image->target_id);
		    	$entity['mobile_image_url'] = !empty($mobile_img)? file_create_url( $mobile_img->getFileUri()) : '';
			}

			// Link : field_cta_text
		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['body'] =  $node->body->value;
		    $entity['carousel_type'] =  $node->field_carousel_type->value;
		    $entity['theme'] =  $node->field_color_theme->value;
		    $entity['theme_mobile'] =  $node->field_mobile_color_theme->value;
		    $entity['position'] =  $node->field_cta_position->value;
		    $entity['position_mobile'] =  $node->field_carouseltext_vertical_pos->value;
		    $entity['nid'] = $node->id();
			$entity['media_url'] = $media_uri;
		    $entity['link'] = "";
		    $entity['link_title'] = "";
					    
		    if (!empty($node->field_cta_text->getValue())){
		    	$entity['link'] = $node->field_cta_text->getValue()[0]['uri'];
		    	$entity['link'] = str_replace("internal:", "", $entity['link']);
				$entity['link_title'] = $node->field_cta_text->getValue()[0]['title'];
		    } 
		    $entity['is_signuplink'] = 0;
			if (strpos($entity['link'], '/sign-up') !== false){
		    	$entity['is_signuplink'] = 1;
			}

		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $slides[] = $entity;
		}
		return $slides;
	}

	public function load_media($mid){
		$media = \Drupal\media\Entity\Media::load($mid);
		$fid = $media->field_media_image->target_id;
		if ($fid == null){
			$fid = $media->field_media_video_file->target_id;
		}
		try{
			$file = \Drupal\file\Entity\File::load($fid);
			$file_uri = file_create_url( $file->getFileUri() );
			return $file_uri;
		} catch(\Exception $e){
			return "";
		}
	}

	public function embed_video($youtube_url) {
		if (empty($youtube_url))
			return '';

		if (strpos($youtube_url, "https://www.youtube.com/embed/") !== false)
			return $youtube_url;
		
	    return preg_replace(
	        "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
	        "https://www.youtube.com/embed/$2",
	        $youtube_url
	    );
	}

	public function get_recipe_videos($brand, $nb_videos = 0){
		$langcode = get_current_langcode($with_prefix = false);
		$recipes = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'videos');
		$query->condition('field_brand', $brand);
		$query->condition('langcode', $langcode);
		$query->condition('status', 1);
		$has_video = $query->orConditionGroup()
						->condition('field_recipe_video', '', '<>')
						->condition('field_youtube_video', '', '<>');
		$query->condition($has_video);
		if ($nb_videos)
			$query->range(0, $nb_videos);
		$query->sort('field_order', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$recipe = [];
			// $langcode = get_current_langcode();

			$node_en = $node;
			if ($langcode != 'en'){
				// if (!$node->hasTranslation('fr'))
				if (!$node->hasTranslation($langcode))
					continue;
				// $node = $node->getTranslation('fr');
				$node = $node->getTranslation($langcode);
			}

		    $recipe['title'] =  $node->field_subtitle->value;
		    $recipe['body'] =  $node->body->value;
		    $video_field = !empty($node->field_youtube_video->value)? "field_youtube_video" : "field_recipe_video";
		    $recipe['video'] = $this->embed_video($node->{$video_field}->value);
		    if (empty($recipe['video']))
		    	continue;
		    $video_key = str_replace("https://www.youtube.com/embed/", "", $recipe['video']);
		    $recipe['key'] = $video_key;
		    $thumbnail_url = "https://img.youtube.com/vi/$video_key/maxresdefault.jpg";
		    $imagesize = getimagesize($thumbnail_url);
			if (!$imagesize){
				$thumbnail_url = "https://img.youtube.com/vi/$video_key/sddefault.jpg";
			}

		    $recipe['thumbnail'] = $thumbnail_url;
		    $recipe['nid'] = $node->id();
		    $recipes[] =$recipe;
		}
		return $recipes;
	}

	public function get_product_category_title($brand, $path){

		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_product_link', "%" .$path, 'LIKE');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;

		$tids = array_values($entity_ids);
		$taxonomy_id = $tids[0];


		$term = Term::load($taxonomy_id);
		if (empty($term))
			return '';
		return $term->field_subtitle->value;

	}

	/**
	 * $img_type can be "banner" or "background" or "mobile"
	 */

	public function get_product_category_image($brand, $path, $img_type = 'banner'){
		// @TODO: remove hardcoded array  in this function
		if (!in_array($img_type, ['banner', 'background', 'mobile']) )
			return '';

		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_product_link', "%" .$path, 'LIKE');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;

		$field_names = [
			'banner' => 'field_banner_image',
			'mobile' => 'field_banner_mobile_image',
			'background' => 'field_background_image',
		];
		$tids = array_values($entity_ids);
		$taxonomy_id = $tids[0];
		$term = Term::load($taxonomy_id);
		// print_var($term->field_product_link);
		// setValue()
		// debug_var($term->field_product_link->getValue()[0]['uri'], 1);
		$url = '';
		try{
			if (empty($term) || !$term->hasField($field_names[$img_type])){
				return '';
			}

			$entity = $term->get($field_names[$img_type])->entity; 
			if (!empty($entity) ){
				$uri = $entity->getFileUri(); // or 
				$url = file_create_url($uri);
			}
		} catch(\Exception $e){
			\Drupal::logger('debug')->info(" empty product category $brand banner image $path ", []);
		}
		// You need to create a url from the URI
		return $url;


	}

	public function fetch_products_dropthehint($brand){
		$langcode = get_current_langcode($with_prefix = false);
		$products = [];
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('field_brand', $brand);
		$query->condition('field_drop_thehint', 0 , '>');
		$query->sort('field_drop_thehint', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$lang_prefix = get_current_langcode();

		$nids = array_values($entity_ids);
		foreach ($nids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			if (empty($node))
				continue;
			$entity = [];
			$node_en = $node;
			if ($langcode != 'en'){
				if (!$node->hasTranslation('fr'))
					continue;
				$node = $node->getTranslation('fr');
			}

			$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image->target_id);

		    $entity['title'] =  $node->field_subtitle->value;
		    $entity['background_color'] =  $node->field_background_color->value;
		    $entity['body'] =  $node->body->value;
		    $entity['nid'] = $node->id();
		    $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    // $entity['link'] = "/$lang_prefix/node/" . $node->id();
		    $entity['link'] = "/$lang_prefix" . $alias;
		    $products[] = $entity;
		}
		return $products;
	}	


	public function get_time_options($brand){
		$options = [];
		if ($brand == 'tostitos'){
			$options = [
				5 => '5 minutes',
				10 => '10 minutes',
				12 => '12 minutes',
				15 => '15 minutes',
				20 => '20 minutes',
				25 => '25 minutes',
				30 => '30 minutes',
				35 => '35 minutes',
				40 => '40 minutes',
				45 => '45 minutes',
				50 => '50 minutes',
				70 => '70 minutes',
			];
		}

		return $options;

	}

	public function fetch_spring_activation_recipes( $number, $offset = 0, $lang_prefix = "en-us"){
		$recipes = [];
		$nb_extra_recipes = 12;
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('status', 1);
		$query->condition('langcode', $lang_prefix);
		// $query->condition('field_brand_website', 'Tastyrewards');
		$query->condition('field_sub_brand', 'springactivation');
		// $query->condition('field_spring_activation', 1);
		// $query->sort('field_recipe_subtitle', 'DESC');
		$query->sort('changed' , 'DESC'); 
		$query->range($offset, $number + $nb_extra_recipes);
		$entity_ids = $query->execute();
		// debug_var($entity_ids, 1);
		if (empty($entity_ids))
			return [];

		$nb_skipped_content = 0;
		foreach ($entity_ids as $nid) {
			$recipe = [];
			$node = \Drupal\node\Entity\Node::load(intval($nid)); 
			if ($lang_prefix != 'en-us' || !$node->hasTranslation('en-us') ){
				$nb_skipped_content++;
				continue;
			}

			if (empty($node->field_recipe_image->target_id)){
				$nb_skipped_content++;
				continue;
			}
				
		    $img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
		    $recipe['image_url'] = file_create_url( $img->getFileUri() );
		    $recipe_title = !empty($node->field_recipe_subtitle->value)? $node->field_recipe_subtitle->value : "";
		    $recipe['title'] =  $recipe_title;
		    // $recipe['rating'] = $this->compute_avg_rating($node);
		    // $lang_prefix = get_current_langcode();
		    $recipe['link'] = "/$lang_prefix/node/" . $nid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, "en-us");
		    $recipe['link'] = "/$lang_prefix" . $alias;
		    // $recipe['link'] = $alias;
		    $recipe['nid'] = $nid;
		    $recipe['cta'] = "View Recipe";

		    $recipe['skipped'] = $nb_skipped_content;
		    $recipes[] = $recipe;
		}

		$slice = array_slice($recipes, 0, $number);

		return $slice;
		// return $recipes;
	}

	public function fetch_spooky_snack_lab_recipes( $number, $offset = 0, $lang_prefix = "en-us"){
		$recipes = [];
		$nb_extra_recipes = 12;
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', 'spookysnacklab');
		$query->sort('changed' , 'DESC'); 
		$query->range($offset, $number + $nb_extra_recipes);
		$entity_ids = $query->execute();
		// debug_var($entity_ids, 1);
		if (empty($entity_ids))
			return [];

		$nb_skipped_content = 0;
		foreach ($entity_ids as $nid) {
			$recipe = [];
			$node = \Drupal\node\Entity\Node::load(intval($nid)); 
			if ($lang_prefix != 'en-us' || !$node->hasTranslation('en-us') ){
				$nb_skipped_content++;
				continue;
			}

			if (empty($node->field_recipe_image->target_id)){
				$nb_skipped_content++;
				continue;
			}
				
		    $img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
		    $recipe['image_url'] = file_create_url( $img->getFileUri() );
		    $recipe_title = !empty($node->field_recipe_subtitle->value)? $node->field_recipe_subtitle->value : "";
		    $recipe['title'] =  $recipe_title;
		    // $recipe['rating'] = $this->compute_avg_rating($node);
		    // $lang_prefix = get_current_langcode();
		    $recipe['link'] = "/$lang_prefix/node/" . $nid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, "en-us");
		    $recipe['link'] = "/$lang_prefix" . $alias;
		    // $recipe['link'] = $alias;
		    $recipe['nid'] = $nid;
		    $recipe['cta'] = "View Recipe";

		    $recipe['skipped'] = $nb_skipped_content;
		    $recipes[] = $recipe;
		}

		$slice = array_slice($recipes, 0, $number);

		return $slice;
		// return $recipes;
	}


	public function fetchProductFields($nid, $lang = 'en'){
		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode();
		$node = \Drupal\node\Entity\Node::load($nid); 
		if (empty($node))
			return false;

		$entity = [];
		$node_en = $node;
		if ($langcode != 'en'){
			if (!$node->hasTranslation('fr'))
				return false;
			$node = $node->getTranslation('fr');
		}
		if (!$node->getTranslation($langcode)->status->value)
			return false;

		$extra_img = "";
		$extra_img1_url = "";
		$img_alt = "";
		$bg_img = "";
		$img_url = "";
		if (!empty($node_en->field_recipe_image->target_id)){
			$img = \Drupal\file\Entity\File::load($node_en->field_recipe_image->target_id);
			$img_alt = $node->getTranslation($langcode)->field_recipe_image->alt;
			$img_url = get_translated_image_url($node,"field_recipe_image", $langcode);
		}
		if (!empty($node_en->field_extra_image1->target_id)){
			$extra_img = \Drupal\file\Entity\File::load($node_en->field_extra_image1->target_id);
			$extra_img1_url = get_translated_image_url($node,"field_extra_image1", $langcode);
		}
		if (!empty($node_en->field_background_image->target_id)){
			$bg_img = \Drupal\file\Entity\File::load($node_en->field_background_image->target_id);
		}
		
		$entity['charcuterie'] =  $node->field_charcuterie->value;
		// $entity['extraimage1_url'] = !empty($extra_img)? file_create_url( $extra_img->getFileUri()) : '';
		$entity['extraimage1_url'] = $extra_img1_url;
		$entity['backgroundimage_url'] = !empty($bg_img)? file_create_url($bg_img->getFileUri()) : '';
		$entity['title'] =  $node->field_subtitle->value;
		$entity['background_color'] =  $node->field_background_color->value;
		$entity['body'] =  $node->body->value;
		$entity['nid'] = $node->id();
		$entity['image_alt'] = $img_alt;
		$entity['image_url'] = $img_url;
		$alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		// $entity['link'] = "/$lang_prefix/node/" . $node->id();
		$entity['link'] = "/$lang_prefix" . $alias;
		return $entity;
	}

}
