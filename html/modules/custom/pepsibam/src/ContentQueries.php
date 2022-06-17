<?php

/**
 * @file
 */

use Drupal\pepsibam\CronScheduledTasks;

namespace Drupal\pepsibam;

use Drupal\pepsibam\CronScheduledTasks;
use Drupal\taxonomy\Entity\Term;

class ContentQueries{

	private $nb_recipes_per_query = 12;
	private $lang_prefix = 'en-ca';
	private $current_category_id;

	public static function instance(){
		return new ContentQueries();
	}

	public function setLangPrefix($prefix){
		$this->lang_prefix = $prefix;
	}

	public function setCategory($tid){
		$this->current_category_id = $tid;
	}

	public function get_recipes_by_search_category($tid, $offset = 0){
		$langcode = get_current_langcode(0);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('status', 1);
		$not_springactivation = $query->orConditionGroup()
				->notExists('field_spring_activation')
				->condition('field_spring_activation', 0)
				->condition('field_spring_activation',NULL,'=');
		$query->condition($not_springactivation);

		$not_brand_recipe = $query->orConditionGroup()
				->notExists('field_brand_website')
				->condition('field_brand_website', 'Tastyrewards')
				->condition('field_brand_website',NULL,'=');
		$query->condition($not_brand_recipe);


		$query->condition('langcode', $langcode);

		// $query->condition('field_search_category', $tid);
		// $query->condition('field_search_category.entity:node.nid', $tid);
		if (!empty($tid))
			$query->condition('field_search_category.entity:taxonomy_term.tid', $tid, 'IN');
		$query->range($offset, $this->nb_recipes_per_query);
		$query->sort('changed', 'DESC');
		$entity_ids = $query->execute();

		if (empty($entity_ids))
			return [];

		foreach ($entity_ids as $nid) {
			$recipe = [];
			$node = \Drupal\node\Entity\Node::load(intval($nid)); 
			if ($langcode != 'en'){
				if (!$node->hasTranslation($langcode))
					continue;
				$node = $node->getTranslation($langcode);
			}

			$recipe['img_url'] = "";
			if ($node->field_recipe_image->target_id){
		    	$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
		    	$recipe['image_url'] = file_create_url( $img->getFileUri() );
			}
		    $title = $node->getTranslation($langcode)->field_recipe_subtitle->value;
		    if (empty($title))
		    	// $title should be subtitle unless the subtitle is empty
		    	$title = $node->getTitle();
		    $recipe['title'] = $title ;
		    $recipe['cook_time'] =  $node->field_recipe_cook_time->value;
		    $recipe['body'] =  $node->body->value;
		    $recipe['rating'] = recipe_avg_rating($node);
		    // $lang_prefix = get_current_langcode();
		    $recipe['link'] = "/" . $this->lang_prefix . "/node/" . $nid;
		    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    $recipe['link'] = "/" . $this->lang_prefix . "" . $alias;
		    $recipe['cta'] = "View Recipe";
		    // $recipe['link'] = $alias;
		    $recipe['nid'] = $nid;

		    $recipes[] = $recipe;
		}

		$slice = array_slice($recipes, 0, $this->nb_recipes_per_query);

		return $slice;
	}

	private function get_recipe_fields($node, $langcode){
		$recipe = [];
		$node_en = $node;
		if ($node->hasTranslation($langcode)){
			$node = $node->getTranslation($langcode);
		}

		$recipe['image_url'] = "";
		if ($node->field_recipe_image->target_id){
			$img = \Drupal\file\Entity\File::load($node->field_recipe_image->target_id);
		    $recipe['image_url'] = file_create_url( $img->getFileUri() );
		}

	    $title = $node->field_recipe_subtitle->value;
	    if (empty($title)){
	    	$title = $node->getTitle();
	    }
	    $recipe['title'] =  $title;
	    $recipe['body'] =  truncateRichText($node->body->value);
	    $recipe['rating'] = recipe_avg_rating($node);
	    $recipe['cook_time'] =  $node->field_recipe_cook_time->value;
	    $cta = "View Recipe";
	    if ($langcode == "fr" || $langcode == "fr-ca")
	    	$cta = "Voire la recette";
	    if ($langcode == "es-us")
	    	$cta = "Ver la Receta";
	    $recipe['cta'] = $cta ;
	    $nid = $node->id();
	    // $lang_prefix = get_current_langcode();
	    $recipe['link'] = "/" . $this->lang_prefix . "/node/" . $nid;
	    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
	    $recipe['link'] = "/" . $this->lang_prefix . "" . $alias;
	    // $recipe['link'] = $alias;
	    $recipe['nid'] = $node->id();


	    return $recipe;

	}

	public function find_matched_keywords_tids($search_term, $langcode){
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'recipe_keywords');
		$query->condition('langcode', $langcode);
		$contains_term = $query->orConditionGroup()
			->condition('name', '%' . $search_term. '%', 'LIKE')
			->condition('field_synonyms', '%' . $search_term. '%', 'LIKE');
		$query->condition($contains_term);
		$entity_ids = $query->execute();

		if (empty($entity_ids))
			return [];

		return array_values($entity_ids);
	}

	public function get_recipe_keywords($node, $lang, $with_synonym = true){
		$refs = $node->getTranslation($lang)->field_recipe_keywords->referencedEntities();
		$keywords = [];
		foreach($refs as $key => $entity){
			if ($entity->hasTranslation($lang)){
				$entity = $entity->getTranslation($lang);
			}
			
			$keywords[] = $entity->name->value;
			if ($with_synonym)
				$keywords[] = $entity->field_synonyms->value;
		}
		return implode(", ", $keywords);
	}

	public function reset_all_recipes_status(){
		$updated = \Drupal::database()->update('pepsi_recipes')
          ->fields(array(
            'status' => 0,
          ))
          ->execute();
	}

	public function update_pepsi_recipes_table(){
		// Set 
		$this->reset_all_recipes_status();


		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('status', 1);
		// $query->condition('langcode', $langcode);
		$not_springactivation = $query->orConditionGroup()
				->notExists('field_spring_activation')
				->condition('field_spring_activation', 0)
				->condition('field_spring_activation',NULL,'=');
		$query->condition($not_springactivation);
		$is_not_brand_recipe = $query->orConditionGroup()
			->condition('field_brand_website', 'Tastyrewards' )
			->notExists('field_brand_website')
			->condition('field_brand_website',NULL,'=');
		$query->condition($is_not_brand_recipe);
		$entity_ids = $query->execute();
		$languages = ['en', 'fr', 'en-us', 'es-us'];
		foreach ($entity_ids as $nid) {
			$node = \Drupal\node\Entity\Node::load($nid); 
			$node_en = $node;
			foreach ($languages as $lang) {
				if (!$node->hasTranslation($lang)){
					continue;
				}

				$node = $node->getTranslation($lang);

				$keywords = $this->get_recipe_keywords($node, $lang);
				$status = $node->isPublished()? 1 : 0;

                \Drupal::database()->merge('pepsi_recipes')
				  ->insertFields(array(
				    'nid' => $nid,
				    'title' => $node->getTitle(),
				    'language' => $lang,
				    'subtitle' => strip_tags($node->field_recipe_subtitle->value),
				    'ingredients' => strip_tags($node->field_recipe_ingredients->value),
				    'changed' => $node->changed->value,
				    'keywords' => $keywords,
				    'status' => $status,
				  ))
				  ->updateFields(array(
				    'title' => $node->getTitle(),
				    'changed' => $node->changed->value,
				    'subtitle' => strip_tags($node->field_recipe_subtitle->value),
				    'ingredients' => strip_tags($node->field_recipe_ingredients->value),
				    'keywords' => $keywords,
				    'status' => $status,
				  ))
				  ->key(array('nid' => $nid , 'language' => $lang ))
				  ->execute();
				// Populate the pepsi_recipes_category table
				$refs = $node->getTranslation($lang)->field_search_category->referencedEntities();
				foreach($refs as $key => $entity){
                    \Drupal::database()->merge('pepsi_recipes_category')
				  ->insertFields(array(
				    'nid' => $nid,
				    'category_tid' => $entity->id(),
				    'language' => $lang,
				  ))
				  ->updateFields(array(
				    'category_tid' => $entity->id(),
				  ))
				  ->key(array('nid' => $nid , 'language' => $lang, 'category_tid' => $entity->id() ))
				  ->execute();
				}
			}

			// Populate the pepsi_recipes_category table
			$refs = $node_en->field_recipe_brands_category->referencedEntities();
			foreach($refs as $key => $entity){
                \Drupal::database()->merge('pepsi_recipes_brands')
			  ->insertFields(array(
			    'nid' => $nid,
			    'brand_tid' => $entity->id(),
			  ))
			  ->updateFields(array(
			    'brand_tid' => $entity->id(),
			  ))
			  ->key(array('nid' => $nid))
			  ->execute();
			}


		}
	}

	public function find_recipes($tids, $search_term, $langcode, $offset = 0){
		// CronScheduledTasks::instance()->run_every("update_recipe_tables", $hours = 8);
		$join_category = "";
		$has_category = "";
		$join_brand = "";
		$has_brand = "";
		$has_search_term = "";
		// If a field_search_category is active
		if (!empty($this->current_category_id)){
			$join_category = " inner join pepsi_recipes_category as t2 on t1.nid = t2.nid ";
			$has_category = " t2.category_tid = " . $this->current_category_id . " AND ";
		}
		// IF brand is selected
		if (!empty($tids)){
			$array_tids = implode(", ", $tids);
			$join_brand = " inner join pepsi_recipes_brands as t3 on t1.nid = t3.nid ";
			$has_brand = " t3.brand_tid IN (" . $array_tids . ") AND ";
		}

		if (!empty($search_term)){
			$search_term = strtolower($search_term);
			// $search_term = addcslashes($search_term, "'");
			$search_term = str_replace("'", "''", $search_term);
			$has_search_term = " (LOWER(t1.subtitle) LIKE '%$search_term%' OR 
									LOWER(t1.keywords) LIKE '%$search_term%' OR 
									LOWER(t1.ingredients) LIKE '%$search_term%'
								) AND ";
		}

		$limit = $this->nb_recipes_per_query;

		$sql =	" SELECT DISTINCT t1.nid, MAX(t1.changed)  from  pepsi_recipes as t1 
					$join_category  
					$join_brand
					WHERE 
					$has_category
					$has_brand
					$has_search_term
					t1.language = '$langcode'
					AND t1.status = 1
					
					group by nid
					order by max(t1.changed) DESC
					LIMIT $limit OFFSET  $offset
		";
		$query = \Drupal::database()->query($sql);
	    try{
	        $results = $query->fetchAll();
	        //log_var($sql, "sql");
	        //log_var($results, "results");
	        if (!empty($results)){
				// foreach ($nids as $nid) {
				foreach ($results as $result) {

					$node = \Drupal\node\Entity\Node::load($result->nid); 
				    $recipes[] = $this->get_recipe_fields($node, $langcode);
				}
				return $recipes;
	        }
	    } catch(\Exception $e){
	    	log_var($sql, "Recipe Query could not be run");
	    }
	    return [];
	}

	public function find_recipe_categories() {
    	// $tid = $this->get_category_tid($category);
    	$vocabulary = 'recipe_search_category';
    	$langcode = get_current_langcode($with_prefix = false);
    	$lang_prefix = get_current_langcode();
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
		$term_data = [];
		foreach ($terms as $term) {
			if ($term->depth > 0)
				// We only care about the terms with detph 0 
				continue;
			$object_term = Term::load($term->tid);
			if (!$object_term->hasTranslation($langcode)){
				continue;
			}

			$img_url = "";
			if (!empty($object_term->getTranslation($langcode)->field_category_image->target_id)){
				$img = \Drupal\file\Entity\File::load($object_term->getTranslation($langcode)->field_category_image->target_id);
	    		$img_url = file_create_url( $img->getFileUri() );
			} elseif (!empty($object_term->field_category_image->target_id)){
				$img = \Drupal\file\Entity\File::load($object_term->field_category_image->target_id);
	    		$img_url = file_create_url( $img->getFileUri() );
			}

			$title = $term->name;
			$description = $object_term->getDescription();

			if ($langcode != 'en' && $object_term->hasTranslation($langcode)){
				$term_trans = $object_term->getTranslation($langcode);
				$description = $term_trans->getDescription();
				$title = $term_trans->name->value;
			}

		 	$link = "/$lang_prefix/recipes/category/" . remove_space($title);
		 	if ($lang_prefix == 'fr-ca'){
		 		$link = "/$lang_prefix/recettes/categorie/" . remove_space($title);
		 	} elseif ($lang_prefix == 'es-us'){
		 		$link = "/$lang_prefix/recetas/categoria/" . remove_space($title);
		 	}


			$term_data[] = array(
			  'tid' => $term->tid,
			  'title' => $title,
			  'parents' => $term->parents,
			  'depth' => $term->depth,
			  'description' => $description,
			  'img_url' => $img_url,
			  'link' => $link,
			 );
		}
		// debug_var($term_data, 1);
		return $term_data;
    }

    public function find_category_by_url($basename){

		$langcode = get_current_langcode($with_prefix = false);
		$lang_prefix = get_current_langcode();
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'recipe_search_category');
		$query->condition('field_basename', $basename);
		// $query->range(0, 3);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$tids = array_values($entity_ids);
		$entity = [];
		$term = Term::load($tids[0]);
	   	$term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
	    $entity['title'] =  $term->name->value;
	    $entity['description'] =  $term->getDescription();

		return $entity;

    }

	public function get_terms_by_vocabulary($vocabulary, $langcode = ""){
		if (empty($langcode))
			$langcode = get_current_langcode($with_prefix = false);
		$entities = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
		$terms = [];
		foreach ($entities as $key => $entity) {
			$term = Term::load($entity->tid);
			if (!$term->hasTranslation($langcode))
				continue;
	   		// $term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
	   		$term = $term->getTranslation($langcode);

			if (intval($term->get('status')->getValue()[0]['value']) == 0){
				continue;
			}

			$terms[] = [
				'tid' => $entity->tid,
				'name' => $term->name->value,
			];
		}
		return $terms;
	}
}

