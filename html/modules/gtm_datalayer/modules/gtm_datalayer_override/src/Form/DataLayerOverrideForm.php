<?php

namespace Drupal\gtm_datalayer_override\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\entity\Form\EntityDuplicateFormTrait;

class DataLayerOverrideForm extends ContentEntityForm {

  use EntityDuplicateFormTrait;

  /**
   * Indicates if this form is the entity's add form.
   *
   * @var bool
   */
  protected $isAddForm = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    if ($route_match->getRawParameter($entity_type_id) !== NULL) {
      return $route_match->getParameter($entity_type_id);
    }

    if ($route_match->getRouteName() == 'entity.gtm_datalayer_override.add_form') {
      $values = [];
    }
    else {
      $this->isAddForm = FALSE;

      $parameters = $route_match->getRawParameters()->all();
      $related_entity = array_slice($parameters, 0, 1);

      $values = [];
      $values['entity_type'] = key($related_entity);
      $values['entity_id'] = reset($related_entity);

      // First try to load an existing entity.
      if (($entities = $this->entityTypeManager->getStorage($entity_type_id)->loadByProperties($values)) && !empty($entities)) {
        return reset($entities);
      }
    }

    // Otherwise create a new entity.
    return $this->entityTypeManager->getStorage($entity_type_id)->create($values);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface $data_layer */
    $data_layer = $this->entity;

    if (!$data_layer->isNew() || !$this->isAddForm) {
      $form['entity_type']['widget'][0]['value']['#disabled'] = TRUE;
      $form['entity_id']['widget'][0]['value']['#disabled'] = TRUE;
    }
    $form['tags']['widget'][0]['value']['#placeholder'] = $this->t("Enter 'name': 'value' pairs…");
    $form['tags']['widget'][0]['value']['#description'] = [
      'help' => [
        '#markup' => $this->t('Enter tags data as name and value pairs as <a href=":href">YAML</a>.', [':href' => 'https://en.wikipedia.org/wiki/YAML']),
      ],
      'example' => [
        '#type' => 'html_tag',
        '#tag' => 'pre',
        '#value' => "# This is a simple example.
element_key: 'some value'

# The below example defines a multi-value tag.
element_key:
  - 'Value #1'
  - 'Value #2'
  - 'Value #3'",
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\gtm_datalayer_override\Entity\DataLayerOverrideInterface $entity */
    $entity = $this->getEntity();
    $this->messenger()->addStatus($this->t('GTM dataLayer Override %label has been saved.', ['%label' => $entity->label()]));

    if ($this->isAddForm) {
      $form_state->setRedirect('entity.gtm_datalayer_override.collection');
    }
    else {
      $form_state->setRedirectUrl($entity->getRelatedEntity()->toUrl());
    }
  }

}
