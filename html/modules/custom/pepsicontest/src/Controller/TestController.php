<?php

/**
 * @file
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

class TestController extends ControllerBase {

    /**
     * {@inheritdoc}
     */
    public function test(Request $request) {
        $urlsource = $request->get('urlsource');
        $id = getSourceIdRegularPages($urlsource, 'en');
        echo ("SOURCE_ID : ". $id);
        exit;
    }
}
