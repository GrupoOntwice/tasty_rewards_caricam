<?php
namespace Drupal\marketocsv_generator\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Class BulkmarketocsvsettingForm.
 */
class BulkmarketocsvsettingForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bulkmarketocsv.usersettings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulkmarketocsv_settings_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	$form['#attached']['library'][] = 'marketocsv_generator/marketocsv_creation_css';
    $config = $this->config('bulkmarketocsv.usersettings');
    $form['bulkuserid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access User ID'),
      '#description' => $this->t('Enter access user id'),
      '#maxlength' => 64,
      '#size' => 64,
      //'#value' => \Drupal::currentUser()->id(),
      '#disabled' => TRUE,
      '#default_value' => $config->get('bulkuserid') ? $config->get('bulkuserid') : \Drupal::currentUser()->id(),
    ];
    $form['range_count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum Range Count'),
      '#description' => $this->t('Minimum range count for batch process record'),
      '#maxlength' => 64,
      '#size' => 64,
      //'#value' => $config->get('startrecordscount') ? $config->get('startrecordscount') : 0,
      //'#disabled' => TRUE,
      '#default_value' => $config->get('range_count') ? $config->get('range_count') : 1000,
    ];
    $form['folder_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set the Folder Name'),
      '#description' => $this->t('Set the folder name for the batch process'),
      '#maxlength' => 64,
      '#size' => 64,
      //'#value' => $config->get('splitpagecount') ? $config->get('splitpagecount') : 1,
      //'#disabled' => TRUE,
      '#default_value' => $config->get('folder_name') ? $config->get('folder_name') : 'bulkjoin_marketocsv',
    ];
     $form['file_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set the File Name'),
      '#description' => $this->t('Set the file name for the batch process'),
      '#maxlength' => 64,
      '#size' => 64,
      //'#value' => $config->get('recordperpage') ? $config->get('recordperpage') : 5,
      //'#disabled' => TRUE,
      '#default_value' => $config->get('file_name') ? $config->get('file_name') : 'bulkmarketocsv.csv',
    ];
  
    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('bulkmarketocsv.usersettings')
      ->set('bulkuserid', $form_state->getValue('bulkuserid'))
      ->set('range_count', $form_state->getValue('range_count'))
      ->set('folder_name', $form_state->getValue('folder_name'))
      ->set('file_name', $form_state->getValue('file_name'))
      ->save();
  }
  
}
