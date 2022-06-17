<?php


namespace Drupal\maintenance_exempt;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Site\MaintenanceModeInterface;
use Drupal\Core\Site\MaintenanceMode;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the default implementation of the maintenance mode service.
 */
class MaintenanceModeExempt extends MaintenanceMode implements MaintenanceModeInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * Constructs a new maintenance mode service.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   */
  public function __construct(StateInterface $state, RequestStack $request_stack) {
    $this->state = $state;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function exempt(AccountInterface $account) {

    // Standard core behaviour - check user's permission.
    if ($account->hasPermission('access site in maintenance mode')) {
      return TRUE;
    }

    // Check if the IP address should be exempted.
    $client_ip = $this->request->getClientIp();
    if (in_array($client_ip, maintenance_exempt_get_ips())) {
      return TRUE;
    }
    if (maintenance_exempt_by_cidr_notation($client_ip)) {
      return TRUE;
    }

    // Fetch the query string exemption key if there is one.
    $config = \Drupal::config('maintenance_exempt.settings');
    $key = $config->get('query_key');

    // Exemption status may be stored in the session.
    if (isset($_SESSION['maintenance_exempt']) && $_SESSION['maintenance_exempt'] == $key) {
      return TRUE;
    }

    if ($key && isset($_GET[$key])) {
      $_SESSION['maintenance_exempt'] = $key;
      return TRUE;
    }

    // No valid exemption, so user remains blocked.
    return FALSE;
  }

}
