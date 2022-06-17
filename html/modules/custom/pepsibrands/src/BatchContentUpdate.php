<?php

/**
 * @file
 */

namespace Drupal\pepsibrands;

use Drupal\pepsibrands\BrandsContentImport;

class BatchContentUpdate{

	public static function instance(){
		return new BatchContentUpdate();
	}

	public function batch_update($brand){
		if (strtolower($brand) == 'tostitos'){
			$this->batch_update_tostitos();
		}
	}

	private function batch_update_tostitos(){
		// Add superscripts to Tostitos recipe description
		$langcode = get_current_langcode($with_prefix = false);
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'recipe');
		$query->condition('field_brand_website', 'tostitos');
		$entity_ids = $query->execute();
		if (empty($entity_ids))
			return [];


		$nids = array_values($entity_ids);
		$number_nids = count($nids);
		$counter = 0;
		log_var("", " There are $number_nids recipes to udpate  ");
		foreach ($nids as $nid) {
			$counter++;
			$node = \Drupal\node\Entity\Node::load($nid); 
			$body = add_sup_tag($node->body->value , $strip_tags = false);
			$node->body->format = 'full_html';
			$node->body->value = $body;
			$node->save();
		}

		log_var("", "  $counter Tostitos recipes have been updated  ");

		return $recipes;
	}

}