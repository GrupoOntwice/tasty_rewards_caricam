<?php

/**
 * @file
 */

namespace Drupal\pepsibam;

use Drupal\pepsibam\CronScheduledTasks;
use Drupal\taxonomy\Entity\Term;

class ContentExport{

	public static function instance(){
		return new ContentExport();
	}

	public function remove_html($text){
		$text = strip_tags($text);
		$text = str_replace('"', "'", $text);
		return $text;

	}


	public function exportRecipes($langcode){
		$filename = "../export_contest/recipes" . date('Y-m-d') .  "_$langcode.csv";
	    $query = \Drupal::entityQuery('node');
	    $query->condition('type', 'recipe');
	    $query->condition('langcode', $langcode);
	    $not_brand_recipe = $query->orConditionGroup()
					->notExists('field_brand_website')
					->condition('field_brand_website', 'Tastyrewards')
					->condition('field_brand_website',NULL,'=');
		$query->condition($not_brand_recipe);

	    $query->condition('status', 1);
	    $entity_ids = $query->execute();
	    // recipe ID, recipe name, url, tags (spicy,...)

	    if (!empty($entity_ids)){
	        $nids = array_values($entity_ids);
	        //  Recipe Name, Description, Ingredients, Directions, Featured Brand, and if we can have recipe categories/keywords,
	    	$headers = ['nid', 'title', 'description', 'ingredients', 'directions', 'brand_sponsor', 'tags', 'url', 'image'];

	        $empty_values = [];
	        for ($i = 0; $i < count($headers); $i++){
	            $empty_values[$i] = '';
	        }

		    $content = '';
		    $delimiter = '","';
		    $csv_contents = [];
		    $content .= '"'.  implode($delimiter,$headers) . '"' ."\n";
		    $host = "https://www.tastyrewards.com";
	        foreach ($nids as  $nid) {
	            $node = \Drupal\node\Entity\Node::load($nid);
	            if (!$node->hasTranslation($langcode))
	            	continue;

	            $node = $node->getTranslation($langcode);

	            $sponsors = $node->get('field_recipe_sponsor')->referencedEntities();

	            if (empty($sponsors)){
	            	$brand = '';
	            } else  {
	            	$sponsor_ids = array_values($sponsors);
	            	$node_brand = $sponsor_ids[0];
	            	$brand = $node_brand->getTitle();
	            }

	            $_product = $empty_values;
		        $_product[array_search('brand', $headers)] = $node->field_brand->value;
		        $_product[array_search('ingredients', $headers)] = $this->remove_html($node->field_recipe_ingredients->value);
		        // $_product[array_search('ingredients', $headers)] = "----";
		        // field_recipe_how_to_make
		    	$_product[array_search('nid', $headers)] = $nid;
		    	$_product[array_search('description', $headers)] = $this->remove_html($node->body->value);
		    	$_product[array_search('directions', $headers)] = $this->remove_html($node->field_recipe_how_to_make->value);
		        $_product[array_search('brand_sponsor', $headers)] = $brand;
		        $_product[array_search('title', $headers)] = $node->getTitle();
				
		        $keywords = ContentQueries::instance()->get_recipe_keywords($node,$langcode, $synonym = false);
			    $_product[array_search('tags', $headers)] = $keywords;

			    // Recipe image" 
			    // field_recipe_image or field_recipe_image_detail
			    $image_url = get_translated_image_url($node,'field_recipe_image', $langcode);
			    $image_url = str_replace("http://default/", "https://www.tastyrewards.com/", $image_url);
			    $_product[array_search('image', $headers)] = $image_url;



		        // $category = '';
		        // $product_category_id = $node->field_product_category->target_id;
		        // if (!empty($product_category_id)){
		        // 	$term_category = \Drupal\taxonomy\Entity\Term::load($product_category_id);
		        // 	$category = $term_category->field_subtitle->value;
		        // }

		        $subcategory = '';
		        // $langcode = 'en';
		        $lang_prefix = 'en-us';
		        $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
			    $url = $host . "/" . $lang_prefix . "" . $alias;

		        // $_product[array_search('subcategory', $headers)] = $subcategory;
		        $_product[array_search('url', $headers)] = $url;

		        $row = '"' .  implode($delimiter,$_product). '"' . "\n";
		        $row = remove_special_characters($row);
		        $content .= $row;
		        $csv_contents[$nid] .= $row;
	        }


		    if (!file_put_contents($filename, $content)){
	            log_var(" $csv_filename could not be created", "CSV contest winners export");
	        }
	    }
		
	}

	public function exportContests($country='ca', $start_date = '', $end_date = ''){
		$has_language = '';
		if ($country == 'usa'){
			$has_language = "AND language in ('en-us', 'es-us') ";
		} elseif($country == 'ca'){
			$has_language = "AND language NOT in ('en-us', 'es-us') ";
		}

		// @TODO: Add lastweek date as filter
		$yesterday = date("Y-m-d", strtotime("yesterday"));
		// $start_week = "2021-12-11";

		$and_conditions = " $has_language  
								AND enterdate = '$yesterday'
								AND synced = 0
								AND spam = 0
								AND contest_id <> 3562 
								-- exclude fritolay contest
							";


		if (!empty($start_date) && !empty($end_date)){
			$and_conditions = " $has_language  
								AND enterdate >= '$start_date'
								AND enterdate < '$end_date'
								AND spam = 0
								AND synced = 0
								AND contest_id <> 3562 
								-- exclude fritolay contest
							";

		}

		$sql_count = "SELECT count(*) as cnt from pepsicontest_reg_contest  WHERE 1 
				$and_conditions				
				-- limit 1000
				";
		$query_count = \Drupal::database()->query($sql_count);
		$res = $query_count->fetchAll();
		if (!empty($res)){
			$_cnt = $res[0]->cnt;
	    	echo " processing $_cnt contest entries \n";
		}


		$sql = "SELECT * from pepsicontest_reg_contest  WHERE 1 
				$and_conditions
				order by regdate desc
				-- limit 1000
				";

		$query = \Drupal::database()->query($sql);


        
        $results = $query->fetchAll();
        if (empty($start_date) || empty($end_date) ){
			$filename = "../export_contest/azcopy/contests_ca_" . $yesterday .  ".csv";
        } else {
			$filename = "../export_contest/azcopy/contests_ca_" . $start_date . "_" . $end_date . ".csv";
        }

        $headers = ['user_id', 'contest_name','first_name','last_name','email','gender','postalcode','province','city','language','enterdate','contest_optin', 'brand'];

        $empty_values = [];
        for ($i = 0; $i < count($headers); $i++){
            $empty_values[$i] = '';
        }

        $content = '';
	    $delimiter = '","';
	    $csv_contents = [];
	    $content .= '"'.  implode($delimiter,$headers) . '"' ."\n";


        foreach ($results as $key => $res) {

        	$contest_node = \Drupal\node\Entity\Node::load($res->contest_id);
        	if ($country == 'ca'){
        		if (!$contest_node->hasTranslation('en') && !$contest_node->hasTranslation('fr') ){
        			continue;
        		}
        	}

        	// @TODO: Check if user has matching active user_id
        	// only run this check for users with nomember = 1
        	if ( $res->nomember == 1 && !$this->isActiveUser($res->user_id)){
        		continue;
        	}

        	$_contest = $empty_values;
        	$_contest[array_search('user_id', $headers)] = $res->user_id;
        	$_contest[array_search('contest_name', $headers)] = $res->contest_name;
        	$_contest[array_search('first_name', $headers)] = $res->first_name;
        	$_contest[array_search('last_name', $headers)] = $res->last_name;
        	$_contest[array_search('email', $headers)] = $res->email;
        	$_contest[array_search('postalcode', $headers)] = $res->postalcode;
        	$_contest[array_search('province', $headers)] = $res->province;
        	$_contest[array_search('gender', $headers)] = $res->gender;
        	$_contest[array_search('city', $headers)] = $res->city;
        	$_contest[array_search('language', $headers)] = $res->language;
        	$_contest[array_search('enterdate', $headers)] = $res->enterdate;
        	$_contest[array_search('contest_optin', $headers)] = $res->contest_optin;


        	$_contest[array_search('brand', $headers)] = $contest_node->field_brand->value;

        	$row = '"' .  implode($delimiter,$_contest). '"' . "\n";
		    $row = remove_special_characters($row);
		    $content .= $row;
        	
        }

        $this->updateSyncFlag($and_conditions);

        if (!file_put_contents($filename, $content)){
            log_var(" $csv_filename could not be created", "CSV contest winners export");
        }
	}

	public function exportUsers($start_date = '', $end_date = '', $country = 'ca'){
		// @TODO: We need to  also add users who updated their profile data

		// $filename = "../export_contest/users" . date('Y-m-d') .  " .csv";
		$daily_export = false;
		if (empty($start_date) || empty($end_date) ){
			$daily_export = true;
			$yesterday = date("Y-m-d", strtotime("yesterday"));
			$start_datetime = date("Y-m-d H:i:s", strtotime("yesterday"));
			$filename = "../export_contest/azcopy/users_ca_" . $yesterday .  ".csv";
        } else {
			$filename = "../export_contest/azcopy/users_ca_" . $start_date . "_" . $end_date . ".csv";
			$start_datetime = date("Y-m-d H:i:s", strtotime($start_date));
			if ($start_datetime == false){
				// if no valid date is provided, we set the date to a distant future
				// so it is not taken into account
				$start_datetime = "2075-01-01 01:01:01";
			}
        }

	    // $nb_lines = count_lines($filename);
	    $nb_lines = 0;
	    $export_size = 1000;
	    // if (count($args) > 0){
	    // 	$export_size = $args[0];
	    // }

	    $timestamp_start = strtotime("24 hours ago");
	    // $timestamp_start = 1642793108;  // January 21st
	    $timestamp_end = '';

	    if (!empty($start_date) && !empty($end_date) ){
	    	$timestamp_start = strtotime($start_date);
	    	$timestamp_end = strtotime($end_date);
	    	$end_datetime = date("Y-m-d H:i:s", strtotime($end_date));
	    }

	    $query = \Drupal::entityQuery('user');
	    $is_CA_user = $query->orConditionGroup()
					->condition('preferred_langcode', 'en')
					->condition('preferred_langcode', 'fr');
		$query->condition($is_CA_user);

		// $query->condition('created' , $timestamp_start, '>');
		
		$changed_since_timestamp = $query->orConditionGroup()
						->condition('created' , $timestamp_start, '>')
						->condition('field_edit_date' , $start_datetime, '>');
		$query->condition($changed_since_timestamp);

		if (!empty($timestamp_end)){
			$query->condition('created' , $timestamp_end, '<');
			// $query->condition('changed' , $timestamp_end, '<');
			$query->condition('field_edit_date' , $end_datetime, '<');
		}

	    $query->condition('status', 1);
	    // $query->range($nb_lines, $export_size);
	    $query->sort('field_created_date', 'DESC');

	    $entity_ids = $query->execute();
	    // recipe ID, recipe name, url, tags (spicy,...)

	    if (!empty($entity_ids)){
	        $user_ids = array_values($entity_ids);
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
		    $nb_users = count($user_ids);
	    	echo " Exporting = $nb_users \n";

	    	$counter = 0;

	        foreach ($user_ids as  $uid) {
	            $user = \Drupal\user\Entity\User::load($uid);

	            $counter++;

	            if ($counter > 1 & ($counter%1000) == 0 ){
	            	echo " $counter users processed \n";
	            }

	            if ($this->isUsaUser($user)){
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


		        $_product[array_search('created', $headers)] = date("Y-m-d H:i:s", $user->created->value);
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

	public function isUsaUser($user){
		$postalcode = $user->field_postalcode->value;
		$sourceid = $user->field_source_id->value;
		if (preg_match('/[0-9]{5}/', $postalcode) || substr( $sourceid, 0, 3 ) === "US_" ){
			return true;
		}

		return false;

	}

	public function updateSyncFlag($conditions){
		if (empty($conditions))
			return;
		$sql = "UPDATE pepsicontest_reg_contest
					SET synced = 2
		  		WHERE 1 
				$conditions				
				";

		$query = \Drupal::database()->query($sql);
		$query->execute();

	}

	public function assignUserSourceidByContest($contest_id, $show_uid = 0){
		$contest_entries = $this->getFirstContestEntries($contest_id);
		$contest_node = \Drupal\node\Entity\Node::load($contest_id);
		
		if ($contest_node == null || $contest_node->getType() !== 'contest'){
			echo "nid passed does not belong to a contest \n";
			return false;
		}
		$source_id = $contest_node->field_source_id->value;
		$total = count($contest_entries);
		$count = 0;
		echo " total contest entries $total \n";

		foreach ($contest_entries as $key => $obj) {
			$count++;
			$user = \Drupal\user\Entity\User::load($obj->user_id);
			date_default_timezone_set("America/Toronto");
			$created =  date("Y-m-d H:i:s", $user->created->value);
			$regdate = $obj->regdate;
			$is_newmember = intval($obj->nomember);
			$date1 = new \DateTime($created);
			$date2 = new \DateTime($regdate);
			$interval = $date1->diff($date2);
			if ($count%1000 == 0 ){
				echo " $count processed \n";
			}
			$source_id = $this->getSourceID($contest_node, $obj->language);
			if (empty($source_id)){
				echo "Contest $contest_id does not have a sourceID value for " . $obj->language . "\n";
				continue;
			}

			if ($interval->days === 0 || $interval->days == 1){
				if ($interval->i <= 1 && $interval->h === 0 && $is_newmember){
					// @TODO: Check if user sourceid is empty first
					if (!$user->field_source_id || empty($user->field_source_id->value) ){
						if ($show_uid){
							echo $obj->user_id . "\n";
						}
						$user->set('field_source_id', $source_id);
						$user->save();
						$message = "updated user: " . $user->id() . " with sourceID = $source_id ";
						append_log($message, $log_type = "info", $filename = 'sourceid_upates');
					}
				}
				// update the user sourceID here
				if ($interval->h > 1 && $interval->h < 8){
					$message = "failed to update user: " . $user->id() . " with sourceID = $source_id ";
					$message .= "created = $created & regdate = $regdate time diff = " . $interval->h;
					append_log($message, $log_type = "info", $filename = 'timezone_sourceid');
				}
			}

		}
	}

	public function getSourceID($node, $language){
		if ($node->hasTranslation($language) ){			
			return $node->getTranslation($language)->field_source_id->value;
		}

		return $node->field_source_id->value;
	}

	public function getFirstContestEntries($contest_id){
		// if special contest add and EXISTS (SELECT 1 from users_field_data as t3 WHERE t3.uid = t1.user_id)
		$sql = "SELECT t1.user_id, t1.regdate, t1.nomember, t1.language  FROM `pepsicontest_reg_contest` as t1
			join (
				SELECT MIN(regdate) as min_date, user_id
				from pepsicontest_reg_contest 
				where contest_id = $contest_id
				 and nomember = 1
				group by user_id
			) as t2 on t1.user_id = t2.user_id and t1.regdate = t2.min_date
			and t1.contest_id = $contest_id

			and NOT EXISTS (SELECT 1 from user__field_source_id as t3 WHERE t3.entity_id = t1.user_id)
			
			order by t1.regdate asc";

		$query = \Drupal::database()->query($sql);
		$result = $query->fetchAll();

		if (!empty($result)){
			return $result;
		}
		return [];
	}

	public function exportUsersActivity($args){

		/*
		Account Creation Date
		Last PTR US Website Login Date
		Last Sweepstakes Entry Date
		*/

		$filename = "../export_contest/users_activity" . date('Y-m-d') .  ".csv";
	    $nb_lines = count_lines($filename);
	    echo "nb lines = $nb_lines \n";
	    $export_size = 50;
	    $with_contest_entry = 0;
	    if (count($args) > 0){
	    	$export_size = $args[0];
	    	if (count($args) > 1 ){
	    		$with_contest_entry = $args[1];
	    	}
	    }

	    $query = \Drupal::entityQuery('user');
	    $is_CA_user = $query->orConditionGroup()
					->condition('preferred_langcode', 'en-us')
					->condition('preferred_langcode', 'es-us');
		$query->condition($is_CA_user);
	    $query->condition('status', 1);
	    $query->range($nb_lines, $export_size);
	    // $query->sort('field_created_date', 'ASC');
	    $query->sort('uid', 'ASC');

	    $entity_ids = $query->execute();
	    // recipe ID, recipe name, url, tags (spicy,...)

	    if (!empty($entity_ids)){
	        $user_ids = array_values($entity_ids);
	    	$headers = ['user_id', 'email', 'last_login', 'last_contest_entry'
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

		    $counter = $nb_lines;

	        foreach ($user_ids as  $uid) {
	            $user = \Drupal\user\Entity\User::load($uid);
	            $counter++;

	            $_product = $empty_values;
	            $last_login = $user->login->value;
	            if (!empty($last_login)){
	            	$last_login = date('Y-m-d', intval($last_login));
	            } else {
	            	$last_login = 'N/A';
	            }
	            $email = $user->mail->value;
	            // $last_contest_entry = $this->getLastContestEntry($email);
	            $last_contest_entry = 'N/A';
	            if ($with_contest_entry){
	            	$last_contest_entry = $this->getLastContestEntry($email);
	            }
	            
	            if ($counter > 1 && ($counter %10 == 0) )
	            	echo " $counter processed \n";

		    	$_product[array_search('user_id', $headers)] = $uid;
		        $_product[array_search('email', $headers)] = $email;
		        $_product[array_search('last_login', $headers)] = $last_login;
		        $_product[array_search('last_contest_entry', $headers)] = $last_contest_entry;



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

	public function getLastContestEntry($email){
		$sql = "SELECT enterdate from pepsicontest_reg_contest WHERE email = '$email'
		 order by enterdate DESC
		  limit 1 ";
		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();

		if (empty($results))
			return 'N/A';

		if (empty($results[0]->enterdate))
			return 'N/A';

		return $results[0]->enterdate;		


	}

	public function syncSfmcUsers($limit=1){

		$timestamp_sfmc_deployment = 1643924830;  //  Feb 3rd 2022 (when we sfmc syncing restarted)
		$timestamp_oktaUS_deployment = 1643169605;  // (Jan 25th at night)


		// $timestamp_oktaUS_deployment = 1623943804;


		$sql = "SELECT t1.uid AS uid, mail as email
  				FROM users_field_data as t1  WHERE 1
  				and status > 0
  				and uid > 0
  				and langcode IN ('en-us', 'es-us')
  				AND ( created < $timestamp_sfmc_deployment AND changed < $timestamp_sfmc_deployment  )  
  				AND ( created > $timestamp_oktaUS_deployment OR changed > $timestamp_oktaUS_deployment  )

  				AND NOT EXISTS 
    			(select 1 from sf_synced_users as t3 where t3.uid = t1.uid and synced > 1)


    			order by uid desc


   				LIMIT $limit ";


        $select = \Drupal::database()->query($sql);
        $nosyc_users = $select->fetchAll();

        
        $epsilonprocess = 0;
        $epsilonsuccess   = 0;
        $sfmc_process = 0;
        $sfmc_success   = 0;
        $sfmc_failed   = 0;

        $counter = 0;
        
        foreach ($nosyc_users as $nosyc_user) {
        	$counter++;

        	if ($counter > 1 && ( $counter % 10) == 0 ){
        		echo " $counter processed.... \n";
        	}
            $account =  \Drupal\user\Entity\User::load($nosyc_user->uid);

            $source = $account->get('field_source')->value;
            $source = empty($source)? 'tastyrewards': $source;
            $result = sfmcservices_subscribe($account, $source);
            $sfmc_failed++;
            if ($result){
                $sfmc_success++;
                $sfmc_failed--;
            }    

        }

        echo " Inserting records into sf_synced_users ... \n ";

        foreach ($nosyc_users as $nosyc_user) {
            $uid =  $nosyc_user->uid;
            $email =  $nosyc_user->email;


        	$sql = " INSERT IGNORE into sf_synced_users (uid, email, synced) VALUES ($uid, '$email', 2)
                
            ";
            $insert = \Drupal::database()->query($sql);
            $query_result = $insert->execute();

        }

        if ($sfmc_success > 0){
            $channel = "sfmc";
            $message = "Sync processing: Salesforce  Processed  $sfmc_success / $sfmc_process";

            $context = [
                          '%username' => $account->get('name')->value
            ];
            \Drupal::logger($channel)->info($message, $context);
        }

	}

	public function isActiveUser($uid){
		$sql = "SELECT uid, status from users_field_data WHERE uid = $uid and status > 0";
		$query = \Drupal::database()->query($sql);
		$result = $query->fetchAll();
		if (!empty($result)){
			return true;
		}
		return false;
	}


	public function exportPolls($country = 'ca', $limit = 3000){
		// columns we want 
		// Uid, email, language,  question (pollname), options, answer, tags (for each option)
		// add new column poll_type (Poll Type: Brand Specific / Occasion Specific / Demographic Specific / Flavour Related / Recipe Related / Other) 

		// get all existing polls : question (id), options (chids),
		// $polls = $this->getAllPolls();
		// then query the poll_vote table  

		echo "exporting polls..\n";
		/*
			Poll__choice (links pid with chid) 
				Entity_id (pid) & choid_target_id (chid) 
			poll_field_data Has the questions
				id, question, langcode

		*/ 

		$filename = "../export_contest/polls_" . date('Y-m-d') .  ".csv";

		$poll_results = new PollResult();
		$poll_results->init();

		$sql = "SELECT t1.chid, t1.pid, t1.timestamp, t1.uid FROM `poll_vote` as t1
				WHERE NOT EXISTS (SELECT 1 from poll_vote_flag as t2 
						where t1.uid = t2.uid AND t1.pid = t2.pid AND synced > 0 )
				order by pid ASC
				limit $limit ";

		// echo "$sql \n";

		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();

		if (!empty($results)){

			// $user_ids = array_values($entity_ids);
	    	$headers = ['user_id', 'email', 'datetime', 'language', 'poll_id', 'question', 'options', 'answer', 'answer_id', 'tag', 'poll_type'
	    	];

	        $empty_values = [];
	        for ($i = 0; $i < count($headers); $i++){
	            $empty_values[$i] = '';
	        }

		    $content = '';
		    $delimiter = '","';
		    $nb_lines = $this->count_lines($filename);
		    echo " current csv contains $nb_lines \n";
		    $csv_contents = [];
		    if ($nb_lines == 0 || $nb_lines == 1){
		    	$content .= '"'.  implode($delimiter,$headers) . '"' ."\n";
		    }

		    $counter = $nb_lines;



			foreach ($results as $key => $res) {
				$counter++;
				if ($counter %100 == 0){
					echo "processing $counter... \n";
				}
				// export csv here
				$entity = $empty_values;
				$user = \Drupal\user\Entity\User::load($res->uid);
				$langcode = $user->preferred_langcode->value;
				// $headers = ['user_id', 'email', 'language', 'question', 'options', 'answer', 'tag', 'poll_type'
				$entity[array_search('user_id', $headers)] = $res->uid;
		        $entity[array_search('email', $headers)] = $user->mail->value;
		        $entity[array_search('language', $headers)] = $langcode;
				$entity[array_search('datetime', $headers)] = date("Y-m-d H:i:s", $res->timestamp);
				$entity[array_search('poll_id', $headers)] = $res->pid;
		        $entity[array_search('question', $headers)] = $poll_results->getQuestion($res->pid, $langcode);
		        $entity[array_search('options', $headers)] = $poll_results->getOptions($res->pid, $langcode);
		        $entity[array_search('answer', $headers)] = $poll_results->getAnswer($res->pid, $langcode, $res->chid);
				$entity[array_search('answer_id', $headers)] = $res->chid;



		        $row = '"' .  implode($delimiter,$entity). '"' . "\n";
		        $row = remove_special_characters($row);
		        $content .= $row;
		        // $csv_contents[$nid] .= $row;
			}

			if (!file_put_contents($filename, $content, FILE_APPEND | LOCK_EX)){
	            log_var(" $csv_filename could not be created", "CSV polls export");
	        } else {
				// @TODO: add sync flag here.
				// delete poll_vote_flag table first
				foreach ($results as $key => $res) {
					$fields = array('pid', 'uid', 'hostname', 'synced');
					$query = \Drupal::database()->insert('poll_vote_flag')
					->fields($fields);
					$data = array($res->pid, $res->uid, "", 3);
					try{
						$query->values(array_combine($fields, $data));
						$query->execute();
					} catch(\Exception $e){
						log_var(" flag could not be set", "CSV polls export");
					}

				}
			}
		}
		else {
	    	echo "No polls to export \n";
	    }



		
		return [];
	}

	public function getAllPolls(){
		echo "exporting polls..\n";
		return [];
	}

	public function count_lines($filename){
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

	public function setCountBySourceID($enterdate = '', $country = 'canada'){
		echo " processing sourceIDS for $enterdate \n";

	    $query = \Drupal::entityQuery('user');

	    if ($country == 'usa'){
		    $with_language = $query->orConditionGroup()
						->condition('preferred_langcode', 'en-us')
						->condition('preferred_langcode', 'es-us');
	    } else {
	    	$with_language = $query->orConditionGroup()
						->condition('preferred_langcode', 'en')
						->condition('preferred_langcode', 'fr');
	    }
		
		$query->condition($with_language);

		$timestamp_start = strtotime($enterdate . " 00:00:00");
		$timestamp_end = strtotime($enterdate . " 23:59:59");

		$query->condition('created' , $timestamp_start, '>');
		$query->condition('created' , $timestamp_end, '<');
		

	    $query->condition('status', 1);
	    // $query->range($nb_lines, $export_size);
	    $query->sort('field_created_date', 'DESC');

	    $entity_ids = $query->execute();
	    // recipe ID, recipe name, url, tags (spicy,...)

	    if (!empty($entity_ids)){
	        $user_ids = array_values($entity_ids);
	    	$headers = ['user_id', 'email', 'firstname', 'lastname', 'city', 'province', 'postalcode',
	    		'gender', 'bday', 'langcode', 'created', 'updated', 'last_login', 'source_id'
	    	];

	       
		    $nb_users = count($user_ids);
	    	echo " Exporting = $nb_users \n";

	    	$counter = 0;
	    	$columns = ['source_id', 'date', 'total', 'sub_ptr', 'sub_snacks', 'country'];
	    	$values = [];
			$sources = [];

	        foreach ($user_ids as  $uid) {
	            $user = \Drupal\user\Entity\User::load($uid);

	            $counter++;
				$source_id = $user->field_source_id->value;
				$created_date = $user->get('created')->value;
				$sub_ptr = $user->field_optin->value;
				$sub_snacks = $user->field_optin3->value;
				if (!isset($sources[$source_id])){
					$sources[$source_id] = [
						'total' => 0,
						'date' => $enterdate,
						'country' => $country,
						'sub_ptr' => 0,
						'sub_snacks' => 0,
					];
				}

				$sources[$source_id]['total'] += 1;
				if ($sub_ptr == 1){
					$sources[$source_id]['sub_ptr'] += 1;
				}
				if ($sub_snacks == 1){
					$sources[$source_id]['sub_snacks'] += 1;
				}



	            if ($counter > 1 & ($counter%1000) == 0 ){
	            	echo " $counter users processed \n";
	            }

	            //insert into the pepsi_sourceid_report
	            // fields: source_id, date, total, sub_ptr, sub_snacks, country



	            
	        }
			echo "inserting sources into DB \n";
			foreach($sources as $source_id => $data){
				$query = \Drupal::database()->insert('pepsi_sourceid_report')
						->fields($columns);
				$values = [
					$source_id, 
					$data['date'],
					$data['total'],
					$data['sub_ptr'],
					$data['sub_snacks'],
					$data['country'],
				];

				try{
					$query->values(array_combine($columns, $values));
					$query->execute();
				} catch(\Exception $e){
					log_var("", "could not insert row into pepsi_sourceid_report", "source_id");
				}
				
			}

	    } else {
	    	echo "No users to export \n";
	    }
	}


}
