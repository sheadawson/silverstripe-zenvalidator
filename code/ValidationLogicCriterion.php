<?php

/**
 *  Defines a criterion that controls the display of a given
 *  {@link FormField} object
 *
 * @package  display_logic
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class ValidationLogicCriterion extends Object {


	/**
	 * The name of the form field that is controlling the display
	 * @var string
	 */
	protected $master = null;



	/**
	 * The comparison function to use, e.g. "EqualTo"
	 * @var string
	 */
	protected $operator = null;




	/**
	 * The value to compare to
	 * @var mixed
	 */
	protected $value = null;



	/**
	 * The parent {@link ValidationLogicCriteria}
	 * @var DisplayLogicCriteria
	 */
	protected $set = null;



	/**
	 * Constructor
	 * @param string               $master   The name of the master field
	 * @param string               $operator The name of the comparison function
	 * @param string               $value    The value to compare to
	 * @param DisplayLogicCriteria $set      The parent criteria set
	 */
	public function __construct($master, $operator, $value, ValidationLogicCriteria $set) {
		parent::__construct();
		$this->master = $master;
		$this->operator = $operator;
		$this->value = $value;
		$this->set = $set;
	}




	/**
	 * Accessor for the master field
	 * @return string
	 */
	public function getMaster() {
		return $this->master;
	}


	public function phpOperator(){
		$operators = Config::inst()->get('ValidationLogicCriteria', 'comparisons');
		if(isset($operators[$this->operator])){ 
			return $operators[$this->operator];	
		}else{
			return $operators['is' . $this->operator];
		}
	}


	/**
	 * Returns a string of php code to be evaluated
	 * @return string
	 **/
	public function toPHP(){
		$value1 = '$fields->dataFieldByName(\'' . $this->master . '\')->Value()';
		$value2 = $this->value;

		if($operator = $this->phpOperator()){
			return $value1 . " {$operator} \"$value2\"";	
		}
		
		switch ($this->operator) {
			case 'contains':
				return 'strpos("' . $value1 . '"' . ", \"$value2\") !== false";
			
			default:
				return false;
		}
	}




	/**
	 * Creates a JavaScript-readable representation of this criterion
	 * @return string
	 */
	public function toScript() {		
		return "\$(\"#{$this->master}\").evaluate{$this->operator}(\"".addslashes($this->value)."\")";
	}
}