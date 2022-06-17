<?php

namespace Drupal\marketocsv_generator;


use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\HttpFoundation\Response;
use Drupal\file\Entity\File;

class CreateCsv {

  public static function processcsvFile($i, $file, &$context){
	$i = $i['0'];	
	$file = $file['0'];
    $message = 'Processing CSV files...';
    $results = array();
    $dataresult = array();
		$handle = fopen($file, 'a+');
		$session = \Drupal::request()->getSession();
		$session->set('statmsg', '');
	if((\Drupal::database()->schema()->tableExists('marketo_activities_10_12')) && (\Drupal::database()->schema()->tableExists('email_marketo_id')) ) {
		$table_datas = \Drupal::database()->select('marketo_activities_10_12', 'ma');
		$table_datas->join('email_marketo_id', 'emi', 'ma.lead_id = emi.field_marketoid_value');
		$table_datas->fields('ma', array('lead_id', 'action_timestamp', 'activity_type_id', 'primary_attribute_value_id', 'primary_attribute_value', 'campaign_id'));
		$table_datas->fields('emi', array('mail', 'field_marketoid_value'));
		$table_datas->range($i, 1000);
		$result_datas = $table_datas->execute()->fetchAll();
		foreach ($result_datas as $table_data) {
			$dataresult = array($table_data->lead_id, $table_data->mail, $table_data->action_timestamp, $table_data->activity_type_id, $table_data->primary_attribute_value_id, $table_data->primary_attribute_value, $table_data->campaign_id);
			$results[] = fputcsv($handle, $dataresult);
		}
		fclose($handle);
	}
	else {
	   $message_data = t('Kindly check if the Given DB table is present in this process.');
	   $session->set('statmsg', $message_data);
	}
		
    $context['message'] = $message;
    $context['results'] = $results;
  }

  public static function joinedtableFinishforcsvCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    $session = \Drupal::request()->getSession();
    $db_detail_msg = $session->get('statmsg');
    
    if ($success) {
      $message = isset($db_detail_msg) ? $db_detail_msg : \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
   
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }
}
