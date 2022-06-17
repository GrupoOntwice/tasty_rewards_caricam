<?php 


/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/export_products.php

 */

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

function generate_product_csv($brand = ''){
    echo "Exporting products... \n";

    $filename = "../export_contest/products" . date('Y-m-d') .  " .csv";
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'product');
    $query->condition('status', 1);
    if (!empty($brand)){
    	$query->condition('field_brand', $brand);
    }

    $product_ids = $query->execute();
    if (!empty($product_ids)){
        $nids = array_values($product_ids);
    	$headers = ['nid', 'title_en', 'title_fr', 'brand', 'category' , 'url_en', 'url_fr'];

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
	        $_product[array_search('brand', $headers)] = $node->field_brand->value;
	    	$_product[array_search('nid', $headers)] = $nid;
	        $_product[array_search('title_en', $headers)] = $node->getTitle();

	        $_product[array_search('title_fr', $headers)] = '';
	        $_product[array_search('url_fr', $headers)] = '';

	        if ($node->hasTranslation('fr')){
		        $_product[array_search('title_fr', $headers)] = $node->getTranslation('fr')->getTitle();
		        $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, 'fr');
			    $url_fr = $host . "/fr-ca"  . $alias;

		        $_product[array_search('url_fr', $headers)] = $url_fr;
	        }


	        $category = '';
	        $product_category_id = $node->field_product_category->target_id;
	        if (!empty($product_category_id)){
	        	$term_category = \Drupal\taxonomy\Entity\Term::load($product_category_id);
	        	$category = $term_category->field_subtitle->value;
	        }

	        $subcategory = '';
	        $langcode = 'en';
	        $lang_prefix = 'en-ca';
	        $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
		    $url = $host . "/" . $lang_prefix . "" . $alias;

	        $_product[array_search('category', $headers)] = $category;
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

$brand = '';
if (count($args) > 0){
	$brand = $args[0];
}


generate_product_csv($brand);