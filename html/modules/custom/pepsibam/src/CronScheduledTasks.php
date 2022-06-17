<?php

/**
 * @file
 */

namespace Drupal\pepsibam;

class CronScheduledTasks{
	private $frequency;

	public static function instance(){
		return new CronScheduledTasks();
	}

	public function run_every($funcname, $hours, $args = [], $with_log = true){
		$this->frequency =  3600*$hours; 
		$last_run =  $this->time_since_last_run($funcname . serialize($args));
		$now = strtotime(date("Y-m-d H:i:s")) ;
		$diff = abs($now - strtotime($last_run));

		if ($last_run === 0 || $diff >= $this->frequency){
			$this->doRun($funcname, $args);
		} else {
			if ($with_log){
				\Drupal::logger('cron-scheduledTasks')->info("$funcname was not run, last run was " . $last_run, []);
			}
		}
	}

	public function time_since_last_run($funcname){
		$config = \Drupal::service('config.factory')->getEditable('pepsibam_cron.settings');
		$last_run = $config->get('last_run');
		if (!isset($last_run[$funcname]))
			return 0;

		return $last_run[$funcname];

	}

	public function doRun($funcname, $params = []){
		try{
			call_user_func_array($funcname, $params);
			$config = \Drupal::service('config.factory')->getEditable('pepsibam_cron.settings');
			$last_run = $config->get('last_run');
			if (empty($last_run))
				$last_run = [];
			$last_run[$funcname . serialize($params)] = date("Y-m-d H:") . "00:00";
			$config->set('last_run', $last_run)
			->save();

			\Drupal::logger('cron-scheduledTasks')->info(" Function $funcname was run successfully", []);
		} catch (\Exception $e){
			\Drupal::logger('cron-scheduledTasks')->info("Could not run $funcname ", []);
			return false;
		}
	} 
}