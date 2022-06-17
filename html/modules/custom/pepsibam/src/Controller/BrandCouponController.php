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
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Contains the callback handler used by the OneAll Social Login Module.
 */
class BrandCouponController extends ControllerBase {

    private $segment_ids = "";

    /**
     * This is the callback handler (referenced by routing.yml).
     */
    public function index(Request $request, $brand) {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        $data = array();
        $data['iframe_url'] = "";
        $data['coupon_body'] = "";

        $nid_coupon = $this->getCouponContent($brand);
        if (empty($nid_coupon))
            return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 

        $node = \Drupal\node\Entity\Node::load($nid_coupon);



        $current_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $brand_field_value = $node->getTranslation($current_lang)->get('field_coupon_brand')->value;
        if ($brand != $brand_field_value){
            $route = $current_lang . ".pepsibam.brands.coupon";
            return new RedirectResponse(\Drupal\Core\Url::fromRoute($route, ['brand' => $brand_field_value])->toString()); 
        }

        $data['title'] = $node->getTranslation($current_lang)->get('title')->value;
        $data['subtitle'] = $node->getTranslation($current_lang)->get('field_subtitle')->value;
        $data['description'] = $node->getTranslation($current_lang)->get('body')->value;
        $data['coupon_description'] = $node->getTranslation($current_lang)->get('field_coupon_description')->value;
        $data['coupon_title'] = $node->getTranslation($current_lang)->get('field_coupon_title')->value;
        $this->segment_ids = $node->getTranslation($current_lang)->get('field_segment_id')->value;
        $absolut_url = $node->getTranslation($current_lang)->get('field_iframe_url')->value;
        $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($current_lang)->field_coupon_image->target_id);
        $imagefile_mobile = \Drupal\file\Entity\File::load($node->getTranslation($current_lang)->field_mobile_image->target_id);
        $banner_image = \Drupal\file\Entity\File::load($node->getTranslation($current_lang)->field_header_image->target_id);
        $banner_img_mobile = \Drupal\file\Entity\File::load($node->getTranslation($current_lang)->field_header_image_mobile->target_id);
        $data['coupon_image'] = $imagefile->getFileUri();
        $data['mobile_image'] = $imagefile_mobile->getFileUri();
        $data['banner_image'] = $banner_image->getFileUri();
        $data['banner_image_mobile'] = $banner_img_mobile->getFileUri();


        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();  //Getting language and passing to tempalte
        $user_id = \Drupal::currentUser()->id();

        $error = false;
        $device_no_supported = 0;

        //if the user is logged in show the coupon iframe, otherwise show the message to login os sig up
        //$mobileDetector = \Drupal::service('krs.mobile_detect');

        /* if ($mobileDetector->isMobile() || $mobileDetector->isTablet() ){
          if ($mobileDetector->isSafari() ){
          $device_no_supported = 1;
          }
          } */

        if ($absolut_url != ''){
            $iframeuri = $absolut_url;
        } else {
            if ($user_id > 0) {


                $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
                $iframeuri = $this->getWebsaverIframe($user, $data['language']);
            } else {

                $iframeuri = $this->getWebsaverIframe(null, $data['language']);
            }
        }
        
        if ($iframeuri)
            $data['iframe_url'] = $iframeuri;
        else {
            $error = true;
            \Drupal::logger('pepsibam coupon')->notice('coupon uri generator fail for user : @user , browser : %browser', array(
                '@user' => $user_id,
                '%browser' => $_SERVER['HTTP_USER_AGENT']
            ));
            $node = \Drupal\node\Entity\Node::load(13); //load the coupon node for not authenticated ussers
            $content_translation = $node->getTranslation($data['language']);

            $data['coupon_body'] = $content_translation->body->value;
        }

        /* }else{
          $node = \Drupal\node\Entity\Node::load(13); //load the coupon node for not authenticated ussers
          $content_translation = $node->getTranslation($data['language']);

          $data['coupon_body'] = $content_translation->body->value;
          } */

        $data['error'] = $error;
        $data['device_no_supported'] = $device_no_supported;

        $env = \Drupal\Core\Site\Settings::get("environment");
        if ($env == "dev" || $env == "staging") {
            $data['iframe_domain'] = 'https://stagegears.websaver.ca/';
            
        } else {
            $data['iframe_domain'] = 'https://gears.websaver.ca/';
        }


        return array(
            '#theme' => 'pepsibam_brand_coupon_template',
            '#data' => $data,
        );
    }

    public function rules(Request $request, $brand) {
        
        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

        //Getting language and passing to twig
        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();

        //check if contest exists 
        if (!$this->getCouponContent($brand)) {
            //return new RedirectResponse(\Drupal\Core\Url::fromRoute($langcode . ".pepsicontest.contest.processed.notfound", array('contest'=>$contest))->toString()); 
            return new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $nid_coupon = $this->getCouponContent($brand);
        $node = \Drupal\node\Entity\Node::load($nid_coupon);

        $current_lang = \Drupal::languageManager()-> getCurrentLanguage()->getId();

        $data['coupon_rules'] = $node->getTranslation($current_lang)->get('field_coupon_rules')->value;

        return array(
            '#theme' => 'pepsibam_brand_coupon_rules_template',
            '#data' => $data,
        );
    }    

    //get the coupon iframe from websaver
    function getWebsaverIframe($user, $lang) {
        //client_id, client_secret, oauth_domain for testing and production are provided by BAM
        $client_id = '25_ojt0z1rrez480884880sg8w0o0cc8co0s4s4s000owkw4k4cc';
        $client_secret = '3oaqzschwhkw0goookccs48cg04sk84sk044gwkcwwg0co4o0k';
        $segment_ids = $this->segment_ids;
     
        $iframe_width = 1220;


        $env = \Drupal\Core\Site\Settings::get("environment");
        if ($env == "dev" || $env == "staging") {
            $oauth_domain = 'https://stagegears.websaver.ca/app_stage.php';
            //$oauth_domain = 'https://gears.websaver.ca';
        } else {
            $oauth_domain = 'https://gears.websaver.ca';
        }


        $oauth2token_url = $oauth_domain . "/oauth/v2/token";

        $clienttoken_post = array(
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "grant_type" => "client_credentials",
        );

        $chtoken = send_curl_request($oauth2token_url, $clienttoken_post);
        

        if ($authObj = json_decode($chtoken)) {
            if (isset($authObj->access_token) && $authObj->access_token > '') {
                $access_token = $authObj->access_token;

                /* if (\Drupal::request()->getClientIp() == '70.35.213.98') 
                  $api_url_extra_data = $oauth_domain . '/oauth/v2/api/RegisterExtraData?accesss_token=' . $access_token;
                  else */
                $api_url_extra_data = $oauth_domain . '/oauth/v2/api/RegisterExtraData?access_token=' . $access_token;

                if ($user != null) {
                    $user_data_arr = array(
                        'first_name' => $user->get('field_firstname')->value,
                        'last_name' => $user->get('field_lastname')->value,
                        'email' => $user->get('mail')->value,
                        'city' => $user->get('field_city')->value,
                        'province' => $user->get('field_province')->value,
                        'postalcode' => $user->get('field_postalcode')->value
                    );
                    $extra_data = array('user_id' => $user->Id(), "segment_id" => $segment_ids, 'user_data' => json_encode($user_data_arr));
                } else {
                    $extra_data = array('user_id' => 0, "segment_id" => $segment_ids);
                }
                $chextradata = send_curl_request($api_url_extra_data, $extra_data);

                if ($dataObj = json_decode($chextradata)) {
                    if ($lang == 'en-us') $lang = 'en'; // calling en-us on the iframe results in error
                    if ($lang == 'es-us') $lang = 'es'; // calling en-us on the iframe results in error
                    if (isset($dataObj->message) && $dataObj->message == 'success') {
                        return $oauth_domain . '/oauth/v2/api/' . $lang . '/UserCouponFrame?access_token=' . $access_token;
                    }
                }
            }
        }
        return false;
    }

    public function getCouponContent($brand){
        $em = \Drupal::service('entity_type.manager');
        $date = date('Y-m-d\TH:i:s');
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
        $lang_url = $prefixes[$langcode];

        $query = $em->getStorage('node')->getQuery()->condition('status', 1)
                ->condition('type', 'coupon')
                ->condition('field_coupon_brand', $brand, '=')
                ->condition('langcode', $langcode)
        ;

        $nids = $query->execute();
        if (!empty($nids)){
            return array_values($nids)[0];
        }
            // debug_var($nids, 1);

        return;
    }

}
