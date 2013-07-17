<?php

class ZenValidator extends Validator{
	

	/**
	 * field validators assigned to this validator
	 * @var array
	 **/
	protected $constraints = array();


	/**
	 * The FieldList being validated
	 * @var FieldList
	 **/
	protected $fields;


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
		$this->form->setAttribute('data-validate', "parsley");
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(ZENVALIDATOR_PATH . '/javascript/parsley/parsley.min.js');
		return $this;
	}

	/**
	 * addConstraint - adds a ZenValidatorType to this validator
	 * @param String $field - name of the field to be validated
	 * @param ZenFieldValidator $constraint 
	 * @return $this
	 **/
	public function addConstraint($fieldName, $constraint){
		$constraint->setField($this->fields->fieldByName($fieldName));
		$this->validators[$fieldName][$constraint->class] = $constraint;
		return $this;
	}	


	/**
	 * remove a validator type from a field
	 * @param String $field - name of the field to have a validationType removed from
	 * @param String $validatorType - name of the type to remove
	 * @return $this
	 **/
	function remove($fieldName, $validatorType){
		unset($this->validators[$fieldName][$validatorType]);
		return $this;
	}


	/**
	 * Performs the php validation on all validators attached to this validator
	 * @return $this
	 **/
	public function php($data){
		$fields = $this->form->fields->dataFields();

		foreach ($this->validators as $fieldName => $validators) {
			foreach ($validators as $validator) {

				if(!$validator->validate($data[$fieldName])){
					$this->validationError($fieldName, $validator->getMessage(), 'required');
				}
			}
		}
	}


	public function removeValidation(){

	}
}