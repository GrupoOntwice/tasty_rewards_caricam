<?php

namespace Drupal\gtm_datalayer_views\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\gtm_datalayer\Controller\DataLayerController;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines a controller to manage GTM dataLayer View.
 */
class DataLayerViewsController extends DataLayerController implements DataLayerViewsControllerInterface {

  /**
   * The view object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * The name of the view itself.
   *
   * @var string
   */
  protected $viewId;

  /**
   * Creates an DataLayerViewsController object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity storage class.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The ContextHandler for applying contexts to conditions properly.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Turns a render array into a HTML string.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ModuleHandlerInterface $module_handler, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entity_type_manager, ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, RendererInterface $renderer, RequestStack $request_stack) {
    parent::__construct($module_handler, $configFactory, $entity_type_manager->getStorage('gtm_datalayer'), $context_handler, $context_repository, $renderer, $request_stack);

    $this->storage = $entity_type_manager->getStorage('gtm_datalayer_view');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('context.handler'),
      $container->get('context.repository'),
      $container->get('renderer'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildGtmPusherScript(ViewExecutable $view) {
    // If is enabled and configured.
    if ($this->isEnabled() && $this->isConfigured()) {
      $this->view = $view;
      $this->viewId = $view->id();

      // Build Google Tag Manager dataLayer pusher (script).
      $data_layer = $this->renderTags();
      if (count($data_layer)) {
        // Attach the pusher library and the dataLayer tag array.
        $view->element['#attached']['library'][] = 'gtm_datalayer/datalayer_pusher';
        $view->element['#attached']['drupalSettings']['datalayer_tags'] = $data_layer;
      }

      if ($this->isDebugEnabled() && count($this->buildDebugMessage())) {
        $this->addDebugMessage('---');
        $this->addDebugMessage('Rendered dataLayer:');
        $this->addDebugMessage($this->t('<pre>@datalayer</pre>', ['@datalayer' => print_r($data_layer, TRUE)]));

        $debug_message = $this->buildDebugMessage();
        $this->messenger()->addWarning($this->renderer->renderPlain($debug_message), TRUE);
      }
    }
  }

  /**
   * Loads GTM dataLayers from storage.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of GTM dataLayers indexed by their weights. Returns an empty
   *   array if no matching entities are found.
   */
  protected function loadEntities() {
    $entity_ids = $this->getEntityIds();

    $entities = [];
    /** @var \Drupal\gtm_datalayer_views\Entity\DataLayerViewInterface $entity */
    foreach ($this->getStorage()->loadMultiple($entity_ids) as $entity_id => $entity) {
      $pattern = str_replace('*', '.*', $entity->getView());

      if (preg_match('/^' . $pattern . '$/i', $this->viewId)) {
        $entities[$entity_id] = $entity;
      }
    }

    return $entities;
  }

  /**
   * Evaluate configured dataLayers and render the tags.
   *
   * @return array
   *   The rendered dataLayer tags.
   */
  protected function renderTags() {
    $tags = [];

    /** @var \Drupal\gtm_datalayer_views\Entity\DataLayerViewInterface $datalayer */
    foreach ($this->loadEntities() as $datalayer_id => $datalayer) {
      $this->addDebugMessage($this->t('Evaluating dataLayer: @datalayer', ['@datalayer' => $datalayer->label()]));
      $this->addDebugMessage('---');

      if ($this->evaluateConditions($datalayer)) {
        $new_tags = $datalayer->getDataLayerProcessor()->configure($this->view, $this->viewId)->render();
        if (count($new_tags)) {
          $tags = array_merge($tags, $new_tags);
        }
      }

      $this->addDebugMessage('---');
    }

    return $tags;
  }

}
