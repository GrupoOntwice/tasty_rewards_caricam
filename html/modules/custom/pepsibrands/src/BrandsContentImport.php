<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;

use Drupal\taxonomy\Entity\Term;

class BrandsContentImport{

	private $slash = DIRECTORY_SEPARATOR;
	private $brand;

	public static function instance(){
		return new BrandsContentImport();
	}

	public function set_brand($brand_name){
		$this->brand = $brand_name;
	}

	public function import_content_csv($filename, $method, $langcode = 'en'){
	    $row = 1;
	    $import_count = 0;
	    $headers = [];
	    $en_filename = str_replace('_fr.csv', '_en.csv', $filename);
	    $has_translation = false;
	    if (($handle = fopen($filename, "r")) !== FALSE) {
	    	if ($langcode == 'fr'){
	    		$handle_en = fopen($en_filename, "r");
	    		if ($handle_en !== FALSE)
	    			$has_translation = true;
	    	}

	    	while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
	        	$num = count($data);
	    		if ($has_translation && $langcode == 'fr' ){
	    			$data_en = fgetcsv($handle_en, 2000, ",");
	    		}

	        	if ($row == 1){
	          		$headers = array_flip($data);
	    			if ($has_translation && $langcode == 'fr'){
	    				// Adds a new column ID_en to the headers with value number of col
	          			$headers['ID_en'] = count($headers);
	    			}
	        	} else {
	    			if ($has_translation && $langcode == 'fr'){
	    				$data[count($data)] = $data_en[0];
	    			}
	        		// if ($row++ == 2)
	        		// 	continue;
	        		try{
	        			$result = $this->$method($data, $headers, $langcode);
	        			if ($result)
	        				$import_count++;
	        		} catch(\Exception $e){
	        			// \Drupal::logger('drush')->info("Drush error " . print_r($e) , []);
	        		}
	        		// $this->create_tostitos_recipe($data, $headers);
	        		// \Drupal::logger("general")->info(" pepsibam_cron has run all functions ", []);
	        	}
	        	// echo $data[$headers['Ingredients']];
				echo "Row = $row \n";
	        	// if ($row >= 3)
	        	// 	debug_var($data, 1);
	        	$row++;
	      }
	      fclose($handle);
		  \Drupal::logger('drush')->info(" $row content have been imported via $method " , []);
		  \Drupal::logger('csv_import')->info(" $import_count / $row content have been imported via $method " , []);

	    } else {
			\Drupal::logger('drush')->info(" The CSV file $filename does not exist "  , []);

	    }
	}

	public function add_node_translation($node){
		if ($node->hasTranslation('fr'))
			return $node->getTranslation('fr');

		return $node->addTranslation('fr');
	}

	public function add_term_translation($term, $lang = 'fr'){
		if ($term->hasTranslation($lang))
			return $term = $term->getTranslation($lang);

		return $term->addTranslation($lang);
	}

	public function sanitize_text($text){
		$sanitized = str_replace("’", "'", $text);
		return $sanitized;
	}

	public function embed_youtube_link($youtube_url) {
		if (empty($youtube_url))
			return '';
		
	    return preg_replace(
	        "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
	        "https://www.youtube.com/embed/$2",
	        $youtube_url
	    );
	}

	/**
	 * This assumes all images to be added as node field are already 
	 * located in the sites\default\files\2020-10 directory
	 * @param  [type] $image_name [description]
	 * @return [type]             [description]
	 */
	public function prepare_image_file($image_name, $is_test = false){
		$image_name = trim($image_name);
		if ($is_test)
			$image_name = "test-img.png";

		$old_brands = ['cheetos', 'lays', 'tostitos', 'bare', 'otep', 'crispyminis', 'missvickies'];



		if (empty($image_name))
			return '';
		
		$folder = "2021-04";
		// $folder = "popcorners";
		$folder = $this->brand;
		if ( in_array($this->brand, $old_brands) ){
			$folder = "2020-10";
		}

		$env =  \Drupal\Core\Site\Settings::get("environment");
		if ($env == 'dev'){
			// $folder = "2020-10";
		}

		$base_dir = getcwd(); // C:\wamp\www\pepsi-tastyrewards\new-tastyrewards
  		$images_dir = $base_dir . $this->slash . "sites" . $this->slash ."default" . $this->slash ."files" . $this->slash . "$folder";
  		$uri = "public://$folder/" . $image_name;
  		// $uri = file_build_uri($images_dir  . $this->slash . $image_name);
		// C:\wamp\www\pepsi-tastyrewards\new-tastyrewards\sites\default\files\2018-10
		$data = file_get_contents($images_dir  . $this->slash . $image_name);
		$exist_replace = \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE;
		
		$file = file_save_data($data, $uri, $exist_replace);
		// debug_var($file, 1);
		// $file = file_save_data($data, "public://rec1.png", FILE_EXISTS_REPLACE);
		return $file;

	}


	public function create_tastyrewards_recipekeywords($data, $headers, $langcode){
		$node = \Drupal\node\Entity\Node::load($data[$headers['nid']]); 
		if (empty($node)){
			echo "\n empty node " . $data[$headers['nid']];
			return;
		}
		if (!$node->hasTranslation($langcode)){
			echo "\n No translation node " . $data[$headers['nid']];
			return;
		}

		$node = $node->getTranslation($langcode);
		$keywords = explode("|", $data[$headers['Keywords']]);
		$keywords = array_unique($keywords);


		$trans_node = null;
		// @TODO: Translate this in French too
		$translated_lang = 'fr';
		if ($langcode == 'en-us')
			$translated_lang = 'es-us';

		if ($node->hasTranslation($translated_lang) ){
			$trans_node = $node->getTranslation($translated_lang);
			$node->getTranslation($translated_lang)->field_recipe_keywords = [];
		}

		$node->field_recipe_keywords = [];

		foreach ($keywords as $keyword) {
			$keyword = trim($keyword);
			$tids = $this->load_recipe_keyword_term($keyword);
			
			if (!empty($tids)){
				$tid = $tids[0];
			} else {
				echo "\n Missing keyword ";
				debug_var($keyword);
				$original_id = custom_hash($keyword);
				// Create new recipe_keywords if it didn't exist initally.
				$term = $this->load_by_original_id($original_id, 'recipe_keywords');
				if (empty($term)){
					$term = \Drupal\taxonomy\Entity\Term::create([ 
						'vid' => 'recipe_keywords', 
				    	'name'       => trim($keyword),
					]);

				}

				$term = $this->add_term_translation($term, $langcode);

				// We should generate a unique field_original_id here.

				$term->field_original_id->value = $original_id;
				$term->save();
				$tid = $term->id();

			}

			$node->field_recipe_keywords[] = ['target_id' => $tid];
			if ($trans_node)
				$trans_node->field_recipe_keywords[] = ['target_id' => $tid];

		}

		$node->save();
		if ($trans_node)
			$trans_node->save();

	}

	public function create_tastyrewards_recipe($data, $headers, $langcode = 'en'){

		$node = \Drupal\node\Entity\Node::load($data[$headers['nid']]); 
		if (empty($node))
			return;
		$categories = explode("|", $data[$headers['Category']]);
		$categories = array_unique($categories);
		$sub_categories = explode("|", $data[$headers['SubCategory']]);
		$sub_categories = array_unique($sub_categories);


		$trans_node = null;
		// @TODO: Translate this in French too
		$translated_lang = 'fr';
		if ($langcode == 'en-us')
			$translated_lang = 'es-us';

		if ($node->hasTranslation($translated_lang) ){
			$trans_node = $node->getTranslation($translated_lang);
		}

		foreach ($categories as $category) {
			$tids = $this->load_category_term($category);
			if (!empty($tids)){
				foreach ($tids as $tid) {
					$node->field_search_category[] = ['target_id' => $tid];
					if ($trans_node)
						$trans_node->field_search_category[] = ['target_id' => $tid];
				}
			}
		}

		foreach ($sub_categories as $sub_category) {
			$tids = $this->load_category_term($sub_category);
			if (!empty($tids)){
				foreach ($tids as $tid) {
					$node->field_search_category[] = ['target_id' => $tid];
					if ($trans_node)
						$trans_node->field_search_category[] = ['target_id' => $tid];
				}
			}
		}
		$node->save();
		if ($trans_node)
			$trans_node->save();





	}

	public function load_category_term($category_title){
		// recipe_search_category
		

		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'recipe_search_category');
		$query->condition('field_basename', "%" . remove_space($category_title) . "%", "LIKE");
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$tids = array_values($entity_ids);
		return $tids;
	}

	public function load_recipe_keyword_term($title){
		// recipe_search_category
		

		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'recipe_keywords');
		$query->condition('name', "" . $title, "LIKE");
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$tids = array_values($entity_ids);
		return $tids;
	}

	

	public function load_by_original_id($entity_id, $entity_type, $brand = ""){
		$entity = null;
		switch ($entity_type) {
			case 'occasion':
			case 'brands_occasions':
				$entity = $this->load_occasion_by_original_id($entity_id, $brand);
				// return $occasion;
				break;

			case 'product_categories':
				$entity = $this->load_product_category_by_original_id($entity_id, $brand);
				break;
			case 'product_group':
				$entity = $this->load_product_group_by_original_id($entity_id, $brand);
				break;

			case 'recipe_search_category':
				$entity = $this->load_recipe_category_by_original_id($entity_id);
				break;

			case 'recipe':
				$entity = $this->load_recipe_by_original_id($entity_id, $brand);

				break;

			case 'videos':
				$entity = $this->load_videos_by_original_id($entity_id, $brand);

				break;

			case 'product':
				$entity = $this->load_product_by_original_id($entity_id, $brand);

				break;
			// case 'home_page_carousel2':
				// $entity = $this->load_carousel_by_original_id($entity_id, $brand);

			case 'recipe_keywords':
				$entity = $this->load_recipe_keywords_by_original_id($entity_id);
				break;
			
			default:
				$entity = $this->load_entity_type_by_original_id($entity_type, $entity_id);
				// code...
				// return null;
				break;
		}
		return $entity;
	}

	public function load_carousel_by_original_id($id){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'home_page_carousel2');
		$query->condition('field_original_id', $id);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node;
		} else {
			return '';
		}
	}


	public function load_product_by_original_id($id, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'product');
		$query->condition('field_original_id', $id);
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node;
		} else {
			return '';
		}
	}


	public function load_node_by_original_id($id, $brand, $entity_type){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', $entity_type);
		$query->condition('field_original_id', $id);
		if (!empty($brand)){
			$query->condition('field_brand', $brand);
		}
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node;
		} else {
			return '';
		}
	}


	public function load_videos_by_original_id($id, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'videos');
		$query->condition('field_original_id', $id);
		if (!empty($brand)){
			$query->condition('field_brand', $brand);
		}
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node;
		} else {
			return '';
		}
	}


	public function load_entity_type_by_original_id($entity_type, $entity_id){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', $entity_type);
		$query->condition('field_original_id', $entity_id);

		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node;
		} else {
			return '';
		}
	}


	public function load_recipe_by_original_id($id, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_original_id', $id);
		if (!empty($brand)){
			$query->condition('field_brand_website', $brand);
		}
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node;
		} else {
			return '';
		}
	}


	public function load_product_group_by_original_id($id, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_group');
		$query->condition('field_original_id', $id);
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$tids = array_values($entity_ids);
		if (count($tids) >= 1 ){
			$term = Term::load($tids[0]);
			return $term;
		} else {
			return '';
		}
	}


	public function load_product_category_by_original_id($id, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_original_id', $id);
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$tids = array_values($entity_ids);
		if (count($tids) >= 1 ){
			$term = Term::load($tids[0]);
			return $term;
		} else {
			return '';
		}
	}

	public function load_recipe_keywords_by_original_id($id){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'recipe_keywords');
		$query->condition('field_original_id', $id);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;


		$tids = array_values($entity_ids);
		if (count($tids) >= 1 ){
			$term = Term::load($tids[0]);
			return $term;
		} else {
			return 0;
		}
	}


	public function load_recipe_category_by_original_id($id){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'recipe_search_category');
		$query->condition('field_original_id', $id);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return 0;


		$tids = array_values($entity_ids);
		if (count($tids) >= 1 ){
			$term = Term::load($tids[0]);
			return $term;
		} else {
			return 0;
		}
	}


	public function load_occasion_by_original_id($id, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'brands_occasions');
		$query->condition('field_original_id', $id);
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return '';


		$tids = array_values($entity_ids);
		if (count($tids) >= 1 ){
			// $node = \Drupal\node\Entity\Node::load($tids[$nb_items - 1]); 
			$term = Term::load($tids[0]);
			return $term;
		} else {
			return '';
		}
		// $body = $node->getTranslation($langcode)->get('body')->getValue();
		// $body = $node->body->value;
		// return $body;
	}

	public function get_product_categoryID_by_title($title, $brand){
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_subtitle', $title);
		$query->condition('field_brand', $brand);
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


	public function extract_recipe_ids($str){
		preg_match_all("/\\[(.*?)\\]/", $str, $matches); 
		if (count($matches) < 2)
			return [];
		$nids = [];
		foreach ($matches[1] as $key => $id) {
			$node = $this->load_by_original_id($id, 'recipe');
			if (empty($node))
				continue;
			$nids[] = $node->id();
		}
		return $nids;
	}

	public function create_tostitos_ingredients($data, $headers, $langcode = 'en'){
		$title = $data[$headers['Name']];

		// product categories matching  
		if ($langcode == 'en'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'ingredients');
			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'ingredients', 
			    	'name'       => "Tostitos - " . $title,
				]);
			}

			$term->field_subtitle->value = $title;
			$term->field_brand->value = "Tostitos";
			$term->field_original_id->value = $data[$headers['ID']];
		} else{
			// The original ID should be the english version
			try{
				// @TODO: Handle the case when there is french content 
				// that don't have English translation. i.e when there are more
				// rows in french then in English in the csv
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'occasion');
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Content does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			$term_fr = $this->add_term_translation($term);
			// $term_fr = $term->addTranslation('fr');
			$term_fr->body->value = $data[$headers['Description']];
			$term_fr->name->value = 'Tostitos - ' . $title;
			$term_fr->field_subtitle->value = $title;
			$term_fr->field_original_id->value = $data[$headers['ID']];

			$term_fr->save();
			return;
		}

		$term->save();
	}


	public function create_tostitos_occasion($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']]);
		$title = $data[$headers['Name']];
		$recipe_ids = $this->extract_recipe_ids($data[$headers['Recipes']]);

		// product categories matching  
		if ($langcode == 'en'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'brands_occasions');
			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'brands_occasions', 
			    	'name'       => "Tostitos - " . $title,
				]);
			}

			$term->description->value = $data[$headers['Description']];
			$term->field_subtitle->value = $title;
			$term->field_brand->value = "Tostitos";
			$term->field_original_id->value = $data[$headers['ID']];

			if (!empty($file) ){
				$term->field_occasion_image->target_id = $file->id();
				$term->field_occasion_image->alt = $title;
				$term->field_occasion_image->title = $title;
			}



			// $term = \Drupal\taxonomy\Entity\Term::create([ 
			//     'name'       => "Tostitos - " . $title,
			//     'field_subtitle' => $title,
			// 	'field_brand' => 'Tostitos',  
			// 	'vid' => 'brands_occasions', 
			// 	'body' => $data[$headers['Description']],
			// 	'field_occasion_image' => [
			//        	'target_id' => $file->id(),
		 //    	   	'alt' => $title,
			//        	'title' => $title,
			//     ],
			// 	'field_original_id' => $data[$headers['ID']],
			// ]);

			foreach ($recipe_ids as $key => $nid) {
				$term->field_occasion_recipes[] = ['target_id' => $nid];
			}
		} else{
			// The original ID should be the english version
			try{
				// @TODO: Handle the case when there is french content 
				// that don't have English translation. i.e when there are more
				// rows in french then in English in the csv
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'occasion');
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Content does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			$term_fr = $this->add_term_translation($term);
			// $term_fr = $term->addTranslation('fr');
			$term_fr->description->value = $data[$headers['Description']];
			$term_fr->name->value = 'Tostitos - ' . $title;
			$term_fr->field_subtitle->value = $title;
			$term_fr->field_occasion_image->target_id = $file->id();
			$term_fr->field_occasion_image->alt = $title;
			$term_fr->field_occasion_image->title = $title;
			$term_fr->field_original_id->value = $data[$headers['ID']];

			foreach ($recipe_ids as $key => $nid) {
				$term_fr->field_occasion_recipes[] = ['target_id' => $nid];
			}

			$term_fr->save();
			return;
			// die("fr");
		}

		$term->save();

	}

	public function create_stacys_collections($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']]);
		$title = $data[$headers['Name']];
		$recipe_ids = $this->extract_recipe_ids($data[$headers['Recipes']]);
		$brand = strcase_brand('stacys');
		// product categories matching  
		if ($langcode == 'en'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'brands_occasions', 'stacys');
			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'brands_occasions', 
			    	'name'       => "stacys - " . $title,
				]);
			}

			$term->description->value = $data[$headers['Description']];
			$term->field_subtitle->value = $title;
			$term->field_brand->value = $brand;
			$term->field_original_id->value = $data[$headers['ID']];
			if (!empty($file) ){
				$term->field_occasion_image->target_id = $file->id();
				$term->field_occasion_image->alt = $title;
				$term->field_occasion_image->title = $title;
			}



			// $term = \Drupal\taxonomy\Entity\Term::create([ 
			//     'name'       => "stacys - " . $title,
			//     'field_subtitle' => $title,
			// 	'field_brand' => 'stacys',  
			// 	'vid' => 'brands_occasions', 
			// 	'body' => $data[$headers['Description']],
			// 	'field_occasion_image' => [
			//        	'target_id' => $file->id(),
		 //    	   	'alt' => $title,
			//        	'title' => $title,
			//     ],
			// 	'field_original_id' => $data[$headers['ID']],
			// ]);
			$term->field_occasion_recipes = [];
			foreach ($recipe_ids as $key => $nid) {
				$term->field_occasion_recipes[] = ['target_id' => $nid];
			}
		} else{
			// The original ID should be the english version
			try{
				// @TODO: Handle the case when there is french content 
				// that don't have English translation. i.e when there are more
				// rows in french then in English in the csv
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'occasion', $brand);
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Content does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			$term_fr = $this->add_term_translation($term);
			// $term_fr = $term->addTranslation('fr');
			$term_fr->description->value = $data[$headers['Description']];
			$term_fr->name->value = 'stacys - ' . $title;
			$term_fr->field_subtitle->value = $title;
			$term_fr->field_occasion_image->target_id = $file->id();
			$term_fr->field_occasion_image->alt = $title;
			$term_fr->field_occasion_image->title = $title;
			$term_fr->field_original_id->value = $data[$headers['ID']];

			foreach ($recipe_ids as $key => $nid) {
				$term_fr->field_occasion_recipes[] = ['target_id' => $nid];
			}

			$term_fr->save();
			return;
			// die("fr");
		}

		$term->save();

	}


	public function add_sup_tag($text, $strip_tags = true){
		if (empty($text))
			return "";

		if ($strip_tags)
			$text = strip_tags($text);
		$text = str_replace("<sup>®</sup>", "®", $text);
		$text = str_replace("®", "<sup>®</sup>", $text);
		$text = str_replace("<sup>_TM_</sup>","_TM_", $text);
		$text = str_replace("™", "<sup>TM</sup>", $text);
		// $text = str_replace("_TM_", "<sup>™</sup>", $text);
		$text = str_replace("_TM_", "<sup>TM</sup>", $text);
		$text = str_replace("🅪", "<sup>MC</sup>", $text);
		$text = str_replace("<sup>_MC_</sup>", "_MC_", $text);
		$text = str_replace("_MC_", "<sup>MC</sup>", $text);
		$text = str_replace("&reg;", "<sup>®</sup>", $text);
		$text = str_replace("CrunchTM ", "Crunch<sup>TM</sup>", $text);
		return $text;
	}

	public function is_valid_shape($shape){
		$shape = strtolower($shape);
		$entityManager = \Drupal::service('entity_field.manager');
		$fields = $entityManager->getFieldStorageDefinitions('node', 'product');
		$options = options_allowed_values($fields['field_shape']);
		$keys_options = array_keys($options);
		if (!in_array($shape, $keys_options))
			return false;
		return true;

	}

	public function create_product($brand, $data, $headers, $langcode, $has_extraimage = false, $test = 0){
		// $test_image = 1;
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		$brand = strcase_brand($brand);
		if ($has_extraimage){
			$file_extra1 = $this->prepare_image_file($data[$headers['Image Aux 1']], $test_image);
			$file_extra2 = $this->prepare_image_file($data[$headers['Image Aux 2']], $test_image);

		}
		if ($data[$headers['Featured Image']]){
			$file_featured = $this->prepare_image_file($data[$headers['Featured Image']], $test);
		}

		if ($data[$headers['Large Image']]){
			$file_extra1 = $this->prepare_image_file($data[$headers['Large Image']], $test);
		}

		$striptags = 1;
		if (strtolower($brand) == 'quaker'){
			$striptags = 0;
		}
		$title =  $data[$headers['Name']];
		if (empty($title))
			return 0;
		
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'product', $brand);
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'product',
					    'title'       => "$brand - " . strip_tags($title),
					]);
				}

				$node->body->format = 'full_html';
				
				$node->body->value = $this->add_sup_tag($data[$headers['Description']], $_striptags = 0);
				$node->title->value = "$brand - " . strip_tags($title);
				$node->field_subtitle->value = $this->add_sup_tag($title, 1);
				$node->field_brand->value =  "$brand";
				$node->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node->field_product_alert->value = $data[$headers['Alert']];

				if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				} else {
					$node->field_is_featured = 0;
				}


				$node->field_portion->value = $data[$headers['Portion']];

				$node->field_nutrition->format = 'full_html';
				$node->field_nutrition->value = $this->table_format_nutrition(strtolower($brand), $data[$headers['Nutrition Facts']]);

				if (!empty($data[$headers['Order']])){
					$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
					$node->field_weight->value = $weight;
				}

				if (!empty($data[$headers['Vegan Disclaimer']])){
					$node->field_charcuterie->value = $data[$headers['Vegan Disclaimer']];
				}

				if (!empty($data[$headers['Background Color']])){
					$node->field_background_color->value = $data[$headers['Background Color']];
				}

				if (!empty($data[$headers['Category']])){
					$node->field_product_category->target_id = $this->find_product_category_id($brand, $data[$headers['Category']]  );
				}
				if (!empty($data[$headers['Claims']])){
					// $claims = $this->explode_claims($data[$headers['Claims']], "|");
					$claims = $this->explode_claims($data[$headers['Claims']]);
					$node->field_claim->setValue($claims);
				}

				$node->field_original_id->value = $data[$headers['ID']];

				if (!empty($file) ){
					$node->field_recipe_image->target_id = $file->id();
					$node->field_recipe_image->alt = $title;
					$node->field_recipe_image->title = $title;
				}
					

				if (!empty($file_extra1) ){
					$node->field_extra_image1->target_id = $file_extra1->id();
					$node->field_extra_image1->alt = $title;
					$node->field_extra_image1->title = $title;
				}

				if (!empty($file_featured) ){
					$node->field_extra_image1->target_id = $file_featured->id();
					$node->field_extra_image1->alt = $title;
					$node->field_extra_image1->title = $title;
				}

				if (!empty($file_extra2) ){
					$node->field_extra_image2->target_id = $file_extra2->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				// $node->field_product_category->target_id = $product_categories[$data[$headers['Category']] ];


			} else {
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'product', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;

				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']], $_striptags = 0);


				$node_fr->title->value = "$brand - " . $title;
				// $node_fr->field_subtitle->value =  $title;
				$node_fr->field_subtitle->value = $this->add_sup_tag($title, 0);
				$node_fr->field_brand->value =  "$brand";
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node_fr->field_product_alert->value = $data[$headers['Alert']];
				$node_fr->field_portion->value = $data[$headers['Portion']];
				$node_fr->field_nutrition->value = $this->table_format_nutrition(strtolower($brand), $data[$headers['Nutrition Facts']] );
				$node_fr->field_original_id->value = $data[$headers['ID']];

				if (!empty($data[$headers['Vegan Disclaimer']])){
					$node_fr->field_charcuterie->value = $data[$headers['Vegan Disclaimer']];
				}

				if (!empty($file) ){
					$node_fr->field_recipe_image->target_id = $file->id();
					$node_fr->field_recipe_image->alt = $title;
					$node_fr->field_recipe_image->title = $title;
				}

				$node_fr->save();

				return;
			}


	  	$node->save();
	  	\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			log_var($data, " log data");
			// \Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_otep_product($data, $headers, $langcode = 'en'){
		$this->create_product("Otep", $data, $headers, $langcode, $extra_image = 1);
	}

	public function create_doritos_product($data, $headers, $langcode = 'en'){
		$this->create_product("Doritos", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_smartfood_product($data, $headers, $langcode = 'en'){
		$this->create_product("smartfood", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_capncrunch_product($data, $headers, $langcode = 'en'){
		$this->create_product("capncrunch", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_popcorners_product($data, $headers, $langcode = 'en'){
		$this->create_product("popcorners", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_sunchips_product($data, $headers, $langcode = 'en'){
		$this->create_product("sunchips", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_stacys_product($data, $headers, $langcode = 'en'){
		$this->create_product("stacys", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_quaker_product($data, $headers, $langcode = 'en'){
		$this->create_product("quaker", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}

	public function create_ruffles_product($data, $headers, $langcode = 'en'){
		$this->create_product("ruffles", $data, $headers, $langcode, $extra_image = 0, $test = 0);
	}


	public function create_cheetos_product($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']], $test = 0);
		$file_large = $this->prepare_image_file($data[$headers['Large Image']], $test = 0);
		$title =  strip_tags($data[$headers['Name']]);
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'product', 'cheetos');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'product',
					    'title'       => "Cheetos - " . $title,
					]);
				}

				$node->body->format = 'full_html';
				$node->body->value = $this->add_sup_tag($data[$headers['Description']]);
				$node->title->value = "Cheetos - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  "Cheetos";
				$node->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node->field_product_alert->value = $data[$headers['Alert']];

				if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				}


				$node->field_portion->value = $data[$headers['Portion']];

				$node->field_nutrition->format = 'full_html';
				$node->field_nutrition->value = $this->table_format_nutrition('cheetos', $data[$headers['Nutrition Facts']]);

				if (!empty($data[$headers['Order']])){
					$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
					$node->field_weight->value = $weight;
				}

				if (!empty($data[$headers['Category']])){
					$node->field_product_category->target_id = $this->find_product_category_id('cheetos', $data[$headers['Category']]  );
				}

				$node->field_original_id->value = $data[$headers['ID']];

				if (!empty($file) ){
					$node->field_recipe_image->target_id = $file->id();
					$node->field_recipe_image->alt = $title;
					$node->field_recipe_image->title = $title;
				}
					

				if (!empty($file_large) ){
					$node->field_extra_image2->target_id = $file_large->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				// $node->field_product_category->target_id = $product_categories[$data[$headers['Category']] ];


			} else {
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'product', 'cheetos');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;

				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']]);
				$node_fr->title->value = "Cheetos - " . $title;
				// $node_fr->field_subtitle->value =  $title;
				$node_fr->field_subtitle->value = $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "Cheetos";
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node_fr->field_product_alert->value = $data[$headers['Alert']];
				$node_fr->field_portion->value = $data[$headers['Portion']];
				$node_fr->field_nutrition->value = $this->table_format_nutrition('cheetos', $data[$headers['Nutrition Facts']] );
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->save();

				return;
			}


	  	$node->save();
	  	\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}

	}
	public function create_tostitos_product($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']]);
		$file_large = $this->prepare_image_file($data[$headers['Large Image']]);
		$title =  strip_tags($data[$headers['Name']]);
		// @TODO: Fix the hardcoded values
		$product_categories = [
			'Tostitos® Chips' => $this->get_product_categoryID_by_title($this->add_sup_tag('TOSTITOS® Tortilla Chips'), 'tostitos'), //82,
			'Simply Tostitos®' => $this->get_product_categoryID_by_title($this->add_sup_tag('Simply TOSTITOS® Tortilla Chips'), 'tostitos'), //83,
			'Salsa and Dips' => $this->get_product_categoryID_by_title($this->add_sup_tag('TOSTITOS®  Salsa and Dips'), 'tostitos'), //84,
		];
		// product categories matching  
		// 
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'product');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'product',
					    'title'       => "Tostitos - " . $title,
					]);
				}

				$node->body->format = 'full_html';
				$node->body->value = $this->add_sup_tag($data[$headers['Description']]);
				$node->title->value = "Tostitos - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  "Tostitos";
				$node->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node->field_product_alert->value = $data[$headers['Alert']];
				if ($this->is_valid_shape($data[$headers['Shape']])){
					$node->field_shape->value = strtolower($data[$headers['Shape']]);
				} else {
					$node->field_shape->value = null;
				}
				// if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				// }
				// In tositos csv claims are separated by EOL character
				// wherease in Lays, they are separated by "|", which is why
				// they are handled differently for the two brands
				// $node->field_claims->value = $this->add_separator_claims($data[$headers['Claims']], "|");
				$claims = $this->explode_claims($data[$headers['Claims']]);
				$node->field_claim->setValue($claims);

				$node->field_portion->value = $data[$headers['Portion']];

				$node->field_nutrition->format = 'full_html';
				$node->field_nutrition->value = $this->table_format_nutrition('tostitos', $data[$headers['Nutrition Facts']]);

				$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
				$node->field_weight->value = $weight;

				$node->field_original_id->value = $data[$headers['ID']];

				$node->field_recipe_image->target_id = $file->id();
				$node->field_recipe_image->alt = $title;
				$node->field_recipe_image->title = $title;

				if (!empty($file_large) ){
					$node->field_extra_image2->target_id = $file_large->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				$node->field_product_category->target_id = $product_categories[$data[$headers['Category']] ];

				//     'field_subtitle' => $title,

				//     'langcode' => $langcode,
				//     'field_brand' => 'Tostitos',
				//     'field_product_category' => [
				//        	'target_id' => $product_categories[$data[$headers['Category']] ],
				//     ],
				//     'field_recipe_image' => [
				//        	'target_id' => $file->id(),
			 //    	   	'alt' => $title,
				//        	'title' => $title,
				// 	    ],
				// 	'body' => $data[$headers['Description']],
				// 	'field_recipe_ingredients' => $data[$headers['Ingredients']],
				// 	'field_product_alert' => $data[$headers['Alert']],
				// 	'field_portion' => $data[$headers['Portion']],
				// 	'field_nutrition' => $data[$headers['Nutrition Facts']],
				// 	'field_original_id' => $data[$headers['ID']],
				// ]);

			} else {
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'product');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;

				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']]);
				$node_fr->title->value = "Tostitos - " . $title;
				// $node_fr->field_subtitle->value =  $title;
				$node_fr->field_subtitle->value = $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "Tostitos";
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node_fr->field_product_alert->value = $data[$headers['Alert']];
				$node_fr->field_portion->value = $data[$headers['Portion']];
				$node_fr->field_nutrition->value = $this->table_format_nutrition('tostitos', $data[$headers['Nutrition Facts']] );
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->save();

				return;
			}


	  	$node->save();
	  	\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_tostitos_videos($data, $headers, $langcode = 'en'){

		// \Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$this->create_videos("Tostitos", $data, $headers, $langcode);
	}

	public function create_doritos_videos($data, $headers, $langcode = 'en'){
		$this->create_videos("Doritos", $data, $headers, $langcode);
	}


	public function create_quaker_videos($data, $headers, $langcode = 'en'){
		$this->create_videos("quaker", $data, $headers, $langcode);
	}

	public function create_crispyminis_videos($data, $headers, $langcode = 'en'){

		$this->create_videos("Crispyminis", $data, $headers, $langcode);
	}

	public function create_smartfood_videos($data, $headers, $langcode = 'en'){

		$this->create_videos("smartfood", $data, $headers, $langcode);
	}

	public function create_lays_videos($data, $headers, $langcode = 'en'){

		// \Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$this->create_videos("Lays", $data, $headers, $langcode);
	}

	public function create_videos($brand, $data, $headers, $langcode = 'en'){
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'videos', $brand);
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'videos',
					    'title'       => "$brand - " . $title,
					]);
				}

				$node->body->value = $data[$headers['Description']];
				$node->title->value = "$brand - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  strcase_brand($brand);
				// $node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['YouTube URL']]);
				$node->field_youtube_video->value = $this->embed_youtube_link($data[$headers['YouTube URL']]);
				$node->field_original_id->value = $data[$headers['ID']];
				if ($headers['Order'])
					$node->field_order->value = $data[$headers['Order']];

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'videos', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node)){
					// Here the ID from the english version doesn't exist. We check if there is 
					// a video with french-only content (no translation)
					$node = $this->load_by_original_id($data[$headers['ID']], 'videos', $brand);
					if (empty($node))
						return;
				}
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "$brand - " . $title;
				$node_fr->field_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "$brand";
				$node_fr->field_youtube_video->value = $this->embed_youtube_link($data[$headers['YouTube URL']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_order->value = $data[$headers['Order']];

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}



	public function create_quaker_years($data, $headers, $langcode = 'en'){
		$title = strip_tags($data[$headers['Year']]);
		$brand = "quaker";
		$file = $this->prepare_image_file($data[$headers['Image']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'custom_content', $brand);
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'custom_content',
					    'title'       => "$brand - " . $title,
					]);
				}

				$node->body->format = 'full_html';
				$node->body->value = $this->add_sup_tag($data[$headers['Description']], $_striptags = 0);

				$node->title->value = "$brand - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  strcase_brand($brand);
				$node->field_original_id->value = $data[$headers['ID']];

				if (!empty($file)){
					$node->field_image->target_id = $file->id();
					$node->field_image->alt = $title;
					$node->field_image->title = $title;
				}

				$node->field_year->value = $data[$headers['Year']];
				$node->field_custom_content_type->value = "moments";

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'custom_content', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']], $_striptags = 0);
				$node_fr->body->format = 'full_html';
				$node_fr->title->value = "$brand - " . $title;
				$node_fr->field_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "$brand";
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_order->value = $data[$headers['Year']];
				$node_fr->field_custom_content_type->value = "moments";

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}

	}

	public function create_pearlmilling_years($data, $headers, $langcode = 'en'){
		$test = 1;
		$title = strip_tags($data[$headers['Year']]);
		$brand = "pearlmilling";
		$file = $this->prepare_image_file($data[$headers['Image']], $test);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'custom_content', $brand);
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'custom_content',
					    'title'       => "$brand - " . $title,
					]);
				}

				$node->body->format = 'full_html';
				$node->body->value = $this->add_sup_tag($data[$headers['Description']], $_striptags = 0);

				$node->title->value = "$brand - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  strcase_brand($brand);
				$node->field_original_id->value = $data[$headers['ID']];

				if (!empty($file)){
					$node->field_image->target_id = $file->id();
					$node->field_image->alt = $title;
					$node->field_image->title = $title;
				}

				$node->field_atc_order->value = $data[$headers['Order']];

				// $node->field_year->value = $data[$headers['Year']];
				$node->field_custom_content_type->value = "history";

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'custom_content', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']], $_striptags = 0);
				$node_fr->body->format = 'full_html';
				$node_fr->title->value = "$brand - " . $title;
				$node_fr->field_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "$brand";

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}

	}

	public function create_quaker_oatcarousel($data, $headers, $langcode = 'en'){
		$title = strip_tags($data[$headers['Title']]);
		$brand = "quaker";
		$entity_type = $data[$headers['Type']];
		$test = 0;
		$field_description = [
			'product' => 'field_charcuterie',
			'product_categories' => 'field_description',
		];

		try{
			if ($langcode == 'en'){
				$entity = $this->load_by_original_id($data[$headers['ID']], $entity_type , $brand);
				if (empty($entity)){
					return;
				}
				$field = $field_description[$entity_type];
				$entity->$field->format = 'full_html';
				$entity->$field->value = $data[$headers['Description']];
				$entity->field_carousels->setValue('power_of_oats');

				$entity->save();
				return;
			} else{
				
				// No French version for this page
				return;
			}


		  	$entity->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}


	public function create_quaker_powerofoats($data, $headers, $langcode = 'en'){
		$title = strip_tags($data[$headers['Title']]);
		$brand = "quaker";
		$test = 0;
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		if ($data[$headers['Icon']]){
			$file_icon = $this->prepare_image_file($data[$headers['Icon']], $test);
		}

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'custom_content', $brand);
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'custom_content',
					    'title'       => "$brand - " . $title,
					]);
				}

				$node->field_custom_content_type->value = "oats";

				$node->body->format = 'full_html';
				$node->body->value = $data[$headers['Description']];
				$node->title->value = "$brand - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  strcase_brand($brand);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_atc_order->value = $data[$headers['Order']];
				$node->field_portion->value = $data[$headers['Portion']];

				if (!empty($file)){
					$node->field_image->target_id = $file->id();
					$node->field_image->alt = $title;
					$node->field_image->title = $title;
				}

				if (!empty($file_icon)){
					$node->field_extra_image2->target_id = $file_icon->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}


				$node->save();
				return;
			} else{
				
				// No French version for this page
				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}

	}

	public function create_tostitos_related_recipes($data, $headers, $langcode = 'en'){
		// Related Recipes
		$data[$headers['Related Recipes']] = trim($data[$headers['Related Recipes']]);
		if (empty($data[$headers['Related Recipes']]))
			return;
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe');
				if (empty($node)){
					return;
				}
				$original_ids = explode(",", $data[$headers['Related Recipes']]);

				// field_test is the field for related recipes
				// debug_var($original_ids);
				$found_recipe_count = 0;
				$related_ids = [];
				foreach ($original_ids as $key => $original_id) {
					$node_recipe = $this->load_by_original_id($original_id, 'recipe');
					if (empty($node_recipe))
						continue;
					$node->field_test[] = ['target_id' => $node_recipe->id()];
					$found_recipe_count++;
					$related_ids[] = $node_recipe->id();
				}

				$node->save();
				debug_var($original_ids);
				if ($found_recipe_count){
					debug_var($node->id());
					debug_var($related_ids);
					debug_var($node->getTitle(), 0);
				}
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe');
					// This should be the FR version instead
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}


	public function find_related_recipes_by_link($link){
		$recipes = [];
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_original_link', "%" . $link, "LIKE");
		$entity_ids = $query->execute();
		// debug_var($entity_ids, 1);
		if (empty($entity_ids))
			return [];

		// debug_var($entity_ids, 1);
		$nids = array_values($entity_ids);
		return $nids[0];
	}

	public function create_tostitos_recipe($data, $headers, $langcode = 'en'){

		$file = $this->prepare_image_file($data[$headers['Image']]);
		if (empty($file)){
			\Drupal::logger('content-import')->info(" image missing for ID " . $data[$headers['ID']], []);
			return 0;
		}
		\Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'recipe',
					    'title'       => "Tostitos - " . $title,
					]);
				}

				$node->body->value = $data[$headers['Description']];
				$node->title->value = "Tostitos - " . $title;
				$node->field_recipe_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value =  "Tostitos";
				$node->field_brand_website->value =  "Tostitos";
				$node->field_recipe_how_to_make->format = 'full_html';
				$node->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']]);
				$node->field_recipe_ingredients->format = 'full_html';
				$node->field_recipe_ingredients->value = $this->format_ingredients('tostitos',$data[$headers['Ingredients']] );
				$node->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node->field_total_rating->value = $data[$headers['Num Ratings']];
				$node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_original_link->value = $data[$headers['Link']];

				if (!empty($file)){
					$node->field_recipe_image_detail->target_id = $file->id();
					$node->field_recipe_image_detail->alt = $title;
					$node->field_recipe_image_detail->title = $title;
				}

				// Related recipes
				$related_recipes_links = explode(",", $data[$headers['Related Recipes']]);
				$related_recipes = [];
				$node->field_test = [];
				foreach ($related_recipes_links as $link) {
					$nid = $this->find_related_recipes_by_link($link);
					if (empty($nid))
						continue;
					$related_recipes[] = $nid;
				}
				// debug_var($node->id());
				// debug_var($related_recipes);
				if (!empty($related_recipes)){
					foreach ($related_recipes as  $recipe_id) {
						$node->field_test[] = ['target_id' => $recipe_id];
					}
				}

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "Tostitos - " . $title;
				$node_fr->field_recipe_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "Tostitos";
				$node_fr->field_brand_website->value =  "Tostitos";
				$node_fr->field_recipe_how_to_make->format = 'full_html';
				$node_fr->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']] );
				$node_fr->field_recipe_ingredients->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $this->format_ingredients('tostitos',$data[$headers['Ingredients']] );
				$node_fr->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node_fr->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node_fr->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node_fr->field_total_rating->value = $data[$headers['Num Ratings']];
				$node_fr->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_original_link->value = $data[$headers['Link']];


				// images should already be in the EN version
				
				// if (!empty($file)){
				// 	$node_fr->field_recipe_image_detail->target_id = $file->id();
				// 	$node_fr->field_recipe_image_detail->alt = $title;
				// 	$node_fr->field_recipe_image_detail->title = $title;
				// }

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_crispyminis_recipe($data, $headers, $langcode = 'en'){

		$file = $this->prepare_image_file($data[$headers['Image']]);
		if (empty($file)){
			\Drupal::logger('content-import')->info(" image missing for ID " . $data[$headers['ID']], []);
			return 0;
		}
		\Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'crispyminis');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'recipe',
					    'title'       => "Crispyminis - " . $title,
					]);
				}

				$node->body->format = 'full_html';
				$node->body->value = $this->add_sup_tag($data[$headers['Description']]);
				$node->title->value = "CrispyMinis - " . $title;
				$node->field_recipe_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value = strcase_brand("CrispyMinis");
				$node->field_brand_website->value =  strcase_brand("CrispyMinis");
				$node->field_recipe_how_to_make->format = 'full_html';
				$node->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']]);
				$node->field_recipe_ingredients->format = 'full_html';
				$node->field_recipe_ingredients->value = $this->format_ingredients('crispyminis',$data[$headers['Ingredients']] );
				$node->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node->field_total_rating->value = $data[$headers['Num Ratings']];
				$node->field_order->value = $data[$headers['Order']];
				// $node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_original_link->value = $data[$headers['Link']];

				if (!empty($file)){
					$node->field_recipe_image_detail->target_id = $file->id();
					$node->field_recipe_image_detail->alt = $title;
					$node->field_recipe_image_detail->title = $title;
				}

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe', 'crispyminis');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']]);
				$node_fr->title->value = "Crispyminis - " . $title;
				$node_fr->field_recipe_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "CrispyMinis";
				$node_fr->field_brand_website->value =  "CrispyMinis";
				$node_fr->field_recipe_how_to_make->format = 'full_html';
				$node_fr->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']] );
				$node_fr->field_recipe_ingredients->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $this->format_ingredients('crispyminis',$data[$headers['Ingredients']] );
				$node_fr->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node_fr->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node_fr->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node_fr->field_total_rating->value = $data[$headers['Num Ratings']];
				// $node_fr->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_original_link->value = $data[$headers['Link']];


				// images should already be in the EN version
				
				// if (!empty($file)){
				// 	$node_fr->field_recipe_image_detail->target_id = $file->id();
				// 	$node_fr->field_recipe_image_detail->alt = $title;
				// 	$node_fr->field_recipe_image_detail->title = $title;
				// }

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_buynow_links($data, $headers, $langcode = 'en'){
		$brand = $data[$headers['brand']];
		$brand_machinename = $data[$headers['key']];
		$query = \Drupal::entityQuery('node');
		$query->condition('status', 1);
		$query->condition('type', 'brand');
		$query->condition('langcode', $langcode);
		$brand_condition = $query->orConditionGroup()
										->condition('field_machinename', $brand_machinename)
										->condition('field_machinename', remove_space($brand))
										->condition('title',$brand, 'CONTAINS' );
		$query->condition($brand_condition);
		$entity_ids = $query->execute();
		if (!empty($entity_ids)){
			$entity_ids = array_values($entity_ids);
			$nid = $entity_ids[0];
			$node = \Drupal\node\Entity\Node::load($nid);
			if ($node->hasTranslation($langcode)){
				$node = $node->getTranslation($langcode);
			}
			// debug_var($nid . " -- " . $node->getTitle() . "    ---   \n");
			$node->field_machinename->value = $brand_machinename;

			$url_en = $data[$headers['url_en']];
			$url_fr = '';
			if (isset($headers['url_fr']))
				$url_fr = $data[$headers['url_fr']];

			if (isset($headers['snacks_key'])){
				$node->field_snacks_com_key->value = $data[$headers['snacks_key']];
			}

			$node->field_brand_iframe_url->value = $url_en;
			$node->save();
			if ($node->hasTranslation('fr') && !empty($url_fr) ){
				$node_fr = $node->getTranslation('fr');
				$node_fr->field_brand_iframe_url->value = $url_fr;
				$node_fr->field_machinename->value = $brand_machinename;
				$node_fr->save();

			}



		}

	}

	public function create_cheetos_recipe($data, $headers, $langcode = 'en'){

		$file = $this->prepare_image_file($data[$headers['Image']]);
		$file_square = $this->prepare_image_file($data[$headers['Mobile Image']]);
		$file_made_with = $this->prepare_image_file($data[$headers['Image Madewith']]);
		$file_made_with2 = $this->prepare_image_file($data[$headers['Image Madewith2']]);

		if (empty($file)){
			\Drupal::logger('content-import')->info(" image missing for ID " . $data[$headers['ID']], []);
			return 0;
		}
		\Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'cheetos');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'recipe',
					    'title'       => "Cheetos - " . $title,
					]);
				}

				$node->body->value = $data[$headers['Description']];
				$node->title->value = "Cheetos - " . $title;
				$node->field_recipe_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value = strcase_brand("Cheetos");
				$node->field_brand_website->value =  strcase_brand("Cheetos");
				$node->field_recipe_how_to_make->format = 'full_html';
				$node->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']]);
				$node->field_recipe_ingredients->format = 'full_html';
				$node->field_recipe_ingredients->value = $this->format_ingredients('cheetos',$data[$headers['Ingredients']] );
				$node->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node->field_total_rating->value = $data[$headers['Num Ratings']];
				$node->field_order->value = $data[$headers['Order']];
				// $node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_original_link->value = $data[$headers['Link']];

				if (!empty($file)){
					$node->field_recipe_image_detail->target_id = $file->id();
					$node->field_recipe_image_detail->alt = $title;
					$node->field_recipe_image_detail->title = $title;
				}

				if (!empty($file_square)){
					$node->field_recipe_image->target_id = $file_square->id();
					$node->field_recipe_image->alt = $title;
					$node->field_recipe_image->title = $title;
				}

				if (!empty($file_made_with)){
					$node->field_extra_image1->target_id = $file_made_with->id();
					$node->field_extra_image1->alt = $title;
					$node->field_extra_image1->title = $title;
				}

				if (!empty($file_made_with2)){
					$node->field_extra_image2->target_id = $file_made_with2->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				}

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe', 'cheetos');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "Cheetos - " . $title;
				$node_fr->field_recipe_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "Cheetos";
				$node_fr->field_brand_website->value =  "Cheetos";
				$node_fr->field_recipe_how_to_make->format = 'full_html';
				$node_fr->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']] );
				$node_fr->field_recipe_ingredients->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $this->format_ingredients('cheetos',$data[$headers['Ingredients']] );
				$node_fr->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node_fr->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node_fr->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node_fr->field_total_rating->value = $data[$headers['Num Ratings']];
				// $node_fr->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_original_link->value = $data[$headers['Link']];


				// images should already be in the EN version
				
				// if (!empty($file)){
				// 	$node_fr->field_recipe_image_detail->target_id = $file->id();
				// 	$node_fr->field_recipe_image_detail->alt = $title;
				// 	$node_fr->field_recipe_image_detail->title = $title;
				// }

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_capncrunch_recipe($data, $headers, $langcode = 'en'){
		$test = 0;
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		$file_square = $this->prepare_image_file($data[$headers['Mobile Image']], $test);
		$file_made_with = $this->prepare_image_file($data[$headers['Image Madewith']], $test);

		if (empty($file)){
			\Drupal::logger('content-import')->info(" image missing for ID " . $data[$headers['ID']], []);
			return 0;
		}
		\Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'capncrunch');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'recipe',
					    'title'       => "capncrunch - " . $title,
					]);
				}
				log_var($node->id(), " capncrunch recipe");

				$node->title->value = "capncrunch - " . $title;
				$node->body->value = $data[$headers['Description']];
				$node->field_recipe_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value = strcase_brand("capncrunch");
				$node->field_brand_website->value =  strcase_brand("capncrunch");
				$node->field_recipe_how_to_make->format = 'full_html';
				$node->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']]);
				$node->field_recipe_ingredients->format = 'full_html';
				$node->field_recipe_ingredients->value = $this->format_ingredients('capncrunch',$data[$headers['Ingredients']] );
				$node->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				// $node->field_rating_voters->value = $data[$headers['Average Ratings']];
				// $node->field_total_rating->value = $data[$headers['Num Ratings']];
				$node->field_order->value = $data[$headers['Order']];
				// $node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_background_color->value = $data[$headers['Background Colour']];
				// $node->field_original_link->value = $data[$headers['Link']];

				if (!empty($file)){
					$node->field_recipe_image_detail->target_id = $file->id();
					$node->field_recipe_image_detail->alt = $title;
					$node->field_recipe_image_detail->title = $title;
				}

				if (!empty($file_square)){
					$node->field_recipe_image->target_id = $file_square->id();
					$node->field_recipe_image->alt = $title;
					$node->field_recipe_image->title = $title;
				}

				if (!empty($file_made_with)){
					$node->field_extra_image1->target_id = $file_made_with->id();
					$node->field_extra_image1->alt = $title;
					$node->field_extra_image1->title = $title;
				}

				if (!empty($file_made_with2)){
					$node->field_extra_image2->target_id = $file_made_with2->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				}

				$node->save();
				return;
			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe', 'capncrunch');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node))
					return;
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "capncrunch - " . $title;
				$node_fr->field_recipe_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "capncrunch";
				$node_fr->field_brand_website->value =  "capncrunch";
				$node_fr->field_recipe_how_to_make->format = 'full_html';
				$node_fr->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']] );
				$node_fr->field_recipe_ingredients->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $this->format_ingredients('capncrunch',$data[$headers['Ingredients']] );
				$node_fr->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node_fr->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				// $node_fr->field_rating_voters->value = $data[$headers['Average Ratings']];
				// $node_fr->field_total_rating->value = $data[$headers['Num Ratings']];
				// $node_fr->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				// $node_fr->field_original_link->value = $data[$headers['Link']];

				$node_fr->field_background_color->value = $data[$headers['Background Colour']];

				// images should already be in the EN version
				
				if (!empty($file)){
					$node_fr->field_recipe_image_detail->target_id = $file->id();
					$node_fr->field_recipe_image_detail->alt = $title;
					$node_fr->field_recipe_image_detail->title = $title;
				}

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			// \Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_quaker_recipe($data, $headers, $langcode = 'en'){
		$test = 0;

		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		// $file_square = $this->prepare_image_file($data[$headers['Mobile Image']]);
		// $file_made_with = $this->prepare_image_file($data[$headers['Image Madewith']]);
		// $file_made_with2 = $this->prepare_image_file($data[$headers['Image Madewith2']]);

		if (empty($file) && $langcode == 'en'){
			\Drupal::logger('content-import')->info(" image missing for ID " . $data[$headers['ID']], []);
			return 0;
		}
		// \Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'quaker');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'recipe',
					    'title'       => "Quaker - " . $title,
					]);
				}

				$node->body->value = $this->add_sup_tag($data[$headers['Description']], false);
				$node->title->value = "Quaker - " . $title;
				$node->field_recipe_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value = strcase_brand("quaker");
				$node->field_brand_website->value =  strcase_brand("quaker");
				$node->field_recipe_how_to_make->format = 'full_html';
				$node->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']]);
				$node->field_recipe_ingredients->format = 'full_html';
				$node->field_recipe_ingredients->value = $this->format_quaker_ingredients($data[$headers['Ingredients']] );
				$node->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node->field_rating_voters->value = $data[$headers['Num Ratings']];
				$total_rating = $data[$headers['Num Ratings']] * $data[$headers['Average Ratings']];
				$node->field_total_rating->value = $total_rating;
				$node->field_order->value = $data[$headers['Order']];
				$node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_original_link->value = $data[$headers['Link']];
				$node->field_recipe_filter->value = $data[$headers['Categories']];
				$node->field_portion->value = $data[$headers['Servings']];

				if (!empty($file)){
					$node->field_recipe_image_detail->target_id = $file->id();
					$node->field_recipe_image_detail->alt = $title;
					$node->field_recipe_image_detail->title = $title;
				}

				if (!empty($file_square)){
					$node->field_recipe_image->target_id = $file_square->id();
					$node->field_recipe_image->alt = $title;
					$node->field_recipe_image->title = $title;
				}

				if (!empty($file_made_with)){
					$node->field_extra_image1->target_id = $file_made_with->id();
					$node->field_extra_image1->alt = $title;
					$node->field_extra_image1->title = $title;
				}

				if (!empty($file_made_with2)){
					$node->field_extra_image2->target_id = $file_made_with2->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				}

				// Related products
				// Related recipes
				$related_product_ids = explode(",", $data[$headers['Related Products']]);
				$related_products = [];
				$node->field_related_products = [];
				foreach ($related_product_ids as $product_id) {
					$product = $this->load_by_original_id($product_id, 'product', 'quaker');

					if (empty($product))
						continue;
					$related_products[] = $product->id();
				}

				if (!empty($related_products)){
					foreach ($related_products as  $recipe_id) {
						$node->field_related_products[] = ['target_id' => $recipe_id];
					}
				}

				$node->save();
				return;
			} else{
				try{
					// We assume here that the FR ID has a matching EN ID
					$node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'quaker');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node)){
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe', 'quaker');
					return;
				}
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "quaker - " . $title;
				$node_fr->field_recipe_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "quaker";
				$node_fr->field_brand_website->value =  "quaker";
				$node_fr->field_recipe_how_to_make->format = 'full_html';
				$node_fr->field_recipe_how_to_make->value = $this->format_ingredients('',$data[$headers['Directions']] );
				$node_fr->field_recipe_ingredients->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $this->format_quaker_ingredients($data[$headers['Ingredients']] );
				$node_fr->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node_fr->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node_fr->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node_fr->field_total_rating->value = $data[$headers['Num Ratings']];
				$node_fr->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_original_link->value = $data[$headers['Link']];
				$node_fr->field_recipe_filter->value = $data[$headers['Categories']];
				$node_fr->field_portion->value = $data[$headers['Servings']];
				$node_fr->field_order->value = $data[$headers['Order']];


				// images should already be in the EN version
				
				// if (!empty($file)){
				// 	$node_fr->field_recipe_image_detail->target_id = $file->id();
				// 	$node_fr->field_recipe_image_detail->alt = $title;
				// 	$node_fr->field_recipe_image_detail->title = $title;
				// }

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			// \Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_stacys_recipe($data, $headers, $langcode = 'en'){
		$test = 0;

		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		// $file_square = $this->prepare_image_file($data[$headers['Mobile Image']]);
		// $file_made_with = $this->prepare_image_file($data[$headers['Image Madewith']]);
		// $file_made_with2 = $this->prepare_image_file($data[$headers['Image Madewith2']]);

		if (empty($file) && $langcode == 'en'){
			\Drupal::logger('content-import')->info(" image missing for ID " . $data[$headers['ID']], []);
			return 0;
		}
		// \Drupal::logger('content-import')->info(" image file ID " . $file->id(), []);
		$title = strip_tags($data[$headers['Title']]);

		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'stacys');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'recipe',
					    'title'       => "Stacys - " . $title,
					]);
				}

				$node->body->value = $this->add_sup_tag($data[$headers['Description']], false);
				$node->title->value = "Stacys - " . $title;
				$node->field_recipe_subtitle->value = $this->add_sup_tag($title);
				$node->field_brand->value = strcase_brand("stacys");
				$node->field_brand_website->value =  strcase_brand("stacys");
				$node->field_recipe_how_to_make->format = 'full_html';
				$node->field_recipe_how_to_make->value = $this->format_ingredients('stacys',$data[$headers['Directions']]);
				$node->field_recipe_ingredients->format = 'full_html';
				$node->field_recipe_ingredients->value = $this->format_ingredients('stacys', $data[$headers['Ingredients']] );
				$node->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node->field_rating_voters->value = $data[$headers['Num Ratings']];
				$total_rating = $data[$headers['Num Ratings']] * $data[$headers['Average Ratings']];
				$node->field_total_rating->value = $total_rating;
				$node->field_order->value = $data[$headers['Order']];
				$node->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_original_link->value = $data[$headers['Link']];
				$node->field_recipe_filter->value = $data[$headers['Categories']];
				$node->field_portion->value = $data[$headers['Servings']];

				if (!empty($file)){
					$node->field_recipe_image_detail->target_id = $file->id();
					$node->field_recipe_image_detail->alt = $title;
					$node->field_recipe_image_detail->title = $title;
				}

				if (!empty($file_square)){
					$node->field_recipe_image->target_id = $file_square->id();
					$node->field_recipe_image->alt = $title;
					$node->field_recipe_image->title = $title;
				}

				if (!empty($file_made_with)){
					$node->field_extra_image1->target_id = $file_made_with->id();
					$node->field_extra_image1->alt = $title;
					$node->field_extra_image1->title = $title;
				}

				if (!empty($file_made_with2)){
					$node->field_extra_image2->target_id = $file_made_with2->id();
					$node->field_extra_image2->alt = $title;
					$node->field_extra_image2->title = $title;
				}

				if (!empty($data[$headers['Featured']])){
					$node->field_is_featured = $data[$headers['Featured']];
				}

				// Related products
				// Related recipes
				$related_product_ids = explode(",", $data[$headers['Related Products']]);
				$related_products = [];
				$node->field_related_products = [];
				foreach ($related_product_ids as $product_id) {
					$product = $this->load_by_original_id($product_id, 'product', 'stacys');

					if (empty($product))
						continue;
					$related_products[] = $product->id();
				}

				if (!empty($related_products)){
					foreach ($related_products as  $recipe_id) {
						$node->field_related_products[] = ['target_id' => $recipe_id];
					}
				}

				$node->save();
				return;
			} else{
				try{
					// We assume here that the FR ID has a matching EN ID
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'recipe', 'stacys');
					// $node = $this->load_by_original_id($data[$headers['ID']], 'recipe', 'stacys');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node)){
					return;
				}
				
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);

				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "stacys - " . $title;
				$node_fr->field_recipe_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "stacys";
				$node_fr->field_brand_website->value =  "stacys";
				$node_fr->field_recipe_how_to_make->format = 'full_html';
				$node_fr->field_recipe_how_to_make->value = $this->format_ingredients('stacys',$data[$headers['Directions']] );
				$node_fr->field_recipe_ingredients->format = 'full_html';
				$node_fr->field_recipe_ingredients->value = $this->format_ingredients('stacys',$data[$headers['Ingredients']] );
				$node_fr->field_recipe_prep_time->value = $data[$headers['Prep Time']];
				$node_fr->field_recipe_cook_time->value = $data[$headers['Cook Time']];
				$node_fr->field_rating_voters->value = $data[$headers['Average Ratings']];
				$node_fr->field_total_rating->value = $data[$headers['Num Ratings']];
				$node_fr->field_recipe_video->value = $this->embed_youtube_link($data[$headers['Video']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->field_original_link->value = $data[$headers['Link']];
				$node_fr->field_recipe_filter->value = $data[$headers['Categories']];
				$node_fr->field_portion->value = $data[$headers['Servings']];


				// images should already be in the EN version
				
				// if (!empty($file)){
				// 	$node_fr->field_recipe_image_detail->target_id = $file->id();
				// 	$node_fr->field_recipe_image_detail->alt = $title;
				// 	$node_fr->field_recipe_image_detail->title = $title;
				// }

				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			// \Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}


	public function create_lays_product_categories($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']]);
		$file_background = $this->prepare_image_file($data[$headers['Background Image']]);
		$file_banner = $this->prepare_image_file($data[$headers['Banner Image']]);
		$file_banner_mobile = $this->prepare_image_file($data[$headers['Banner Mobile']]);
		$title = strip_tags($data[$headers['Name']]);

		// product categories matching  
		if ($langcode == 'en'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories');

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'product_categories', 
			    	'name'       => "Lays - " . $title,
				]);
			}

			$term->field_subtitle->value = $this->add_sup_tag($title);
			$term->field_brand->value = "Lays";
			$term->field_product_category_image->target_id = $file->id();
			$term->field_product_category_image->alt = $title;
			$term->field_product_category_image->title = $title;
			$term->field_original_id->value = $data[$headers['ID']];
			$term->field_order->value = $data[$headers['Order']];
			$term->field_background_color->value = $data[$headers['Background Colour']];

			// print_var($term->field_product_link);
			$term->field_product_link->setValue([
				'uri' =>  "internal:" . $data[$headers['Link Uri']],
				'title' =>   $data[$headers['Link Title']],
			]); 
			// $term->field_product_link->title = $data[$headers['Link Title']];

			// field_banner_mobile_image field_banner_image
			if (!empty($file_banner) ){
				$term->field_banner_image->target_id = $file_banner->id();
				$term->field_banner_image->alt = $title;
				$term->field_banner_image->title = $title;
			}

			if (!empty($file_banner_mobile) ){
				$term->field_banner_mobile_image->target_id = $file_banner_mobile->id();
				$term->field_banner_mobile_image->alt = $title;
				$term->field_banner_mobile_image->title = $title;
			}

			if (!empty($file_background) ){
				$term->field_background_image->target_id = $file_background->id();
				$term->field_background_image->alt = $title;
				$term->field_background_image->title = $title;
			}

		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'product_categories');
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Product Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			// $term_fr = $term->addTranslation('fr');
			$term_fr = $this->add_term_translation($term);
			$term_fr->name->value = 'Lays - ' . $title;
			$term_fr->field_subtitle->value = $this->add_sup_tag($title);
			$term_fr->field_brand->value = "Lays";
			// Images are not translated
			// $term_fr->field_product_category_image->target_id = $file->id();
			// $term_fr->field_product_category_image->alt = $title;
			// $term_fr->field_product_category_image->title = $title;
			$term_fr->field_original_id->value = $data[$headers['ID']];
			$term_fr->field_product_link->setValue([
				'uri' =>  "internal:" . $data[$headers['Link Uri']],
				'title' =>   $data[$headers['Link Title']],
			]); 
			

			$term_fr->save();
			return;
			// die("fr");
		}

		$term->save();

	}


	public function create_tostitos_product_categories($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']]);
		$title = strip_tags($data[$headers['Name']]);

		// product categories matching  
		if ($langcode == 'en'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories');

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'product_categories', 
			    	'name'       => "Tostitos - " . $title,
				]);
			}

			$term->field_subtitle->value = $this->add_sup_tag($title);
			$term->field_brand->value = "Tostitos";
			$term->field_product_category_image->target_id = $file->id();
			$term->field_product_category_image->alt = $title;
			$term->field_product_category_image->title = $title;
			$term->field_original_id->value = $data[$headers['ID']];
			$term->field_order->value = $data[$headers['Order']];
			$term->description->value = $this->add_sup_tag($data[$headers['Description']]);

			// print_var($term->field_product_link);
			$term->field_product_link->setValue([
				'uri' =>  "internal:" . $data[$headers['Link Uri']],
				'title' =>   $data[$headers['Link Title']],
			]); 
			// $term->field_product_link->title = $data[$headers['Link Title']];

			

		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'product_categories');
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Product Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			// $term_fr = $term->addTranslation('fr');
			$term_fr = $this->add_term_translation($term);
			$term_fr->name->value = 'Tostitos - ' . $title;
			$term_fr->field_subtitle->value = $this->add_sup_tag($title);
			$term_fr->field_brand->value = "Tostitos";
			$term_fr->description->value = $data[$headers['Description']];
			// Images are not translated
			// $term_fr->field_product_category_image->target_id = $file->id();
			// $term_fr->field_product_category_image->alt = $title;
			// $term_fr->field_product_category_image->title = $title;
			$term_fr->field_original_id->value = $data[$headers['ID']];
			$term_fr->field_product_link->setValue([
				'uri' =>  "internal:" . $data[$headers['Link Uri']],
				'title' =>   $data[$headers['Link Title']],
			]); 
			

			$term_fr->save();
			return;
			// die("fr");
		}

		$term->save();

	}

	public function create_missvickies_product_categories($data, $headers, $langcode = 'en'){
		$file = $this->prepare_image_file($data[$headers['Image']], 0);
		$title = strip_tags($data[$headers['Name']]);
		$brand = strcase_brand("MissVickies");
		// product categories matching  
		if ($langcode == 'en'){

			$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories', $brand);

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'product_categories', 
			    	'name'       => "MissVickies - " . $title,
				]);
			}
			$term->field_subtitle->value = $this->add_sup_tag($title);
			$term->field_brand->value = strcase_brand("MissVickies");
			$term->field_product_category_image->target_id = $file->id();
			$term->field_product_category_image->alt = $title;
			$term->field_product_category_image->title = $title;
			$term->field_original_id->value = $data[$headers['ID']];
			// $term->field_order->value = $data[$headers['Order']];
			// $term->description->value = $this->add_sup_tag($data[$headers['Description']]);

			// $term->field_product_link->title = $data[$headers['Link Title']];

			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}
			

		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'product_categories', $brand);
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Product Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			// $term_fr = $term->addTranslation('fr');
			$term_fr = $this->add_term_translation($term);
			$term_fr->name->value = 'MissVickies - ' . $title;
			$term_fr->field_subtitle->value = $this->add_sup_tag($title);
			$term_fr->field_brand->value = strcase_brand("MissVickies");
			// Images are not translated
			// $term_fr->field_product_category_image->target_id = $file->id();
			// $term_fr->field_product_category_image->alt = $title;
			// $term_fr->field_product_category_image->title = $title;
			$term_fr->field_original_id->value = $data[$headers['ID']];
			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term_fr->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}

			$term_fr->save();
			return;
			// die("fr");
		}

		$term->save();

	}

	public function create_crispyminis_product_categories($data, $headers, $langcode = 'en'){
		$test = 0;
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		$file_hover = $this->prepare_image_file($data[$headers['Image Hover']], $test);
		$title = strip_tags($data[$headers['Name']]);
		$brand = strcase_brand("crispyminis");
		// product categories matching  
		if ($langcode == 'en'){

			$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories', $brand);

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'product_categories', 
			    	'name'       => "CrispyMinis - " . $title,
				]);
			}
			$term->field_subtitle->value = $this->add_sup_tag($title);
			$term->field_brand->value = strcase_brand("CrispyMinis");
			$term->field_product_category_image->target_id = $file->id();
			$term->field_product_category_image->alt = $title;
			$term->field_product_category_image->title = $title;
			$term->field_original_id->value = $data[$headers['ID']];
			$term->field_order->value = $data[$headers['Order']];
			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}

			if (!empty($file) ){
				$term->field_product_category_image->target_id = $file->id();
				$term->field_product_category_image->alt = $title;
				$term->field_product_category_image->title = $title;
			}

			if (!empty($file_hover) ){
				$term->field_background_image->target_id = $file_hover->id();
				$term->field_background_image->alt = $title;
				$term->field_background_image->title = $title;
			}

		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'product_categories', $brand);
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Product Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			// $term_fr = $term->addTranslation('fr');
			$term_fr = $this->add_term_translation($term);
			$term_fr->name->value = 'CrispyMinis - ' . $title;
			$term_fr->field_subtitle->value = $this->add_sup_tag($title);
			$term_fr->field_brand->value = strcase_brand("CrispyMinis");
			$term_fr->field_original_id->value = $data[$headers['ID']];
			
			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term_fr->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}			
			$term_fr->save();
			return;
		}

		$term->save();

	}

	public function create_grabsnack_prizes($data, $headers, $langcode = 'en'){
		$test_image = 0;
		if (!empty($data[$headers['Image']]) || $test_image){
			$file = $this->prepare_image_file($data[$headers['Image']], $test_image);
		}

		$title =  strip_tags($data[$headers['Title']]);
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'prizes');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'prizes',
					    'title'       =>  $title,
					]);
				}

				$node->title->value = $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_prize_type->value = "instant_prize";


				// if (!empty($data[$headers['Order']])){
				// 	$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
				// 	$node->field_weight->value = $weight;
				// }

				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_quantity->value = $data[$headers['Total Quantity']];
				$node->field_quantity_per_week->value = $data[$headers['QTY per week']];
				$node->field_prize_value->value = $data[$headers['Value']];

				if (!empty($file) ){
					$node->field_prize_image->target_id = $file->id();
					$node->field_prize_image->alt = $title;
					$node->field_prize_image->title = $title;
				}
	  			$node->save();

	  			$node_fr = $this->add_node_translation($node);
	  			$node_fr->title->value = strip_tags($data[$headers['Title FR']]);;
				$node_fr->field_subtitle->value = $this->add_sup_tag($node_fr->title->value);
				$node_fr->save();


			}


	  	\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_hockey_prizes($data, $headers, $langcode = 'en'){
		$test_image = 0;
		if (!empty($data[$headers['Image']]) || $test_image){
			$file = $this->prepare_image_file($data[$headers['Image']], $test_image);
		}

		$title =  strip_tags($data[$headers['Title']]);
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'prizes');
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'prizes',
					    'title'       =>  $title,
					]);
				}

				$node->title->value = $title;
				$node->field_subtitle->value = $this->add_sup_tag($title);
				$node->field_prize_type->value = "instant_prize";


				// if (!empty($data[$headers['Order']])){
				// 	$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
				// 	$node->field_weight->value = $weight;
				// }

				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_quantity->value = $data[$headers['Total Quantity']];
				$node->field_quantity_per_week->value = $data[$headers['QTY per week']];
				$node->field_prize_value->value = $data[$headers['Value']];

				if (!empty($file) ){
					$node->field_prize_image->target_id = $file->id();
					$node->field_prize_image->alt = $title;
					$node->field_prize_image->title = $title;
				}
	  			$node->save();

	  			$node_fr = $this->add_node_translation($node);
	  			$node_fr->title->value = strip_tags($data[$headers['Title FR']]);;
				$node_fr->field_subtitle->value = $this->add_sup_tag($node_fr->title->value);
				$node_fr->save();


			}


	  	\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}


	public function create_smartfood_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("smartfood", $data, $headers, $langcode, $test = 0);
	}

	public function create_sunchips_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("sunchips", $data, $headers, $langcode, $test = 0);
	}

	public function create_stacys_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("stacys", $data, $headers, $langcode, $test = 0);
	}

	public function create_cheetos_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("cheetos", $data, $headers, $langcode, $test = 0);
	}

	public function create_ruffles_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("ruffles", $data, $headers, $langcode, $test = 0);
	}

	public function create_quaker_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("quaker", $data, $headers, $langcode, $test = 0);
	}

	public function _add_product_group($term, $data, $headers, $langcode){
		$brand = $term->field_brand->value;
		$group_id = $data[$headers['Group ID']];
		$group_name = $data[$headers['Group']];
		$term_group = $this->load_by_original_id($group_id, 'product_group', $brand);

		if (empty($term_group)){
			$term_group = \Drupal\taxonomy\Entity\Term::create([ 
				'vid' => 'product_group', 
		    	// 'name'       => "$brand - " . $group_name,
			]);
		}

		if ($term_group->hasTranslation($langcode)){
			$term_group =  $term_group->getTranslation($langcode);
		} else {
			$term_group = $term_group->addTranslation($langcode);
		}


		$term_group->name->value = "$brand - " . $group_name;
		$term_group->field_subtitle->value = $this->add_sup_tag($group_name);
		$term_group->field_brand->value = strcase_brand($brand);
		$term_group->field_basename->value = convert_title_to_url($group_name);

		$term_group->field_original_id->value = $data[$headers['Group ID']];
		if (!empty($data[$headers['Group Order']])){
			$term_group->field_order->value = $data[$headers['Group Order']];
		}
		$term_group->save();
		return $term_group->id();
	}

	public function create_product_categories($brand, $data, $headers, $langcode, $test = 0){
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		// $file_hover = $this->prepare_image_file($data[$headers['Image Hover']], $test);
		$title = strip_tags($data[$headers['Name']]);
		$brand = strcase_brand($brand);
		$brand_lwrcase = strtolower($brand);
		// product categories matching  
		if ($langcode == 'en'){

			$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories', $brand);

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'product_categories', 
			    	'name'       => "$brand - " . $title,
				]);
			}
			$term->field_subtitle->value = $this->add_sup_tag($title, false);
			$term->field_brand->value = strcase_brand($brand);

			$term->field_original_id->value = $data[$headers['ID']];
			$term->field_order->value = $data[$headers['Order']];
			if ($headers['Link Uri'] && empty($data[$headers['Link Uri']])){
				$link_uri = preg_replace("/[^A-Za-z0-9éêèáâà ]/", '', strtolower($title));
				$data[$headers['Link Uri']] = strtolower("/brands/$brand_lwrcase/products-categories/" . str_replace(' ', '-', $link_uri) );
			}


			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}
			if ($headers['Description'] ){
				$term->description->value = $data[$headers['Description']];
			}

			if ($headers['Group'] && !empty($data[$headers['Group']]) ){
				$group_id = $this->_add_product_group($term, $data, $headers, $langcode);
				$term->field_product_group->target_id = $group_id;
			}


			if ($data[$headers['Background Color']]){
				$term->field_background_color->value = $data[$headers['Background Color']];
			}

			if (!empty($file) ){
				$term->field_product_category_image->target_id = $file->id();
				$term->field_product_category_image->alt = $title;
				$term->field_product_category_image->title = $title;
			}

			if (!empty($file_hover) ){
				$term->field_background_image->target_id = $file_hover->id();
				$term->field_background_image->alt = $title;
				$term->field_background_image->title = $title;
			}

		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories', $brand);
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Product Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			if (empty($term)){
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'product_categories', $brand);
				if (empty($term))
					return;
			}
			// $term_fr = $term->addTranslation('fr');
			$term_fr = $this->add_term_translation($term);
			$term_fr->name->value = " $brand - " . $title;
			$term_fr->field_subtitle->value = $this->add_sup_tag($title, $striptags = false);
			$term_fr->field_brand->value = strcase_brand($brand);
			$term_fr->field_original_id->value = $data[$headers['ID']];

			if ($headers['Link Uri'] && empty($data[$headers['Link Uri']])){
				$link_uri = preg_replace("/[^A-Za-z0-9éêèáâà ]/", '', strtolower($title));
				$data[$headers['Link Uri']] = strtolower("/marques/$brand/produits-categories/") . str_replace(' ', '-', $link_uri);
			}
			
			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term_fr->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}

			if ($headers['Description'] ){
				$term_fr->description->value = $data[$headers['Description']];
			}

			if ($headers['Group'] && !empty($data[$headers['Group']]) ){
				$group_id = $this->_add_product_group($term_fr, $data, $headers, $langcode);
				// $term->field_product_group->target_id = $group_id;
			}

			if (!empty($file) ){
				$term_fr->field_product_category_image->target_id = $file->id();
				$term_fr->field_product_category_image->alt = $title;
				$term_fr->field_product_category_image->title = $title;
			}


			$term_fr->save();
			return;
		}

		$term->save();
	}

	public function create_doritos_product_categories($data, $headers, $langcode = 'en'){
		$this->create_product_categories("doritos", $data, $headers, $langcode, $test = 0);
		return;
		$test = 0;
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		// $file_hover = $this->prepare_image_file($data[$headers['Image Hover']], $test);
		$title = strip_tags($data[$headers['Name']]);
		$brand = strcase_brand("doritos");
		// product categories matching  
		if ($langcode == 'en'){

			$term = $this->load_by_original_id($data[$headers['ID']], 'product_categories', $brand);

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'product_categories', 
			    	'name'       => "doritos - " . $title,
				]);
			}
			$term->field_subtitle->value = $this->add_sup_tag($title);
			$term->field_brand->value = strcase_brand("doritos");
			$term->field_product_category_image->target_id = $file->id();
			$term->field_product_category_image->alt = $title;
			$term->field_product_category_image->title = $title;
			$term->field_original_id->value = $data[$headers['ID']];
			$term->field_order->value = $data[$headers['Order']];
			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}
			if ($data[$headers['Background Color']]){
				$term->field_background_color->value = $data[$headers['Background Color']];
			}

			if (!empty($file) ){
				$term->field_product_category_image->target_id = $file->id();
				$term->field_product_category_image->alt = $title;
				$term->field_product_category_image->title = $title;
			}

			if (!empty($file_hover) ){
				$term->field_background_image->target_id = $file_hover->id();
				$term->field_background_image->alt = $title;
				$term->field_background_image->title = $title;
			}

		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID_en']], 'product_categories', $brand);
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Product Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			// $term_fr = $term->addTranslation('fr');
			$term_fr = $this->add_term_translation($term);
			$term_fr->name->value = 'Doritos - ' . $title;
			$term_fr->field_subtitle->value = $this->add_sup_tag($title);
			$term_fr->field_brand->value = strcase_brand("Doritos");
			$term_fr->field_original_id->value = $data[$headers['ID']];
			
			if ($headers['Link Uri'] && $headers['Link Title'] ){
				$term_fr->field_product_link->setValue([
					'uri' =>  "internal:" . $data[$headers['Link Uri']],
					'title' =>   $data[$headers['Link Title']],
				]); 
			}			
			$term_fr->save();
			return;
		}

		$term->save();

	}


	public function has_next_word($text, $word_list){
		$text = strtolower(trim($text) );
		foreach ($word_list as  $word) {
			if (strpos($text, $word) === 0){
				return strlen($word);
			}
		}
		return false;
	}

	public function split_quaker_ingredient($text){
		$columns = [];
		$text = trim($text);
		$word_list = [ 'tsp', 'tbsp', 'cups','cup', 'teaspoon(s)', 'teaspoons','teaspoon',
			'tasses','tasse', 'c.à thé', 'cuillère à thé', 'cuillères à thé',
			'cuillère à soupe', 'pincée'
		];
		// There is a more effective way to do this
		// but this will do for now
		if(
			substr( $text, 0, 3 ) === '1/2' 
			|| substr( $text, 0, 3 ) === '1/3' 
			|| substr( $text, 0, 3 ) === '1/4' 
			|| substr( $text, 0, 3 ) === '3/4' 
		) {
			$nb_length = 3;
			// $columns[0] = substr($text, 0, 3);
			// $columns[1] = substr_replace($text, '', 0, 3);
			if ($this->has_next_word(substr($text, $nb_length), $word_list)){
        		$word_length = $this->has_next_word(substr($text, $nb_length), $word_list);
        		$nb_length += $word_length + 1;
        	}
			$columns[0] = substr($text, 0, $nb_length);
			$columns[1] = substr($text, $nb_length); 
		} elseif( is_numeric(substr( $text, 0, 1 )) ){
			$nb_length = 0;
		    for($i=0; $i<strlen($text); $i++){
		        if (!is_numeric($text[$i]) 
		        	&& $text[$i] != "."
		        	&& $text[$i] != "/"
		        	&& $text[$i] != " "
		        ){
		        	if ($this->has_next_word(substr($text, $i-1), $word_list)){
		        		$word_length = $this->has_next_word(substr($text, $i-1), $word_list);
		        		$nb_length += $word_length;
		        	}
		        	break;
		        }
		        $nb_length++;
		    }


			$columns[0] = substr($text, 0, $nb_length);
			$columns[1] = substr($text, $nb_length); 

		} else{
			$columns[0] = $text;
		}

		return $columns;

	}


public function create_keywords_recipe($data, $headers, $langcode = 'en-ca'){
		$title = strip_tags($data[$headers['keywords']]);
		if ($langcode == 'en-ca'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'recipe_keywords');

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'recipe_keywords', 
			    	'name'       => trim($title),
				]);
			}

			$term->field_original_id->value = $data[$headers['ID']];
			// $term->field_subtitle->value = $this->add_sup_tag($title);
			$term->save();
		}
		else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID']], 'recipe_keywords');
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Recipe Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			if ($langcode === 'fr-ca')
				$langcode = "fr";
			// $term_fr = $term->addTranslation('fr');
			if (empty($term)){
				$term_trans = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'recipe_keywords', 
			    	'name'       => trim($title),
				]);
				$term_trans->langcode->value = $langcode;
			} else {
				$term_trans = $this->add_term_translation($term, $langcode);
				$term_trans->name->value =  trim($title);
			}

			$term_trans->field_original_id->value = $data[$headers['ID']];
			$term_trans->save();
		}


	}


	public function create_categories_recipe($data, $headers, $langcode = 'en-ca'){
		$file = $this->prepare_image_file($data[$headers['Image']]);
		$title = strip_tags($data[$headers['Title']]);
		$depth = $data[$headers['Parent_Category']] == 0? 0 : 1;

		// product categories matching  
		if ($langcode == 'en-ca'){
			$term = $this->load_by_original_id($data[$headers['ID']], 'recipe_search_category');

			if (empty($term)){
				$term = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'recipe_search_category', 
			    	'name'       => trim($title),
				]);
			}

			// $term->field_subtitle->value = $this->add_sup_tag($title);
			$term->description->value = $data[$headers['Description']];
			$term->field_category_image->target_id = $file->id();
			$term->field_category_image->alt = $title;
			$term->field_category_image->title = $title;
			$term->field_original_id->value = $data[$headers['ID']];
			$term->depth = $depth;
			$term->field_basename->value = remove_space($title);
			$term_parent = $this->load_by_original_id($data[$headers['Parent_Category']], 'recipe_search_category');
			$term->parent->setValue($term_parent? $term_parent->id() : 0);




		} else{
			// @TODO: The original ID should be the english version
			try{
				$term = $this->load_by_original_id($data[$headers['ID']], 'recipe_search_category');
			} catch(\Exception $e){
				\Drupal::logger('content-import')->info("Recipe Category does not have an English version", []);
				\Drupal::logger('content-import')->info($e, []);
				return;
			}
			// $term_fr = $term->addTranslation('fr');
			if (empty($term)){
				$term_trans = \Drupal\taxonomy\Entity\Term::create([ 
					'vid' => 'recipe_search_category', 
			    	'name'       => trim($title),
				]);
				$term_trans->langcode->value = $langcode;
			} else {
				$term_trans = $this->add_term_translation($term, $langcode);
				$term_trans->name->value =  trim($title);
			}

			$term_trans->field_basename->value = remove_space($title);
			
			$term_trans->field_category_image->target_id = $file->id();
			$term_trans->field_category_image->alt = $title;
			$term_trans->field_category_image->title = $title;
			$term_trans->depth = $depth;
			$term_trans->description->value = $data[$headers['Description']];
			$term_parent = $this->load_by_original_id($data[$headers['Parent_Category']], 'recipe_search_category');
			$term_trans->parent->setValue($term_parent? $term_parent->id() : 0);
			// Images are not translated
			// $term_fr->field_product_category_image->target_id = $file->id();
			// $term_fr->field_product_category_image->alt = $title;
			// $term_fr->field_product_category_image->title = $title;
			$term_trans->field_original_id->value = $data[$headers['ID']];

			

			$term_trans->save();
			return;
			// die("fr");
		}

		$term->save();

	}



	public function split_ingredient($text){
		$columns = [];
		$text = trim($text);
		// There is a more effective way to do this
		// but this will do for now
		if(
			substr( $text, 0, 3 ) === '1/2' 
			|| substr( $text, 0, 3 ) === '1/3' 
			|| substr( $text, 0, 3 ) === '1/4' 
			|| substr( $text, 0, 3 ) === '3/4' 
		) {
			$columns[0] = substr($text, 0, 3);
			$columns[1] = substr_replace($text, '', 0, 3);
		} elseif( is_numeric(substr( $text, 0, 1 )) ){
			$nb_length = 0;
		    for($i=0; $i<strlen($text); $i++){
		        if (!is_numeric($text[$i]) 
		        	&& $text[$i] != "."
		        	&& $text[$i] != "/"
		        	&& $text[$i] != " "
		        )
		        	break;
		        $nb_length++;
		    }


			$columns[0] = substr($text, 0, $nb_length);
			$columns[1] = substr($text, $nb_length); 

		} else{
			$columns[0] = $text;
		}

		return $columns;

	}

	public function format_ingredients($brand ,$text){
		/*
			// $text is formatted as follows
			1 cup (250 mL) shredded rotisserie chicken
			1/2 cup (125 mL) prepared jerk BBQ sauce, divided
		 */
		$text = $this->add_sup_tag($text);
		$ingredients = empty($brand)? "<ol>" : "<ul>";
		// $lines = explode(PHP_EOL, $text);
		$lines = preg_split("/\\r\\n|\\r|\\n/", $text);

		foreach ($lines as $key => $line) {
			$line = trim($line);
			if (empty($line))
				continue;
			
			$is_indented = substr($line, 0, 2 ) === "--"? 1: 0;
			$class = $is_indented? "$brand-row-indented": '';
			$ingredients .= '<li>';
			$serving_and_values = explode("|", $line);
			$columns = $this->split_ingredient($line);
			if (count($columns) > 1 && !empty($brand)){
				$ingredients .= "<span class='$brand-yellow'>" . $columns[0] . '</span>';
				$ingredients .= $columns[1]; 
			} else {
				$ingredients .= $columns[0]; 
			}
			$ingredients .= "</li>";
		}
		$ingredients .= empty($brand)? "</ol>" : "</ul>";
		// $ingredients .= "";
		return $ingredients;
	}

	public function format_quaker_ingredients($text){
		/*
			// $text is formatted as follows
			1 cup (250 mL) shredded rotisserie chicken
			1/2 cup (125 mL) prepared jerk BBQ sauce, divided
		 */
		$brand = 'quaker';
		$text = $this->add_sup_tag($text);
		$ingredients = empty($brand)? "<ol>" : "<ul>";
		// $lines = explode(PHP_EOL, $text);
		$lines = preg_split("/\\r\\n|\\r|\\n/", $text);
           
		foreach ($lines as $key => $line) {
			$line = trim($line);
			if (empty($line))
				continue;
			
			$is_indented = substr($line, 0, 2 ) === "--"? 1: 0;
			$class = $is_indented? "$brand-row-indented": '';
			$ingredients .= '<li class="quaker-recipe-list">';
			$serving_and_values = explode("|", $line);
			$columns = $this->split_quaker_ingredient($line);
			if (count($columns) > 1 && !empty($brand)){
				$ingredients .= "<span class='quaker-recipe-label'>" . $columns[0] . '</span>';
				$ingredients .= "<span class='quaker-recipe-text'>" . $columns[1] . '</span>';
			} else {
				$ingredients .= "<span class='quaker-recipe-text'>" . $columns[0] . '</span>';
			}
			$ingredients .= "</li>";
		}
		$ingredients .= empty($brand)? "</ol>" : "</ul>";
		// $ingredients .= "";
		return $ingredients;
	}

	public function format_body_add_li($text, $containertag){
		/*
			// $text is formatted as follows
			1 cup (250 mL) shredded rotisserie chicken
			1/2 cup (125 mL) prepared jerk BBQ sauce, divided
		 */
		$text = $this->add_sup_tag($text);
		$newtext = "<" . $containertag .">";
		$notes = "";
		// $lines = explode(PHP_EOL, $text);
		$lines = preg_split("/\\r\\n|\\r|\\n/", $text);

		foreach ($lines as $key => $line) {
			$line = trim($line);
			if (empty($line))
				continue;
			
			if (strpos(strtolower($line),"notas de") !== false || strpos(strtolower($line),"variaciones") !== false || strpos(strtolower($line),"consejo:") !== false )  {
				if ($notes == "") {
					$notes = "<p>";
				}
				$notes .= $line;
			}
			else{
				if ($notes == ""){
					$newtext .= "<li>$line</li>";
				}
				else {
					$notes .= $line;
				}
			}

			
		}
		$newtext .= "</" . $containertag. ">";
		if ($notes != '')	$notes .= "</p>";

		$notes = str_replace("Variaciones", "<br>Variaciones", "$notes");
		$notes = str_replace("Notas de cocción", "<br>Notas de cocción<br>", "$notes");
		$notes = str_replace("24 VARIACIONES DE BARRAS", "<br>24 VARIACIONES DE BARRAS", "$notes");
		$notes = str_replace("BARRAS DE GALLETA", "<br>BARRAS DE GALLETA", "$notes");
		
		
		



		return $newtext . $notes;
	}


	public function format_body_add_p($text){
		/*
			// $text is formatted as follows
			<p>Jugar en las hojas de otoño. </p>
			<p>Salir al patio o ir al parque con los niños y divertirse jugueteando entre las bonitas hojas. ¡Sin olvidar tomar algunas fotos durante la actividad!</p>
		 */
		$text = $this->add_sup_tag($text);
		$newtext = "";
		// $lines = explode(PHP_EOL, $text);
		$lines = preg_split("/\\r\\n|\\r|\\n/", $text);

		foreach ($lines as $key => $line) {
			$line = trim($line);
			if (empty($line)){
				$newtext = $newtext . "<p>&nbsp;</p>";
			}
			else{
				$newtext = $newtext ."<p>$line</p>";
			}
		}
		return $newtext;
	}

	public function table_format_nutrition($brand ,$text){
		/*
			// $text is formatted as follows
			- Calories | 250 
			- Fat 14 g | 22 %
 			-- Saturated 2 g | 10 %
		 */
		
		$nutrition = "";
		// $lines = explode(PHP_EOL, $text);
		$lines = preg_split("/\\r\\n|\\r|\\n/", $text);

		foreach ($lines as $key => $line) {
			$line = trim($line);
			$is_indented = substr($line, 0, 2 ) === "--"? 1: 0;
			$class = $is_indented? "$brand-row-indented": '';
			$nutrition .= '<div class="' . $brand. '-productdetails-nutrition-row ' . $class . '">';
			$serving_and_values = explode("|", $line);
			if (count($serving_and_values) <= 2){
				foreach ($serving_and_values as $key => $value) {
					if (empty(trim($value)))
						continue;
        			$nutrition .= '<div class="' . $brand . '-productdetails-nutrition-cell">';
        			$value = ltrim(trim($value), '-');
        			$nutrition .= $value . "</div>" ;
				}
			} else if (count($serving_and_values) == 3){
				foreach ($serving_and_values as $key => $value) {
					if (empty(trim($value))){
						$value = "";
					} else {
        				$value = ltrim(trim($value), '-');
					}
        			$nutrition .= '<div class="' . $brand . '-productdetails-nutrition-cell">';
        			$nutrition .= $value . "</div>" ;
				}
			}
			$nutrition .= "</div>";
		}
		return $nutrition;
	}

	public function add_separator_claims($claims, $separator){
		$lines = preg_split("/\\r\\n|\\r|\\n/", $claims);
		$result = '';
		foreach ($lines as $key => $line) {
			if (empty($line))
				continue;
			$result .= $line . $separator;
		}

		return $result;

	}

	public function is_valid_claim($claim){
		$claim = strtolower($claim);
		$entityManager = \Drupal::service('entity_field.manager');
		$fields = $entityManager->getFieldStorageDefinitions('node', 'product');
		$options = options_allowed_values($fields['field_claim']);
		$keys_options = array_keys($options);
		if (!in_array($claim, $keys_options))
			return false;
		return true;

	}

	public function explode_claims($claims, $separator = null){
		// $claims = trim($claims)
		if (empty($separator)){
			$lines = preg_split("/\\r\\n|\\r|\\n/", $claims);
		} else{
			$lines = explode($separator, $claims);
		}

		$array = [];
		foreach ($lines as $key => $line) {
			$line = trim(strtolower($line) );
			if (empty($line))
				continue;
			$line = str_replace(",", "", $line);
			$line = str_replace("-", "_", $line);
			$line = str_replace(" ", "_", $line);
			$line = str_replace("*", "", $line);
			$line = str_replace("(", "", $line);
			$line = str_replace(")", "", $line);
			$line = str_replace("&", "n", $line);
			if ($this->is_valid_claim($line))
				$array[] = $line;
		}
		return $array;
	}


	public function get_lays_product_category_ids(){
		$product_categories = [
			'Lay\'s Poppables™' => $this->get_product_categoryID_by_title('Lay\'s Poppables™', 'lays'),
			'Lay\'s Stax®' 		=> $this->get_product_categoryID_by_title('Lay\'s Stax®', 'lays'),
			'Lay\'s®' 			=> $this->get_product_categoryID_by_title('Lay\'s®', 'lays'),
			'Lay\'s® Wavy' 		=> $this->get_product_categoryID_by_title('Lay\'s® Wavy', 'lays'),
		];

		return $product_categories;
	}

	public function search_product_category($brand, $keyword, $exact_match = false){
		$wildcard = $exact_match? "" : "%";
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', 'product_categories');
		$query->condition('field_brand', $brand);
		$query->condition('field_subtitle', "$wildcard" . $keyword . "$wildcard", "LIKE");
		$query->sort('field_order', 'ASC');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];

		$tids = array_values($entity_ids);
		return $tids[0];
	}

	public function find_product_category_id($brand, $title){
		$title = strtolower($title);
		$tid = 0;
		if ($brand == 'lays'){
			if (strpos($title, "poppables") !== false){
				$tid = $this->search_product_category($brand, 'poppables');
			} elseif (strpos($title, "wavy") !== false){
				$tid = $this->search_product_category($brand, 'wavy');
			} elseif (strpos($title, "stax") !== false){
				$tid = $this->search_product_category($brand, 'stax');
			} elseif (strpos($title, "variety packs") !== false){
				$tid = $this->search_product_category($brand, 'variety packs');
			} elseif (strpos($title, "lightly salted") !== false){
				$tid = $this->search_product_category($brand, 'lightly salted');
			} else{
				$tid = $this->get_product_categoryID_by_title($this->add_sup_tag('LAY\'S®'), 'lays');
			}
		} elseif ($brand == 'crispyminis'){
			if (strpos($title, "rice chips") !== false){
				$tid = $this->search_product_category($brand, 'rice chips');
			} elseif (strpos($title, "large rice cakes") !== false){
				$tid = $this->search_product_category($brand, 'large rice cakes');
			} elseif (strpos($title, "veggie") !== false){
				$tid = $this->search_product_category($brand, 'veggie');
			}
		} elseif ($brand == 'missvickies'){
			if (strpos($title, "signature") !== false){
				$tid = $this->search_product_category($brand, 'signature');
			} else {
				$tid = $this->get_product_categoryID_by_title($this->add_sup_tag('Miss Vickie\'s®'), 'missvickies');
			} 
		// } elseif (strtolower($brand) == 'doritos'){
		// 	if (strpos($title, "inamita") !== false){
		// 		$tid = $this->search_product_category($brand, 'Dinamita');
		// 	} elseif (strpos($title, "variety packs") !== false){
		// 		$tid = $this->search_product_category($brand, 'Variety Packs');
		// 	} else {
		// 		$tid = $this->get_product_categoryID_by_title($this->add_sup_tag('Doritos®'), 'doritos');
		// 	} 
		// } elseif (strtolower($brand) == 'ruffles'){
		// 	if (strpos($title, "double crunch") !== false){
		// 		$tid = $this->search_product_category($brand, 'double crunch');
		// 	} elseif (strpos($title, "dips") !== false){
		// 		$tid = $this->search_product_category($brand, 'Dips');
		// 	} else {
		// 		$tid = $this->get_product_categoryID_by_title($this->add_sup_tag('Ruffles®'), 'ruffles');
		// 	} 
		} else{
			// quaker, doritos, ruffles
			$title = strip_tags($title);
			// in quaker_product_en.csv the "Category" column doens't have <sup> tags
			// while the product category on the CMS do have <sup>®</sup>
			$title = str_replace("®", "<sup>®</sup>", $title);
			$title = str_replace("_tm_", "<sup>TM</sup>", $title);
			$title = str_replace("_mc_", "<sup>MC</sup>", $title);
			$tid = $this->search_product_category($brand, $title, $exact_match = 1);
		}
		return $tid;
	}


	public function create_lays_product($data, $headers, $langcode = 'en'){
		$data[$headers['Category']]  = strtolower($this->sanitize_text($data[$headers['Category']] ) );
		$file = $this->prepare_image_file($data[$headers['Product Image']]);
		$file_featured = $this->prepare_image_file($data[$headers['Featured Image']]);
		$file_background = $this->prepare_image_file($data[$headers['Background Image']]);
		$title = $data[$headers['Name']];
		// @TODO: the french version has different values
		$product_categories = [
			'lay\'s poppables™' => $this->get_product_categoryID_by_title($this->add_sup_tag('LAY\'S POPPABLES™'), 'lays'),
			'lay\'s stax®' => $this->get_product_categoryID_by_title($this->add_sup_tag('LAY\'S STAX®'), 'lays'),
			'lay\'s®' 		=> $this->get_product_categoryID_by_title($this->add_sup_tag('LAY\'S®'), 'lays'),
			'lay\'s® variety packs'  => $this->get_product_categoryID_by_title($this->add_sup_tag('LAY\'S® Variety Packs'), 'lays'),
			'lay\'s® lightly salted' => $this->get_product_categoryID_by_title($this->add_sup_tag('LAY\'S® Lightly Salted'), 'lays'),
			'wavy lay\'s®' 		=> $this->get_product_categoryID_by_title($this->add_sup_tag('WAVY LAY\'S®'), 'lays'),
		];
		// debug_var($headers);
		// debug_var($data, 1);
		if ($langcode == 'en'){

			$node = $this->load_by_original_id($data[$headers['ID']], 'product');
			if (empty($node)){
				$node = \Drupal\node\Entity\Node::create([
				    'type'        => 'product',
				    'title'       => "Lays - " . $title,
				]);
			}

			$node->body->format = 'full_html';
			$node->body->value = $this->add_sup_tag($data[$headers['Description']], $strip_tags = false);
			$node->title->value = "Lays -" . $title;
			$node->langcode->value =  $langcode;
			$node->field_subtitle->value =  $this->add_sup_tag($title);
			$node->field_brand->value =  "Lays";
			// $node->field_recipe_image->target_id = $file->id();
			$node->field_recipe_ingredients->value = $data[$headers['Ingredients']];
			$node->field_background_color->value = $data[$headers['Background Colour']];
			$node->field_font_color->value = strtolower($data[$headers['Font Colour']]);
			$node->field_product_alert->value = $data[$headers['Alert']];
			$node->field_portion->value = $data[$headers['Portion']];
			$node->field_is_featured->value = $data[$headers['Featured']];
			// @TODO: Extract the numbers from the field (right now it's STAX2 Stax3)
			$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
			$node->field_weight->value = $weight;
			$node->field_nutrition->format = 'full_html';
			$node->field_nutrition->value = $this->table_format_nutrition('lays', $data[$headers['Nutrition Facts']]);
			// $node->field_product_category->target_id = $product_categories[$data[$headers['Category']] ];
			$node->field_product_category->target_id = $this->find_product_category_id('lays', $data[$headers['Category']]  );
			// $node->field_claims->value = $data[$headers['Claims']];
			$claims = $this->explode_claims($data[$headers['Claims']], '|');
			$node->field_claim->setValue($claims);

			if (!empty($file)){
				$node->field_recipe_image->target_id = $file->id();
				$node->field_recipe_image->alt = $title;
				$node->field_recipe_image->title = $title;
			}
			if (!empty($file_background)){
				$node->field_background_image->target_id = $file_background->id();
				$node->field_background_image->alt = $title;
				$node->field_background_image->title = $title;
			}
			if (!empty($file_featured)){
				$node->field_extra_image1->target_id = $file_featured->id();
				$node->field_extra_image1->alt = $title;
				$node->field_extra_image1->title = $title;
			}

			$node->field_original_id->value = $data[$headers['ID']];
	  		$node->save();
	  		return 1;

			}else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'product');
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return 0;
				}

				if (empty($node))
					return 0;
				// debug_var($node->id());
				// die(" fr prod ");
				$node_fr = $this->add_node_translation($node);
				// $node_fr = $node->addTranslation('fr');
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']], $strip_tags = false);
				$node_fr->title->value = "Lays - " . $title;
				$node_fr->field_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  "Lays";
				$node_fr->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node_fr->field_product_alert->value = $data[$headers['Alert']];
				$node_fr->field_portion->value = $data[$headers['Portion']];
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_nutrition->value = $this->table_format_nutrition('lays', $data[$headers['Nutrition Facts']]);

				if (!empty($file)){
					$node_fr->field_recipe_image->target_id = $file->id();
					$node_fr->field_recipe_image->alt = $title;
					$node_fr->field_recipe_image->title = $title;
				}

				// No need to have claims in French
				
				// $node_fr->field_claims->value = $data[$headers['Claims']];
				// $node_fr->field_nutrition->value = $data[$headers['Nutrition Facts']];
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_nutrition->value = $this->table_format_nutrition('lays', $data[$headers['Nutrition Facts']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->save();

				return 1;
			}
			return 0;

	}

	public function create_crispyminis_product($data, $headers, $langcode = 'en'){
		$test = 0;
		$data[$headers['Category']]  = strtolower($this->sanitize_text($data[$headers['Category']] ) );
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		$file_large = $this->prepare_image_file($data[$headers['Large Image']], $test = 0);
		$file_hover = $this->prepare_image_file($data[$headers['Image Hover']], $test = 0);
		$file_featured = $this->prepare_image_file($data[$headers['Featured Image']], 0 );
		$title = $data[$headers['Name']];
		$brand = strcase_brand('crispyminis');

		if ($langcode == 'en'){

			$node = $this->load_by_original_id($data[$headers['ID']], 'product', $brand);
			if (empty($node)){
				$node = \Drupal\node\Entity\Node::create([
				    'type'        => 'product',
				    'title'       => "Crispyminis - " . $title,
				]);
			}

			$node->body->format = 'full_html';
			$node->body->value = $this->add_sup_tag($data[$headers['Description']], $strip_tags = false);
			$node->title->value = "Crispyminis -" . $title;
			$node->langcode->value =  $langcode;
			$node->field_subtitle->value =  $this->add_sup_tag($title);
			$node->field_brand->value =  strcase_brand('crispyminis');
			// $node->field_recipe_image->target_id = $file->id();
			$node->field_recipe_ingredients->value = $data[$headers['Ingredients']];
			$node->field_product_alert->value = $data[$headers['Alert']];
			$node->field_portion->value = $data[$headers['Portion']];
			$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
			$node->field_weight->value = $weight;
			// $node->field_is_featured->value = $data[$headers['Featured']];
			// @TODO: Extract the numbers from the field (right now it's STAX2 Stax3)
			$node->field_nutrition->format = 'full_html';
			$node->field_nutrition->value = $this->table_format_nutrition('crispyminis', $data[$headers['Nutrition Facts']]);
			// $node->field_product_category->target_id = $product_categories[$data[$headers['Category']] ];
			$node->field_product_category->target_id = $this->find_product_category_id('crispyminis', $data[$headers['Category']]  );

			if (!empty($file)){
				$node->field_recipe_image->target_id = $file->id();
				$node->field_recipe_image->alt = $title;
				$node->field_recipe_image->title = $title;
			}

			if (!empty($file_large) ){
				$node->field_extra_image1->target_id = $file_large->id();
				$node->field_extra_image1->alt = $title;
				$node->field_extra_image1->title = $title;
			}

			if (!empty($file_featured) ){
				$node->field_extra_image2->target_id = $file_featured->id();
				$node->field_extra_image2->alt = $title;
				$node->field_extra_image2->title = $title;
			}

			if (!empty($file_hover) ){
				$node->field_background_image->target_id = $file_hover->id();
				$node->field_background_image->alt = $title;
				$node->field_background_image->title = $title;
			}

			if (!empty($data[$headers['Featured']])){
				$node->field_is_featured = $data[$headers['Featured']];
			}
		

			$node->field_original_id->value = $data[$headers['ID']];
	  		$node->save();
	  		return 1;

			}else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'product', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return 0;
				}

				if (empty($node))
					return 0;
				// debug_var($node->id());
				// die(" fr prod ");
				$node_fr = $this->add_node_translation($node);
				// $node_fr = $node->addTranslation('fr');
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']], $strip_tags = false);
				$node_fr->title->value = "Crispyminis - " . $title;
				$node_fr->field_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  $brand;
				$node_fr->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node_fr->field_product_alert->value = $data[$headers['Alert']];
				$node_fr->field_portion->value = $data[$headers['Portion']];
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_nutrition->value = $this->table_format_nutrition('crispyminis', $data[$headers['Nutrition Facts']]);

				// Weight is non translatable
				// $weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
				// $node_fr->field_weight->value = $weight;

				if (!empty($file)){
					$node_fr->field_recipe_image->target_id = $file->id();
					$node_fr->field_recipe_image->alt = $title;
					$node_fr->field_recipe_image->title = $title;
				}

				// No need to have claims in French
				
				// $node_fr->field_claims->value = $data[$headers['Claims']];
				// $node_fr->field_nutrition->value = $data[$headers['Nutrition Facts']];
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_nutrition->value = $this->table_format_nutrition('crispyminis', $data[$headers['Nutrition Facts']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->save();

				return 1;
			}
			return 0;

	}

	public function create_missvickies_flavours($data, $headers, $langcode = 'en'){
		// $data[$headers['Category']]  = strtolower($this->sanitize_text($data[$headers['Category']] ) );
		// $title = $data[$headers['Name']];
		$brand = strcase_brand('MissVickies');

		$env =  \Drupal\Core\Site\Settings::get("environment");
		$nid_column = 'nid';
		if ($env == 'staging' || $env == 'dev'){
			$nid_column = 'nid_staging';
		}
		$nid = $data[$headers[$nid_column]];
		$node = \Drupal\node\Entity\Node::load($nid); 

		$flavours = explode("|",  $data[$headers['flavour']] );
		$arr = [];
		foreach ($flavours as $flavour) {
			if (empty($flavour) || trim($flavour) == 'N/A')
				continue;
			$arr[] = trim($flavour);
		}
		if (!empty($arr)){
			$node->field_flavour->setValue($arr);
			$node->save();
			return 1;
		}


	}

	public function create_missvickies_product($data, $headers, $langcode = 'en'){
		$test = 0;
		$data[$headers['Category']]  = strtolower($this->sanitize_text($data[$headers['Category']] ) );
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		$file_extra1 = $this->prepare_image_file($data[$headers['Product Image']], $test);
		$title = $data[$headers['Name']];
		$brand = strcase_brand('MissVickies');

		if ($langcode == 'en'){

			$node = $this->load_by_original_id($data[$headers['ID']], 'product', $brand);
			if (empty($node)){
				$node = \Drupal\node\Entity\Node::create([
				    'type'        => 'product',
				    'title'       => "MissVickies - " . $title,
				]);
			}

			$node->body->format = 'full_html';
			$node->body->value = $this->add_sup_tag($data[$headers['Description']], $strip_tags = false);
			$node->title->value = "MissVickies -" . $title;
			$node->langcode->value =  $langcode;
			$node->field_subtitle->value =  $this->add_sup_tag($title);
			$node->field_brand->value =  strcase_brand('MissVickies');
			// $node->field_recipe_image->target_id = $file->id();
			$node->field_recipe_ingredients->value = $data[$headers['Ingredients']];
			$node->field_product_alert->value = $data[$headers['Alert']];
			$node->field_portion->value = $data[$headers['Portion']];
			$node->field_pairing->value = $data[$headers['Wine Pairing']];
			// $node->field_is_featured->value = $data[$headers['Featured']];
			// @TODO: Extract the numbers from the field (right now it's STAX2 Stax3)
			$node->field_nutrition->format = 'full_html';
			$node->field_nutrition->value = $this->table_format_nutrition('missvickies', $data[$headers['Nutrition Facts']]);
			// $node->field_product_category->target_id = $product_categories[$data[$headers['Category']] ];
			$node->field_product_category->target_id = $this->find_product_category_id('missvickies', $data[$headers['Category']]  );

			if (!empty($file)){
				$node->field_recipe_image->target_id = $file->id();
				$node->field_recipe_image->alt = $title;
				$node->field_recipe_image->title = $title;
			}

			if (!empty($file_extra1) ){
				$node->field_extra_image2->target_id = $file_extra1->id();
				$node->field_extra_image2->alt = $title;
				$node->field_extra_image2->title = $title;
			}

			if (!empty($data[$headers['Order']])){
				$weight = filter_var($data[$headers['Order']], FILTER_SANITIZE_NUMBER_INT);
				$node->field_weight->value = $weight;
			}

			if (!empty($data[$headers['Featured']])){
				$node->field_is_featured = $data[$headers['Featured']];
			}
		

			$node->field_original_id->value = $data[$headers['ID']];
	  		$node->save();
	  		return 1;

			}else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'product', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return 0;
				}

				if (empty($node))
					return 0;
				// debug_var($node->id());
				// die(" fr prod ");
				$node_fr = $this->add_node_translation($node);
				// $node_fr = $node->addTranslation('fr');
				$node_fr->body->format = 'full_html';
				$node_fr->body->value = $this->add_sup_tag($data[$headers['Description']], $strip_tags = false);
				$node_fr->title->value = "MissVickies - " . $title;
				$node_fr->field_subtitle->value =  $this->add_sup_tag($title);
				$node_fr->field_brand->value =  $brand;
				$node_fr->field_recipe_ingredients->value = $data[$headers['Ingredients']];
				$node_fr->field_product_alert->value = $data[$headers['Alert']];
				$node_fr->field_portion->value = $data[$headers['Portion']];
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_pairing->value = $data[$headers['Wine Pairing']];
				$node_fr->field_nutrition->value = $this->table_format_nutrition('missvickies', $data[$headers['Nutrition Facts']]);

				if (!empty($file)){
					$node_fr->field_recipe_image->target_id = $file->id();
					$node_fr->field_recipe_image->alt = $title;
					$node_fr->field_recipe_image->title = $title;
				}

				if (!empty($file_extra1) ){
					$node_fr->field_extra_image2->target_id = $file_extra1->id();
					$node_fr->field_extra_image2->alt = $title;
					$node_fr->field_extra_image2->title = $title;
				}

				// No need to have claims in French
				
				// $node_fr->field_claims->value = $data[$headers['Claims']];
				// $node_fr->field_nutrition->value = $data[$headers['Nutrition Facts']];
				$node_fr->field_nutrition->format = 'full_html';
				$node_fr->field_nutrition->value = $this->table_format_nutrition('missvickies', $data[$headers['Nutrition Facts']]);
				$node_fr->field_original_id->value = $data[$headers['ID']];
				$node_fr->save();

				return 1;
			}
			return 0;

	}

	public function create_lays_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'Lays');
	}

	public function create_popcorners_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'popcorners');
	}

	public function create_crispyminis_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'CrispyMinis');
	}

	public function create_missvickies_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'MissVickies', $test = 0);
	}

	public function create_smartfood_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'smartfood', $test = 0);
	}

	public function create_sunchips_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'sunchips', $test = 0);
	}

	public function create_stacys_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'stacys', $test = 0);
	}

	public function create_doritos_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'Doritos', $test = 0);
	}

	public function create_ruffles_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'ruffles', $test = 0);
	}
	public function create_quaker_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'quaker', $test = 0);
	}

	public function create_pearlmilling_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'pearlmilling', $test = 0);
	}

	public function create_capncrunch_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'capncrunch', $test = 0);
	}

	public function create_otep_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'otep');
	}

	public function create_cheetos_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'Cheetos');
	}

	public function create_tostitos_slides($data, $headers, $langcode = 'en'){
		$this->create_brands_slides($data, $headers, $langcode, $brand = 'Tostitos');
	}

	public function create_brands_slides($data, $headers, $langcode, $brand, $test = 0){
		// $test = 1;
		$file = $this->prepare_image_file($data[$headers['Image']], $test);
		$file_mobile = $this->prepare_image_file($data[$headers['Image Mobile']], $test);

		$title = $data[$headers['Title']];
		$brand = strcase_brand($brand);
		try{
			if ($langcode == 'en'){
				$node = $this->load_by_original_id($data[$headers['ID']], 'home_page_carousel2', $brand);
				if (empty($node)){
					// die("empty");
					$node = \Drupal\node\Entity\Node::create([
					    'type'        => 'home_page_carousel2',
					    'title'       => "$brand - " . $title,
					]);
				}

				$node->title->value = "$brand - " . $title;
				$node->field_subtitle->value = $this->add_sup_tag($data[$headers['Headline']], 0);
				$node->field_subtitle_logged_in->value = $this->add_sup_tag($data[$headers['Headline']], 0);
				$node->field_carousel_type->value = 'main';
				$node->field_brand->value =  strcase_brand($brand);
				$node->body->format = 'full_html';
				$node->body->value = $this->add_sup_tag($data[$headers['Description']], 0);
				$node->field_original_id->value = $data[$headers['ID']];
				$node->field_carousel_position->value = $data[$headers['Order']];
				$node->field_cta_position->value = 'L';

				if (!empty($file) ){
					$node->field_carousel_image->target_id = $file->id();
					$node->field_carousel_image->alt = $title;
					$node->field_carousel_image->title = $title;
				}

				if (!empty($file_mobile) ){
					$node->field_mobile_image->target_id = $file_mobile->id();
					$node->field_mobile_image->alt = $title;
					$node->field_mobile_image->title = $title;
				}
				// if (!$this->is_internal_url($data[$headers['Button Link']]) ){
				if (!empty($data[$headers['Button Link']]) ){
					$node->field_cta_text->uri = $data[$headers['Button Link']];
					$node->field_cta_text->title = $data[$headers['Button Label']];
				}

				// debug_var($node->id());
				// echo " here \n";

			} else{
				try{
					$node = $this->load_by_original_id($data[$headers['ID_en']], 'home_page_carousel2', $brand);
				} catch(\Exception $e){
					\Drupal::logger('content-import')->info("Content does not have an English version", []);
					\Drupal::logger('content-import')->info($e, []);
					return;
				}

				if (empty($node)){
					// Here the ID from the english version doesn't exist. We check if there is 
					// a slide with french-only content (no translation)
					$node = $this->load_by_original_id($data[$headers['ID']], 'home_page_carousel2', $brand);
					if (empty($node))
						return;
				}
				// $node_fr = $node->addTranslation('fr');
				$node_fr = $this->add_node_translation($node);
				$node_fr->body->value = $data[$headers['Description']];
				$node_fr->title->value = "$brand - " . $title;
				$node_fr->field_subtitle->value =  $data[$headers['Headline']];
				$node_fr->field_subtitle_logged_in->value =  $data[$headers['Headline']];
				$node_fr->field_brand->value =  $brand;
				$node_fr->field_carousel_type->value =  "main";
				$node_fr->field_cta_position->value = 'L';
				if (!empty($file) ){
					$node_fr->field_carousel_image->target_id =  $file->id();
					$node_fr->field_carousel_image->alt =  $title;
					$node_fr->field_carousel_image->title =  $title;
				}
				if (!empty($file_mobile) ){
					$node_fr->field_mobile_image->target_id =  $file_mobile->id();
					$node_fr->field_mobile_image->alt =  $title;
					$node_fr->field_mobile_image->title =  $title;
				}
				$node_fr->field_original_id->value = $data[$headers['ID']];
				if (!empty($data[$headers['Button Link']]) ){
				// if (!$this->is_internal_url($data[$headers['Button Link']]) ){
					$node_fr->field_cta_text->uri = $data[$headers['Button Link']];
					$node_fr->field_cta_text->title = $data[$headers['Button Label']];
				}
				$node_fr->save();

				return;
			}


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
			return 1;
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function create_smartfood_contentblocks($data, $headers, $langcode = 'en'){
		$this->create_brands_contentblocks($data, $headers, $langcode, $brand = 'smartfood');
	}

	public function create_stacys_contentblocks($data, $headers, $langcode = 'en'){
		$this->create_brands_contentblocks($data, $headers, $langcode, $brand = 'stacys');
	}

	public function create_sunchips_contentblocks($data, $headers, $langcode = 'en'){
		$this->create_brands_contentblocks($data, $headers, $langcode, $brand = 'sunchips');
	}

	public function create_doritos_contentblocks($data, $headers, $langcode = 'en'){
		$this->create_brands_contentblocks($data, $headers, $langcode, $brand = 'doritos');
	}

	public function create_ruffles_contentblocks($data, $headers, $langcode = 'en'){
		$this->create_brands_contentblocks($data, $headers, $langcode, $brand = 'ruffles');
	}

	public function create_quaker_contentblocks($data, $headers, $langcode = 'en'){
		$this->create_brands_contentblocks($data, $headers, $langcode, $brand = 'quaker');
	}

	

	public function create_brands_contentblocks($data, $headers, $langcode, $brand, $test = 0){
		$block_type = $data[$headers['Block Type']];
		$brand = strcase_brand($brand);
		try{
			$node = $this->load_content_block($brand, $block_type, $langcode);
			if (empty($node)){
				echo " $brand Content block of type $block_type not found\n";
				return;
			}

			$node->body->format = 'full_html';
			$node->body->value = $data[$headers['Description']];
				


		  	$node->save();
			\Drupal::logger('content-import')->info(" Node saved  " . $node->id(), []);
			return 1;
		} catch(\Exception $e){
			\Drupal::logger('content-import')->info(" Node save error  " . print_r($e), []);
		}
	}

	public function load_content_block($brand, $block_type, $langcode){
		$query = \Drupal::entityQuery('node');
		$query->condition('type', "content_block");
		$query->condition('field_block_type', $block_type);
		$query->condition('langcode', $langcode);
		$query->condition('field_brand', $brand);
		$entity_ids = $query->execute();

		if (empty($entity_ids))
			return '';


		$nids = array_values($entity_ids);
		if (count($nids) >= 1 ){
			$node = \Drupal\node\Entity\Node::load($nids[0]); 
			return $node->getTranslation($langcode);
		} else {
			return '';
		}
	}

	private function is_internal_url($url){
		if (strpos($url, "http") !== false){
			return false;
		}
		return true;
	}


}