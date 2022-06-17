<?php

/**
 * @file
 * Contains \Drupal\fancy_login\Controller\FancyLoginController.
 */

namespace Drupal\pepsibam\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\node\Entity\Node;

class RtpController extends ControllerBase {

    /*
        #Home page
        topLeft
        topFeatured
        coupons

        #Life page
        topPromotion
        coupons

        #Recipe page
        topPromotion
        coupons
    */

    public function getRtpHtmlAction(Request $request) {

        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
                
        $segments = $request->request->get('segments');
        $page = $request->request->get('page');

//        $segments = array("8173");
//        $page = "";

        $pepsisegment = array();
        $rtp = array();
        $rtpnodes = array();
        $segmentid_member = "8170"; // marketo segment id for member

        foreach ($segments as $segmentid) {
            $nodes = \Drupal::entityTypeManager()
                ->getStorage('node')
                ->loadByProperties(['field_marketo_segment_id' => $segmentid]);
            if ( count($nodes) > 0 ){
                $pepsisegment[] = $nodes;
            }
        }

        if (count($pepsisegment) == 0) return new JsonResponse($rtp);
        
        $rnd = mt_rand(0,count($pepsisegment)-1);
        
        $nodes = $pepsisegment[$rnd];

        if (count($nodes) > 0) {
            
            $node = reset($nodes);
            $node_id = key($nodes);

            $nodelanguages = $node->getTranslationLanguages();

            if (isset($nodelanguages[$language])) {
                //getting the node for current language
                $node = $node->getTranslation($language);

                if ($node->isPublished()) {


                    if(in_array($segmentid_member, $segments)){
                        $popup_content = $this->getSegmentNodeSelected($node->field_popup->getValue(), $rtpnodes, $language,'popup');
                        if ($popup_content) {
                            $rtp['popup'] = $popup_content['node_content'];
                            $rtpnodes[] = $popup_content['node_id'];
                        }

                        $leaderboard_content = $this->getSegmentNodeSelected($node->field_leaderboard->getValue(), $rtpnodes, $language,'leaderboard');
                        if ($leaderboard_content) {
                            $rtp['leaderboard'] = $leaderboard_content['node_content'];
                            $rtpnodes[] = $leaderboard_content['node_id'];
                        }
                    }
                    
                    switch ($page) {
                        case "home":
                            $homeTopLeft = $this->getSegmentNodeSelected($node->field_home_top_left->getValue(), $rtpnodes, $language,'topLeft');
                            if ($homeTopLeft) {
                                $rtp['topLeft'] = $homeTopLeft['node_content'];
                                $rtpnodes[] = $homeTopLeft['node_id'];
                            }

                            $homeTopFeatured = $this->getSegmentNodeSelected($node->field_home_top_featured->getValue(), $rtpnodes,$language,'topFeatured');
                            if ($homeTopFeatured) {
                                $rtp['topFeatured'] = $homeTopFeatured['node_content'];
                                $rtpnodes[] = $homeTopFeatured['node_id'];
                            }

                            $homeGrid = $this->getSegmentNodeSelected($node->field_home_grid->getValue(), $rtpnodes,$language,'coupons');
                            if ($homeGrid) {
                                $rtp['coupons'] = $homeGrid['node_content'];
                                $rtpnodes[] = $homeGrid['node_id'];
                            }
                            break;
                        case "life":
                            $homeTopFeatured = $this->getSegmentNodeSelected($node->field_life_top_promotion->getValue(), $rtpnodes,$language,'topFeatured');
                            if ($homeTopFeatured) {
                                $rtp['topPromotion'] = $homeTopFeatured['node_content'];
                                $rtpnodes[] = $homeTopFeatured['node_id'];
                            }

                            $homeGrid = $this->getSegmentNodeSelected($node->field_life_grid->getValue(), $rtpnodes,$language,'coupons');
                            if ($homeGrid) {
                                $rtp['coupons'] = $homeGrid['node_content'];
                                $rtpnodes[] = $homeGrid['node_id'];
                            }
                            break;
                        case "recipe":
                            $homeTopFeatured = $this->getSegmentNodeSelected($node->field_recipe_top_promotion->getValue(), $rtpnodes,$language,'topFeatured');
                            if ($homeTopFeatured) {
                                $rtp['topPromotion'] = $homeTopFeatured['node_content'];
                                $rtpnodes[] = $homeTopFeatured['node_id'];
                            }

                            $homeGrid = $this->getSegmentNodeSelected($node->field_recipe_grid->getValue(), $rtpnodes,$language,'coupons');
                            if ($homeGrid) {
                                $rtp['coupons'] = $homeGrid['node_content'];
                                $rtpnodes[] = $homeGrid['node_id'];
                            }
                            break;
                    }
                }
            }
        }

        return new JsonResponse($rtp);
    }
    
    public function getContentBigSpot($node,$langcode,$spot){
        
       $content = ''; 
       $contenttype = $node->getType();
       
       switch ($contenttype) {
           case 'contest':
                $title = $node->getTranslation($langcode)->title->value;
                $brand = $node->getTranslation($langcode)->field_brand->value;
                $description = $node->getTranslation($langcode)->field_contest_description->value;
                $tag = $node->getTranslation($langcode)->field_contest_tag->value;
                $contesturi = $node->getTranslation($langcode)->field_contest_uri->value;
                
                $landinguri = \Drupal\Core\Url::fromRoute($langcode.'.pepsicontest.contest.signup',array('contest'=>$contesturi),array('absolute'=>True))->toString(); 
                $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_landing_image->target_id);
                
                $image  = $imagefile->getFileUri();
                $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
                //$our_uri = $url->getUri();
                
                $content = '<div id="topCarousel" class="carousel slide" data-ride="carousel">
                 <div class="item dont-remove-class">
                     <a href="' . $landinguri . '">
                         <img class="rtm_home_left_contest homepage_carousel second-slide " src="'. $imageurl .'" alt="'.$title.'" onclick="promotionClickTracking(this);">
                     </a>
                 </div>
                </div>';       
                break;
           case 'advertisement':
                $title = $node->getTranslation($langcode)->title->value;
                $brand = $node->getTranslation($langcode)->field_brand->value;
                
                $adsuri = $node->getTranslation($langcode)->field_ads_link->uri;
                
                $adsuri = str_replace('internal:','',$adsuri);
                
                $target = parse_url($adsuri, PHP_URL_SCHEME)?:'';
                $target = $target?' target="_blank"':'';
                
                $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_ads_image->target_id);
                $image  = $imagefile->getFileUri();
                $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
               
                $content = '<div id="topCarousel" class="carousel slide" data-ride="carousel">
                 <div class="item dont-remove-class">
                     <a href="' . $adsuri . '"' .$target .'>
                         <img class="rtm_home_left_ads homepage_carousel second-slide" src="'. $imageurl .'" alt="'.$title.'" onclick="promotionClickTracking(this);">
                     </a>
                 </div>
                </div>';
                break;
            
       }
       return $content;
    }
    
    public function getContentAds($node,$langcode,$spot){

       switch($spot){
           case 'popup':
               $title = $node->getTranslation($langcode)->title->value;
               if(stripos($title, "member")){
                   $html_banner = $node->getTranslation($langcode)->field_html_banner->value;
                   if(strlen($html_banner)>2){
                       $content = $html_banner;
                   }else{
                       $title = $node->getTranslation($langcode)->title->value;
                       $brand = $node->getTranslation($langcode)->field_brand->value;

                       $adsuri = $node->getTranslation($langcode)->field_ads_link->uri;

                       $adsuri = str_replace('internal:','',$adsuri);

                       $target = parse_url($adsuri, PHP_URL_SCHEME)?:'';
                       $target = $target?' target="_blank"':'';

                       $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_ads_image->target_id);

                       $image  = $imagefile->getFileUri();
                       $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
                       $content = '<a href="'. $adsuri . '"' . $target .' id="mk-popup-callout">
                            <img class="popup_tracking img-responsive" src="'. $imageurl . '" alt="'. $title .'" style="height:auto;width:100%;">
                            </a>';
                       $content ='<div id="nl_memebers" style="background: url('. $imageurl . ');">
                                <div class="nl_close">&nbsp;</div>
                                <div class="clear">&nbsp;</div>
                                <div class="nl_members_btn" id="nl_memebers_en_btn" onclick="window.location.href = \''. $adsuri . '\';">&nbsp;</div>
                              </div>';
                   }
               }else{
                   $content = '';
               }
               break;
           case 'leaderboard':
               $html_banner = $node->getTranslation($langcode)->field_html_banner->value;
               if(strlen($html_banner)>2){
                   $content = $html_banner;
               }else{
                   $title = $node->getTranslation($langcode)->title->value;
                   $title_string = str_replace(" ", "_", $title);
                   $brand = $node->getTranslation($langcode)->field_brand->value;

                   $adsuri = $node->getTranslation($langcode)->field_ads_link->uri;

                   $adsuri = str_replace('internal:','',$adsuri);

                   $target = parse_url($adsuri, PHP_URL_SCHEME)?:'';
                   $target = $target?' target="_blank"':'';

                   $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_ads_image->target_id);

                   $image  = $imagefile->getFileUri();
                   $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
                   $content = '<a href="'. $adsuri . '"' . $target .' id="'.$brand.'-'.$title_string.'">
                            <img class="leaderboard_tracking img-responsive" src="'. $imageurl . '" alt="'. $title .'" style="height:auto;width:100%;">
                            </a>';
               }
               break;
           default:
               $content = '';
               $contenttype = $node->getType();
               switch ($contenttype) {
                   case 'contest':
                       $title = $node->getTranslation($langcode)->title->value;
                       $brand = $node->getTranslation($langcode)->field_brand->value;
                       $description = $node->getTranslation($langcode)->field_contest_description->value;
                       $tag = $node->getTranslation($langcode)->field_contest_tag->value;
                       $contesturi = $node->getTranslation($langcode)->field_contest_uri->value;

                       $landinguri = \Drupal\Core\Url::fromRoute($langcode.'.pepsicontest.contest.signup',array('contest'=>$contesturi),array('absolute'=>True))->toString();
                       $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_landing_image->target_id);

                       $image  = $imagefile->getFileUri();
                       $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
                       //$our_uri = $url->getUri();
                       $target = '';
                       $content = '<a href="'. $landinguri . '"' . $target .' id="mk-coupon-callout">
                            <img class="coupon_tracking" src="'. $imageurl . '" alt="'. $title .'" style="height:auto;width:100%;">
                            </a>';
                       break;
                   case 'advertisement':
                       $title = $node->getTranslation($langcode)->title->value;
                       $brand = $node->getTranslation($langcode)->field_brand->value;

                       $adsuri = $node->getTranslation($langcode)->field_ads_link->uri;

                       $adsuri = str_replace('internal:','',$adsuri);

                       $target = parse_url($adsuri, PHP_URL_SCHEME)?:'';
                       $target = $target?' target="_blank"':'';

                       $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_ads_image->target_id);

                       $image  = $imagefile->getFileUri();
                       $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
                       $content = '<a href="'. $adsuri . '"' . $target .' id="mk-coupon-callout">
                            <img class="coupon_tracking" src="'. $imageurl . '" alt="'. $title .'" style="height:auto;width:100%;">
                            </a>';
                       break;
               }
               break;
       }

        return $content;
    }    

    public function getContentLifeRecipe($node,$langcode,$spot){
        
        $content = ''; 
        $contenttype = $node->getType();
        
        $title = $node->getTranslation($langcode)->title->value;
        $brand = $node->getTranslation($langcode)->field_brand->value;
        
        switch ($contenttype) {
            case 'article':
                $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
                $url = $url->toString();
                
                $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_image->target_id);

                $image  = $imagefile->getFileUri();
                $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();

                $content = '<div class="boxinside grid dont-remove-class" style="background-image:url(\''. $imageurl .'\')">
                <figure class="effect-lily">
                    <figcaption>
                        <div class="article">
                            <h2><span>'. t('Featured article') .'</span>'. $title .'</h2>
                        </div>
                        <a href="'. $url .'">View more</a>
                    </figcaption>           
                </figure>
                </div>';
                break;
            case 'recipe':
                $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
                $url = $url->toString();
                
                $imagefile = \Drupal\file\Entity\File::load($node->getTranslation($langcode)->field_recipe_image->target_id);

                $image  = $imagefile->getFileUri();
                $imageurl = \Drupal\Core\Url::fromUri(file_create_url($image))->getUri();
                
                $content = '<div class="boxinside grid recipe" style="background-image:url(\''. $imageurl .'\')">
                    <figure class="effect-lily ">
                        <figcaption>
                            <div class="recipe">
                                <h2><span>' . t('Tasty') . '</span>'. $title .'</h2>
                            </div>
                            <a href="'. $url .'">View more</a>
                        </figcaption>           
                    </figure>
                </div>';
                break;
        } 
        return $content; 
    }    
    
    public function getNodeContentforTrm($node,$lang,$spot){

        
        $content = '';
        $contenttype = $node->getType();
        
        switch ($spot) {
            case 'topLeft': //
                $content = $this->getContentBigSpot($node,$lang,$spot);
                break;
            case 'topFeatured': // Recipes, Life or Advertisement content type
            case 'topPromotion':
            case 'coupons': // Recipes, Life or Advertisement content type
                switch ($contenttype) {
                    case 'advertisement': 
                    case 'contest':
                        $content = $this->getContentAds($node,$lang,$spot);
                        break;
                    case 'article';
                    case 'recipe';
                        $content = $this->getContentLifeRecipe($node,$lang,$spot);
                        break;
                }
                break;
            case 'popup':
            case 'leaderboard':
                $content = $this->getContentAds($node,$lang,$spot);
                break;
        }
        
        return $content;
    }
    
    
    public function getSegmentNodeSelected($childnodes, $rtpnodes, $lang = 'en',$spot){
        //kint($childnode);
        //Loop all node for this spot and remove the ones are not published
        /*var_dump($type);
        echo('childnodes');
        var_dump($childnodes);
        echo('nodesselectedsofar');
         */
        
        $nodeselected = array();
        $childtmp = array();
        foreach ($childnodes as $node) {
            $node_id = $node['target_id'];
            
            //removing the ones is already in $rtpnodes array
            if ( in_array($node_id, $rtpnodes)){
              continue;  
            }
            //removing the ones are not published
            $nodepublished = Node::load($node_id);
            
            $nodelanguages = $nodepublished->getTranslationLanguages();
            
            if (isset($nodelanguages[$lang])) {
                //getting the node for current language
                $nodepublished = $nodepublished->getTranslation($lang);
            }
            else { //node doesn't exists for the language
                continue;
            }
            if (!$nodepublished->isPublished()){
                continue;
            }
            
            $childtmp[] = $node;
            $nodeselected[] = $nodepublished;
        }

        if (count($childtmp) == 0) return null;
        
        $rnd = mt_rand(0,count($childtmp)-1);
        
        $nodeList['node_id'] = $childtmp[$rnd]['target_id'];
        
        $winnernode = $nodeselected[$rnd];
        
        
        $nodeList['node_content'] = $this->getNodeContentforTrm($nodeselected[$rnd],$lang,$spot);
        
        //Then Pick randomly one of them
        return $nodeList;
    }
    
    

}
