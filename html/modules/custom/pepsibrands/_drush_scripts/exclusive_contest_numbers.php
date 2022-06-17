<?php 

/**
To run this script, do the following command:
	php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/exclusive_contest_numbers.php 


*/

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

// pfc_crosssell_users  pepsicontest_reg_contest
function drush_get_exclusive_contest($contest_id){
	$sql = "SELECT * from pepsicontest_reg_contest WHERE 1
				AND contest_id = $contest_id
				limit 2000
	";
	$query = \Drupal::database()->query($sql);
	$results = $query->fetchAll();
	return $results;
}

if (count($args) != 1){
	echo " you must pass the contest_id as argument \n";
	return;
}

$contest_id = $args[0];

$exclusive_entries = drush_get_exclusive_contest($contest_id);
$count = 0;
$total = 0;
foreach ($exclusive_entries as $key => $obj) {
	$total++;
	$email = $obj->email;
	$enterdate = $obj->enterdate;
	$user = user_load_by_mail($email);
	$created_timestamp = $user->created->value;
	$created_date = gmdate("Y-m-d", $created_timestamp);
	if ($created_date < $enterdate){
		$count++;
	}

}
echo " Total users who already had an account = $count / $total \n";
echo "Net new signups =  " . ($total - $count) . "\n";
// debug_var($exclusive_entries)