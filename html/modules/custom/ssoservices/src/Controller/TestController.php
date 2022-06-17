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

class TestController extends ControllerBase {

    /**
     * {@inheritdoc}
     */
    public function test(Request $request) {
        $sso_service = \Drupal::service('services.sso');

        /*
      Tasty Rewards (US) : SubscriptionID = "100022221_PTR_NEWSLETTER_PEPSICOTRNEWS_20200819"; (1)
      Tasty Makers (US) : SubscriptionID = "100022227_PEPSI_NEWSLETTER_PEPSITASTEMAKERSEMAILOPTIN_20140320"; (3)
      Pepsi Emails (US) : SubscriptionID = "100022227_PEPSI_NEWSLETTER_PURELYPEPSINEWSLETTER_20140320"; (4)
      MTN DEW emails (US) : SubscriptionID = "100022222_MOUNTAINDEW_NEWSLETTER_DEWSLETTERNEWSLETTER_20140320"; (5)
      Snacks.com US (not active yet) : SubscriptionID = "100022221_PTR_SNACKSCOM_SNACKSNEWSLETTEROPTIN_20211201"  (6)
      */        
        $och_optins[1] = false;
        
        $och_optins[3] = false;
        $och_optins[4] = true;
        $och_optins[5] = true;
        $och_optins[6] = false;
        //$och_optins = '100022222_MOUNTAINDEW_NEWSLETTER_DEWSLETTERNEWSLETTER_20140320';
        $optins = $sso_service->getSubscriptionIdsStringOptOut($och_optins);
        
        var_dump($optins);

        exit;
    }
}
