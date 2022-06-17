<?php

/**
 * 
 *
 */

namespace Drupal\pepsibrands;

class SocialFeedServices {


  public function __construct() {
  }

  
  public function socialFetcher() {

    //Get social feed Content Type
    $query = \Drupal::entityQuery('node')
			->condition('status', 1) //published or not
			->condition('type', 'brand_social_feed'); //content type

			//->pager(1); //specify results to return
		$nids = $query->execute();

		if ($nids && count($nids) > 0){
				$node = null;
				foreach($nids as $nid){
          $node = \Drupal\node\Entity\Node::load($nid);
          $instagram_token = $node->get('field_instagram_token')->getValue();

          $fb_appId        = $node->get('field_facebook_app_id')->getValue();
          $fb_appSecret    = $node->get('field_facebook_app_secret')->getValue();
          $fb_pageName     = $node->get('field_facebook_page_name')->getValue();
          $fb_pageId       = $node->get('field_facebook_page_id')->getValue();
          $fb_userToken    = $node->get('field_facebook_user_token')->getValue();
          $fb_permToken    = $node->get('field_facebook_permanent_token')->getValue();


          $instagram_posts = $this->instagramFetcher($instagram_token[0]['value']);

          $facebook_posts = $this->facebookFetcher($fb_appId[0]['value'],$fb_appSecret[0]['value'],$fb_pageName[0]['value'],$fb_pageId[0]['value'],$fb_userToken[0]['value'],$fb_permToken[0]['value']);
          
          $fb_posts       = isset($facebook_posts['fb_posts'])? $facebook_posts['fb_posts'] : array();
          $fb_permToken   = isset($facebook_posts['fb_permToken'])? $facebook_posts['fb_permToken'] : '';

          $posts = $this->mergePost($instagram_posts, $fb_posts );

          $node->set('field_feed_content', serialize($posts));
          $node->set('field_facebook_permanent_token', $fb_permToken);
          $node->save();
        }
		}  


    return true;
  }

  private function mergePost($instagram_posts,$facebook_posts) {
    $posts = array();

    $n = count($instagram_posts) > count($facebook_posts) ? count($instagram_posts) : count($facebook_posts);

    for($i = 0; $i < $n; $i++){
      if (isset($instagram_posts[$i])) $posts[] = $instagram_posts[$i];
      if (isset($facebook_posts[$i])) $posts[] = $facebook_posts[$i];
    }
    return $posts; 
  }


  private function instagramFetcher($token){
    $arr_posts = array();

    if (empty($token)) return $arr_posts;  

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://graph.instagram.com/me/media?fields=media_type%2Cmedia_url%2Cpermalink%2Cthumbnail_url&access_token=$token",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      \Drupal::logger('socialfeed')->info("Instagram fetch failed", []);
    } else {
      $resultarray = json_decode($response);
      $insta_posts = isset($resultarray->data)?$resultarray->data:array();
      $insta_error = isset($resultarray->error)?$resultarray->error:null;
      if (isset($insta_error)) {
        \Drupal::logger('socialfeed')->info("Instagram fetch call failed", []);
      }
      $n = 0;
      foreach($insta_posts as $post) {
        $arr_posts[] = array(
                      'url' => $post->permalink, 
                      "img" => $post->media_url, 
                      "type" => "instagram"
                    );
        $n++;
        if ($n > 6) break;                  
      }
    }
    return $arr_posts;
  }


/*
  private function instagramFetcher2($username) {

    if ($username == '') 
      return array();

    $url = "https://www.instagram.com/" . $username . "/?__a=1";
  
    // header option for file_get_contents
    // need to set user agent, because facebook will check user agent
    $options = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Accept-language: en\r\n" .
                  "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                  "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
      )
    );
  
    // fetch webpage content
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
  
    $resultarray = json_decode($result);
    
    $insta_post = $resultarray->graphql->user->edge_owner_to_timeline_media->edges;
    $arr_posts = array();

    for($i=0 ; $i < count($insta_post);$i++) {
      if ($i >= 6) { break; }  
      //var_dump($insta_post[$i]->node->shortcode);
      $url = 'https://instagram.com/p/' . $insta_post[$i]->node->shortcode;
      $arr_posts[] = array('url' => $url, "img" => $insta_post[$i]->node->thumbnail_src, "type" => "instagram");
    }

    return $arr_posts;

  }
*/
  private function facebookFetcher($fb_appId,$fb_appSecret,$fb_pageName,$fb_pageId,$fb_userToken,$fb_permToken) {
    $fb_posts = array();

    if (empty($fb_appId) || empty($fb_appSecret) || empty($fb_pageName) || empty($fb_userToken) ) return array();   // nothing to fetch

    // IF permanent token is not yet created, has to be generated.
    if (empty($fb_permToken)) {
      //Generate the permanent token
      $fb_permToken = $this->facebookPermaToken($fb_appId,$fb_appSecret,$fb_pageName,$fb_pageId, $fb_userToken);
    }
    if (!empty($fb_permToken)){
      //Pull the Post
      $fb_posts = $this->facebookPosts($fb_pageId, $fb_permToken);
    }

    return  array('fb_posts' => $fb_posts,'fb_permToken' => $fb_permToken); 
  }

  private function facebookPermaToken($fb_appId,$fb_appSecret,$fb_pageName,$fb_pageId,$fb_userToken) {
    $fb_permToken = '';
    
    $fb_longToken = $this->facebookLongToken($fb_appId,$fb_appSecret,$fb_userToken);

    $fb_userId    = $this->facebookUserId($fb_longToken);

    //$fb_pageId    = $this->facebookPageId($fb_pageName, $fb_longToken);

    $fb_permToken = $this->facebookPermToken($fb_userId, $fb_pageId, $fb_longToken);
    
    return $fb_permToken;
  }

  private function facebookLongToken($fb_appId,$fb_appSecret,$fb_userToken) {
    $fb_longToken = '';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://graph.facebook.com/v4.0/oauth/access_token?grant_type=fb_exchange_token&client_id=$fb_appId&client_secret=$fb_appSecret&fb_exchange_token=$fb_userToken",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      \Drupal::logger('socialfeed')->info("Facebook fetch failed - LongToken", []);
    } else {
      $resultarray = json_decode($response);
      $fb_longToken = isset($resultarray->access_token)?$resultarray->access_token:"";
      $error = isset($resultarray->error)?$resultarray->error:null;
      if (isset($error)) {
        \Drupal::logger('socialfeed')->info("Instagram fetch call failed - LongToken - Error", []);
      }
    }
    return $fb_longToken;
  }

  private function facebookUserId($fb_longToken) {
    $fb_userId = '';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://graph.facebook.com/v9.0/me?access_token=$fb_longToken",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      \Drupal::logger('socialfeed')->info("Facebook fetch failed - User Id", []);
    } else {
      $resultarray = json_decode($response);
      $fb_userId = isset($resultarray->id)?$resultarray->id:"";
      $error = isset($resultarray->error)?$resultarray->error:null;
      if (isset($error)) {
        \Drupal::logger('socialfeed')->info("Instagram fetch call failed - User Id - Error", []);
      }
    }
    return $fb_userId;
  }  

  private function facebookPageId($fb_pageName, $fb_longToken) {
    $fb_pageId = '';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://graph.facebook.com/v9.0/$fb_pageName?fields=id&access_token=$fb_longToken",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      \Drupal::logger('socialfeed')->info("Facebook fetch failed - Page Id", []);
    } else {
      $resultarray = json_decode($response);
      $fb_pageId = isset($resultarray->id)?$resultarray->id:"";
      $error = isset($resultarray->error)?$resultarray->error:null;
      if (isset($error)) {
        \Drupal::logger('socialfeed')->info("Instagram fetch call failed - Page Id - Error", []);
      }
    }
    return $fb_pageId;
  }  

  private function facebookPermToken($fb_userId, $fb_pageId, $fb_longToken) {
    $fb_permToken = '';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://graph.facebook.com/v9.0/$fb_userId/accounts?fields=name,access_token&access_token=$fb_longToken",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      \Drupal::logger('socialfeed')->info("Facebook fetch failed - Page Id", []);
    } else {
      $resultarray = json_decode($response);
      $data = isset($resultarray->data)?$resultarray->data:"";
      $error = isset($resultarray->error)?$resultarray->error:null;
      if (isset($error)) {
        \Drupal::logger('socialfeed')->info("Instagram fetch call failed - Page Id - Error", []);
      }
      else{
        foreach($data as $item){
            if ($item->id === $fb_pageId){
              return $item->access_token; //$fb_permToken
            }
        }
      }

    }
    return $fb_permToken;
  } 

  private function facebookPosts($fb_pageId, $fb_permToken) {
    $fb_posts = array();

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://graph.facebook.com/v9.0/$fb_pageId/feed?fields=picture,permalink_url&access_token=$fb_permToken&limit=7",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      \Drupal::logger('socialfeed')->info("Facebook fetch failed - Page Id", []);
    } else {
      $resultarray = json_decode($response);
      $data = isset($resultarray->data)?$resultarray->data:"";
      $error = isset($resultarray->error)?$resultarray->error:null;
      if (isset($error)) {
        \Drupal::logger('socialfeed')->info("Instagram fetch call failed - Page Id - Error", []);
      }
      else{
        foreach($data as $post){
          $fb_posts[] = array(
            'url' => $post->permalink_url, 
            "img" => $post->picture, 
            "type" => "facebook"
          );
        }
      }

    }
    return $fb_posts;
  } 

  
}