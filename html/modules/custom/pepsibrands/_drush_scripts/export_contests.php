<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/export_contests.php  1000

  To export all  users in 1 batch, just do the following make sure to  call generate_users_csv()
 */
use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

$start_date = '';
$end_date = '';

if (count($args) > 1 ){
	$start_date = $args[0];
	$end_date = $args[1];
}


$obj = new ContentExport();

$obj->exportContests('ca', $start_date, $end_date);
// $obj->assignUserSourceidByContest($contest_id = 3595);