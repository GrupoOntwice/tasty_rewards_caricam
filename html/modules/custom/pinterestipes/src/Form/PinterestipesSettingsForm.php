<?php

/**
 * @file
 * Contains \Drupal\pinterestipes\Form\PinterestipesSettingsForm.
 */

namespace Drupal\pinterestipes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class PinterestipesSettingsForm extends ConfigFormBase
{

	/**
	 * 1. BUILD
	 * 2. SUBMIT
	 * 3. HELPERS
	 */

	/** ====================================
	 * 1. BUILD
	===================================== **/

	public function buildForm(array $form, FormStateInterface $formState = null)
	{

		$config = $this->config('pinterestipes.settings');

		/** IFTTT SETTINGS **/
		$form['ifttt'] = array(
			'#type' => 'details',
			'#title' => 'IFTTT',
			'#open' => true,
		);

		$form['ifttt']['pinterest_ifttt_key'] = array(
			'#type' => 'textfield',
			'#title' => 'IFTTT Key',
			'#default_value' => $config->get('pinterest_ifttt_key'),
		);

		$formBuilt = parent::buildForm($form, $formState);
		return $formBuilt;
	}

	/** ====================================
	 * 2. SUBMIT
	===================================== **/

	public function submitForm(array &$form, FormStateInterface $formState)
	{
		$this->configFactory->getEditable('pinterestipes.settings')

			->set('pinterest_ifttt_key', $formState->getValue('pinterest_ifttt_key'))
			->save();

		parent::submitForm($form, $formState);
	}

	/** ====================================
	 * 3. HELPERS
	===================================== **/

	public function getFormId()
	{

		return 'pinterestipes_admin_settings';
	}

	protected function getEditableConfigNames()
	{

		return [
			'pinterestipes.settings',
		];
	}
}
