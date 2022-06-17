<?php

namespace Drupal\pepsibam\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UserAgent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
// use Drupal\ssoservices\PimcoreApi;
/**
 * Contains the callback handler used by the OneAll Social Login Module.
 */
class RegisterController extends ControllerBase {

    protected $currentuser;
    protected $city;
    protected $province;
    protected $source;
    protected $changeddata = false;
    protected $changedpwd = false;
    protected $changedlang = false;
    protected $changedemail = false;
    protected $changedpcode = false;
    protected $changedoptin = false;
    protected $changedoptin2 = false;
    protected $proceedUnsubscribe = false;
    protected $proceedActivation = false;
    protected $proceedResetPwd = false;
    protected $syncSfmcUSA = true;  // set this to false when okta's SF syncing is fixed

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function index(Request $request, $source) {

        $source = strtolower($this->validateSource($source));

        $data = array();
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        if (\Drupal::currentUser()->id() > 0) {
            //If user loggend in, exit if it's coming from iframe, otherwise redirect into updateprofile 
            $route_name = \Drupal::request()->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_NAME);
            if ($route_name == 'pepsibam.iframe.register') {
                $data["nodata"] = true;
            } else {
                return new RedirectResponse(\Drupal\Core\Url::fromRoute("pepsibam.updateprofile")->toString());
            }
        }

        //Check if there is session from facebook API

        $session = \Drupal::service('session');

        $email = $session->get('email');
        $fbid = $session->get('fbid');
        $firstname = $session->get('firstname');
        $lastname = $session->get('lastname');



        if ($email)
            $data["email"] = $email;
        if ($fbid)
            $data["fbid"] = $fbid;
        if ($firstname)
            $data["firstname"] = $firstname;
        if ($lastname)
            $data["lastname"] = $lastname;

        $session->remove('email');
        $session->remove('fbid');
        $session->remove('firstname');
        $session->remove('lastname');

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;

        $data['source'] = $source;

        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['clmn'] = $request->get('clmn');

        //\Doctrine\Common\Util\Debug::dump($data['bdaydropdown']);
        return array(
            '#theme' => 'pepsibam_registration_template',
            '#data' => $data,
        );
    }

    function split_postalcode($postalcode){
        $zipcode = [];

        $zipcode['main'] = rtrim(substr($postalcode, 0, 6), '-');
        $zipcode['extension'] = empty(substr($postalcode, 6)) ? '' : substr($postalcode, 6);
        return $zipcode;
    }
    /**
     * {@inheritdoc}
     */
    public function ajax_register_callback(Request $request) {

        $url_parts = explode('/', $request->getPathInfo()); // '/en-us/pepsi/register/ajaxaction';
        $country_code = ($url_parts[1] == 'en-us' || $url_parts[1] == 'es-us') ? 'usa' : 'ca';
        
        $isIframeForm = !empty($request->request->get('iframe-form')) ? $request->request->get('iframe-form'): false;

        $lang = trim($request->request->get('language'));

        $post['firstname'] = trim($request->request->get('firstname'));
        $post['lastname'] = trim($request->request->get('lastname'));
        $post['email'] = trim(strtolower($request->request->get('email')));
        $post['bday'] = $request->request->get('bday');
        $post['postalcode'] = trim(strtoupper($request->request->get('postalcode')));
        $post['source'] = $request->request->get('source');
        $post['langcode'] = $lang;
        $post['countrycode'] = $country_code;
        $post['isIframeForm'] = $isIframeForm;

        $post['optin'] = !empty($request->request->get('optin'))? 1 : 0;
        $post['optin2'] = !empty($request->request->get('optin2'))? 1 : 0;
        $post['optin3'] = !empty($request->request->get('optin3'))? 1 : 0;

        $post['grecaptcharesponse'] = $request->request->get('grecaptcharesponse');
        $post['password'] = $request->request->get('password');
        $post['gender'] = $request->request->get('gender');
        $post['socialsource'] = $request->request->get('socialsource');

        

        if (!$isIframeForm){
            // $post['optin'] = 1; // null !== $request->request->get('optin')  ? $request->request->get('optin') : true;
            $post['casl'] = trim($request->request->get('casl'));
            $post['fbid'] = $request->request->get('fbid');
            $post['confirm_password'] = $request->request->get('confirm_password');
            $post['csrfToken'] = $request->request->get('csrfToken');
            $post['confirm_email'] = trim(strtolower($request->request->get('confirm_email')));
            $post['sourceid'] = getSourceIdRegularPages($request->request->get('sourceid'), $lang);
        } else {
            $regsource = get_registration_source($post['source'], $lang);
            if (isset($regsource['sourceID'])) {
                $post['sourceid'] = $regsource['sourceID'];
            }
            else{
                $post['sourceid'] = getSourceIdRegularPages($request->request->get('sourceid'), $lang);
            }

            if ($country_code == 'ca' || 1){
                // For USA, the optin is always 1
                $post['optin'] = !empty($request->request->get('optin-canada'))? 1 : 0;

            }

        }

        $source = $this->validateSource($post['source']);

        $post['postalcode'] = str_replace(' ', '', $post['postalcode']);

        $errors = $this->validate_registration($post, $country_code, $isIframeForm);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';

        $post['zipcode'] = $this->split_postalcode($post['postalcode']);
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database

            try {
                
                $date = date('Y-m-d H:i:s');

                $post['city'] = !empty($this->city)? $this->city : '';
                $post['province'] = !empty($this->province)? $this->province : '';
                $post['ccid'] = $this->source['ccid'];
                $post['source'] = $source;
                $post['ipaddress'] = getIPaddress();
                $post['useragent'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
                $post['language'] = $language;
                $post['date'] = $date;
                $post['isIframeForm'] = $isIframeForm;
                $post['socialsource'] = isset($post['socialsource'])? $post['socialsource'] : "";

                // Update to switch the registration process for CAN users.
                // Have to call the OCH API call.
                $sso_service = \Drupal::service('services.sso');
                $okta_userid = $sso_service->CreateOchUser($post);
                    
                if ($okta_userid) {
                    $status = TRUE;
                    $post['field_marketoid'] = $okta_userid;  // OCH Account ID Needed to get / update the OCH apicalls
                    $user = CreateUserRecord($post);
                }
                else
                {
                    $status = FALSE;
                    $errors['form'] = t('Oops, there is an issue. Please try again.');
                    $oktaerr = $sso_service->getErrorMsg();
                    if (!empty($oktaerr)){
                        $errors = $sso_service->ConvertFormErrors();
                    }
                }
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }
            if ($status) {

                if ($language !== 'en' && $language !== 'fr') {  
                    if ($this->syncSfmcUSA === true ){
                        sfmcservices_subscribe($user, $source);
                    }
                }

                $session = \Drupal::service('session');
                $session->set('optin', $post['optin']);

                $langcode = get_current_langcode();

                //Even there is no success in marketo call, have to return success for user
                $status = TRUE;
                // if ($source == 'tastyrewards'){
                if (in_array($source, ['tastyrewards', 'popupTastyrewards', 'popupTastyrewardsMembers'])){
                    $route = Url::fromRoute($langcode . '.pepsibam.register.processed', [], ['absolute' => true])->toString();
                    // \Drupal::logger("general")->info("Tastyrewards source registered thankyou page", []);

                }
                else{
                    $route = Url::fromRoute('pepsibam.iframe.register.processed', [], ['absolute' => true])->toString();
                    // \Drupal::logger("general")->info("iframe source registered thankyou page " . $source, []);
                }
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        if ($isIframeForm){
            $json = json_encode($return);
            print_r($json);
            exit();
        }
        return new JsonResponse($return);
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function updateProfilePage() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API
        //Get data for current user

        $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

        // OCH Api call to get the client from OCH before profile update
        $sso_service = \Drupal::service('services.sso');
        $ochuser =  $sso_service->GetOchUser();

        if (!$ochuser) {
            // throw new NotFoundHttpException();
            //throw new MethodNotAllowedHttpException($message = '');
        }
        else{
            $user = $sso_service->updateOchToTR($user,$ochuser);
        }

        $data = [];
        $data['allow_bday_entry'] = 0;
        if ($user->id() > 0 ){ // Have to present the login 

            $data['email'] = $user->get('mail')->value;
            $data['firstname'] = $user->get('field_firstname')->value;
            $data['lastname'] = $user->get('field_lastname')->value;
            $data['postalcode'] = $user->get('field_postalcode')->value;
            $data['gender'] = $user->get('field_gender')->value;
            $data['bday'] = $user->get('field_bday')->value;
            $data['optin'] = $user->get('field_optin')->value;
            $data['optin2'] = $user->get('field_optin2')->value;
            if ($data['optin2'] === null){
                // When it's an existing user and the field hasn't been set yet
                // the checkbox should be pre-checked, hence optin2 = 1
                $data['optin2'] = 1;
            }
            $data['optin3'] = $user->get('field_optin3')->value;
            $data['optin4'] = $user->get('field_optin4')->value;
            $data['optin5'] = $user->get('field_optin5')->value;
            $data['optin6'] = $user->get('field_optin6')->value;


            $data['allowoptin'] = false;
            if (!$user->get('field_optin')->value)
                $data['allowoptin'] = true;


            //\Doctrine\Common\Util\Debug::dump($data);
            //generating CSRF token
            $data['csrfToken'] = $this->CreateCsrfToken();
            // @TODO: Fetch shipping address from pimcore, NOT och
            //$token =  $sso_service->get_consumer_token();
            //$pimcore_obj = new PimcoreApi();
            //$pimcore_obj->setAccessToken($token);
            $shipping_address = $sso_service->getShippingAddress();

            if (empty($shipping_address)){
                $data['accountAddress'] =  $this->RemoveProfileAddresses($ochuser["accountAddresses"]);
            } else {
                $data['accountAddress'] = $shipping_address;
            }

            //Getting language and passing to twig
            $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
            $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;
            // if (in_array(get_current_langcode(), ['en-us', 'es-us']))
            $data['language_preference'] = $user->get("preferred_langcode")->value;
            
            $data['bdaydropdown'] = $this->CreateDateDropdown($data['bday']);
            if (!$data['bday']) {
                $data['allow_bday_entry'] = 1;
            }

            $data['loggedin'] = "1";
            if ($lang == 'en-us' || $lang == 'es-us'){
                $data['states'] = $this->getUSStates();
            }

        }
        else{
            $data['loggedin'] = "0";
            $data['language'] = $lang;
            $request = \Drupal::request();
            $referer = $request->headers->get('referer');

            if ($lang == 'en-us' || $lang == 'en-us'){
                $ssoconfig = \Drupal::config('ssoconfig_us.adminsettings');
            }
            else{
                $ssoconfig = \Drupal::config('ssoconfig.adminsettings');
            }

            $snack_url  = $ssoconfig->get('snack_url');

            if(strpos($referer, $snack_url) !== false){
                $data['loggedin'] = "2";    
            }
            
        }
        //\Doctrine\Common\Util\Debug::dump($data);

        return array(
            '#theme' => 'pepsibam_updateprofile_template',
            '#data' => $data,
        );
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function deleteProfilePage() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;
        $data['uid'] = \Drupal::currentUser()->id();


        return array(
            '#theme' => 'pepsibam_deleteprofile_template',
            '#data' => $data,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_deleteprofile_callback(Request $request) {

        $post['optin'] = $request->request->get('optin');
        $post['casl'] = trim($request->request->get('casl'));
        $post['csrfToken'] = $request->request->get('csrfToken');


        $errors = $this->validateDeleteProfile($post);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';
        $user_id = '';
        if (count($errors) > 0) {
            $status = FALSE;
        } else {


            // OCH Api call to get the client from OCH before profile update
            $sso_service = \Drupal::service('services.sso');
            $sso_service->DeleteOchUser();

            //saving in the database

            try {

                $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
                $user_id = \Drupal::currentUser()->id();
                $this->currentuser = $user;
                $date = date('Y-m-d H:i:s');

                $user->set('status', 0);    //status 0 is inactive
                $user->set('field_edit_date', $date);
                $user->set('field_deleted_date', $date);
                $user->set('field_ip_address', getIPaddress());
                $user->set('field_user_agent', substr($_SERVER['HTTP_USER_AGENT'], 0, 255));

                $user->set('field_optin', 0); //Unsubscribe
                $user->set("field_optout_date", $date);

                $user->save();
                $status = TRUE;
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }

            if ($status) {

                //Marketo Connection to be removed
                //MarketoUnsubscribe($user); // Call MArketo API (create/update profile)
                
                //Even there is no success in marketo call, have to return success for user
                $status = TRUE;
                $route = '/';    //Url::fromRoute('pepsibam.deleteprofile.processed', [],['absolute'=>true ])->toString();
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken, 'user_id' => $user_id);

        return new JsonResponse($return);
    }

    public function validateDeleteProfile($post) {

        //Check the CsrfToken
        $error = array();

        if (!$this->CheckCsrfToken($post['csrfToken'])) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }

        //Check if optin
        if ($post['optin'])
            $this->changedoptin = true;

        if (!$this->changedoptin) {
            $error['form'] = t('Oops, looks like you forgot to confirm that you would like to delete your account.');
            return $error;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_deleteprofilereasons_callback(Request $request) {

        $post['user_id'] = $request->request->get('user_id');
        $post['reasons'] = $request->request->get('reasons');
        $post['casl'] = trim($request->request->get('casl'));
        $post['csrfToken'] = $request->request->get('csrfToken');
        if (!$post['user_id']){
            if (\Drupal::currentUser()->id() > 0 ){
                $post['user_id'] = \Drupal::currentUser()->id();
            }
        }

        $route = '';
        $errors = '';

        if (!$post['reasons']) {
            $status = true;
            $route = '/';
        } else {
            //saving in the database

            try {

                $user = \Drupal\user\Entity\User::load($post['user_id']);

                $date = date('Y-m-d H:i:s');
                $user->set('field_deleted_date', $date);
                $user->set('field_deleted_reasons', $post['reasons']);

                $user->save();
                $status = TRUE;
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }

            if ($status) {

                //Marketo Connection to be removed
                //MarketoUnsubscribe($user); // Call MArketo API (create/update profile)
                //
                //Even there is no success in marketo call, have to return success for user
                $status = TRUE;
                $route = '/';    //Url::fromRoute('pepsibam.deleteprofile.processed', [],['absolute'=>true ])->toString();
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        return new JsonResponse($return);
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_updateprofile_callback(Request $request) {

        $post['firstname'] = trim($request->request->get('firstname'));
        $post['lastname'] = trim($request->request->get('lastname'));
        $post['password'] = trim($request->request->get('password'));
        $post['language'] = trim($request->request->get('language'));
        $post['confirm_password'] = trim($request->request->get('confirm_password'));
        $post['gender'] = trim($request->request->get('gender'));
        //$post['email'] = trim(strtolower($request->request->get('email')));
        //$post['confirm_email'] = trim(strtolower($request->request->get('confirm_email')));
        $post['bday'] = $request->request->get('bday');
        if ($post['bday'] == '--'){
            $post['bday'] = null;
        }

        $post['allow_bday_entry'] = $request->request->get('allow_bday_entry');
        $post['postalcode'] = trim(strtoupper($request->request->get('postalcode')));
        //$post['gender'] = $request->request->get('gender');
        $langcode = get_current_langcode();
        // if ($langcode == 'en-us' ){
            // $post['optin'] = 1;
        // } else {
        $post['optin'] = !empty($request->request->get('optin')) ? 1 : 0;
        $post['optin2'] = !empty($request->request->get('optin2')) ? 1 : 0;
        $post['optin3'] = !empty($request->request->get('optin3')) ? 1 : 0;
        $post['optin4'] = !empty($request->request->get('optin4')) ? 1 : 0;
        $post['optin5'] = !empty($request->request->get('optin5')) ? 1 : 0;
        $post['optin6'] = !empty($request->request->get('optin6')) ? 1 : 0;
        // }
        $post['casl'] = trim($request->request->get('casl'));
        $post['csrfToken'] = $request->request->get('csrfToken');
        $post['postalcode'] = str_replace(' ', '', $post['postalcode']);

        $errors = $this->validateUpdateProfile($post);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';
        $route_options = ['absolute' => true];
        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database

            try {

                $user = $this->currentuser;
                $date = date('Y-m-d H:i:s');

                if ($this->changedpcode) {
                    $user->set('field_city', $this->city);
                    $user->set('field_province', $this->province);
                    $user->set('field_postalcode', $post['postalcode']);
                }

                if ($post['language'] != 'en-us' && $post['language'] != 'es-us') {

                    if ($this->changedoptin) {

                        $user->set('field_optin', $post['optin']);
                        $user->set("field_optin_date", $date); //Check if neeeded
                        $user->set("field_optin_casl", $post['casl']); //Check if neeeded
                    }

                    if ($this->changedoptin2) {
                        $user->set('field_optin2', $post['optin2']);
                    }
                }
                else{
                    $user->set('field_optin', $post['optin']);
                    $user->set('field_optin2', $post['optin2']);
                }
                /*
                  if ($this->changedemail) {
                  $user->set('name', $post['email']);
                  $user->set('mail', $post['email']);
                  } */
                if ($this->changedlang) {
                    $currentlang = \Drupal::languageManager()->getCurrentLanguage()->getId();
                    if (in_array($currentlang, ['en-us', 'es-us'])){
                        $currentlang = $post['language'];
                        $lang_object = \Drupal::languageManager()->getLanguage($currentlang);
                        $route_options['language'] = $lang_object;
                    }
                    
                    $user->set("langcode", $currentlang);
                    $user->set("preferred_langcode", $currentlang);
                    $user->set("preferred_admin_langcode", $currentlang);
                }
                if ($this->changeddata) {
                    $user->set('field_firstname', $post['firstname']);
                    $user->set('field_lastname', $post['lastname']);
                    //$user->set('field_gender', $post['gender']);
                    //There is no birthday in the edit form 
                    // $user->set('field_bday', $post['bday']);
                }
                if ($this->changeddata || $this->changedlang || $this->changedpcode ){
                    $user->set('field_edit_date', $date);
                }

                if ($this->changedpwd) {
                    $user->setPassword($post['password']);
                }

                $user->set('field_optin3', $post['optin3']);
                $user->set('field_optin4', $post['optin4']);
                $user->set('field_optin5', $post['optin5']);
                $user->set('field_optin6', $post['optin6']);

                $user->set('field_ip_address', getIPaddress());
                $user->set('field_user_agent', substr($_SERVER['HTTP_USER_AGENT'], 0, 255));
                if ($post['language'] == 'en-us' || $post['language'] == 'es-us') {
                    $user->set('field_gender', $post['gender']);
                }

                if ($user->get('field_bday')->value == null && $post['bday'] != null && ( $post['language'] == 'en-us' || $post['language'] == 'es-us')) {
                    $user->set('field_bday', $post['bday']);
                }

                $user->save();
                $status = TRUE;
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }

            if ($status) {
                
                // OCH update both CAN and US members
                $sso_service = \Drupal::service('services.sso');
                $ochuser =  $sso_service->UpdateOchUser($user);
                if (!$ochuser){
                    $status = FALSE;
                    $errors['form'] = t('Oops, there is an issue. Please try again.');
                    $route = '';                        
                }
                else{
                    if ($this->changedpcode) {
                        $ochuser =  $sso_service->UpdateAddressOchUser($user);
                    }

                    if ( in_array($langcode, ['en-us', 'es-us'] )) {  
                        if ($this->syncSfmcUSA === true ){
                            $source = $user->get('field_source')->value;
                            $source = empty($source)? 'tastyrewards': $source;

                            sfmcservices_subscribe($user, $source);
                        }
                    }
                    //Even there is no success in marketo call, have to return success for user
                    $status = TRUE;
                    $route = Url::fromRoute('pepsibam.updateprofile.processed', [], ['absolute' => true])->toString();
                }
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        return new JsonResponse($return);
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function updateProfilePasswordPage() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API
        //Get data for current user


        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;


        return array(
            '#theme' => 'pepsibam_updateprofilepwd_template',
            '#data' => $data,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_updateprofilepassword_callback(Request $request) {

        $post['currentpassword'] = $request->request->get('currentpassword');
        $post['password'] = $request->request->get('password');
        $post['confirm_password'] = $request->request->get('confirm_password');
        $post['csrfToken'] = $request->request->get('csrfToken');


        $errors = $this->validateUpdateProfilePwd($post);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';

        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database

            try {

                $user = $this->currentuser;

                $user->setPassword($post['password']);

                $user->save();
                $status = TRUE;
                //$route = base_path();
                $route = Url::fromRoute('pepsibam.updateprofilepwd.processed', [], ['absolute' => true])->toString();
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        return new JsonResponse($return);
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function unsubscribePage() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API

        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;

        return array(
            '#theme' => 'pepsibam_unsubscribe_template',
            '#data' => $data,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_unsubscribe_callback(Request $request) {

        $post['email'] = trim(strtolower($request->request->get('email')));
        $post['csrfToken'] = $request->request->get('csrfToken');
        $isIframeForm = !empty($request->request->get('iframe-form')) ? $request->request->get('iframe-form'): false;
        $errors = $this->validateUnsubscribe($post, $isIframeForm);

        $source = !$isIframeForm? 'tastyrewards' : $request->request->get('source');

        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';
        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database
            try {
                if ($this->proceedUnsubscribe) { // Only proceed if email exists in ther DB, otherwise, only return as false positive
                    $user = $this->currentuser;
                    $user->set('field_optin', 0); //Unsubscribe
                    $user->set("field_optout_date", date('Y-m-d H:i:s'));

                    $user->save();
                    sfmcservices_subscribe($user, $source, $unsubscribe = true);
                    //Marketo Connection to be removed
                    //MarketoUnsubscribe($user); // Call MArketo API (create/update profile)
                }

                $status = TRUE;
                //$route = base_path();
                $lanid= \Drupal::languageManager()->getCurrentLanguage()->getId();
                $route = Url::fromRoute($lanid .'.pepsibam.unsubscribe.processed', [], ['absolute' => true])->toString();
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);
        if ($isIframeForm){
            $json = json_encode($return);
            print_r($json);
            exit();
        }

        return new JsonResponse($return);
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function accountactivation($token) {


        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //$user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        //\Doctrine\Common\Util\Debug::dump(\Drupal::currentUser()->id());



        $token = base64_decode($token);
        $tokenarr = explode('|', $token);

        $error = [];

        if (!isset($tokenarr[0]) || !isset($tokenarr[1])) { //token bad formed
            $error['badtoken'] = true;
        } elseif (!$this->validateDate($tokenarr[0])) { //token well formated but no date in the first position
            $error['badtoken_date'] = true;
        } elseif ($this->validateDateExpirated($tokenarr[0])) { //token expired
            $error['dateexpired'] = true;
        } elseif (!$this->searchUserbyToken($tokenarr[1])) {
            $error['nouser'] = true;
        } elseif ($this->currentuser->get('status')->value == '1') {
            $error['alreadyactivated'] = true;
        }

        if (count($error) > 0) {
            //Check if user is already activated and is logedin, then redirect to home page
            if (\Drupal::currentUser()->id() > 0) {
                return new RedirectResponse(\Drupal\Core\Url::fromRoute("<front>")->toString());
            }


            if (isset($error['alreadyactivated']))
                $msg = t('User is already activated');
            else
                $msg = t('There is an issue, invalid url or expired');
        }
        else {
            $this->currentuser->set("status", 1);
            $this->currentuser->set('field_activationurl', ''); //reset url
            $this->currentuser->set('field_activationtoken', ''); //reset token

            $date = date('Y-m-d H:i:s');
            $this->currentuser->set('field_activation_date', $date);

            $this->currentuser->save();
            
            //Marketo Connection to be removed
            //MarketoSubscribe($this->currentuser);

            $path = Url::fromRoute('user.login')->toString();
            $msg = t('Your account was activated, please click <a href=":path"><b>here</b></a> to login', array(":path" => $path));
        }

        /* return array(
          '#type' => 'markup',
          '#markup' => $msg,
          );
         * 
         */

        return array(
            '#theme' => 'pepsibam_processed_template',
            '#data' => array('header' => t('Account activation'), 'subheader' => $msg, 'body' => '')
        );
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function activationFormPage() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API

        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;

        return array(
            '#theme' => 'pepsibam_activationform_template',
            '#data' => $data,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_activation_callback(Request $request) {

        $post['email'] = trim(strtolower($request->request->get('email')));
        $post['csrfToken'] = $request->request->get('csrfToken');


        $errors = $this->validateActivationForm($post);
        $route = '';
        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database

            try {
                if ($this->proceedActivation) { // Only proceed if email exists in ther DB, otherwise, only return as false positive
                    $user = $this->currentuser;

                    $this->GenerateSaveActivationToken($user);

                    //Call Marketo subscribe
                    // MarketoSubscribe($user);

                    MarketoEmailActivation($user, $resend = true); // Call Marketo API (create/update profile and send activation email)
                }

                $status = TRUE;
                //$route = base_path();
                $route = Url::fromRoute('pepsibam.activationform.processed', [], ['absolute' => true])->toString();
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        return new JsonResponse($return);
    }

    public function GenerateSaveActivationToken(&$user) {

        //Generate Unique Url

        $hashed_id = hash('crc32', $user->id());
        $date = date('Y-m-d H:i:s');
        $code = base64_encode($date . '|' . $hashed_id);
        $url = Url::fromRoute('pepsibam.accountactivation', ['token' => $code], ['absolute' => true]);

        $user->set('field_activationurl', $url->toString()); //Unsubscribe absolute URL
        $user->set('field_activationtoken', $hashed_id); //Token

        $user->save();
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function resetPwdRequest() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API

        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;
        $data['is_resetpwd'] = true;

        return array(
            '#theme' => 'pepsibam_resetpwdrequestform_template',
            '#data' => $data,
        );
    }

    public function createPwdRequest() {
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API

        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;
        $data['is_resetpwd'] = false;

        return array(
            '#theme' => 'pepsibam_resetpwdrequestform_template',
            '#data' => $data,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_resetpwdrequest_callback(Request $request) {


        $post['email'] = trim(strtolower($request->request->get('email')));
        $post['csrfToken'] = $request->request->get('csrfToken');
        $post['isCreatePwd'] = $request->request->get('isCreatePwd');


        $errors = $this->validateResetPwdForm($post);
        $route = '';
        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database
            //try {
            if ($this->proceedResetPwd) { // Only proceed if email exists in ther DB, otherwise, only return as false positive
                $user = $this->currentuser;

                //Generate Unique Url
                $hashed_id = hash('crc32', $user->id());
                $date = date('Y-m-d H:i:s');
                $code = base64_encode($date . '|' . $hashed_id . '|password');
                $url = Url::fromRoute('pepsibam.changepassword', ['token' => $code], ['absolute' => true]);

                $user->set('field_activationurl', $url->toString()); //Unsubscribe absolute URL

                $user->set('field_activationtoken', $hashed_id); //Token

                $user->save();
                if (!empty($post['isCreatePwd']) ){
                    sfmcservices_trigger_forgotpassword($user, $createPwd = true);
                } else {
                    sfmcservices_trigger_forgotpassword($user);
                }
            }

            $status = TRUE;
            //$route = base_path();
            $route = Url::fromRoute('pepsibam.resetpwdrequestform.processed', [], ['absolute' => true])->toString();

            /* } catch (\Exception $e) {
              $status = FALSE;
              $errors['form'] = t('Oops, there is an issue. Please try again.');
              $route = '';
              } */
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        return new JsonResponse($return);
    }

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function resetpassword($token) {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //Check if there is session from facebook API

        $token = base64_decode($token);
        $tokenarr = explode('|', $token);


        $error = [];

        if (!isset($tokenarr[0]) || !isset($tokenarr[1]) || !isset($tokenarr[2])) { //token bad formed checj if date, token , and password key is coming
            $error['badtoken'] = true;
        } elseif (!$this->validateDate($tokenarr[0])) { //token well formated but no date in the first position
            $error['badtoken_date'] = true;
        } elseif ($this->validateDateExpirated($tokenarr[0])) { //token expired
            $error['dateexpired'] = true;
        } elseif (!$this->searchUserbyToken($tokenarr[1])) {
            $error['nouser'] = true;
        } elseif ($this->currentuser->get('status')->value == '0') {
            $error['blockeduser'] = true;
        }

        if (count($error) > 0) {
            $msg = t('There is an invalid request, wrong or expired url, or user is blocked');
            return array(
                '#theme' => 'pepsibam_processed_template',
                '#data' => array('header' => t('Reset Password'), 'subheader' => $msg, 'body' => '')
            );
        }


        $data = [];

        //generating CSRF token
        $data['csrfToken'] = $this->CreateCsrfToken();

        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $data['language'] = $prefixes[\Drupal::languageManager()->getCurrentLanguage()->getId()] ;

        $data['uritoken'] = $tokenarr[1];


        return array(
            '#theme' => 'pepsibam_updatepassword_template',
            '#data' => $data,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_resetpassword_callback(Request $request) {


        $post['password'] = $request->request->get('password');
        $post['confirm_password'] = $request->request->get('confirm_password');
        $post['csrfToken'] = $request->request->get('csrfToken');
        $post['uritoken'] = $request->request->get('uritoken');


        $errors = $this->validateUpdatePassword($post);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';

        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving in the database

            try {

                $user = $this->currentuser;

                $user->setPassword($post['password']);
                $user->set('field_activationurl', ''); //reset url
                $user->set('field_activationtoken', ''); //reset token

                // Clear flood control


                $user->save();
                $status = TRUE;
                $ip =  $request->getClientIP();
                pepsibam_clear_flood($user, $ip);
            } catch (\Exception $e) {
                $status = FALSE;
                $errors['form'] = t('Oops, there is an issue. Please try again.');
                $route = '';
            }

            if ($status) {

                //Call Marketo set (method in MarketoServices)
                // MarketoSubscribe($user); // Call MArketo API (create/update profile)
                //Even there is no success in marketo call, have to return success for user
                $status = TRUE;
                //$route = base_path();
                $route = Url::fromRoute('pepsibam.changepassword.processed', [], ['absolute' => true])->toString();
            }
        }

        $csrfToken = '';
        if (!$status) {
            //regenerate token and send back to the form 
            $csrfToken = $this->CreateCsrfToken();
        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => $csrfToken);

        return new JsonResponse($return);
    }

    public function validateResetPwdForm($post) {

        //Check the CsrfToken
        $error = array();


        if (!$this->CheckCsrfToken($post['csrfToken'])) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }

        //validate Email
        if (trim($post['email']) == '') {
            $error['email'] = t('Please enter your email address.');
        } elseif (!$this->EmailValidation($post['email']))
            $error['email'] = t('Please enter a valid email address.');
        else {
            $user = $this->UserRegistered($post['email']);

            if ($user) {
                $usrlang = $user->get('preferred_langcode')->value;

                /*
                if ($usrlang == 'en' ||  $usrlang == 'fr'){
                    $roles = $user->getRoles();
                    if (in_array('administrator', $roles) || in_array('reporting', $roles) || in_array('editor', $roles) ) {
                    
                    }else{
                        $error['email'] = t("Looks like you're already a Canadian Tasty Rewards™ member! <a href='/" .$usrlang . "-ca/'><i>Switch&nbspcountries&nbspto&nbspsign&nbspin</i></a>");
                        return $error;
                    }
                } 
                */           
    
                if ($user->get('status')->value == '1') { //Only allow reset password for active account
                    $this->proceedResetPwd = true;
                    $this->currentuser = $user;
                } else {
                    // Blocked users who reset their password are automatically unblocked
                    $user->set('status', 1); 
                    $user->save();
                    $this->proceedResetPwd = true;
                    $this->currentuser = $user;                    
                }
            } elseif( !empty($post['isCreatePwd'])) {
                // look for the email in the usa_csv table DB
                $user_profile = find_snackperk_user($post['email']);
                if (!empty($user_profile)){
                   $user = create_snackperk_user($user_profile);
                   if (!empty($user)) {
                        $this->currentuser = $user;
                        $this->proceedResetPwd = true;
                   }
                } else {
                    // Look for the email on FB data extension
                    //$facebook_ad_user = find_fb_ad_user($post['email']);
                    //if (!empty($facebook_ad_user)){
                    //    $user = create_user_fb_ad($facebook_ad_user);
                    //    $this->currentuser = $user;
                    //    $this->proceedResetPwd = true;
                    //} else {
                        // SHould we throw an error here?
                        $error['email'] = t('This email is not in our system ');
                    //}
                }
            }
        }
        return $error;
    }

    public function validateActivationForm($post) {

        //Check the CsrfToken
        $error = array();


        if (!$this->CheckCsrfToken($post['csrfToken'])) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }

        //validate Email
        if ($post['email'] == '') {
            $error['email'] = t('Sorry we were not able to process your request, please enter your email address.');
        } elseif (!$this->EmailValidation($post['email']))
            $error['email'] = t('Sorry we were not able to process your request, please enter a valid email address.');
        else {
            if ($user = $this->UserRegistered($post['email'])) {
                $this->proceedActivation = true;
                $this->currentuser = $user;
            }
        }
        return $error;
    }

    public function validateUnsubscribe($post, $iframe_form = false) {

        //Check the CsrfToken
        $error = array();


        if (!$this->CheckCsrfToken($post['csrfToken']) && !$iframe_form) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }

        //validate Email
        if ($post['email'] == '') {
            $error['email'] = t('Sorry we were not able to process your request, please enter your email address.');
        } elseif (!$this->EmailValidation($post['email']))
            $error['email'] = t('Sorry we were not able to process your request, please enter a valid email address.');
        else {
            if ($user = $this->UserRegistered($post['email'])) {
                $this->proceedUnsubscribe = true;
                $this->currentuser = $user;
            }
        }
        return $error;
    }

    public function validateUpdatePassword($post) {

        //Check the CsrfToken
        $error = array();

        if (!$this->CheckCsrfToken($post['csrfToken'])) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }


        //password 
        if (!$this->PasswordFormatValidation($post['password']))
            $error['password'] = t('Please enter a valid password.');
        if (trim(strtolower($post['password'])) != trim(strtolower($post['confirm_password'])))
            $error['confirm_password'] = t("Password confirmation doesn't match");

        //check uritoken if corresponf to a valid user
        if ($this->searchUserbyToken($post['uritoken'])) {
            if ($this->currentuser->get('status')->value == '0') {
                $error['form'] = t('seem you account is not activated, please activate you account');
            }
        } else {
            $error['form'] = t('We are not able to find your account');
        }


        return $error;
    }

    public function validateUpdateProfilePwd($post) {

        //Check the CsrfToken
        $error = array();

        if (!$this->CheckCsrfToken($post['csrfToken'])) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }


        //New password 
        if (!$this->PasswordFormatValidation($post['password']))
            $error['password'] = t('Please enter a valid password.');
        if (trim(strtolower($post['password'])) != trim(strtolower($post['confirm_password'])))
            $error['confirm_password'] = t("Password confirmation doesn't match");

        //Checking current password
        if (!$this->CurrentPasswordValidation($post['currentpassword']))
            $error['currentpassword'] = t('Current password does not match with the user password');


        return $error;
    }

    public function validateUpdateProfile($post) {

        //Check the CsrfToken
        $error = array();

        if (!$this->CheckCsrfToken($post['csrfToken'])) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }

        //Check if is some field changed
        $this->isFieldsChanged($post);

        /* if (!$this->changeddata && !$this->changedlang && !$this->changedpcode && !$this->changedoptin && !$this->changedpwd ){
          $error['form'] = t('There is not changes to save');
          return $error;
          } */

            if ($this->changedpcode) {
                //postalcode
                $country_code = (isset($post['language']) && ($post['language'] == 'en-us' || $post['language'] == 'es-us' ))? 'usa': 'ca';
                if (!$this->zipCodeValidation($post['postalcode'], $country_code))
                    $error['postalcode'] = t('Please enter a valid postal code');
                else {
                    //gettting City and province
                    $this->searchProvince($post['postalcode']);
                }
            }

        if ($this->changedpwd) {
            if (!$this->PasswordFormatValidation($post['password']))
                $error['password'] = t('Please enter a valid password.');
            if (trim(strtolower($post['password'])) != trim(strtolower($post['confirm_password'])))
                $error['confirm_password'] = t("Password confirmation doesn't match");
        }

        if ($this->changeddata) {
            //firstname val
            if (!$this->userNamesValidation($post['firstname']))
                $error['firstname'] = t('Please enter a valid first name');

            //lastname val
            if (!$this->userNamesValidation($post['lastname']))
                $error['lastname'] = t('Please enter a valid last name');


            //gender
            /* $genders = array("M", "F", "O");
              if (!in_array($post['gender'], $genders))
              $error['gender'] = t('Please enter a valid gender');
             */
            //birthdate
            // $dt = \DateTime::createFromFormat("Y-m-d", $post['bday']);
            // if ($dt && array_sum($dt->getLastErrors()) == 0) {
            //     if (!$this->ValidateAge($dt, $this->province))
            //         $error['bday'] = t('Sorry! You must be 13+ to sign up.');
            // } else
            //     $error['bday'] = t('Please enter a valid birthdate');
        }

        if ($post['allow_bday_entry'] == '1'){
            if ( $post['bday']){ //Evaluate if this is not null
                $dt = \DateTime::createFromFormat("Y-m-d", $post['bday']);
                if ($dt && array_sum($dt->getLastErrors()) == 0) {
                    // if (!$this->ValidateAge($dt, $this->province)) //Have to check how USA works
                    //    $error['bday'] = t('Sorry! You must be 13+ to sign up.');
                } else
                    $error['bday'] = t('Please enter a valid birthdate');
            }
        }
        

        /* if ($this->changedemail) {
          //validate Email
          if (!$this->EmailValidation($post['email']))
          $error['email'] = t('Please enter a valid email');
          elseif (trim(strtolower($post['email'])) != trim(strtolower($post['confirm_email'])))
          $error['confirm_email'] = t("Confirmation email doesn't match");
          elseif ($this->UserRegistered($post['email']))
          $error['email'] = t('Email already taken');
          } */

        return $error;
    }

    public function isFieldsChanged($post) {


        //Validate id any field changed
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

        $this->currentuser = $user;

        $old_optin = $user->get('field_optin')->value;
        $old_optin2 = $user->get('field_optin2')->value;

        if ($post['optin'] != $old_optin) {
            $this->changedoptin = true;
        }

        if ($post['optin2'] != $old_optin2 || $old_optin2 === null
        ) {
            $this->changedoptin2 = true;
        }

        /* if ($post['email']        != $user->get('mail')->value) {
          $this->changedemail = true;
          } */

        if ($post['firstname'] != $user->get('field_firstname')->value ||
                $post['lastname'] != $user->get('field_lastname')->value ||
                //$post['gender']        != $user->get('field_gender')->value ||
                $post['bday'] != $user->get('field_bday')->value
        ) {
            $this->changeddata = true;
            $this->province = $user->get('field_province')->value;
            $this->city = $user->get('field_city')->value;
        }

        if ($post['password'] != '' && $post['confirm_password'] = !'') {
            $this->changedpwd = true;
        }


        if ($post['postalcode'] != $user->get('field_postalcode')->value) {
            $this->changedpcode = true;
        }


        $currentlanguage = \Drupal::languageManager()->getCurrentLanguage()->getId();
        if (in_array($currentlanguage, ['en-us', 'es-us']))
            $currentlanguage = $post['language'];

        if ($user->get("preferred_langcode")->value != $currentlanguage)
            $this->changedlang = true;
    }

    public function validate_registration($post, $country_code = 'ca',  $is_iframe = false) {

        //Check the CsrfToken
        $error = array();

        if (!$is_iframe && !$this->CheckCsrfToken($post['csrfToken']) ) {
            $error['form'] = t('Oops, there is an issue. Please try again.');
            return $error;
        }

        //firstname val
        if (!$this->userNamesValidation($post['firstname']))
            $error['firstname'] = t('Please enter a valid first name.');

        //lastname val
        if (!$this->userNamesValidation($post['lastname']))
            $error['lastname'] = t('Please enter a valid last name.');

        //postalcode
        //if (!preg_match('/^[a-zA-Z0-9]{3}( )?[a-zA-Z0-9]{3}$/', trim($post['postalcode'])))
        if (!$this->zipCodeValidation($post['postalcode'], $country_code)){
            $error['postalcode'] = t('Please enter a valid postal code.');
            \Drupal::logger('debug')->info(" Country = $country_code,  Mobile invalid postalcode  " .$post['postalcode'] , []);
        }
        else {
            //gettting City and province
            $this->searchProvince($post['postalcode']);
        }

        //gender
        $genders = array("M", "F", "O");
        if ($post['gender'] != 'M' && $post['gender'] != 'M')
            $post['gender'] = 'O';
        if (!in_array($post['gender'], $genders))
            $error['gender'] = t('Please enter a valid option');

        //birthdate
        $dt = \DateTime::createFromFormat("Y-m-d", $post['bday']);
        if ($dt && array_sum($dt->getLastErrors()) == 0) {
            if (!$this->ValidateAge($dt, $this->province))
                $error['bday'] = t('Sorry! You must be 13+ to sign up.');
        } else
            $error['bday'] = t('Please enter your birthday.');


        /*
        if ( $country_code != 'ca') { // Only Validation for us members. CAN validation is done in the okta side
            //validate Email 
            if (!$this->EmailValidation($post['email']))
                $error['email'] = t('Please enter a valid email address.');
            elseif (!EpsilonEmailValidation($post['email']))
                $error['email'] = t('This email address is not accepted. Please use a different email address.');
            // elseif (trim(strtolower($post['email'])) != trim(strtolower($post['confirm_email'])) && !$is_iframe)
            //     $error['confirm_email'] = t("Emails do not match.");
            //elseif ($this->UserRegistered($post['email']))
            //    $error['email'] = t('You’ve already registered to Tasty Rewards.');
            elseif(!validate_ip_address(getIPaddress(), $post['email'] )){
                $error['email'] = t('Oops! Something went wrong!');
            }
        }*/

        //password 
        if ($post['socialsource'] == '') { //If it's coming as social (GOOGLE), it doesn't need password
            if (!$this->PasswordFormatValidation($post['password']) && !$is_iframe)
                $error['password'] = t('Please enter a valid password.');
            // if (trim(strtolower($post['password'])) != trim(strtolower($post['confirm_password'])) && !$is_iframe)
            //     $error['confirm_password'] = t("Passwords do not match.");
        }
        //captcha
        //if (!RecaptchaValidation($post['grecaptcharesponse']) && !$is_iframe) {
        if (!RecaptchaValidation($post['grecaptcharesponse'])) {
            $error['grecaptcharesponse'] = t('Recaptcha Required.');
        }

        return $error;
    }

    public function CurrentPasswordValidation($password) {

        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

        $uid = \Drupal::service('user.auth')->authenticate($user->get('mail')->value, $password);

        if ($uid) {
            //  do stuff
            $this->currentuser = $user;
            return true;
        }
        return false;
    }

    public function PasswordFormatValidation($password) {
        //if (!preg_match('#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#',$password ))  return false;
        if (!preg_match('#.*^(?=.{8,16})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#', $password))
            return false;
        return true;
    }

    public function userNamesValidation($name) {
        if (preg_match ("/^[a-zA-Z-\s]+$/",$name)) {
            return 1;
        }
            
        $sanitized_name = replace_allowed_characters($name);
        if (preg_match ("/^[a-zA-Z-\s]+$/",$sanitized_name)) {
            return 1;
        }
        return 0;
    }

    public function zipCodeValidation($postalcode, $country_code = 'ca') {
        if ( $country_code == 'ca' && preg_match("/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/", $postalcode))
            return true;
        else if ( $country_code == 'usa' && preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$postalcode)){
            return true;
        }
        return false;
    }

    public function EmailValidation($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '|') !== false || strpos($email, ' ') !== false)
            return false;
        // Invalidate spammy emails
        $bam_ip = '70.35.213.98';
        $blocked_ips = ['67.68.59.221', '70.53.255.24', '70.53.255.24'];
        if ( strpos($email, "@commuccast.com") !== false ||
             strpos($email, "@distromtl.com") !== false ||
             strpos($email, "@ispqc.com") !== false ||
             strpos($email, "@xferdirect.com") !== false ||
             in_array(getIPaddress(), $blocked_ips)
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

    /*
     * Check token if it's coming from a good source
     */

    public function CheckCsrfToken($csrfToken) {
        return true; //Verify this below code later... 
        $session = \Drupal::service('session');
        $csrfToken_real = $session->get('csrfToken');

        if (trim($csrfToken) == '')
            return false;

        if ($csrfToken_real === $csrfToken)
            return true;

        return false;
    }

    /*
     * create token to send back to a form
     */

    public function CreateCsrfToken() {
        $session = \Drupal::service('session');
        //$csrfToken = openssl_random_pseudo_bytes(100);
        $csrfToken = uniqid() . uniqid() . uniqid() . uniqid() . uniqid() . uniqid();
        $session->set('csrfToken', $csrfToken);

        return $csrfToken;
    }

    function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
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

    function validateDateExpirated($tokendate) {
        $hours = 24;
        $lastdate = date('Y-m-d H:i:s', (strtotime(sprintf("-%d hours", $hours))));
        if ($lastdate > $tokendate)
            return true; //Expirated

        return false;
    }

    function searchProvince($postalcode) {

        //get Province and City from 'bampostalcode' module
        $pcodearr = bampostalcode_getProvCity($postalcode);

        if ($pcodearr) {
            $city = $pcodearr['city'];
            $province = $pcodearr['province_abbr'];
        } else {
            $city = 'NA';
            $province = getProvinceFromPCode($postalcode);
        }

        $this->city = $city;
        $this->province = $province;
    }

    function validateSource($source) {
        $sourcetmp = explode("?",$source);
        if (count($sourcetmp) > 0)  $source = $sourcetmp[0];

        if (!$this->getRegSource($source)) {
            $source = 'tastyrewards';
            $this->getRegSource($source);
        }
        return $source;
    }

    function getRegSource($source) {
        $regsource = \Drupal::service('entity_type.manager');
        $query = $regsource->getStorage('node')->getQuery()->condition('status', 1)->condition('type', 'registration_source')->condition('field_source_uri', $source, '=');
        $nids = $query->execute();

        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

        if (count($nids) == 0) {
            return false;
        }


        $node = \Drupal\node\Entity\Node::load(reset($nids));
        if ($node) {
            $node->getTranslation($langcode);
            $title_field = $node->getTranslation($langcode)->field_ccid->value;

            $sourcearr = array('source_uri' => $node->getTranslation($langcode)->field_source_uri->value,
                'ccid' => $node->getTranslation($langcode)->field_ccid->value,
                'source_domain' => $node->getTranslation($langcode)->field_source_domain->value
            );
            $this->source = $sourcearr;
            return true;
        }
        return false;
    }

    function searchUserbyToken($token) {


        $query = \Drupal::entityQuery('user')
                ->condition('field_activationtoken', $token);

        $result = $query->execute();

        $users_ids = array_keys($result);
        if (isset($users_ids[0])) {
            // $this->currentuser = user_load($users_ids[0]);
            $this->currentuser = \Drupal\user\Entity\User::load($users_ids[0]);
            return true;
        }

        return false;
    }

    function CreateDateDropdown($date = null) {

        $dd = 0;
        $mm = 0;
        $yy = 0;

        $months = [t("January"), t("February"), t("March"), t("April"), t("May"), t("June"), t("July"), t("August"), t("September"), t("October"), t("November"), t("December")];

        if ($date) {
            $newdate = \DateTime::createFromFormat("Y-m-d", $date);
            $dd = $newdate->format("d");
            $mm = $newdate->format("m");
            $yy = $newdate->format("Y");
        }

        //dropdown day
        $dayoption = '<option value="">' . t('Day') . '</option>';
        for ($i = 1; $i <= 31; $i++) {
            $tmp = ($i == $dd) ? 'selected' : '';
            $dayoption = $dayoption . '<option value="' . substr('0' . $i, -2) . '"' . $tmp . '>' . $i . '</option>';
        }

        //dropdown month
        $monthoption = '<option value="">' . t('Month') . '</option>';
        for ($i = 1; $i <= 12; $i++) {
            $tmp = ($i == $mm) ? 'selected' : '';
            $monthoption = $monthoption . '<option value="' . substr('0' . $i, -2) . '"' . $tmp . '>' . $months[$i - 1] . '</option>';
        }

        //dropdown year
        $yearoption = '<option value="">' . t('Year') . '</option>';
        $currentyear = date("Y") - 13;
        for ($i = $currentyear; $i > $currentyear - 100; $i--) {
            $tmp = ($i == $yy) ? 'selected' : '';
            $yearoption = $yearoption . '<option value="' . $i . '"' . $tmp . '>' . $i . '</option>';
        }

        return array('dayoption' => $dayoption, 'monthoption' => $monthoption, 'yearoption' => $yearoption);
    }

    function RemoveProfileAddresses($addresses) {

        foreach($addresses as $k => $address) {
                if($address['addressType'] == "PROFILE_ADDRESS") {
                    unset($addresses[$k]);
                }
        }            
        return $addresses;
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_editaddress_callback(Request $request) {

        $post['id']         = trim($request->request->get('address-id'));
        $post['firstname']  = trim($request->request->get('firstname_adr'));
        $post['lastname']   = trim($request->request->get('lastname_adr'));
        $post['address1']   = trim($request->request->get('address1_adr'));
        $post['address2']   = trim($request->request->get('address2_adr'));
        $post['city']       = trim($request->request->get('city_adr')); 
        $post['state']   = trim($request->request->get('state_adr'));
        $post['zip']        = strtoupper(trim($request->request->get('zip_adr')));
        $post['primaryAddr'] = trim($request->request->get('primaryAddr_adr'));
        
        
        $post['language']   = get_current_langcode();

        $errors = $this->validateAddress($post);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';
        $route_options = ['absolute' => true];

        if (count($errors) > 0) {
            $status = FALSE;
        } else {
            //saving the data calling OCH
            
            // OCH update both CAN and US members
            
            $sso_service = \Drupal::service('services.sso');
            $token =  $sso_service->get_consumer_token();
            //@TODO: call pimcore api for shipping address creation/update
            // leave OCH api call as backup in case pimcore api fails
            // $pimcore_obj = new PimcoreApi();
            // $pimcore_obj->setAccessToken($token);
            // $success = $pimcore_obj->createOrUpdateAddress($post);

            $success = $sso_service->pimcoreCreateOrUpdateAddress($post);
            if ($success ){
                $status = TRUE;
                $route = Url::fromRoute('pepsibam.updateprofile', [], ['absolute' => true])->toString();
            } else {
                if ($post['id'] == 'new')
                    $ochuser =  $sso_service->AddAddressOchUser($user = null, $address = $post);
                else{
                    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
                    $ochuser =  $sso_service->UpdateAddressOchUser($user, $address = $post);
                }
    
    
                if (!$ochuser){
                    $status = FALSE;
                    $errors['form'] = t('Oops, there is an issue. Please try again.');
                    $route = '';                        
                }
                else{
                    $status = TRUE;
                    $route = Url::fromRoute('pepsibam.updateprofile', [], ['absolute' => true])->toString();
                }
            }

        }

        $return = array('status' => $status, 'route' => $route, 'errors' => $errors, 'token' => isset($csrfToken)?$csrfToken:'');

        return new JsonResponse($return);
    }    


/**
     * {@inheritdoc}
     */
    public function ajax_removeaddress_callback(Request $request) {

        $post['id']         = trim($request->request->get('address-id'));
        
        $post['language']   = get_current_langcode();

        //$errors = $this->validateAddress($post);
        //\Doctrine\Common\Util\Debug::dump($errors);
        //exit;
        $route = '';
        $route_options = ['absolute' => true];
        $sso_service = \Drupal::service('services.sso');

        $token =  $sso_service->get_consumer_token();
        // $pimcore_obj = new PimcoreApi();
        // $pimcore_obj->setAccessToken($token);
        // $success = $pimcore_obj->deleteAddress($post['id']);
        $success = $sso_service->pimcoreDeleteAddress($post['id']);

        if ($success){
            $status = TRUE;
            $route = Url::fromRoute('pepsibam.updateprofile', [], ['absolute' => true])->toString();
        } else {
            // In case the pimcore API call fails we fall back to OCH api
            // OCH update both CAN and US members
           $ochuser =  $sso_service->RemoveAddressOchUser($post['id']);
           if (!$ochuser){
                   $status = FALSE;
                   $errors['form'] = t('Oops, there is an issue. Please try again.');
                   $route = '';                        
           }
           else{
                   $status = TRUE;
                   $route = Url::fromRoute('pepsibam.updateprofile', [], ['absolute' => true])->toString();
           }
        }
                


        $return = array('status' => $status, 'route' => $route, 'errors' => isset($errors)?$errors:[], 'token' => isset($csrfToken)?$csrfToken:'');

        return new JsonResponse($return);
    }      

    public function validateAddress($post) {

        //Check the CsrfToken
        $error = array();

        /*
        $post['id']         = trim($request->request->get('address-id'));
        $post['firstname']  = trim($request->request->get('firstname_adr'));
        $post['lastname']   = trim($request->request->get('lastname_adr'));
        $post['address1']   = trim($request->request->get('address1_adr'));
        $post['address2']   = trim($request->request->get('address2_adr'));
        $post['city']       = trim($request->request->get('city_adr')); 
        $post['state']   = trim($request->request->get('state_adr'));
        $post['zip']        = trim($request->request->get('zip_adr'));
        $post['language']   = get_current_langcode();
        */

        $country_code = (isset($post['language']) && ($post['language'] == 'en-us' || $post['language'] == 'es-us' ))? 'usa': 'ca';

        //firstname val
        if (!$this->userNamesValidation($post['firstname']))
            $error['firstname'] = t('Please enter a valid first name');

        //lastname val
        if (!$this->userNamesValidation($post['lastname']))
            $error['lastname'] = t('Please enter a valid last name');

        if (trim($post['city'])=='')
            $error['city'] = t('Please enter a valid city');

        if (trim($post['state'])=='')
            $error['state'] = t('Please enter a valid State');

        if (!$this->zipCodeValidation($post['zip'], $country_code)) {
                $error['zip'] = t('Please enter a valid postal code');
        }


        return $error;
    }    

    public function getUSStates(){

        return [
		    'AL'=>'Alabama',
		    'AK'=>'Alaska',
		    'AZ'=>'Arizona',
		    'AR'=>'Arkansas',
		    'CA'=>'California',
		    'CO'=>'Colorado',
		    'CT'=>'Connecticut',
		    'DE'=>'Delaware',
		    'DC'=>'District of Columbia',
		    'FL'=>'Florida',
		    'GA'=>'Georgia',
		    'HI'=>'Hawaii',
		    'ID'=>'Idaho',
		    'IL'=>'Illinois',
		    'IN'=>'Indiana',
		    'IA'=>'Iowa',
		    'KS'=>'Kansas',
		    'KY'=>'Kentucky',
		    'LA'=>'Louisiana',
		    'ME'=>'Maine',
		    'MD'=>'Maryland',
		    'MA'=>'Massachusetts',
		    'MI'=>'Michigan',
		    'MN'=>'Minnesota',
		    'MS'=>'Mississippi',
		    'MO'=>'Missouri',
		    'MT'=>'Montana',
		    'NE'=>'Nebraska',
		    'NV'=>'Nevada',
		    'NH'=>'New Hampshire',
		    'NJ'=>'New Jersey',
		    'NM'=>'New Mexico',
		    'NY'=>'New York',
		    'NC'=>'North Carolina',
		    'ND'=>'North Dakota',
		    'OH'=>'Ohio',
		    'OK'=>'Oklahoma',
		    'OR'=>'Oregon',
		    'PA'=>'Pennsylvania',
		    'RI'=>'Rhode Island',
		    'SC'=>'South Carolina',
		    'SD'=>'South Dakota',
		    'TN'=>'Tennessee',
		    'TX'=>'Texas',
		    'UT'=>'Utah',
		    'VT'=>'Vermont',
		    'VA'=>'Virginia',
		    'WA'=>'Washington',
		    'WV'=>'West Virginia',
		    'WI'=>'Wisconsin',
		    'WY'=>'Wyoming',
            'AS'=>'American Samoa',
            'GU'=>'Guam',
            'MP'=>'Northern Mariana Islands',
            'PR'=>'Puerto Rico',
            'UM'=>'United States Minor Outlying Islands',
            'VI'=>'Virgin Islands'

        ];

    }

}
