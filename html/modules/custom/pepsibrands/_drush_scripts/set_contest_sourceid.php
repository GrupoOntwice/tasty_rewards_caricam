<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/set_contest_sourceid.php  1000
 */
use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

$show_uid = 0;

if (count($args) < 1 || count($args) > 2  ){
	echo "this drush script should be run with contest__id as argument\n";
	return ;
}

if (count($args) == 2)
	$show_uid = $args[1];


$contest_id = $args[0];
echo "processing contest ID $contest_id\n";

$obj = new ContentExport();
// $obj->exportUsersActivity( $args);
$obj->assignUserSourceidByContest($contest_id, $show_uid);