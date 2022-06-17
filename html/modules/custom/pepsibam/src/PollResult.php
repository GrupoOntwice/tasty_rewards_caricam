<?php

/**
 * @file
 */

namespace Drupal\pepsibam;


class PollResult{

	private $polls = [];

	public static function instance(){
		return new PollResult();
	}

	public function init(){
		// columns we want 
		// Uid, email, language,  question (pollname), options, answer, tags (for each option)
		// add new column poll_type (Poll Type: Brand Specific / Occasion Specific / Demographic Specific / Flavour Related / Recipe Related / Other) 

		// get all existing polls : question (id), options (chids),
		$this->init_questions();
		$this->init_choices();

		/*
			Poll__choice (links pid with chid) 
				Entity_id (pid) & choid_target_id (chid) 
			poll_field_data Has the questions
				id, question, langcode

		*/ 
	}

	public function hasChoice($pid, $choice_id){
		if ( !array_key_exists($pid, $this->polls) ){
			return null;
		}

		if (isset($this->polls['choices'][$choice_id] )){
			return true;
		}

		return false;
	}

	public function getQuestion($pid, $langcode){
		$pid = intval($pid);
		if ( isset($this->polls[$pid]['question'][$langcode]) ){
			return $this->polls[$pid]['question'][$langcode];
		}
		return '';
	}

	public function getOptions($pid, $langcode){
		$pid = intval($pid);
		$options = '';
		if ( isset($this->polls[$pid]['choices'] ) ){
			foreach ($this->polls[$pid]['choices'] as $chid => $choice) {
				$options .= $choice[$langcode] . "|";
			}
			$options = rtrim($options, "|");
		}
		return $options;
	}

	public function getAnswer($pid, $langcode, $chid){
		$pid = intval($pid);
		$chid = intval($chid);
		if ( isset($this->polls[$pid]['choices'] ) ){
			return $this->polls[$pid]['choices'][$chid][$langcode];
		}
		return '';
	}

	public function addChoice($pid, $choice_id){
		// this should add chid, lang, choice_text
		$sql = "SELECT id, langcode, choice from poll_choice_field_data WHERE id = $choice_id";
		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		if (!empty($results)){
			$choices = [];
			foreach ($results as $key => $res) {
				$choices[$res->langcode] = $res->choice;
			}
			$this->polls[$pid]['choices'][$choice_id] = [
				'chid' => $choice_id,				
			];

			foreach ($choices as $lang => $answer) {
				$this->polls[$pid]['choices'][$choice_id][$lang] = $answer;
			}
		}
	}

	public function init_choices(){
		$sql = "SELECT entity_id as pid, choice_target_id as chid from poll__choice
				order by entity_id asc";
		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		if (!empty($results)){
			
			foreach ($results as $key => $res) {
				if (!$this->hasChoice($res->pid, $res->chid) ){
					$this->addChoice($res->pid, $res->chid);
				}
			}
		}

		$polls = $this->polls;
		return $polls;

	}

	public function init_questions(){
		$sql = "SELECT id, question, langcode from poll_field_data
				order by id asc";
		$query = \Drupal::database()->query($sql);
		$results = $query->fetchAll();
		if (!empty($results)){
			
			foreach ($results as $key => $res) {
				if ( !array_key_exists($res->id, $this->polls) ){
					$this->polls[$res->id] = [
						'id' => $res->id,
						'question' => [
							$res->langcode => $res->question,
						],
						'choices' => [],
					];
				} else {
					$this->polls[$res->id]['question'][$res->langcode] = $res->question; 
				}
			}

			$polls = $this->polls;


		}

	}
}