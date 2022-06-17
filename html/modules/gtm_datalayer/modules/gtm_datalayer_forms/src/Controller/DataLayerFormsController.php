<?php

namespace Drupal\gtm_datalayer_forms\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\gtm_datalayer\Controller\DataLayerController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines a controller to manage GTM dataLayer Form.
 */
class DataLayerFormsController extends DataLayerController implements DataLayerFormsControllerInterface {

  /**
   * The nested array of form elements that comprise the form.
   *
   * @var array
   */
  protected $form;

  /**
   * The name of the form handler.
   *
   * @var string
   */
  protected $formHandler;

  /**
   * The name of the form itself.
   *
   * @var string
   */
  protected $formId;

  /**
   * The current state of the form.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  protected $formState;

  /**
   * Deny any page caching.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $tempStore;

  /**
   * Creates an DataLayerFormsController object.
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
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   The temp store object.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $page_cache_kill_switch
   *   Deny any page caching.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ModuleHandlerInterface $module_handler, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entity_type_manager, ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, RendererInterface $renderer, RequestStack $request_stack, PrivateTempStoreFactory $private_temp_store, KillSwitch $page_cache_kill_switch) {
    parent::__construct($module_handler, $configFactory, $entity_type_manager->getStorage('gtm_datalayer'), $context_handler, $context_repository, $renderer, $request_stack);

    $this->killSwitch = $page_cache_kill_switch;
    $this->tempStore = $private_temp_store;
    $this->storage = $entity_type_manager->getStorage('gtm_datalayer_form');
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
      $container->get('request_stack'),
      $container->get('tempstore.private'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildGtmPusherScript(array &$form, FormStateInterface $form_state, string $form_id, string $form_handler = 'alter') {
    // If is enabled and configured.
    if ($this->isEnabled() && $this->isConfigured()) {
      $this->form = &$form;
      $this->formId = $form_id;
      $this->formState = $form_state;
      $this->formHandler = $form_handler;

      // Build Google Tag Manager dataLayer pusher (script).
      $data_layer = $this->renderTags();
      if ($form_handler == 'alter') {
        $form['#submit'][] = 'gtm_datalayer_forms_form_submit';
        // Add our validation and submit handlers.
        if (!empty($form['actions']['submit']['#submit'])) {
          $form['actions']['submit']['#submit'][] = 'gtm_datalayer_forms_form_submit';
        }
        else {
          $form['#submit'][] = 'gtm_datalayer_forms_form_submit';
        }
        $form['#validate'][] = 'gtm_datalayer_forms_form_validate';

        if (count($data_layer)) {
          // Attach the pusher library and the dataLayer tag array.
          $form['#attached']['library'][] = 'gtm_datalayer/datalayer_pusher';
          $form['#attached']['drupalSettings']['datalayer_tags'] = $data_layer;
        }
      }
      elseif (($form_handler == 'validate' || $form_handler == 'submit') && count($data_layer)) {
        $this->tempStore->get('gtm_datalayer_forms')->set('datalayer_tags_' . $form_handler, $data_layer);
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
   * {@inheritdoc}
   */
  public function pushGtmTags(array &$render_array) {
    $data_layer = [];
    foreach (['validate', 'submit'] as $handler) {
      if (($tags = $this->tempStore->get('gtm_datalayer_forms')->get('datalayer_tags_' . $handler, [])) && !empty($tags)) {
        // Only add the tags one time.
        $this->tempStore->get('gtm_datalayer_forms')->delete('datalayer_tags_' . $handler);

        // The values of the last ones prevail.
        $data_layer = array_replace($data_layer, $tags);
      }
    }

    if (count($data_layer)) {
      // Attach the pusher library and the dataLayer tag array.
      $render_array['#attached']['library'][] = 'gtm_datalayer/datalayer_pusher';
      $render_array['#attached']['drupalSettings']['datalayer_tags'] = $data_layer;
      // If a page shows validation or submit results, it should never be
      // cached.
      $this->killSwitch->trigger();
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
    /** @var \Drupal\gtm_datalayer_forms\Entity\DataLayerFormInterface $entity */
    foreach ($this->getStorage()->loadMultiple($entity_ids) as $entity_id => $entity) {
      $pattern = str_replace('*', '.*', $entity->getFrom());

      if (preg_match('/^' . $pattern . '$/i', $this->formId)) {
        $entities[$entity_id] = $entity;
      }
    }

    return $entities;
  }

  /**
   * Evaluate configured dataLayer and render the tags.
   *
   * @return array
   *   The rendered dataLayer tags.
   */
  protected function renderTags() {
    $tags = [];

    /** @var \Drupal\gtm_datalayer_forms\Entity\DataLayerFormInterface $datalayer */
    foreach ($this->loadEntities() as $datalayer_id => $datalayer) {
      $this->addDebugMessage($this->t('Evaluating dataLayer: @datalayer', ['@datalayer' => $datalayer->label()]));
      $this->addDebugMessage('---');

      if ($this->evaluateConditions($datalayer)) {
        $new_tags = $datalayer->getDataLayerProcessor()->configure($this->form, $this->formState, $this->formId, $this->formHandler)->render();
        if (count($new_tags)) {
          $tags = array_merge($tags, $new_tags);
        }
      }

      $this->addDebugMessage('---');
    }

    return $tags;
  }

}
