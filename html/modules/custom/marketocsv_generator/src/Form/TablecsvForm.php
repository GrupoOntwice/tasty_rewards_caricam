<?php

namespace Drupal\marketocsv_generator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Datetime;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing;
use Drupal\group\Entity;
use Drupal\group\Entity\Group;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\group\Entity\GroupType;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\field\FieldConfigInterface;
use PhpOffice\PhpSpreadsheet\Document\PhpOffice\PhpSpreadsheet\Document;
use Drupal\user\Entity\User;

/**
 * Class TablecsvForm.
 *
 * @package Drupal\marketocsv_example\Form
 */
class TablecsvForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csvtable_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	
	//$form['text']['#markup'] = t('Some text.');
	$form['generatecsvhidden'] = array(
		'#type' => 'hidden',
		'#title' => t('vid'),
		'#required' => TRUE,
		'#prefix' => '<div class="form-group panel panel-primary" id="names-fieldset-wrapper"><div class="panel-heading">Generate CSV</div><div class="panel-body">',
		'#attributes' => array('class' => array('form-control'), 'autocomplete' => 'off'), 
		'#default_value' => \Drupal::currentUser()->id(),
	);
	$settingslink = \Drupal::l(t('Check Settings ?'), \Drupal\Core\Url::fromRoute('marketocsv_generator.settings_form'));
	$form['block_title'] = [
	'#type' => 'item',
	//'#title' => t('Block title'),
	'#markup' => t('Kindly, check your setting before click on the submit button of Generate CSV @link', array('@link' => $settingslink)),
	];
	
    $form['create_csv'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Generate the CSV file'),
      '#attributes' => array('class' => array('btn btn-success btn-lg')),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
	$header_data = array();
	$ma_count = '';
    /** Count the Total Rows of the table **/
    if(\Drupal::database()->schema()->tableExists('marketo_activities_10_12')) {
		$ma_query = \Drupal::database()->query('Select * from marketo_activities_10_12');
		$ma_query->allowRowCount = TRUE;
		$ma_count = $ma_query->rowCount();
	}
    /** Header data of the CSV file **/
	$header_data = array('Lead ID', 'Email', 'Action Timestamp', 'Activity Type ID', 'Primary Attribute Value ID', 'Primary Attribute Value', 'Campaign_ID');
	/** Configuration settings of Marketo form **/
	$userentities_load = \Drupal::config('bulkmarketocsv.usersettings');
	$bulkuserid = $userentities_load->get('bulkuserid');
	$rangecount = $userentities_load->get('range_count');
	$folder_name = $userentities_load->get('folder_name');
	$file_name = $userentities_load->get('file_name');
	/** Configuration of the file path in the System **/
	$marketofilepath = \Drupal::service('file_system')->realpath(file_default_scheme() . "://" . $folder_name);
	$marketofile = \Drupal::service('file_system')->realpath(file_default_scheme() . "://" . $folder_name . '/'. $file_name);
	/** Creation of the folder and filepath for first time **/
	if(!file_exists($marketofilepath) && file_prepare_directory($marketofilepath, FILE_CREATE_DIRECTORY)){
		$file = File::create([
		'filename' => $file_name,
		'uri' => 'public://' . $folder_name . '/'. $file_name,
		'status' => 1,
		]);
		$file->save();
		$dir = dirname($file->getFileUri());
		if (!file_exists($dir)) {
		  mkdir($dir, 0770, TRUE);
		}
		$fp = fopen($file->getFileUri(), 'w+');
		fputcsv($fp, $header_data);
		fclose($fp);
	}
	/** Create header line in csv if no records is present **/
	if(file_exists($marketofile)) {
	$count_csv = file($marketofile, FILE_SKIP_EMPTY_LINES);
	}
	if(file_exists($marketofile) && count($count_csv) <= 1) {
		$fp = fopen($marketofile, 'w+');
		fputcsv($fp, $header_data);
		fclose($fp);
	}
	/** Operations of the Batch by setting limit to run million of the record **/
	$operations = array();
	if(file_exists($marketofile) && isset($ma_count)) {
		for($i=0;$i<=$ma_count;$i=$i+1000) {
			$operations[] = array(
			  '\Drupal\marketocsv_generator\CreateCsv::processcsvFile',
			  array(array($i), array($marketofile))
			);
		}
	}
	/** Setting the Batch for the Generation of the CSV **/
	$batch = array(
	'title' => t('Join and Generate CSV...'),
	'init_message' => t('Join and Generate CSV is starting.'),
    'progress_message' => t('Processed CSV @current out of @total.'),
    'error_message' => t('Join and Generate CSV Batch has encountered an error.'),
	'operations' => $operations,
	'finished' => '\Drupal\marketocsv_generator\CreateCsv::joinedtableFinishforcsvCallback',
	);
	batch_set($batch);
  }

}
