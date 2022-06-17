<?php

namespace Drupal\gtm_datalayer_views\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\gtm_datalayer\Form\DataLayerAddForm;

/**
 * Provides add form for dataLayer view instance forms.
 */
class DataLayerViewAddForm extends DataLayerAddForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\gtm_datalayer_views\Entity\DataLayerViewInterface $datalayer */
    $datalayer = $this->entity;

    $form = parent::form($form, $form_state);

    $form['#title'] = $this->t('Add dataLayer view');

    $form['view'] = [
      '#title' => $this->t('View ID'),
      '#type' => 'textfield',
      '#default_value' => $datalayer->getView(),
      '#description' => $this->t("The view ID, the '*' character is a wildcard."),
      '#required' => TRUE,
      '#size' => 50,
    ];

    $form['plugin']['#weight'] = 10;
    $form['weight']['#weight'] = 10.1;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $form_state->setRedirect('entity.gtm_datalayer_view.edit_form', ['gtm_datalayer_view' => $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getProcessorPlugins($group = 'view') {
    return parent::getProcessorPlugins($group);
  }

}
