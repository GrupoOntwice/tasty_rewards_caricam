<?php 
/*
	To run this script use the following command:


 	php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/contest_gmail_bot_detection.php 2021-11-28  {save_result}
*/

use Drupal\pepsibam\BotIdentification;

$args = [];

foreach($extra as $arg) {
	$args[] = $arg;
}

$enterdate = '';
$save_result = false;
if (count($args) > 0){
	$enterdate = $args[0];
	if (count($args) > 1)
		$save_result = $args[1];
}

echo "find bots for date $enterdate \n";
$obj = new BotIdentification();

$obj->setContestDate($enterdate);
$obj->setDrushFlag(true);
$obj->save_results = $save_result;
$obj->flagBotGmailEntries($enterdate);

