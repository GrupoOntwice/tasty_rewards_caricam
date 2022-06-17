<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/export_recipes.php 

 */


use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

function generate_recipes_csv(){
    echo "Exporting Recipes... \n";

    $filename = "../export_contest/recipes" . date('Y-m-d') .  " .csv";
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'recipe');
    $query->condition('langcode', 'en');
    $not_brand_recipe = $query->orConditionGroup()
				->notExists('field_brand_website')
				->condition('field_brand_website', 'Tastyrewards')
				->condition('field_brand_website',NULL,'=');
	$query->condition($not_brand_recipe);

    $query->condition('status', 1);
    $entity_ids = $query->execute();
    // recipe ID, recipe name, url, tags (spicy,...)

    if (!empty($entity_ids)){
        $nids = array_values($entity_ids);
    	$headers = ['nid', 'brand', 'title_en', 'title_fr', 'tags', 'url_en', 'url_fr'];

        $empty_values = [];
        for ($i = 0; $i < count($headers); $i++){
            $empty_values[$i] = '';
        }

	    $content = '';
	    $delimiter = '","';
	    $csv_contents = [];
	    $content .= '"'.  implode($delimiter,$headers) . '"' ."\n";
	    $host = "https://www.tastyrewards.com";
        foreach ($nids as  $nid) {
            $node = \Drupal\node\Entity\Node::load($nid);

            $_product = $empty_values;
	    	$_product[array_search('nid', $headers)] = $nid;
	        $_product[array_search('title_en', $headers)] = $node->getTitle();

	        $_product[array_search('title_fr', $headers)] = '';
	        $_product[array_search('url_fr', $headers)] = '';
	        $keywords_en = ContentQueries::instance()->get_recipe_keywords($node,'en', $synonym = false);
		    $_product[array_search('tags', $headers)] = $keywords_en;

	        if ($node->hasTranslation('fr')){
		        $_product[array_search('title_fr', $headers)] = $node->getTranslation('fr')->getTitle();
		        $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, 'fr');
			    $url_fr = $host . "/fr-ca"  . $alias;

		        $_product[array_search('url_fr', $headers)] = $url_fr;
	        }

	        $sponsors = $node->get('field_recipe_sponsor')->referencedEntities();

            if (empty($sponsors)){
            	$brand = '';
            } else  {
            	$sponsor_ids = array_values($sponsors);
            	$node_brand = $sponsor_ids[0];
            	$brand = $node_brand->getTitle();
            }

	        $_product[array_search('brand', $headers)] = $brand;


	        // $category = '';
	        // $product_category_id = $node->field_product_category->target_id;
	        // if (!empty($product_category_id)){
	        // 	$term_category = \Drupal\taxonomy\Entity\Term::load($product_category_id);
	        // 	$category = $term_category->field_subtitle->value;
	        // }

	        $subcategory = '';
	        $langcode = 'en';
	        $lang_prefix = 'en-ca';
	        $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    $url = $host . "/" . $lang_prefix . "" . $alias;

	        // $_product[array_search('subcategory', $headers)] = $subcategory;
	        $_product[array_search('url_en', $headers)] = $url;

	        $row = '"' .  implode($delimiter,$_product). '"' . "\n";
	        $row = remove_special_characters($row);
	        $content .= $row;
	        $csv_contents[$nid] .= $row;
        }


	    if (!file_put_contents($filename, $content)){
            log_var(" $csv_filename could not be created", "CSV contest winners export");
        }
    }
}

$lang = 'en-us';
if (count($args) == 1){
	$lang = $args[0];
}

// generate_recipes_csv();
$obj = new ContentExport();
$obj->exportRecipes($lang);
echo "Exporting recipes\n";


/*

python code to move the images in the export folder.

import shutil
from urllib.parse import unquote

images = ["/http.apache/www.tastyrewards.com/html/sites/default/files/2016-08/PFC-CRM_recipes_FreshStartMuffins_1200x1200_2.jpg",
 .....
 .....

"/http.apache/www.tastyrewards.com/html/sites/default/files/2020-07/Doritos-Taco-Salad-Bowl_1200x1200.jpg",
"/http.apache/www.tastyrewards.com/html/sites/default/files/2021-08/Baked%20Oatmeal%20Cups.jpg"]


for i, img_path in enumerate(images):
	dest_folder = '/http.apache/www.tastyrewards.com/export_contest/images_recipes'
	path_decoded = unquote(img_path)
	shutil.copy(path_decoded, dest_folder)
	

*/