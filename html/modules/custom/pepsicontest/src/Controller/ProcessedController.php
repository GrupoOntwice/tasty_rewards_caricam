<?php
/**
 * @file
 * Contains \Drupal\fancy_login\Controller\FancyLoginController.
 */
 
namespace Drupal\pepsicontest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;

class ProcessedController extends ControllerBase {

    
  /**
   * {@inheritdoc}
   */
  public function index() {

    \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

    $route = \Drupal::request()->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_NAME);
    $home = \Drupal\Core\Url::fromRoute("<front>")->toString();
    
    switch ($route) {
        case 'en.pepsicontest.contest.thank-you':
        case 'fr.pepsicontest.contest.thank-you':    
            $data['header'] = t('Your entry has been received.');
            $path = Url::fromRoute('pepsibam.coupon')->toString();
            $data['body'] = t('As a thank you for entering our contest, click <a href=":path">here</a> for a coupon to use at your next purchase!', array(":path"=>$path)); 
            $data['h3'] = t('Good luck!'); 
            break;    

        case 'en.pepsicontest.contest.preocessed.notlogged':
        case 'fr.pepsicontest.contest.preocessed.notlogged':    
            $data['header'] = t('Opps, you are not logged in');
            $path = Url::fromRoute('user.login')->toString();
            $data['body'] = t('please click <a href=":path">here</a> to login', array(":path"=>$path)); 
            break;    
 
        case 'en.pepsicontest.contest.processed.notfound':
        case 'fr.pepsicontest.contest.processed.notfound':    
            $data['header'] = t('Opps, contest not found ');
            $data['h3'] = t('<a href=":path">Return to homepage</a>', array(":path"=>$home));  
            break;    

        case 'en.pepsicontest.contest.processed.closed':
        case 'fr.pepsicontest.contest.processed.closed':    
            $data['header'] = t('Thank you for your interest. The contest is now closed.');
            
            $data['h3'] = t('<a href=":path">Return to homepage</a>', array(":path"=>$home));  
            break;    
       
        case 'en.pepsicontest.contest.processed.comingsoon':
        case 'fr.pepsicontest.contest.processed.comingsoon':    
            $data['header'] = t('CONTEST COMING SOON!');
            $data['body'] = t('Contests is not available at the moment. Please check back soon.'); 
            $data['h3'] = t('<a href=":path">Return to homepage</a>', array(":path"=>$home));  
            break;    
        
        
        
        case 'en.pepsicontest.contest.processed.alreadyregistered':
        case 'fr.pepsicontest.contest.processed.alreadyregistered':    
            $data['header'] = t('Good news!');
            $data['body'] = t('You have already entered this contest. You can only participate once for the duration of the Contest.'); 
            $data['h3'] = t('<a href=":path">Return to homepage</a>', array(":path"=>$home)); 
            break;    
        
        
        
        default:
            $data['header'] = t("Page not found");
            break;
    }
    
    $data['header'] = isset($data['header']) ? $data['header'] : '';
    $data['subheader'] = isset($data['subheader']) ? $data['subheader'] : '';
    $data['body'] = isset($data['body']) ? $data['body'] : '';
    $data['h3'] = isset($data['h3']) ? $data['h3'] : '';
    
    
    //Getting language and passing to twig
    $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
        
    return array(
            '#theme' => 'pepsicontest_processed_template',
            '#data' => $data,
    );
    
  }
  
}
