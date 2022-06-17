<?php
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Drupal\file\Plugin\Field\FieldType;

var_dump($_SERVER['argc']);


$_SERVER['HTTP_HOST'] = 'local.tastyrewards.ca';
$autoloader = require_once 'autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();

require_once 'core/includes/database.inc';
require_once 'core/includes/schema.inc';

$node = \Drupal\node\Entity\Node::load(100);
var_dump($node);

