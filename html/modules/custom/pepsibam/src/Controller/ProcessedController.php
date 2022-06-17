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

class ProcessedController extends ControllerBase {


    /**
     * {@inheritdoc}
     */
    public function RequestProcessed() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        $route = \Drupal::request()->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_NAME);
        $home = \Drupal\Core\Url::fromRoute("<front>")->toString();
        $session = \Drupal::service('session');
        $optin = $session->get('optin');
        $langcode = get_current_langcode();
        switch ($route) {
            case 'pepsibam.register.processed':
            case 'fr-ca.pepsibam.register.processed':
            case 'en-ca.pepsibam.register.processed':
            case 'en-us.pepsibam.register.processed':
            case 'es-us.pepsibam.register.processed':
            case 'pepsibam.iframe.register.processed':

                $data['header'] = $optin?  t("You’re all set! Thanks for joining the Tasty Rewards<sup>TM</sup> crew. Get ready for tasty surprises coming your way!")  : t(" Thanks for signing up! Just one more thing… Don’t forget to <a href='/$langcode/my-account'>subscribe</a> to our Tasty Rewards<sup>TM</sup> emails so you can stay in the loop with exciting members-only offers and updates! ") ;
                $data['header2'] = $optin? t("Also, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href='/$langcode/my-account'>here</a>.") : t("Plus, keep an eye out for content and ads tailored to your interests! Check your settings or opt-out at any time <a href='/$langcode/my-account'>here</a>.");
                $data['header2'] = ($langcode == 'en-us' || $langcode == 'es-us')? '' : $data['header2'];


                $data['body'] = ""; //t("Please <b>check your email to activate your account</b> and off you go - soon you’ll have access to coupons, exclusive contests and more from your favourite PepsiCo brands. How tasty is that?");
                $data['tracking'] = "register-processed";
                $data['cta'] = $optin? "Sign me up" : "";
                break;

            case 'pepsibam.updateprofile.processed':
                $data['header'] = t("Your profile has been updated.");
                break;
            case 'pepsibam.updateprofilepwd.processed':
                $data['header'] = t("Your profile has been updated.");
                break;

            case 'pepsibam.unsubscribe.processed':
            case 'en.pepsibam.unsubscribe.processed':
            case 'fr.pepsibam.unsubscribe.processed':
            case 'en-us.pepsibam.unsubscribe.processed':
            case 'es-us.pepsibam.unsubscribe.processed':
                $data['header'] = t("You’ve successfully been unsubscribed.");
                $data['body'] = '<a class="btn btn_red" href="'. $home .'">' . t('Go back to Tasty Rewards<sup>TM</sup>') . '</a>';
                $data['tracking'] = "unsubscribe-processed";
                break;

            case 'pepsibam.activationform.processed':
                $data['header'] = t("Your request was processed, we sent you an email with instructions");
                break;

            case 'pepsibam.resetpwdrequestform.processed':
                $data['header'] = t("Thanks!");
                $data['subheader'] = t("An email has been sent to the address provided with steps to follow in order to reset your password.");
                $data['body'] = '<a class="btn btn_red btn_width_set" href="'. $home .'">' . t('Go back to Tasty Rewards<sup>TM</sup>') . '</a>';
                $data['isPasswordReset'] = 1;
                break;

            case 'pepsibam.changepassword.processed':
                $data['header'] = t("Your password was updated, you can login now");
                break;

            case 'pepsibam.account.blocked':
                $path = Url::fromRoute('pepsibam.activationform', [],['absolute'=>true ])->toString();
                $data['header'] = t("Your account is not active, please click <a href=':activatepath'>here</a> to activate it",array(':activatepath' => $path ));
                break;


            default:
                $data['header'] = t("Page not found");
                break;
        }

        $data['header'] = isset($data['header']) ? $data['header'] : '';
        $data['subheader'] = isset($data['subheader']) ? $data['subheader'] : '';
        $data['body'] = isset($data['body']) ? $data['body'] : '';


        //Getting language and passing to twig
        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();

        return array(
            '#theme' => 'pepsibam_processed_template',
            '#data' => $data,
        );

    }
    public function ajax_popup_check_close(Request $request){
        $user_id = $request->request->get('uid');
        $return = ['status' => 'FAIL'];

        if (!empty($user_id)){
            // Save user ID and date in the new table
            $sql = "SELECT * from pepsi_popup_form WHERE user_id = $user_id";
            try{
                $select = \Drupal::database()->query($sql);
                $result = $select->fetchAll();
                if (!empty($result)){
                    $close_until = $result[0]->date_exit;
                    $today = date("Y-m-d H:i:s");
                    $show_popup = ($today > $close_until )? 1 : 0;

                    $return = [
                        'status' => 'SUCCESS',
                        'show_popup' => $show_popup,
                        'close_date' => $result[0]->date_exit,
                    ];
                }
            } catch(\Exception $e){

                \Drupal::logger('custom-module')->info(" Could not fetch data from table pepsi_popup_form", []);
            }
            

        }


        return new JsonResponse($return);

    }


    public function ajax_popup_close(Request $request){
        $user_id = $request->request->get('uid');
        $date = $request->request->get('date');
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        // $date = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $return = ['status' => 'FAIL'];

        if (!empty($user_id)){
            $result = savePopupUserAction($user_id, $date);
            if ($result){
                $return = [
                    'status' => 'SUCCESS',
                    'message' => 'User ' . $user_id . ' successfully updated',
                ];
            }

        }


        return new JsonResponse($return);
    }

    public function ajax_popup_optin(Request $request){
        $user_id = $request->request->get('uid');
        $optin = $request->request->get('optin');
        $optin3 = $request->request->get('optin3');
        $optin3 = $optin3 ? 1 : 0;

        $return = ['status' => 'FAIL'];

        if (!empty($user_id)){
            $user = \Drupal\user\Entity\User::load($user_id);

            $sso_service = \Drupal::service('services.sso');

            $user->set('field_optin', $optin); 
            $user->set('field_optin3', $optin3); 
            $user->save();

            $ochuser =  $sso_service->UpdateOchUser($user);
            if (!$ochuser){
                return new JsonResponse($return);                         
            }

            // sfmcservices_subscribe($user, $source = 'popupTastyrewardsMembers'); // new sourceID

            // $next_year = date("Y-m-d H:i:s", strtotime("+1 year"));
            // Do we need this though?
            // $result = savePopupUserAction($user_id, $next_year);

            \Drupal::logger("custom-module")->info("User $user_id opted in to Tastyrewards",[]);

            $return = [
                'status' => 'SUCCESS',
                'message' => 'User ' . $user_id . ' successfully updated',
            ];

        }


        return new JsonResponse($return);
    }

    /**
     * {@inheritdoc}
     */
    public function ajax_saverecipevote_callback(Request $request) {

        $node_id = $request->request->get('node');
        $vote_value = $request->request->get('value');
        $langcode = $request->request->get('langcode');
        $total = 0;
        $average = 0;

        try {
            // $node = Node::load($node_id);
            $node_recipe = Node::load($node_id);
            if ($langcode == 'en-us' || $langcode == 'es-us'){
                $node = $node_recipe->getTranslation($langcode);
            } else {
                $node = $node_recipe->getTranslation('en');
            }
            $nb_votes = empty($node->get('field_rating_voters')->value ) ? 0 : $node->get('field_rating_voters')->value;
            $total_rating = empty($node->get('field_total_rating')->value ) ? 0 : $node->get('field_total_rating')->value;
            $node->set('field_rating_voters', ++$nb_votes);
            $total_rating += $vote_value;
            $node->set('field_total_rating', $total_rating);
            $node->save();
            $average = $total_rating/ $nb_votes;
            if ($langcode != 'en-us' && $langcode != 'es-us'){
                $node = $node_recipe->getTranslation('fr');
                $node->set('field_rating_voters', $nb_votes);
                $node->set('field_total_rating', $total_rating);
                $node->save();
            }

            // $nid = \Drupal::database()->insert('rating')
            //     ->fields(array(
            //         'node_id' => $node_id,
            //         'vote_value' => $vote_value,
            //     ))
            //     ->execute();

            // $average = $this->getAverage($node_id, $total);

        } catch (\Exception $e) {

            $errors = t('Error ocurred when processing');

        }
        $return = array('average' => $average, 'total' => $total, 'errors' => $errors);

        return new JsonResponse($return);
    }

    //
    public function getAverage($node_id, &$total){

        $query = \Drupal::database()->select('rating', 'rt');
        $query->condition('node_id', $node_id);
        $query->fields('rt', array('vote_value'));

        $result = $query->execute();

        $sum = 0;
        $total = 0;
        foreach($result as $row) {
            $sum +=  $row->vote_value;
            $total ++;
        }

        return round($sum/$total);

    }

    /**
     * {@inheritdoc}
     */
    public function ajax_updaterecipevote_callback(Request $request) {

        $node_id = $request->request->get('node');
        $vote_value = $request->request->get('value');
        $old_value = $request->request->get('old_value');

        try {
            // $node = Node::load($node_id);
            $node_recipe = Node::load($node_id);
            if ($langcode == 'en-us' || $langcode == 'es-us'){
                $node = $node_recipe->getTranslation($langcode);
            } else {
                $node = $node_recipe->getTranslation('en');
            }
            $nb_votes = empty($node->get('field_rating_voters')->value ) ? 0 : $node->get('field_rating_voters')->value;
            $total_rating = empty($node->get('field_total_rating')->value ) ? 0 : $node->get('field_total_rating')->value;
            $node->set('field_rating_voters', ++$nb_votes);
            $total_rating += $vote_value;
            $node->set('field_total_rating', $total_rating);
            $node->save();
            if ($langcode != 'en-us' && $langcode != 'es-us'){
                $node = $node_recipe->getTranslation('fr');
                $node->set('field_rating_voters', $nb_votes);
                $node->set('field_total_rating', $total_rating);
                $node->save();
            }
            $average = $total_rating/ $nb_votes;
            // $query = \Drupal::database()->select('rating', 'rt');
            // $query->condition('node_id', $node_id);
            // $query->condition('vote_value', $old_value);
            // $query->fields('rt', array('id'));
            // $query->range(0, 1);
            // $nid = $query->execute()->fetchField();

            // if ($nid){
            //     $query1 = \Drupal::database()->update('rating');
            //     $query1->fields([
            //         'vote_value' => $vote_value
            //     ]);
            //     $query1->condition('id', $nid);
            //     $query1->execute();
            // }
            // $total = 0;
            // $average = $this->getAverage($node_id, $total);

        } catch (\Exception $e) {

            $errors = t('Error ocurred when processing');

        }
        $return = array('average' => $average, 'total' => $total, 'errors' => $errors);

        return new JsonResponse($return);
    }

    public function ajax_verifypollvote_callback(Request $request) {

        $pollids = $request->request->get('pollid');
        $pollid = intval($pollids);
        $status = '0';
        if ($pollid > 0 ){
            $status = getPollIsVoted($pollid);
        }
        $return = array('status' => $status);

        return new JsonResponse($return);
    }

}
