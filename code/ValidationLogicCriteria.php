<?php
/**
 * @package ZenValidator
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 * Credit to Uncle Cheese for the recipe
 */
class ValidationLogicCriteria extends Object
{
    /**
     * The name of the form field that depends on the criteria
     *
     * @var string
     */
    protected $master = null;

    /**
     * The form field that responds to the state of {@link $master}
     *
     * @var FormField
     */
    protected $slave = null;

    /**
     * A parent {@link ValidationLogicCriteria}, for grouping
     *
     * @var ValidationLogicCriteria
     */
    protected $parent = null;

    /**
     * A list of {@link ValidationLogicCriterion} objects
     *
     * @var array
     */
    protected $criteria = array();

    /**
     * Either "and" or "or", determines disjunctive or conjunctive logic for the whole criteria set
     * @var string
     */
    protected $logicalOperator = null;

    /**
     * @var array
     */
    private static $comparisons;

    /**
     * Constructor
     *
     * @param FormField $slave  The form field that responds to changes of another form field
     * @param [type]    $master The name of the form field to respond to
     * @param [type]    $parent The parent {@link ValidationLogicCriteria}
     */
    public function __construct(FormField $slave, $master, $parent = null)
    {
        parent::__construct();
        $this->slave = $slave;
        $this->master = $master;
        $this->parent = $parent;
        return $this;
    }

    /**
     * Wildcard method for applying all the possible conditions
     *
     * @param sting $method The method name
     * @param array $args The arguments
     * @return ValidationLogicCriteria
     */
    public function __call($method, $args)
    {
        if (in_array($method, array_keys($this->config()->comparisons))) {
            $val = isset($args[0]) ? $args[0] : null;
            if (substr($method, 0, 2) == "is") {
                $operator = substr($method, 2);
            } else {
                $operator = $method;
//				$operator = ucwords($method);
            }

            $this->addCriterion(ValidationLogicCriterion::create($this->master, $operator, $val, $this));
            return $this;
        }
        return parent::__call($method, $args);
    }

    /**
     * Adds a {@link ValidationLogicCriterion} for a range of values
     *
     * @param int  $min The minimum value
     * @param int  $max The maxiumum value
     * @return ValidationLogicCriteria
     */
    // public function isBetween($min, $max) {
    // 	$this->addCriterion(ValidationLogicCriterion::create($this->master, "Between", "{$min}-{$max}", $this));
    // 	return $this;
    // }

    /**
     * Adds a new criterion, and makes this set use conjuctive logic
     *
     * @param string $master The master form field
     * @return ValidationLogicCriteria
     */
    public function andIf($master = null)
    {
        if ($this->logicalOperator == "or") {
            user_error("ValidationLogicCriteria: Cannot declare a logical operator more than once. (Specified andIf() after calling orIf()). Use a nested ValidationLogicCriteriaSet to combine conjunctive and disjuctive logic.", E_USER_ERROR);
        }
        if ($master) {
            $this->master = $master;
        }
        $this->logicalOperator = "and";
        return $this;
    }

    /**
     * Adds a new criterion, and makes this set use disjunctive logic
     *
     * @param string $master The master form field
     * @return ValidationLogicCriteria
     */
    public function orIf($master = null)
    {
        if ($this->logicalOperator == "and") {
            user_error("ValidationLogicCriteria: Cannot declare a logical operator more than once. (Specified orIf() after calling andIf()). Use a nested ValidationLogicCriteriaSet to combine conjunctive and disjuctive logic.", E_USER_ERROR);
        }
        if ($master) {
            $this->master = $master;
        }
        $this->logicalOperator = "or";
        return $this;
    }

    /**
     * Adds a new criterion
     *
     * @param ValidationLogicCriterion $c
     */
    public function addCriterion(ValidationLogicCriterion $c)
    {
        $this->criteria[] = $c;
    }

    /**
     * Gets all the criteria
     *
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Gets a Javascript symbol for the logical operator
     *
     * @return string
     */
    public function getLogicalOperator()
    {
        return $this->logicalOperator == "or" ? "||" : "&&";
    }

    /**
     * Creates a nested {@link ValidationLogicCriteria}
     *
     * @return ValidationLogicCriteria
     */
    public function group()
    {
        return ValidationLogicCriteria::create($this->slave, $this->master, $this);
    }

    /**
     * Ends the chaining and returns the parent object, either {@link ValidationLogicCriteria} or {@link FormField}
     *
     * @return FormField/ValidationLogicCriteria
     */
    public function end()
    {
        if ($this->parent) {
            $this->parent->addCriterion($this);
        }
        return $this->slave;
    }

    /**
     * @return string
     */
    public function phpEvalString()
    {
        $string = "(";
        $first = true;
        foreach ($this->getCriteria() as $c) {
            $string .= $first ? "" :  " {$this->getLogicalOperator()} ";
            $string .= $c->toPHP();
            $first = false;
        }
        $string .= ");";
        return $string;
    }

    /**
     * Creates a JavaScript readable representation of the logic
     *
     * @return string
     */
    public function toScript()
    {
        $script = "(";
        $first = true;
        foreach ($this->getCriteria() as $c) {
            $script .= $first ? "" :  " {$this->getLogicalOperator()} ";
            $script .= $c->toScript();
            $first = false;
        }
        $script .= ")";
        return $script;
    }

    /**
     * Gets a list of all the master fields in this criteria set
     *
     * @return string
     */
    public function getMasterList()
    {
        $list = array();
        foreach ($this->getCriteria() as $c) {
            if ($c instanceof ValidationLogicCriteria) {
                $list += $c->getMasterList();
            } else {
                $list[] = $c->getMaster();
            }
        }
        return $list;
    }
}
