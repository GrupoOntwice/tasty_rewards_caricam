<?php
/**
 * @file
 * Contains Drupal\sfmcservices\Form\MessagesForm.
 */
namespace Drupal\pepsibam\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ComingSoonForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'comingsoon.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'comingsoon_setting_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('comingsoon.adminsettings');

    $form['comingsoon'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Coming Soon page configuration '),
    ];


    $form['comingsoon']['modes'] = array(
	    '#type' => 'select',
	    //'#options' => drupal_map_assoc(array(t('Always Coming Soon'), t('Always Active'), t('Active for Whitelist'))),
	    '#options' => [
	    	'always_active' => 'Always Active',
	    	'whitelist' => 'Active for Whitelist',
	    	'always_comingsoon' => 'Always Coming Soon',
	    ],
	    '#title' => t('Coming Soon status'),
	    '#default_value' => $config->get('modes'),
	  );

    

    $form['comingsoon']['whitelist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IP Addresses on whitelist'),
      '#description' => $this->t('Allowed ip addresses. Use values separated by "," '),
      '#default_value' => $config->get('whitelist'),
    ];
    

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('comingsoon.adminsettings')
      ->set('whitelist', $form_state->getValue('whitelist'))
      ->set('modes',     $form_state->getValue('modes'))
      
      ->save();
  }
}