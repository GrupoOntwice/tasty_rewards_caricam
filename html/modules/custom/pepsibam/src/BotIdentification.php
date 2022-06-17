<?php

/**
 * @file
 */

namespace Drupal\pepsibam;

class BotIdentification{

	private $time_first_found_domain;
	private $time_last_found_domain;
	private $contest_id;
	private $from_drush = false;
	public $save_results = false;
	private $enterdate = '';

	public static function instance(){
		return new BotIdentification();
	}

	public function setDrushFlag($bool){
		$this->from_drush = $bool;
	}

	public function setContestDate($enterdate){
		$this->enterdate = $enterdate;
	}



	public function sendReport(){
		// $to = ["rotsy@bamstrategy.com", "miguel@bamstrategy.com", "sara.trivisonno@bamstrategy.com"];
		$to = ["rotsy@bamstrategy.com", "sara.trivisonno@bamstrategy.com"];
		$contests = $this->getActiveContests();
		$domain_names = '';
		foreach ($contests as $contest) {
			$contest_name = $contest['contest_name'];
			$domain_names .= "<div> DOMAINS FOR $contest_name</div>";
			$domain_names .= $this->fetchDomainNames($contest['contest_id']);
			
		}
		$yesterday = date('Y-m-d',strtotime("-1 days"));
		pepsicontest_send_email($to, " Contest Domain names report - ($yesterday)", $domain_names);
		// debug_var($to, 3);

	}

	public function getActiveContests(){
		$yesterday = date('Y-m-d',strtotime("-1 days"));
		// $yesterday = '2021-12-02';
		$sql = "SELECT distinct(contest_id) from pepsicontest_reg_contest where 1
					AND enterdate = '$yesterday'					
		";
		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		$contests = [];
		foreach ($results as $key => $res) {
			$contest_node = \Drupal\node\Entity\Node::load($res->contest_id);
			$contests[] = [
				'contest_id' => $res->contest_id,
				'contest_name' => $contest_node->getTitle(),
			];
		}

		return $contests;
	}

	public function fetchDomainNames($contest_id = 0){
		$yesterday = date('Y-m-d',strtotime("-1 days"));
		// $yesterday = '2021-12-02';


		$sql = "SELECT substring_index(email, '@', -1) domain, COUNT(*) email_count
					FROM pepsicontest_reg_contest
					WHERE 1
					 AND enterdate = '$yesterday'
					 AND contest_id = $contest_id
					GROUP BY substring_index(email, '@', -1)

					ORDER BY email_count DESC, domain; ";
		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		$top_10 = array_slice($results, 0, 10);
		$domain_list = "<div>";
		foreach ($top_10 as $res) {
			// code...
			$domain_list .= "<p> " . $res->domain . ":  " . $res->email_count . "</p>";
		}
		$domain_list .= "</div>";
		// debug_var($domain_list, 2);
		return $domain_list;
	}

	public function hasLoggedInAfterContest($email, $regdate){
		$user = user_load_by_mail($email);
		$last_login = $user->getLastLoginTime();
		if ($last_login == 0)
			return false;

		return true;
	}

	public function flagUsersAsSpam($emails){
		if (empty($emails))
			return ;


		try{

			if (!empty($this->enterdate)){
				\Drupal::database()->update('pepsicontest_reg_contest')
		            ->fields([
		                'spam' => 5,
		            ])
		            // ->condition('contest_id',$this->contest_id)
		            ->condition('enterdate',$this->enterdate)
		            ->condition('email',$emails, 'IN')
		            ->execute();

			} else {
				\Drupal::database()->update('pepsicontest_reg_contest')
		            ->fields([
		                'spam' => 5,
		            ])
		            // ->condition('contest_id',$this->contest_id)
		            ->condition('email',$emails, 'IN')
		            // ->condition('regdate',$regdate)
		            ->execute();

			}


	            return 1;

		} catch(\Exception $e){
			echo " Could not update spam column\n";
			log_var($e, "could not update spam column", "spam_contest");
	    }
	}

	public function markAsBots($entries){
		$start_date = $this->time_first_found_domain;
		$end_date = $this->time_last_found_domain;

		$emails = [];
		$count = 0;
		foreach ($entries as $email => $val) {
			$count++;
			// echo "updating $email ... \n";
			$regdate = $val['regdate'];
			$this->contest_id = $val['contest_id'];
			$has_logged_in = $this->hasLoggedInAfterContest($email, $regdate);
			if (!$has_logged_in){
				$emails[] = $email;
			}
			if ($count % 50 == 0){
				echo "\n updating emails $count";
				$this->flagUsersAsSpam($emails);
				$emails = [];
				// $count = 0;
			}
		}

		$this->flagUsersAsSpam($emails);

	}

	public function flagBotGmailEntries($enterdate = ''){
		$entries = $this->fetchPotentialBotEntries($enterdate);


		$curr_domain = "";
		$prev_domain = "";
		$domain_count = 0;
		$min_count = 15;
		$time_first_occurence = "";
		$last_regdate = "";
		$queue_emails = [];
		$max_entries_per_minutes = 3;  // if > 3 per minutes

		foreach ($entries as $key => $obj) {
			$email = $obj->email;
			$curr_domain = $this->getDomain($email);
			if ($curr_domain == $prev_domain){
				// make sure there is a 
				$domain_count++;
				$queue_emails[$email] = [
					'regdate' => $obj->regdate,
					'contest_id' => $obj->contest_id,
				];
			} else {
				// This is when we encounter a different email domain
				// and the count is > than threshold
				if ($domain_count > $min_count){
					echo " $prev_domain has $domain_count   (---> $curr_domain) from " . $this->time_first_found_domain ." to $last_regdate  \n";
					$this->time_last_found_domain = $last_regdate;

					$timestamp_start = strtotime(date($this->time_first_found_domain ));
					$timestamp_end = strtotime(date($this->time_last_found_domain ));
        			$diff = abs($timestamp_start - $timestamp_end); // in seconds

        			if ( ($domain_count*60)/$diff > $max_entries_per_minutes ){
        				if (!$this->from_drush || $this->save_results)
							$this->markAsBots($queue_emails);
        			}

				}
				$domain_count = 0;
				$queue_emails = [];
			}

			if ($domain_count == 1){
				$this->time_first_found_domain = $obj->regdate;
			}

			$last_regdate = $obj->regdate;
			$prev_domain = $curr_domain;
		}
	}

	public function getDomain($email_address){
		return substr($email_address, strpos($email_address, '@'));
	}

	public function fetchPotentialBotEntries($enterdate ,$limit = 10000){
		$bot_useragent = "Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36";
		$bot_useragent = str_replace(";", "\\;", $bot_useragent);

		$has_enterdate = '';
		if (!empty($enterdate))
			$has_enterdate = " AND enterdate = '$enterdate' ";

		$sql = "SELECT * from pepsicontest_reg_contest WHERE 1 
				 AND user_agent LIKE '%$bot_useragent' 
				  AND spam = 0
				  AND nomember = 1
				  $has_enterdate
				  and language in ('en-us', 'es-us')
				  -- AND enterdate < '2021-12-25'
				 order by regdate desc
				 -- limit $limit
				";

		// debug_var($sql);

		$query = \Drupal::database()->query($sql);
		$result = $query->fetchAll();


		return $result;

	}

}