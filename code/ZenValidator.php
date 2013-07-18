<?php

class ZenValidator extends Validator{
	
	/**
	 * The FieldList being validated
	 * @var FieldList
	 **/
	protected $fields;


	/**
	 * field validators assigned to this validator
	 * @var array
	 **/
	protected $fieldConstraints = array();


	/**
	 * @var Boolean
	 **/
	protected $parsley = true;


	/**
	 * The FieldList being validated
	 * @var FieldList
	 **/
	function __construct(FieldList $fields){
		$this->fields = $fields;
	}


	/**
	 * @param Form $form
	 */
	public function setForm($form) {
		$this->form = $form;
		
		if($this->parsley) $this->applyParsley();

		return $this;
	}

	/**
	 * addConstraint - adds a ZenValidatorType to this validator
	 * @param String $field - name of the field to be validated
	 * @param ZenFieldValidator $constraint 
	 * @return $this
	 **/
	public function addConstraint($fieldName, $constraint){
		$constraint
			->setField($this->fields->fieldByName($fieldName))
			->setValidator($this);

		if(!isset($this->fieldConstraints[$fieldName])){
			$this->fieldConstraints[$fieldName] = array();
		}

		$this->fieldConstraints[$fieldName][$constraint->class] = $constraint;

		return $this;
	}	


	/**
	 * remove a validator type from a field
	 * @param String $field - name of the field to have a validationType removed from
	 * @param String $validatorType - name of the type to remove
	 * @return $this
	 **/
	function removeConstraint($fieldName, $constraint){
		unset($this->fieldConstraints[$fieldName][$constraint]);
		return $this;
	}


	/**
	 * applyParsley
	 * @return void
	 **/
	public function applyParsley(){
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(ZENVALIDATOR_PATH . '/javascript/parsley/parsley.min.js');

		$this->form->setAttribute('data-validate', 'parsley');
		$this->form->addExtraClass('parsley');
		
		foreach ($this->fieldConstraints as $constraints) {
			foreach ($constraints as $constraint) {
				$constraint->applyParsley();
			}
		}
	}


    /**
	 * Enable or disable client side validation
	 * @param Boolean $bool
	 */
	public function enableParsley($bool) {
		$this->parsley = $bool;
		return $this;
	}


	/**
	 * Performs the php validation on all ZenValidatorConstraints attached to this validator
	 * @return $this
	 **/
	public function php($data){
		$fields = $this->form->fields->dataFields();

		foreach ($this->fieldConstraints as $fieldName => $constraints) {
			foreach ($constraints as $constraint) {
				if(!$constraint->validate($data[$fieldName])){
					$this->validationError($fieldName, $constraint->getMessage(), 'required');
				}
			}
		}
	}


	/**
	 * @TODO 
	 **/
	public function removeValidation(){

	}
}