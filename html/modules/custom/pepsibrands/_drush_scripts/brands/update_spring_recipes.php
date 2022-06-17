<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/brands/update_spring_recipes.php  
 */
use Drupal\pepsibam\ContentQueries;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

$spring_recipes_title = [
	"zesty-macaroni-salad",
	"fritosr-taco-salad",
	"california-sushi-nachos-0",
	"fresh-cherry-tomato-avocado-salad-cups",
	"chilaquiles-con-tostitosr",
	"warm-tostitosr-buffalo-chicken-dip-tortilla-chips",
	"greek-dip",
	"grilled-pineapple-guacamole",
	"mtn-dewr-dew-punch",
	"paloma-picante-cocktail",
	"pepsir-bourbon-milkshake",
	"tropic-its-hot",
];

function untag_all_spring_recipes(){
	$query = \Drupal::entityQuery('node');
	$query->condition('type', 'recipe');
	$query->condition('status', 1);
	// $query->condition('langcode', $langcode);
	// $is_not_brand_recipe = $query->orConditionGroup()
		// ->condition('field_brand_website', 'Tastyrewards' )
		// ->notExists('field_brand_website')
		// ->condition('field_brand_website',NULL,'=');
	// $query->condition($is_not_brand_recipe);
	$query->condition('field_sub_brand', 'springactivation');

	$entity_ids = $query->execute();
	$languages = ['en', 'fr', 'en-us', 'es-us'];
	foreach ($entity_ids as $nid) {
		$node = \Drupal\node\Entity\Node::load($nid); 
		$node->field_sub_brand->value = null;
		$node->save();
	}


}

function set_recipes_as_spring($spring_recipes_title){
	$query = \Drupal::entityQuery('node');
	$query->condition('type', 'recipe');
	$query->condition('status', 1);
	$query->condition('langcode', 'en-us');
	$entity_ids = $query->execute();

	foreach ($entity_ids as $nid) {
		$node = \Drupal\node\Entity\Node::load($nid); 

		$title = strtolower(  str_replace(" ", "-", $node->field_recipe_subtitle->value) );

		if (in_array($title, $spring_recipes_title)){
			$node->set('field_sub_brand', 'springactivation');
			$node->save();
		}

	}



}


set_recipes_as_spring($spring_recipes_title);

// untag_all_spring_recipes();
