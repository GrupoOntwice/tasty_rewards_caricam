<?php 

/**
 * To run this use the following command
 * php vendor/bin/drush php-script modules/custom/pepsibrands/_drush_scripts/export_users.php  1000

  To export all  users in 1 batch, just do the following make sure to  call generate_users_csv()
 */
use Drupal\pepsibam\ContentQueries;
use Drupal\pepsibam\ContentExport;


$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
	$args[] = $arg;
}

function count_lines($filename){
	$linecount = 0;
	if (file_exists($filename) ){
		$handle = fopen($filename, "r");
		while(!feof($handle)){
		  $line = fgets($handle);
		  $linecount++;
		}
		if ($linecount > 1)
			$linecount--;

		fclose($handle);
	}

	return $linecount;
}


function generate_users_csv($args){
    echo "Exporting Users... \n";

    $filename = "../export_contest/users_" . date('Y-m') .  ".csv";
    $nb_lines = count_lines($filename);
    echo "nb lines = $nb_lines \n";
    $export_size = 1000;
    if (count($args) > 0){
    	$export_size = $args[0];
    }

    $query = \Drupal::entityQuery('user');
    $is_CA_user = $query->orConditionGroup()
				->condition('preferred_langcode', 'en')
				->condition('preferred_langcode', 'fr');
	$query->condition($is_CA_user);
    $query->condition('status', 1);
    $query->range($nb_lines, $export_size);
    $query->sort('uid', 'ASC');

    $entity_ids = $query->execute();
    // recipe ID, recipe name, url, tags (spicy,...)

    if (!empty($entity_ids)){
        $user_ids = array_values($entity_ids);
    	// $headers = ['user_id', 'email', 'firstname', 'lastname', 'city', 'province', 'postalcode',
    	// 	'gender', 'bday', 'langcode'
    	// ];

    	$headers = ['user_id', 'email', 'firstname', 'lastname', 'city', 'province', 'postalcode',
	    		'gender', 'bday', 'langcode', 'created', 'updated', 'last_login', 'source_id'
	    	];

        $empty_values = [];
        for ($i = 0; $i < count($headers); $i++){
            $empty_values[$i] = '';
        }

	    $content = '';
	    $delimiter = '","';
	    $csv_contents = [];
	    if ($nb_lines == 0 || $nb_lines == 1){
	    	$content .= '"'.  implode($delimiter,$headers) . '"' ."\n";
	    }
	    $host = "https://www.tastyrewards.com";
	    $obj = new ContentExport();
        foreach ($user_ids as  $uid) {
            $user = \Drupal\user\Entity\User::load($uid);

            if ($obj->isUsaUser($user)){
            	continue;
            }

            $_product = $empty_values;
	    	$_product[array_search('user_id', $headers)] = $uid;
	        $_product[array_search('email', $headers)] = $user->mail->value;
	        $_product[array_search('firstname', $headers)] = $user->field_firstname->value;
	        $_product[array_search('lastname', $headers)] = $user->field_lastname->value;
	        $_product[array_search('city', $headers)] = $user->field_city->value;
	        $_product[array_search('province', $headers)] = $user->field_province->value;
	        $_product[array_search('postalcode', $headers)] = $user->field_postalcode->value;
	        $_product[array_search('gender', $headers)] = $user->field_gender->value;
	        $_product[array_search('bday', $headers)] = $user->field_bday->value;
	        $_product[array_search('langcode', $headers)] = $user->preferred_langcode->value;

	        $_product[array_search('created', $headers)] = $user->created->value? date("Y-m-d H:i:s", $user->created->value) : '';
	        $_product[array_search('updated', $headers)] = $user->changed->value? date("Y-m-d H:i:s", $user->changed->value) : '';

		    $_product[array_search('last_login', $headers)] = $user->login->value? date("Y-m-d H:i:s", $user->login->value) : '';

		    $_product[array_search('source_id', $headers)] = $user->field_source_id->value? $user->field_source_id->value : '';



	        $row = '"' .  implode($delimiter,$_product). '"' . "\n";
	        $row = remove_special_characters($row);
	        $content .= $row;
	        $csv_contents[$nid] .= $row;
        }


	    if (!file_put_contents($filename, $content, FILE_APPEND | LOCK_EX)){
            log_var(" $csv_filename could not be created", "CSV contest winners export");
        }
    } else {
    	echo "No users to export \n";
    }
}

$start_date = '';
$end_date = '';
if (count($args) > 1){
	$start_date = $args[0];
	$end_date = $args[1];
}

// generate_users_csv($args);

$obj = new ContentExport();

$obj->exportUsers($start_date, $end_date, 'ca');
// $obj->exportUsersActivity( $args);
// $obj->assignUserSourceidByContest($contest_id = 3595);