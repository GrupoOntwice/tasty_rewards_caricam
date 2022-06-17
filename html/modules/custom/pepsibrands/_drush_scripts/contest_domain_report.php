<?php 

/*
To run this script
php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/contest_domain_report.php
*/

$args = [];

foreach($extra as $arg) {
	$args[] = $arg;
}

bot_identification_report();