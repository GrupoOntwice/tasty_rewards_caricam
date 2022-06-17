<?php 


/**
To run this script, do the following command:
	php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/brand_sitemap.php 


*/

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

/**
*/

function export_brand_sitemap(){
	$brand_pages = [
		'tostitos' => ['homepage', 'recipes', 'all_recipes', 'products', 'products.categories',  'occasions', 'about-us'],
		'lays' => ['homepage', 'products', 'products.categories', 'brand.videos', 'about-us', 'realjoy.landing', 'realjoy.terms'],
		'cheetos' => ['homepage', 'recipes', 'products', 'products.categories', 'about-us', 'all_recipes'],
		'doritos' => ['homepage', 'products', 'products.categories', 'about-us'],
		'ruffles' => ['homepage', 'products', 'products.categories', 'about-us'],
		'smartfood' => ['homepage', 'products', 'products.categories', 'about-us'],
		'sunchips' => ['homepage', 'products', 'about-us'],
		'stacys' => ['homepage', 'products', 'products.categories', 'recipes', 'all_recipes' , 'collections', 'about-us'],
		'crispyminis' => ['homepage', 'products', 'products.categories',  'about-us', 'recipes', 'all_recipes'],
		'quaker' => ['homepage', 'recipes', 'products', 'products.categories', 'about-us', 'oat-flour', 'power-of-oats', 'quaker.landing', 'all_recipes'],
		'bare' => ['homepage'],
		'fritolayvarietypacks' => ['homepage'],
		'otep' => ['homepage'],
		'popcorners' => ['homepage'],
		'capncrunch' => ['homepage'],
		'flaminhot' => ['homepage', 'recipes', 'all_recipes'],
		'missvickies' => ['homepage', 'products', 'products.categories', 'about-us'],
	];

	$headers = ['brand','url_en', 'url_fr', 'url_en-us', 'url_es-us'];
	$empty_values = [];
    for ($i = 0; $i < count($headers); $i++){
        $empty_values[$i] = '';
    }

    $filename = "../export_contest/brands_urls_" . date('Y-m-d') .  ".csv";

    $content = '';
    $delimiter = '","';
    $csv_contents = [];
    $content .= '"'.  implode($delimiter,$headers) . '"' ."\n";


    $main_pages = drush_TR_main_pages();

	foreach ($main_pages as $title => $page) {
		$title =  'Tasty Rewards ' . $title;
		$_item = $empty_values;
		$_item[array_search('brand', $headers)] = $title;

		$_item[array_search('url_en', $headers)] = $page['en-ca'];
		$_item[array_search('url_fr', $headers)] = $page['fr-ca'];
		$_item[array_search('url_en-us', $headers)] = $page['en-us'];
		$_item[array_search('url_es-us', $headers)] = $page['es-us'];
		$row = '"' .  implode($delimiter,$_item). '"' . "\n";
		$row = remove_special_characters($row);
		$content .= $row;

	}

	// Export all life & articles content
	$articles = drush_all_contents('article');
	$recipes = drush_all_contents('recipe');
	foreach ($articles as $nid) {
		$_item = $empty_values;
		$_item[array_search('brand', $headers)] = '';

		$_item[array_search('url_fr', $headers)] = drush_get_content_alias($nid, 'fr');
		$_item[array_search('url_en', $headers)] = drush_get_content_alias($nid, 'en');				
		$_item[array_search('url_en-us', $headers)] = drush_get_content_alias($nid, 'en-us');				
		$_item[array_search('url_es-us', $headers)] = drush_get_content_alias($nid, 'es-us');				
		$row = '"' .  implode($delimiter,$_item). '"' . "\n";
		$row = remove_special_characters($row);
		$content .= $row;
	}	

	foreach ($recipes as $nid) {
		$_item = $empty_values;
		$_item[array_search('brand', $headers)] = '';

		$_item[array_search('url_fr', $headers)] = drush_get_content_alias($nid, 'fr');
		$_item[array_search('url_en', $headers)] = drush_get_content_alias($nid, 'en');				
		$_item[array_search('url_en-us', $headers)] = drush_get_content_alias($nid, 'en-us');				
		$_item[array_search('url_es-us', $headers)] = drush_get_content_alias($nid, 'es-us');				
		$row = '"' .  implode($delimiter,$_item). '"' . "\n";
		$row = remove_special_characters($row);
		$content .= $row;
	}

	$brand_urls = [];
	foreach ($brand_pages as $brand => $pages) {
		foreach ($pages as $page) {
			$_item = $empty_values;

			if ($page == "products.categories"){
				$categories = get_product_category_ids($brand);
				// $nb = count($categories); 
				// echo "$brand has $nb categories \n";
				foreach ($categories as $tid) {
					$_item = $empty_values;
					$_item[array_search('brand', $headers)] = $brand;
					$brand_urls[] = [
						'en' => get_category_url($tid, 'en'),
						'fr' => get_category_url($tid, 'fr'),
					];
					$_item[array_search('url_fr', $headers)] = get_category_url($tid, 'fr');
					$_item[array_search('url_en', $headers)] = get_category_url($tid, 'en');				
					$row = '"' .  implode($delimiter,$_item). '"' . "\n";
	        		$row = remove_special_characters($row);
	        		$content .= $row;
				}

			} else {
				$_item[array_search('brand', $headers)] = $brand;
				$_item[array_search('url_fr', $headers)] = get_brand_url($brand, $page, 'fr');
				$_item[array_search('url_en', $headers)] = get_brand_url($brand, $page, 'en');

				$row = '"' .  implode($delimiter,$_item). '"' . "\n";
	        	$row = remove_special_characters($row);
	        	$content .= $row;
	        	if ($page == 'products'){
	        		$products = get_products_by_brand($brand);
					foreach ($products as $nid) {
						$_item = $empty_values;
						$_item[array_search('brand', $headers)] = $brand;

						$_item[array_search('url_fr', $headers)] = get_product_url($nid, 'fr');
						$_item[array_search('url_en', $headers)] = get_product_url($nid, 'en');				
						$row = '"' .  implode($delimiter,$_item). '"' . "\n";
		        		$row = remove_special_characters($row);
		        		$content .= $row;
					}

	        	}

	        	if ($page == 'all_recipes'){
					$recipes = all_recipes_by_brand($brand);
					foreach ($recipes as $nid) {
						$_item = $empty_values;
						$_item[array_search('brand', $headers)] = $brand;

						$_item[array_search('url_fr', $headers)] = get_product_url($nid, 'fr');
						$_item[array_search('url_en', $headers)] = get_product_url($nid, 'en');				
						$row = '"' .  implode($delimiter,$_item). '"' . "\n";
		        		$row = remove_special_characters($row);
		        		$content .= $row;
					}	        		
	        	}
			}
		}
	}

		




	if (!file_put_contents($filename, $content)){
        log_var(" $csv_filename could not be created", "CSV contest winners export");
    }
    echo "File created at $filename \n";
	

}

function drush_TR_main_pages(){
	$host = "https://www.tastyrewards.com";

	$pages = [
		'Home Page' => [
			'en-ca' => $host . '/en-ca',
			'fr-ca' => $host . '/fr-ca',
			'en-us' => $host . '/en-us',
			'es-us' => $host . '/es-us',
		],
		'Brands' => [
			'en-ca' => $host . '/en-ca',
			'fr-ca' => $host . '/fr-ca',
			'en-us' =>  '',
			'es-us' => '',
		],
		'Coupons' => [
			'en-ca' => $host . '/en-ca/coupons',
			'fr-ca' => $host . '/fr-ca/coupons',
			'en-us' => $host . '/en-us/coupons',
			'es-us' => $host . '/es-us/cupones',
		], 
		'Contest/Sweepstakes' => [
			'en-ca' => $host . '/en-ca/contests',
			'fr-ca' => $host . '/fr-ca/concours',
			'en-us' => $host . '/en-us/sweepstakes-contests',
			'es-us' => $host . '/es-us/sorteos-concursos',
		], 
		'Life' => [
			'en-ca' => $host . '/en-ca/life',
			'fr-ca' => $host . '/fr-ca/mode-de-vie',
			'en-us' => $host . '/en-us/life',
			'es-us' => $host . '/es-us/modo-de-vida',
		], 
		'Recipes' => [
			'en-ca' => $host . '/en-ca/recipes',
			'fr-ca' => $host . '/fr-ca/recettes',
			'en-us' => $host . '/en-us/recipes',
			'es-us' => $host . '/es-us/recetas',
		], 
		'About Us' => [
			'en-ca' => $host . '/en-ca/about-tasty-rewards',
			'fr-ca' => $host . '/fr-ca/a-propos',
			'en-us' => $host . '/en-us/about-tasty-rewards',
			'es-us' => $host . '/es-us/sobre-nosotros',
		], 
		 
	];

	return $pages;
}

function drush_get_content_alias($nid, $langcode){

	$host = "https://www.tastyrewards.com";
	$node = \Drupal\node\Entity\Node::load($nid);
	if (!$node->hasTranslation($langcode)){
		return '';
	}

	$node = $node->getTranslation($langcode);
	if (!$node->status->value)
		return '';

	$alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
	if (in_array($langcode, ['en-us', 'es-us'])){
		$langprefix = $langcode;
	} else {
		$langprefix = $langcode . "-ca";
	}
    $link = $host . "/$langprefix" . $alias;
    return $link;

}

function drush_all_contents($type){
	$query = \Drupal::entityQuery('node');
	$query->condition('type', $type);
	$query->condition('status', 1);
	// $query->condition('field_brand', $brand);
	if ($type == 'recipe'){
		$not_brand_recipe = $query->orConditionGroup()
				->notExists('field_brand_website')
				->condition('field_brand_website', 'Tastyrewards')
				->condition('field_brand_website',NULL,'=');
		$query->condition($not_brand_recipe);
	}
	$entity_ids = $query->execute();
	if (empty($entity_ids))
		return [];


	$nids = array_values($entity_ids);
	return $nids;

}

function get_products_by_brand($brand){
	$query = \Drupal::entityQuery('node');
	$query->condition('type', 'product');
	$query->condition('status', 1);
	$query->condition('field_brand', $brand);
	$entity_ids = $query->execute();
	if (empty($entity_ids))
		return [];


	$nids = array_values($entity_ids);
	return $nids;

}

function all_recipes_by_brand($brand){

	$query = \Drupal::entityQuery('node');
	$query->condition('type', 'recipe');
	$query->condition('status', 1);
	$query->condition('field_brand_website', $brand);
	$entity_ids = $query->execute();
	if (empty($entity_ids))
		return [];


	$nids = array_values($entity_ids);
	return $nids;

}


function get_product_url($nid, $langcode){

	$host = "https://www.tastyrewards.com";
	$alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
    $link = $host . "/$langcode-ca" . $alias;
    return $link;

}

function get_category_url($tid, $langcode){
	$term = \Drupal\taxonomy\Entity\Term::load($tid);
	if (empty($term)){
		return null;
	}


	$term_en = $term;
	$category = [];
	// field_product_category_image, field_product_link, 
	if ($langcode != 'en'){
    	if (!$term->hasTranslation('fr'))
			return null;
    	$term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
    }
    $host = "https://www.tastyrewards.com";

	$link = $term->field_product_link->getValue()[0]['uri'];
	$link = $host . "/$langcode-ca" . strtolower(str_replace("internal:", "", $link) );
	echo "$link \n";
	return $link;
}

function get_product_category_ids($brand){

	$query = \Drupal::entityQuery('taxonomy_term');
	$query->condition('vid', 'product_categories');
	$query->condition('status', 1);
	$query->condition('field_brand', $brand);
	$query->sort('field_order', 'ASC');
	$entity_ids = $query->execute();
	if (empty($entity_ids))
		return [];

	$tids = array_values($entity_ids);
	return $tids;
}


function get_brand_url($brand, $page, $langcode, $category = ''){
	$route_name = $langcode . "-ca.pepsibrands." . $page;

	// if category route, requires additional param {cateogry}

	$url = "https://www.tastyrewards.com";
	try{
		if (!empty($category)){
			$url .=  \Drupal\Core\Url::fromRoute($route_name, ['brand' => $brand, 'category' => $category])->toString();
		} else {
			$url .=  \Drupal\Core\Url::fromRoute($route_name, ['brand' => $brand])->toString();
		}
	} catch(\Exception $e){
		$url = '';
	}
	echo "$url \n";
	if ($langcode ==  'fr')
		$url = str_replace("/en-ca/", "/fr-ca/", $url);
	return $url;
	
}

export_brand_sitemap();