<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/sourceid_report.php start_date end_date
 */
use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}


$country = 'canada';
$end_date = '';
if (count($args) > 0){
	$enterdate = $args[0];
	if (count($args) > 1){
		$end_date = $args[1];
		$start_date = $args[0];
	}

	if (count($args) > 2)
		$country = $args[2];

} else {
	echo " please provide the date as argument\n";
	return;
}

$obj = new ContentExport();


if ($end_date){
	$now = time(); // or your date as well
	$start_time = strtotime($start_date);
	$end_time = strtotime($end_date);
	$datediff = $end_time - $start_time;

	$nb_days = round($datediff / (60 * 60 * 24));

	for ($i = 0; $i < $nb_days; $i++){
		$enterdate = date('Y-m-d', strtotime($start_date . " +$i day"));
		$obj->setCountBySourceID($enterdate, $country);
	}
} else {
	$obj->setCountBySourceID($enterdate, $country);
}



