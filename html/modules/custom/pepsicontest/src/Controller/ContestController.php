<?php

/**
 * @file
 */

namespace Drupal\pepsicontest\Controller;

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
use Drupal\pepsicontest\ContestValidator;

class ContestController extends ControllerBase {

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
    protected $multiple_entries = true;
    protected $is_snackperk_user = false;
    protected $gets_bonus_entries = false;
    protected $syncSfmcUSA = true;
    /**
     * {@inheritdoc}
     */
    public function index(Request $request) {
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        //Check if coming with a token from Marketo for bonus points
        $session = \Drupal::service('session');
        $mkcontest = $request->get('mkcontest');
        if ($mkcontest) {
            $session->set('mkcontest',$mkcontest);
        }
       
        //Getting language and passing to twig
        // $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $currentLang = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $currentLang;
        $data['language_url'] = $prefixes[$currentLang];
        
        //create contest arrays
        $this->getContestforLanding();
        
        $data['contestopened'] = $this->contestopened;
        $data['contestclosed'] = $this->contestclosed;
        // $data['contestclosed'] = array_slice($this->contestclosed, 0, 3);
        $data['w'] = $request->get('w');

        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($data['contestopened']);
        echo "</pre>";
        */
        return array(
            '#theme' => 'pepsicontest_landing_template',
            '#data' => $data,
        );
    }

    public function thankyou($contest) {
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];
        $data['language'] = $langcode;
        $data['language_url'] = $lang_url;

        //check if contest exists 
        if (!$this->getContestId($contest)) {
            $lang_prefix = \Drupal::languageManager()->getCurrentLanguage()->getId();
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
            
        }
        
        $node = $this->contestnode;
        
        $node->getTranslation($langcode);
        
        $session = \Drupal::service('session');
        
        $signupnomember = $session->get('signupnomember');
        $signupnomemberGeo = $session->get('signupnomemberGeo');
        
        
        $data['body'] = $node->getTranslation($langcode)->field_thankyou->value;
        $data['title'] = $node->getTranslation($langcode)->field_title->value;
        $data['brand'] = $node->getTranslation($langcode)->field_brand->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;
        $data['tag'] = $node->getTranslation($langcode)->field_contest_tag->value;
        
        $data['landinguri'] = $path = Url::fromRoute('pepsicontest.landing',array(),array('absolute'=>True))->toString(); 
        $data['contestname'] = $contest;
        $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_landing_image->target_id);
        if ($imagefile){
            $data['image']  = $imagefile->getFileUri();
        }
        $data['image_url'] = get_translated_image_url($node,'field_landing_image', $langcode);
        
        $data['signupnomember'] = $signupnomember;
        $data['signupnomemberGeo'] = $signupnomemberGeo;

        $data['send_datalayer'] = $session->get('send_datalayer');
        $session->set('send_datalayer','yes');
        $data['send_datalayer_optin_value'] = $session->get('send_datalayer_optin_value');
        $session->remove('send_datalayer_optin_value');

        $session->remove('signupnomember');
        $session->remove('signupnomemberGeo');
        
        
        //update to show the contest available for the current user
        $this->availableforuser = true; //to make sure have only contest available for users
        $this->getContestforLanding();
        $data['contestopened'] = $this->contestopened;
        //$data['contestclosed'] = $this->contestclosed;
        
        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($data['contestopened']);
        echo "</pre>";
        */


        return array(
            '#theme' => 'pepsicontest_thanks_template',
            '#data' => $data,
        );
    }
    
    
    public function rules($contest) {
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $langprefix = get_current_langcode(true);
        //Getting language and passing to twig
        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();

        //check if contest exists 
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
            
        }
        
        $node = $this->contestnode;
        
        $node->getTranslation($langcode);
        
        
        $data['body'] = $node->getTranslation($langcode)->field_contest_rules->value;
        $data['title'] = $node->getTranslation($langcode)->field_title->value;
        $data['brand'] = $node->getTranslation($langcode)->field_brand->value;
        $data['tag'] = $node->getTranslation($langcode)->field_contest_tag->value;
        $contest_type = $this->contestnode->field_contest_type->value;
        $data['contest_type'] = $contest_type;

        $data['back_button'] = "/" . $langprefix . "/contests/" . $contest . "/signup";

        return array(
            '#theme' => 'pepsicontest_rules_template',
            '#data' => $data,
        );
    }

    public function getContestRegSource($contest, $contest_type = 'regular'){
        $reg_source = 'tastyrewards';
        if ($contest_type == 'gameday'){
            $reg_source = 'gameday';
        }
        return $reg_source;
    }
    
    public function signup(Request $request, $contest) {
        
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
        //check if contest exists 
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
        }
        
        $node = $this->contestnode;
        $node->getTranslation($langcode);

        $contest_type = $node->field_contest_type->value;
        $lang_prefix = get_current_langcode(true);
        if ($contest_type == 'hockey') {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".custom.contest.landing", array('contest'=>$contest))->toString());
        }

        $contesturi_en = $this->contestnode->getTranslation($langcode)->field_contest_uri->value;

        $data['rules_link'] = "/" . $lang_prefix . "/contests/" . $contesturi_en . "/officialrules";

        $data['has_error'] = false;
        $data['subtitle'] = $node->getTranslation($langcode)->field_title->value;
        $data['body'] = $node->getTranslation($langcode)->body->value;
        $data['is_snackperk_user'] = 0;

        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;

        $contest_type = $node->getTranslation($langcode)->field_contest_type->value; 
        $data['contest_type'] = $contest_type;
        $reg_source = $this->getContestRegSource($contest, $contest_type);

        if ($contest_type == 'gameday'){
            $data['recipes'] = fetch_brand_recipes('gameday', $number = 6, 0, $langcode);
            $data['featured_recipes'] = get_featured_recipes('gameday', $image_detail = 0);
            $data['videos'] = get_recipes_with_videos('gameday');
            $data['team_options'] = $this->getFootballTeamOptions();

        }

        $base64_email = $request->get('snackperk_user');

        $base64_pfcid = $request->get('pfc_id');

        if (!empty($base64_pfcid) && $contest_type == 'exclusive'){
            try{
                // $pfc_id = base64_decode($base64_pfcid);
                // It looks like they didn't encode the pfc_id 
                $pfc_id = $base64_pfcid;
                
                $pfc_user_profile = $this->fetchPFCuserData($pfc_id);
                $post['email'] = $pfc_user_profile['email'];
                $post['firstname'] = $pfc_user_profile['firstname'];
                $post['lastname'] = $pfc_user_profile['lastname'];
                $post['gender'] = $pfc_user_profile['gender'];
                $post['postalcode'] = $pfc_user_profile['postalcode'];
                $dob = $pfc_user_profile['dob'];

                if (!empty($dob)){
                    $dob = str_replace(' 12:00AM', '', $dob);
                    if ((bool)strtotime($dob)){
                        $date = date_create_from_format('M j Y', $dob);
                        // Feb 24 1980 12:00AM
                        $birthday = date_format($date, 'Y-m-d');
                    }
                }

                if (isset($birthday) ){
                    $post['bday'] = $birthday;
                }
            } catch(\Exception $e){
                $pfc_id = '';
            }
        }
        

        if (!empty($base64_email)){
            $snackperk_email = base64_decode($base64_email);
            $query_result = find_snackperk_user($snackperk_email);
            if (!empty($query_result)) {
                $snackperk_userprofile = $query_result[0];
                // If the user had already created their password we should
                // skip this step and return the normal form
                $existing_user = user_load_by_mail($snackperk_userprofile->email);
                if (empty($existing_user)){
                    $data['is_snackperk_user'] = 1;
                    $this->gets_bonus_entries = true;
                    $post['email']          = $snackperk_userprofile->email;
                    $post['firstname']      = $snackperk_userprofile->firstname;
                    $post['lastname']       = $snackperk_userprofile->lastname;
                    $post['postalcode']     = $snackperk_userprofile->postalcode;
                    $post['bday']           = trim($snackperk_userprofile->bday);
                    \Drupal::logger("snackperk-contest")->info(" Contest form accessed by " . $post['email'],[]);
                } 
                unset($existing_user);
            }
        }
        
        $contest_id = $node->id();
        $openingdate = $node->getTranslation($langcode)->field_opening_date->date; 
        $closingdate = $node->getTranslation($langcode)->field_closing_date->date; 
        $contestentry = $node->getTranslation($langcode)->field_contest_entry->value; //1=> Only once, 2=> One per day
        
        $data['colorTheme'] = $node->getTranslation($langcode)->field_contest_color_theme->value; 
        $data['mobileColorTheme'] = $node->getTranslation($langcode)->field_contest_mobile_color_theme->value; 
        // $data['bannerTextPosition'] = $node->getTranslation($langcode)->field_contest_banner_position->value; 

        
        //Check if contest is still opened
        $status = $this->getContestStatus ($openingdate, $closingdate);
        
        
        //$status  3 = close, 2 - coming soon, 
        if ($status == 2 || $status == 3)  {
            //Have to redirecto to landing page with a anchor 
            return new RedirectResponse(\Drupal\Core\Url::fromRoute("pepsicontest.landing")->toString()."#$contest"); 
        }
        
        //Check if coming with a token from Marketo for bonus points
        $session = \Drupal::service('session');
        $mkcontest = $request->get('mkcontest');
        
        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($mkcontest);
        echo "</pre>";        
        */
        
        if ($mkcontest) {
            $session->set('mkcontest',$mkcontest);
        }
        
        $template = 'pepsicontest_signup_template';
        $alreadyregistered = false;
        $data['geotargeting'] = false;
        $data['clmn'] = $request->get('clmn');
        $data['logged_in'] = 0;
        $data['is_legal_age'] = 1;
        $data['allow_bday_entry'] = 0;
        //When there is a user's session 
        //Check if user is loged in
        if (!$user_id = \Drupal::currentUser()->id()) {
            $template = 'pepsicontest_register_template';
            //return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.register", array('contest'=>$contest))->toString()); 
        }
        else{
            //getting the logged user data
            $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
            $this->user =  $user;
            $data['logged_in'] = 1;
            $post['email']          = $user->get('mail')->value;
            $post['firstname']      = $user->get('field_firstname')->value;
            $post['lastname']       = $user->get('field_lastname')->value;
            $post['postalcode']     = $user->get('field_postalcode')->value;  

            $birthday    = $user->get('field_bday')->value;  
            $post['bday'] = $birthday;
            $today_minus_18y = date('Y-m-d', strtotime('-18 year'));
            if (!$birthday && $birthday > $today_minus_18y){
                $data['is_legal_age'] = 0;
            }
            if ($langcode == 'en-us' || $langcode == 'es-us'){
                if (!$post['bday']){
                    $data['allow_bday_entry'] = 1;
                }
            }


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
        
        
        $post['submitted'] = 0;
        //Check if this is coming from a POST
        if ($request->getMethod()=='POST'){
            $post['submitted'] = 1;
            //data validation
            $post['casl'] = $request->get('casl');
            $post['newregister'] = $request->get('newregister');
            
            
            $post['firstname'] = trim(isset($post['firstname'])?$post['firstname']:$request->request->get('firstname'));
            $post['lastname'] = trim(isset($post['lastname'])?$post['lastname']:$request->request->get('lastname'));
            $post['email'] = trim(strtolower(isset($post['email'])?$post['email']:$request->request->get('email')));
            // $post['confirm_email'] = trim(strtolower($request->request->get('confirm_email')));
            $post['password'] = $request->request->get('password');
            $post['confirm_password'] = $request->request->get('confirm_password');
            
            $post['postalcode'] = trim(strtoupper(!empty($post['postalcode'])?$post['postalcode']:$request->request->get('postalcode')));
            $post['postalcode'] = str_replace(' ', '', $post['postalcode']);
            
            $post['gender'] = $request->request->get('gender');
            $post['fbid'] = $request->request->get('fbid');
            //$optin_value = $request->request->get('optin') == 'on'? 1 : 0;
            $post['optin'] = in_array( $request->request->get('optin'), ['on', 1, '1'] )? 1 : 0; //get_current_langcode() == 'en-us'? 1 : $optin_value;
            if ( in_array($langcode, ['en-us', 'es-us'])){
                $post['optin_rules'] = in_array( $request->request->get('optin_rules'), ['on', 1, '1'] )? 1 : 0; 
            } 

            $post['csrfToken'] = $request->request->get('csrfToken');
            $post['grecaptcharesponse'] = $request->request->get('grecaptcharesponse_contest');
            $captcha = $request->request->get('g-recaptcha-response');
            if ($captcha){
                // debug_var($captcha, 3);
                $post['grecaptcharesponse'] = $captcha;
            }

            $post['captcha_score'] = ContestValidator::instance()->getCaptchaScore($post['grecaptcharesponse'] );

            $post['contest_id'] = $contest_id;
            
            
            
            $post['questionid'] = $request->get('questionid');
            $post['questiontype'] = $request->get('questiontype');
            $this->questiontype = !empty($post['questiontype'])? reset($post['questiontype']) : '';
            $post['answ'] = $request->get('answ');
            $post['contest_type'] = $request->get('contest_type');
            if ($post['contest_type'] == 'gameday'){
                $post['team'] = $request->get('team');
                $post['accept_rules'] = $request->get('accept_rules');
            }

      	$post['optin_majority'] = 0;
            $post['optin_parental'] = 0;
            if ($contest_type == 'exclusive'){
                $post['optin_majority'] = in_array( $request->request->get('optin_majority'), ['on', 1, '1'] )? 1 : 0; 
                $post['optin_parental'] = in_array( $request->request->get('optin_parental'), ['on', 1, '1'] )? 1 : 0; 
            }
            
            
            $post['bday_day']   = $request->request->get('bday_day')?$request->request->get('bday_day'):'';
            $post['bday_month'] = $request->request->get('bday_month')?$request->request->get('bday_month'):'';
            $post['bday_year']  = $request->request->get('bday_year')?$request->request->get('bday_year'):'';
            $post['bday']       = $post['bday_year'] . '-' . $post['bday_month'] . '-' . $post['bday_day'];
            $post['socialsource']  = $request->get('socialsource');
            $source = $this->validateSource('tastyrewards');

            $post['sourceid'] = $node->getTranslation($langcode)->field_source_id->value;
            // $post_all = $request->request->all();
            // 'g-recaptcha-response'
            
            if ($post['newregister']) { // signup + register into contest
                $errors = $this->validateRegistration($post,$new=true); 
                

                if (count($errors) == 0) {
                    //Process and register the contest
                    //create user
                    $saved = true;
                    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
                    $date = date('Y-m-d H:i:s');

                    ContestValidator::instance()->saveCaptchaScore($post);
                        // Call to OCH Okta
                        $sso_service = \Drupal::service('services.sso');

                        $post['city']  = $this->city;
                        $post['province'] = $this->province;
                        $post['zipcode']['main'] = $post['postalcode'];
                        $post['source'] = $source;
                        $post['ccid'] = $this->source['ccid'];
                        $post['ipaddress'] = getIPaddress();
                        $post['useragent'] = substr($_SERVER['HTTP_USER_AGENT'],0,255);
                        $post['date'] = $date;
                        $post['language'] = $language;

                        $okta_userid = $sso_service->CreateOchUser($post);
                        
                        if ($okta_userid) {
                            // Save in Drupal too, but with no password
                            $post['field_marketoid'] = $okta_userid;  // OCH Account ID Needed to get / update the OCH apicalls
                            // @TODO: if score is below 0.5, we should avoid creating the user?
                            // or perhaps delete the user afterwards
                            $user = CreateUserRecord($post);
                            $this->GenerateSaveActivationToken($user);

                            $this->user = $user; //setting user to class                            
                            $status = TRUE;
                        }
                        else{
                            $saved = false;
                            $errors['form'] = t('Oops, there is an issue. Please try again.');
                            $oktaerr = $sso_service->getErrorMsg();
                            $oktaerrkey = $sso_service->getErrorKey();
                            if (!empty($oktaerrkey) && $oktaerrkey == "userIdexists")
                            {
                                $path = Url::fromRoute('user.login')->toString();
                                $errors['email'] = t('You’ve already registered to Tasty Rewards.') . ' ' . t("Log in <a class='trigger_sign_in_popup contest-login' href='#'>here</a> to enter the contest!");
                            }
                            else{
                                if (!empty($oktaerr)){
                                    $errors['form'] = t('Oops, there is an issue. Please try again.');
                                }
                            }
                        }

                        if ($saved){

                            $session = \Drupal::service('session');
                            $session->set('signupnomember','1');
                            
                            //Check if Geo targetting contest 
                            if ($this->GeoTargetFilter()){
                                //register contest
                                $this->registerUser($node, $user, $post['answ'],1, $post['captcha_score']);
                            }
                            else{
                                $session->set('signupnomemberGeo','1'); // set seesion in order to show the right error message in thank you page
                            }
                            insert_ip_address(getIPaddress(), $post['email']);
                            $_langcode = get_current_langcode();
                            if ( in_array($_langcode, ['en-us', 'es-us'] )) {  
                                if ($this->syncSfmcUSA === true ){

                                    sfmcservices_subscribe($user, $source);
                                }
                            }
                            
                            $session->set('send_datalayer','no'); // set session in order to sending datalayer for tracking
                            $session->set('send_datalayer_optin_value',$post['optin']); // set session in order to sending datalayer for tracking
                            // insert_ip_address(getIPaddress(), $post['email']);

                            if ($contest_type != 'gameday'){
                                return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsicontest.contest.thank-you.new", array('contest'=>$contest))->toString());
                            } else{
                                $this->db_insert_extra_field($user, "team", $post['team']);
                            }
                            $data['success'] = 1;


                        }
                    /*}
                    else{
                        try 
                        {
                            $user_profile = find_snackperk_user($post['email']);
                            if (!empty($user_profile)){
                                // What if the user had already claimed their bonus?
                                $user = create_snackperk_user($user_profile,$source_id);
                                $this->is_snackperk_user = true;
                            
                            } else{
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
                                            'field_source' => $source,
                                            'field_ccid' => $this->source['ccid'],
                                            'field_ip_address' => getIPaddress(),
                                            'field_user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255),
                                            'field_source_id' => $post['sourceid']
                                            
                                ]);
                            }
                            $user->setPassword($post['password']);

                            $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

                            
                            $user->set("field_created_date", $date); 
                            if ($post['optin']) {
                                $user->set("field_optin_date", $date ); 
                                $user->set("field_optin_casl", $post['casl']); 
                            }

                            $user->set("langcode", $language);
                            $user->set("preferred_langcode", $language);
                            $user->set("preferred_admin_langcode", $language);
                            insert_ip_address(getIPaddress(), $post['email']);
                            $user->save();

                            $this->GenerateSaveActivationToken($user);

                            $this->user = $user; //setting user to class

                            

                        } catch (\Exception $e) {
                            $saved = false;
                            $errors['form'] = t('Oops, there is an issue. Please try again.');
                        }

                        if ($saved){


                            //Call Marketo subscribe
                            // MarketoSubscribe($user);
                            sfmcservices_subscribe($user, $reg_source);
                            //Call Activation Email call
                            // MarketoEmailActivation ($user, $resend = false); // Call Marketo API (create/update profile and send welcome-activation email)
    
                            sfmcservices_trigger_email_activation($user);
                            $session = \Drupal::service('session');
                            $session->set('signupnomember','1');
                            
                            //Check if Geo targetting contest 
                            if ($this->GeoTargetFilter()){
                                //register contest
                                $this->registerUser($node, $user, $post['answ'],1, $post['captcha_score']);
                            }
                            else{
                                $session->set('signupnomemberGeo','1'); // set seesion in order to show the right error message in thank you page
                            }
                            $session->set('send_datalayer','no'); // set session in order to sending datalayer for tracking
                            $session->set('send_datalayer_optin_value',$post['optin']); // set session in order to sending datalayer for tracking
                            if ($contest_type != 'gameday'){
                                return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsicontest.contest.thank-you.new", array('contest'=>$contest))->toString());
                            } else{
                                $this->db_insert_extra_field($user, "team", $post['team']);
                            }
                            $data['success'] = 1;
                        }

                    } */  

                }
                
            }
            else { // // only register to contest
                $errors = $this->validateRegistration($post,$new=false);   
                // @TODO: Also call inser_ip_address for users with account 
                if (count($errors) == 0) {
                    ContestValidator::instance()->saveCaptchaScore($post);
                    //Process and register the contest
                    //Save data in the user object
                    $user = $this->user;
                    $date = date('Y-m-d H:i:s');
                    if(isset($user)) {
                        
                    // $user->set('field_edit_date', $date);
                    $user->set('field_ip_address', getIPaddress());
                    $user->set('field_user_agent', substr($_SERVER['HTTP_USER_AGENT'],0,255));
                    $optin_changed = false;

                    if ($user->get('field_optin')->value != 1) {
                        if ($post['optin'])
                            $optin_changed = true;
                        
                        $user->set('field_optin', $post['optin']);
                        
                        if ($post['optin']) {
                            $user->set("field_optin_date", $date );
                            $user->set("field_optin_casl", $post['casl']);
                        }                        
                    }

                    $user->save();


                    //Call Marketo subscribe - 
                    // MarketoSubscribe($user);
                    if ($optin_changed){
                       // sfmcservices_subscribe($user, $reg_source); //Removed, now have to point to OCH
                        $langcode = get_current_langcode();
                        if ( in_array($langcode, ['en-us', 'es-us'] )) {  
                            if ($this->syncSfmcUSA === true ){
                                $source = $user->get('field_source')->value;
                                $source = empty($source)? 'tastyrewards': $source;

                                sfmcservices_subscribe($user, $source);
                            }
                        }
                    }

                    
                    // OCH update both CAN and US members
                    $sso_service = \Drupal::service('services.sso');
                    $ochuser =  $sso_service->UpdateOchUser($user);
                    if (!$ochuser){
                        $status = FALSE;
                        $errors['form'] = t('Oops, there is an issue. Please try again.');
                        $route = '';                        
                    }


                    $this->registerUser($node, $user, $post['answ']);
                    $session = \Drupal::service('session');
                    $session->remove('signupnomember');
                    $session->remove('signupnomemberGeo');

                    $session->set('send_datalayer','no'); // set session in order to sending datalayer for tracking
                    $session->set('send_datalayer_optin_value',$post['optin']); // set session in order to sending datalayer for tracking
                    if ($contest_type != 'gameday'){
                        return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".pepsicontest.contest.thank-you", array('contest'=>$contest))->toString() . "?contest=1"); 

                    }else{
                        $this->db_insert_extra_field($user, "team", $post['team']);
                    }
                    $data['success'] = 1;

                    }

                }                
            }
            
            $data['error'] = $errors;
            $data['has_error'] = count($errors) > 0? 1: 0;
        }


        
        
        $session = \Drupal::service('session');

        $email = $session->get('email');
        $fbid = $session->get('fbid');
        $firstname = $session->get('firstname');
        $lastname = $session->get('lastname');        
        
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
        

        $data['firstname']        = isset($post['firstname'])?$post['firstname']:'';
        $data['lastname']         = isset($post['lastname'])?$post['lastname']:'';
        $data['email']            = isset($post['email'])?$post['email']:'';
        $data['confirm_email']    = isset($post['confirm_email'])?$post['confirm_email']:'';
        $data['password']         = isset($post['password'])?$post['password']:'';
        $data['confirm_password'] = isset($post['confirm_password'])?$post['confirm_password']:'';
        $data['fbid']             = isset($post['fbid'])?$post['fbid']:'';
        
        $data['postalcode']       = isset($post['postalcode'])?$post['postalcode']:'';
        $data['gender']           = isset($post['gender'])?$post['gender']:'';
        $data['team']             = isset($post['team'])?$post['team']:'';
        $data['optin']            = isset($post['optin'])?$post['optin']:0;
        $data['optin_majority']   = isset($post['optin_majority'])?$post['optin_majority']:0;
        $data['optin_parental']   = isset($post['optin_parental'])?$post['optin_parental']:0;
        if ( in_array($langcode, ['en-us', 'es-us'])){
            $data['optin_rules']            = isset($post['optin_rules'])?$post['optin_rules']:0;
        }
        $data['submitted']        = isset($post['submitted'])?$post['submitted']:0;
        
        $data['bdaydropdown'] = $this->CreateDateDropdown(isset($post['bday'])?$post['bday']:null);
        if ($contest_type == 'gameday'){
            $data['bdaydropdown'] = $this->CreateDateDropdown(isset($post['bday'])?$post['bday']:null, 18);
        }


        $data['team_options'] = $this->getFootballTeamOptions(isset($post['team'])?$post['team']:null);
        
        
        //contest data
        $data['status'] = $status;
        $data['title'] = $node->getTranslation($langcode)->field_title->value;
        $data['brand'] = $node->getTranslation($langcode)->field_brand->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;
        $data['tag'] = $node->getTranslation($langcode)->field_contest_tag->value;
        
        
        $data['contesturi'] = $contest;
        
        
        $mobileimagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_header_image_mobile->target_id);
        $data['headermobileimage']  = $mobileimagefile->getFileUri();
        
        
        $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_header_image->target_id);
        $data['headerimage']  = $imagefile->getFileUri();
        $user = $this->user;
        if($user){
            $data['optin']  = isset($post['optin'])?$post['optin'] : $user->get('field_optin')->value;
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

        /*echo "<pre>";
           \Doctrine\Common\Util\Debug::dump($data['status']);
        echo "</pre>";
         * 
        */
        
        /** For testing Only, comment the below lines on prod.    ***/
        /*
         * $data['geotargeting'] = true; //true false
        $data['optin'] = 0; // 0, 1
        $data['status'] = 0; // 0 , 1, 4,  ( 0 =not registerdes), 1 = Already regitered but still can participate, 4= already Registered and can't participate at least today o no anymore depend of the type of contest
        $data['contestentry'] = 2; // 1, 2
         * 
         */
        /**For testing **/

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }


    public function favoriteTeam(Request $request){
        if ($request->query->has('email') ){
            $email = $request->query->get('email');
            $sql = "SELECT field_data, email, enterdate from pepsicontest_extra_field where field_name = 'team' AND email = '$email' ";

            $query = \Drupal::database()->query($sql);
            $results = $query->fetchAll();
            foreach($results as $result){
                echo "--TEAM : " . $result->field_data . "  , date: " . $result->enterdate . "\n";
            }
            debug_var("", 1);

        }
        die("email could not be found ");
        return;
    }

    public function fetchPFCuserData($pfc_id){
        if(filter_var($pfc_id, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT * from pfc_crosssell_users where email = '$pfc_id' ";
        } else {
            $sql = "SELECT * from pfc_crosssell_users where subscriber_key = '$pfc_id' ";
        }

        $query = \Drupal::database()->query($sql);
        $result = $query->fetchAll();
        $user_profile = [];
        if (!empty($result)){
            $obj = $result[0];
            $user_profile = [
                'email' => $obj->email,
                'firstname' => $obj->firstname,
                'lastname' => $obj->lastname,
                'gender' => $obj->gender,
                'dob' => $obj->dob,
                'postalcode' => $obj->zip_postal_code,
            ];
        }
        return $user_profile;

    }

    public function getFootballTeamOptions($selected_province = null) {
        $teams = [
            "Arizona Cardinals",
            "Atlanta Falcons",
            "Carolina Panthers",
            "Chicago Bears",
            "Dallas Cowboys",
            "Detroit Lions",
            "Green Bay Packers",
            "Los Angeles Rams",
            "Minnesota Vikings",
            "New Orleans Saints",
            "New York Giants",
            "Philadelphia Eagles",
            "San Francisco 49ers",
            "Seattle Seahawks",
            "Tampa Bay Buccaneers",
            "Washington Football Team",
            "Baltimore Ravens",
            "Buffalo Bills",
            "Cincinnati Bengals",
            "Cleveland Browns",
            "Denver Broncos",
            "Houston Texans",
            "Indianapolis Colts",
            "Jacksonville Jaguars",
            "Kansas City Chiefs",
            "Las Vegas Raiders",
            "Los Angeles Chargers",
            "Miami Dolphins",
            "New England Patriots",
            "New York Jets",
            "Pittsburgh Steelers",
            "Tennessee Titans",
        ];

        $options = [];
        foreach ($teams as $team) {
            $options[$team] = $team;
        }

        $province_option = '<option value="">' . t('Football Team') . '</option>';
        foreach ($options as $key => $province) {

            $tmp = ($selected_province == $key) ? 'selected' : '';
            $province_option = $province_option . '<option value="' . $key . '" ' . $tmp . ' >' . t($province) . '</option>';
        }



        return $province_option;
    }
    

    function getContestQuestion($node,$langcode){
        $questions = $node->getTranslation($langcode)->field_questions->referencedEntities() ; 
        
        $dataquestion = array();
        
        foreach ($questions as $question) {
            $questionid = $question->nid->value;
            $subject = $question->getTranslation($langcode)->field_subject->value;
            $type = \Drupal\taxonomy\Entity\Term::load($question->get('field_question_type')->target_id)->getName();
            $answers = $question->getTranslation($langcode)->field_answer->getValue();
            
            
            
            $dataquestion[] = array('questionid'=>$questionid, 'subject'=>$subject,'type'=>$type,'answers'=>$answers);
            
        }
        
        return $dataquestion;
        
    }
    
    function getOpenMicrositeContests($opened = true){
        $em = \Drupal::service('entity_type.manager');
        $date = date('Y-m-d\TH:i:s');
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        if ($opened) {
            $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                    ->condition('type', 'microsite_contest')
                    ->condition('field_opening_date', $date, '<=')
                    ->condition('field_closing_date', $date, '>=')
                    ->condition('langcode', $langcode)
                    ->sort('field_opening_date')
            ;
        } else { //search for comming soon
            $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                    ->condition('type', 'microsite_contest')
                    ->condition('langcode', $langcode)
                    ->sort('field_opening_date', 'DESC')
            ;
        }

        $nids = $query->execute();
        return $nids;
    }
 
    function getContestforLanding($opened = false) {
        $em = \Drupal::service('entity_type.manager');
        $date = date('Y-m-d\TH:i:s');
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        $env =  \Drupal\Core\Site\Settings::get("environment");
        if ($env == 'staging' || $env == 'dev'){
            // We don't have a cron on staging so we trigger 
            // the report on the contest landing page
            // \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("send_contest_optin_report", $hours = 12);
            
        }

        if ($opened) {
            $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                    ->condition('type', 'contest')
                    ->condition('field_opening_date', $date, '<=')
                    ->condition('field_closing_date', $date, '>=')
                    ->condition('field_contest_type', 'exclusive', '<>')
                    ->condition('field_contest_type', 'test_contest', '<>')
                    ->condition('langcode', $langcode)
                    ->sort('field_opening_date')
            ;
        } else { //search for comming soon
            $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                    ->condition('type', 'contest')
                    ->condition('field_contest_type', 'exclusive', '<>')
                    ->condition('field_contest_type', 'test_contest', '<>')
                    ->condition('langcode', $langcode)
                    ->sort('field_opening_date', 'DESC')
            ;
        }

        $nids = $query->execute();

        // Add microsite contests 
        $nids = array_merge($nids, $this->getOpenMicrositeContests());
         /*echo "<pre>";
          \Doctrine\Common\Util\Debug::dump($nids);
          echo "</pre>";
          exit;*/
        $contests = [];
        $contests1 = [];
        $contests2 = [];
        $this->contestopened = array();
        $this->contestclosed = array();

        $defaultimg = '';



        if (count($nids) == 0) {
            $contests[] = array(
                'opened' => 0, //No contest
                'title' => t('Contest coming soon!'), //Default message when there is no contest
                'body' => '',
                'summarybody' => '',
                'brand' => "Tastyrewards",
                'image' => $defaultimg,
                'footer' => '',
                'uricontest' => '',
                'description' => '',
                'registered' => false,
                'registeragain' => false,
                'contesttype' => null,
                'winners' => '',
            );
            
            return true;
        }

        /* echo "<pre>";
          \Doctrine\Common\Util\Debug::dump($node);
          echo "</pre>";
         */
        $user_id = (int) \Drupal::currentUser()->id();
        
            
        foreach ($nids as $nid) {
            $node = \Drupal\node\Entity\Node::load($nid);


            $node->getTranslation($langcode);


            // $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_landing_image->target_id);
            // $imagepath = $imagefile->getFileUri();
            
            $imagepath = get_translated_image_url($node,'field_landing_image', $langcode);

            //Checking opening Status
            $openingdate = $node->getTranslation($langcode)->field_opening_date->date;
            $closingdate = $node->getTranslation($langcode)->field_closing_date->date;
            $contestentry = $node->getTranslation($langcode)->field_contest_entry->value; //1=> Only once, 2=> One per day

            $opened = $this->getContestStatus ($openingdate, $closingdate);


            //Cheking if user already registed if user if logged
            $registered = false;
            $registeragain = false;
            
                    
            if ($user_id > 0) {
                //check in contests table is already exist  
                
                if ($this->isUserRegistered($nid,$user_id,$contestentry)) {
                     $registered = true;
                }
            }
            
            if ($this->availableforuser && $registered && !$this->registeragain ) {
                // skip the next contest item
                continue;
            }
            
            $contest_type = $node->getTranslation($langcode)->field_contest_type->value; 
            $uricontest = \Drupal\Core\Url::fromRoute($lang_url .".pepsicontest.contest.signup", array('contest'=>$node->getTranslation($langcode)->field_contest_uri->value))->toString();
            if ($node->getType() == 'microsite_contest'){
                $uricontest = \Drupal\Core\Url::fromRoute($lang_url .".custom.contest.signup", array('contest'=>$node->getTranslation($langcode)->field_contest_uri->value))->toString();
            } elseif($contest_type === 'quaker' || $contest_type === 'lays'){
                $uricontest = \Drupal\Core\Url::fromRoute($lang_url .".custom-quaker.contest.signup", array('contest'=>$node->getTranslation($langcode)->field_contest_uri->value))->toString();
            } elseif(in_array($contest_type, ['nintendo', 'nintendo2', 'grabsnack', 'cheetos', 'hockey'])){
                $uricontest = \Drupal\Core\Url::fromRoute($lang_url .".custom.contest.landing", array('contest'=>$node->getTranslation($langcode)->field_contest_uri->value))->toString();

            }

            $contest_machinename = $node->getTranslation($langcode)->field_contest_uri->value;

            
            if ($opened == 1) { //If opened, has to be in the top of the array
                $contests1[] = array(
                    'opened' => $opened,
                    'title' => $node->getTranslation($langcode)->field_title->value,
                    'body' => $node->getTranslation($langcode)->body->value,
                    'summarybody' => $node->getTranslation($langcode)->body->summary,
                    'brand' => $node->getTranslation($langcode)->field_brand->value,
                    'image' => $imagepath, //$url,
                    'footer' => $node->getTranslation($langcode)->field_legal_footer->value,
                    'uricontest' => $uricontest,
                    'contestname' => $contest_machinename,
                    'description' => $node->getTranslation($langcode)->body->value,
                    'registered' => $registered,
                    'registeragain' => $this->registeragain,
                    'contesttype' => $contestentry,
                    'winners' => $node->getTranslation($langcode)->field_winners->value,
                );
            }
            elseif ($opened == 2) { //If comming soon, has to be under "contest opened"
                $contests2[] = array(
                    'opened' => $opened,
                    'title' => $node->getTranslation($langcode)->field_title->value,
                    'body' => $node->getTranslation($langcode)->body->value,
                    'summarybody' => $node->getTranslation($langcode)->body->summary,
                    'brand' => $node->getTranslation($langcode)->field_brand->value,
                    'image' => $imagepath, //$url,
                    'footer' => $node->getTranslation($langcode)->field_legal_footer->value,
                    'uricontest' => $uricontest,
                    'contestname' => $contest_machinename,
                    'description' => $node->getTranslation($langcode)->body->value,
                    'registered' => $registered,
                    'registeragain' => $this->registeragain,
                    'contesttype' => $contestentry,
                    'winners' => $node->getTranslation($langcode)->field_winners->value,
                );
            }
            else {
                $contests[] = array(
                    'opened' => $opened,
                    'title' => $node->getTranslation($langcode)->field_title->value,
                    'body' => $node->getTranslation($langcode)->body->value,
                    'summarybody' => $node->getTranslation($langcode)->body->summary,
                    'brand' => $node->getTranslation($langcode)->field_brand->value,
                    'image' => $imagepath, //$url,
                    'footer' => $node->getTranslation($langcode)->field_legal_footer->value,
                    'uricontest' => $uricontest,
                    'contestname' => $contest_machinename,
                    'description' => $node->getTranslation($langcode)->body->value,
                    'registered' => $registered,
                    'registeragain' => $this->registeragain,
                    'contesttype' => $contestentry,
                    'winners' => $node->getTranslation($langcode)->field_winners->value,
                );
            }
         
        }
        // order the array based on the opening status, the opening ones (opened = 0) have to be in the top
        
        
        $this->contestopened = array_merge ($contests1,$contests2);
        $this->contestclosed = $contests;
        
        //return array('opened'=>$contestsopened,'closed'=>$contestsclosed);
        return true;
    }
    
    function compare_openedstatus($a, $b)
    {
        return strnatcmp($a['opened'], $b['opened']);
    }
    
    
    function getContestStatus ($openingdate, $closingdate) {
        //$date = date('Y-m-d\TH:i:s');
        //$openingdate->setTimezone(new DateTimeZone('America/New York'));
        //$date = \DateTime::createFromFormat("Y-m-d H:i:s", date('Y-m-d H:i:s'));
        $date = DrupalDateTime::createFromFormat("Y-m-d H:i:s", date('Y-m-d H:i:s'), 'America/New_York');
        $openingdate->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $closingdate->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        
                
        $opened = 0;
        if ($date >= $openingdate && $date <= $closingdate) {
                $opened = 1; //openned
        } elseif ($openingdate > $date) {
                $opened = 2; //coming soon
        } elseif ($closingdate < $date) {
                $opened = 3; //Closed
        }
        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($date->__toString());
        \Doctrine\Common\Util\Debug::dump($openingdate->__toString());
        \Doctrine\Common\Util\Debug::dump($closingdate->__toString());
        echo "</pre>";
        */
        return $opened;
    }
    
    function isUserRegistered ($contest_id, $user_id, $contestentry){
        
        $this->registeragain = false;
        if ($contestentry == '7') {
            $last_sunday = date('Y-m-d',strtotime('last sunday'));
            $sql = "SELECT count(*) as cnt from pepsicontest_reg_contest 
            WHERE contest_id = $contest_id and user_id = $user_id and enterdate > '$last_sunday' ";
            $query = \Drupal::database()->query($sql);
            $result = $query->fetchAll();
            $count = !empty($result) ? $result[0]->cnt : 0;
            if ( $count < 7) {
                $this->registeragain = true;
                return true;
            }

        } else {
            $query = \Drupal::database()->select('pepsicontest_reg_contest', 'nfd');
                    $query->addField('nfd', 'contest_id');
                    $query->addField('nfd', 'enterdate');
                    $query->condition('nfd.contest_id', $contest_id);
                    $query->condition('nfd.user_id', $user_id);
                    $query->orderBy('nfd.enterdate', 'DESC');
                    $query->range(0, 1);
                    
            $registered = $query->execute()->fetch();
        }
        $date = date('Y-m-d');
        
        if ($registered) {
            if ($contestentry == '2' && $date != $registered->enterdate ) { // One per contest
                $this->registeragain = true;
            }
            /*echo "<pre>";
            \Doctrine\Common\Util\Debug::dump($contestentry);
            \Doctrine\Common\Util\Debug::dump($this->registeragain);
            echo "</pre>";        
           //exit;*/
            return true;
        }
        
        return false;
    }
    
    function registerUser ($node, $user, $subjectanswers, $nomember = 0, $captcha_score = null){
        // if (is_numeric($captcha_score) && floatval($captcha_score) <= 0.5 ){
        //     $result = ContestValidator::instance()->sendToSpamEntries($node, $user, $nomember);
        //     return $result;
        // }
        
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

        if (\Drupal::database()->schema()->tableExists('pepsicontest_reg_contest')) {

            //check if the user registered at the same date
            $check_existing_query = \Drupal::database()->select('pepsicontest_reg_contest', 'nfd');
            $check_existing_query->addField('nfd', 'contest_id');
            $check_existing_query->addField('nfd', 'enterdate');
            $check_existing_query->condition('nfd.contest_id', $node->id());
            $check_existing_query->condition('nfd.user_id', $user->id());
            $check_existing_query->orderBy('nfd.enterdate', 'DESC');
            $check_existing_query->range(0, 1);
            $registered = $check_existing_query->execute()->fetch();
            $date = date('Y-m-d');
            $registered_same_date = false;
            if ($registered && $date==$registered->enterdate) {
                $registered_same_date = true;
            }
            if(!$registered_same_date){
                $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
            }
            
            if ($this->multiple_entries)
                $this->addAdditionalEntries($usercontest, $user->get('field_marketoid')->value);

            if ($this->gets_bonus_entries)
                $this->addBonusEntries($usercontest);
        }
        
        $toMarketoArray = [];
        
        if (!$this->registeragain) {
            if (\Drupal::database()->schema()->tableExists('pepsicontest_reg_answer')) {
                if (is_array($subjectanswers)) {
                    // check if multiple choice
                    
                    foreach ($subjectanswers as $questionid => $answers) {

                        $nodeQuestion = \Drupal\node\Entity\Node::load($questionid);
                        $questionText = $nodeQuestion->getTranslation('en')->field_subject->value;
                        $answersText = $nodeQuestion->getTranslation('en')->field_answer->getValue();
                        foreach ($answers as $answer_id => $answer) {
                            $useranswer = ['contest_id' => $node->id(), 'user_id' => $user->id(),
                             'question_id' => $questionid, 
                             'answer' => $this->questiontype == 'MultipleChoice'? $answer_id : $answer];
                            $result = \Drupal::database()->insert('pepsicontest_reg_answer')->fields($useranswer)->execute();

                            $toMarketoArray[] = ["leadID" => $user->get('field_marketoid')->value
                                , "contestID" => $node->id()
                                , "contestText" => $node->getTranslation('en')->field_title->value
                                , "questionID" => $questionid
                                , "questionText" => $questionText
                                , "answerID" => $this->questiontype == 'MultipleChoice'? $answer_id : $answer
                                , "answerText" => $answersText[$answer_id]['value']
                                , "language" => $langcode
                            ];
                        }
                    }
                } else {
                    //If Contest doesn't have suvey
                    $toMarketoArray[] = ["leadID" => $user->get('field_marketoid')->value
                                , "contestID" => $node->id()
                                , "contestText" => $node->getTranslation($langcode)->field_title->value
                                , "questionID" => 0
                                , "questionText" => ''
                                , "answerID" => 0
                                , "answerText" => ''
                                , "language" => $langcode
                            ];
                }
            }
            

            //Saving Contest into Marketo
            // marketoSavePoll($toMarketoArray, $user->get('field_marketoid')->value , $objectname = 'marketoDrupalLeadContest_c') ;
        }
        
        //Adding Call for Sync Marketing Activities
        $campaignId = $node->getTranslation($langcode)->field_mkt_activity_code->value;
        if ($campaignId && $campaignId > '') MarketoSyncAndEmail($user,$campaignId, $resend = false);
        
        return false;
    }
    
    function addBonusEntries($usercontest){
        $n = 5;
        $success = true;
        for ($x = 1; $x <= $n; $x++) {
            $usercontest['bonus'] = $x;
            try{
                $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
            } catch( \Exception $e){
                \Drupal::logger("custom-modules")->info(" Bonus entries for snackperk_user could not be saved", []);
                $success = false;
            }
        }

        if ($success){
            $email = $usercontest['email'];
            $sql = "UPDATE csv_users_imported SET bonus = $n WHERE email like '$email' ";

            try{
                $select = \Drupal::database()->query($sql);
                $result = $select->execute();
            } catch (\Exception $e) { 
                $channel = "custom-module";
                $message = " csv redundancy SQL query error ";
                $context = [ ];
                \Drupal::logger($channel)->info($message, $context);
                
                //return null;
            }
        }

    }

    function addAdditionalEntries($usercontest, $marketoId){
        
        $n = 5;
        //check if exists token in memory and is valid and not used.
        $session = \Drupal::service('session');
        $mkcontest = $session->get('mkcontest');
        // if ($mkcontest == null) {
        if (empty($mkcontest)) {

            return false;
        }
        if ($usercontest['email'] != base64_decode($mkcontest)){
            return false;
        }
        // @TODO: Check if the user has already had bonus on this contest
        // Insert N times into the contest (bonus)
        for ($x = 1; $x <= $n; $x++) {
            $usercontest['bonus'] = $x;
            $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
        }
        
        // if (\Drupal::database()->schema()->tableExists('marketo_token')) {
        //     $check_existing_query = \Drupal::database()->select('marketo_token', 'mt');
        //     $check_existing_query->addField('mt', 'marketoid');
            
            
        //     // Insert N times into the contest (bonus)
        //     for ($x = 1; $x <= $n; $x++) {
        //         $usercontest['bonus'] = $x;
        //         $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
        //     }
        //     //updating the token as used and remove from session.
        //     $session->remove('mkcontest');
            
        //     $check_existing_query = \Drupal::database()->update('marketo_token')->fields(['status' => "1"]);
        //     $check_existing_query->condition('token', $mkcontest);
        //     $token_exist = $check_existing_query->execute();
            

        // } else {
           
        // }
        
    }
    

    function getContestId ($contest){
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
        $em = \Drupal::service('entity_type.manager');
        
        $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                ->condition('type', 'contest')
                ->condition('field_contest_uri', $contest, '=')
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

    public function validateRegistration($post,$new) {
        //Check the CsrfToken
        $error = array();
        $user = $this->user;
        $langcode = get_current_langcode();
        $max_entries_per_ip = 30;
        
        
        // if(!$this->CheckCsrfToken($post['csrfToken'])) {
        //     $error['form'] = t('Oops, there is an issue. Please try again.');
        //     return $error;
        // }

        if ($new) {

            //firstname val
            if (!$this->userNamesValidation($post['firstname']))
                $error['firstname'] = t('Please enter a valid first name.');

            //lastname val
            if (!$this->userNamesValidation($post['lastname']))
                $error['lastname'] = t('Please enter a valid last name.');

            //postalcode
            //if (!preg_match('/^[a-zA-Z0-9]{3}( )?[a-zA-Z0-9]{3}$/', trim($post['postalcode'])))
            if (!$this->postalCodeValidation($post['postalcode']))                
                $error['postalcode'] = t('Please enter a valid postal code.');
            else {
                //gettting City and province
                $this->searchProvince ($post['postalcode']);
            }

            //gender
            $genders = array("M", "F", "O");
            // Gender is no longer a mandatory field
           // if (!in_array($post['gender'], $genders))
            //    $error['gender'] = t('Please enter a valid gender');

            //birthdate
            $dt = \DateTime::createFromFormat("Y-m-d", $post['bday']);
            
            if ($dt && array_sum($dt->getLastErrors()) == 0) {
                if (!$this->ValidateAge($dt, $this->province))
                    $error['bday'] = t('Sorry! You must be 13+ to sign up.');
            } else
                $error['bday'] = t('Please enter a valid birthdate.');


            /*
            //Validate Email  CANADA - OKTA
            if (($langcode == 'en-ca' || $langcode == 'fr-ca')) {
                // Validated in OKTA side
                if(!validate_ip_address(getIPaddress(), $post['email'], $max_entries_per_ip ) ){
                    $error['email'] = t('You cannot enter this contest');
                }
            }
            else{
                if (!$this->EmailValidation($post['email']))
                    $error['email'] = t('Please enter a valid email');
                // This validation step is removed for the new site
                // elseif (trim(strtolower($post['email'])) != trim(strtolower($post['confirm_email'])))
                //     $error['confirm_email'] = t("Emails do not match.");
                elseif ($this->UserRegistered($post['email'])) {
                    //$path = Url::fromRoute('user.login')->toString();
                    //$error['email'] = t('You’ve already registered to Tasty Rewards.') . ' ' . t("Log in <a class='contest-login' href=':loginpath'>here</a> to enter the contest!",array(':loginpath' => $path ));
                    $errors['email'] = t('You’ve already registered to Tasty Rewards.') . ' ' . t("Log in <a class='trigger_sign_in_popup contest-login' href='#'>here</a> to enter the contest!");
                }
                elseif(!validate_ip_address(getIPaddress(), $post['email'], $max_entries_per_ip) ){
                    $error['email'] = t('You cannot enter this contest');
                }
                // elseif($post['contest_type'] == 'gameday'){
                //     $error['email'] = t('You’ve already registered to Tasty Rewards.') . ' ' . t("Signup <a class='contest-login js-tr-signup' href='javascript:void(0);'>here</a> to enter the contest!");
                // }
            }
            */

            
            if ( empty($post['socialsource']) ){ // For social like "GOOGLE", then user doesn't need the password, only No social need the password
                //password 
                if (!$this->PasswordFormatValidation($post['password'])  )
                    $error['password'] = t('Please enter a valid password');
            }


            //captcha
            // This validation step is removed for the new site
            // debug_var($post,1);
            /*
            if (!RecaptchaValidation($post['grecaptcharesponse'])){
                 $error['grecaptcharesponse'] = t('Recaptcha Required.');
            }
            */

        }

        //Evaluate the bday for the ones did not enter previusly.

        // $bday = $user->get('field_bday')->value;  
        // I changed this because $user is NULL when $new == 1
        $bday = $post['bday'];

        //birthdate
        if (!$bday){
            $dt = \DateTime::createFromFormat("Y-m-d", $post['bday']);
                
            if ($dt && array_sum($dt->getLastErrors()) == 0) {
                if (!$this->ValidateAge($dt, $this->province))
                    $error['bday'] = t('Sorry! You must be 13+ to sign up.');
            } else
                $error['bday'] = t('Please enter a valid birthdate.');
        }


        if ($post['contest_type'] == 'gameday'){
            if (empty($post['team'])){
                $error['team'] = t('Please enter football team');
            }
            if (empty($post['accept_rules'])){
                $error['accept_rules'] = t('Required field');
            }

        }

        if ( in_array($langcode, ['en-us', 'es-us'])){
            if (empty($post['optin_rules'])){
                $error['optin_rules'] = t('Required field');
            }
        }


        if ($post['contest_type'] == 'exclusive'){
            if (empty($post['optin_majority']) && empty($post['optin_parental']) ){
                $error['optin_majority'] = t('Required field');
            }
            $is_pfc_user = $this->find_pfc_user($post['email']);
            if (!$is_pfc_user){
                $error['email'] = t('You cannot enter this contest');
            } elseif ($this->hasEnteredContest($post['email'], $this->contestnode->id() ) ){
                $error['email'] = t('You have already entered this contest');
            }
        }
        
        if (!$this->registeragain) { //question validation would be only for first registration
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

        return $error;
    }    
    

    public function CheckCsrfToken($csrfToken) {
        $session = \Drupal::service('session');
        $csrfToken_real = $session->get('csrfToken');
        
        if (trim($csrfToken) == '') return false;
        
        if ($csrfToken_real === $csrfToken ) 
            return true;
        
        return false;
    }

    public function find_pfc_user($email){
        $sql = "SELECT * from pfc_crosssell_users where email = '$email' ";
        $query = \Drupal::database()->query($sql);
        $result = $query->fetchAll();
        return empty($result)? 0 : 1;
    }

    public function hasEnteredContest($email, $contestid){
        $sql = "SELECT * from pepsicontest_reg_contest where email = '$email' and contest_id = $contestid ";
        $query = \Drupal::database()->query($sql);
        $result = $query->fetchAll();
        return empty($result)? 0 : 1;

    }

    public function PasswordFormatValidation($password){
      //if (!preg_match('#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#',$password ))  return false;
      if (!preg_match('#.*^(?=.{8,16})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#',$password ))  return false;
        return true;
    }
    
    public function userNamesValidation($name){
      if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u",$name ))  return false;
        return true;
    }
    
    public function postalCodeValidation($postalcode){
        $langcode = get_current_langcode();
        $postalcode = strtoupper($postalcode);

        if ( ($langcode == 'en-ca' || $langcode == 'fr-ca') && !preg_match("/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/",$postalcode ))  return false;
        if (($langcode == 'en-us' || $langcode == 'es-us') && !preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$postalcode))  return false;

        return true;
    }

    public function phoneNumberValidation($phone){
        $regx = '/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/';
        if ( preg_match($regx,$phone ) ) {
            return true;
        } 
        
        return false;
    }
    
    public function EmailValidation($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '|') !== false || strpos($email, ' ') !== false )
            return false;

        // Invalidate spammy emails
        if ( strpos($email, "@commuccast.com") !== false ||
             strpos($email, "@distromtl.com") !== false ||
             strpos($email, "@ispqc.com") !== false ||
             strpos($email, "@xferdirect.com") !== false 
        ){
            return false;
        }

        
        return true;
    }

    public function UserRegistered($email) {
        if ($user = user_load_by_mail($email))
            return $user;
        return false;
    }

    
    public function UserbyToken($token) {
        if ($user = user_load_by_mail($email))
            return $user;
        return false;
    }
    
    
    public function UserFieldsMarketo($post) {
        return array('email' => $post['email'],
            'firstName' => $post['firstname'],
        );
    }

    public function db_insert_extra_field($user, $field_name, $field_value){
        $sql = "INSERT INTO pepsicontest_extra_field (contest_id, user_id, field_name, field_data, enterdate, email)
        VALUES ('" . $this->contestnode->id() . "', '" . $user->id() . "', '$field_name', '" . $field_value. "' ,
          '" . date("Y-m-d"). "',  '" . $user->mail->value. "' );";
        try{
            $select = \Drupal::database()->query($sql);
            $result = $select->execute();
        } catch (\Exception $e) {
            $channel = "custom-module";
            $message = " Could not insert Lays banner field into Database ";
            $context = [ ];
            \Drupal::logger($channel)->info($message, $context);

            return null;
        }
        return true;
    }

    function searchProvince ($postalcode) {
                
        //get Province and City from 'bampostalcode' module
        $pcodearr = bampostalcode_getProvCity($postalcode);
                
        if ($pcodearr) {
            $city = $pcodearr['city'];
            $province = $pcodearr['province_abbr'];
        }
        else {
            $city = 'NA';
            $province = getProvinceFromPCode($postalcode);
            
        }
        
        $this->city = $city;
        $this->province = $province;
    }

    function ValidateAge($bday, $province) {
        

        $today = \DateTime::createFromFormat("Y-m-d", date('Y-m-d'));
        $diff = $today->diff($bday);

        $MajorityAgeArr = ['AB' => 18,
            'BC' => 19,
            'MB' => 18,
            'NB' => 18,
            'NL' => 19,
            'NT' => 19,
            'NS' => 19,
            'NU' => 19,
            'ON' => 18,
            'PE' => 18,
            'QC' => 18,
            'SK' => 18,
            'YT' => 19];

        //$MajorityAge = isset($MajorityAgeArr[$province]) ? $MajorityAgeArr[$province] : 19;

        $MajorityAge = 13; // 
        
        $months = ($diff->y * 12 + $diff->m);
        $monthlimit = $MajorityAge * 12; //years minimum
        
        
        if ($months < $monthlimit)
            return false;
        return true;
    }    
    
    function ContestFieldsMarketo($contest, $user_mk_id) { 
            $m_poll = [ [
                    "leadID" => $user_mk_id,
                    "questionID" => $poll["pid"],
                    "questionText" => $poll["question"],
                    "answerID" => $poll["chid"], 
                    "answerText" => $poll["choice"], 
                    "language" => \Drupal::languageManager()->getCurrentLanguage()->getId() 

            ]];

       return $m_poll;
    }
    
    function GeoTargetFilter(){
        
        //Getting the Geotargetting setting from contest 
        $geotargeting = $this->contestnode->get('field_geo_targeting')->value;
        $is_qc = $this->isQC();

        if ($geotargeting == '1' && $is_qc  ) { //Exclude Quebec
            return false;
        }
        if ($geotargeting == '2' && !$is_qc  ) { //Quebec Only
            return false;
        }
        return true;
    }
    
    function isQC(){
        //Getting province from user
        $province = $this->user->get('field_province')->value;
        if ($province == 'NA'){
            //Fix NA
            $province = getProvinceFromPCode($this->user->get('field_postalcode')->value);
            $this->user->set('field_province', $province);
            $this->user->save();
        }
        
        if ($province == 'QC'){
            return true;
        }
        return false;
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
    
    function CreateDateDropdown($date = null, $age_limit = 13) {
        
        $dd = 0;
        $mm = 0;
        $yy = 0;
        
        $months = [t("January"), t("February"), t("March"), t("April"), t("May"), t("June"), t("July"), t("August"), t("September"), t("October"), t("November"), t("December")];
        
        if ($date) {
           $newdate = \DateTime::createFromFormat("Y-m-d", $date);
           if ($newdate && array_sum($newdate->getLastErrors()) == 0) {
                $dd = $newdate->format("d");
                $mm = $newdate->format("m");
                $yy = $newdate->format("Y");
           }
        }   
        
        //dropdown day
        $dayoption = '<option value="">' . t('Day') . '</option>';
        for ($i = 1; $i <= 31; $i++) {
            $tmp = ($i==$dd)?'selected':'';
            $dayoption  = $dayoption . '<option value="'.  substr('0'.$i , -2) . '"' . $tmp . '>' . $i . '</option>';
        }
        
        //dropdown month
        $monthoption = '<option value="">' . t('Month') . '</option>';
        for ($i = 1; $i <= 12; $i++) {
            $tmp = ($i==$mm)?'selected':'';
            $monthoption = $monthoption . '<option value="'.substr('0'.$i , -2) . '"' . $tmp . '>' . $months[$i-1] . '</option>';
        }
        
        //dropdown year
        $yearoption = '<option value="">' . t('Year') . '</option>';
        $currentyear = date("Y") - $age_limit;
        for ($i = $currentyear; $i > $currentyear - 100 ; $i--) {
            $tmp = ($i==$yy)?'selected':'';
            $yearoption = $yearoption . '<option value="'.$i . '"' . $tmp . '>' . $i . '</option>';
        }
        
        return  array('dayoption' => $dayoption, 'monthoption' => $monthoption, 'yearoption' => $yearoption);
        
    }

    public function surveyRules(Request $request){
        $data = [];
        $langcode = get_current_langcode();

        $data['rules'] = get_block_content('tastyrewards', 'terms');
        // $data['carousel'] = BrandsContentFilters::instance()->fetch_brand_slides($brand = "Tastyrewards", "rules");
        
        $template = 'survey_contest_rules';

        if ($langcode !== 'en-ca')
            return new RedirectResponse(\Drupal\Core\Url::fromRoute('<front>', [])->toString()); 

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }    

    function validateSource($source){
        
       if(!$this->getRegSource($source)) {
                $source = 'tastyrewards';
                $this->getRegSource($source);
       }
       return $source;
    }
    
    function getRegSource($source){
        $regsource = \Drupal::service('entity_type.manager');
        $query = $regsource->getStorage('node')->getQuery()->condition('status', 1)->condition('type', 'registration_source')->condition('field_source_uri', $source, '=');
        $nids = $query->execute();
            
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
        if (count($nids) == 0){
            return false;
        }
        
        
        $node = \Drupal\node\Entity\Node::load(reset($nids));
        if ($node){
            $node->getTranslation($langcode);
            $title_field = $node->getTranslation($langcode)->field_ccid->value;

            $sourcearr = array('source_uri'=>$node->getTranslation($langcode)->field_source_uri->value,
                               'ccid'=>$node->getTranslation($langcode)->field_ccid->value,
                               'source_domain'=>$node->getTranslation($langcode)->field_source_domain->value
                );
            $this->source = $sourcearr;
            return true;
        }
        return false;
    }

    public function GenerateSaveActivationToken(&$user) {

        //Generate Unique Url
                
        $hashed_id = hash('crc32',$user->id());
        $date = date('Y-m-d H:i:s');
        $code = base64_encode($date . '|' .$hashed_id);
        $url = Url::fromRoute('pepsibam.accountactivation', ['token'=>$code],['absolute'=>true ] );
                    
        $user->set('field_activationurl', $url->toString()); //Unsubscribe absolute URL
        $user->set('field_activationtoken', $hashed_id); //Token
                    
        $user->save();
        
    }

    public function activeContests(Request $request) {
        // url =  /contests-current
        $langcode =  \Drupal::languageManager()->getCurrentLanguage()->getId();
        $contest_resource = get_active_contests($langcode, $with_param = false);
          // $variables['legal_footer'] = $contest_resource['legal_footer'];
        $redirectTo = $contest_resource['contest_url'];
        $query_string = "?";
        if (isset($_SERVER['QUERY_STRING'])){
            parse_str($_SERVER['QUERY_STRING'], $url_parameters); 
            foreach($url_parameters as $key => $val){
                if ($key == 'email')
                    $key = 'mkcontest';

                $query_string .= $key . "=" . $val . "&";
            }
        }

        $query_string = substr_replace($query_string, "", -1);
        $response = new RedirectResponse($redirectTo . $query_string);
        $response->send();
        return $response;

    }

    public function contestAdmin(Request $request){
        $template = "contest_admin";
        $data = [];
        $status = '';
        $data['title'] = "Contest Admin";

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    public function drawWinner(Request $request){
        $template = "contest_draw";
        $data = [];
        $status = '';
        $data['title'] = "Contest Draw";

        $data['nofilter'] = 1;
        $post = [];

        //Check if this is coming from a POST
        if ($request->getMethod() == 'POST'){
            $contest_id = $request->request->get('contestid');
            $number = $request->request->get('number');
            $start_date = $request->request->get('start_date');
            $end_date = $request->request->get('end_date');
            $data['number'] = $number;
            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;

            $data['winners'] = $this->drawRandomWinners($contest_id, $start_date, $end_date, $number);
            $post['contestid'] = $request->get('contestid');
            $contest_node = \Drupal\node\Entity\Node::load($contest_id);
            $contest_type = $contest_node->field_contest_type->value;
            if ($contest_type == 'gameday'){
                // Get football team
                $data['winners'] = $this->addFootballTeam($data['winners']);
            }
        }
        // debug_var($data, 3);

        $data['all_contests'] = $this->getContestFilter();
        $email = "";

        if (!empty($contest_id)){
            $data['nofilter'] = 0;
            // $email = $request->get('email');
        } else {
            $contest_id = 0;
        }

        $data['contestid'] = $contest_id;

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    public function addFootballTeam($winners){
        foreach ($winners as $key => $obj) {
            $email =  $obj->email;
            // $sql = "SELECT * FROM  `pepsicontest_extra_field` ";
            $query = \Drupal::database()->select('pepsicontest_extra_field', 'nfd');
            $query->addField('nfd', 'field_data');
            $query->addField('nfd', 'field_name');
            $query->addField('nfd', 'email');
            $query->condition('nfd.email', $email);

            $query->range(0, 1);
                
            $result = $query->execute()->fetchAll();
            if (!empty($result)){
                $team = $result[0]->field_data;
                $winners[$key]->team = $team;
            }

        }
        return $winners;

    }


    public function contestStats(Request $request){
        $template = "contest_stats";
        $data = [];
        $status = '';
        $data['title'] = "Contest Stats";

        $data['nofilter'] = 1;
        $post = [];

        //Check if this is coming from a POST
        if ($request->getMethod() == 'POST'){
            $contest_id = $request->request->get('contestid');
            // $number = $request->request->get('number');
            $start_date = $request->request->get('start_date');
            $end_date = $request->request->get('end_date');
            $data['stats'] = $this->getContestStats($contest_id, $start_date, $end_date);
            $post['contestid'] = $request->get('contestid');
            // $data['number'] = $number;
            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;
            // debug_var($data, 3);
        }
        // debug_var($data, 3);

        $data['all_contests'] = $this->getContestFilter();
        $email = "";

        if (!empty($contest_id)){
            $data['nofilter'] = 0;
            // $email = $request->get('email');
        } else {
            $contest_id = 0;
        }

        $data['contestid'] = $contest_id;
        return array(
            '#theme' => $template,
            '#data'  => $data,
        );

    }


    public function allEntries(Request $request){
        $template = "contest_all_entries";
        $data = [];
        $status = '';
        $data['title'] = "Contest Entries";

        $data['all_contests'] = $this->getContestFilter();
        $data['nofilter'] = 1;
        // debug_var($data, 3);

        $contest_id = $request->get('contestid');
        $email = "";

        if (!empty($contest_id)){
            $data['nofilter'] = 0;
            $email = $request->get('email');
        } else {
            $contest_id = 0;
        }

        $data['contestid'] = $contest_id;
        $data['email'] = $email;


        $data['entries'] = $this->fetchContestEntries($contest_id, $email);



        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    public function fetchContestEntries($contest_id = 0, $email){
        $email = trim($email);

        if ($contest_id == 'All')
            $contest_id = 0;

        $query = \Drupal::database()->select('pepsicontest_reg_contest', 'nfd');
                $query->addField('nfd', 'contest_id');
                $query->addField('nfd', 'enterdate');
                $query->addField('nfd', 'regdate');
                $query->addField('nfd', 'email');
                $query->addField('nfd', 'first_name');
                $query->addField('nfd', 'last_name');
                $query->addField('nfd', 'contest_name');
                $query->addField('nfd', 'contest_optin');
                if (!empty($contest_id)){
                    $query->condition('nfd.contest_id', $contest_id);
                }
                if (!empty($email)){
                    $query->condition('nfd.email', "%" . $email . "%", 'LIKE');
                }

                $query->orderBy('nfd.enterdate', 'DESC');
                $query->range(0, 50);
                
        $result = $query->execute()->fetchAll();
        return $result;

    }

    public function getContestStats($contest_id, $start_date, $end_date){
        $starting_at = "";
        $ending_at = "";
        if (!empty($start_date)){
            $starting_at = " AND t1.enterdate >= '$start_date' ";
        }
        if (!empty($end_date)){
            $ending_at = " AND t1.enterdate <= '$end_date' ";
        }

        $cnt_total = $this->getContestCount($contest_id, $starting_at, $ending_at);
        $cnt_unique = $this->getContestCount($contest_id, $starting_at, $ending_at, $unique = 1);
        $cnt_optin = $this->getContestCount($contest_id, $starting_at, $ending_at, $unique = 1, $optin = 1);

        $contest_node = \Drupal\node\Entity\Node::load($contest_id);

        return [
            'total' => $cnt_total,
            'unique' => $cnt_unique,
            'optin' => $cnt_optin,
            'contest_name' => $contest_node->field_contest_uri->value,
        ];


    }

    public function getContestCount($contest_id, $starting_at, $ending_at, $unique = false, $optin = false){
        $star = "*";
        $has_optin = "";
        if ($unique)
            $star = "DISTINCT email";

        if ($optin)
            $has_optin = " AND contest_optin = 1";


        $sql_total = "SELECT count($star) as cnt FROM pepsicontest_reg_contest AS t1
              WHERE 1
              AND t1.contest_id = $contest_id
              AND spam = 0
              $starting_at
              $ending_at
              $has_optin
        ";

        $query = \Drupal::database()->query($sql_total);
        $result = $query->fetchAll();
        if (!empty($result))
            return $result[0]->cnt;

        return 0;

    }

    public function drawRandomWinners($contest_id, $start_date, $end_date, $number = 5){
        $starting_at = "";
        $ending_at = "";
        if (!empty($start_date)){
            $starting_at = " AND t1.enterdate >= '$start_date' ";
        }
        if (!empty($end_date)){
            $ending_at = " AND t1.enterdate <= '$end_date' ";
        }
        $sql = "SELECT t1.email, contest_name, t1.contest_id, t1.postalcode, first_name, last_name, gender, postalcode, t1.enterdate  FROM pepsicontest_reg_contest AS t1
              WHERE 1
              AND t1.contest_id = $contest_id
              $starting_at
              $ending_at
              order by RAND()
              limit $number

        ";

        $query = \Drupal::database()->query($sql);
        $results = $query->fetchAll();
        return $results;
    }

    public function getContestFilter(){
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'contest');
        $query->condition('nid', 1500, '>');

        $query->sort('created', 'DESC');
        $entity_ids = $query->execute();
        $contests = [];

        foreach ($entity_ids as $key => $nid) {
            $contest_node = \Drupal\node\Entity\Node::load($nid);
            $contests[$nid] = [
                'contest_id' => $nid,
                'contest_name' => $contest_node->field_contest_uri->value,
            ];
        }
        return $contests;
        /*
        $sql = "SELECT contest_id, MIN(contest_name) as contest_name from pepsicontest_reg_contest 
         WHERE contest_id > 1500
        group by contest_id";
        $query = \Drupal::database()->query($sql);
        $results = $query->fetchAll();
        return $results;
        */
    }

    public function contestExportWinners(Request $request){
        export_winners_csv(3);
        die(" exported ");
    }


    public function translate(Request $request) {
        $subject = t("Grab Snacks Win Swag Contest: Potential Instant Win Prize Claim Details!");
        debug_var($subject);
        $firstname = "test";
        $content = "<div style = 'text-align: center'>
                    <img style = 'width: 100px' src = 'cid:logo_img'/><br><br>

                    <img style = 'width: 60%' src = 'cid:header_img'/><br><br>
                    <div style = 'width: 60%; margin: auto; text-align: left; font-size: 18px'>".
                    t("Hello") . " $firstname, <br><br>" . 
                    t("Congratulations – You’ve been selected as a potential instant prize winner in the Grab Snacks Win Swag Contest!") . "<br>" .
                    t("The prize you’ve been selected as a potential winner of is") . " $prize. <br> <br> " .

            t("Before we can declare you an official winner, you will need to complete the prize claim process.<br><br>
            Click the button below to get started. <br><br>") . 

            "<a href='$claim_url'>". t("CLAIM PRIZE") . "</a> <br> <br> <br> <br>
            <div style='font-size: 16px'>" . 
            
            t("This message was sent to you because you participated in the “Grab Snacks Win Swag Contest”, run by
            PepsiCo Canada. We can be contacted at 2095 Matheson Boulevard East, Mississauga, Ontario, L4W 0G2 or
              www.pepsico.ca. Unless you have previously opted in, your email address has not been added to any
                email marketing lists by participating in this contest.") . "</div> </div> </div>";

        debug_var($content);
        debug_var("translation", 1);
    }
    
}
