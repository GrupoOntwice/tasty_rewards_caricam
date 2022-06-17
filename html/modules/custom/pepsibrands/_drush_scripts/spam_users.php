<?php 


/**
To run this script, do the following command:
	php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/spam_users.php 


*/

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

function flag_spam_users($arguments){

	$limit = 10;
	$offset = 0;
	if (count($arguments) > 0 && is_numeric($arguments[0]) ){
		$limit = $arguments[0];
		if (count($arguments) > 1){
			$offset = $arguments[1];
		} 
	} 
	print_r($offset);
	print_r($arguments);
	$spam_contest_entries = get_spam_entries($limit, $offset);
	foreach ($spam_contest_entries as $key => $obj) {
		$uid = $obj->user_id;
		$user = \Drupal\user\Entity\User::load($uid);
		if (empty($user)){
			echo " USER not found  = $uid \n";
			continue;
		}
		$user->set('field_black_listed', 1);
		echo " USER ID = $uid \n";
		try{
			$user->save();
		} catch(\Exception $e){
			log_var($e, " Could not save user", "spam_table");
		}
	}

}

function get_spam_entries($limit, $offset = 0){
	// $sql = "SELECT * from csv_usa_users as t1  where  t1.email like '" . $email . "' and NOT EXISTS (SELECT 1  from csv_users_imported as t2 WHERE t2.email = t1.email and t2.bonus > 0)";
	
	$sql = " SELECT distinct(user_id) from pepsicontest_reg_contest as t1
	WHERE ( spam = 1 OR spam = 5)
	and NOT EXISTS (SELECT 1 from user__field_black_listed as t2 WHERE t2.entity_id = t1.user_id and t2.field_black_listed_value = 1 )
	-- order by user_id asc
	limit $limit  offset $offset
	";

	// echo "$sql \n";
	
	$query = \Drupal::database()->query($sql);
	$result = $query->fetchAll();
	return $result;
}

flag_spam_users($args);