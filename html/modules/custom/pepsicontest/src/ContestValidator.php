<?php

/**
 * @file
 */

namespace Drupal\pepsicontest;


class ContestValidator{
	
	public static function instance(){
		return new ContestValidator();
	}

	public function validate_age($province, $bday, $contest_type){
		if ($contest_type == 'nintendo'){
			return $this->validate_age_by_province($province, $bday);
		} elseif($contest_type == 'nintendo2'){
			return $this->validate_age_by_province($province, $bday);
		} elseif($contest_type == 'cheetos'){
			if (19 <= $this->get_age_from_dob($bday)){
				return 1;
			}
			return 0;
		} elseif($contest_type == 'grabsnack' || $contest_type == 'hockey'){
			if (16 <= $this->get_age_from_dob($bday)){
				return 1;
			}
			return 0;
		}

	}

	public function validate_age_by_province($province, $bday){
		// if (16 <= $this->get_age_from_dob($bday)){
		// 		return 1;
		// }
		// return 0;

		$province = strtolower($province);
		$legal_age = [
			'alberta' => 18,
			'british-columbia' => 19,
			'manitoba' => 18,
			'new-brunswick' => 19,
			'newfoundland-and-labrador' => 19,
			'northwest-territories' => 19,
			'nova-scotia' => 19,
			'nunavut' => 19,
			'ontario' => 18,
			'prince-edward-island' => 18,
			'quebec' => 18,
			'québec' => 18,
			'saskatchewan' => 18,
			'yukon-territory' => 19,
		];

		if ($legal_age[$province] <= $this->get_age_from_dob($bday)){
			return 1;
		}
		return 0;
	}

	public function get_age_from_dob($birthDate){
		$date = new \DateTime($birthDate);
		$now = new \DateTime();
		$diff = $now->diff($date);
		return $diff->y;
	}

	public function validate_claim($nid, $email, $enterdate){
		$nb_days_valid = 3;
		$n_days_ago = date("Y-m-d", strtotime("-$nb_days_valid day"));
		$claim = [];

		if ($enterdate < $n_days_ago){
			$claim['claimed'] = -1;
			$claim['expired'] = 1;
			// claim no longer valid after 3 days
			return $claim;
		}

		$query = \Drupal::database()->select('pepsicontest_winners', 'cw');
                // $query->addField('cw', 'contest_id');
                // $query->addField('cw', 'enterdate');
                $query->addField('cw', 'claimed');
                $query->condition('cw.contest_id', $nid);
                $query->condition('cw.email', $email);
                $query->condition('cw.enterdate', $enterdate);
                // $query->condition('cw.claimed', 0);
        
        // $num_rows = $query->countQuery()->execute()->fetchField();
        $result = $query->execute()->fetchAll();
 
        if (!empty($result)){
        	$claim['claimed'] = $result[0]->claimed;
			$claim['expired'] = 0;
			$claim['participant'] = $this->get_contest_participant_name($email, $enterdate, $nid);
        }

        return $claim;
	}

	public function get_contest_participant_name($email, $enterdate, $contest_id){
		$query = \Drupal::database()->select('pepsicontest_reg_contest', 'cw');
                $query->addField('cw', 'first_name');
                $query->addField('cw', 'last_name');
                $query->condition('cw.contest_id', $contest_id);
                $query->condition('cw.email', $email);
                $query->condition('cw.enterdate', $enterdate);
        
        $result = $query->execute()->fetchAll();
        $arr = [];
        if (!empty($result)){
        	$arr['firstname'] = $result[0]->first_name;
        	$arr['lastname'] = $result[0]->last_name;
        }
        return $arr;
	}

	public function validate_upc_code($code, $contest_type){
		if ($contest_type == 'nintendo'){
			return $this->FritolayValidateCode($code);
		} elseif($contest_type == 'nintendo2'){
			return $this->NintendoValidateCode($code);
		} elseif($contest_type == 'grabsnack'){
			return $this->GrabsnackValidateCode($code);
		} elseif($contest_type == 'cheetos'){
			return $this->CheetosValidateCode($code);
		} elseif($contest_type == 'hockey'){
			return $this->HockeyValidateCode($code);
		}

		return false;

	}

	private function NintendoValidateCode($code){
		$code = trim($code);
		if (strlen($code) !== 12)
			return false;

		$valid_codes = [
			"060410054185",
			"060410054086",
			"060410054208",
			"060410055090",
			"060410055038",
			"055577120217",
			"055577120200",
			"055577120224",
			"055577120231",
			"060410040775",
			"060410054994",
			"060410040768",
			"055577106662",
			"055577102732",
			"055577105221",
			"055577105665",
			"055577120101",
			"055577120118",
			"055577120149",
			"216736204215",
		];
	

		if ( in_array($code, $valid_codes) ){
			return true;
		}

		return false;

	}

	private function HockeyValidateCode($code){
		$code = trim($code);
		$case_upc = [
			'0-55577-10372-2',
			'100-55577-11300-1',
			'100-55577-11301-8',
			'100-55577-11302-5',
			'100-55577-11303-2',
			'100-55577-11304-9',
			'100-55577-11306-3',
			'100-55577-11307-0',
			'100-55577-11308-7',
			'0-55577-11309-7',
			'100-55577-11310-0',
			'100-55577-11311-7',
			'100-55577-11314-8',
			'100-55577-11312-4',
			'100-55577-11315-5',
			'100-55577-10239-5',
			'100-55577-10363-7',
			'0-55577-31190-5',
			'100-55577-10140-4',
			'0-55577-10372-2',
			'100-55577-11300-1',
			'100-55577-11301-8',
			'100-55577-11302-5',
			'100-55577-11303-2',
			'100-55577-11304-9',
			'100-55577-11306-3',
			'100-55577-11307-0',
			'100-55577-11308-7',
			'0-55577-11309-7',
			'100-55577-11310-0',
			'100-55577-11311-7',
			'100-55577-11314-8',
			'100-55577-11312-4',
			'100-55577-11315-5',
			'100-55577-10239-5',
			'100-55577-10363-7',
			'0-55577-31190-5',
			'100-55577-10140-4',
		];

		$unit_upc = [
			'55577102305',
			'55577113004',
			'55577113011',
			'55577113028',
			'55577113035',
			'55577113042',
			'55577113066',
			'55577113073',
			'55577113080',
			'55577103661',
			'55577113103',
			'55577113110',
			'55577113141',
			'55577113127',
			'55577113158',
			'55577102398',
			'155577103630',
			'55577311905',
			'55577101407',
			'0-55577-10230-5',
			'0-55577-11300-4',
			'0-55577-11301-1',
			'0-55577-11302-8',
			'0-55577-11303-5',
			'0-55577-11304-2',
			'0-55577-11306-6',
			'0-55577-11307-3',
			'0-55577-11308-0',
			'0-55577-10366-1',
			'0-55577-11310-3',
			'0-55577-11311-0',
			'0-55577-11314-1',
			'0-55577-11312-7',
			'0-55577-11315-8',
			'0-55577-10239-8',
			'1-55577-10363-0',
			'0-55577-31190-5',
			'0-55577-10140-7',
			'904134651742',
		];

		if ( in_array($code, $unit_upc) || in_array($code, $case_upc)){
			return true;
		}

		return false;

	}



	private function CheetosValidateCode($code){
		$code = trim($code);
		$valid_codes = [
			'060410054703',
			'060410054727',
			// '060410054789',
			'060410054741',
			'060410054765',
			'060410054802',
			'060410055113',
			'904174631847',
		];

		if ( in_array($code, $valid_codes) ){
			return true;
		}

		return false;
	}

	private function GrabsnackValidateCode($code){
		$code = trim($code);
		if (!is_numeric($code)){
			return false;
		}

		// only 9 digit code accepted
		if (strlen($code) != 9 ){
		  return false;
		}

		$p1 = intval(substr($code, 0, 1));
		if ($p1< 1 || $p1 > 7){
			return false;
		}
		// digit 2-4 can be anything
		$p5_p7 = intval(substr($code, 4,3));
		if ($p5_p7 < 143 || $p5_p7 > 220){
			return false;
		}

		return true;
	}

	private function FritolayValidateCode($code){
		$code = str_replace(":", "",$code); // Remove ":" sign
		// 16 / 19 digit codes
		// 16 digit codes only have one ":" while 19 digit codes have two ":" 

		// Count number of strings
		if (strlen($code) != 15 && strlen($code) != 17 ){
		  return false;
		}

		$p1 = substr ( $code , 0 , 1);  //1, 2, 3, 4, 5, 6, 7 only
		if (!is_numeric($p1)) return false;
		$p1 = (int)$p1;
		if ($p1 < 1 || $p1 > 7 ) return false;

		$p2_p3 = substr ( $code , 1 , 2); //7T, KT, 08 only
		if ($p2_p3 != "7T" &&  $p2_p3 != "KT" &&  $p2_p3 != "08" ) return false;

		$p4 = substr ( $code , 3 , 1); //1, 2, 3 only
		if (!is_numeric($p4)) return false;
		$p4 = (int)$p4;
		if ($p4 < 1 || $p4 > 3 ) return false;

		$p5_p6_p7 = substr ( $code , 4 , 3); // 90-206 inclusive
		if (!is_numeric($p5_p6_p7)) return false;
		$p5_p6_p7 = (int)$p5_p6_p7;
		if ($p5_p6_p7 < 90 || $p5_p6_p7 > 206 ) return false;
		  
		$p8_p9    = substr ( $code , 7 , 2); // 01
		if ($p8_p9 != "01") return false;

		$p10_p11   = substr ( $code , 9 , 2); // 11, 01 only
		if ($p10_p11 != "11" &&  $p10_p11 != "01") return false;

		$p12   = substr ( $code , 11 , 1); //1-2 only
		if (!is_numeric($p12)) return false;
		$p12 = (int)$p12;
		if ($p12 < 0 || $p12 > 2 ) return false;

		$p13   = substr ( $code , 12 , 1); //0-9 inclusive
		if (!is_numeric($p13)) return false;
		$p13 = (int)$p13;
		if ($p13 < 0 || $p13 > 9 ) return false;

		$p14   = substr ( $code , 13 , 1); //0-5 inclusive
		if (!is_numeric($p14)) return false;
		$p14 = (int)$p14;
		if ($p14 < 0 || $p14 > 5 ) return false;

		$p15   = substr ( $code , 14 , 1); //0-9 inclusive
		if (!is_numeric($p15)) return false;
		$p15 = (int)$p15;
		if ($p15 < 0 || $p15 > 9 ) return false;

		if (strlen($code) == 17){
			$p16   = substr ( $code , 15 , 1); //0-9 inclusive
			$p17   = substr ( $code , 16 , 1); //0-9 inclusive
			if (!is_numeric($p16) || !is_numeric($p17) ) return false;
			
			if ($p16 < 0 || $p16 > 5 ) return false;
			if ($p17 < 0 || $p17 > 9 ) return false;
		}


		//If positions 2 & 3 are "7T" (Colour coded red), positions 10 & 11 will be "11" (Colour coded red as well)	
		if ($p2_p3 == "7T" && $p10_p11 != "11") return false;

		//If positions 2 & 3 are "KT" (Colour coded blue), positions 10 & 11 will be "01" (Colour coded blue as well)	  
		if ($p2_p3 == "KT" && $p10_p11 != "01") return false;

		return true;
	}


	public function getCaptchaScore($grecaptcharesponse){        
        
        $secret = '6LfHi6QUAAAAAEX2f9VtLPv5hdSf3MjOTXP61Z92';
        // $secret = '6LfHi6QUAAAAAEX2f9VtLPv5hdSf3MjOTXP61Z92';
        
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$grecaptcharesponse);

     
        $responseData = json_decode($verifyResponse);

        if (isset($responseData->success) && $responseData->success == 1){
            $score  = $responseData->score;
            if (is_numeric($score)){
                return floatval($score);
            }

        }

        return null;
        
    }

    public function saveCaptchaScore($post){

    	$fields = [
    		'contest_id' => $post['contest_id'],
    		'email' => $post['email'],
    		'enterdate' => date("Y-m-d"),
    		'regdate' => date("Y-m-d H:i:s"),
    		'score' => $post['captcha_score'],
    	];

    	if ($post['captcha_score'] == null){
			log_var("", "captcha score is null", "captcha");
			return false;
		}

    	// log_var($fields, "Saving captcha score", "captcha");

    	if (\Drupal::database()->schema()->tableExists('pepsicontest_scores')) {
    		try{
            	$result = \Drupal::database()->insert('pepsicontest_scores')->fields($fields)->execute();
    		} catch(\Exception $e){
    			log_var("", "captcha score could not be saved", "captcha");
    		}

            return true;
        } 

        return false;

    }

    public function sendToSpamEntries($node, $user, $nomember){
    	$langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
        $date = date('Y-m-d H:i:s');

        $usercontest =  array(
                         'contest_id' => $node->id()
                        ,'user_id' => $user->id()
                        ,'contest_name' => $node->getTranslation($langcode)->field_contest_uri->value
                        ,'first_name' => $user->get('field_firstname')->value
                        ,'last_name' => $user->get('field_lastname')->value
                        ,'email' => $user->get('mail')->value
                        ,'gender' => $user->get('field_gender')->value?$user->get('field_gender')->value:'O'
                        ,'postalcode' =>  $user->get('field_postalcode')->value?$user->get('field_postalcode')->value:'NA'
                        ,'province' => $user->get('field_province')->value?$user->get('field_province')->value:'NA'
                        ,'city' => $user->get('field_city')->value?$user->get('field_city')->value:'NA'
                        ,'language' => $user->get('preferred_langcode')->value
                        ,'regdate' => $date
                        ,'user_ip' => $user->get('field_ip_address')->value
                        ,'user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255)
                        ,'enterdate' => $date
                        ,'nomember' => $nomember
                        ,'contest_optin' => $user->get('field_optin')->value
                        ,'bonus' => 0
                        );

        if (\Drupal::database()->schema()->tableExists('pepsicontest_spam_entries')) {

        	try{
	        	$result = \Drupal::database()->insert('pepsicontest_spam_entries')
	        									->fields($usercontest)
	        									->execute();

        	} catch(\Exception $e){
        		log_var($e, "Could not insert spam entries", "captcha_spam");
        	}

        }
    }

}