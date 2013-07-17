<?php

class ZenFieldValidator_required extends Object{

	private $message;

	private $field;


	function __construct($field, $message){
		$this->field = $field;
		$this->$message = $message;
		$this->setHTMLAttributes();
	}


	/**
	 * Sets the html attributes required for frontend validation
	 * @return void
	 **/
	function setHTMLAttributes(){
		$this->field->setAttribute('data-required', 'true');
	}


	/**
	 * Performs php validation on the value 
	 * @param $value
	 * @return bool
	 **/
	function validate($value){
		return $value != '';
	}


	/**
	 * Get's the message that was set on the constrctor or falls back to default
	 * @return string
	 **/
	function getMessage(){
		if(!$this->message){
			$this->message = _t('ZenValidatorMessages.REQUIRED', 'This field is required');
		}
		return $this->message;
	}
}
