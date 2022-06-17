<?php

/**
 * @file
 */

namespace Drupal\pepsibam;

class ReportGenerator{
	private $frequency;
	private $period;
	private $start_date;
	private $end_date;

	public static function instance(){
		return new ReportGenerator();
	}

	public function set_period($p){
		$this->period = $p;
	}

	public function set_date_ranges($start, $end){
		$this->start_date = date_create($start);
		$this->end_date = date_create($end);
	}

	public function count_leads($language, $new_members = true, $optin = false){
		// SET @start_date =  'Sept 7 2020 00:01AM';
		$start_date = date_format($this->start_date, "M j Y") . " 01:01AM";
		$end_date = date_format($this->end_date, "M j Y") . " 11:59PM";
		$only_new_leads = $new_members? "AND `created` > UNIX_TIMESTAMP(STR_TO_DATE('$start_date', '%M %d %Y %h:%i%p'))" : "";
		$is_USA = ($language == 'en-us' || $language == 'es-us') ? "(t1.langcode = 'en-us' OR t1.langcode = 'es-us' ) " : "( t1.langcode <> 'en-us' AND t1.langcode <> 'es-us')";
		$JOIN_optin_table = $optin? "INNER JOIN user__field_optin AS t2 ON t1.uid = t2.entity_id " : "";
		$AND_has_optin = $optin? "AND t2.field_optin_value = 1" : "";
		$AND_active_member = $new_members ? " AND t1.status = 1 " : "";
		$AND_not_blacklisted = " and NOT EXISTS (SELECT 1 from user__field_black_listed as t4 WHERE t4.entity_id = t1.uid and t4.field_black_listed_value = 1 )";
		// 

			 
		$sql =	" SELECT  COUNT(t1.uid) AS cnt  FROM `users_field_data` AS t1 
			  	$JOIN_optin_table  
			 	WHERE  `created` < UNIX_TIMESTAMP(STR_TO_DATE('$end_date', '%M %d %Y %h:%i%p')) 
					$only_new_leads  
				   AND (mail NOT LIKE '%commuccast.com' AND mail NOT LIKE '%distromtl.com' AND mail NOT LIKE '%ispqc.com' AND mail NOT LIKE '%xferdirect.com')    
				    AND $is_USA   
				    $AND_active_member
				    $AND_has_optin
				    $AND_not_blacklisted
			 
			";


		$query = \Drupal::database()->query($sql);
	    try{
	        $results = $query->fetchAll();
	        if (!empty($results)){
			// @TODO: Include snackperks members for USA : if $lang = en-us and new_members = 0
				if (($language == 'en-us' || $language == 'es-us')  && !$new_members && !$optin){
					echo "total_members includes Snackperk users =   ";
					// debug_var($sql, 1);
					$snackperk_users = $this->count_snackperks();
					echo " $snackperk_users \n";
					return $snackperk_users + $results[0]->cnt;
				} else {
	        		return $results[0]->cnt;
				}
	        }
	    } catch(\Exception $e){
	        \Drupal::logger('report-generator')->info("Could not run Count leads Queries", []);
	    }

        return 0;
	}


	private function count_snackperks(){

		$sql =	" SELECT value  FROM pepsi_temp_results as t1 
		WHERE t1.name = 'snackperks_user_count' 
		and event_date = (select max(event_date) from pepsi_temp_results WHERE name = 'snackperks_user_count' ) ";

		$query = \Drupal::database()->query($sql);
	    try{
	        $results = $query->fetchAll();
	        if (!empty($results))
	        	return $results[0]->value;
	    } catch(\Exception $e){
	        \Drupal::logger('report-generator')->info("Could not run Snackperk inactive user count", []);
	    }

        return 0;
	}

	public function count_loggedin_users($language){
		// SET @start_date =  'Sept 7 2020 00:01AM';
		$start_date = date_format($this->start_date, "M j Y") . " 01:01AM";
		$end_date = date_format($this->end_date, "M j Y") . " 11:59PM";
		$is_USA = ($language == 'en-us') ? "t1.langcode = 'en-us'" : "t1.langcode <> 'en-us'";
		// 

			 
		$sql =	" SELECT  COUNT(t1.uid) AS cnt  FROM `users_field_data` AS t1 
			  	-- INNER JOIN user__field_optin AS t2 ON t1.uid = t2.entity_id 
			 	WHERE  `login` < UNIX_TIMESTAMP(STR_TO_DATE('$end_date', '%M %d %Y %h:%i%p')) 
				   AND `login` > UNIX_TIMESTAMP(STR_TO_DATE('$start_date', '%M %d %Y %h:%i%p'))
				   AND (mail NOT LIKE '%commuccast.com' AND mail NOT LIKE '%distromtl.com' AND mail NOT LIKE '%ispqc.com' AND mail NOT LIKE '%xferdirect.com')    
				    AND $is_USA  
				    AND t1.status = 1
			 
			";

		$query = \Drupal::database()->query($sql);
	    try{
	        $results = $query->fetchAll();
	        if (!empty($results))
	        	return $results[0]->cnt;
	    } catch(\Exception $e){
	        \Drupal::logger('report-generator')->info("Could not run sync_tostitos_contests request", []);
	    }

        return 0;
	}

	public function count_active_leads($language){
		// SET @start_date =  'Sept 7 2020 00:01AM';
		$start_date = date_format($this->start_date, "M j Y") . " 01:01AM";
		$end_date = date_format($this->end_date, "M j Y") . " 11:59PM";
		$is_USA = ($language == 'en-us' || $language == 'es-us' ) ? "(t1.langcode = 'en-us' OR t1.langcode = 'es-us')" : "(t1.langcode <> 'en-us' AND t1.langcode <> 'es-us' )";
		// 

		$AND_not_blacklisted = " and NOT EXISTS (SELECT 1 from user__field_black_listed as t4 WHERE t4.entity_id = t1.uid and t4.field_black_listed_value = 1 )";
			 
		$sql =	" SELECT  COUNT(t1.uid) AS cnt  FROM `users_field_data` AS t1 
			  	-- INNER JOIN user__field_optin AS t2 ON t1.uid = t2.entity_id 
			 	WHERE  `created` < UNIX_TIMESTAMP(STR_TO_DATE('$end_date', '%M %d %Y %h:%i%p')) 
				   -- AND `created` > UNIX_TIMESTAMP(STR_TO_DATE(@start_date, '%M %d %Y %h:%i%p'))
				   AND (mail NOT LIKE '%commuccast.com' AND mail NOT LIKE '%distromtl.com' AND mail NOT LIKE '%ispqc.com' AND mail NOT LIKE '%xferdirect.com')    
				    AND $is_USA  
				    AND t1.status = 1
				    $AND_not_blacklisted 
			 
			";

		$query = \Drupal::database()->query($sql);
	    try{
	        $results = $query->fetchAll();
	        if (!empty($results))
	        	return $results[0]->cnt;
	    } catch(\Exception $e){
	        \Drupal::logger('report-generator')->info("Could not run sync_tostitos_contests request", []);
	    }

        return 0;

	}

	public function count_leads_popup($langcode, $popup_code){
		$start_date = date_format($this->start_date, "Y-m-d H:i:s");
		$end_date = date_format($this->end_date, "Y-m-d H:i:s");
		//$lang_condition = $langcode == 'en-us'? "=" : "<>";
		$is_USA = ($langcode == 'en-us' || $langcode == 'es-us' ) ? "(t1.langcode = 'en-us' OR t1.langcode = 'es-us')" : "(t1.langcode <> 'en-us' AND t1.langcode <> 'es-us' )";
		// popupTastyrewardsMembers , popuptastyrewards
		$sql =	" SELECT  COUNT(t1.uid) AS cnt  FROM `pepsi_tracking` AS t1 
			 	WHERE  `event_date` < '$end_date'
				    AND `event_date` > '$start_date'
				    AND tracking_code LIKE '$popup_code'
				    AND $is_USA
 
			 
			";

		// debug_var($this->start_date);
		// debug_var($sql);

		$query = \Drupal::database()->query($sql);
		try{
	        $results = $query->fetchAll();
	        if (!empty($results))
	        	return $results[0]->cnt;
	    } catch(\Exception $e){
	        \Drupal::logger('report-generator')->info("Could not run sync_tostitos_contests request", []);
	    }
	    return 0;
	}

	public function insert_report($langcode){

		$new_members = $this->count_leads($langcode, $new_members = true);
		$total_members = $this->count_leads($langcode, $new_members = 0);
		$total_active_members = $this->count_active_leads($langcode);
		$date_range = date_format($this->start_date, "Y-m-d") . " to " . date_format($this->end_date, "Y-m-d");
		$popup1_total = $this->count_leads_popup($langcode, 'popuptastyrewards');
		$popup2_total = $this->count_leads_popup($langcode, 'popupTastyrewardsMembers');

		$country = ($langcode == 'en-us' || $langcode == 'es-us')? 'USA' : 'CA';

		try{
	        $sql = " INSERT into pepsi_reporting (period, new_members, total_members, total_active, date_range, popup1_total, popup2_total, lang) VALUES ('P2', $new_members, $total_members , $total_active_members '$date_range', $popup1_total, $popup2_total, '$country')
	        ";
	        $insert = \Drupal::database()->query($sql);
	        $query_result = $insert->execute();
	    } catch(\Exception $e){
	        \Drupal::logger("reporting")->info("Could not insert into pepsi_reporting table " . $e->getMessage(), []);
	    }
	}

	public function generate_report($langcode){
		$new_members = $this->count_leads($langcode, $new = true);
		$total_members = $this->count_leads($langcode, $new = 0);
		$new_subscribers = $this->count_leads($langcode, $new = true, $optin = 1);
		$total_active_members = $this->count_active_leads($langcode);
		$date_range = date_format($this->start_date, "Y-m-d") . " to " . date_format($this->end_date, "Y-m-d");
		$popup1_total = $this->count_leads_popup($langcode, 'popuptastyrewards');
		$popup2_total = $this->count_leads_popup($langcode, 'popupTastyrewardsMembers');
		$total_subscribers = $this->count_leads($langcode, $new = 0, $optin = 1);
		$logged_in_users = $this->count_loggedin_users($langcode);

		$country = ($langcode == 'en-us' || $langcode == 'es-us')? 'USA' : 'CA';
		$report = [
			'new_members' => $new_members,
			'new_subscribers' => $new_subscribers,
			'total_members' => $total_members,
			'total_subscribers' => $total_subscribers,
			'total_active_members' => $total_active_members,
			'logged_in_users' => $logged_in_users,
			'popup1_total' => $popup1_total,
			'popup2_total' => $popup2_total,
		];

		return $report;
	}

	public function getSourceidByDate($start_date, $end_date, $country){

		$with_langcode = "AND t1.langcode NOT IN ('en-us', 'es-us') AND t2.field_source_id_value NOT LIKE 'US_%'";
		if ($country == 'usa'){
			$with_langcode = "AND t1.langcode IN ('en-us', 'es-us') AND t2.field_source_id_value NOT LIKE 'CA_%' ";
		}

		$sql = "
		SELECT  t2.field_source_id_value AS source, COUNT(*) AS total FROM `users_field_data` AS t1 
		INNER JOIN user__field_source_id AS t2 ON t1.uid = t2.entity_id
		WHERE 1
		-- AND t1.langcode  IN ('en-us', 'es-us')
		$with_langcode
		AND t1.uid > 0
		AND created >  UNIX_TIMESTAMP('$start_date')
		AND created < UNIX_TIMESTAMP('$end_date')
		AND STATUS > 0
		GROUP BY t2.field_source_id_value
		";

		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		$sourceIDs = [];

		$sub_numbers = $this->getSubCount($start_date, $end_date, $country);
		if (!empty($results)){
			foreach($results as $key => $res){
				$source_id = $res->source;
				$sub_ptr = isset($sub_numbers[$source_id]['sub_ptr'])? $sub_numbers[$source_id]['sub_ptr']: '';
				$sub_snacks = isset($sub_numbers[$source_id]['sub_snacks'])? $sub_numbers[$source_id]['sub_snacks']: '';
				$sourceIDs[] = [
					'source' => $source_id,
					'total' => $res->total,
					'sub_ptr' => $sub_ptr,
					'sub_snacks' => $sub_snacks,
					// 'sub_snacks' => $sub_numbers[$source_id]['sub_snacks'],
				];
			}
		}

		return $sourceIDs;

	}

	function getSubCount($start_date, $end_date, $country){
		$sql = "
			SELECT source_id, SUM(sub_ptr) as n_ptr, SUM(sub_snacks) as n_snacks  from pepsi_sourceid_report 
			WHERE 1 
			AND date >= '$start_date'
			AND date <= '$end_date'
			AND country = '$country'
			group by source_id
		";

		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		$ret = [];
		if ($results){
			foreach ($results as $key => $res) {
				$ret[$res->source_id] = [
					'sub_ptr' => $res->n_ptr,
					'sub_snacks' => $res->n_snacks,
				];
			}
		}
		return $ret;

	}

}