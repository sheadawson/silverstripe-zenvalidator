<?php

use SilverStripe\i18n\i18n;
use Psr\Log\LoggerInterface;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\Forms\Validator;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Configurable;

/**
 *
 * @package ZenValidator
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 *
 **/
class ZenValidator extends Validator
{
    use Configurable;

    /**
     * @var boolean
     */
    private static $default_js = true;

    /**
     * constraints assigned to this validator
     *
     * @var array<string,mixed>
     **/
    protected $constraints = array();

    /**
     * @var boolean
     **/
    protected $parsleyEnabled;

    /**
     * @var boolean
     **/
    protected $defaultJS;

    /**
     * @param array<string,mixed> $constraints
     * @param boolean $parsleyEnabled (default: true)
     * @param boolean $defaultJS (default: null)
     **/
    public function __construct($constraints = array(), $parsleyEnabled = true, $defaultJS = null)
    {
        parent::__construct();

        $this->parsleyEnabled = $parsleyEnabled;
        $this->defaultJS = ($defaultJS !== null) ? $defaultJS : $this->config()->get('default_js');

        if (count($constraints)) {
            $this->setConstraints($constraints);
        }
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        parent::setForm($form);

        // a bit of a hack, need a security token to be set on form so that we can iterate over $form->Fields()
        if (!$form->getSecurityToken()) {
            $form->disableSecurityToken();
        }

        // disable parsley in the cms
        if (is_subclass_of($form->getController()->class, LeftAndMain::class)) {
            $this->parsleyEnabled = false;
        }

        // set the field on all constraints
        foreach ($this->constraints as $fieldName => $constraints) {
            // Check field exists before trying to add constraints.
            if ($this->form->Fields()->dataFieldByName($fieldName)) {
                foreach ($constraints as $constraint) {
                    $constraint->setField($this->form->Fields()->dataFieldByName($fieldName));
                }
            }
        }

        // apply parsley
        if ($this->parsleyEnabled) {
            $this->applyParsley();
        }

        return $this;
    }

    /**
     * Helper function, but feel free to include your own
     *
     * @return void
     */
    public static function globalRequirements()
    {
        Requirements::javascript("https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js", ['defer' => true]);
        $avoidEntwine = self::config()->avoid_entwine;
        if (!$avoidEntwine) {
            Requirements::javascript("sheadawson/silverstripe-zenvalidator:javascript/entwine/jquery.entwine-dist.js", ['defer' => true]);
        }
    }

    /**
     * applyParsley
     *
     * @return $this
     **/
    public function applyParsley()
    {
        $this->parsleyEnabled = true;

        $useCurrent = self::config()->use_current;
        $avoidEntwine = self::config()->avoid_entwine;

        // Include your own version of jQuery (>= 1.8) and entwine
        // You can also simply call globalRequirements()
        Requirements::javascript("sheadawson/silverstripe-zenvalidator:javascript/parsley-2.9.1/parsley.min.js", ['defer' => true]);

        $lang = strtolower(substr(i18n::get_locale(), 0, 2));
        if ($lang != 'en') {
            Requirements::javascript("sheadawson/silverstripe-zenvalidator:javascript/parsley-2.9.1/i18n/$lang.js", ['defer' => true]);
        }

        if ($this->form) {
            if ($this->defaultJS) {
                $this->form->addExtraClass('parsley');
                if ($avoidEntwine) {
                    Requirements::javascript("sheadawson/silverstripe-zenvalidator:javascript/zenvalidator_pure.js", ['defer' => true]);
                } else {
                    Requirements::javascript("sheadawson/silverstripe-zenvalidator:javascript/zenvalidator.js", ['defer' => true]);
                }
            } else {
                $this->form->addExtraClass('custom-parsley');
            }

            foreach ($this->constraints as $fieldName => $constraints) {
                foreach ($constraints as $constraint) {
                    try {
                        $constraint->applyParsley();
                    } catch (Exception $ex) {
                        throw new Exception("An exception occured while applying constraint on $fieldName : " . $ex->getMessage());
                    }
                }
            }
        }

        return $this;
    }

    /**
     * disableParsley
     *
     * @return $this
     **/
    public function disableParsley()
    {
        $this->parsleyEnabled = false;
        if ($this->form) {
            $this->form->removeExtraClass('parsley');
            $this->form->removeExtraClass('custom-parsley');
        }
        return $this;
    }

    /**
     * parsleyIsEnabled
     *
     * @return boolean
     **/
    public function parsleyIsEnabled()
    {
        return $this->parsleyEnabled;
    }

    /**
     * setConstraint - sets a ZenValidatorContraint on this validator
     *
     * @param string $fieldName - name of the field to be validated
     * @param ZenValidatorConstraint $constraint
     * @return $this
     **/
    public function setConstraint($fieldName, $constraint)
    {
        $class = get_class($constraint);
        // remove existing constraint if it already exists
        if ($this->getConstraint($fieldName, $class)) {
            $this->removeConstraint($fieldName, $class);
        }

        $this->constraints[$fieldName][$class] = $constraint;

        if ($this->form) {
            $dataField = $this->form->Fields()->dataFieldByName($fieldName);
            $constraint->setField($dataField);
            if ($this->parsleyEnabled) {
                // If there is no field, output a clear error message before trying to apply parsley
                if (!$dataField) {
                    throw new Exception("You have set a constraint on '$fieldName' but it does not exist in the FieldList.");
                }
                $constraint->applyParsley();
            }
        }

        return $this;
    }

    /**
     * setConstraints - sets multiple constraints on this validator
     *
     * @param array<string,ZenValidatorConstraint|array<ZenValidatorConstraint>> $constraints - $fieldName => ZenValidatorConstraint
     * @return $this
     **/
    public function setConstraints($constraints)
    {
        foreach ($constraints as $fieldName => $v) {
            if (is_array($v)) {
                foreach ($v as $constraintFromArray) {
                    $this->setConstraint($fieldName, $constraintFromArray);
                }
            } else {
                $this->setConstraint($fieldName, $v);
            }
        }

        return $this;
    }

    /**
     * get a constraint by fieldName, constraintName
     * @param string $fieldName
     * @param string $constraintName
     * @return ?ZenValidatorConstraint
     **/
    public function getConstraint($fieldName, $constraintName)
    {
        if (isset($this->constraints[$fieldName][$constraintName])) {
            return $this->constraints[$fieldName][$constraintName];
        }
        return null;
    }

    /**
     * get constraints by fieldName
     *
     * @param string $fieldName
     * @return array<string,ZenValidatorConstraint>|null
     **/
    public function getConstraints($fieldName)
    {
        if (isset($this->constraints[$fieldName])) {
            return $this->constraints[$fieldName];
        }
        return null;
    }

    /**
     * remove a constraint from a field
     *
     * @param string $fieldName - name of the field to have a constraint removed from
     * @param string $constraintName - class name of constraint
     * @return $this
     **/
    public function removeConstraint($fieldName, $constraintName)
    {
        if ($constraint = $this->getConstraint($fieldName, $constraintName)) {
            if ($this->form) {
                $constraint->removeParsley();
            }
            $class = get_class($constraint);
            unset($this->constraints[$fieldName][$class]);
            unset($constraint);
        }

        return $this;
    }

    /**
     * remove all constraints from a field
     *
     * @param string $fieldName - name of the field to have constraints removed from
     * @return $this
     **/
    public function removeConstraints($fieldName)
    {
        if ($constraints = $this->getConstraints($fieldName)) {
            foreach ($constraints as $k => $v) {
                $this->removeConstraint($fieldName, $k);
            }
            unset($this->constraints[$fieldName]);
        }

        return $this;
    }

    /**
     * A quick way of adding required constraints to a number of fields
     *
     * @param array<string|int,string|null> $fields - can be either indexed array of fieldnames, or associative array of fieldname => message
     * @param array<string|int,string|null> $otherFields
     * @return $this
     */
    public function addRequiredFields($fields, ...$otherFields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        if (!empty($otherFields)) {
            $fields = array_merge($fields, $otherFields);
        }
        if (ArrayLib::is_associative($fields)) {
            foreach ($fields as $k => $v) {
                $constraint = Constraint_required::create();
                if ($v) {
                    $constraint->setMessage($v);
                }
                $this->setConstraint($k, $constraint);
            }
        } else {
            foreach ($fields as $field) {
                $this->setConstraint($field, Constraint_required::create());
            }
        }

        return $this;
    }

    /**
     * @param string $message
     * @return void
     */
    protected function debug($message)
    {
        Injector::inst()->get(LoggerInterface::class)->debug($message);
    }

    /**
     * Performs the php validation on all ZenValidatorConstraints attached to this validator
     *
     * @return boolean
     **/
    public function php($data)
    {
        $valid = true;

        // If we want to ignore validation
        $clicked = $this->form->getRequestHandler()->buttonClicked();
        if ($clicked && $clicked instanceof FormActionNoValidation) {
            return $valid;
        }

        // validate against form field validators
        $fields = $this->form->Fields();
        foreach ($fields as $field) {
            $valid = ($field->validate($this) && $valid);
        }

        // validate against ZenValidator constraints
        foreach ($this->constraints as $fieldName => $constraints) {
            $field = $this->form->Fields()->dataFieldByName($fieldName);

            if (!$field) {
                continue;
            }

            if ($field->validationApplies()) {
                foreach ($constraints as $constraint) {
                    if (!$constraint->validate($data[$fieldName])) {
                        $this->validationError($fieldName, $constraint->getMessage(), 'required');
                        $valid = false;
                        $this->debug("Validation (" . get_class($constraint) . ") failed for $fieldName with message: " . $constraint->getMessage());
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * This method is not imeplemented because form->transform calls it, but not all FormTransformations
     * necessarily want to remove validation... right?
     * Use removeAllValidation() instead.
     **/
    public function removeValidation()
    {
        //
    }

    /**
     * Removes all constraints from this validator.
     **/
    public function removeAllValidation()
    {
        if ($this->form) {
            foreach ($this->constraints as $fieldName => $constraints) {
                foreach ($constraints as $constraint) {
                    $constraint->removeParsley();
                    unset($constraint);
                }
            }
        }
        $this->constraints = array();
    }

    /**
     * Returns whether the field in question is required. This will usually display '*' next to the
     * field.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function fieldIsRequired($fieldName)
    {
        $required = false;

        $constraints = $this->getConstraints($fieldName);
        if ($constraints) {
            foreach ($constraints as $constraint) {
                if ($constraint instanceof Constraint_required) {
                    $required = true;
                    break;
                }
            }
        }

        return $required || parent::fieldIsRequired($fieldName);
    }
}
