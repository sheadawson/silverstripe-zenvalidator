<?php
/**
 * 
 * @package ZenValidator
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 *
 **/
class ZenValidatorFormFieldExtension extends Extension{

	/**
	 * @var array
	 **/
	protected $constraints = array();

	
	/**
	 * Set a ZenValidationConstraint on this field
	 * @param ZenValidatorConstraint $constraint
	 * @return this->owner
	 **/
	public function setConstraint(ZenValidorConstraint $constraint){
		$constraint->setField($this->owner);
		$this->constraints[$constraint->class] = $constraint;
		return $this->owner;
	}


	/**
	 * Remove a ZenValidationConstraint from this field, by it's class name
	 * @param string $class
	 * @return this->owner
	 **/
	public function removeConstraint($class){
		if(isset($this->constraints[$class])){
			$constraint = $this->constraints[$class];
			$constraint->removeParsley();
		}
		unset($this->constraints[$class]);
		return $this->owner;
	}


	/**
	 * Remove all ZenValidatorConstraints from this field
	 * @return this->owner
	 **/
	public function removeConstraints(){
		foreach ($this->constraints as $key => $value) {
			$this->removeConstraint($key);
		}
		return $this->owner;
	}


	/**
	 * @return array
	 **/
	public function getConstraints(){
		return $this->constraints;
	}


	/**
	 * Get a ZenValidatorConstraint from this field by class name
	 * @param string $class
	 * @return array
	 **/
	public function getConstraint($class){
		if(isset($this->constraints[$class])){
			return $this->constraints[$class];
		}
	}
}