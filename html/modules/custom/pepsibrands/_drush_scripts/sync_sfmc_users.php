<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/sync_sfmc_users.php 

 */


use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

$limit = 10;
if (count($args) > 0 ){
	$limit = $args[0];
}


$obj = new ContentExport();
echo "Syncing USA users to salesforce...\n";
$obj->syncSfmcUsers($limit);