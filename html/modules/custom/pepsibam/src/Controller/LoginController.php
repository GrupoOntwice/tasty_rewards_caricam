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

class LoginController extends ControllerBase {

    
  /**
   * {@inheritdoc}
   */
  public function fblogin(Request $request) {

    $fbid = $request->get('id');
    $email = $request->get('email');
    $firstname = $request->get('firstname');
    $lastname = $request->get('lastname');
    
    $referer = $request->get('referer');
    
    //Check if email is registered in the user table.
    $user = null;
    
    if (isset($email) && $email  > '') {
        $user = user_load_by_mail($email);
    }
    
    if (!$user && $fbid) {
        //search user by facebookId
        $user = null;
        $query = \Drupal::entityQuery('user')
                 ->condition('field_fbid',  $fbid);

        $result = $query->execute();
        
        $users_ids = array_keys($result);
        
        if (isset($users_ids[0])) {
            $user = user_load($users_ids[0]);
        }
    }
    
    
    $status = FALSE;
    $route = ""; 
    
    //If user 
    if($user) {
        
        //pepsibam_user_login($user);
        
        /*$marketoId =  $user->field_marketoid->value;
        $epsilon_id = $account->get('field_epsilonid')->value;
        if (!$marketoId) {
            
           //Call Marketo API and get and save marketo Id in user entity
           // MarketoSubscribe($user);
    
        }
        */
       
        if ($user->get('status')->value == 1) { //Active and reidrect to profile page
            if ($referer > '')
                $route = $referer;
            else    
                $route = Url::fromRoute('pepsibam.updateprofile', [],['absolute'=>true ])->toString();
            //Make login 
            user_login_finalize($user);
        }
        else {
            $route = Url::fromRoute('pepsibam.account.blocked', [],['absolute'=>true ])->toString();
        }
        $status = TRUE;
    }
    else {
        $status = TRUE;
        
        //checking if the referer if contest registration page, if yes, we have to redirect that page, otherwise send to regular registration page
        
        if (preg_match('/contest/',$referer) || preg_match('/concours/',$referer) ) {
            $route = $referer;
        }else{
            $route = Url::fromRoute("pepsibam.register")->toString(); 
        }
        
        $session = \Drupal::service('session');
        if (!$session->isStarted()) {
          $session->migrate();
        }
        
        if ($email ) $session->set('email', $email);
        if ($fbid ) $session->set('fbid', $fbid);
        if ($firstname ) $session->set('firstname', $firstname);
        if ($lastname ) $session->set('lastname', $lastname);
        
        
        //Create session values got from FB and have to be displayed in the registration form 
        //fromRoute($route_name, $route_parameters = array(), $options = array()) {
    }
    
    //$data = $session->get('email');
    //\Doctrine\Common\Util\Debug::dump($data);

    $return = array('status' => $status, 'route' => $route , 'user_exist' => !empty($user)? 1: 0 );
    $response = new JsonResponse($return);
    $response->headers->set('Content-Type', 'application/json; charset=utf-8');
    return $response;
  }


  public function google_login(Request $request) {
    // $fbid = $request->get('id');
    $email = $request->get('email');
    $firstname = $request->get('firstname');
    $lastname = $request->get('lastname');
    
    $referer = $request->get('referer');
    
    //Check if email is registered in the user table.
    $user = null;
    
    if (isset($email) && $email  > '') {
        $user = user_load_by_mail($email);
    }
    
    
    $status = FALSE;
    $route = ""; 
    
    //If user 
    if($user) {
       
        if ($user->get('status')->value == 1) { //Active and reidrect to profile page
            if ($referer > '')
                $route = $referer;
            else    
                $route = Url::fromRoute('pepsibam.updateprofile', [],['absolute'=>true ])->toString();
            //Make login 
            user_login_finalize($user);
        }
        else {
            $route = Url::fromRoute('pepsibam.account.blocked', [],['absolute'=>true ])->toString();
        }
        $status = TRUE;
    }
    else {
        $status = TRUE;
    }
    

    $return = array('status' => $status, 'route' => $route, 'exist' => $user? 1: 0 );

    $json = json_encode($return);
    print_r($json);
    exit();

    // return new JsonResponse($return);

  }

}
