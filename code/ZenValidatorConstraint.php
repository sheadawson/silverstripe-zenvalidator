<?php

abstract class ZenValidatorConstraint extends Object{

	/**
	 * @var FormField
	 **/
	protected $field;

	
	/**
	 * @var ZenValidator
	 **/
	protected $validator;

	
	/**
	 * @var string
	 **/
	protected $customMessage;

	
	/**
	 * @var string
	 **/
	protected $trigger;


	/**
	 * Set the field this constraint is applied to
	 * @param FormField $field
	 * @return this
	 **/
	public function setField(FormField $field){
		$this->field = $field;
		return $this;
	}


	/**
	 * Set the validator this constraint belongs to
	 * @param ZenValidator $validator
	 * @return this
	 **/
	public function setValidator(ZenValidator $validator){
		$this->validator = $validator;
		return $this;
	}


	/**
	 * Set a custom message for this constraint
	 * @param String $message
	 * @return this
	 **/
	function setMessage($message){
		$this->customMessage = $message;
		return $this;
	}


	/**
	 * Get's the message that was set on the constrctor or falls back to default
	 * @return string
	 **/
	function getMessage(){
		return $this->customMessage ? $this->customMessage : $this->getDefaultMessage();
	}


	/**
	 * Return the default message for this constraint
	 * @return string
	 **/
	abstract function getDefaultMessage();


	/**
	 * Sets the html attributes required for frontend validation
	 * Subclasses should call parent::applyParsley
	 * @return void
	 **/
	public function applyParsley(){
		if($this->customMessage){
			$this->field->setAttribute(sprintf('data-%s-message', $this->getConstraintName()), $this->customMessage);	
		}
	}


	/**
	 * Performs php validation on the value 
	 * @param $value
	 * @return bool
	 **/
	abstract function validate($value);


	/**
	 * Gets the name of this constraint from it's classname which should correspond
	 * to the string that parsley uses to identify a constraint type
	 * @return string
	 **/
	public function getConstraintName(){
		return str_replace('Constraint_', '', $this->class);
	}


	
}


/**
 * Constraint_required 
 * Basic required field form validation
 **/
class Constraint_required extends ZenValidatorConstraint{

	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-required', 'true');

	}


	public function validate($value){
		return $value != '';
	}


	public function getDefaultMessage(){
		return _t('ZenValidator.REQUIRED', 'This field is required');
	}
}


/**
 * Constraint_minlength
 **/
class Constraint_minlength extends ZenValidatorConstraint{
	
	/**
	 * @var int
	 **/
	protected $min;


	/**
	 * @param int $min - minimum allowed length
	 **/
	function __construct($min){
		$this->min = (int)$min;
		parent::__construct();
	}

	
	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-minlength', $this->min);
	}

	
	public function validate($value){
		if(!$value) return true;
		return strlen(trim($value)) >= $this->min;
	}


	public function getDefaultMessage(){
		return sprintf(_t('ZenValidator.MINLENGTH', 'This value is too short. It should have %s characters or more'), $this->min);
	}
}


class Constraint_maxlength extends ZenValidatorConstraint{

	/**
	 * @var int
	 **/
	protected $max;


	/**
	 * @param int $min - minimum allowed length
	 **/
	function __construct($max){
		$this->max = (int)$max;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-maxlength', $this->max);
	}

	
	public function validate($value){
		if(!$value) return true;
		return strlen(trim($value)) <= $this->max;
	}


	public function getDefaultMessage(){
		return sprintf(_t('ZenValidator.MAXLENGTH', 'This value is too long. It should have %s characters or less'), $this->max);
	}
}


class Constraint_rangelength extends ZenValidatorConstraint{

	/**
	 * @var int
	 **/
	protected $min;


	/**
	 * @var int
	 **/
	protected $max;


	/**
	 * @param int $min - minimum allowed length
	 * @param int $max - maximum allowed length
	 **/
	function __construct($min, $max){
		$this->min = (int)$min;
		$this->max = (int)$max;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-rangelength', sprintf("[%s,%s]", $this->min, $this->max));
	}


	function validate($value){
		if(!$value) return true;
		$len = strlen(trim($value));
		return $len >= $this->min && $len <= $this->max;
	}


	function getDefaultMessage(){
		return sprintf(_t('ZenValidator.RANGELENGTH', 'This value length is invalid. It should be between %s and %s characters long'), $this->min, $this->max);
	}
}


class Constraint_min extends ZenValidatorConstraint{

	/**
	 * @var int
	 **/
	protected $min;


	/**
	 * @param int $min - minimum allowed length
	 **/
	function __construct($min){
		$this->min = (int)$min;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-min', $this->min);
	}


	function validate($value){
		if(!$value) return true;
		return (int)$value >= $this->min;
	}


	function getDefaultMessage(){
		return sprintf(_t('ZenValidator.MIN', 'This value should be greater than or equal to %s'), $this->min);
	}
}


class Constraint_max extends ZenValidatorConstraint{

	/**
	 * @var int
	 **/
	protected $max;


	/**
	 * @param int $min - minimum allowed length
	 **/
	function __construct($max){
		$this->max = (int)$max;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-max', $this->max);
	}


	function validate($value){
		if(!$value) return true;
		return (int)$value <= $this->max;
	}


	function getDefaultMessage(){
		return sprintf(_t('ZenValidator.MAX', 'This value should be less than or equal to %s'), $this->max);
	}
}


class Constraint_range extends ZenValidatorConstraint{

	/**
	 * @var int
	 **/
	protected $min;


	/**
	 * @var int
	 **/
	protected $max;


	/**
	 * @param int $min - minimum allowed length
	 * @param int $max - maximum allowed length
	 **/
	function __construct($min, $max){
		$this->min = (int)$min;
		$this->max = (int)$max;
		parent::__construct();
	}
	

	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-range', sprintf("[%s,%s]", $this->min, $this->max));
	}


	function validate($value){
		if(!$value) return true;
		return (int)$value >= $this->min && (int)$value <= $this->max;
	}


	function getDefaultMessage(){
		return sprintf(_t('ZenValidator.RANGE', 'This value should be between %s and %s'), $this->min, $this->max);
	}
}


class Constraint_email extends ZenValidatorConstraint{

	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-type', 'email');
	}

	
	function validate($value){
		if(!$value) return true;
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}


	function getDefaultMessage(){
		return _t('ZenValidator.EMAIL', 'This value should be a valid email');
	}
}
