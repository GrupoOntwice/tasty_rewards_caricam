<?php

/**
 * @file
 */

namespace Drupal\ssoservices\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;


class SsoController extends ControllerBase {

    /**
     * {@inheritdoc}
     */
    public function ajax_login(Request $request) {

        $sso_service = \Drupal::service('services.sso');

        $params = array();
        $content = $request->getContent();
        if (!empty($content)) {
        // 2nd param to get as array
            $params = json_decode($content, TRUE);
        }
        $accessToken = $params['accessToken'];
        $idtoken  = $params['idtoken'];
        $message = "ok";
        if (empty($accessToken) || empty($idtoken)){
            $status = false;
            $message = 'No token provided';
        }
        else{
            $status = $sso_service->LoginUser($accessToken,$idtoken);
        }

        $return = array("status" => $status? 1 : 0,"message"=>$message);
        return new JsonResponse($return);
    }
}
