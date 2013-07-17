<?php

abstract class ZenFieldValidator extends Object{
	protected $message;

	protected $field;

	protected $val;

	protected $trigger;

	function __construct($val=''){
		$this->val = $val;
	}

	function setField($field){
		$this->field = $field;
		$this->setHTMLAttributes();
		return $this;
	}


	function setMessage($message){
		$this->message = $message;
		return $this;
	}


	function setTrigger($trigger){
		$this->trigger = $trigger;
		return $this;
	}


	/**
	 * Get's the message that was set on the constrctor or falls back to default
	 * @return string
	 **/
	abstract function getMessage();


	/**
	 * Sets the html attributes required for frontend validation
	 * @return void
	 **/
	protected function setHTMLAttributes(){
		$this->field->setAttribute('data-trigger', $this->trigger);
	}


	/**
	 * Performs php validation on the value 
	 * @param $value
	 * @return bool
	 **/
	abstract function validate($value);


	
}

class ZenValidator_required extends ZenFieldValidator{

	protected function setHTMLAttributes(){
		parent::setHTMLAttributes();
		$this->field->setAttribute('data-required', 'true');
	}


	function validate($value){
		return $value != '';
	}


	function getMessage(){
		return $this->message ? $this->message : _t('ZenValidatorMessages.REQUIRED', 'This field is required');
	}
}


class ZenValidator_minlength extends ZenFieldValidator{

	protected function setHTMLAttributes(){
		parent::setHTMLAttributes();
		$this->field->setAttribute('data-minlength', (int)$this->val);
	}

	
	function validate($value){
		return strlen($value) >= $this->val;
	}


	function getMessage(){
		return $this->message ? $this->message : _t('ZenValidatorMessages.REQUIRED', 'This field is required to be at least x long');
	}
}


class ZenValidator_email extends ZenFieldValidator{

	protected function setHTMLAttributes(){
		parent::setHTMLAttributes();
		$this->field->setAttribute('data-type', 'email');
	}

	
	function validate($value){
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}


	function getMessage(){
		return $this->message ? $this->message : _t('ZenValidatorMessages.REQUIRED', 'Please enter a valid email address');
	}
}
