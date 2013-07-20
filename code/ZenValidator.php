<?php
/**
 * 
 * @package ZenValidator
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 *
 **/
class ZenValidator extends Validator{

	
	/**
	 * @var Boolean
	 **/
	protected $parsleyEnabled;


	/**
	 * @param boolean $parsleyEnabled
	 **/
	public function __construct($parsleyEnabled = true){
		parent::__construct();
		$this->parsleyEnabled = $parsleyEnabled;
	}


	/**
	 * @param Form $form
	 */
	public function setForm($form) {
		parent::setForm($form);
		
		if($this->parsleyEnabled) $this->applyParsley();

		return $this;
	}


	/**
	 * applyParsley
	 * @return this
	 **/
	public function applyParsley(){
		$this->parsleyEnabled = true;
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(ZENVALIDATOR_PATH . '/javascript/parsley/parsley.min.js');

		$this->form->setAttribute('data-validate', 'parsley');
		$this->form->addExtraClass('parsley');

		foreach ($this->form->Fields()->dataFields() as $field) {
			foreach ($field->getConstraints() as $constraint) {
				$constraint->applyParsley();
			}
		}
		return $this;
	}


	/**
	 * disableParsley
	 * @return this
	 **/
	public function disableParsley(){
		$this->parsleyEnabled = false;
		$this->form->setAttribute('data-validate', '');
		$this->form->removeExtraClass('parsley');

		foreach ($this->form->Fields()->dataFields() as $field) {
			foreach ($field->getConstraints() as $constraint) {
				$constraint->removeParsley();
			}
		}
		return $this;
	}



	/**
	 * Set a ZenValidatorConstraint on a field
	 * @param string $fieldName
	 * @param ZenValidatorConstraint $constriant
	 */
	public function setConstraint($fieldName, ZenValidatorConstraint $constraint){
		$this->form->Fields()->fieldByName($fieldName)->setConstraint($constraint);
		if($this->parsleyEnabled){
			$constraint->applyParsley();
		}
		return $this;
	}


	/**
	 * Remove a ZenValidatorConstraint from a field
	 * @param string $fieldName
	 * @param string $constriant (classname)
	 */
	public function removeConstraint($fieldName, $constraintName){
		$this->form->Fields()->fieldByName($fieldName)->removeConstraint($constraintName);
		return $this;
	}


	/**
	 * Gets a ZenValidatorConstraint on a particular field
	 * @param string $fieldName
	 * @param string $constriant (classname)
	 * @return ZenValidatorConstraint
	 */
	public function getFieldConstraint($fieldName, $constraintName){
		return $this->form->Fields()->fieldByName($fieldName)->getConstraint($constraintName);
	}


	/**
	 * Gets an array of ZenValidatorConstraints on a particular field
	 * @param string $fieldName
	 * @return array
	 */
	public function getFieldConstraints($fieldName){
		return $this->form->Fields()->fieldByName($fieldName)->getConstraints();
	}


	/**
	 * Performs the php validation on all ZenValidatorConstraints attached to this validator
	 * @return $this
	 **/
	public function php($data){
		$fields = $this->form->fields->dataFields();

		foreach ($this->form->Fields()->dataFields() as $field) {
			foreach ($field->getConstraints() as $constraint) {
				if(!$constraint->validate($data[$field->getName()])){
					$this->validationError($field->getName(), $constraint->getMessage(), 'required');
				}
			}
		}
	}


	/**
	 * @TODO ?
	 **/
	public function removeValidation(){

	}
}