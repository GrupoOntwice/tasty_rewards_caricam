<?php

/**
 * @file
 */

namespace Drupal\microsite_contest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\pepsicontest\Controller\ContestController;
use Drupal\pepsicontest\ContestWinnerPicker;

class MicrositeContestController extends ContestController {

    protected $contestnode; 
    protected $user; 
    protected $source;

    protected $mail;
    protected $postalcode;
    protected $city;
    protected $province;
    protected $questiontype;
    protected $postanswers;
    protected $postanswersdefault;
    protected $changedpcode;
    
    protected $contestopened;
    protected $contestclosed;
    protected $availableforuser = false;
    
    protected $registeragain;
    protected $multiple_entries = false;

    /**
     * {@inheritdoc}
     */
    public function index(Request $request) {
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        //Check if coming with a token from Marketo for bonus points
        $session = \Drupal::service('session');
        die(" --- --- ");
    }

    /**
     * Overriding ContestController signup method
     * @param  Request $request [description]
     * @param  $contest : 
     * @return [type]           [description]
     */
    public function signup(Request $request, $contest) {
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //check if contest exists 
        if (!$this->getContestId($contest)) {
            // @TODO: create the contest not found page. 
            // 
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            die("Contets not found ");
            // return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
        }
        $node = $this->contestnode;  // set by getContestId()
        $node->getTranslation($langcode);

        $data['subtitle'] = $node->getTranslation($langcode)->field_title->value;
        $data['body'] = $node->getTranslation($langcode)->body->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;


        $data['provinces_options'] = $this->CreateProvinceDropdown();
        
        $contest_id = $node->id();
        $openingdate = $node->getTranslation($langcode)->field_opening_date->date; 
        $closingdate = $node->getTranslation($langcode)->field_closing_date->date; 
        $contestentry = $node->getTranslation($langcode)->field_contest_entry->value; //1=> Only once, 2=> One per day

        // prizes
        $prizes = $node->getTranslation($langcode)->field_contest_prizes->referencedEntities();
        $data['prizes'] = array();
        foreach ($prizes as $key => $node_prize) {
            $prize_img = \Drupal\file\Entity\File::load($node_prize->getTranslation($langcode)->field_prize_image->target_id);
            
            $data['prizes'][$key] = array(
                'title' => $node_prize->getTranslation($langcode)->field_subtitle->value,
                'description' => $node_prize->getTranslation($langcode)->body->value,
                'image' => $prize_img->getFileUri(),
            );
        }

        
        $data['colorTheme'] = $node->getTranslation($langcode)->field_contest_color_theme->value;         
        $data['mobileColorTheme'] = $node->getTranslation($langcode)->field_mobile_color_theme->value; 
        // $data['bannerTextPosition'] = $node->getTranslation($langcode)->field_contest_banner_position->value; 

        
        //Check if contest is still opened
        $status = $this->getContestStatus($openingdate, $closingdate);
        

        //$status  3 = close, 2 - coming soon, 
        if ($status == 2 || $status == 3)  {
            //Have to redirecto to landing page with a anchor 
            return new RedirectResponse(\Drupal\Core\Url::fromRoute("pepsicontest.landing")->toString()."#$contest"); 
        }
        
        //Check if coming with a token from Marketo for bonus points
        $session = \Drupal::service('session');
        $mkcontest = $request->get('mkcontest');      
        
        if ($mkcontest) {
            $session->set('mkcontest',$mkcontest);
        }
        
        $template = 'microsite_contest_signup_template';
        $alreadyregistered = false;
        $data['geotargeting'] = false;
        $data['clmn'] = $request->get('clmn');
        //When there is a user's session 
        //Check if user is loged in
        if (!$user_id = \Drupal::currentUser()->id()) {
            $data['is_loggedin'] = 0;
            // $template = 'microsite_contest_register_template';
            //return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.register", array('contest'=>$contest))->toString()); 
        }
        else{
            $data['is_loggedin'] = 1;
            //getting the logged user data
            $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
            $this->user =  $user;
            
            $post['email']          = $user->get('mail')->value;
            $post['firstname']      = $user->get('field_firstname')->value;
            $post['lastname']       = $user->get('field_lastname')->value;
            $post['postalcode']     = $user->get('field_postalcode')->value;  
            
            //Check if user didn't play before 
            $status = 0; // user no registered
            if ($this->isUserRegistered($contest_id,$user_id,$contestentry )){
                if ($this->registeragain) {
                    //return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.processed.alreadyregistered", array('contest'=>$contest))->toString()); 
                    $status = 1; // already Registered, but could register again today
                }
                else {
                    $status = 4; // already Registered and can't participate at least today o no anymore depend of the type of contest
                }
            }
            
            //Check if Geo targetting contest 
            if (!$this->GeoTargetFilter()){
                $data['geotargeting'] = true;
            }
       }
        
        
        $data['submitted'] = 0;
        //Check if this is coming from a POST
        if ($request->getMethod()=='POST'){
            $data['submitted'] = 1;
            //data validation
            $post['casl'] = $request->get('casl');
            $post['newregister'] = $request->get('newregister');
            
            
            $post['firstname'] = trim(isset($post['firstname'])?$post['firstname']:$request->request->get('firstname'));
            $post['lastname'] = trim(isset($post['lastname'])?$post['lastname']:$request->request->get('lastname'));
            $post['postalcode'] = trim(isset($post['postalcode'])?$post['postalcode']:$request->request->get('postalcode'));
            $post['postalcode'] = str_replace(' ', '', $post['postalcode']);
            $post['email'] = trim(strtolower(isset($post['email'])?$post['email']:$request->request->get('email')));
            // $post['confirm_email'] = trim(strtolower($request->request->get('confirm_email')));
            
            
            //$optin_value = $request->request->get('optin') == 'on'? 1 : 0;
            $post['optin']      = $request->request->get('optin') == 'on'? 1 : 0; //get_current_langcode() == 'en-us'? 1 : $optin_value;
            $post['consent']    = $request->request->get('consent') == 'on'? 1 : 0; 
            $post['csrfToken'] = $request->request->get('csrfToken');
            $post['grecaptcharesponse'] = $request->request->get('grecaptcharesponse_contest');

            $nb_upc = $request->get('nb_upc_code');
            $post['nb_upc_code'] = !is_numeric($nb_upc)? 1 : abs($nb_upc);
            $post['upc_code'] = $request->get('upc_code');
            $post['province'] = $request->get('province');

            for ($i = 2; $i < intval($post['nb_upc_code']) + 1 ; $i++ ) {
                $post['upc_code_' . $i ] = $request->get('upc_code_' . $i);
                
            }        
            
            


            
            // Fields we may potentially add later on
            
            /* 
            $post['password'] = $request->request->get('password');
            $post['confirm_password'] = $request->request->get('confirm_password');
            $post['fbid'] = $request->request->get('fbid');
            $post['gender'] = $request->request->get('gender');
            $post['questionid'] = $request->get('questionid');
            $post['questiontype'] = $request->get('questiontype');
            $this->questiontype = !empty($post['questiontype'])? reset($post['questiontype']) : '';
            $post['answ'] = $request->get('answ');
            
            $post['postalcode'] = trim(strtoupper(isset($post['postalcode'])?$post['postalcode']:$request->request->get('postalcode')));
            $post['postalcode'] = str_replace(' ', '', $post['postalcode']);
            $post['bday_day']   = $request->request->get('bday_day')?$request->request->get('bday_day'):'';
            $post['bday_month'] = $request->request->get('bday_month')?$request->request->get('bday_month'):'';
            $post['bday_year']  = $request->request->get('bday_year')?$request->request->get('bday_year'):'';
            $post['bday']       = $post['bday_year'] . '-' . $post['bday_month'] . '-' . $post['bday_day'];

            */
            
            $source = $this->validateSource('tastyrewards');
            
            
            if ($post['newregister']) { // signup + register into contest
                $errors = $this->validateRegistration($post,$new=true); 
                if (count($errors) == 0) {
                    //Process and register the contest
                    //create user
                    $saved = true;
                    try {
                        $user = User::create([
                                    'name' => $post['email'],
                                    'mail' => $post['email'],
                                    'status' => 1,
                                    'field_firstname' => $post['firstname'],
                                    'field_lastname' => $post['lastname'],
                                    'field_city' => $this->city,
                                    'field_province' => $this->province,
                                    'field_postalcode' => $post['postalcode'],
                                    'field_gender' => $post['gender'],
                                    'field_bday' => $post['bday'],
                                    'field_fbid' => $post['fbid'],
                                    'field_optin' => $post['optin'],
                                    'field_marketocookie' => "microsite_contest",
                                    'field_source' => $source,
                                    'field_ccid' => $this->source['ccid'],
                                    'field_ip_address' => getIPaddress(),
                                    'field_user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255)
                        ]);
                        $user->setPassword($post['password']);

                        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

                        $date = date('Y-m-d H:i:s');
                        $user->set("field_created_date", $date); 
                        if ($post['optin']) {
                            $user->set("field_optin_date", $date ); 
                            $user->set("field_optin_casl", $post['casl']); 
                        }

                        $user->set("langcode", $language);
                        $user->set("preferred_langcode", $language);
                        $user->set("preferred_admin_langcode", $language);

                        // $user->save();

                        // $this->GenerateSaveActivationToken($user);

                        $this->user = $user; //setting user to class

                    } catch (\Exception $e) {
                        $saved = false;
                        $errors['form'] = t('Oops, there is an issue. Please try again.');
                        $user_mail = isset($post['mail'])? $post['mail'] : " ";
                        \Drupal::logger('general')->info("Could not save Tostitos contest entry by " . $user_mail, []);
                    }
                    if ($saved){
                        $existing_user = user_load_by_mail($post['email']);
                        if (empty($existing_user) )
                            sfmcservices_subscribe($user);
                        else
                            $user = $existing_user;
                        // sfmcservices_trigger_email_activation($user);
                        
                        //@TODO: Determine if we need Geotracking?
                        

                        $session = \Drupal::service('session');
                        $session->set('signupnomember','1');
                        
                        //Check if Geo targetting contest 
                        if ($this->GeoTargetFilter()){
                            //register contest
                            
                            $this->registerUser($node, $user, $post['answ'],1, $post['nb_upc_code']);
                        }
                        else{
                            $session->set('signupnomemberGeo','1'); // set seesion in order to show the right error message in thank you page
                        }

                        /*
                        $session->set('send_datalayer','no'); // set session in order to sending datalayer for tracking
                        $session->set('send_datalayer_optin_value',$post['optin']); // set session in order to sending datalayer for tracking
                        */
                        $langcode = get_current_langcode();
                        
                        $is_winner = ContestWinnerPicker::instance()->draw_instant_winner($node, $post['email']);

                        if ($is_winner){                            
                            // Save the information of the winner in a DE
                            // Add the winner to the Tostitos Contest winner field
                            $previous_winners = $node->get('field_winners')->value;

                            $current_prize = getNextPrizeTitle($this->contestnode, $skip = true);

                            $node->set('field_winners', $previous_winners . ", " . $user->get('mail')->value . "[" . $current_prize . "]");

                            $node->save();
                            sfmcservices_contest_winner($user, $node);
                            if (empty($existing_user))
                                user_delete($user->id());
                            return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.winner", array('contest'=>$contest))->toString());
                        }

                        // We are not keeping user info on the CMS
                        if (empty($existing_user))
                            user_delete($user->id());

                        return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.sorry", array('contest'=>$contest))->toString());

                        // return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.thank-you.new", array('contest'=>$contest))->toString());
                    }

                }
                
            }
            else { // // only register to contest
                $errors = $this->validateRegistration($post,$new=false);   

                if (count($errors) == 0) {
                    //Process and register the contest
                    //Save data in the user object
                    $user = $this->user;
                    $date = date('Y-m-d H:i:s');
                    if(isset($user)) {
                        
                    $user->set('field_edit_date', $date);
                    $user->set('field_ip_address', getIPaddress());
                    $user->set('field_user_agent', substr($_SERVER['HTTP_USER_AGENT'],0,255));
                    

                    if ($user->get('field_optin')->value != 1) {
                        
                        $user->set('field_optin', $post['optin']);
                        
                        if ($post['optin']) {
                            $user->set("field_optin_date", $date );
                            $user->set("field_optin_casl", $post['casl']);
                        }                        
                    }

                    $user->save();
                    insert_ip_address(getIPaddress(), $post['email']);

                    //Call Marketo subscribe
                    // MarketoSubscribe($user);
                    sfmcservices_subscribe($user);
                    

                    $this->registerUser($node, $user, $post['answ'], 0 , $post['nb_upc_code']);
                    $session = \Drupal::service('session');
                    $session->remove('signupnomember');
                    $session->remove('signupnomemberGeo');

                    $session->set('send_datalayer','no'); // set session in order to sending datalayer for tracking
                    $session->set('send_datalayer_optin_value',$post['optin']); // set session in order to sending datalayer for tracking
                    
                    $langcode = get_current_langcode();

                    // $langcode = get_current_langcode();
                        
                    $is_winner = ContestWinnerPicker::instance()->draw_instant_winner($node, $post['email']);

                    if ($is_winner){                            
                        // Save the information of the winner in a DE
                        // Add the winner to the Tostitos Contest winner field
                        $previous_winners = $node->get('field_winners')->value;
                        $current_prize = getNextPrizeTitle($this->contestnode, $skip = true);

                        $node->set('field_winners', $previous_winners . ", " . $user->get('mail')->value . "[" . $current_prize . "]");
                        $node->save();
                        sfmcservices_contest_winner($user, $node);
                                                
                        return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.winner", array('contest'=>$contest))->toString());
                    }                  
                    

                    return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.sorry", array('contest'=>$contest))->toString());

                    // return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.thank-you.new", array('contest'=>$contest))->toString());
                    }

                }                
            }
            
            $data['error'] = $errors;
        }
        
        
        $session = \Drupal::service('session');

        $email = $session->get('email');
        $fbid = $session->get('fbid');
        $firstname = $session->get('firstname');
        $lastname = $session->get('lastname');        
        
        if ($session->get('nb_upc_code')){
            $post["nb_upc_code"] = $session->get('nb_upc_code');
            if ($post["nb_upc_code"] > 1){
                for($i = 2; $i <= $post["nb_upc_code"] + 1; $i++){
                    $post["upc_code_" . $i] = $session->get('upc_code_' . $i);
                }                
            }
        }
        if ($session->get('upc_code'))
            $post["upc_code"] = $session->get('upc_code');
        if ($session->get('email'))
            $post["email"] = $session->get('email');
        if ($session->get('fbid'))
            $post["fbid"] = $session->get('fbid');
        if ($session->get('firstname'))
            $post["firstname"] = $session->get('firstname');
        if ($session->get('lastname'))
            $post["lastname"] = $session->get('lastname');
        
        $session->remove('email');
        $session->remove('fbid');
        $session->remove('firstname');
        $session->remove('lastname');        
        $session->remove('nb_upc_code');        
        $session->remove('upc_code');        
        

        $data['firstname']        = isset($post['firstname'])?$post['firstname']:'';
        $data['lastname']         = isset($post['lastname'])?$post['lastname']:'';
        $data['email']            = isset($post['email'])?$post['email']:'';
        $data['confirm_email']    = isset($post['confirm_email'])?$post['confirm_email']:'';
        $data['password']         = isset($post['password'])?$post['password']:'';
        $data['confirm_password'] = isset($post['confirm_password'])?$post['confirm_password']:'';
        $data['upc_code']         = isset($post['upc_code'])?$post['upc_code']:'';
        $data['nb_upc_code']      = isset($post['nb_upc_code'])?$post['nb_upc_code']:'';
        if ($data['nb_upc_code'] > 1){
            $data['extra_upc_codes'] = [];
            for($i = 2; $i <= $post["nb_upc_code"] + 1; $i++){
                $data['extra_upc_codes'][$i] = isset($post['upc_code_' . $i])?$post['upc_code_' . $i]:'';
            }
        }

        $data['fbid']             = isset($post['fbid'])?$post['fbid']:'';
        
        $data['postalcode']       = isset($post['postalcode'])?$post['postalcode']:'';
        $data['gender']           = isset($post['gender'])?$post['gender']:'';
        $data['province']         = isset($post['province'])?$post['province']:'';
        $data['optin']            = isset($post['optin'])?$post['optin']:0;
        $data['consent']          = isset($post['consent'])?$post['consent']:0;
        
        $data['bdaydropdown'] = $this->CreateDateDropdown(isset($post['bday'])?$post['bday']:null);
        
        
        //contest data
        $data['status'] = $status;
        $data['title'] = $node->getTranslation($langcode)->field_title->value;
        $data['brand'] = $node->getTranslation($langcode)->field_brand->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;
        $data['tag'] = $node->getTranslation($langcode)->field_contest_tag->value;

        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;
        
        $data['provinces_options'] = $this->CreateProvinceDropdown($data['province']);
        $data['contesturi'] = $contest;
        
        // Make the mobile header image mandatory?
        $mobileimagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_header_image_mobile->target_id);
        $data['headermobileimage']  = $mobileimagefile->getFileUri();
        
        
        $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_header_image->target_id);
        $data['headerimage']  = $imagefile->getFileUri();
        $user = $this->user;
        $data['user_optin'] = 0;
        if($user){
            $data['optin']  = isset($post['optin'])?$post['optin'] : $user->get('field_optin')->value;
            $data['user_optin'] = $data['optin'];
        }else{
            $data['optin']  = isset($post['optin'])?$post['optin']:0;
        }


        //get contest question
        $data['questions'] = $this->getContestQuestion($node,$langcode);
        $data['answ'] = array();
        
        
        if (isset($post['answ'])) {
            $data['answ'] = $post['answ'];
        }
        
        //generating CSRF token
        $data['csrfToken'] = CreateCsrfToken();
        //Getting language and passing to twig
        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $data['langcode'] = get_current_langcode();
        
        $data['registeragain'] = $this->registeragain;
        
        $data['contestentry'] = $contestentry;
        
        // print_r($template);
        // die("374 ");
        /**For testing **/
        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }
    /**
     * Overriding Registration validation
     * @param  [array] $post array of values submitted through the contest form
     * @param  [boolean] $new  True if the user is new on the site, false if is an existing one
     * @return [boolean]       
     */
    public function validateRegistration($post,$new) {
        
        //Check the CsrfToken
        $error = array();
        $user = $this->user;
        
        
        // if(!$this->CheckCsrfToken($post['csrfToken'])) {
        //     $error['form'] = t('Oops, there is an issue. Please try again.');
        //     return $error;
        // }

        if ($new) {

            // UPC code
            if (!$this->UPCcodeValidation($post['upc_code']))
                $error['upc_code'] = t('Sorry, your UPC could not be verified. Please double check that you entered it correctly and try again');
            if ($post['nb_upc_code'] > 1){
                $error['extra_upc_codes'] = [];
                $has_invalid_codes = false;
                for($i = 2; $i <= $post['nb_upc_code']  ; $i++){
                    if (!$this->UPCcodeValidation($post['upc_code_' . $i])){
                        $has_invalid_codes = true;
                        $error['extra_upc_codes'][$i] = t('Sorry, your UPC could not be verified. Please double check that you entered it correctly and try again');
                    }
                }
                if (!$has_invalid_codes)
                    unset($error['extra_upc_codes']);
            }
            //firstname val
            if (!$this->userNamesValidation($post['firstname']))
                $error['firstname'] = t('Please enter a valid first name.');

            //lastname val
            if (!$this->userNamesValidation($post['lastname']))
                $error['lastname'] = t('Please enter a valid last name.');

            if (!$this->postalCodeValidation($post['postalcode']))                
                $error['postalcode'] = t('Please enter a valid postal code.');

            if (!$post['consent'])                
                $error['consent'] = t('Mandatory field');

            //@TODO: Validate if user has already signed up to contest this week
            if (!$this->CheckQuotaUPC($post['email']))
                $error['email'] = t('You have used up your UPC codes for this week');

            //validate Email 
            if (!$this->EmailValidation($post['email']))
                $error['email'] = t('Please enter a valid email');

            if (!$this->WeeklyEntriesValidation($post['email'], $post['nb_upc_code']))
                $error['email'] = t('You have used up 7 UPC codes this week');
            // This validation step is removed for the new site
            // elseif (trim(strtolower($post['email'])) != trim(strtolower($post['confirm_email'])))
            //     $error['confirm_email'] = t("Emails do not match.");
            elseif ($this->UserRegistered($post['email'])) {
                // $path = Url::fromRoute('user.login')->toString();
                $error['email'] = t('You have already entered this contest today. You can only participate once per day to a limit of 7 entries per week.') ;
            }
            elseif(!validate_ip_address(getIPaddress(), $post['email'])){
                $error['email'] = t('Your IP address has been blocked');
            }

            // Potential extra validation steps
            /* 
            //postalcode
            //if (!preg_match('/^[a-zA-Z0-9]{3}( )?[a-zA-Z0-9]{3}$/', trim($post['postalcode'])))
            if (!$this->postalCodeValidation($post['postalcode']))                
                $error['postalcode'] = t('Please enter a valid postal code.');
            else {
                //gettting City and province
                $this->searchProvince ($post['postalcode']);
            } 

            if (!$this->PasswordFormatValidation($post['password'])  )
                $error['password'] = t('Please enter a valid password');
            //captcha
             */
            
            // This validation step is removed for the new site
            if (!RecaptchaValidation($post['grecaptcharesponse'])){
                $error['grecaptcharesponse'] = t('Recaptcha Required.');
            }

           

            

        }
        // No questions are involved in the microSite
        if (!$this->registeragain && 0) { //question validation would be only for first registration
            if (is_array($post['questionid'])) {
                foreach ($post['questionid'] as $questionid) {
                    if (!isset($post['answ'][$questionid])) {
                        if ($post['questiontype'][$questionid] == 'MultipleChoice')
                            $error['q'][$questionid] = t('Please answer the multiple choice question');
                        else {
                            $error['q'][$questionid] = t('Please answer the question');
                        }
                    }
                }
            }
        }
        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($post);
        \Doctrine\Common\Util\Debug::dump($error);
        echo "</pre>";        
         * 
         */
        //exit;
        

        return $error;
    }   

    /**
     * Overriding ContestController getContestId method
     * @param  $contest [description]
     * @return [type]          [description]
     */
    function getContestId ($contest){
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
        $em = \Drupal::service('entity_type.manager');
        
        $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                ->condition('type', 'microsite_contest')
                ->condition('field_contest_uri', $contest, '=')
                // ->condition('field_contest_type', 'external', '=')
                ->condition('langcode', $langcode);
        $nids = $query->execute();
            
        if (count($nids) == 0){
            return false;
        }
        
        $node = \Drupal\node\Entity\Node::load(reset($nids));
        
        if ($node){
            $node->getTranslation($langcode);
            $this->contestnode =  $node; 
            return true;
        }
        return false;
    }

    /**
     * Overriding ContestController registerUser method
     * @param  entity  $node           Contest Node
     * @param  entity  $user           
     * @param  array  $subjectanswers [description]
     * @param  integer $nomember       [description]
     * @return [type]                  [description]
     */
    function registerUser ($node, $user, $subjectanswers, $nomember = 0, $nb_upc_code = 1){
        
        parent::registerUser ($node, $user, $subjectanswers, $nomember);

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
        // ADD to new contest_microsite_weekly table
        // Check if user already exist in the table
        $sql = "SELECT * from pepsi_microsite_weekly_entries  WHERE  
                contest_id = " . $node->Id() . " AND email = '" . $usercontest['email'] . "'" ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        
        if (empty($result)){
            $sql = "INSERT IGNORE INTO pepsi_microsite_weekly_entries (email, contest_name, contest_id, nb_entries, nb_upc) VALUES (
            '" . $usercontest['email'] . "', '" . $usercontest['contest_name'] . "', " . $usercontest['contest_id'] . ", $nb_upc_code, $nb_upc_code); ";

        } else {
            $nb_entries = $result[0]->nb_entries;
            $total_entries = $nb_entries + $nb_upc_code;
            // Update the nb_count
            // UPDATE `pepsi_microsite_weekly_entries` SET `email`=[value-1],`contest_name`=[value-2],`contest_id`=[value-3],`nb_entries`=[value-4],`nb_upc`=[value-5] WHERE 1
            $sql = "UPDATE pepsi_microsite_weekly_entries SET nb_entries = $total_entries  WHERE 
            email = '" . $usercontest['email'] . "' AND contest_id = " . $usercontest['contest_id'] . "; ";
        }


        try{
            // print_r($sql);
            $select = \Drupal::database()->query($sql);
            $result = $select->execute();
        } catch (\Exception $e) { 
            $channel = "general";
            $message = " microsite contest entry could not be saved \n Query: " . $sql . " \n " . $e->getMessage() ;
            $context = [ ];
            \Drupal::logger($channel)->info($message, $context);
            
            //return null;
        }
        
        // die("register --");
        $chips_and_dips_upc = false;
        
        if ($chips_and_dips_upc){
            // $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
            
            $this->addAdditionalEntries($usercontest, 1);
        }

        return false;
    }

    public function getConfirmationPageData($contest){
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];
        
        $node = $this->contestnode;
        
        $node->getTranslation($langcode);
        
        $session = \Drupal::service('session');
        
        $signupnomember = $session->get('signupnomember');
        $signupnomemberGeo = $session->get('signupnomemberGeo');
        
        
        
        $data['language'] = $langcode;
        $data['language_url'] = $lang_url;
        $data['body'] = $node->getTranslation($langcode)->field_thankyou->value;
        $data['title'] = $node->getTranslation($langcode)->field_title->value;
        $data['brand'] = $node->getTranslation($langcode)->field_brand->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;
        $data['tag'] = $node->getTranslation($langcode)->field_contest_tag->value;
        
        $data['landinguri'] = $path = Url::fromRoute('pepsicontest.landing',array(),array('absolute'=>True))->toString(); 
        $data['contestname'] = $contest;
        $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_landing_image->target_id);
        $data['image']  = $imagefile->getFileUri();
        $data['signupnomember'] = $signupnomember;
        $data['signupnomemberGeo'] = $signupnomemberGeo;

        $data['send_datalayer'] = $session->get('send_datalayer');

        $recipes = $node->getTranslation($langcode)->field_contest_recipes->referencedEntities();
        $data['recipes'] = array(); 
        foreach ($recipes as $key => $node_recipe) {
            $img = \Drupal\file\Entity\File::load($node_recipe->getTranslation($langcode)->field_recipe_image->target_id);
            $path_alias = \Drupal::service('path_alias.manager')->getAliasByPath("/node/". $node_recipe->Id(), $langcode);
            $data['recipes'][$key] = array(
                'title' => $node_recipe->getTranslation($langcode)->field_recipe_subtitle->value,
                'description' => $node_recipe->getTranslation($langcode)->body->value,
                'image' => $img->getFileUri(),
                'url' => "/" . get_current_langcode() . $path_alias,
            );
        }   

        $session->set('send_datalayer','yes');
        $data['send_datalayer_optin_value'] = $session->get('send_datalayer_optin_value');
        $session->remove('send_datalayer_optin_value');

        $session->remove('signupnomember');
        $session->remove('signupnomemberGeo');  
        // //update to show the contest available for the current user
        $this->availableforuser = true; //to make sure have only contest available for users
        $this->getContestforLanding();
        $data['contestopened'] = $this->contestopened;
        return $data;
    }

    public function confirmation($contest) {
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        //check if contest exists 
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
            
        }

        $data = $this->getConfirmationPageData($contest);

        return array(
            '#theme' => 'microsite_contest_confirmation_template',
            '#data' => $data,
        );
    }


    public function confirmation_winner($contest){
        $data = [];
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        //check if contest exists 
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
            
        }
        
        $data = $this->getConfirmationPageData($contest);
        $prize_node = getNextPrize($this->contestnode);
        $data['prize'] = $prize_node->getTranslation($langcode)->field_subtitle->value;

        return array(
            '#theme' => 'microsite_contest_confirmation_winner_template',
            '#data' => $data,
        );
    }


    public function contestRules($contest){
        return parent::rules($contest);
    }

    /**
     * Overriding ContestController addAdditionalEntries method
     * @param  $usercontest  Contest node
     * @param  $bonus   
     */
    function addAdditionalEntries($usercontest, $bonus = false){
        if (empty($bonus)){
            return;
        }
        $n = 5;
        for ($x = 1; $x <= $n; $x++) {
            $usercontest['bonus'] = $x;
            $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
        }      
    }

    public function isValidUPC(Request $request){
        // $return = ['Status' => 'FAIL', 'method' => $request->getMethod(), 'count' => $request->request->count()];
        $return = ['status' => 0];

        // $return = ['post' = $_POST];
        // if( $request->getMethod()=='POST'){
            $upc_code = $request->request->get('upccode');
            if( $this->UPCcodeValidation($upc_code) )
                $return = ['status' => 1];

        // }
        // return $return;
        return new JsonResponse($return);
    }

    /**
     * 
     * @param $upc_code 
     */
    function UPCcodeValidation($upc_code){
        // Long and short codes are valids?
        $upc_code = "". $upc_code;
        $valid_chips_codes = array(
            "060410030462","060410030370","060410030387","060410030486","060410010822","060410901243","060410901236","060410901205",
            "060410901168","060410901175","060410901199","060410901212","060410901229","060410901151","060410037386","060410040232",
            "060410040263","060410038888","060410004463","060410030646","060410030653","060410034132"
        );

        $valid_dips_codes = array(
            "060410049549","060410049556","060410073025","060410010976","060410010983","060410010990","060410009246","060410016275",
            "060410026625","060410011003","060410069608","060410072998","060410072981","060410043912","060410040386","060410040393","060410034026","850142621437"
        );
        if (in_array($upc_code, $valid_dips_codes) || in_array($upc_code, $valid_chips_codes)){
            return true;
        }
        return false;
    }

    public function WeeklyEntriesValidation($email, $nb_upc_code){
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        // contest_id, enterdate, bonus, email
        $node = $this->contestnode;
        $contest_id = $node->id();
        // @TODO: Add an index to the email field oin the pepsicontest_reg_contest table
        $sql = "SELECT nb_entries from pepsi_microsite_weekly_entries  WHERE contest_id = " . $contest_id . " AND email = '" . $email . "'" ;

        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        
        if (empty($result)){
            return true;
        } else {
            $nb_entries = $result[0]->nb_entries;
            if ($nb_entries + $nb_upc_code < 8)
                return true;
        }
        return false;
    }

    public function UserRegistered($email) {
        // TODO: find if user has registered for the day already
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        // contest_id, enterdate, bonus, email
        $node = $this->contestnode;
        $contest_id = $node->id();
        // @TODO: Add an index to the email field oin the pepsicontest_reg_contest table
        $sql = "SELECT * from pepsicontest_reg_contest  WHERE enterdate = CURDATE() 
                AND contest_id = " . $contest_id . " AND email = '" . $email . "'" ;

        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        
        if (!empty($result)){
            return true;
        }
        return false;      

    }

    function CheckQuotaUPC($email){
        return true;
        // $sql = "SELECT * from pepsicontest_reg_contest  WHERE enterdate = CURDATE() 
        //         AND contest_id = " . $contest_id . " AND email = '" . $email . "'" ;

        // $select = \Drupal::database()->query($sql);
        // $result = $select->fetchAll();
        
        // if (!empty($result)){
        //     return true;
        // }
    }

    function CreateProvinceDropdown($selected_province = null) {
        $provinces = [t("Alberta"), t("British Columbia"), t("Manitoba"), t("New Brunswick"), t("Newfoundland and Labrador"), t("Nova Scotia"), t("Ontario"), t("Prince Edward Island"), t("Quebec"), t("Saskatchewan"), t("Northwest Territories"), t("Nunavut"), t("Yukon")];
        $options = [];
        foreach ($provinces as $key => $province) {
            $options[str_replace(' ', '-', strtolower($province))] = $province;
        }

        $province_option = '<option value="">' . t('Province') . '</option>';
        foreach ($options as $key => $province) {
               
            $tmp = ($selected_province == $key) ? 'selected' : '';
            $province_option = $province_option . '<option value="' . $key . '" ' . $tmp . ' >' . $province . '</option>';
        }

        

        return $province_option;
    }
    
}
