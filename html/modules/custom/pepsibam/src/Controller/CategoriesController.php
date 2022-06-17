<?php

namespace Drupal\pepsibam\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\taxonomy\Entity\Term;

use Drupal\pepsibam\ContentQueries;


class CategoriesController extends ControllerBase {
    /**
     * {@inheritdoc}
     */
    private $nb_recipes_per_query = 12;

    private function is_wrong_language_url(){
		$langcode = get_current_langcode();
		$current_path = \Drupal::service('path.current')->getPath();
		if ( in_array($langcode, ['en-ca', 'en-us'] ) && strpos($current_path, "recipes/category/") !== false
			|| $langcode == 'fr-ca' && strpos($current_path, "recettes/categorie/") !== false
			|| $langcode == 'es-us' && strpos($current_path, "recetas/categoria/") !== false
			){
			return false;
		}
		return true;
    }

    private function translateCategory($category, $langcode, $depth = 0){
    	$matching_lang = [
    		'en' => 'fr',
    		'fr' => 'en',
    		'en-us' => 'es-us',
    		'es-us' => 'en-us',
    	];
    	$tid = $this->get_category_tid($category, $matching_lang[$langcode]);
		$term_entity = Term::load($tid);

		$term_trans = \Drupal::service('entity.repository')->getTranslationFromContext($term_entity, $langcode);

		return remove_space($term_trans->name->value);

    }

    private function redirect_correct_lang_route($langcode, $route, $params){
    	$category = $params['category'];
    	$params['category'] = $this->translateCategory($category, $langcode);
    	if ($params['subcategory']){
    		$subcategory = $params['subcategory'];
    		$params['subcategory'] = $this->translateCategory($subcategory, $langcode, $depth = 1);
    	}

    	return new RedirectResponse(\Drupal\Core\Url::fromRoute($route, $params)->toString()); 
    }

    public function recipeLanding(Request $request) {
    	$data = [];
    	$langcode = get_current_langcode($with_prefix = false);
    	$lang_prefix = get_current_langcode();
		

		$data['categories'] = $this->get_categories();
		$data['langcode'] = $langcode;
		$content_query = new ContentQueries();
		$content_query->setLangPrefix($lang_prefix);
		$data['recipes'] = $content_query->get_recipes_by_search_category(0);
		// term reference: field_recipe_brands_category
		// get a list of all terms in the vocabulary
		$data['brands'] = ContentQueries::instance()->get_terms_by_vocabulary('brands_category');
		$data['langprefix'] = $lang_prefix;
		$data['langcode'] = get_current_langcode($with_prefix = 0);
		
		$data['nb_recipes'] = count($data['recipes']);
		$data['nb_per_query'] = $this->nb_recipes_per_query;
    	return array(
            '#theme' => 'recipe_main_landing',
            '#data' => $data,
        );
    }


    public function categoryLanding(Request $request, $category) {
    	$data = [];
    	$langcode = get_current_langcode($with_prefix = false);
    	$lang_prefix = get_current_langcode();
    	if ($this->is_wrong_language_url()){
    		$params = ['category' => $category];
			return $this->redirect_correct_lang_route($langcode, $langcode .".pepsibam.recipe.category" , $params);
		}
    	//@TODO: Make sure $category is one of the existing category
    	if (!$this->is_valid_category($category, $langcode))
	    	// if not one of the existing categories send to 404
    		return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
		

		$data['subcategories'] = $this->get_subcategories($category);
		$data['langcode'] = $langcode;
		$content_query = new ContentQueries();
		$content_query->setLangPrefix($lang_prefix);
		$category_tid = $this->get_category_tid($category, $langcode);
		$data['recipes'] = $content_query->get_recipes_by_search_category($category_tid);
		$data['category_id'] = $category_tid;
		$term = Term::load($category_tid);
		$data['category_title'] = $term->getTranslation($langcode)->name->value;
		// term reference: field_recipe_brands_category
		// get a list of all terms in the vocabulary
		$data['brands'] = ContentQueries::instance()->get_terms_by_vocabulary('brands_category');
		$data['langprefix'] = $lang_prefix;
		$data['langcode'] = get_current_langcode($with_prefix = 0);
		
		$data['nb_recipes'] = count($data['recipes']);
		$data['nb_per_query'] = $this->nb_recipes_per_query;

    	return array(
            '#theme' => 'recipe_category_landing',
            '#data' => $data,
        );
    }

    public function subCategoryLanding(Request $request, $category, $subcategory) {
    	$data = [];
    	$langcode = get_current_langcode($with_prefix = false);
    	$lang_prefix = get_current_langcode();
    	//@TODO: Make sure $category is one of the existing category
		
		if ($this->is_wrong_language_url()){
    		$params = [
    			'category' => $category,
    			'subcategory' => $subcategory,
    		];
			return $this->redirect_correct_lang_route($langcode, $langcode .".pepsibam.recipe.subcategory" , $params);
		}

    	if (!$this->is_valid_category($category, $langcode))
	    	// if not one of the existing categories send to 404
    		return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 
		// $data['subcategories'] = $this->get_subcategories($category);
		$data['langcode'] = $langcode;
		$category_tid = $this->get_category_tid($category, $langcode);
		$subcategory_tid = $this->get_category_tid($subcategory, $langcode);
		$content_query = new ContentQueries();
		$content_query->setLangPrefix($lang_prefix);


		$data['recipes'] = $content_query->get_recipes_by_search_category($subcategory_tid);
		// debug_var($data, 1);
		$data['category_id'] = $category_tid;
		$data['subcategory_id'] = $subcategory_tid;
		$term = Term::load($subcategory_tid);
		$data['category_title'] = $term->getTranslation($langcode)->name->value;
		// term reference: field_recipe_brands_category
		// get a list of all terms in the vocabulary
		$data['brands'] = ContentQueries::instance()->get_terms_by_vocabulary('brands_category');
		$data['langprefix'] = $lang_prefix;
		$data['langcode'] = get_current_langcode($with_prefix = 0);
		$data['nb_recipes'] = count($data['recipes']);
		$data['nb_per_query'] = $this->nb_recipes_per_query;
		

    	return array(
            '#theme' => 'recipe_subcategory_landing',
            '#data' => $data,
        );
    }


    public function searchRecipes(Request $request){
    	$data = [];
    	$data['status'] = 'fail';
    	$tids = $request->get('tids');
    	$lang = $request->get('lang');
    	$lang_prefix = $request->get('langprefix');
    	$offset = $request->get('offset');
    	if (empty($offset))
    		$offset = 0;
    	$category_tid = $request->get('category');
    	$search_term = $request->get('search');

    	// log_var($search_term, " Searched terms  ");
    	$tids = !empty($tids)? json_decode($tids) : [];
    	$recipes = [];
    	$content_query = new ContentQueries();
    	// $lang_prefix = get_current_langcode();
    	$content_query->setLangPrefix($lang_prefix);
    	$content_query->setCategory($category_tid);

		$recipes = $content_query->find_recipes($tids, $search_term, $lang, $offset);

    	if (!empty($recipes)){
    		$data['recipes'] = $recipes;
			$data['count'] = count($recipes);
    	} else {
    		$data['recipes'] = '';
			$data['count'] = 0;
    	}
		$data['status'] = 'success';
    	echo json_encode($data);
    	die;
    }

    private function is_valid_category($category, $langcode = "en", $depth = 0){
    	$vocabulary = 'recipe_search_category';
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
		foreach ($terms as $partial_term) {
			// taxonomy terms returned by loadTree() do not contain all the fields that
			// a normal taxonomy term has. Hence the variable name $partial_term
			// Unlike the taxonomy_term_load() which returns the full term entity 
			$term_entity = Term::load($partial_term->tid);
			if (!$term_entity->hasTranslation($langcode)){
				continue;
			}

			$term_trans = $term_entity->getTranslation($langcode);

			if ($category == remove_space($term_trans->name->value) && $partial_term->depth == $depth){
				return true;
			}
		}

		return false;

    }

    private function get_category_tid($category, $langcode = "en"){
    	$vocabulary = 'recipe_search_category';
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
		foreach ($terms as $partial_term) {
			// taxonomy terms returned by loadTree() do not contain all the fields that
			// a normal taxonomy term has. Hence the variable name $partial_term
			// Unlike the taxonomy_term_load() which returns the full term entity 
			$term_entity = Term::load($partial_term->tid);
			$term_trans = \Drupal::service('entity.repository')->getTranslationFromContext($term_entity, $langcode);
			if (!$term_trans)
				continue;

			if ($category == remove_space($term_trans->name->value) )
				return $partial_term->tid;
		}
		return 0;
    }


    private function get_categories(){
    	$langcode = get_current_langcode($with_prefix = false);
    	$vocabulary = 'recipe_search_category';
    	$lang_prefix = get_current_langcode();
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
		$term_data = [];
		foreach ($terms as $term) {
			if (!empty($term->parents) && $term->parents[0] != 0 )
				// We only care about the parents
				continue;
			$object_term = Term::load($term->tid);
			$img_url = "";

			if (!$object_term->hasTranslation($langcode)){
				continue;
			}
			$term_trans = $object_term->getTranslation($langcode);
			// We want the image from the current language. If not available
			// get the image from the default language
			if (!empty($object_term->getTranslation($langcode)->field_category_image->target_id)){
				$img = \Drupal\file\Entity\File::load($object_term->getTranslation($langcode)->field_category_image->target_id);
	    		$img_url = file_create_url( $img->getFileUri() );
			}elseif (!empty($object_term->field_category_image->target_id)){
				$img = \Drupal\file\Entity\File::load($object_term->field_category_image->target_id);
	    		$img_url = file_create_url( $img->getFileUri() );
			}

			// $term_trans = \Drupal::service('entity.repository')->getTranslationFromContext($object_term, $langcode);
			
			if ($term_trans){
				$title = $term_trans->name->value;
			} else {
				$title = $term->name;
			}

		 	$link = "/$lang_prefix/recipes/category/" . remove_space($title);
		 	if ($lang_prefix == 'fr-ca'){
		 		$link = "/$lang_prefix/recettes/categorie/" . remove_space($title);
		 	}
		 	if ($lang_prefix == 'es-us'){
		 		$link = "/$lang_prefix/recetas/categoria/" . remove_space($title);
		 	}

			$term_data[] = array(
			  'tid' => $term->tid,
			  'title' => $title,
			  'depth' => $term->depth,
			  'description' => $object_term->getDescription(),
			  'img_url' => $img_url,
			  'link' => $link,
			 );
		}
		return $term_data;
    }


    private function get_subcategories($category){
    	$langcode = get_current_langcode($with_prefix = false);
    	$tid = $this->get_category_tid($category, $langcode);
    	$vocabulary = 'recipe_search_category';
    	$lang_prefix = get_current_langcode();
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
		$term_data = [];
		foreach ($terms as $term) {
			if (empty($term->parents) || $term->parents[0] != $tid)
				// We only care about the terms whose parents are the current category
				continue;
			$object_term = Term::load($term->tid);
			$img_url = "";
			if (!$object_term->hasTranslation($langcode)){
				continue;
			}
			$term_trans = $object_term->getTranslation($langcode);
			// We want the image from the current language. If not available
			// get the image from the default language
			if (!empty($object_term->getTranslation($langcode)->field_category_image->target_id)){
				$img = \Drupal\file\Entity\File::load($object_term->getTranslation($langcode)->field_category_image->target_id);
	    		$img_url = file_create_url( $img->getFileUri() );
			}elseif (!empty($object_term->field_category_image->target_id)){
				$img = \Drupal\file\Entity\File::load($object_term->field_category_image->target_id);
	    		$img_url = file_create_url( $img->getFileUri() );
			}
			
			if ($term_trans){
				$title = $term_trans->name->value;
			} else {
				$title = $term->name;
			}

		 	$link = "/$lang_prefix/recipes/category/$category/" . remove_space($title);
		 	if ($lang_prefix == 'fr-ca'){
		 		$link = "/$lang_prefix/recettes/categorie/$category/" . remove_space($title);
		 	}
		 	if ($lang_prefix == 'es-us'){
		 		$link = "/$lang_prefix/recetas/categoria/$category/" . remove_space($title);
		 	}

			$term_data[] = array(
			  'tid' => $term->tid,
			  'title' => $title,
			  'parents' => $term->parents,
			  'depth' => $term->depth,
			  'description' => $object_term->getDescription(),
			  'img_url' => $img_url,
			  'link' => $link,
			 );
		}
		// debug_var($term_data, 1);
		return $term_data;
    }	

}