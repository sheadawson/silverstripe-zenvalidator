<?php

/**
 * @package ZenValidator
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 **/
abstract class ZenValidatorConstraint extends Object{

	/**
	 * @var FormField
	 **/
	protected $field;

	
	/**
	 * @var string
	 **/
	protected $customMessage;


	/**
	 * @var boolean
	 **/
	protected $parsleyApplied;


	/**
	 * Set the field this constraint is applied to
	 * @param FormField $field
	 * @return this
	 **/
	public function setField(FormField $field){
		$this->field = $field;
		return $this;
	}


	public function getField(){
		return $this->field;
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
		$this->parsleyApplied = true;
		if($this->customMessage){
			$this->field->setAttribute(sprintf('data-%s-message', $this->getConstraintName()), $this->customMessage);	
		}
	}


	/**
	 * Removes the html attributes required for frontend validation
	 * Subclasses should call parent::removeParsley
	 * @return void
	 **/
	public function removeParsley(){
		$this->parsleyApplied = false;
		if($this->field && $this->customMessage){
			$this->field->setAttribute(sprintf('data-%s-message', $this->getConstraintName()), '');	
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


	public function removeParsley(){
		parent::removeParsley();
		$this->field->setAttribute('data-required', '');
	}


	public function validate($value){
		return $value != '';
	}


	public function getDefaultMessage(){
		return _t('ZenValidator.REQUIRED', 'This field is required');
	}
}


/**
 * Constraint_length
 **/
class Constraint_length extends ZenValidatorConstraint{
	
	/**
	 * @var string
	 **/
	protected $type;


	/**
	 * @var int
	 **/
	protected $val1, $val2;


	/**
	 * @param srting $type (min,max,range)
	 * @param int $val1
	 * @param int $val2
	 **/
	function __construct($type, $val1, $val2 = null){
		$this->type = $type;
		$this->val1 = (int)$val1;
		$this->val2 = (int)$val2;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		switch ($this->type) {
			case 'min':
				$this->field->setAttribute('data-minlength', $this->val1);
				break;
			case 'max':
				$this->field->setAttribute('data-maxlength', $this->val1);
				break;
			case 'range':
				$this->field->setAttribute('data-rangelength', sprintf("[%s,%s]", $this->val1, $this->val2));
				break;
		}
	}


	public function removeParsley(){
		parent::removeParsley();
		switch ($this->type) {
			case 'min':
				$this->field->setAttribute('data-minlength', '');
				break;
			case 'max':
				$this->field->setAttribute('data-maxlength', '');
				break;
			case 'range':
				$this->field->setAttribute('data-rangelength', '');
				break;
		}
	}


	function validate($value){
		if(!$value) return true;

		switch ($this->type) {
			case 'min':
				return strlen(trim($value)) >= $this->val1;
			case 'max':
				return strlen(trim($value)) <= $this->val1;
			case 'range':
				return strlen(trim($value)) >= $this->val1 && strlen(trim($value)) <= $this->val2;
		}
	}


	function getDefaultMessage(){
		switch ($this->type) {
			case 'min':
				return sprintf(_t('ZenValidator.MINLENGTH', 'This value is too short. It should have %s characters or more'), $this->val1);
			case 'max':
				return sprintf(_t('ZenValidator.MAXLENGTH', 'This value is too long. It should have %s characters or less'), $this->val1);
			case 'range':
				return sprintf(_t('ZenValidator.RANGELENGTH', 'This value length is invalid. It should be between %s and %s characters long'), $this->val1, $this->val2);
		}
	}
}


class Constraint_value extends ZenValidatorConstraint{

	
	/**
	 * @var string
	 **/
	protected $type;


	/**
	 * @var int
	 **/
	protected $val1, $val2;


	/**
	 * @param srting $type (min,max,range)
	 * @param int $val1
	 * @param int $val2
	 **/
	function __construct($type, $val1, $val2 = null){
		$this->type = $type;
		$this->val1 = (int)$val1;
		$this->val2 = (int)$val2;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		switch ($this->type) {
			case 'min':
				$this->field->setAttribute('data-min', $this->val1);
				break;
			case 'max':
				$this->field->setAttribute('data-max', $this->val1);
				break;
			case 'range':
				$this->field->setAttribute('data-range', sprintf("[%s,%s]", $this->val1, $this->val2));
				break;
		}
	}


	public function removeParsley(){
		parent::removeParsley();
		switch ($this->type) {
			case 'min':
				$this->field->setAttribute('data-min', '');
				break;
			case 'max':
				$this->field->setAttribute('data-max', '');
				break;
			case 'range':
				$this->field->setAttribute('data-range', '');
				break;
		}
	}


	function validate($value){
		if(!$value) return true;

		switch ($this->type) {
			case 'min':
				return (int)$value >= $this->val1;
			case 'max':
				return (int)$value <= $this->val1;
			case 'range':
				return (int)$value >= $this->val1 && (int)$value <= $this->val2;
		}
	}


	function getDefaultMessage(){
		switch ($this->type) {
			case 'min':
				return sprintf(_t('ZenValidator.MIN', 'This value should be greater than or equal to %s'), $this->val1);
			case 'max':
				return sprintf(_t('ZenValidator.MAX', 'This value should be less than or equal to %s'), $this->val1);
			case 'range':
				return sprintf(_t('ZenValidator.RANGE', 'This value should be between %s and %s'), $this->val1, $this->val2);
		}
	}
}


class Constraint_regex extends ZenValidatorConstraint{

	/**
	 * @var string
	 **/
	protected $regex;


	/**
	 * @param string $regex
	 **/
	function __construct($regex){
		$this->regex = $regex;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$this->field->setAttribute('data-regexp', trim($this->regex, '/'));
	}


	public function removeParsley(){
		parent::removeParsley();
		$this->field->setAttribute('data-regexp', '');
	}


	function validate($value){
		if(!$value) return true;
		return preg_match($this->regex, $value);
	}


	function getDefaultMessage(){
		return _t('ZenValidator.REGEXP', 'This value seems to be invalid');
	}
}



class Constraint_remote extends ZenValidatorConstraint{

	/**
	 * @var string
	 **/
	protected $url;

	/**
	 * @var array
	 **/
	protected $params;

	/**
	 * @var string
	 **/
	protected $method;

	/**
	 * @var string
	 **/
	protected $jsonp;


	/**
	 * @param string $url - the url to call via ajax
	 * @param array $params - request vars
	 * @param string $method - method of ajax request (GET / POST)
	 * @param boolean $jsonp  if you make cross domain ajax call and expect jsonp,
	 * The following are valid responses from the remote url, with a 200 response code: 1, true, { "success": "..." } and assume false otherwise
     * You can show frontend server-side specific error messages by returning { "error": "your custom message" } or { "message": "your custom message" }
	 **/
	function __construct($url, $params=array(), $method='GET', $jsonp=false){
		$this->url = Director::absoluteURL($url);
		$this->params = $params;
		$this->method = $method;
		$this->jsonp = $jsonp;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$url = count($this->params) ? $this->url . '?' . $this->http_build_query($this->params) : $this->url;
		$this->field->setAttribute('data-remote', $url);
		if($this->method == 'POST') $this->field->setAttribute('data-remote-method', 'POST');
		if($this->jsonp) $this->field->setAttribute('data-remote-datatype', 'jsonp');
	}


	public function removeParsley(){
		parent::removeParsley();
		$this->field->setAttribute('data-remote', '');
		if($this->field->getAttribute('data-remote-method')) $this->field->setAttribute('data-remote-method', '');
		if($this->field->getAttribute('data-remote-datatype')) $this->field->setAttribute('data-remote-datatype', '');
	}


	function validate($value){
		// get result 
		$ch=curl_init();
		$url = $this->url;

		$this->params[$this->field->getName()] = $value;

		$query = http_build_query($this->params);
		if($this->method == 'GET'){
			$url = $url . '?' . $query;	
		}else{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		}

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ZENVALIDATOR');
		$result = curl_exec($ch);
		curl_close($ch);
		
		// validate result
		if($result == '1' || $result == 'true'){
			return true;
		}

		$isJson = ((is_string($result) && (is_object(json_decode($result)) || is_array(json_decode($result))))) ? true : false;

		if($isJson){
			$result = Convert::json2obj($result);
			if(isset($result->success)){
				return true;
			}else{
				if(isset($result->message)){
					$this->setMessage($result->message);
				}elseif(isset($result->error)){
					$this->setMessage($result->error);
				}
			}
		}

		return false;
	}


	function getDefaultMessage(){
		return _t('ZenValidator.REMOTE', 'This value seems to be invalid');
	}
}


class Constraint_type extends ZenValidatorConstraint{

	/**
	 * @var string
	 **/
	protected $type;


	/**
	 * @param int $type - allowed datatype
	 **/
	function __construct($type){
		$this->type = $type;
		parent::__construct();
	}


	public function applyParsley(){
		parent::applyParsley();
		$type = ($this->type == 'url') ? 'urlstrict' : $this->type;
		$this->field->setAttribute('data-type', $type);
	}


	public function removeParsley(){
		parent::removeParsley();
		$this->field->setAttribute('data-type', '');
	}

	
	function validate($value){
		if(!$value) return true;

		switch ($this->type) {
			case 'url':
				return filter_var($value, FILTER_VALIDATE_URL);
			case 'email':
				return filter_var($value, FILTER_VALIDATE_EMAIL);
			case 'number':
				return is_numeric($value);
			case 'alphanum':
				return ctype_alnum($value);
		}
	}


	function getDefaultMessage(){
		switch ($this->type) {
			case 'url':
				return _t('ZenValidator.URL', 'This value should be a valid URL');
			case 'urlstrict':
				return _t('ZenValidator.URL', 'This value should be a valid URL');
			case 'email':
				return _t('ZenValidator.EMAIL', 'This value should be a valid email');
			case 'number':
				return _t('ZenValidator.NUMBER', 'This value should be a number');
			case 'alphanum':
				return _t('ZenValidator.URL', 'This value should be alphanumeric');
		}
	}
}




