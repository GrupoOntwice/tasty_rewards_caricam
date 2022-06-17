
<?php
/**
php vendor/bin/drush php-script modules/custom/pepsibrands/drush_content_import.php missvickies flavours
*/

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

 if(count($args) == 3) {
 	$brand = $args[0];
 	$entity_type = $args[1];
 	$lang = $args[2];
 	echo " command ran with ...";
 	print_r($args);
 	brands_content_population($brand, $entity_type, $lang);
 } 
 elseif(count($args) == 1) {
 	$brand = $args[0];
 	$entity_types = ['slides','product_categories','product', 'recipe', 'videos'];
 	$languages = ['en', 'fr'];
 	$entities = [
 		'quaker' => ['contentblocks', 'product_categories', 'product',
 			'oatcarousel', 'powerofoats', 'recipe', 'slides', 'videos',
 			'years'],
 		'doritos' => ['contentblocks', 'slides', 'product_categories', 'product', 'videos'],
 		'ruffles' => ['contentblocks', 'slides', 'product_categories', 'product'],
 		'smartfood' => [ 'slides', 'product_categories', 'product', 'videos', 'contentblocks'],
 		'stacys' => [ 'slides', 'product_categories', 'product', 'recipe', 'contentblocks', 'collections'],
 		'sunchips' => [ 'slides', 'product_categories', 'product', 'contentblocks'],
 		'pearlmilling' => [ 'slides', 'years'],
 		'capncrunch' => [ 'slides', 'product', 'recipe'],
        'popcorners' => [ 'product', 'slides'],
 	];

 	// foreach ($entity_types as  $entity_type) {
 	foreach ($entities[$brand] as $entity_type) {
 		foreach ($languages as  $lang) {
 			echo "Importing $brand $entity_type  -  $lang ...\n";
 			brands_content_population($brand, $entity_type, $lang);
 		}
 	}
 	// brands_content_population('tostitos', 'product', 'en');

 }
 // brands_content_population('tostitos', 'product', 'fr');
 // brands_content_population('lays', 'slides', 'en');
//die(" DONE\n");
