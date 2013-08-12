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
	 * constraints assigned to this validator
	 * @var array
	 **/
	protected $constraints = array();


	/**
	 * @var Boolean
	 **/
	protected $parsleyEnabled = true;


	/**
	 * @param boolean $parsleyEnabled
	 **/
	public function __construct($constraints = array()){
		parent::__construct();
		
		if(count($constraints)){
			$this->setConstraints($constraints);
		}
	}


	/**
	 * @param Form $form
	 */
	public function setForm($form) {
		parent::setForm($form);
		
		// a bit of a hack, need a security token to be set on form so that we can iterate over $form->Fields()
		if(!$form->getSecurityToken()) $form->disableSecurityToken();

		// disable parsley in the cms
		if(is_subclass_of($form->getController()->class, 'LeftAndMain')){
			$this->parsleyEnabled = false;
		}

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

		if($this->form){
			$this->form->setAttribute('data-validate', 'parsley');
			$this->form->addExtraClass('parsley');

			foreach ($this->constraints as $fieldName => $constraints) {
				foreach ($constraints as $constraint) {
					$constraint->setField($this->form->Fields()->dataFieldByName($fieldName))->applyParsley();
				}
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
		if($this->form){
			$this->form->setAttribute('data-validate', '');
			$this->form->removeExtraClass('parsley');	
		}
		return $this;
	}


	/**
	 * setConstraint - sets a ZenValidatorContraint on this validator
	 * @param String $field - name of the field to be validated
	 * @param ZenFieldValidator $constraint 
	 * @return $this
	 **/
	public function setConstraint($fieldName, $constraint){
		$this->constraints[$fieldName][$constraint->class] = $constraint;
		if($this->form && $this->parsleyEnabled){
			$constraint->setField($this->form->Fields()->dataFieldByName($fieldName))->applyParsley();
		}
		return $this;
	}


	/**
	 * setConstraints - sets multiple constraints on this validator
	 * @param array $constraints - $fieldName => ZenValidatorConstraint
	 * @return $this
	 **/
	public function setConstraints($constraints){
		foreach ($constraints as $fieldName => $v) {
			if (is_array($v)) {
				foreach ($v as $constraintFromArray) {
					$this->setConstraint($fieldName, $constraintFromArray);
				}
			}else{
				$this->setConstraint($fieldName, $v);
			}
		}
		return $this;
	}	


	/**
	 * remove a validator type from a field
	 * @param String $field - name of the field to have a validationType removed from
	 * @param String $validatorType - name of the type to remove
	 * @return $this
	 **/
	function removeConstraint($fieldName, $constraint){
		if($this->form) $constraint->removeParsley();
		unset($this->constraints[$fieldName][$constraint]);
		return $this;
	}


	/**
	 * A quick way of adding required constraints to a number of fields
	 * @param array $fieldNames - can be either indexed array of fieldnames, or associative array of fieldname => message
	 * @return this
	 */
	public function addRequiredFields($fields){
		if(ArrayLib::is_associative($fields)){
			foreach ($fields as $k => $v) {
				$constraint = Constraint_required::create();
				if($v) $constraint->setMessage($v);
				$this->setConstraint($k, $constraint);
			}	
		}else{
			foreach ($fields as $field) {
				$this->setConstraint($field, Constraint_required::create());
			}
		}

		return $this;
	}


	/**
	 * Performs the php validation on all ZenValidatorConstraints attached to this validator
	 * @return boolean
	 **/
	public function php($data){
		$valid = true;
		$fields = $this->form->fields->dataFields();

		foreach ($this->constraints as $fieldName => $constraints) {
			foreach ($constraints as $constraint) {

				if(!$constraint->validate($data[$fieldName])){
					$this->validationError($fieldName, $constraint->getMessage(), 'required');
					$valid = false;
				}
			}
		}
		return $valid;
	}


	/**
	 * Removes all constraints from this validator. Note that this gets called on Form::transform and may not always 
	 * be desireable for custom transformations that want to retain the validator. In that case, apply the validator after transformation
	 **/
	public function removeValidation(){
		var_dump(Debug::caller()); die;
		if($this->form){
			foreach ($this->constraints as $fieldName => $constraints) {
				foreach ($constraints as $constraint) {
					$constraint->removeParsley();
					unset($constraint);
				}
			}	
		}
		$this->constraints = array();
	}
}