<?php

/**
 * @file
 * Contains \Drupal\fancy_login\Controller\FancyLoginController.
 */

namespace Drupal\pepsibam\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use FuelSdk\ET_DataExtension_Row;
use FuelSdk\ET_DataExtension_Column;
use FuelSdk\ET_DataExtension;
use FuelSdk\ET_Client;
use FuelSdk\ET_POST;
use FuelSdk\ET_Get;
use FuelSdk\ET_Folder;
use Drupal\microsite_contest\ContestWinnerPicker;
use Drupal\pepsibam\CronScheduledTasks;
use Drupal\pepsibam\ReportGenerator;
use Drupal\pepsibrands\BrandsContentFilters;
use Drupla\pepsibrands\BrandsContentImport;
use Drupal\pepsibam\ContentQueries;
class TestController extends ControllerBase {

    /**
     * {@inheritdoc}
     */
    /*    public function test(Request $request) {

      set_time_limit (0);

      $start = $request->query->get('start');
      $end = $request->query->get('end');

      if (!$start || !$end ) {
      echo "No parameters set";
      exit;
      }
      SyncPingNumber($start,$end);


      exit;
      }
     */
    public function test(Request $request) {
        print_r("<pre>");
        $country_code = 'usa';
        $langcode = 'en';
        $contest_node = \Drupal\node\Entity\Node::load(4205);
        // get_product_category_background('Lays', '/brands/lays/products-categories/lays-wavy');
        // get_block_content('Bare', 'social');
        // $occasions = BrandsContentFilters::instance()->fetch_recipes_by_occasion(135);
        // die(" oower");
       //  $brands = [
       //          'tostitos', 'lays', 'bare', 'fritolay',
       //          'crispyminis', 'otep', 'cheetos',
       //          'missvickies', 'flaminhot', 'smartfood', 
       //          'stacys', 'capncrunch', 'sunchips', 'quaker',
       //          'doritos', 'ruffles',
       //      ];
       // foreach ($brands as $brand) {
       //    echo "updating coupon for $brand \n";
       //    update_brand_coupon_content($brand);
       // }
       $obj = new \Drupal\ssoservices\PimcoreApi();
       $obj->handshake(56);
       $headers = $obj->getCustomer();
       die;
       brands_content_population('buynow', 'links', 'en');
        // export_winners_csv();
        // send_contest_email("rashi@bamstrategy.com", "www.tastyrewards.com", "Rashi", "Test prize", $contest_type = 'grabsnack' );
        // run_contest_seeding();
        // brands_content_population($brand, 'product_categories', 'en');
        $brand = 'popcorners';
        brands_content_population($brand, 'product', 'en');
        brands_content_population($brand, 'product', 'fr');
        die(" coron run ");
        brands_content_population($brand, 'oatcarousel', 'en');
        // brands_content_population($brand, 'slides', 'en');
        // brands_content_population($brand, 'recipe', 'en');
        
        // brands_content_population('lays', 'product_categories', 'fr');
        // brands_content_population('tostitos', 'slides', 'fr');
        // tostitos_content_population('lays', 'slides', 'fr');

         $uri = "public://2020-10/5_Easy_Ways_to_Decorate_a_Pumpkin_mobile.jpg";
          // $uri = "public://2018-10/PepsiCoFoodsCanada_Coupon_FR.jpg";
          $files = \Drupal::entityTypeManager()
          ->getStorage('file')
          ->loadByProperties(['uri' => $uri]);
          $file = array_values($files)[0];
          debug_var($files, 1);


        $last_run = CronScheduledTasks::instance()->time_since_last_run("syncUserIDs");
        // print_r($last_run); die(" last ");
        $cron = CronScheduledTasks::instance()->run_every("syncUserIDs", $hours = 1);
        // $questions = getContestQuestionsAnswers($contest_node, "en");
        $res = validate_ip_address("23.45.134.56"); 
        print_r($res);
        die(" inserted");
        insert_ip_address("23.45.134.56"); die(" inserted");
        syncContestPollAnswers(); die("polls");
        print_r($questions); die(" Questions");
        $account = user_load(487658);
        $myuser = user_load_by_mail("02fatboy30@gmail.com");
        print_r($myuser); die(" users");
        

        pepsibam_clear_flood($account);
        $prize = getNextPrize($contest_node);
        $is_winner = ContestWinnerPicker::instance()->resetWeeklyContestEntries();
        print_r($prize->Id()); die(" prize");
        $is_winner = ContestWinnerPicker::instance()->draw_instant_winner($contest_node);
        die(" not active ");
        $openingdate = $contest_node->getTranslation($langcode)->field_opening_date->date; 
        print_r($openingdate->format('Y-m-d')); die;
        $SFMC_Service = \Drupal::service('sfmcservices.apicall');
        $account = user_load(491283); // 300998,  290807 , || 290824 (rotsy@bamstrategy.com)
        sfmcservices_contest_winner($account, $contest_node);
        die(" Contest saved ");
        $SFMC_Service->init_api($country_code);
        $result = $SFMC_Service->find_email_fb_list('ibreadguy@aol.com');
        print_r($result);
        die("search");
        $sf_obj = new ET_DataExtension();
        $sf_obj->authStub = $SFMC_Service->get_client();
        print_r($sf_obj); die;


        //--------------
        
        sfmcservices_trigger_email_activation($account);
        print_r($_SERVER);
        // unblock_usa_users();
        die(" unblocked");
        // syncPollInfo();
        die(" Synced POLL ");
        reSyncContestInfo();
        $subscriber = UserFieldsSFMC($account, false, 'tastyrewards');
        $country_code = 'ca';
        $SFMC_Service = \Drupal::service('sfmcservices.apicall');
        $SFMC_Service->init_api($country_code);


        $sf_obj = new ET_DataExtension();

        $SFMC_Service = \Drupal::service('sfmcservices.apicall');

        $sf_obj->authStub = $SFMC_Service->get_client();

        $contest_data = contest_data();
        // print_r($subscriber);


        $leadresponse = $SFMC_Service->syncContest($contest_data, $country_code);
        // $leadresponse = $SFMC_Service->createOrUpdateLeads($subscriber, $country_code);
        print_r($leadresponse);
        die(" lead ");

        // $account = user_load(299726);
        // print_r($account->Id());
        // $result = sfmcservices_subscribe($account, 'lays');
        // print_r('user saved');
        // die;
        // $sourceID = 'mysourceID';
        // $user->set("field_marketoid", $sourceID);
        // $user->save();


        $result = reSyncUserInfo();
        print_r('synched');
        die;
        $source = 'cheetos';
        $result = get_registration_source($source, 'en-us');
        print_r($result);

        $config = \Drupal::service('config.factory')->getEditable('mymodule.settings');
         $result =    $config->get('myvar');
         print_r($result);
         die;

      
        $sourceID =  get_registration_source('lays');

        print_r($sourceID);
        die;



        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        print_r($prefixes);

        die("\n USER sync info executed \n");
        // $token = $SFMC_Service->get_auth_token();
        // print_r($token);

        $result = sfmcservices_subscribe($account, true);
        print_r($result);
        die("--- SF update ");

        $result = sfmcservices_trigger_email_activation($account);
        // $result =  $SFMC_Service->createOrUpdateLeads($subscriber);
        print_r($result);
        // die("----- DIE ------ ");



        print_r($subscriber);
        $payload = json_encode($subscriber);
        print_r($payload);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://mcmqczd118jdz8xqdfsbxdwh96f0.rest.marketingcloudapis.com/hub/v1/dataevents/key:CF_Staging_Profile/rowset');

        curl_setopt($ch, CURLOPT_POSTFIELDS, ''. $payload);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $curl_result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            die("---ERROR ");
        }
        $result = json_decode($curl_result);
        print_r($result);
        print_r($result->access_token);
        curl_close ($ch);
        die("curl results");

        die;

        print_r($payload);
        // die;

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/

        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // curl_setopt($ch, CURLOPT_POSTFIELDS, "{ \"grant_type\": \"client_credentials\", \"client_id\": \"owi6eueg404ubcvotmjspuf1\", \"client_secret\": \"DP8EKgin6ak8K3EPogaTwmCE\", \"scope\": \"data_extensions_read data_extensions_write\", \"account_id\": \"100019596\" }");




        // $sf_obj = new ET_Folder();
        $sf_obj = new ET_DataExtension();

        $SFMC_Service = \Drupal::service('sfmcservices.apicall');

        $sf_obj->authStub = $SFMC_Service->get_client();
        $token = $sf_obj->authStub->getAuthToken();
        $account = user_load(290783); // 290783

        sfmcservices_trigger_email_activation($account);
        die();

        $subscriber = UserFieldsSFMC($account);
        // print_r($subscriber);
        print_r($token);
        $getResult = $sf_obj->get();
        print_r($getResult);
        die();
        $sf_objs[] = $getResult->results;

        // while($getResult->moreResults) {
        //     $getResult = $getDE->getMoreResults();
        //     $sf_objs[] = $getResult->results;
        // }
    
        die;

        // $account->setEmail("rotsybam@mail.com");
        // print_r(get_class_methods($account));
        // print_r($account);
        $response = sfmcservices_subscribe($account);
        // $response = sfmcservices_trigger_email_activation($account);
        // $response = sfmcservices_trigger_forgotpassword($account);
        print_r("USER forgot password \n");
        print_r($response);
        $sfmc_config = \Drupal::config('sfmcservices.adminsettings');
        $MID = $sfmc_config->get('sfmcservices_mid');


        // exit;
    }

    public function monthly_report(Request $request){
        $langcode = get_current_langcode();
        $start_date = \Drupal::request()->query->get('start_date');
        $end_date = \Drupal::request()->query->get('end_date');

        $o_report = new ReportGenerator();
        $o_report->set_date_ranges($start_date, $end_date);
        $report = $o_report->generate_report($langcode);

        debug_var($start_date);
        debug_var($report, 1);



    }

    public function welcomeemail(Request $request) {
      // $container = \Drupal::getContainer();

      $account = user_load(290774); //290776
      // $account->setEmail("rotsybam@mail.com");
      $email = $account->get('mail')->value;
      // print_r(get_class_methods($account));
      // $subscriber = subscriber_attributes($account);
      $zipcode = $account->get('field_postalcode')->value;
      print_r($zipcode);
      print_r($email);
      $country_code = get_user_country_code($account);
      echo "\n";
      print_r($country_code);
      echo "\n";

      // $response = sfmcservices_subscribe($account);
      $response = sfmcservices_trigger_email_activation ($account, $resend = false);
      // $response = sfmcservices_trigger_email_activation($account);
      // $response = sfmcservices_trigger_forgotpassword($account);
      print_r($response);
      die("die");

       
    $sfmc_config = \Drupal::config('sfmcservices.adminsettings');


    $MID = $sfmc_config->get('sfmcservices_mid'); 
  
      
        // exit;

     }

        // exit;


    public function forgotpass(Request $request) {

        $tt = \Drupal::service('taxonomy_tree.taxonomy_term_tree');     
        $tree = $tt->load('recipe_search_category');

        print_r("<pre>");
        var_dump($tree);
        print_r("</pre>");
        exit;
        // exit;
    }
    
    public function social(Request $request) {

        $socialfeedService = \Drupal::service('socialfeedservices.postfetcher');

        $result = $socialfeedService->socialFetcher();

        print_r("<pre>");
        var_dump($result);
        print_r("</pre>");
        exit;
    }    
    
    
    private function sendEmailJson() {
        return [
            "to" => [
                "address" => "miguel@bamstrategy.com",
                "subscriberKey" => "miguel@bamstrategy.com",
            ],
            "options" => [
                "requestType" => "ASYNC",
            ],
        ];
    }

    public function testExport(Request $request) {

       
        //$str = BrandsContentImport::instance()->format_body_articles($text);
        es_content_population();
        es_content_article_population();
        exit;

        $node = Node::load(1158);

        $sourceLanguageKey = 'en-us';
        $translationLanguageKey = 'es-us';
        
        $source_translation = $node->getTranslation($sourceLanguageKey);
        $target_translation = $node->addTranslation($translationLanguageKey, $source_translation->toArray());

        // Make sure we do not inherit the affected status from the source values.
        if ($node->getEntityType()->isRevisionable()) {
            $target_translation->setRevisionTranslationAffected(NULL);
        }

        //$entityManager = \Drupal::getContainer()->get('entity.manager');
        //$user = $entityManager->getStorage('user')->load(45986);

        $translationManager = \Drupal::getContainer()->get('content_translation.manager');
        $metadata = $translationManager->getTranslationMetadata($target_translation);
        $metadata->setSource($sourceLanguageKey);
        $node->save();


        exit;
    }

    public function unblock(Request $request){
        $emails = ['testing2@testing.ca', 'testing@testing.ca'];

        foreach ($emails as $email) {
            $user = user_load_by_mail($email);
            if (!$user || $user->isActive())
                continue;
            echo $email. " ";
            $user->activate();
            $user->save();
        }

        die(" unblocked");
    }


}
