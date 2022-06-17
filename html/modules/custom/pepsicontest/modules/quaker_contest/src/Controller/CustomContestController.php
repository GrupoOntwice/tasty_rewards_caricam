<?php

/**
 * @file
 */

namespace Drupal\quaker_contest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
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

use Drupal\pepsicontest\ContestValidator;
use Drupal\pepsicontest\ContestWinnerPicker;

class CustomContestController extends ContestController {

    protected $contestnode;
    protected $user;
    protected $source;
    protected $microsite_prefix = 'microsite.';

    protected $mail;
    protected $postalcode;
    protected $city;
    protected $province;
    protected $questiontype;
    protected $postanswers;
    protected $postanswersdefault;
    protected $changedpcode;

    protected $allowed_wins_per_user;

    protected $contestopened;
    protected $contestclosed;
    protected $availableforuser = false;

    protected $registeragain;
    protected $multiple_entries = false;
    protected $num_entries = 0;
    protected $is_member = false;

    /**
     * {@inheritdoc}
     */
    public function index(Request $request) {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        //Check if coming with a token from Marketo for bonus points
        $session = \Drupal::service('session');
        die(" --- --- ");
    }

    private function redirect_lang_route($contest, $route){
        $params = [];
        if (!empty($contest))
            $params = ['contest' => $contest];

        return new RedirectResponse(\Drupal\Core\Url::fromRoute($route, $params)->toString());
    }

    private function translatedUrl($route_name, $params){
        $langprefix = get_current_langcode();
        $trans_langprefix = $langprefix == 'en-ca'? 'fr-ca': 'en-ca';
        $translated_route = str_replace($langprefix, $trans_langprefix, $route_name);
        $url = Url::fromRoute($translated_route, $params);
        if (empty($url))
            return '';

        $url = str_replace($langprefix, $trans_langprefix, $url->toString()) ;
        return $url;
    }


    public function landing(Request $request, $contest) {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        $data = [];
        $post = [];
        if (!$this->getContestId($contest)) {
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        // $session_manager = \Drupal::service('session_manager');
        // $data['has_session'] = 0;
        // if ($session_manager->isStarted()){
        //     $data['has_session'] = 1;
        //     debug_var($session_manager->getId());
        //     // $session_manager->destroy();
        //     die("   active session");
        // } else {
        //     $session_manager->destroy();
        // }


        $env =  \Drupal\Core\Site\Settings::get("environment");
        if ($env == "staging" ){
            // \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("export_winners_csv", $hours = 24);
        }

        $data['has_age_gate'] = 1;
        $node = $this->contestnode->getTranslation($langcode);
        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $data['trans_url'] = $this->translatedUrl($route_name, ['contest' => $contest] );
        $data['trans_lang'] = $langcode == 'en'? "Français" : "English";

        $data['time_to_launchday'] = $this->getTimeToLaunchDate();
        if (!empty($data['time_to_launchday'] )){
            $data['contest_opendate'] = $data['time_to_launchday']['open_date'];
        }


        $data['is_comingsoon'] = 0;
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        // @TODO: Remove this when comingsoon ends
        // if ($contest_type == 'hockey') {
            // $data['is_comingsoon'] = 1;
        // }
        // $this->isComingSoon();
        if ( $request->query->has('comingsoon') || $this->isComingSoon() ){
            $data['is_comingsoon'] = 1;
            $data['comingsoon_header'] = t('Grab Snacks. Win† Swag Contest is coming soon! ');
            $data['comingsoon_paragraph'] = t('Stay tuned: our latest contest is about to launch, featuring some of our most stylish prizes ever! <br> Come back on June 14!');
            if ($contest_type == 'hockey'){
                $data['videos_landing'] = get_recipes_with_videos('quaker', 2);
            }
        }

        if($contest_type === 'nintendo' || $contest_type === 'nintendo2'){
            $nintendo_end_date = new  \DateTime("2022-05-15 23:59:59");
            $date_now = new \DateTime(date('Y-m-d H:i:s'));
            $interval = $date_now->diff($nintendo_end_date);
            $data['days_til_end'] = $interval->days;

            $template = 'custom_landing_template';
            $data['carousel'] = get_contest_banner('contest_nintendo');
        } elseif ($this->isCustomContest()){
            $template = 'custom_landing_template';
        }

        if ($contest_type == 'cheetos' && $data['is_comingsoon'] == 0){
            // There is no landing page for cheetos
            // so we redirect to the signup form
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".custom-quaker.contest.signup",['contest'=> $contest] )->toString());
        }

        if ($contest_type == 'cheetos' && $request->query->has('comingsoon')){
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".custom.contest.comingsoon", array('contest'=>$contest))->toString());
        }

        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;

        $data['langcode'] = $langcode;
        $data['contesturi'] = $contest;
        $data['lang_prefix'] = $lang_prefix;
        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['provinces_options'] = $this->CreateProvinceDropdown();
        $data['age_provinces_options'] = $this->CreateProvinceDropdown();
        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;
        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/officialrules";
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        $data['contest_type'] = $contest_type;
        $data['signup_link'] = Url::fromRoute($lang_prefix . '.custom-quaker.contest.signup', ['contest' => $contest])->toString();
        // $data['register_link'] = Url::fromRoute($lang_prefix . '.custom-quaker.contest.register', ['contest' => $contest])->toString();

        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/faq";

        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";

      $data['prizes'] = [];

        if ($this->hasPrize($node)){
            $entities = $node->field_contest_prizes->referencedEntities();
            $prizes = [];
            // foreach ($entities as $key => $prize_node) {
            //     $img_url = "";
            //     if (!empty($prize_node->field_prize_image->target_id)){
            //         $img = \Drupal\file\Entity\File::load($prize_node->field_prize_image->target_id);
            //         $img_url = file_create_url( $img->getFileUri() );
            //     }

            //     $prizes[] = [
            //         'title' => $prize_node->getTranslation($langcode)->field_subtitle->value,
            //         'body' => $prize_node->getTranslation($langcode)->body->value,
            //         'img_url' => $img_url,
            //     ];
            // }
            $data['prizes'] = $prizes;
        }

        if ($request->getMethod()=='POST'){
            $data['submitted'] = 1;
        }

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    public function comingSoon(Request $request, $contest){
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        $data = [];
        $post = [];
        if (!$this->getContestId($contest)) {
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }


        $node = $this->contestnode->getTranslation($langcode);
        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $data['trans_url'] = $this->translatedUrl($route_name, ['contest' => $contest] );
        $data['trans_lang'] = $langcode == 'en'? "Français" : "English";


        $data['is_comingsoon'] = 0;
        // $this->isComingSoon();
        if ( $request->query->has('comingsoon') || $this->isComingSoon() ){
            $data['is_comingsoon'] = 1;
            $data['comingsoon_header'] = t('Grab Snacks. Win† Swag Contest is coming soon! ');
            $data['comingsoon_paragraph'] = t('Stay tuned: our latest contest is about to launch, featuring some of our most stylish prizes ever! <br> Come back on June 14!');
        }

        $template = 'custom_comingsoon_template';
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;


        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;

        $data['langcode'] = $langcode;
        $data['contesturi'] = $contest;
        $data['lang_prefix'] = $lang_prefix;
        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['provinces_options'] = $this->CreateProvinceDropdown();
        $data['age_provinces_options'] = $this->CreateProvinceDropdown();
        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;
        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/officialrules";
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        $data['contest_type'] = $contest_type;
        $data['signup_link'] = Url::fromRoute($lang_prefix . '.custom-quaker.contest.signup', ['contest' => $contest])->toString();
        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/faq";

        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";;




        return array(
            '#theme' => $template,
            '#data'  => $data,
        );

    }


    public function cheetosGame(Request $request, $contest, $playerid) {
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        $data = [];
        $post = [];
        if (!$this->getContestId($contest)) {
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        if (is_numeric($playerid)){
            $data['for_fun'] = 1;
        } else {
            $data['for_fun'] = 0;
        }

        $data['contest_uri'] = $this->contestnode->getTranslation($langcode)->field_contest_uri->value;
        $data['is_winner'] = 0;
        $data['redirect'] = \Drupal\Core\Url::fromRoute($lang_prefix . ".custom.contest.confirmation", ['contest'=>$contest])->toString();
        $user_info = $this->getContestSession('cheetos', $playerid);
        $data['redirect'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/confirm";
        if (!empty($user_info)){
            // $ $this->setContestSession('cheetos', $player_id, $confirmation_url);
            $data['is_winner'] = 1;
            // @TODO: only send uid & firstname as query string
            $data['redirect'] = $user_info['confirmation_url'] . "?uid=" . $user_info['uid'];
            $data['firstname'] = $user_info['firstname'];

        }
        $data['landing_page'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/landing";

        $node = $this->contestnode->getTranslation($langcode);
        $route_name = \Drupal::routeMatch()->getRouteName();
        // if ($contest != $data['contest_uri']){
        //     $route_name = $lang_prefix . substr($route_name, 5);
        //     return $this->redirect_lang_route($data['contest_uri'], $route_name);
        // }

        $data['second_version'] = 0;
        if ($playerid == 2){
            $data['second_version'] = 1;
        }

        $data['is_comingsoon'] = 0;
        // $this->isComingSoon

        $template = 'cheetos_playgame';
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;


        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;

        $data['langcode'] = $langcode;
        $data['contesturi'] = $contest;
        $data['lang_prefix'] = $lang_prefix;
        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['provinces_options'] = $this->CreateProvinceDropdown();
        $data['age_provinces_options'] = $this->CreateProvinceDropdown();
        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;
        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/officialrules";
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        $data['contest_type'] = $contest_type;
        $data['signup_link'] = Url::fromRoute($lang_prefix . '.custom-quaker.contest.signup', ['contest' => $contest])->toString();
        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/faq";

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    public function ajaxConfirmationEmail(Request $request) {
        // $base64_claim = $request->query->get('claim');
        // $claim_url = base64_decode($base64_claim);
        // $firstname = $request->query->get('firstname');
        // send_contest_email($user->mail->value, $claim_url, $firstname, $prizename);


        // $base64_claim = trim($request->request->get('claim'));
        $firstname = trim($request->request->get('firstname'));
        $prizename = trim($request->request->get('prize'));
        // $base64_email = rtrim($request->request->get('uid'), '%3D');
        // $base64_email = str_replace("%3D", '', $base64_email);
        // $email = base64_decode($base64_email);
        // $claim_url = base64_decode($base64_claim);
        // @TODO: Add $contest_type
        $email = trim($request->request->get('email'));
        $claim_url = trim($request->request->get('claim'));
        send_contest_email($email, $claim_url, $firstname, $prizename);
        log_var($claim_url, "sending email to $email  $firstname about $prizename");
        $success = 1;
        $status = 1;
        $return = array('status' => $status, 'success' => $success);

        return new JsonResponse($return);
    }


    public function ajaxMathChallenge(Request $request) {
        $is_correct = 0;
        $response = trim($request->request->get('answer'));
        $enterdate = trim($request->request->get('enterdate'));
        $contest_id = trim($request->request->get('nid'));
        $uid = trim($request->request->get('uid'));
        $question_id = trim($request->request->get('question_id'));

        $challenge = $this->getMathChallenge($contest_id);
        $answers = $challenge['answers'];
        $email = trim( base64_decode($uid) );
        $last_char = substr($email, -1);
        if (!preg_match('/[a-zA-Z]/', $last_char)){
            $email = substr($email, 0, -1);
        }


        if ($answers[$question_id] == $response){
            $is_correct = 1;
        } else {
            // @TODO: make sure this means the user loses the prize for good
            $query = \Drupal::database()->update('pepsicontest_winners');
            $query->fields([
                'claimed' => -1,
              ])
               ->condition('contest_id', intval($contest_id) )
              ->condition('enterdate', $enterdate )
              ->condition('email', $email );
             $success = $query->execute();
             //

        }

        $status = 1;
        $return = array('status' => $status, 'correct' => $is_correct);

        return new JsonResponse($return);

    }


    public function isContestParticipantEmail($email, $contest_id){
        $sql =  " SELECT count(*) as cnt FROM pepsicontest_reg_contest
                    WHERE email = '$email'
                    and contest_id = $contest_id ";
        $query = \Drupal::database()->query($sql);
        $exist = 0;
        try{
            $results = $query->fetchAll();
            if (!empty($results)){
                if( $results[0]->cnt > 0 )
                    $exist = 1;
            }
        } catch(\Exception $e){
            log_var($sql, "Find email query could not be executed ");
        }
        return $exist;
    }

    public function ajaxClaim(Request $request) {
        $message = '';

        $firstname = strtolower(trim($request->request->get('firstname')));
        $lastname = strtolower( trim($request->request->get('lastname')) );
        $email = strtolower(trim($request->request->get('email')) );
        $enterdate = trim($request->request->get('enterdate'));
        $contest_id = trim(strtolower($request->request->get('contest_id')));
        $langcode = trim($request->request->get('langcode'));

        $is_valid = 0;

        if (!$this->isContestParticipantEmail($email, $contest_id) ){
            $message = t('Sorry, the email address could not be found. Please double-check that you entered the correct information. ');
            $env =  \Drupal\Core\Site\Settings::get("environment");

            if ($email == 'valid@claim.ca' && ( $env == 'dev' || $env == 'staging') ){
                $is_valid = 1;
            }
        }else {
            $claim_status = $this->is_valid_prize_claim($enterdate, $email, $contest_id );
            if (!empty($claim_status)){
                if ($claim_status['expired'] == 1){
                    $message = t('Sorry, you have not claimed your prize within the notification period and the prize has been forfeited in accordance with the Official Rules');
                    if ($langcode == 'fr'){
                        $message = 'Désolé, vous n’avez pas réclamé votre prix dans les délais prévus. Votre prix a donc été annulé conformément au Règlement officiel';
                    }

                }elseif ($claim_status['claimed'] == 1 ){
                    $message = t('The information you entered is valid, but our records indicate that the winner registration process has already been completed');
                    if ($langcode == 'fr'){
                        $message = "L'information que vous avez saisie est valide, mais nos dossiers indiquent que le processus d'inscription du gagnant a déjà été complété";
                    }

                }elseif ($claim_status['claimed'] == -1 ){
                    $message = t('Unfortunately, your answer to the mathematical skill-testing question is incorrect and in accordance with the Official Rules, you are no longer eligible to win the prize.');
                    if ($langcode == 'fr'){
                        $message = "Malheureusement, votre réponse à la question réglementaire d’arithmétique est incorrecte. Selon le Règlement officiel, vous n’êtes plus admissible pour gagner le prix.";
                    }

                }elseif($claim_status['claimed'] == 0){
                    if (!empty($claim_status['participant'])){
                        $_firstname = strtolower($claim_status['participant']['firstname']);
                        $_lastname = strtolower($claim_status['participant']['lastname']);
                        if ($firstname == $_firstname && $lastname == $_lastname){
                            $is_valid = 1;
                        } else {
                            if ($firstname != $_firstname){
                                $message = t('Please enter a valid First name');
                            }else{
                                $message = t('Please enter a valid Last name');
                            }
                        }
                    }
                }

            } else {
                $message = t('An unknown error has occurred. Please contact contest@tastyrewards.ca for assistance. ');

            }
        }

        // $sql =  " SELECT count(*) as cnt FROM pepsicontest_reg_contest
        //             WHERE email = '$email' and first_name = '$firstname'
        //             and last_name = '$lastname' and enterdate = '$enterdate'
        //             and contest_id = $contest_id ";
        // $query = \Drupal::database()->query($sql);
        // try{
        //     $results = $query->fetchAll();
        //     if (!empty($results)){
        //         if( $results[0]->cnt > 0 )
        //             $is_valid = 1;
        //     }
        // } catch(\Exception $e){
        //     log_var($sql, "Contest claim query not executed properly");
        // }
        $status = 1;
        $return = array(
            'status' => $status,
            'message' => $message,
            'valid' => $is_valid
        );

        return new JsonResponse($return);
    }


    public function ajaxEligibility(Request $request) {

        $post = [];
        $lang_prefix = trim($request->request->get('lang_prefix'));
        $langcode = trim($request->request->get('langcode'));
        $lang_object = \Drupal::languageManager()->getLanguage($langcode);

        $post['province'] = trim($request->request->get('province'));
        $post['bday'] = trim($request->request->get('bday'));
        $post['contest'] = trim(strtolower($request->request->get('contest')));
        $post['contest_type'] = trim(strtolower($request->request->get('contest_type')));
        $url = Url::fromRoute('module_new', []);

        $is_legal = $this->isLegalAge($post['province'], $post['bday'], $post['contest_type']);
        $status = 1;
        $url = Url::fromRoute($lang_prefix .'.custom-quaker.contest.signup',
            ['contest' => $post['contest']], ['language' => $lang_object]
        );
        $redirect_url = $url->toString();
        $return = array('status' => $status, 'legal' => $is_legal, 'redirect' => $redirect_url);

        if ($is_legal){
            $session = $request->getSession();
            $session->set('contest_age_validated', 1);
        }

        return new JsonResponse($return);
    }

    /**
     * Overriding ContestController signup method
     * @param  Request $request [description]
     * @param  $contest :
     * @return [type]           [description]
     */
    public function signup(Request $request, $contest) {
        // die(" signup");
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $lang_prefix = get_current_langcode();

        //check if contest exists
        if (!$this->getContestId($contest)) {
            // @TODO: create the contest not found page.
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            die("Contets not found ");
        }
        $node = $this->contestnode;  // set by getContestId()
        $node->getTranslation($langcode);

        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $data['has_age_gate'] = 1;

        // $data['game_url'] = "/" . $lang_prefix . "/contest/$contest/game/1";
        $data['game_url'] = $this->getCheetosGameUrl($contest, 1);

        $data['trans_url'] = $this->translatedUrl($route_name, ['contest' => $contest] );
        $data['trans_lang'] = $langcode == 'en'? "Français" : "English";

        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;


        $data['subtitle'] = $node->getTranslation($langcode)->field_title->value;
        $data['body'] = $node->getTranslation($langcode)->body->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;

        $this->allowed_wins_per_user = $node->field_total_wins_per_account->value;

        // $data['provinces_options'] = $this->CreateProvinceDropdown();

        $contest_id = $node->id();
        $openingdate = $node->getTranslation($langcode)->field_opening_date->date;
        $closingdate = $node->getTranslation($langcode)->field_closing_date->date;
        $contestentry = $node->getTranslation($langcode)->field_contest_entry->value; //1=> Only once, 2=> One per day



        $data['contesturi'] = $contest;
        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en. "/officialrules";

        $data['colorTheme'] = $node->getTranslation($langcode)->field_contest_color_theme->value;
        $data['mobileColorTheme'] = $node->getTranslation($langcode)->field_mobile_color_theme->value;
        // $data['bannerTextPosition'] = $node->getTranslation($langcode)->field_contest_banner_position->value;

        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['provinces_options'] = $this->CreateProvinceCodeDropdown();
        $data['age_provinces_options'] = $this->CreateProvinceDropdown();

        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";

        //Check if contest is still opened
        $status = $this->getContestStatus($openingdate, $closingdate);
        $concours = $lang_prefix == 'fr-ca'? 'concours' : 'contest';

        $data['faq_link'] = "/" . $lang_prefix . "/$concours/" . $data['contest_uri'] . "/faq";
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
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        // @TODO: Remove this when comingsoon ends
        // if ($contest_type == 'hockey') {
            // return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".custom.contest.landing", array('contest'=>$contest))->toString());
        // }

        $data['contest_type'] = $contest_type;
        if ($contest_type === 'quaker'){
            $template = 'quaker_contest_signup_template';
        }elseif ($this->isCustomContest() ){
            $template = 'custom_contest_signup_template';
        } else {
            $template = 'lays_contest_signup_template';
        }
        // $template = 'pepsicontest_register_template';
        $alreadyregistered = false;
        $data['geotargeting'] = false;
        $data['clmn'] = $request->get('clmn');
        //When there is a user's session
        //Check if user is loged in
        if (!$user_id = \Drupal::currentUser()->id()) {
            $data['is_loggedin'] = 0;
            $template = 'quaker_contest_register_template';
            if ($contest_type == 'lays'){
                $template = 'lays_contest_register_template';
            }elseif ( $this->isCustomContest() ){
                // $template = 'custom_contest_register_template';
                $template = 'custom_contest_signup_template';
            }
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
            // $status = 0; // user no registered
            if (!$this->isCustomContest() ){
                if ($this->isUserRegistered($contest_id,$user_id,$contestentry )){
                    if ($this->registeragain) {
                        $status = 1; // already Registered, but could register again today
                    }
                    else {
                        $status = 4; // already Registered and can't participate at least today o no anymore depend of the type of contest
                        $message = 'no more contest entries today';
                        if ($contestentry == '7'){
                            $message = t('You can only participate 7 times per week');
                        } elseif ($contestentry == '3'){
                            $message = t('You can only participate three times a day');
                        }
                        $data['already_registered_msg'] = $message;
                    }
                }

            }

            //Check if Geo targetting contest
            if (!$this->GeoTargetFilter()){
                $data['geotargeting'] = true;
            }
       }


        $data['submitted'] = 0;
        $data['entry_saved'] = 0;
        //Check if this is coming from a POST
        if ($request->getMethod()=='POST'){
            $data['submitted'] = 1;
            $data['has_age_gate'] = 0;
            //data validation
            $post['casl'] = $request->get('casl');
            $post['newregister'] = $request->get('newregister');
            $post['upccode'] = $request->get('upccode');
            $post['sourceid'] = $node->getTranslation($langcode)->field_source_id->value;

            $post['firstname'] = trim(isset($post['firstname'])?$post['firstname']:$request->request->get('firstname'));
            $post['lastname'] = trim(isset($post['lastname'])?$post['lastname']:$request->request->get('lastname'));
            $post['postalcode'] = trim(isset($post['postalcode'])?$post['postalcode']:$request->request->get('postalcode'));
            $post['postalcode'] = str_replace(' ', '', $post['postalcode']);
            $post['email'] = trim(strtolower(isset($post['email'])?$post['email']:$request->request->get('email')));
            if ($contest_type == "quaker"){
                $post['recipeTitle'] = trim(strtolower(isset($post['recipeTitle'])?$post['recipeTitle']:$request->request->get('recipeTitle')));
            } elseif($contest_type === "lays"){
                $post['lays-banner'] = trim(strtolower(isset($post['lays-banner'])?$post['lays-banner']:$request->request->get('lays-banner')));
            } elseif( in_array($contest_type, ['nintendo', 'nintendo2']) ){
                $post['optin_maj'] = $request->request->get('optin_maj') == 'on'? 1 : 0;
                $post['province'] = trim(isset($post['province'])?$post['province']:$request->request->get('province'));
            } elseif($contest_type === "cheetos" ){
                $post['optin_maj'] = $request->request->get('optin_maj') == 'on'? 1 : 0;
                $post['province'] = trim(isset($post['province'])?$post['province']:$request->request->get('province'));
                $post['cheetos_roc'] = empty($request->request->get('cheetos-roc-submit')) ? 0:1;
                $post['cheetos_qc'] = empty($request->request->get('cheetos-qc-submit')) ? 0:1;
            } elseif($contest_type === "grabsnack"){
                $post['optin_maj'] = $request->request->get('optin_maj') == 'on'? 1 : 0;
                $post['optin_maj2'] = $request->request->get('optin_maj2') == 'on'? 1 : 0;
                $post['province'] = trim(isset($post['province'])?$post['province']:$request->request->get('province'));
            } elseif($contest_type === "hockey"){
                $post['province'] = trim(isset($post['province'])?$post['province']:$request->request->get('province'));
                $post['optin_maj'] = $request->request->get('optin_maj') == 'on'? 1 : 0;
            }
            // $post['confirm_email'] = trim(strtolower($request->request->get('confirm_email')));

            //$optin_value = $request->request->get('optin') == 'on'? 1 : 0;
            $post['optin']      = $request->request->get('optin') == 'on'? 1 : 0;
            $post['consent']    = $request->request->get('consent') == 'on'? 1 : 0;
            $post['csrfToken'] = $request->request->get('csrfToken');
            $post['grecaptcharesponse'] = $request->request->get('grecaptcharesponse_contest');

            $post['uploadImg'] = isset($post['uploadImg'])? trim($post['uploadImg']) : "";
            $files = null;
            if (!empty($request->files->get('uploadImg'))){
                $files = $request->files->get('uploadImg');
                $post['uploadImg'] = $files->getClientOriginalName();
            }
            $post['files'] = $files;
            $post['bday_day']   = $request->request->get('bday_day')?$request->request->get('bday_day'):'';
            $post['bday_month'] = $request->request->get('bday_month')?$request->request->get('bday_month'):'';
            $post['bday_year']  = $request->request->get('bday_year')?$request->request->get('bday_year'):'';
            $post['bday']       = $post['bday_year'] . '-' . $post['bday_month'] . '-' . $post['bday_day'];




            if ($contest_type == 'lays'){
                $source = $this->validateSource('laysSummerContest');
            } elseif($contest_type == 'nintendo'){
                $source = $this->validateSource('nintendo_vp');
            } elseif($contest_type == 'nintendo2'){
                $source = $this->validateSource('nintendo_vp');
            } elseif($contest_type == 'grabsnack'){
                $source = $this->validateSource('grabsnack');
            } elseif($contest_type == 'cheetos'){
                $source = $this->validateSource('cheetos_carnival');
            } elseif($contest_type == 'hockey'){
                $source = $this->validateSource('quakerhockeyhungry');
            } else{
                $source = $this->validateSource('tastyrewards');
            }

            if ($post['newregister'] && ! $this->isCustomContest() ) { // signup + register into contest
                $errors = $this->validateRegistration($post, $new = true, $contest_type);

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
                                    'field_marketocookie' => "quaker_contest",
                                    'field_source' => $source,
                                    'field_ccid' => $this->source['ccid'],
                                    'field_ip_address' => getIPaddress(),
                                    'field_user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255),
                                    'field_source_id' => $post['sourceid']
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
                            sfmcservices_subscribe($user, $source);
                        else
                            $user = $existing_user;
                        // sfmcservices_trigger_email_activation($user);

                        //@TODO: Determine if we need Geotracking?


                        $session = \Drupal::service('session');
                        $session->set('signupnomember','1');

                        //Check if Geo targetting contest
                        if ($this->GeoTargetFilter()){
                            //register contest

                            $this->registerUser($node, $user, $answ = null,1);
                            // assuming the image is validated
                            if ($contest_type === "quaker"){
                                $this->db_insert_recipe($user, $post['recipeTitle'], $post['uploadImg']);
                                $success = $this->saveUploadedImage($files, $post['email']);
                            } elseif($contest_type === 'lays'){
                                $this->db_insert_extra_field($user, "banner", $post['lays-banner']);

                            }
                        }
                        else{
                            $session->set('signupnomemberGeo','1'); // set seesion in order to show the right error message in thank you page
                        }

                        $langcode = get_current_langcode();
                        $current_lang = \Drupal::languageManager()->getcurrentLanguage()->getId();



                        return new RedirectResponse(\Drupal\Core\Url::fromRoute($current_lang .".pepsicontest.contest.thank-you", array('contest'=>$contest))->toString());
                    }

                }

            } elseif( $this->isCustomContest() ){
                $new = $post['newregister'];
                $new = 1;
                $errors = $this->validateRegistration($post, $new, $contest_type);
                $nb_entry = 1;
                if ($contest_type == 'nintendo2'){
                    // Get the extra entries earned if nintendo
                    $nb_entry = $this->getEntriesPerUPC($post['upccode']);
                }
                // Check if the user has participated 3 times.
                // @TODO: make sure if there is a better place to check eligibility
                $can_play = $this->checkEligibility($post['email'], $contest, $nb_entry);

                if (!$can_play){
                    $errors['email'] = t('Sorry, you have reached your daily limit. Please come back tomorrow for more chance to win.');
                }


                if (count($errors) == 0 && $can_play) {

                    //Process and register the contest
                    //create user
                    $existing_user = user_load_by_mail($post['email']);
                    if (empty($existing_user) ){
                        $saved = true;
                        try {
                            $user = User::create([
                                        'name' => $post['email'],
                                        'mail' => $post['email'],
                                        'status' => 1,
                                        'field_firstname' => $post['firstname'],
                                        'field_lastname' => $post['lastname'],
                                        'field_city' => $this->city,
                                        'field_province' => $post['province'],
                                        'field_postalcode' => $post['postalcode'],
                                        // 'field_gender' => $post['gender'],
                                        'field_bday' => $post['bday'],
                                        // 'field_fbid' => $post['fbid'],
                                        'field_optin' => $post['optin'],
                                        // 'field_source' => $source,
                                        // 'field_ccid' => $this->source['ccid'],
                                        'field_ip_address' => getIPaddress(),
                                        'field_user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255),
                                        'field_source_id' => $post['sourceid']
                            ]);

                            $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

                            $date = date('Y-m-d H:i:s');
                            $user->set("field_created_date", $date);

                            $user->set("langcode", $language);
                            $user->set("preferred_langcode", $language);
                            $user->set("preferred_admin_langcode", $language);
                            $user->save();

                            $this->user = $user; //setting user to class

                        } catch (\Exception $e) {
                            $saved = false;
                            $errors['form'] = t('Oops, there is an issue. Please try again.');
                            $user_mail = isset($post['mail'])? $post['mail'] : " ";
                            \Drupal::logger('general')->info("Could not save Tostitos contest entry by " . $user_mail, []);
                        }

                    }
                    else{
                        $user = $existing_user;
                        $this->is_member = true;
                    }
                        // sfmcservices_trigger_email_activation($user);

                        //@TODO: Determine if we need Geotracking?


                        $session = \Drupal::service('session');
                        $session->set('signupnomember','1');
                        $upc_code = '';
                        if ($post['upccode'])
                            $upc_code = $post['upccode'];

                        $this->registerUser($node, $user, $answ = null,1, $upc_code);

                        $base64_email = base64_encode(trim($user->mail->value));
                        $user_email = trim($user->mail->value);

                        if ($this->hasPrize($node)){
                            $winner_picker = new ContestWinnerPicker();
                            $draw_result = $winner_picker->draw_instant_winner($node, $post['email']);


                            if ($draw_result['is_winner']){
                                // Save the information of the winner in a DE
                                // Add the winner to the Tostitos Contest winner field

                                // $previous_winners = $node->get('field_winners')->value;

                                // $current_prize = getNextPrizeTitle($this->contestnode, $skip = true);
                                // $prize_node = $this->NextPrize($this->contestnode, $skip = true);
                                //
                                $prize_node = \Drupal\node\Entity\Node::load($draw_result['prize_nid']);

                                // $node->set('field_winners', $previous_winners . ", " . $user->get('mail')->value . "[" . $current_prize . "]");

                                // $node->save();
                                // sfmcservices_contest_winner($user, $node);
                                sfmcservices_save_winner($user, $node, $prize_node);
                                // $winner_picker->db_save_winner($this->contestnode, $post['email'], $prize_node);
                                $base64_email = base64_encode(trim($user->mail->value));
                                // $session = \Drupal::service('session');
                                $uid = $user->id();



                                $host = \Drupal::request()->getSchemeAndHttpHost();
                                $langprefix = get_current_langcode();
                                $enterdate = date('Y-m-d');
                                if ($langprefix == 'en-ca'){
                                    $claim_url = $host . "/en-ca/contest/$contest/claim?date=$enterdate&uid=" . $base64_email . "&salt=" . base64_decode(date('H:i:s') );
                                } else {
                                    $claim_url = $host . "/fr-ca/concours/$contest/reclamer?date=$enterdate&uid=" . $base64_email . "&salt=" . base64_decode(date('H:i:s') );
                                }
                                $firstname = $user->field_firstname->value;
                                $prizename = $prize_node->field_subtitle->value;

                                // sfmcservices_subscribe($user, $source);

                                $user_info = [
                                    'email' => trim($user->mail->value),
                                    'claim_url' => $claim_url,
                                    'firstname' => $firstname,
                                ];
                                $session->set('user_' . $uid, $user_info);
                                // send_contest_email($user->mail->value, $claim_url, $firstname, $prizename);
                                if (empty($existing_user))
                                    // user_delete($user->id());
                                    \Drupal::entityTypeManager()->getStorage('user')->load($user->id())->delete();

                                $url_params = [
                                    'claim' => base64_encode($claim_url),
                                    'firstname' => $firstname,
                                    'em' => $base64_email,
                                    'uid' => $uid,
                                ];

                                $langprefix = get_current_langcode();

                                if ($contest_type == 'cheetos' && $post['cheetos_roc']){
                                    $player_id = simple_hash($firstname);
                                    $confirmation_url = \Drupal\Core\Url::fromRoute("custom.contest.confirmation.winner",['contest'=>$contest, 'prize_id' => $prize_node->id() ])->toString();

                                    $user_info['uid'] = $uid;
                                    $user_info['confirmation_url'] = $confirmation_url;


                                    $this->setContestSession('cheetos', $player_id, $user_info);


                                    return new RedirectResponse(\Drupal\Core\Url::fromRoute("en-ca.custom.contest.cheetos.game",['contest'=>$contest, 'playerid' => $player_id ])->toString());

                                }

                                return new RedirectResponse(\Drupal\Core\Url::fromRoute("custom.contest.confirmation.winner",['contest'=>$contest, 'prize_id' => $prize_node->id() ], ['query' => $url_params ])->toString());
                            }

                            // sfmcservices_subscribe($user, $source);

                            if (empty($existing_user)){
                                // user_delete($user->id());
                                \Drupal::entityTypeManager()->getStorage('user')->load($user->id())->delete();
                            }

                            $langprefix = get_current_langcode();

                            $url_params = [
                                'uid' => $base64_email,
                            ];

                            if ($contest_type == 'cheetos' && $post['cheetos_roc']){
                                return new RedirectResponse(\Drupal\Core\Url::fromRoute("en-ca.custom.contest.cheetos.game",['contest'=>$contest, 'playerid' => 'run' ])->toString());

                            }

                            return new RedirectResponse(\Drupal\Core\Url::fromRoute($langprefix . ".custom.contest.confirmation", ['contest'=>$contest] , ['query' => $url_params ])->toString());

                        }


                        // sfmcservices_subscribe($user, $source);
                        if (!$this->is_member){
                            $user->delete();
                        }

                        //Check if Geo targetting contest
                        // if ($this->GeoTargetFilter()){
                        //     $this->registerUser($node, $user, $answ = null,1);
                        // }
                        // else{
                        //     $session->set('signupnomemberGeo','1'); // set seesion in order to show the right error message in thank you page
                        // }

                        // $langcode = get_current_langcode();
                        $current_lang = \Drupal::languageManager()->getcurrentLanguage()->getId();
                        $data['entry_saved'] = 1;
                        $url_params = [
                            'upc' => base64_encode($post['upccode']),
                            'uid' => $base64_email,
                            'nb' => $nb_entry,
                        ];
                        // $post['upccode']

                        $langprefix = get_current_langcode();
                        $hash = simple_hash($user_email);

                        $session->set('contest_confirm_' . $hash ,'1');

                        return new RedirectResponse(\Drupal\Core\Url::fromRoute($langprefix . ".custom.contest.confirmation", array('contest'=>$contest) , ['query' => $url_params ])->toString());

                        // return new RedirectResponse(\Drupal\Core\Url::fromRoute($current_lang .".pepsicontest.contest.thank-you", array('contest'=>$contest))->toString());
                        // return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.sorry", array('contest'=>$contest))->toString());

                }
            }
            else { // // only register to contest
                $errors = $this->validateRegistration($post, $new=false, $contest_type);

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
                    sfmcservices_subscribe($user, $source);


                    $this->registerUser($node, $user, $answer = null);
                    if ($contest_type === 'quaker'){
                        $this->db_insert_recipe($user, $post['recipeTitle'], $post['uploadImg']);
                        $success = $this->saveUploadedImage($files, $post['email']);
                    } elseif($contest_type === 'lays'){
                        $this->db_insert_extra_field($user, "banner", $post['lays-banner']);
                    }


                    $session = \Drupal::service('session');
                    $session->remove('signupnomember');
                    $session->remove('signupnomemberGeo');

                    $session->set('send_datalayer','no'); // set session in order to sending datalayer for tracking
                    $session->set('send_datalayer_optin_value',$post['optin']); // set session in order to sending datalayer for tracking

                    $langcode = get_current_langcode();


                    // return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode .".custom.contest.sorry", array('contest'=>$contest))->toString());
                    $lang_prefix = \Drupal::languageManager()->getCurrentLanguage()->getId();
                    return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix .".pepsicontest.contest.thank-you.new", array('contest'=>$contest))->toString(). "?contest=1");
                    }

                }
            }

            $data['error'] = $errors;
        } else {
            $session = $request->getSession();
            $is_legal_age = $session->get('contest_age_validated');
            $session->remove('contest_age_validated');

            // if (!$is_legal_age){
            //     return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".custom.contest.landing", ['contest' => $contest])->toString());
            // }
        }


        $session = \Drupal::service('session');

        $email = $session->get('email');
        $fbid = $session->get('fbid');
        $firstname = $session->get('firstname');
        $lastname = $session->get('lastname');
        $upccode = $session->get('upccode');


        if ($session->get('email'))
            $post["email"] = $session->get('email');
        if ($session->get('fbid'))
            $post["fbid"] = $session->get('fbid');
        if ($session->get('firstname'))
            $post["firstname"] = $session->get('firstname');
        if ($session->get('lastname'))
            $post["lastname"] = $session->get('lastname');
        if ($session->get('upccode'))
            $post["upccode"] = $session->get('upccode');

        $session->remove('email');
        $session->remove('fbid');
        $session->remove('firstname');
        $session->remove('lastname');
        $session->remove('upccode');



        $data['firstname']        = isset($post['firstname'])?$post['firstname']:'';
        $data['lastname']         = isset($post['lastname'])?$post['lastname']:'';
        $data['upccode']         = isset($post['upccode'])?$post['upccode']:'';
        $data['email']            = isset($post['email'])?$post['email']:'';
        $data['confirm_email']    = isset($post['confirm_email'])?$post['confirm_email']:'';
        $data['password']         = isset($post['password'])?$post['password']:'';
        $data['confirm_password'] = isset($post['confirm_password'])?$post['confirm_password']:'';


        $data['fbid']             = isset($post['fbid'])?$post['fbid']:'';

        $data['postalcode']       = isset($post['postalcode'])?$post['postalcode']:'';
        $data['gender']           = isset($post['gender'])?$post['gender']:'';
        $data['banner']           = isset($post['banner'])?$post['banner']:'';
        $data['province']         = isset($post['province'])?$post['province']:'';
        $data['optin']            = isset($post['optin'])?$post['optin']:0;
        $data['optin_maj']            = isset($post['optin_maj'])?$post['optin_maj']:0;
        $data['optin_maj2']            = isset($post['optin_maj2'])?$post['optin_maj2']:0;
        $data['consent']          = isset($post['consent'])?$post['consent']:0;

        $data['bdaydropdown'] = $this->CreateDateDropdown(isset($post['bday'])?$post['bday']:null);


        //contest data
        $data['status'] = $status;
        $data['title'] = $node->getTranslation($langcode)->field_title->value;
        $data['brand'] = $node->getTranslation($langcode)->field_brand->value;
        $data['description'] = $node->getTranslation($langcode)->field_contest_description->value;
        $data['tag'] = $node->getTranslation($langcode)->field_contest_tag->value;

        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;

        $data['provinces_options'] = $this->CreateProvinceCodeDropdown($data['province']);
        $data['contesturi'] = $contest;

        // Make the mobile header image mandatory?
        $mobileimagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_header_image_mobile->target_id);
        $data['headermobileimage']  = $mobileimagefile->getFileUri();


        $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_header_image->target_id);
        $data['headerimage']  = $imagefile->getFileUri();
        $user = $this->user;
        $data['user_optin'] = 0;
        if($user){
            // $data['optin']  = isset($post['optin'])?$post['optin'] : $user->get('field_optin')->value;
            $data['optin'] = !empty($user->get('field_optin')->value) ? 1 : 0;
            $data['user_optin'] = $data['optin'];
        }else{
            $data['optin']  = isset($post['optin'])?$post['optin']:0;
        }


        //get contest question
        // $data['questions'] = $this->getContestQuestion($node,$langcode);
        $data['answ'] = array();


        if (isset($post['answ'])) {
            $data['answ'] = $post['answ'];
        }

        //generating CSRF token
        $data['csrfToken'] = CreateCsrfToken();
        //Getting language and passing to twig
        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $data['lang_prefix'] = get_current_langcode();

        $data['langcode'] = get_current_langcode(false);

        $data['registeragain'] = $this->registeragain;

        $data['contestentry'] = $contestentry;

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }
    /*

    public function register(Request $request, $contest) {
        $data = [];

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $lang_prefix = get_current_langcode();

        //check if contest exists
        if (!$this->getContestId($contest)) {
            // @TODO: create the contest not found page.
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            die("Contets not found ");
        }
        $node = $this->contestnode;  // set by getContestId()
        $node->getTranslation($langcode);

        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $data['has_age_gate'] = 1;


        $data['trans_url'] = $this->translatedUrl($route_name, ['contest' => $contest] );
        $data['trans_lang'] = $langcode == 'en'? "Français" : "English";

        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;


        $data['subtitle'] = $node->getTranslation($langcode)->field_title->value;

        $this->allowed_wins_per_user = $node->field_total_wins_per_account->value;

        // $data['provinces_options'] = $this->CreateProvinceDropdown();

        $contest_id = $node->id();
        $openingdate = $node->getTranslation($langcode)->field_opening_date->date;
        $closingdate = $node->getTranslation($langcode)->field_closing_date->date;
        $contestentry = $node->getTranslation($langcode)->field_contest_entry->value; //1=> Only once, 2=> One per day



        $data['contesturi'] = $contest;
        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en. "/officialrules";

        $data['colorTheme'] = $node->getTranslation($langcode)->field_contest_color_theme->value;
        $data['mobileColorTheme'] = $node->getTranslation($langcode)->field_mobile_color_theme->value;
        // $data['bannerTextPosition'] = $node->getTranslation($langcode)->field_contest_banner_position->value;

        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['provinces_options'] = $this->CreateProvinceCodeDropdown();
        $data['age_provinces_options'] = $this->CreateProvinceDropdown();
        $contest_type = 'nintendo';
        $data['contest_type'] = $contest_type;
        $data['status'] = 1;
        $data['login_link'] = '/en-ca/contest/nintendo-microsite/signin';

        if ($request->getMethod()=='POST'){

            // @TODO: Find if the email already exists.

            $data['submitted'] = 1;
            $data['has_age_gate'] = 0;
            //data validation
            $post['casl'] = $request->get('casl');
            $post['newregister'] = $request->get('newregister');
            $post['password'] = $request->get('password');
            $post['upccode'] = $request->get('upccode');
            $post['sourceid'] = $node->getTranslation($langcode)->field_source_id->value;

            $post['firstname'] = trim(isset($post['firstname'])?$post['firstname']:$request->request->get('firstname'));
            $post['lastname'] = trim(isset($post['lastname'])?$post['lastname']:$request->request->get('lastname'));
            $post['postalcode'] = trim(isset($post['postalcode'])?$post['postalcode']:$request->request->get('postalcode'));
            $post['postalcode'] = str_replace(' ', '', $post['postalcode']);
            $post['email'] = trim(strtolower(isset($post['email'])?$post['email']:$request->request->get('email')));
            $post['email'] = $this->addEmailPrefix($post['email']);
            if($contest_type === "nintendo" ){
                $post['optin_maj'] = $request->request->get('optin_maj') == 'on'? 1 : 0;
                $post['province'] = trim(isset($post['province'])?$post['province']:$request->request->get('province'));
            }

            $post['optin']      = $request->request->get('optin') == 'on'? 1 : 0;
            $post['consent']    = $request->request->get('consent') == 'on'? 1 : 0;
            // $post['csrfToken'] = $request->request->get('csrfToken');
            // $post['grecaptcharesponse'] = $request->request->get('grecaptcharesponse_contest');





            if ($contest_type == 'nintendo'){
                $source = $this->validateSource('nintendo_vp');
            }


            if ($post['newregister'] || 1 ) {
                $errors = $this->validateRegistration($post, $new = true, $contest_type);

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
                                    'field_marketocookie' => "quaker_contest",
                                    'field_source' => $source,
                                    'field_ccid' => $this->source['ccid'],
                                    'field_ip_address' => getIPaddress(),
                                    'field_user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255),
                                    'field_source_id' => $post['sourceid']
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
                            sfmcservices_subscribe($user, $source);
                        else
                            $user = $existing_user;
                        // sfmcservices_trigger_email_activation($user);

                        //@TODO: Determine if we need Geotracking?


                        $session = \Drupal::service('session');
                        $session->set('signupnomember','1');

                        //Check if Geo targetting contest
                        if ($this->GeoTargetFilter()){
                            // saves record in the contest table
                            $this->registerUser($node, $user, $answ = null,1);

                        }
                        else{
                            $session->set('signupnomemberGeo','1'); // set seesion in order to show the right error message in thank you page
                        }

                        $langcode = get_current_langcode();
                        $current_lang = \Drupal::languageManager()->getcurrentLanguage()->getId();



                        return new RedirectResponse(\Drupal\Core\Url::fromRoute($current_lang .".pepsicontest.contest.thank-you", array('contest'=>$contest))->toString());
                    }

                }

            }

            $data['error'] = $errors;
        }

        $template = 'nintendo_register';

        $data['firstname']        = isset($post['firstname'])?$post['firstname']:'';
        $data['lastname']         = isset($post['lastname'])?$post['lastname']:'';
        // $data['upccode']         = isset($post['upccode'])?$post['upccode']:'';
        $data['email']            = isset($post['email'])?$this->removeEmailPrefix($post['email']):'';
        $data['province']            = isset($post['province'])?$post['province']:'';
        $data['postalcode']            = isset($post['postalcode'])?$post['postalcode']:'';

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );

    }

    public function addEmailPrefix($email){
        return $this->microsite_prefix . $email;
    }

    public function removeEmailPrefix($email){
        return str_replace($this->microsite_prefix, "", $email);
    }

    public function signIn(Request $request, $contest) {
        $data = [];

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $lang_prefix = get_current_langcode();

        //check if contest exists
        if (!$this->getContestId($contest)) {
            // @TODO: create the contest not found page.
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            die("Contets not found ");
        }
        $node = $this->contestnode;  // set by getContestId()
        $node->getTranslation($langcode);

        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $data['has_age_gate'] = 1;


        $data['trans_url'] = $this->translatedUrl($route_name, ['contest' => $contest] );
        $data['trans_lang'] = $langcode == 'en'? "Français" : "English";

        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;


        $data['subtitle'] = $node->getTranslation($langcode)->field_title->value;

        $this->allowed_wins_per_user = $node->field_total_wins_per_account->value;

        // $data['provinces_options'] = $this->CreateProvinceDropdown();

        $contest_id = $node->id();
        $openingdate = $node->getTranslation($langcode)->field_opening_date->date;
        $closingdate = $node->getTranslation($langcode)->field_closing_date->date;
        $contestentry = $node->getTranslation($langcode)->field_contest_entry->value; //1=> Only once, 2=> One per day



        $data['contesturi'] = $contest;
        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en. "/officialrules";

        $data['colorTheme'] = $node->getTranslation($langcode)->field_contest_color_theme->value;
        $data['mobileColorTheme'] = $node->getTranslation($langcode)->field_mobile_color_theme->value;
        // $data['bannerTextPosition'] = $node->getTranslation($langcode)->field_contest_banner_position->value;

        $data['bdaydropdown'] = $this->CreateDateDropdown();
        $data['provinces_options'] = $this->CreateProvinceCodeDropdown();
        $data['age_provinces_options'] = $this->CreateProvinceDropdown();
        $contest_type = 'nintendo';
        $data['contest_type'] = $contest_type;
        $data['status'] = 1;
        $data['session'] = [];




        if ($request->getMethod()=='POST'){

            // @TODO: Find if the email already exists.

            $data['submitted'] = 1;
            $data['has_age_gate'] = 0;
            //data validation
            $post['casl'] = $request->get('casl');
            $post['password'] = $request->get('password');

            $post['email_noprefix'] = trim(strtolower($request->request->get('email')));
            $post['email'] = $this->addEmailPrefix($post['email_noprefix']);

            $errors = $this->validateLogin($post);

            if (empty($errors)){
                // Create session
                $user = user_load_by_mail($post['email']);
                $user_profile = [
                    'email' => $user->mail->value,
                    'firstname' => $user->field_firstname->value,
                    'lastname' => $user->field_lastname->value,
                ];

                $this->startSession($user_profile);



                // @TODO: Redirect the user to confirmation page
                return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_prefix . ".custom.contest.landing",['contest'=> $contest] )->toString());


            }

            $data['error'] = $errors;


        }

        $data['is_login'] = 1;

        $template = 'nintendo_register';

        $data['email'] = isset($post['email_noprefix'])? $post['email_noprefix'] :'';

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );

    }

    public function validateLogin($post){

        $uid = \Drupal::service('user.auth')->authenticate($post['email'], $post['password']);

        $errors = [];

        if (empty($uid) || !is_numeric($uid) ){
            $errors['email'] = t('Incorrect username or password');
        }

        return $errors;
    }

    public function startSession($profile){
        $session = \Drupal::service('session');


        $session_manager = \Drupal::service('session_manager');
        $session_manager->start();

        $session_id = $session_manager->getId();

        // $session->set('MS_' . $session_id, $profile);
        // Check whether a session has been started.
        // $session_manager->isStarted();
    }
    */


    public function prizeList(Request $request, $contest) {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        $data = [];
        $post = [];
        if (!$this->getContestId($contest)) {
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $node = $this->contestnode->getTranslation($langcode);
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;

        if (!$this->hasPrize($node)){
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }


        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $template = 'custom_prizes_template';
        $data['langcode'] = $langcode;
        $data['lang_prefix'] = $lang_prefix;
        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        $data['contest_type'] = $contest_type;
        $data['prizes'] = [];
        $data['back_button'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/landing";

        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;

        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/officialrules";
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";
        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/faq";

        $data['signup_link'] = Url::fromRoute($lang_prefix . '.custom-quaker.contest.signup', ['contest' => $contest])->toString();


        if ($this->hasPrize($node)){
            $entities = $node->field_contest_prizes->referencedEntities();
            $prizes = [];
            foreach ($entities as $key => $prize_node) {
                $img_url = "";
                if (!empty($prize_node->field_prize_image->target_id)){
                    $img = \Drupal\file\Entity\File::load($prize_node->field_prize_image->target_id);
                    $img_url = file_create_url( $img->getFileUri() );
                }

                $prizes[] = [
                    'title' => $prize_node->getTranslation($langcode)->field_subtitle->value,
                    'body' => $prize_node->getTranslation($langcode)->body->value,
                    'img_url' => $img_url,
                ];
            }
            $data['prizes'] = $prizes;
        }

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    public function isComingSoon(){
        $node = $this->contestnode;

        $arr_opendate = $node->field_opening_date->getValue();
        $obj_date = date_create($arr_opendate[0]['value']);
        $str_opendate = date_format($obj_date, "Y-m-d H:i:s");

        $now =  get_date_with_site_timezone($node->field_opening_date);

        $time_now = strtotime($now);
        $time_open = strtotime($str_opendate);
        $datediff = $time_open - $time_now ;

        $days_till_open =  round($datediff / (60 * 60 * 24));

        // if ($days_till_open <= 14 && $days_till_open > 0){
        if ( $days_till_open > 0){
            return true;
        }
        return false;
    }

    public function faq(Request $request, $contest) {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        $data = [];
        $post = [];
        if (!$this->getContestId($contest)) {
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $node = $this->contestnode->getTranslation($langcode);
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;

        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            $route_name = $lang_prefix . substr($route_name, 5);
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $data['is_comingsoon'] = 0;
        if ( $request->query->has('comingsoon') || $this->isComingSoon() ){
            $data['is_comingsoon'] = 1;
            $data['time_to_launchday'] = $this->getTimeToLaunchDate();
            if (!empty($data['time_to_launchday'] )){
                $data['contest_opendate'] = $data['time_to_launchday']['open_date'];
            }
        }


        $data['back_button'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/landing";

        $template = 'custom_faq_template';
        $data['langcode'] = $langcode;
        $data['lang_prefix'] = $lang_prefix;
        $data['contest_uri'] = $node->getTranslation($langcode)->field_contest_uri->value;
        $data['legal_footer'] = $node->getTranslation($langcode)->field_legal_footer->value;
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        $data['contest_type'] = $contest_type;

        $contesturi_en = $this->contestnode->getTranslation('en')->field_contest_uri->value;

        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/faq";
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";

        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/officialrules";

        return array(
            '#theme' => $template,
            '#data'  => $data,
        );
    }

    private function hasPrize($node){
        $prizes = $node->field_contest_prizes->referencedEntities();
        if (empty($prizes))
            return false;
        // check if the contest has prize
        return true;
    }

    private function is_valid_prize_claim($enterdate, $email, $nid) {
        $result = ContestValidator::instance()->validate_claim($nid, $email, $enterdate);
        return $result;
    }

    public function checkEligibility($email, $contest_uri, $nb_entries = 1){
        $contest_id = $this->contestnode->id();

        // @TODO: Check if user is fraudulent.
        $is_fraud =  contest_check_for_fraud($email, $contest_id);
        $env =  \Drupal\Core\Site\Settings::get("environment");
        if (in_array($env, ['dev', 'staging'])){
            $is_fraud = false;
        }

        if ($is_fraud)
            return false;


        $this->registeragain = false;
        $contestentry = $this->contestnode->field_contest_entry->value;
        $entry_limit = 1000;
        if ($contestentry == '3'){
            $entry_limit = 3;
        }elseif ($contestentry == '2'){
            $entry_limit = 1;
        } elseif($contestentry == '7'){
            $total_entries_this_week = $this->count_weekly_registrations($this->contestnode->id(), $email);
            if ($total_entries_this_week > 7){
                return false;
            }
            $this->num_entries = $total_entries_this_week;
            return true;
        }

        $date = date('Y-m-d');

        $sql =  " SELECT 1 FROM pepsicontest_reg_contest
                    WHERE email = '$email'
                    and contest_id = $contest_id
                    and enterdate = '$date'
                    limit 3
                    ";
        $query = \Drupal::database()->query($sql);
        $exist = 0;

        try{
            $results = $query->fetchAll();
            if (!empty($results)){
                $this->num_entries = count($results) + $nb_entries - 1;
                if ($this->num_entries >= $entry_limit){
                    return false;
                }
                if ($this->num_entries > 0 && $this->num_entries < $entry_limit) {
                    // this boolean value is used in the signup() function
                    // to determine if the user is allowed to register again
                    $this->registeragain = true;
                }
                return true;

            } else {
                return true;
            }
        } catch(\Exception $e){
            log_var($sql, "Check eligibility query not running ");
            return false;
        }



        // // @TODO: make this query more efficient
        // $query = \Drupal::database()->select('pepsicontest_reg_contest', 'nfd');
        //         $query->addField('nfd', 'contest_name');
        //         $query->addField('nfd', 'enterdate');
        //         $query->condition('nfd.contest_name', $contest_uri);
        //         $query->condition('nfd.email', $email);
        //         $query->condition('nfd.enterdate', $date);
        //         // $query->orderBy('nfd.enterdate', 'DESC');
        //         // $query->range(0, 1);

        // $num_rows = $query->countQuery()->execute()->fetchField();
        // $this->num_entries = $num_rows;

        // if ($num_rows >= $entry_limit){
        //     return false;
        // }

        // if ($num_rows > 0 && $num_rows < $entry_limit) {
        //     // this boolean value is used in the signup() function
        //     // to determine if the user is allowed to register again
        //     $this->registeragain = true;
        // }

        // return true;

    }

    public function claimPrize(Request $request, $contest) {
        $data = [];

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $data['langcode'] = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        //Getting language and passing to twig
        //check if contest exists
        if (!$this->getContestId($contest)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $node = $this->contestnode->getTranslation($langcode);
        $env =  \Drupal\Core\Site\Settings::get("environment");
        $debug_mode = false;
        if ($request->query->has('debug') && $env != 'prod'){
            $debug_mode = true;
        }

        if (!$debug_mode && ( !$request->query->has('date') || !$request->query->has('uid') ) ){
            log_var("query has to have 'date' and 'uid' param ", " prize claim");
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
        $enterdate = $request->get('date');
        $base64_email = $request->get('uid');
        $email = base64_decode($base64_email);
        // if (!$this->is_valid_prize_claim($enterdate, $email, $node->id())){
        //     die("Prize has already been claimed or winner does not exist");
        //     throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        // }

        $data['langprefix'] = $lang_prefix;
        $contest_type = $this->contestnode->field_contest_type->value;
        $data['contest_type'] = $contest_type;
        if ($contest_type == 'cheetos'){
            // $data['email'] = $email;
        }

        $data['contesturi'] = $contest;

        $data['enterdate'] = $enterdate;
        $data['contest_id'] = $node->id();
        $data['provinces_options'] = $this->CreateProvinceCodeDropdown();
        // URL should be of the form link?contest=23&date=some_date&uid=someuid

        $skills_challenge = $this->getRandomSkillsChallenge();
        $data['challenge_question'] = $skills_challenge['question'];
        $data['challenge_id'] = $skills_challenge['id'];
        $data['challenge_answer'] = $skills_challenge['answer'];
        $post = [];
        $post['answer'] = $skills_challenge['answer'];
        $data['pdf_url'] = '';
        if ( !empty($node->field_pdf->getValue() ) ){
            try{
              $file_id = $node->field_pdf->getValue()[0]['target_id'];
              $file = \Drupal\file\Entity\File::load($file_id);
              // $data['pdf_url'] = $file->url();
              $data['pdf_url'] = !empty($file)? file_create_url( $file->getFileUri()) : '';
            } catch (\Exception $e){
              log_var("", " Contest  PDF could not be retrieved  ");
            }
        }


        $contesturi_en = $node->getTranslation('en')->field_contest_uri->value;

        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/officialrules";
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";
        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en. "/faq";

        $data['submitted'] = 0;
        if ($request->getMethod()=='POST'){
            $data['submitted'] = 1;
            $data['is_valid'] = 1;


            $post['firstname'] = trim(isset($post['firstname'])?$post['firstname']:$request->request->get('firstname'));
            $post['lastname'] = trim(isset($post['lastname'])?$post['lastname']:$request->request->get('lastname'));
            $post['postalcode'] = trim(isset($post['postalcode'])?$post['postalcode']:$request->request->get('postalcode'));
            $post['postalcode'] = str_replace(' ', '', $post['postalcode']);
            $post['province'] = $request->get('province');
            $post['city'] = $request->get('city');
            $post['address1'] = $request->get('address1');
            $post['address2'] = $request->get('address2');
            $post['phone'] = $request->get('phone');
            $post['challenge'] = $request->get('challenge');
            $post['contest_id'] = $node->id();
            // @TODO: get the email from the form instead.
            $post['email'] = $request->request->get('email');
            // $post['email'] = rtrim($email, ";" );
            $post['enterdate'] = $enterdate;
            // @TODO: Validate province, email, zipcode,...
            $errors = $this->ValidateClaimFields($post);

            $data['address1'] = $post['address1'];
            $data['address2'] = $post['address2'];
            $data['province'] = $post['province'];
            $data['postalcode'] = $post['postalcode'];
            $data['city'] = $post['city'];
            $data['phone'] = $post['phone'];
            $data['email'] = $post['email'];
            $data['firstname'] = $post['firstname'];
            $data['lastname'] = $post['lastname'];

            $claim_status = $this->is_valid_prize_claim($enterdate, $email, $node->id() );
            if (!empty($claim_status)){
                if ($claim_status['expired'] == 1 || in_array($claim_status['claimed'], [1,-1])){
                    $data['is_valid'] = 0;
                    $errors['email'] = t('An unknown error has occurred. Please contact contest@tastyrewards.ca for assistance.');
                }
            }

            if (empty($errors) && $data['is_valid']){


                $this->SaveClaim($post);
                // debug_var($post);

                return new RedirectResponse(\Drupal\Core\Url::fromRoute("custom.contest.claim.thankyou", array('contest'=>$contest))->toString());
            } else{
                $data['error'] = $errors;
            }
        }

        // $data = $this->getConfirmationPageData($contest);

        return array(
            '#theme' => 'custom_claim_template',
            '#data' => $data,
        );
    }

    public function ValidateClaimFields($post){
        $errors = [];

        if (!$this->EmailValidation($post['email'])){
            $errors['email'] = t('Email is invalid');
        }

        if (empty($post['firstname']) ){
            $errors['firstname'] = t('First name is required');
        }

        if (empty($post['lastname']) ){
            $errors['lastname'] = t('Last name is required');
        }

        if (empty($post['address1']) ){
            $errors['address1'] = t('Address is required');
        }

        if (!$this->postalCodeValidation($post['postalcode'])){
            $errors['postalcode'] = t('Please enter a valid postal code.');
        }

        if (!$this->phoneNumberValidation($post['phone'])){
            $errors['phone'] = t('Please enter a valid phone number');
        }
        return $errors;
    }

    public function getMathChallenge($nid){
        $node = Node::load($nid);
        $contest_type = $node->field_contest_type->value;


        $challenges = [];
        $challenges['grabsnack']['questions'] =  [
            t('Divide 20 by 2, then multiply by 5, then add 3, and then subtract 4.'),
            t('Divide 50 by 5, then multiply by 3, then add 6, and then subtract 8.'),
            t('Divide 10 by 2, then multiply by 7, then add 6, and then subtract 4.'),
            t('Divide 16 by 4, then multiply by 12, then add 6, and then subtract 3. '),
        ];
        // $answers = [

        $challenges['grabsnack']['answers'] =  [
            '49',
            '28',
            '37',
            '51',
        ];

        $challenges['cheetos'] = $challenges['grabsnack'];
        $challenges['hockey'] = $challenges['grabsnack'];

        if (!$challenges[$contest_type]){
            return [];
        }

        return [
            'questions' => $challenges[$contest_type]['questions'] ,
            'answers' => $challenges[$contest_type]['answers'] ,
        ];
    }

    public function getRandomSkillsChallenge(){
        $challenge = $this->getMathChallenge($this->contestnode->id());
        $questions = $challenge['questions'];
        $answers = $challenge['answers'];

        $random_number = rand(0, count($questions) - 1);
        return [
            'question' => $questions[$random_number],
            'answer' => $answers[$random_number],
            'id' => $random_number,
        ];
    }

    public function CreateProvinceDropdown($selected_province = null) {
        $provinces = [t("Alberta"), t("British Columbia"), t("Manitoba"), t("New Brunswick"), t("Newfoundland and Labrador"), t("Nova Scotia"), t("Ontario"), t("Prince Edward Island"), t("Quebec"), t("Saskatchewan"), t("Northwest Territories"), t("Nunavut"), t("Yukon")];
        $options = [];
        foreach ($provinces as $key => $province) {
            $options[str_replace(' ', '-', strtolower($province))] = $province;
        }

        $province_option = '<option value="">' . t('Province/Territory') . '</option>';
        foreach ($options as $key => $province) {

            $tmp = ($selected_province == $key) ? 'selected' : '';
            $province_option = $province_option . '<option value="' . $key . '" ' . $tmp . ' >' . $province . '</option>';
        }



        return $province_option;
    }

    function CreateProvinceCodeDropdown($selected_province = null) {
        $provinces = ["Alberta", "British Columbia", "Manitoba", "New Brunswick", "Newfoundland and Labrador", "Nova Scotia", "Ontario", "Prince Edward Island", "Quebec", "Saskatchewan", "Northwest Territories", "Nunavut", "Yukon"];

        $province_codes = [
            "Alberta" => 'AB',
            "British Columbia" => 'BC',
            "Manitoba" => 'MB',
            "New Brunswick" => 'NB',
            "Newfoundland and Labrador" => 'NL',
            "Nova Scotia" => 'NS',
            "Ontario" => 'ON',
            "Prince Edward Island" => 'PE',
            "Quebec" => 'QC',
            "Saskatchewan" => 'SK',
            "Northwest Territories" => 'NT',
            "Nunavut" => 'NU',
            "Yukon" => 'YT'
        ];

        $options = [];
        foreach ($provinces as $key => $province) {
            $options[$province_codes[$province]] = $province;
        }
        $langcode = get_current_langcode($with_prefix = false);
        $prov_default = $langcode == 'fr'? 'Province' : 'Province / Territory';

        // $province_option = '<option value="">' . t('Province') . '</option>';
        $province_option = '<option value="">' . $prov_default . '</option>';
        foreach ($options as $key => $province) {

            $tmp = ($selected_province == $key) ? 'selected' : '';
            $province_option = $province_option . '<option value="' . $key . '" ' . $tmp . ' >' . t($province) . '</option>';
        }



        return $province_option;
    }

    public function SaveClaim($post){
        $address = $post['address1'] . " " . $post['address2'];
        $postalcode = $post['postalcode'];
        $province = $post['province'];
        $city = $post['city'];
        $phone = $post['phone'];
        $enterdate = trim($post['enterdate']);
        $email = trim($post['email']);
        $contest_id = trim($post['contest_id']);

        $query = \Drupal::database()->update('pepsicontest_reg_contest');
        $query->fields([
            'address' => $address,
            'postalcode' => $postalcode,
            'province' => $province,
            'city' => $city,
          ])
          ->condition('contest_id', $contest_id)
          ->condition('enterdate', $enterdate )
          ->condition('email', $email);
         $result = $query->execute();

        if ($result > 0){
            // @TODO: update field claim = 1
            $query = \Drupal::database()->update('pepsicontest_winners');
            $query->fields([
                'claimed' => 1,
                'phone' => $phone,
              ])
              ->condition('contest_id', $contest_id)
              ->condition('enterdate', $enterdate )
              ->condition('email', $email);
             $success = $query->execute();

             return $success;
        } else {
            return 0;
        }
    }


    public function seedPrizes(Request $request, $contest) {
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }

        $node = $this->contestnode;

        if ($this->hasPrize($node)){
            $winner_picker = new ContestWinnerPicker();
            $winner_picker->do_seeding($node);
            die("seeding done");
        }

            die("has NO prize");
    }



    public function prizeSchedule(Request $request, $contest) {
        $data = [];
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }

        $node = $this->contestnode;
        $config = \Drupal::service('config.factory')->getEditable('microsite_contest_' .$node->id().'.settings');
        $variables = array();

        $variables['nb_winners_current_seeding'] = $config->get('nb_winners_current_seeding');
        $variables['nb_prize_current_seeding'] = $config->get('nb_prize_current_seeding');
        $variables['time_next_draw'] = $config->get('time_next_draw');
        $variables['time_last_seeding'] = $config->get('time_last_seeding');
        $draw_schedule = $config->get('draw_schedule');
        $prize_schedule = $config->get('prize_schedule');
        debug_var($variables);

        foreach ($draw_schedule as $key =>  $value) {
            $prize_node = Node::load($prize_schedule[$key]);
            $prizename = $prize_node->field_subtitle->value;
            echo "\n $value  -    Prize  $prizename "  ;
        }

        die(" done ");

    }


    public function monitor(Request $request, $contest) {
        $data = [];
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }

        $node = $this->contestnode;
        echo " opening date : ";
        debug_var($node->field_opening_date->value);
        echo " until  ";
        debug_var($node->field_closing_date->value);
        echo " \n ";
        $entities = $node->field_contest_prizes->referencedEntities();
        $prizes = [];
        echo "\n------------------------\n";
        // debug_var
        foreach ($entities as $key => $prize_node) {
            $prizes[] = [
                'nid' => $prize_node->id(),
                'title' => $prize_node->field_subtitle->value,
                'qty_awarded' => $prize_node->field_quantity_awarded->value,
                'total_qty' => $prize_node->field_quantity->value,
            ];
            $nid = $prize_node->id();
            $title = $prize_node->field_subtitle->value;
            $qty_awarded = $prize_node->field_quantity_awarded->value;
            $qty = $prize_node->field_quantity->value;

            // @TODO: Check if this lines up with the winners table
            echo "\n $nid - $qty_awarded / $qty   - $title ";

        }

        if (count($prizes)) {
            echo "\n------------------------\n";
            $suspicious_accounts = $this->fetchSuspiciousWinners($node->id());
            debug_var(" Potentially fraudulant accounts: \n");
		    debug_var($suspicious_accounts);
            echo "\n------------------------\n";

        }

        echo "\n------------------------\n";
        debug_var("Unique entries ");
        $count = $this->getUniqueContestEntries($node->id());
        echo "\n------------------------\n";
        debug_var("Contest entries ");
        $count = $this->getContestEntries($node->id());


        debug_var('', 1);
    }

    public function getContestEntries($contest_id){
        $sql = "SELECT enterdate, count(*) as cnt
                 from pepsicontest_reg_contest
                    WHERE
                    contest_id = $contest_id
                    group by enterdate" ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result)){
            return 0;
        }
        $total = 0;
        $daily_entries = [];
        foreach($result as $res){
            $daily_entries[] = " " . $res->enterdate . " : " . $res->cnt;
            $total += $res->cnt;
        }
        debug_var("Total entries : $total");
        foreach($daily_entries as $entry){
            debug_var($entry);
        }
        return $total;
    }

    public function getUniqueContestEntries($contest_id){
        $sql = "SELECT count(DISTINCT(email)) as cnt
                 from pepsicontest_reg_contest
                    WHERE
                    contest_id = $contest_id
                    " ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result)){
            return 0;
        }
        $total = 0;
        $daily_entries = [];
        $total = $result[0]->cnt;
        debug_var("Unique entries : $total");

        return $total;
    }

    public function fetchSuspiciousWinners($contest_id){
        $sql = "SELECT t1.email, t1.enterdate, t1.prize_name, t2.address, t2.city
                 from pepsicontest_winners as t1
                    left join pepsicontest_reg_contest as t2
                    ON (t1.contest_id = t2.contest_id AND t1.enterdate = t2.enterdate)
                    WHERE
                    t1.suspicious = 1 AND (t2.address <> '' and t2.address is not null )
                    AND  t1.contest_id = $contest_id  " ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result)){
            return '';
        }

        $rows = "Email \t Enterdate \t Prize \t Address \t City \t  \n";
        $processed = [];

        foreach ($result as  $val) {
            if (in_array($val->email, $processed))
                continue;

            $rows .= $val->email . "\t" . $val->enterdate . "\t" . $val->prize_name . "\t" . $val->address . "\t" . $val->city;
            $rows .= "\n";
            $processed[] = $val->email;
        }
        return $rows;
    }


    public function prizeStatus(Request $request, $contest) {
        $data = [];
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }

        $node = $this->contestnode;
        echo " opening date : ";
        debug_var($node->field_opening_date->value);
        echo " until  ";
        debug_var($node->field_closing_date->value);
        echo " \n ";
        $entities = $node->field_contest_prizes->referencedEntities();
        $prizes = [];
        // debug_var
        foreach ($entities as $key => $prize_node) {
            $prizes[] = [
                'nid' => $prize_node->id(),
                'title' => $prize_node->field_subtitle->value,
                'qty_awarded' => $prize_node->field_quantity_awarded->value,
                'total_qty' => $prize_node->field_quantity->value,
            ];
            $nid = $prize_node->id();
            $title = $prize_node->field_subtitle->value;
            $qty_awarded = $prize_node->field_quantity_awarded->value;
            $qty = $prize_node->field_quantity->value;

            // @TODO: Check if this lines up with the winners table
            echo "\n $nid - $qty_awarded / $qty   - $title ";

        }

        debug_var('', 1);


    }


    public function winner(Request $request, $contest, $prize_id) {
        $data = [];
        // @TODO: We need a mechanism that prevents access to this page unless
        // the user was drawn
        // -- check if the referrer is the contest signup page
        // $request->headers->get('referer');
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_prefix = get_current_langcode();
        $lang_url = get_current_langcode($prefix = false);
        $data['is_winner'] = 1;

        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }

        $contest_type = $this->contestnode->field_contest_type->value;
        $data['contest_type'] = $contest_type;
        $data['claim_url'] = '';

        $data['signup_link'] = Url::fromRoute($lang_prefix . '.custom-quaker.contest.signup', ['contest' => $contest])->toString();

        if ($request->query->has('claim')){
            $base64_claim = $request->query->get('claim');
            $data['claim_url'] = base64_decode($base64_claim);
        }
        $debug = 0;
        if ( $request->query->has('debug')){
            $debug = 1;
        }

        $uid = $request->query->get('uid');
        if ($contest_type == 'cheetos'){
            $data['game_url'] = $this->getCheetosGameUrl($contest, 1);
        }

        $contesturi_en = $this->contestnode->getTranslation('en')->field_contest_uri->value;
        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/faq";

        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/officialrules";
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";


        $data['langcode'] = get_current_langcode(false);

        $firstname = $request->query->get('firstname');
        $data['firstname'] = $firstname;

        $base64_email = $request->query->get('em');
        $session = \Drupal::service('session');
        $winner_session = $session->get('user_' . $uid);
        if (!empty($winner_session) || $debug ){
            $data['email'] = $winner_session['email'];
            $langcode = get_current_langcode($prefix = false);
            // $email = base64_decode($base64_email);
            $email = $data['email'];
            // check if the user has really won
            $found = $this->FindContestWinner($email,  $prize_id);
            $prize_node = Node::load($prize_id);
            $data['prize'] = $prize_node->getTranslation($langcode)->getTitle();
            $data['prize_img'] = '';


            if (!empty($prize_node->field_prize_image->target_id)){
                // $img = \Drupal\file\Entity\File::load($prize_node->field_prize_image->target_id);
                // $data['prize_img'] = file_create_url( $img->getFileUri() );
                $data['prize_img'] = get_translated_image_url($prize_node,"field_prize_image", $langcode);
            }



            if ($found || $debug ){
                $data['claim_url'] = $winner_session['claim_url'];
                if (!$debug){
                    send_contest_email($email, $data['claim_url'], $firstname, $data['prize'], $contest_type);
                }

                // This ensures nobody else has access to the prize winning
                // confirmation page after this
                $session->remove('user_' . $uid);
                return array(
                    '#theme' => 'custom_thankyou_template',
                    '#data' => $data,
                );
            }
        }

        return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());
        // $data = $this->getConfirmationPageData($contest);

    }

    public function confirmation(Request $request, $contest) {
        $data = [];
        $data['is_winner'] = 0;

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = get_current_langcode(false);
        $lang_prefix = get_current_langcode();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        $data['langcode'] = $langcode;
        $data['lang_prefix'] = $lang_prefix;

        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }
        $contest_type = $this->contestnode->field_contest_type->value;
        $data['contest_type'] = $contest_type;

        $data['contest_uri'] = $this->contestnode->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        if ($contest != $data['contest_uri']){
            return $this->redirect_lang_route($data['contest_uri'], $route_name);
        }

        $concours = $lang_prefix == 'fr-ca'? 'concours' : 'contest';

        $contesturi_en = $this->contestnode->getTranslation('en')->field_contest_uri->value;

        $data['back_button'] = "/" . $lang_prefix . "/contest/" . $data['contest_uri'] . "/participate";

        $data['faq_link'] = "/" . $lang_prefix . "/$concours/" . $data['contest_uri'] . "/faq";

        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/officialrules";
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";
        $data['legal_footer'] = $this->contestnode->getTranslation($langcode)->field_legal_footer->value;

        $base64_upc = $request->query->get('upc');
        $nb_entries = $request->query->get('nb');
        $data['code'] = '';
        // if (!empty($base64_upc)){
        $session = \Drupal::service('session');
        if ($contest_type == 'nintendo' || $contest_type == 'nintendo2'){
            $data['nb_entries'] = $nb_entries;
            $base64_email = $request->query->get('uid');
            $email = base64_decode($base64_email);
            $hash = simple_hash($email);
            $confirm_session =  $session->get('contest_confirm_' . $hash);
            if ($confirm_session){
                $ip_address = getIPaddress();
                // @TODO: replace findTodayEntry with ip address check.
                $has_used_ip = $this->findEntryWithIpAddress($this->contestnode->id(), $ip_address);
                // $has_received_code_today = $this->findTodayEntry($email, $this->contestnode->id());
                $data['code'] = '';
                if (!$has_used_ip){
                    $has_used_email = $this->findEntryWithEmail($this->contestnode->id(), $email);
                    if (!$has_used_email){
                        $data['code'] = $this->getNextContestCode($this->contestnode->id(), $email );
                    }

                }
                $session->remove('contest_confirm_' . $hash);
            }

            if (!empty($data['code'])){
                try{
                    $this->updateContestCodeTable($this->contestnode->id(), $data['code'], $email);
                } catch(\Exception $e){
                    log_var($data, "Contest code table could not be updated", "Nintendo_contest");
                }
            }
        }

        if ($contest_type == 'cheetos'){
            $data['game_url'] = $this->getCheetosGameUrl($contest, 1);
        }


        // $data = $this->getConfirmationPageData($contest);

        // template filename is custom_contest_thankyou.html.twig
        return array(
            '#theme' => 'custom_thankyou_template',
            '#data' => $data,
        );
    }

    public function emailTest(Request $request, $contest){
        if ($request->query->has('address') ){
            $email = $request->query->get('address');
        } else {
            die("add the email as ?address=myemail@mail.com");
        }

        if (!$this->getContestId($contest)) {
            //
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $contest_type = $this->contestnode->field_contest_type->value;

        $langcode = get_current_langcode();
        $claim_url = "/$langcode/test/url";

        send_contest_email($email, $claim_url, "BamTest", "Test prize", $contest_type);

        die(" send email ");
    }

    public function updateContestCodeTable($contest_id, $code, $email){
        \Drupal::database()->update('pepsicontest_codes')
            ->fields([
                'enterdate' => date('Y-m-d'),
                'email' => $email,
                'ip_address' => getIPaddress(),
            ])
            ->condition('contest_id',$contest_id)
            ->condition('code',$code)
            ->execute();


    }

    public function getNextContestCode($contest_id, $email){
        $sql = "SELECT * from pepsicontest_codes  WHERE
                    contest_id = $contest_id AND email = '' AND enterdate = '' limit 1  " ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result)){
            return '';
        }
        // Add the user to contest weekly entry table
        // Do we still need this?
        $this->addTodayEntry($email, $contest_id);

        return $result[0]->code;
    }

    public function findEntryWithIpAddress($contest_id, $ip_address){
        $nb_code_per_ip = 11;
        $sql = "SELECT COUNT(*) as cnt  from pepsicontest_codes  WHERE
                    contest_id = $contest_id AND ip_address = '$ip_address'   " ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result) || $result[0]->cnt < $nb_code_per_ip){
            return false;
        }

        return 1;

    }

    public function findEntryWithEmail($contest_id, $email){
        $sql = "SELECT * from pepsicontest_codes  WHERE
                    contest_id = $contest_id AND email = '$email' limit 1  " ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result)){
            return false;
        }

        return 1;

    }

    public function findTodayEntry($email, $contest_id){

        $env =  \Drupal\Core\Site\Settings::get("environment");
        if ($env == "staging" ){
            \Drupal\pepsibam\CronScheduledTasks::instance()->run_every("reset_nintendo_contest_entries", $hours = 12);
        }
        $sql = "SELECT * from pepsi_microsite_weekly_entries  WHERE
                    contest_id = $contest_id AND email = '$email' limit 1  " ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (empty($result)){
            return false;
        }

        return 1;

    }

    public function addTodayEntry($email, $contest_id){

        $sql = "INSERT IGNORE INTO pepsi_microsite_weekly_entries (email, contest_name, contest_id, nb_entries, nb_upc) VALUES (
            '$email', 'fritolaycontest', " . $contest_id . ", 1, 1); ";

        $select = \Drupal::database()->query($sql);
        try{
            $result = $select->execute();
        }catch(\Exception $e){
            log_var("", "Failed to add Nintendo contest entry");
        }

    }

    public function isCustomContest(){
        $contest_type = $this->contestnode->field_contest_type->value;
        if (in_array($contest_type, ['nintendo', 'nintendo2', 'grabsnack', 'cheetos', 'hockey'])){
            return true;
        }
        return false;
    }


    public function claimThankyou($contest) {
        $data = [];
        $data['langcode'] = get_current_langcode(false);
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        //Getting language and passing to twig
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        //check if contest exists
        if (!$this->getContestId($contest)) {
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($lang_url . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString());

        }

        $langcode = get_current_langcode($prefix = false);
        $contest_type = $this->contestnode->getTranslation($langcode)->field_contest_type->value;
        $data['contest_type'] = $contest_type;


        $lang_prefix = get_current_langcode();

        $contesturi_en = $this->contestnode->getTranslation('en')->field_contest_uri->value;
        $data['faq_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/faq";

        $data['rules_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/officialrules";
        $data['prize_link'] = "/" . $lang_prefix . "/contest/" . $contesturi_en . "/prizes";

        return array(
            '#theme' => 'custom_claim_thankyou_template',
            '#data' => $data,
        );

    }

    public function rules(Request $request, $contest) {
        if (!$this->getContestId($contest)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            die("Contets not found ");
        }

        $langcode = get_current_langcode($prefix = false);
        $langprefix = get_current_langcode();

        $contest_uri = $this->contestnode->getTranslation($langcode)->field_contest_uri->value;
        $route_name = \Drupal::routeMatch()->getRouteName();
        // Fix the toggle language problem
        if ($contest != $contest_uri){
            $langprefix = get_current_langcode();
            $route_name = $langprefix . substr($route_name, 5);
            return $this->redirect_lang_route($contest_uri, $route_name);
        }

        $is_comingsoon = 0;
        if ( $request->query->has('comingsoon') || $this->isComingSoon() ){
            $is_comingsoon = 1;
        }


        $contest_type = $this->contestnode->field_contest_type->value;

        $return = parent::rules($contest);
        $return['#data']['contest_type'] = $contest_type;
        $return['#data']['langcode'] = $langcode;
        $return['#data']['is_comingsoon'] = $is_comingsoon;
        $return['#data']['back_button'] = "/" . $langprefix . "/contest/" . $contest . "/landing";

        $contesturi_en = $this->contestnode->getTranslation('en')->field_contest_uri->value;

        $return['#data']['faq_link'] = "/" . $langprefix . "/contest/" . $contesturi_en . "/faq";
        $return['#data']['prize_link'] = "/" . $langprefix . "/contest/" . $contesturi_en . "/prizes";

        $return['#data']['rules_link'] = "/" . $langprefix . "/contest/" . $contesturi_en . "/officialrules";


        return $return;
    }

    function registerUser ($node, $user, $subjectanswers, $nomember = 0, $upccode = null){
        $langcode = get_current_langcode(false);
        $contest_type = $node->getTranslation($langcode)->field_contest_type->value;
        $contestentry = $node->getTranslation($langcode)->field_contest_entry->value;
        $user_ip = $user->get('field_ip_address')->value;

        if (! $this->isCustomContest() ){
            return parent::registerUser ($node, $user, $subjectanswers, $nomember);
        }


        $date = date('Y-m-d H:i:s');
        $random_number = 0;
        $bonus = $this->num_entries + 1;
        if ($contestentry == '7'){
            // we do not care about bonuses for customContests
            // the random number will ensure there are no duplicate keys in
            // pepsicontest_reg_contest, 'bonus' being a primary key
            $now = strtotime("now");
            $bonus = intval($now) % 3600;
        }

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
                        ,'language' => $langcode
                        ,'regdate' => $date
                        ,'user_ip' => $user_ip? $user_ip : '0.0.0.0'
                        ,'user_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255)
                        ,'enterdate' => $date
                        ,'nomember' => $nomember
                        ,'contest_optin' => 0
                        // ,'bonus' => $this->num_entries + 1 + $random_number
                        ,'bonus' => $bonus

                        );
        if ($upccode){
            $usercontest['upc_code'] = $upccode;
        }

        if (\Drupal::database()->schema()->tableExists('pepsicontest_reg_contest')) {
            $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();

            if ($contest_type == 'nintendo2'){
                $nb_entries = $this->getEntriesPerUPC($upccode);
                $now = strtotime("now");
                $bonus = intval($now) % 3600;
                for ($x = 1; $x < $nb_entries; $x++) {
                    $usercontest['bonus'] = $x + $bonus;
                    $result = \Drupal::database()->insert('pepsicontest_reg_contest')->fields($usercontest)->execute();
                }
            }



        }

        if ($contestentry == '7'){
            // Check if user already exist in the table
            $sql = "SELECT * from pepsi_microsite_weekly_entries  WHERE
                    contest_id = " . $node->Id() . " AND email = '" . $usercontest['email'] . "'" ;
            $select = \Drupal::database()->query($sql);
            $result = $select->fetchAll();

            if (empty($result)){
                $sql = "INSERT IGNORE INTO pepsi_microsite_weekly_entries (email, contest_name, contest_id, nb_entries, nb_upc) VALUES (
                '" . $usercontest['email'] . "', '" . $usercontest['contest_name'] . "', " . $usercontest['contest_id'] . ", 1, 1 ); ";

            } else {
                $nb_entries = $result[0]->nb_entries;
                $total_entries = $nb_entries + 1 ;
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
                $channel = "debug";
                $message = " custom contest entry could not be saved \n Query: " . $sql . " \n " . $e->getMessage() ;
                $context = [ ];
                \Drupal::logger($channel)->info($message, $context);

                //return null;
            }

        }


        return false;
    }

    public function FindContestWinner($email, $prize_id){
        $nid =  $this->contestnode->id();
        $today =  date('Y-m-d');
        $sql = "SELECT * from pepsicontest_winners  WHERE
                    contest_id = $nid AND email = '$email'  AND prize_id = $prize_id
                    AND enterdate = '$today'" ;
        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();
        if (!empty($result))
            return true;

        return false;
    }

    public function saveUploadedImage($files, $email){
        $image_name = $files->getClientOriginalName();
        $ext = "jpg";
        if (substr_compare(strtolower($image_name), $ext, strlen($image_name)-strlen($ext), strlen($ext)) !== 0){
            $ext = "png";
        }
        $saved_img_name = base64_encode($email) . "." . $ext;

        $slash = DIRECTORY_SEPARATOR;

        $target_file = rtrim($_SERVER['DOCUMENT_ROOT'], $slash ) . $slash . "sites" . $slash .
         "default" . $slash . "files" . $slash . $saved_img_name;

         //@TODO: Add more validation rules here:
         // check if the user had already uploaded an image before
         // Also make sure the userID is included in the filename to make sure it overwrites
         // the previous image associated to the user

        if(move_uploaded_file($files->getPathname(), $target_file) ){
            return 1;
        } else {
            return 0;
        }
    }

    public function db_insert_extra_field($user, $field_name, $field_value){
        $sql = "INSERT INTO pepsicontest_extra_field (contest_id, user_id, field_name, field_data, enterdate)
        VALUES ('" . $this->contestnode->id() . "', '" . $user->id() . "', '$field_name', '" . $field_value. "' ,
          '" . date("Y-m-d H:i"). "'  );";
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

    public function isUserRegistered($contest_id,$user_id,$contestentry ){
        if ($contestentry == '1' || $contestentry == '2'){
            return parent::isUserRegistered($contest_id,$user_id,$contestentry );
        }
        $account = \Drupal\user\Entity\User::load($user_id);
        $email = $account->mail->value;
        if ($contestentry == '7'){
            $this->registeragain = false;
            // check if the user has registered 7 times this week
            $nb_entries = $this->count_weekly_registrations($contest_id, $email);
            if ($nb_entries > 0) {
                if ( $nb_entries < 7 ) {

                    $this->registeragain = true;
                }
                return true;
            }

            return false;

        }
        elseif ($contestentry == '3'){
            $this->registeragain = false;

            $num_rows = $this->count_today_registrations($contest_id, $email);
            $this->num_entries = $num_rows;

            if ($num_rows > 0) {
                // 3 per day is the limit
                if ( $num_rows < 3 ) {
                    // this boolean value is used in the signup() function
                    // to determine if the user is allowed to register again
                    $this->registeragain = true;
                }
                return true;
            }

            return false;
        }
    }

    private function count_today_registrations($contest_id, $email){
        $date = date('Y-m-d');
        // $query = \Drupal::database()->select('pepsicontest_reg_contest', 'nfd');
        //         $query->addField('nfd', 'contest_id');
        //         $query->addField('nfd', 'enterdate');
        //         $query->condition('nfd.contest_id', $contest_id);
        //         $query->condition('nfd.email', $email);
        //         $query->condition('nfd.enterdate', $date);
        //         // $query->orderBy('nfd.enterdate', 'DESC');
        //         // $query->range(0, 1);

        // $num_rows = $query->countQuery()->execute()->fetchField();
        // return !empty($num_rows)? $num_rows : 0;

        $sql =  " SELECT 1 FROM pepsicontest_reg_contest
                    WHERE email = '$email'
                    and contest_id = $contest_id
                    and enterdate = '$date'
                    limit 3
                    ";
        $query = \Drupal::database()->query($sql);
        $exist = 0;
        try{
            $results = $query->fetchAll();
            if (!empty($results)){
                return  count($results);
            } else {
                return 0;
            }
        } catch(\Exception $e){
            log_var($sql, "Count registration query could not be executed ");
        }
        return 0;
    }

    private function count_weekly_registrations($contest_id, $email){
        $sql = "SELECT nb_entries, nb_upc from pepsi_microsite_weekly_entries  WHERE contest_id = " . $contest_id . " AND email = '" . $email . "'" ;

        $select = \Drupal::database()->query($sql);
        $result = $select->fetchAll();

        if (empty($result)){
            return 0;
        } else {
            $nb_entries = $result[0]->nb_entries;
            if ($nb_entries == 0 && $result[0]->nb_upc > 0){
                $nb_entries = $result[0]->nb_upc;
            }
            return $nb_entries;
        }
    }

    public function db_insert_recipe($user, $title, $filename){
        $sql = "INSERT INTO pepsi_contest_reg_recipe (contest_id, user_id, recipe_title, recipe_img, regdate)
        VALUES ('" . $this->contestnode->id() . "', '" . $user->id() . "', '" . $title. "', '" . $filename. "' ,
          '" . date("Y-m-d H:i"). "'  );";
        try{
            $select = \Drupal::database()->query($sql);
            $result = $select->execute();
        } catch (\Exception $e) {
            $channel = "custom-module";
            $message = " Could not insert recipe image into Database ";
            $context = [ ];
            \Drupal::logger($channel)->info($message, $context);

            return null;
        }
        return true;
    }

    public function recipeImageValidation($files){
        if (empty($files))
            return false;
        return true;
    }

    public function RecipeNameValidation($title){
        return empty($title)? false : true;
    }

    public function recipeImgSizeValidation($files){
        return $files->getSize() > 2*1024*1024 ? false: true;
    }

    public function ontarioPostalCodeValidation($postalcode){
        if ( !preg_match("/^[KLMNP]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/",$postalcode ))
            return false;
        return true;
    }


    public function validateRegistration($post, $new, $contest_type = "quaker") {
        //Check the CsrfToken
        $error = array();
        $user = $this->user;


        // if(!$this->CheckCsrfToken($post['csrfToken'])) {
        //     $error['form'] = t('Oops, there is an issue. Please try again.');
        //     return $error;
        // }

        if ($contest_type == 'nintendo' || $contest_type == 'nintendo2'){
            // skip upc code validation on registration
            // $has_account = $this->UserRegistered($post['email']);
            // if ($has_account){
            //     $error['email'] = t('Email already exists. Please log in using your account');
            // }

        }

        if (!$this->validateCode($post['upccode'], $contest_type)){
            if ($contest_type == 'nintendo' || $contest_type == 'nintendo2'){
                if (empty($post['upccode'])){
                    $error['upccode'] = t('At least one code is required to proceed.');
                } elseif(strlen($post['upccode']) < 12 ){
                    $error['upccode'] = t('Code requires 12 characters. Check packaging and try again.');
                } else {
                    $error['upccode'] = t('Invalid code. Check packaging and try again, or contact us. ');
                }
            }elseif ($contest_type == 'cheetos' || $contest_type == 'hockey'){
                if (empty($post['upccode'])){
                    $error['upccode'] = t('At least one code is required to proceed.');
                } elseif(strlen($post['upccode']) < 12 ){
                    $error['upccode'] = t('UPC requires 12 characters. Check packaging and try again.');
                } else {
                    $error['upccode'] = t('Invalid code. Check packaging and try again, or <a style = "text-decoration: underline" href="https://contact.pepsico.com/tastyrewards">contact us.</a>');
                }
            }elseif ($contest_type == 'grabsnack'){
                if (empty($post['upccode'])){
                    $error['upccode'] = t('A code is required to proceed.');
                }elseif(strlen($post['upccode']) < 9 ){
                    $error['upccode'] = t('Code requires 9 characters. Check packaging and try again.');
                } else {
                    $error['upccode'] = t('Invalid code. Check packaging and try again, or <a style = "text-decoration: underline" href="https://contact.pepsico.com/tastyrewards">contact us.</a>');
                }
            } else {
                if (empty($post['upccode'])){
                    $error['upccode'] = t('A code is required to proceed.');
                }elseif(strlen($post['upccode']) < 9 ){
                    $error['upccode'] = t('Code requires 9 characters. Check packaging and try again.');
                } else {
                    $error['upccode'] = t('Invalid code. Check packaging and try again, or <a style = "text-decoration: underline" href="https://contact.pepsico.com/tastyrewards">contact us.</a>');
                }
            }
        }

        if (isset($post['optin_maj']) && $post['optin_maj'] === 0){
            $error['optin_maj'] = t('Required field');
        }

        if (isset($post['optin_maj2'])){
            unset($error['optin_maj']);
            if ( $post['optin_maj'] === 0 && $post['optin_maj2'] === 0){
                $error['optin_maj'] = t('Please select the checkbox that applies to your age');
            }
        }

        if (isset($post['province']) && empty($post['province'])  ){
            $error['province'] = t('Province is a required field');
        }

        //postalcode
        //if (!preg_match('/^[a-zA-Z0-9]{3}( )?[a-zA-Z0-9]{3}$/', trim($post['postalcode'])))
        if (!$this->postalCodeValidation($post['postalcode'])){

            $error['postalcode'] = t('Please enter a valid postal code.');
            if (empty($post['postalcode']))
                $error['postalcode'] = t('Postal Code is a required field.');
        }
        else {
            //gettting City and province
            // $this->searchProvince ($post['postalcode']);
        }

        if ($new) {

            //firstname val
            if (!$this->userNamesValidation($post['firstname'])){
                $error['firstname'] = t('Please enter a valid last name.');
                if (empty($post['firstname']))
                    $error['firstname'] = t('First Name is a required field.');
            }

            //lastname val
            if (!$this->userNamesValidation($post['lastname'])){
                $error['lastname'] = t('Please enter a valid last name.');
                if (empty($post['lastname']))
                    $error['lastname'] = t('Last Name is a required field.');
            }




            //gender
            $genders = array("M", "F", "O");
            // Gender is no longer a mandatory field
           // if (!in_array($post['gender'], $genders))
            //    $error['gender'] = t('Please enter a valid gender');

            //birthdate
            $dt = \DateTime::createFromFormat("Y-m-d", $post['bday']);

            if (!$contest_type === "lays"){
                if ($dt && array_sum($dt->getLastErrors()) == 0) {
                    if (!$this->ValidateAge($dt, $this->province))
                        $error['bday'] = t('Sorry! You must be 13+ to sign up.');
                } else
                    $error['bday'] = t('Please enter a valid birthdate.');

                if (!($this->recipeImageValidation($post['files']))){
                    $error['uploadImg'] = t('Sorry, your image could not be uploaded. Please double check that you uploaded a valid file and try again.');
                } elseif (!($this->recipeImgSizeValidation($post['files']))){
                    $error['uploadImg'] = t('Sorry, your image could not be uploaded. Please double check that you uploaded a valid file and try again.');
                }

                if (!$this->RecipeNameValidation($post['recipeTitle']))
                    $error['recipeTitle'] = t('Please enter a name for your recipe');
            }



            //validate Email
            if (!$this->EmailValidation($post['email'])){
                $error['email'] = t('Please enter a valid email');
                if (empty($post['email'])){
                    $error['email'] = t('Email is a required field');
                }
            }
            // This validation step is removed for the new site
            // elseif (trim(strtolower($post['email'])) != trim(strtolower($post['confirm_email'])))
            //     $error['confirm_email'] = t("Emails do not match.");


            // elseif ($this->UserRegistered($post['email'])) {
            //     $path = Url::fromRoute('user.login')->toString();
            //     $error['email'] = t('You’ve already registered to Tasty Rewards.') . ' ' . t("Log in <a class='contest-login' href=':loginpath'>here</a> to enter the contest!",array(':loginpath' => $path ));
            // }
            elseif(!validate_ip_address(getIPaddress(), $post['email'])){
                $error['email'] = t('Oops! Something went wrong');
            }

            //password
            if (!$this->PasswordFormatValidation($post['password'])  ){
                // bypass this for now
                // $error['password'] = t('Please enter a valid password');
            }
            // This validation step is removed for the new site
            // if (trim(strtolower($post['password'])) != trim(strtolower($post['confirm_password'])))
            //     $error['confirm_password'] = t("Passwords do not match.");

            //captcha
            // This validation step is removed for the new site
            if (!RecaptchaValidation($post['grecaptcharesponse'])){
                // bypass this for now
                 // $error['grecaptcharesponse'] = t('Recaptcha Required.');
            }


        }

        // if ($post['optin_maj'] != 1 )
                // $error['optin_maj'] = t('');

        if (!$this->EntriesLimitValidation($post['email'])){
            if ($contest_type == 'grabsnack'){
                $error['email'] = t('Sorry, you have reached your contest entry limit for the week.');
            } elseif($contest_type == 'nintendo'){
                $error['email'] = t('You have entered 3 times today');
            }
        }

        if ($contest_type === 'lays'){
            if (!$this->ontarioPostalCodeValidation($post['postalcode']))
                $error['postalcode'] = t('Please enter a valid Ontario postal code.');
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
        /*echo "<pre>";
        \Doctrine\Common\Util\Debug::dump($post);
        \Doctrine\Common\Util\Debug::dump($error);
        echo "</pre>";
         *
         */
        //exit;


        return $error;
    }

    public function EmailValidation($email) {
        $env =  \Drupal\Core\Site\Settings::get("environment");
        $blocked_emails = [
            'ashleyng6@gmail.com',
        ];

        $gmail = $this->removeGmailVariation($email);

        if ( in_array($this->removeGmailVariation($email), $blocked_emails) ){
            return false;
        }


        if ($env == "prod" ){
            if (strpos($email, "+") !== false )
            return false;
        }
        // reject email variation with + sign
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '|') !== false || strpos($email, ' ') !== false )
            return false;

        // Invalidate spammy emails
        if ( strpos($email, "@commuccast.com") !== false ||
             strpos($email, "@distromtl.com") !== false ||
             strpos($email, "@test.com") !== false ||
             strpos($email, "@ispqc.com") !== false ||
             strpos($email, "@xferdirect.com") !== false
        ){
            return false;
        }


        return true;
    }

    public function removeGmailVariation($email){
        if ( strpos($email, "@gmail.com") !== false ){
            $prefix = explode("@gmail.com", $email)[0];
            $prefix_without_dots = str_replace(".", "", $prefix);
            return $prefix_without_dots . "@gmail.com";
        }
        return $email;
    }

    public function EntriesLimitValidation($email){
        $node = $this->contestnode;
        $contestentry = $node->field_contest_entry->value;
        $contest_id = $node->id();

        if ($contestentry == '7'){
            // @TODO: Add an index to the email field oin the pepsicontest_reg_contest table
            $sql = "SELECT nb_entries from pepsi_microsite_weekly_entries  WHERE contest_id = " . $contest_id . " AND email = '" . $email . "'" ;

            $select = \Drupal::database()->query($sql);
            $result = $select->fetchAll();

            if (empty($result)){
                return true;
            } else {
                $nb_entries = $result[0]->nb_entries;
                if ($nb_entries  < 7)
                    return true;
            }
            return false;
        } elseif($contestentry == '3') {
            $num_rows = $this->count_today_registrations($contest_id, $email);
            if ($num_rows >= 3)
                return false;
            return true;
        } else {
            return true;
        }

    }



    public function validateCode($code, $contest_type){
        $result = ContestValidator::instance()->validate_upc_code($code, $contest_type);
        return $result;
    }

    public function isLegalAge($province, $bday, $contest_type){
        $result = ContestValidator::instance()->validate_age($province, $bday, $contest_type);
        return $result;
    }

    public function NextPrize($node, $skip_next = false){

        // @TODO: Change the logic about how prizes alternate
        // For now we will just return the same prize
        $contest_prizes = $node->field_contest_prizes->referencedEntities();
        if (!empty($contest_prizes)){
            return array_values($contest_prizes)[0];
        }
        return getNextPrize($node, $skip_next);
    }

    public function getTimeToLaunchDate(){
        $now = get_date_with_site_timezone($this->contestnode->field_opening_date);

        $open_date = $this->contestnode->field_opening_date->getValue();
        if (empty($open_date))
            return [];

      $date = date_create($open_date[0]['value']);
      $str_opendate = date_format($date, "Y-m-d") . " 12:00:00";


        $diff = abs(strtotime($str_opendate) - strtotime($now));


        $days = floor($diff / (60*60*24));
        $hours = floor(($diff - $days *60*60*24) / (60*60));
        $minutes = floor(($diff - $days *60*60*24 - $hours * 60*60 ) / (60));
        $seconds = floor(($diff - $days *60*60*24 - $hours * 60*60 - $minutes*60) / (1));

        $diff_time = [
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'open_date' => $str_opendate,
        ];

        return $diff_time;
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
                ->condition('type', 'contest')
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

    public function setContestSession($contest_type, $key, $value){
        $session = \Drupal::service('session');
        $session->set($contest_type . '_' . $key, $value);
    }


    public function getContestSession($contest_type, $key, $delete_session = 1){
        $session = \Drupal::service('session');
        $val = $session->get($contest_type . '_' . $key);
        if ($val){
            if ($delete_session)
                $session->remove($contest_type . '_' . $key);

            return $val;
        }
        return false;

    }

    public function getCheetosGameUrl($contest, $playerid){


        return Url::fromRoute("en-ca.custom.contest.cheetos.game",['contest'=>$contest, 'playerid' => $playerid ])->toString();
    }

    public function getEntriesPerUPC($upc_code){

        $code_entries = [
			'060410054185' => 3,
            '060410054086' => 3,
			'060410054208' => 3,
			'060410055090' => 3,
			'060410055038' => 3,
			'055577120217' => 3,
			'055577120200' => 3,
			'055577120224' => 3,
			'055577120231' => 3,
			'060410040775' => 2,
			'060410054994' => 2,
			'060410040768' => 2,
			'055577106662' => 2,
			'055577102732' => 2,
			'055577105221' => 2,
			'055577105665' => 2,
			'055577120101' => 1,
			'055577120118' => 1,
			'055577120149' => 1,
            '216736204215' => 1,
		];

        // $upc_code = $post['upccode'];

        if (!$code_entries[$upc_code]){
            return 1;
        }

        return $code_entries[$upc_code];

        // $email = $post['email'];
        // $contest_id = $this->contestnode->id();
        // $today = date('Y-m-d');

        // $sql =  " SELECT count(*) as cnt FROM pepsicontest_reg_contest
        //             WHERE email = '$email'
        //             and contest_id = $contest_id
        //             AND enterdate = '$today'
        //             and upc_code = '$upc_code'
        //              ";
        // $query = \Drupal::database()->query($sql);
        // $extra_entries = 0;
        // try{
        //     $results = $query->fetchAll();
        //     if (!empty($results)){
        //         if( $results[0]->cnt == 0 )
        //             $extra_entries = $code_entries[$upc_code] - 1;

        //     }
        // } catch(\Exception $e){
        //     log_var($sql, "Seach for UPC entries query could not be executed ");
        // }
        // return $extra_entries;


    }

}
