<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContributeForm
 *
 * @author miguel.pino
 */
namespace Drupal\pepsibam\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;


use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;



/**
 * Register form.
 */
class RegisterForm extends FormBase {

  protected $step = 1;
 
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
      return 'pepsireg_register_form';
  }

  /**
   * {@inheritdoc}
   */
  // public function buildForm(array $form, FormStateInterface $form_state) {
  //       $form['email'] = array(
  //         '#type' => 'email',
  //         '#title' => t('Email'),
  //         '#description' => '&nbsp;',
  //         '#required' => TRUE,
  //       );
  //       $form['password'] = array(
  //         '#type' => 'password',
  //         '#title' => t('Password'),
  //         '#description' => '',
  //       );
  //       $form['confirm_password'] = array(
  //         '#type' => 'password',
  //         '#title' => t('Confirm Password'),
  //         '#description' => '',
  //       );

  //       $form['firstname'] = array(
  //         '#type' => 'textfield',
  //         '#title' => t('First Name'),
  //         '#description' => '',
  //       );
        
  //       $form['lastname'] = array(
  //         '#type' => 'textfield',
  //         '#title' => t('Last Name'),
  //         '#description' => '',
  //       );
        
        
  //       $form['optin'] = array(
  //         '#type' => 'checkbox',
  //         '#title' => t('I would like to receive emails'),
  //         '#description' => '',
  //       );
        
  //       $form['registration'] = array(
  //         '#type' => 'button',
  //         '#value' => 'Commit',
  //         '#ajax' => array(
  //           'callback' => 'Drupal\pepsibam\Form\RegisterForm::randomUsernameCallback',
  //           'event' => 'click',
  //           'progress' => array(
  //             'type' => 'throbber',
  //             'message' => 'Submit Registration form',
  //           ),

  //         ),
  //       );
        
        
  //       /*
  //       $form['submit'] = array(
  //         '#type' => 'submit',
  //         '#value' => t('Submit'),
  //       );
  //        */
        
  //       /*$form['actions'] = array(
  //           '#type' => 'actions',
  //           'submit' =>array(
  //             '#type' => 'submit',
  //             '#value' => 'Submit',
  //           ),
  //           'validation-hack' => array(
  //             '#type' => 'button',
  //             '#value' => 'validated',
  //             '#attributes' => array('style' => 'display:block'),
  //             '#ajax' => array(
  //               'callback' => array($this, 'doSomething'),
  //               'event' => 'click',
  //               'progress' => array(
  //                 'type' => 'throbber',
  //                 'message' => 'Example...',
  //               ),
  //             ),
  //           ),
  //         );
  //       */
        
        
        
  //       return $form;      
  // }
  // 
  
  private function addGoogleButton(){
     return '<div class="modal__social-button">
                    <div class="g-signin2 google-registration hidden" id="google-registration" onclick="ClickLogin()" data-onsuccess="onSignIn" data-theme="dark"></div>

                    <button type="button" class="modal__sign-up-google" onclick="googleSignUp()">
                        <img src="/themes/tastytheme/src/images/google.png" alt="google" />' . $this->t("Sign up") . '
                    </button>
                </div>';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    //$form = parent::buildForm($form, $form_state);
 
    // Add a wrapper div that will be used by the Form API to update the form using AJAX
    $form['#prefix'] = '<div id="ajax_form_multistep_form">' ;
    $form['#suffix'] = '</div>';
      // $form['message-step'] = [
      //   '#markup' => '<div class="step">' . $this->t('Step 1 of 2') . '</div>',
      // ];
      $form['message-title'] = [
        '#markup' => '<h2>' . $this->t('Join the Tastyrewards Experience') . '</h2>',
      ];

      $form['googleSignUp'] = [
        '#type' => 'button',
        '#value' => $this->t('Google signup'),
        '#attributes' => [
          'onclick' => 'googleSignUp()',
          'class' => 'modal__sign-up-google',
        ],
        '#prefix' => '<div class="modal__social-button"><div class="g-signin2 google-registration hidden" id="google-registration" onclick="ClickLogin()" data-onsuccess="onSignIn" data-theme="dark"></div>',
        '#suffix' => '<img src="/themes/tastytheme/src/images/google.png" alt="google" />' . $this->t("Sign up") . '</div>',
        // '#attached' => array(
        //   'library' => array(
        //     'example/foo',
        //   ),
        // ),
      ];
      $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email Address'),
        '#placeholder' => $this->t('Email Address'),
        '#required' => TRUE,
      ];
      $form['first_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('First name'),
        '#placeholder' => $this->t('First name'),
        '#required' => TRUE,
      ];

      $form['last_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last name'),
        '#placeholder' => $this->t('Last name'),
        '#required' => TRUE,
      ];

      $form['postalcode'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Postal Code'),
        '#placeholder' => $this->t('Postal Code'),
        '#required' => TRUE,
      ];

      $form['password'] = [
        '#type' => 'password',
        '#title' => $this->t('Password'),
        '#placeholder' => $this->t('Password'),
        '#required' => TRUE,
      ];

      $form['bday_day'] = [
        '#type' => 'select',
        '#title' => $this
          ->t('Day'),
        '#options' => [
          '' => $this->t('Day'),
          '01' => 1,
          '02' => 2,
        ],
      ];

      $form['bday_month'] = [
        '#type' => 'select',
        '#title' => $this
          ->t('Month'),
        '#options' => [
          '' => $this->t('Month'),
          '01' => 1,
          '02' => 2,
        ],
      ];

      $form['bday_year'] = [
        '#type' => 'select',
        '#title' => $this
          ->t('Year'),
        '#options' => [
          '' => $this->t('Year'),
          '01' => 1,
          '02' => 2,
        ],
      ];
 
    $form['optin'] = array(
          '#type' => 'checkbox',
          '#title' => t('I would like to receive emails'),
          '#description' => '',
        );
 
    // if ($this->step == 2) {
    //   $form['message-step'] = [
    //     '#markup' => '<div class="step">' . $this->t('Step 2 of 2') . '</div>',
    //   ];
    //   $form['message-title'] = [
    //     '#markup' => '<h2>' . $this->t('Please enter your contact details below:') . '</h2>',
    //   ];
    //   $form['phone'] = [
    //     '#type' => 'textfield',
    //     '#title' => $this->t('Phone'),
    //     '#placeholder' => $this->t('Phone'),
    //     '#required' => TRUE,
    //   ];
    //   $form['email'] = [
    //     '#type' => 'email',
    //     '#title' => $this->t('Email address'),
    //     '#placeholder' => $this->t('Email address'),
    //     '#attributes' => array('class' => array('mail-first-step')),
    //     '#required' => TRUE,
    //   ];
    //   $form['subscribe'] = [
    //     '#type' => 'checkbox',
    //     '#title' => $this->t('Subscribe to newsletter'),
    //   ];
    //   $form['agree'] = [
    //     '#markup' => '<p class="agree">' . $this->t(' By signing up you agree to the <a href="@terms">Terms and Conditions</a> and <a href="@policy">Privacy Policy</a>',
    //         array('@terms' => '/terms-and-conditions', '@policy' => '/privacy-policy')) . '</p>',
    //   ];
    // }
 
    // if ($this->step == 3) {
    //   $form['message-step'] = [
    //     '#markup' => '<p class="complete">' . $this->t('- Complete -') . '</p>',
    //   ];
    //   $form['message-title'] = [
    //     '#markup' => '<h2>' . $this->t('Thank you') . '</h2>',
    //   ];
 
    // }
 
      $form['buttons']['forward'] = array(
        '#type' => 'submit',
        '#value' => t('Create my account'),
        '#prefix' => '<div class="step1-button">',
        '#suffix' => '</div>',
        '#ajax' => array(
          // We pass in the wrapper we created at the start of the form
          'wrapper' => 'ajax_form_multistep_form',
          // We pass a callback function we will use later to render the form for the user
          'callback' => '::ajax_form_multistep_form_ajax_callback',
          'event' => 'click',
        ),
      );
  
 
    $form['#attached']['library'][] = 'pepsibam/registrationform';
    return $form;
  }
 
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return parent::validateForm($form, $form_state);
  }
 
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->step == 2) {
      $values = $form_state->getValues();
      $email = $values['email'];
      \Drupal::logger('custom-module')->info(" Saved Ajax form values", []);
      // Save data or send email here.
    }
 
    $this->step++;
    $form_state->setRebuild();
  }
 
  public function ajax_form_multistep_form_ajax_callback(array &$form, FormStateInterface $form_state) {
    return $form;
  }
 

  
  
  public function randomUsernameCallback(array &$form, FormStateInterface &$form_state) {
    
    $errors = $form_state->getErrors();
    /*echo "<pre>";
    \Doctrine\Common\Util\Debug::dump($errors);
    echo "</pre>";  
    */
    drupal_get_messages('error');
    // Get all User Entities.
    //$all_users = entity_load_multiple('user');
    
    // Remove Anonymous User.
    //array_shift($all_users);
    
    // Pick Random User.
    //$random_user = $all_users[array_rand($all_users)];
    // Instantiate an AjaxResponse Object to return.
    $ajax_response = new AjaxResponse();
    
    
    if (!$errors) {
        $ajax_response->addCommand(new \Drupal\Core\Ajax\RedirectCommand('/') );
    }
    else {
        //ValCommand does not exist, so we can use InvokeCommand.
        $ajax_response->addCommand(new InvokeCommand('.edit-email--description', 'append' , array('Error')));
    }
    
    //$ajax_response->addCommand(new \Drupal\Core\Ajax\RedirectCommand('/') );
    
    // ChangedCommand did not work.
    //$ajax_response->addCommand(new ChangedCommand('#edit-user-name', '#edit-user-name'));
    
    // We can still invoke the change command on #edit-user-name so it triggers Ajax on that element to validate username.
    //$ajax_response->addCommand(new InvokeCommand('#edit-user-name', 'change'));
    
    // Return the AjaxResponse Object.
    return $ajax_response;
  }
  
  
}
?>