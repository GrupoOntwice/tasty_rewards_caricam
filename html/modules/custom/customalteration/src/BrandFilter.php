<?php

/**
 * @file
 */

namespace Drupal\customalteration;

class BrandFilter{

	public static function instance(){
		return new BrandFilter();
	}

	public function save_brand_options($options){
		$langcode = get_current_langcode();
		$config = \Drupal::service('config.factory')->getEditable('customalteration.settings');
		$last_saved = $config->get('last_saved');
		if (isset($last_saved[$langcode])){
			$_24hours = 24*3600;
			$now = strtotime(date("Y-m-d H:i:s"));
			$last_save_timestamp = strtotime($last_saved[$langcode]);
			if (abs($now - $last_save_timestamp) > $_24hours){
				$this->doSave($options, $langcode);
			}
		} else {
			$this->doSave($options, $langcode);
		}
	}

	public function doSave($options, $langcode){
		$brands_options = [];
		foreach ($options as $option) {
			$key = strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', trim($option) ) );
			$key = str_replace(' ', '', $key);
			$brands_options[$key] = $option;
		}
		$config = \Drupal::service('config.factory')->getEditable('customalteration.settings');
		$brands = $config->get('brand_options');
		if (empty($brands))
			$brands = [];
		$brands[$langcode] = $brands_options;
		$last_saved = $config->get('last_saved');
		if (empty($last_saved))
			$last_saved = [];
		$last_saved[$langcode] = date("Y-m-d H:i:s");
		$config->set('last_saved', $last_saved);
		$config->set('brand_options', $brands)
				->save();

		\Drupal::logger('custom-module')->info("Brands filters saved " . json_encode($brands), []);
		
	}

	public function get_brand_options(){
		$langcode = get_current_langcode();
		$config = \Drupal::service('config.factory')->getEditable('customalteration.settings');
		$brand_options = $config->get('brand_options');

		if (isset($brand_options[$langcode]))
			return $brand_options[$langcode];

		return [];
	}

}