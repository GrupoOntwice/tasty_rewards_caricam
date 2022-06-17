<?php
/**
 * @file
 * Contains Drupal\sfmcservices\Form\MessagesForm.
 */
namespace Drupal\sfmcservices\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'sfmcservices.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sfmcservices_setting_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sfmcservices.adminsettings');

    $form['sfmcservices_canada'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Canada '),
    ];

    $form['sfmcservices_canada']['sfmcservices_mid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MID'),
      '#description' => $this->t('Business Unit ID'),
      '#default_value' => $config->get('sfmcservices_mid'),
    ];

    $form['sfmcservices_canada']['sfmcservices_sourceID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source ID'),
      '#description' => $this->t('Source  ID'),
      '#default_value' => $config->get('sfmcservices_sourceID'),
    ];

    $form['sfmcservices_canada']['sfmcservices_contest_status_DE'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contest Status Data Extension'),
      '#description' => $this->t('Contact Data extension that contains the status of active contest and coupons'),
      '#default_value' => $config->get('sfmcservices_contest_status_DE'),
    ];

    //sfmcservices_contest_status_DE

    $form['sfmcservices_canada']['sfmcservices_nl_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('NL Label '),
      '#description' => $this->t('NL Label '),
      '#default_value' => $config->get('sfmcservices_nl_label'),
    ];

    $form['sfmcservices_canada']['sfmcservices_baseurl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('Base Salesforce url'),
      '#default_value' => $config->get('sfmcservices_baseurl'),
    ];

    $form['sfmcservices_canada']['sfmcservices_base_auth_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base Auth Url'),
      '#description' => $this->t('Base Auth Url'),
      '#default_value' => $config->get('sfmcservices_base_auth_url'),
    ];

    $form['sfmcservices_canada']['sfmcservices_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SOAP endpoint'),
      '#description' => $this->t('SOAP endpoint'),
      '#default_value' => $config->get('sfmcservices_endpoint'),
    ];



    $form['sfmcservices_canada']['sfmcservices_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data Extension key'),
      '#description' => $this->t('External key of the Data Extension to which subscribers will be added'),
      '#default_value' => $config->get('sfmcservices_data_extension_key'),
    ];

    $form['sfmcservices_canada']['sfmcservices_contest_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contest Data Extension key'),
      '#description' => $this->t('External key of the Data Extension for contest data'),
      '#default_value' => $config->get('sfmcservices_contest_data_extension_key'),
    ];

    $form['sfmcservices_canada']['sfmcservices_poll_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Poll Data Extension key'),
      '#description' => $this->t('External key of the Data Extension for poll data'),
      '#default_value' => $config->get('sfmcservices_poll_data_extension_key'),
    ];

    $form['sfmcservices_canada']['sfmcservices_winners_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contest Winners Data Extension key'),
      '#description' => $this->t('External key of the Data Extension for Contest Winners'),
      '#default_value' => $config->get('sfmcservices_winners_data_extension_key'),
    ];

    $form['sfmcservices_canada']['sfmcservices_clientID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_1.0 : Client ID'),
      '#description' => $this->t(' Client ID for version 1.0 of the API'),
      '#default_value' => $config->get('sfmcservices_clientID'),
    ];

    $form['sfmcservices_canada']['sfmcservices_clientSecret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_1.0 : Client Secret'),
      '#description' => $this->t('Client Secret for version 1.0 of the API'),
      '#default_value' => $config->get('sfmcservices_clientSecret'),
    ];

    $form['sfmcservices_canada']['sfmcservices_clientID_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_2.0 : Client ID'),
      '#description' => $this->t(' Client ID for version 2.0 of the API'),
      '#default_value' => $config->get('sfmcservices_clientID_2'),
    ];

    $form['sfmcservices_canada']['sfmcservices_clientSecret_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_2.0 : Client Secret'),
      '#description' => $this->t('Client Secret for version 2.0 of the API'),
      '#default_value' => $config->get('sfmcservices_clientSecret_2'),
    ];

    $form['sfmcservices_canada']['sfmcservices_customerkey_welcome_en'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Welcome Email Customer Key (EN)'),
      '#description' => $this->t('SFMC Triggered send external key for English welcome email'),
      '#default_value' => $config->get('sfmcservices_customerkey_welcome_en'),
    ];

    $form['sfmcservices_canada']['sfmcservices_customerkey_welcome_fr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Welcome Email Customer Key (FR)'),
      '#description' => $this->t('SFMC Triggered send external key for French welcome email'),
      '#default_value' => $config->get('sfmcservices_customerkey_welcome_fr'),
    ];

    $form['sfmcservices_canada']['sfmcservices_customerkey_forgot_en'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forgot Password Email Customer Key (EN)'),
      '#description' => $this->t('SFMC Triggered email external key for English "Forgot password" email'),
      '#default_value' => $config->get('sfmcservices_customerkey_forgot_en'),
    ];

    $form['sfmcservices_canada']['sfmcservices_customerkey_forgot_fr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forgot Password Email Customer Key (FR)'),
      '#description' => $this->t('SFMC Triggered email external key for French "Forgot password" email'),
      '#default_value' => $config->get('sfmcservices_customerkey_forgot_fr'),
    ];

    $form['sfmcservices_canada']['sfmcservices_customerkey_unsubscribed'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unsubscribed users Customer Key'),
      '#description' => $this->t('SFMC Data Extension Key for Unsubscribed users'),
      '#default_value' => $config->get('sfmcservices_customerkey_unsubscribed'),
    ];

    $form['sfmcservices_canada']['sfmcservices_mapping_email_subscriberkey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mapping Email SubscriberKey Customer Key'),
      '#description' => $this->t('SFMC Data Extension Key for mapping between EmailAddress and SubscriberKey'),
      '#default_value' => $config->get('sfmcservices_mapping_email_subscriberkey'),
    ];

    $form['sfmcservices_canada']['sfmcservices_mapping_email_uid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mapping Email UserID'),
      '#description' => $this->t('SFMC Data Extension Key for mapping between EmailAddress and Drupal user ID'),
      '#default_value' => $config->get('sfmcservices_mapping_email_uid'),
    ];

    $form['sfmcservices_usa'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('USA '),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_mid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MID'),
      '#description' => $this->t('Business Unit ID'),
      '#default_value' => $config->get('usa_sfmcservices_mid'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_sourceID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source ID'),
      '#description' => $this->t('Source  ID'),
      '#default_value' => $config->get('usa_sfmcservices_sourceID'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_contest_status_DE'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contest Status Data Extension'),
      '#description' => $this->t('Contact Data extension that contains the status of active contest and coupons'),
      '#default_value' => $config->get('usa_sfmcservices_contest_status_DE'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_nl_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('NL Label '),
      '#description' => $this->t('NL Label '),
      '#default_value' => $config->get('usa_sfmcservices_nl_label'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_baseurl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('Base Salesforce url'),
      '#default_value' => $config->get('usa_sfmcservices_baseurl'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_base_auth_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base Auth Url'),
      '#description' => $this->t('Base Auth Url'),
      '#default_value' => $config->get('usa_sfmcservices_base_auth_url'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SOAP endpoint'),
      '#description' => $this->t('SOAP endpoint'),
      '#default_value' => $config->get('usa_sfmcservices_endpoint'),
    ];



    $form['sfmcservices_usa']['usa_sfmcservices_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data Extension key'),
      '#description' => $this->t('External key of the Data Extension to which subscribers will be added'),
      '#default_value' => $config->get('usa_sfmcservices_data_extension_key'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_data_extension_key_fb_ad'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Ad Data Extension key'),
      '#description' => $this->t('External key of the Data Extension for Facebook subscribers'),
      '#default_value' => $config->get('usa_sfmcservices_data_extension_key_fb_ad'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_contest_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contest Data Extension key'),
      '#description' => $this->t('External key of the Data Extension for contest data'),
      '#default_value' => $config->get('usa_sfmcservices_contest_data_extension_key'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_poll_data_extension_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Poll Data Extension key'),
      '#description' => $this->t('External key of the Data Extension for poll data'),
      '#default_value' => $config->get('usa_sfmcservices_poll_data_extension_key'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_clientID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_1.0 : Client ID'),
      '#description' => $this->t(' Client ID for version 1.0 of the API'),
      '#default_value' => $config->get('usa_sfmcservices_clientID'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_clientSecret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_1.0 : Client Secret'),
      '#description' => $this->t('Client Secret for version 1.0 of the API'),
      '#default_value' => $config->get('usa_sfmcservices_clientSecret'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_clientID_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_2.0 : Client ID'),
      '#description' => $this->t(' Client ID for version 2.0 of the API'),
      '#default_value' => $config->get('usa_sfmcservices_clientID_2'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_clientSecret_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API_2.0 : Client Secret'),
      '#description' => $this->t('Client Secret for version 2.0 of the API'),
      '#default_value' => $config->get('usa_sfmcservices_clientSecret_2'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_customerkey_welcome_en'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Welcome Email Customer Key (EN)'),
      '#description' => $this->t('SFMC Triggered send external key for English welcome email'),
      '#default_value' => $config->get('usa_sfmcservices_customerkey_welcome_en'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_customerkey_create_pwd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Create Password'),
      '#description' => $this->t('SFMC Triggered send external key for Create password'),
      '#default_value' => $config->get('usa_sfmcservices_customerkey_create_pwd'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_customerkey_create_pwd_es'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Create Password (ES)'),
      '#description' => $this->t('SFMC Triggered send external key for Spanish Create password '),
      '#default_value' => $config->get('usa_sfmcservices_customerkey_create_pwd_es'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_customerkey_forgot_en'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forgot Password Email Customer Key (EN)'),
      '#description' => $this->t('SFMC Triggered email external key for English "Forgot password" email'),
      '#default_value' => $config->get('usa_sfmcservices_customerkey_forgot_en'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_customerkey_forgot_es'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forgot Password Email Customer Key (FR)'),
      '#description' => $this->t('SFMC Triggered email external key for Spanish "Forgot password" email'),
      '#default_value' => $config->get('usa_sfmcservices_customerkey_forgot_es'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_customerkey_unsubscribed'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unsubscribed users Customer Key'),
      '#description' => $this->t('SFMC Data Extension Key for Unsubscribed users'),
      '#default_value' => $config->get('usa_sfmcservices_customerkey_unsubscribed'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_mapping_email_subscriberkey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mapping Email SubscriberKey Customer Key'),
      '#description' => $this->t('SFMC Data Extension Key for mapping between EmailAddress and SubscriberKey'),
      '#default_value' => $config->get('usa_sfmcservices_mapping_email_subscriberkey'),
    ];

    $form['sfmcservices_usa']['usa_sfmcservices_mapping_email_uid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mapping Email UserID'),
      '#description' => $this->t('SFMC Data Extension Key for mapping between EmailAddress and Drupal user ID'),
      '#default_value' => $config->get('usa_sfmcservices_mapping_email_uid'),
    ];

    $form['smtp'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('SMTP Contest Email'),
      '#description' => $this->t('Credentials for the contest@tastyrewards.ca stmp account'),
    ];

    $form['smtp']['smtp_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SMTP username'),
      '#description' => $this->t('Username'),
      '#default_value' => $config->get('smtp_username'),
    ];

    $form['smtp']['smtp_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SMTP password'),
      '#description' => $this->t('password'),
      '#default_value' => $config->get('smtp_password'),
    ];

    $form['3tierLogic'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('3 Tier Logic API'),
      '#description' => $this->t('API keys and endpoints for 3Tier logic'),
    ];

    $form['3tierLogic']['3tl_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('API Key'),
      '#default_value' => $config->get('3tl_api_key'),
    ];

    $form['3tierLogic']['3tl_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base Url'),
      '#description' => $this->t('Base Url'),
      '#default_value' => $config->get('3tl_base_url'),
    ];

    $form['3tierLogic']['3tl_callback_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback Url'),
      '#description' => $this->t('Callback Url'),
      '#default_value' => $config->get('3tl_callback_url'),
    ];

    $form['3tierLogic']['3tl_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Token'),
      '#description' => $this->t('API Token'),
      '#default_value' => $config->get('3tl_token'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('sfmcservices.adminsettings')
      ->set('3tl_api_key',                              $form_state->getValue('3tl_api_key'))
      ->set('3tl_base_url',                              $form_state->getValue('3tl_base_url'))
      ->set('3tl_callback_url',                         $form_state->getValue('3tl_callback_url'))
      ->set('3tl_token',                         $form_state->getValue('3tl_token'))
      ->set('smtp_username',                            $form_state->getValue('smtp_username'))
      ->set('smtp_password',                            $form_state->getValue('smtp_password'))
      ->set('sfmcservices_mid',                         $form_state->getValue('sfmcservices_mid'))
      ->set('sfmcservices_clientID',                    $form_state->getValue('sfmcservices_clientID'))
      ->set('sfmcservices_clientID_2',                  $form_state->getValue('sfmcservices_clientID_2'))
      ->set('sfmcservices_sourceID',                    $form_state->getValue('sfmcservices_sourceID'))
      ->set('sfmcservices_contest_status_DE',           $form_state->getValue('sfmcservices_contest_status_DE'))
      ->set('sfmcservices_nl_label',                    $form_state->getValue('sfmcservices_nl_label'))
      ->set('sfmcservices_clientSecret',                $form_state->getValue('sfmcservices_clientSecret'))
      ->set('sfmcservices_clientSecret_2',              $form_state->getValue('sfmcservices_clientSecret_2'))
      ->set('sfmcservices_endpoint',                    $form_state->getValue('sfmcservices_endpoint'))
      ->set('sfmcservices_base_auth_url',               $form_state->getValue('sfmcservices_base_auth_url'))
      ->set('sfmcservices_baseurl',                     $form_state->getValue('sfmcservices_baseurl'))
      ->set('sfmcservices_data_extension_key',          $form_state->getValue('sfmcservices_data_extension_key'))
      ->set('sfmcservices_contest_data_extension_key',  $form_state->getValue('sfmcservices_contest_data_extension_key'))
      ->set('sfmcservices_mapping_email_uid',           $form_state->getValue('sfmcservices_mapping_email_uid'))
      ->set('sfmcservices_poll_data_extension_key',     $form_state->getValue('sfmcservices_poll_data_extension_key'))
      ->set('sfmcservices_winners_data_extension_key',     $form_state->getValue('sfmcservices_winners_data_extension_key'))
      ->set('sfmcservices_customerkey_welcome_en',      $form_state->getValue('sfmcservices_customerkey_welcome_en'))
      ->set('sfmcservices_customerkey_welcome_fr',      $form_state->getValue('sfmcservices_customerkey_welcome_fr'))
      ->set('sfmcservices_customerkey_forgot_en',       $form_state->getValue('sfmcservices_customerkey_forgot_en'))
      ->set('sfmcservices_customerkey_forgot_fr',       $form_state->getValue('sfmcservices_customerkey_forgot_fr'))
      ->set('sfmcservices_customerkey_unsubscribed',       $form_state->getValue('sfmcservices_customerkey_unsubscribed'))
      ->set('sfmcservices_mapping_email_subscriberkey',       $form_state->getValue('sfmcservices_mapping_email_subscriberkey'))
      ->set('usa_sfmcservices_mid',                     $form_state->getValue('usa_sfmcservices_mid'))
      ->set('usa_sfmcservices_clientID',                $form_state->getValue('usa_sfmcservices_clientID'))
      ->set('usa_sfmcservices_clientID_2',              $form_state->getValue('usa_sfmcservices_clientID_2'))
      ->set('usa_sfmcservices_sourceID',                $form_state->getValue('usa_sfmcservices_sourceID'))
      ->set('usa_sfmcservices_contest_status_DE',       $form_state->getValue('usa_sfmcservices_contest_status_DE'))
      ->set('usa_sfmcservices_nl_label',                $form_state->getValue('usa_sfmcservices_nl_label'))
      ->set('usa_sfmcservices_clientSecret',            $form_state->getValue('usa_sfmcservices_clientSecret'))
      ->set('usa_sfmcservices_clientSecret_2',          $form_state->getValue('usa_sfmcservices_clientSecret_2'))
      ->set('usa_sfmcservices_endpoint',                $form_state->getValue('usa_sfmcservices_endpoint'))
      ->set('usa_sfmcservices_base_auth_url',           $form_state->getValue('usa_sfmcservices_base_auth_url'))
      ->set('usa_sfmcservices_baseurl',                 $form_state->getValue('usa_sfmcservices_baseurl'))
      ->set('usa_sfmcservices_data_extension_key',      $form_state->getValue('usa_sfmcservices_data_extension_key'))
      ->set('usa_sfmcservices_data_extension_key',      $form_state->getValue('usa_sfmcservices_data_extension_key'))
      ->set('usa_sfmcservices_data_extension_key_fb_ad',      $form_state->getValue('usa_sfmcservices_data_extension_key_fb_ad'))
      ->set('usa_sfmcservices_poll_data_extension_key', $form_state->getValue('usa_sfmcservices_poll_data_extension_key'))
      ->set('usa_sfmcservices_customerkey_welcome_en',  $form_state->getValue('usa_sfmcservices_customerkey_welcome_en'))
      ->set('usa_sfmcservices_customerkey_create_pwd',  $form_state->getValue('usa_sfmcservices_customerkey_create_pwd'))
      ->set('usa_sfmcservices_customerkey_create_pwd_es',  $form_state->getValue('usa_sfmcservices_customerkey_create_pwd_es'))
      ->set('usa_sfmcservices_customerkey_forgot_en',   $form_state->getValue('usa_sfmcservices_customerkey_forgot_en'))
      ->set('usa_sfmcservices_customerkey_forgot_es',   $form_state->getValue('usa_sfmcservices_customerkey_forgot_es'))
      ->set('usa_sfmcservices_mapping_email_uid',       $form_state->getValue('usa_sfmcservices_mapping_email_uid'))
      ->set('usa_sfmcservices_customerkey_unsubscribed',       $form_state->getValue('usa_sfmcservices_customerkey_unsubscribed'))
      ->set('usa_sfmcservices_mapping_email_subscriberkey',       $form_state->getValue('usa_sfmcservices_mapping_email_subscriberkey'))
      ->set('usa_sfmcservices_contest_data_extension_key',  $form_state->getValue('usa_sfmcservices_contest_data_extension_key'))
      ->save();
  }
}