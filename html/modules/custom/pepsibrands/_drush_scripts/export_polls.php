<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/export_polls.php 
 */
use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}
$export_size = 3000;
if (count($args) > 0 ){
	$export_size = $args[0];
}



$obj = new ContentExport();
$obj->exportPolls($lang = 'ca' ,$export_size);