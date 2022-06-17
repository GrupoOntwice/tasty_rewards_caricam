<?php

/**
 * @file
 */
use Drupal\pepsibam\CronScheduledTasks;

namespace Drupal\pepsicontest;

class ContestWinnerPicker{

    private $nb_instant_winners;
    private $nb_daily_winners = 2;
    private $nb_winners_per_seeding;
    private $max_daily_win_per_user = 1;
    private $max_total_win_per_user;
    private $seeding_frequency = 7*1; // Every 14 days
    private $contest_duration = 7*8; // Number of days (8 weeks)
    private $contest_node;
    

    public function instance(){
        return new ContestWinnerPicker();
    }

    /**
     * [draw_instant_winner description]
     * @param  [type] $contest_node [description]
     * @param  [type] $email        [description]
     * @return array with 'is_winner' and 'prize_nid' 
     */
    public function draw_instant_winner($contest_node, $email){
        // return random winner for now
        if (!$this->is_active($contest_node))
            return [];

        $this->max_total_win_per_user = $contest_node->field_total_wins_per_account->value;
        
        return $this->draw_microsite_contest_winner($contest_node, $email);
        // WE draw a winner regardless of contest type
        /*
        if ($contest_node->getType() == 'microsite_contest'){
            return $this->draw_microsite_contest_winner($contest_node, $email);
        } else {

            $value = rand(0,1) == 1;
            // if ($value){
            //     $this->db_save_winner($contest_node, $email, $prize_node)
            // }
            return $value;
        }
        */

        
    }

    public function draw_microsite_contest_winner($contest_node, $email){

        $return = [
            'is_winner' => false,
            'prize_nid' => 0,
        ];
        if (!$this->is_eligible_winner($contest_node, $email) ){
            return $return;
        }

        $current_time = date("Y-m-d H:i:s");
        $current_date = date("Y-m-d");
        $nid = $contest_node->id();
        $config = \Drupal::service('config.factory')->getEditable("microsite_contest_$nid.settings");
        $date_last_seeding = $config->get('time_last_seeding');
        $env =  \Drupal\Core\Site\Settings::get("environment");

        if ($this->getDayOfTheWeek() == 'Monday' || $env == 'staging'){
            if ($env == 'staging'){
                // \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("reset_weekly_entries", $hours = 24 * 6.5, [], $log = true);
                // \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("reset_nintendo_contest_entries", $hours = 24);
            }
        }
        $this->run_contest_periodic_jobs();

        $this->contest_node = $contest_node;
        $nb_days = $this->seeding_frequency;
        $date_next_seeding = date( "Y-m-d", strtotime( "$date_last_seeding  +$nb_days day" ) );
        if ($current_date > $date_next_seeding || empty($date_last_seeding)){
            $this->do_seeding($contest_node);
        }



        $return['prize_nid'] = $config->get('next_prize');

        $variables = $this->getContestDrawSettings($contest_node);
        //log_var($variables, 'contest variable');
        $time_next_draw = $variables['time_next_draw'];
        $nb_selected_winners = $variables['nb_winners_current_seeding']; 
        $nb_prizes_available = $variables['nb_prize_current_seeding']; 

        // $nb_selected_winners = 0; // Get this value from the variables
        if ($current_time > $time_next_draw && $nb_selected_winners < $nb_prizes_available){
            $prize_node = \Drupal\node\Entity\Node::load($return['prize_nid']);
            $saved = $this->db_save_winner($contest_node, $email, $prize_node);
            if (!$saved)
                return $return;
            
            $return['is_winner'] = true;
            // Current user should be selected
            $draw_schedule = $config->get('draw_schedule');
            $prize_schedule = $config->get('prize_schedule');
            $config->set('nb_winners_current_seeding', $nb_selected_winners + 1);
            $config->set('time_next_draw', $draw_schedule[$nb_selected_winners + 1]);
            $config->set('next_prize', $prize_schedule[$nb_selected_winners + 1])
            ->save();

            // update the winners table here. 
            // $result = $this->add_contest_winner($contest_node, $email);
            // if ($result){
            // }
                   
        }
        return $return;
    }

    public function run_contest_periodic_jobs(){
        // \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("export_winners_csv", $hours = 24*7);
        // \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("flag_fraudulent_entries", $hours = 24);
    }

    public function add_contest_winner($contest_node, $email){
        // $connection = \Drupal::database();
        $connection = \Drupal\Core\Database\Database::getConnection();
        // $transaction = $connection->startTransaction();
        $success = false;
        try{
            // primary keys for the table pepsi_microsite_winners are (contest_id, prize, date)
            // "prize" have to be in one language otherwise we get two different primary keys for the same prize
            $current_prize = getNextPrizeTitle($contest_node, $skip = true, $always_english = 1);
            $date_format = "Y-m-d";
            $current_date = date("Y-m-d");            

            $connection->insert('pepsi_microsite_winners')
            ->fields([
              'contest_id' => $contest_node->id(),
              'email' => $email,
              'prize' => $current_prize,
              // 'date' => date("Y-m-d"),
              'date' => date($date_format),
            ])
            ->execute();
            $success = true;

        } catch(\Exception $e){
            // $transaction->rollBack();
            // print_r($e->getMessage());
            \Drupal::logger("general")->info(" Contest Winner could not be added to pepsi_microsite_winners table", []);
        }

        if ($success){
            // $transaction->commit();
            return true;
        }
        return false;

    }

    public function db_save_winner($contest_node, $email, $prize_node){
        $langcode = get_current_langcode($prefix = false);
        $connection = \Drupal::database();
        $row = [
            'contest_id' => $contest_node->id(),
            'prize_id' => $prize_node->id(),
            'email' => $email,
            'language' => $langcode,
            // 'prize_name' => $prize_node->getTranslation($langcode)->field_subtitle->value,
            'prize_name' => $prize_node->field_subtitle->value,
            'enterdate' => date("Y-m-d"),
            'regdate' => date("Y-m-d H:i:s"),
        ];

        $query = $connection->insert('pepsicontest_winners')->fields([
            'contest_id', 'prize_id', 'email', 'language', 'prize_name', 'enterdate', 'regdate'
        ]);

        $query->values($row);
        try{
            $query->execute();
            $prize_node->field_quantity_awarded->value += 1;
            $prize_node->save();
            return 1;
        }catch(\Exception $e){
            log_var($row, "Contest winner cannot be saved");
            return false;
        }

    }

    public function db_save_claimed_prize($contest_node, $email, $prize_node){

    }

    public function get_current_prize($contest_node){
        $langcode = get_current_langcode($prefix = false);
        // For now just return the first prize
        $prizes = $contest_node->field_contest_prizes->referencedEntities();
        if (!empty($prizes))
            return $prizes[0];

        // @TODO: build a logic that picks a random prize
        // among the ones that are available. 
        return null;
    }

    private function is_eligible_winner($contest_node, $email){
        if (strpos($email, 'ispqc.com') !== false 
            || strpos($email, 'distromtl') !== false 
            || strpos($email, 'xferdirect') !== false 
            )
        {
            return false;
        }
        // Check if the user has exceeded daily quota
        $wins_today = $this->count_daily_wins($contest_node, $email);
        if ($wins_today >= $this->max_daily_win_per_user)
            return false;

        // check if total win quota is exceeded
        $total_wins = $this->count_total_wins($contest_node, $email);
        if ($total_wins >= $this->max_total_win_per_user)
            return false;

        return true;
    }

    private function count_daily_wins($contest_node, $email){
        $today = date('Y-m-d');
        $query = \Drupal::database()->select('pepsicontest_winners', 'cw');
                $query->addField('cw', 'contest_id');
                $query->addField('cw', 'enterdate');
                $query->addField('cw', 'email');
                $query->condition('cw.contest_id', $contest_node->id());
                $query->condition('cw.email', $email);
                $query->condition('cw.enterdate', $today);
        
        $num_rows = $query->countQuery()->execute()->fetchField();
        return empty($num_rows) ? 0 : $num_rows;

    }

    private function count_total_wins($contest_node, $email){
        $query = \Drupal::database()->select('pepsicontest_winners', 'cw');
                $query->addField('cw', 'contest_id');
                $query->addField('cw', 'enterdate');
                $query->addField('cw', 'email');
                $query->condition('cw.contest_id', $contest_node->id());
                $query->condition('cw.email', $email);
        
        $num_rows = $query->countQuery()->execute()->fetchField();
        return empty($num_rows) ? 0 : $num_rows;

    }

    public function is_active($contest_node){
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        try{
            $openingdate = $contest_node->getTranslation($langcode)->field_opening_date->date; 
            $closingdate = $contest_node->getTranslation($langcode)->field_closing_date->date; 
        } catch(\Exception $e){
            \Drupal::logger('general')->info("Error: Contest Date has not been set ", []);
            return false;
        }

        $current_date = date("Y-m-d H:i:s");
        if ( $current_date >= $openingdate->format("Y-m-d H:i:s") &&
             $current_date <= $closingdate->format("Y-m-d H:i:s")  ){
            return true;
        }

        return false;

    }

    public function do_seeding($node){
        $current_date = date("Y-m-d");
        $nid = $node->id();
        $config = \Drupal::service('config.factory')->getEditable("microsite_contest_$nid.settings");
        $date_last_seeding = $config->get('time_last_seeding');
        // if ($current_date > $date_last_seeding){
        //  If monday reset weekly contest entries count
        // if ($this->getDayOfTheWeek() == 'Monday')
            // $this->resetWeeklyContestEntries();
        $prizes = $this->getPrizesPerSeeding($node);
        // debug_var($prizes);
        $nb_winners = count($prizes);
        $daily_draw_time = $this->pickRandomTimes($nb_winners, $this->seeding_frequency );
        //debug_var($prizes);
        //debug_var(count($daily_draw_time));
        //debug_var($nb_winners, 1);

        // $daily_draw_time = $this->pickRandomTimeToday($this->nb_daily_winners);

        $config->set('nb_winners_current_seeding', 0);        
        $config->set('time_next_draw', $daily_draw_time[0]);        
        $config->set('nb_prize_current_seeding', $nb_winners);  
        // node_id of the next prize      
        $config->set('next_prize', $prizes[0]);        
        $config->set('draw_schedule', $daily_draw_time);
        $config->set('prize_schedule', $prizes);
        $config->set('time_last_seeding', $current_date)
        ->save();


        \Drupal::logger('general')->info("Winner picker variables reset at " . date("Y-m-d H:i:s"), []);
        // }


    }

    public function getDayOfTheWeek(){
        $date_today = date('Y-m-d');
        $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');
        $dayofweek = date('w', strtotime($date_today));
        return $days[$dayofweek];
    }

    public function resetWeeklyContestEntries(){
        // $sql = "UPDATE pepsi_microsite_weekly_entries SET nb_entries = 0  WHERE 1 ";
        $sql = "TRUNCATE  pepsi_microsite_weekly_entries";
        try{
            $select = \Drupal::database()->query($sql);
            $result = $select->execute();
        } catch (\Exception $e) { 
            $channel = "general";
            $message = " microsite contest entry could not be reset  ";
            $context = [ ];
            \Drupal::logger($channel)->info($message, $context);
        }
    }

    public function getContestDrawSettings($node){
        $variables = array();
        $nid = $node->id();
        $config = \Drupal::service('config.factory')->getEditable("microsite_contest_$nid.settings");        

        $variables['nb_winners_current_seeding'] = $config->get('nb_winners_current_seeding'); 
        $variables['nb_prize_current_seeding'] = $config->get('nb_prize_current_seeding'); 
        $variables['time_next_draw'] = $config->get('time_next_draw'); 
        $variables['draw_schedule'] = $config->get('draw_schedule'); 
        $variables['prize_schedule'] = $config->get('prize_schedule'); 

        return $variables;
    }

    public function pickRandomTimeToday($number = 1){
        $today = date("Y-m-d");
        $unix_start = strtotime($today . " 00:00:00");
        $unix_end = strtotime($today . " 23:49:59");
        $diff = $unix_end - $unix_start ;
        $arr_time = [];
        for ($i = 0; $i < $number; $i++){
            $rndtime =  $unix_start + mt_rand(0,$diff);
            $arr_time[] = date("Y-m-d H:i:s",$rndtime);
        }
        sort($arr_time);
        return $arr_time;
    }

    public function pickRandomTimes($number = 1, $nb_days = 1){
        $arr_time = [];
        $datetime = new \DateTime('today');
        $datetime->modify("+$nb_days day");
        $day_end = $datetime->format('Y-m-d');
        $today = date("Y-m-d H:i:s");
        $unix_start = strtotime($today);
        $unix_end = strtotime($day_end . " 23:49:59");
        $diff = $unix_end - $unix_start ;
        for ($i = 0; $i < $number; $i++){
            $rndtime =  $unix_start + mt_rand(0,$diff);
            $arr_time[] = date("Y-m-d H:i:s",$rndtime);
        }
        sort($arr_time);
        return $arr_time;
    }

    public function getPrizesPerSeeding($node){
        // $node = $this->contest_node;
        // $node = \Drupal\node\Entity\Node::load(2774);
        $closingdate = $node->field_closing_date->getValue();
        $closingdate_fmt = date_create($closingdate[0]['value'])->format('Y-m-d');

        $now = time(); // or your date as well
        $end_date = strtotime($closingdate_fmt);
        $datediff =  $end_date - $now;
        $nb_weeks_left = floor($datediff / (60 * 60 * 24* 7));

        $prizes_nodes =  $node->field_contest_prizes->referencedEntities();
        if (empty($prizes_nodes))
            return [];
        $prizes = [];
        foreach ($prizes_nodes as $key => $prize) {
            $qty_per_seeding = 0;
            $qty_per_week = $prize->field_quantity_per_week->value;
            $qty_available = $prize->field_quantity->value - $prize->field_quantity_awarded->value;
        // @TODO: Rethink this to account for prize quantity awarded
        // account for nb seeding left? 
            if ( floor($qty_per_week * $nb_weeks_left) < $qty_available ){
                $qty_per_seeding = $qty_available - floor($qty_per_week * $nb_weeks_left);
            }
            if ($nb_weeks_left == 1){
                // Take all the remaining prizes
                $qty_per_seeding = $qty_available;
            }
            // $qty_per_seeding = ($prize->field_quantity->value * $this->seeding_frequency) / $this->contest_duration;
            
            for ($i = 0; $i < $qty_per_seeding; $i++ ) {
                $prizes[] = $prize->id();
            } 
        }
        shuffle($prizes);
        return $prizes;
    }



    public function find_custom_contest_fraud(){
        $sql = " SELECT t1.contest_id, t1.email, t1.enterdate, t1.prize_name, t1.language

                FROM  pepsicontest_winners AS t1 
                WHERE 
                   -- t1.enterdate > '$ten_days_ago'
                     t1.claimed = 1
                    -- AND t1.suspicious = 0
                ";


        $query = \Drupal::database()->query($sql);

        try{
            $results = $query->fetchAll();
            $exported = $this->batch_set_fraud_flag($results);


        } catch(\Exception $e){
            log_var($sql, "Could not run fraud prevention", 'custom-contest');
            return null;
        }

    }

    public function update_csv_winners_flag(){
        $sql = " SELECT t1.contest_id, t1.email, t1.enterdate, t1.prize_name, t1.language, t1.phone

                FROM  pepsicontest_winners AS t1 
                WHERE 1
                   -- t1.enterdate > '$ten_days_ago'
                    and  t1.claimed = 1
                    AND t1.exported = 1
                    and t1.enterdate > '2021-10-01'
                    and t1.enterdate < '2021-10-12'
                    order by regdate desc
                    -- AND t1.suspicious = 0
                ";


        $query = \Drupal::database()->query($sql);
        $results = $query->fetchAll();
        $update_query = '';
        foreach ($results as $obj) {
            $enterdate = $obj->enterdate;
            $email = $obj->email;
            $update_query .= "UPDATE  pepsicontest_winners set exported = 2 where claimed = 1 AND enterdate = '$enterdate' AND email = '$email';
            ";
            // code...
        }
        debug_var($update_query,2);
    }

    public function export_winners_claim($limit = null ){
        $ten_days_ago = date("Y-m-d", strtotime("-10 day"));
        $with_limit = "";
        if (!empty($limit) && is_numeric($limit))
            $with_limit = "  limit $limit ";
                // FROM  pepsicontest_reg_contest AS t1 
        $sql = " SELECT t1.contest_id, t1.email, t1.enterdate, t1.prize_name, t1.language, t1.phone

                FROM  pepsicontest_winners AS t1 
                WHERE 
                   -- t1.enterdate > '$ten_days_ago'
                     t1.claimed = 1
                    AND t1.exported = 0
                    AND t1.suspicious = 0
                    $with_limit 
                ";


        $query = \Drupal::database()->query($sql);

        try{
            $results = $query->fetchAll();
            $exported = $this->save_csv_file($results);

            if ($exported){
                $this->set_export_flag($results);
            }
        } catch(\Exception $e){
            log_var($sql, "Could not run export claim", 'custom-contest');
            return null;
        }
    }

    private function set_export_flag($sql_result){
        foreach($sql_result as $res) {
            $email = $res->email;
            $contest_id = $res->contest_id;
            $enterdate = $res->enterdate;
            $sql = " UPDATE pepsicontest_winners SET exported = 3 WHERE 
                        email = '$email' AND
                        contest_id = $contest_id AND
                        enterdate = '$enterdate'
                        ";


            $query = \Drupal::database()->query($sql);
            try{
                $results = $query->execute();
            } catch(\Exception $e){
                log_var($sql, "Could not run export claim", 'custom-contest');
                return null;
            }

        }

    }

    private function set_winners_email_flag($sql_result){
        foreach($sql_result as $res) {
            $email = $res->email;
            $contest_id = $res->contest_id;
            $enterdate = $res->enterdate;
            $sql = " UPDATE pepsicontest_winners SET email_sent = -1 WHERE 
                        email = '$email' AND
                        contest_id = $contest_id AND
                        enterdate = '$enterdate'
                        ";

            $query = \Drupal::database()->query($sql);
            try{
                $results = $query->execute();
            } catch(\Exception $e){
                log_var($sql, "Could not run export claim", 'custom-contest');
                return null;
            }

        }

    }

    private function remove_space($string){
        $string = strtolower($string);
        $string = preg_replace('/\s+/', '', $string);
        $string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);
        return $string;
    }

    public function fetchAllShippingAddress(){
        $sql = "SELECT address, email, contest_id from pepsicontest_reg_contest 
                WHERE address IS NOT NULL and address <> '' limit 10000 ";

        $query = \Drupal::database()->query($sql);
        $results = $query->fetchAll();
        if ( empty($results) )
            return [];

        $address_by_contest_email = [];
        foreach ($results as $res) {
            $address_by_contest_email[$res->contest_id][$res->email] = $this->remove_space($res->address);
        }
        return $address_by_contest_email;

    }

    public function batch_set_fraud_flag($sql_result){

        $dict_address = [];

        $contest_addresses = $this->fetchAllShippingAddress();

        foreach($sql_result as $res) {
            $email = $res->email;

            // $shipping_info = $this->fetchUserAddress($res->email, $res->contest_id);
            if (!isset($contest_addresses[$res->contest_id]) 
                || !isset($contest_addresses[$res->contest_id][$email])){
                continue;
            }


            // $address = $this->remove_space($shipping_info['address']);
            $address = $contest_addresses[$res->contest_id][$email]; 
            if (empty($address))
                continue;

            if (!isset($dict_address[$address])) {
                $dict_address[$address][] = $email;
            } else {
                if ( !in_array($email, $dict_address[$address])){
                    // it's the second email linked to the same address
                    $dict_address[$address][] = $email;
                    // debug_var($dict_address, 1);
                    // flag this address 
                    $this->update_suspicious_flag($res->contest_id, $email, $res->enterdate);
                }
                    // do nothing
            }

            // $winner[array_search('BillToAddress1', $headers)] = $shipping_info['address'];
            // $winner[array_search('BillToPostalZIP', $headers)] = $shipping_info['postalcode'];
        }
        
    }


    private function save_csv_file($sql_result){
        // $filename = "../contest_winners_" . date('Y-m-d') .  " .csv";
        $filename = "../export_contest/contest_winners_" . date('Y-m-d') .  " .csv";
        // $headers = ['ShipToEmail','ShipToFirstName','ShipToLastName', 'Item', 'ShipToAddress1','ShipToCity',
                    // 'ShipToProvState','ShipToPostalZIP','CustOrderDate','contest_id', 'RefNum'];

        $headers = ['Docket', 'CustRef1', 'CustOrderDate', 'ShipToCompany', 'ShipToFirstName', 'ShipToLastName',
                    'ShipToAddress1', 'ShipToAddress2', 'ShipToCity', 'ShipToPostalZIP', 'ShipToProvState', 
                    'ShipToCountry', 'ShipToPhone', 'ShipToEmail', 'BillToCompany', 'BillToFirstName', 
                    'BillToLastName', 'BillToAddress1', 'BillToAddress2', 'BillToCity', 'BillToPostalZIP', 
                    'BillToProvState', 'BillToCountry', 'BillToPhone', 'BillToEmail', 'CustRef2', 'Media2Code', 
                    'Media3Code', 'ShippingAgentCode', 'ShippingAgentService', 'Priority', 'InHandDate', 
                    'Language', 'Batch Shipping', 'UDFCharfld1', 'UDFCharfld2', 'UDFCharfld3', 'UDFCharfld4', 
                    'UDFCharfld5', 'UDFNumfld1', 'UDFNumfld2', 'UDFNumfld3', 'UDFDatefld1', 'UDFDatefld2', 
                    'UDFBoolean1', 'UDFBoolean2', 'UDFBoolean3', 'Item', 'Quantity', 'RefNum', 'OrderID', 'phone'
                ];
        $order_id = intval(date('md')) *1000;
        $content = '';
        $delimiter = '","';
        $csv_contents = [];
        $content .= '"'.  implode($delimiter,$headers) . '"' ."\n";

        $empty_values = [];
        for ($i = 0; $i < count($headers); $i++){
            $empty_values[$i] = '';
        }

        foreach($sql_result as $res) {
            if (empty($csv_contents[$res->contest_id])){
                $csv_contents[$res->contest_id] = '"'.  implode($delimiter,$headers) . '"' ."\n";
            } 

            $shipping_info = $this->fetchUserAddress($res->email, $res->contest_id);
            $winner = $empty_values;

            $winner[array_search('phone', $headers)] = $res->phone;
            $winner[array_search('ShipToEmail', $headers)] = $res->email;
            $winner[array_search('BillToEmail', $headers)] = $res->email;
            $winner[array_search('ShipToFirstName', $headers)] = $shipping_info['firstname'];
            $winner[array_search('BillToFirstName', $headers)] = $shipping_info['firstname'];
            $winner[array_search('ShipToLastName', $headers)] = $shipping_info['lastname'];
            $winner[array_search('BillToLastName', $headers)] = $shipping_info['lastname'];
            $winner[array_search('Item', $headers)] = str_replace("’", "'", $res->prize_name) ;
            $winner[array_search('ShipToAddress1', $headers)] = $shipping_info['address'];
            $winner[array_search('BillToAddress1', $headers)] = $shipping_info['address'];
            $winner[array_search('ShipToCity', $headers)] = $shipping_info['city'];
            $winner[array_search('BillToCity', $headers)] = $shipping_info['city'];
            $winner[array_search('ShipToProvState', $headers)] = $shipping_info['province'];
            $winner[array_search('BillToProvState', $headers)] = $shipping_info['province'];
            $winner[array_search('ShipToPostalZIP', $headers)] = $shipping_info['postalcode'];
            $winner[array_search('BillToPostalZIP', $headers)] = $shipping_info['postalcode'];
            $winner[array_search('CustOrderDate', $headers)] = $res->enterdate;
            $winner[array_search('Language', $headers)] = $res->language;
            $winner[array_search('Quantity', $headers)] = 1;
            $winner[array_search('OrderID', $headers)] = ++$order_id;
            $winner[array_search('RefNum', $headers)] = $this->get_refnum_by_prize($res->prize_name);
            // SEarch address here, order by enterdate, limit 1
            // add fraudulent check here
            // $winner = [ 
            //     $res->email,
            //     $shipping_info['firstname'],
            //     $shipping_info['lastname'],
            //     $res->prize_name,
            //     $shipping_info['address'],
            //     $shipping_info['city'],
            //     $shipping_info['province'],
            //     $shipping_info['postalcode'],
            //     $res->enterdate,
            //     $res->contest_id,
            //     $this->get_refnum_by_prize($res->prize_name),
            // ];

            $row = '"' .  implode($delimiter,$winner). '"' . "\n";
            $row = str_replace("®", "", $row);
            $row = remove_special_characters($row);
            $content .= $row;
            $csv_contents[$res->contest_id] .= $row;
        }
        foreach ($csv_contents as $contest_id => $value) {
            // Each contest_id has its own csv file
            $csv_filename = str_replace("contest_winners", "contest_winners_" . $contest_id, $filename);
            if (!file_put_contents($csv_filename, $value)){
                log_var(" $csv_filename could not be created", "CSV contest winners export");
            }
        }
        return 1;
        // return file_put_contents($filename, $content);
    }

    public function get_refnum_by_prize($prize_name){
        $ref_numbers = [
            "LAY’S branded towel" => 3580,
            "LAY'S branded towel" => 3580,
            "MISS VICKIE’S branded beach bag" => 3575,
            "MISS VICKIE'S branded beach bag" => 3575,
            "LAY’S branded beach ball" => 3580,
            "LAY'S branded beach ball" => 3580,
            "DORITOS branded bucket hat" => 3577,
            "CHEETOS branded tank top" => 3576,
            "DORITOS branded fanny pack" => 3578,
            "RUFFLES branded snapback hat" => 3579,
            "Instant camera" => 3581,
            "RUFFLES branded wireless boom box" => 3582,
            "CHEETOS Free Product Coupon" => 3569,
            "DORITOS DINAMITA Free Product Coupon" => 3570,
            "RUFFLES Free Product Coupon" => 3571,
            "FUNYUNS Free Product Coupon" => 3572,
            "SMARTFOOD Free Product Coupon" => 3573,
        ];

        if ($ref_numbers[$prize_name]){
            return $ref_numbers[$prize_name];
        }

        return 0;
    }


    public function fetchUserAddress($email, $contest_id){
        // @TODO: change this query to flag fraudulent entries
        $sql = " SELECT t2.email, t2.address, t2.postalcode, t2.province, t2.city,
                        t2.first_name, t2.last_name
                     from  pepsicontest_reg_contest  as t2
                    WHERE t2.address IS NOT NULL 
                    AND t2.address <> ''
                    AND contest_id = $contest_id
                    AND email = '$email'
                    limit 1
                        ";


        $shipping_info = [];
        $query = \Drupal::database()->query($sql);
        try{
            $results = $query->fetchAll();
            $shipping_info = [
                'firstname' => $results[0]->first_name,
                'lastname' => $results[0]->last_name,
                'address' => $results[0]->address,
                'province' => $results[0]->province,
                'city' => $results[0]->city,
                'postalcode' => $results[0]->postalcode,
            ];
            return $shipping_info;
        } catch(\Exception $e){
            log_var($sql, "Could save fraudulant entries", 'custom-contest');
            return $shipping_info;
        }
    }

    public function check_fraudulant_entries($contest_id){
        $sql = " SELECT t1.email, t2.address, t2.enterdate, COUNT(*) AS c FROM pepsicontest_winners AS t1 INNER JOIN
                    pepsicontest_reg_contest AS t2 ON 
                    (t1.contest_id = t2.contest_id AND t1.enterdate = t2.enterdate)
                    WHERE t2.address IS NOT NULL AND t2.address <> ''
                    GROUP BY t2.address, t1.email, t2.enterdate
                    HAVING c > 1
                        ";


        $query = \Drupal::database()->query($sql);
        try{
            $results = $query->fetchAll();
            foreach ($results as $key => $value) {
                $this->update_suspicious_flag($contest_id, $value->email,  $value->enterdate);
            }

        } catch(\Exception $e){
            log_var($sql, "Could save fraudulant entries", 'custom-contest');
            return null;
        }
    }

    private function update_suspicious_flag($contest_id, $email,  $enterdate){
        $query = \Drupal::database()->update('pepsicontest_winners');
        $query->fields([
            'suspicious' => 1,
          ])
          ->condition('contest_id', $contest_id)
          ->condition('enterdate', $enterdate )
          ->condition('email', $email);
         $success = $query->execute();

         return $success;
    }

    public function reset_prizes_quantity(){

        $sql = " SELECT t1.contest_id, t1.prize_id, t1.enterdate, t1.email 

                FROM  pepsicontest_winners AS t1 
                WHERE 
                     t1.email_sent = 0
                

                ";


        $query = \Drupal::database()->query($sql);

        try{
            $results = $query->fetchAll();

            if (!empty($results)){
                $result_groupby_prizeID = [];
                foreach ($results as $key => $res) {
                    if (empty($result_groupby_prizeID[$res->prize_id]) ){
                        $object = new \stdClass();
                        $object->contest_id = $res->contest_id;
                        $object->prize_id = $res->prize_id;
                        $object->cnt = 1;
                        $result_groupby_prizeID[$res->prize_id] = $object;
                    } else {
                        $result_groupby_prizeID[$res->prize_id]->cnt += 1;

                    }
                }


                foreach ($result_groupby_prizeID as $key => $res) {
                    $prize = \Drupal\node\Entity\Node::load($res->prize_id);
                    $quantity = $res->cnt;
                    if (intval($prize->field_quantity_awarded->value) > $quantity ){
                        $prize->field_quantity_awarded->value -= $quantity;
                        $prize->save();
                    }

                }
                $this->set_winners_email_flag($results);
                pepsibam_append_to_log(" Prize quantity reallocated ");
            }
        } catch(\Exception $e){
            log_var($sql, "Could not run export claim", 'custom-contest');
            return null;
        }

    }
}