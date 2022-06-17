<?php
/**
 * @file
 * Contains Drupal\ssoconfig_us\Form\MessagesForm.
 */
namespace Drupal\ssoconfig_us\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ssoconfig_us.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ssoconfig_us_setting_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ssoconfig_us.adminsettings');

    $form['ssoconfig_oauth'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Auth Settings'),
    ];

    $form['ssoconfig_oauth']['access_token_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token URL'),
      '#description' => $this->t('The endpoint for authentication server, this is used to exchange the authorization code for an access token.'),
      '#default_value' => $config->get('access_token_url'),
    ];

    $form['ssoconfig_oauth']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('The client identifier issued to the client during the Application registration process.'),
      '#default_value' => $config->get('client_id'),
    ];


    $form['ssoconfig_oauth']['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('The client secret issued to the client during the Application registration process.'),
      '#default_value' => $config->get('client_secret'),
    ];

    $form['ssoconfig_oauth']['scope'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Scope'),
      '#description' => $this->t('The scope of the access request, It may have multiple space-delimited values'),
      '#default_value' => $config->get('scope'),
    ];

    $form['ssoconfig_oauth']['groupIds'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Group IDs'),
      '#description' => $this->t('Group code Id provided by OKTA for Snacks-CA'),
      '#default_value' => $config->get('groupIds'),
    ];


    /*
    OCH configuration
    */
    $form['ssoconfig_pimcore'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Pimcore Settings'),
    ];
    
    $form['ssoconfig_pimcore']['pimcore_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pimcore Endpoint'),
      '#description' => $this->t('Endpoint for shipping addresses'),
      '#default_value' => $config->get('pimcore_endpoint'),
    ];

    /*
    OCH configuration
    */
    $form['ssoconfig_och'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('OCH Settings'),
    ];
    
    $form['ssoconfig_och']['och_userendpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Endpoint'),
      '#description' => $this->t('Endpoint to User calls'),
      '#default_value' => $config->get('och_userendpoint'),
    ];

    /*
    OktaSignIn settings
    */
    $form['ssoconfig_signin'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Okta SignIn Settings'),
    ];
    
    $form['ssoconfig_signin']['signin_baseUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base Url'),
      '#description' => $this->t('Base Url for the Login Widget'),
      '#default_value' => $config->get('signin_baseUrl'),
    ];

    $form['ssoconfig_signin']['signin_clientId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('clientId'),
      '#description' => $this->t('Client Id for the Login Widget'),
      '#default_value' => $config->get('signin_clientId'),
    ];

    $form['ssoconfig_signin']['signin_issuer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Issuer'),
      '#description' => $this->t('Issuer for the Login Widget'),
      '#default_value' => $config->get('signin_issuer'),
    ];

    $form['ssoconfig_signin']['signin_googleipd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Idp'),
      '#description' => $this->t('Google ipd for the Login Widget'),
      '#default_value' => $config->get('signin_googleipd'),
    ];

    $form['ssoconfig_signin']['signin_scope'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Scope for Okta plug-in'),
      '#description' => $this->t('Scope for Okta plug-in for authParams'),
      '#default_value' => $config->get('signin_scope'),
    ];

    $form['ssoconfig_signin']['signin_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User name prefix'),
      '#description' => $this->t('prefix to add to user name in the login form'),
      '#default_value' => $config->get('signin_prefix'),
    ];

      /*
      Account Links settings
      */
      $form['ssoconfig_snack'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Snack Web Site Links Settings'),
      ];

      $form['ssoconfig_snack']['snack_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Snack Web site URL'),
          '#description' => $this->t('Main snack URL'),
          '#default_value' => $config->get('snack_url'),
      ];

    
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ssoconfig_us.adminsettings')
      ->set('access_token_url',       $form_state->getValue('access_token_url'))
      ->set('client_id',              $form_state->getValue('client_id'))
      ->set('client_secret',          $form_state->getValue('client_secret'))
      ->set('scope',                  $form_state->getValue('scope'))
      ->set('groupIds',               $form_state->getValue('groupIds'))
      ->set('pimcore_endpoint',       $form_state->getValue('pimcore_endpoint'))
      ->set('och_userendpoint',       $form_state->getValue('och_userendpoint'))

      ->set('signin_baseUrl',         $form_state->getValue('signin_baseUrl'))
      ->set('signin_clientId',        $form_state->getValue('signin_clientId'))
      ->set('signin_issuer',          $form_state->getValue('signin_issuer'))
      ->set('signin_googleipd',       $form_state->getValue('signin_googleipd'))
      ->set('signin_scope',           $form_state->getValue('signin_scope'))
      ->set('signin_prefix',          $form_state->getValue('signin_prefix'))

      ->set('snack_url',       $form_state->getValue('snack_url'))

     ->save();
  }
}