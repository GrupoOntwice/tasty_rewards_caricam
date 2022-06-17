<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/send_stacys_email.php
 */
use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibrands\EmailManager;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

function send_riseproject_emails(){
	$sql = "SELECT email, firstname, language from stacys_rise_entries
				WHERE email_sent = 0 
				limit 1000 ";

	$query = \Drupal::database()->query($sql);
	$results = $query->fetchAll();

	$obj = new EmailManager();
	if (!empty($results)){
		$count = count($results);
		echo " sending email to $count recipients \n";
		foreach ($results as $key => $res) {
			$email = $res->email;
			$firstname = $res->firstname;
			$language = !empty($res->language)? $res->language : 'en';
			$obj->sendStacysEmail($email, $firstname , $language );
		}
	}


}


// send_riseproject_emails();