<?php

use SilverStripe\Core\Extension;

class ZenValidatorFormFieldExtension extends Extension
{
    /**
     * @param string $master The name of the form field to respond to
     * @return ValidationLogicCriteria
     */
    public function validateIf($master)
    {
        return $this->owner->validationLogicCriteria = ValidationLogicCriteria::create($this->owner, $master);
    }

    /**
     * Checks to see if any ValidationLogicCriteria has been set and if so,
     * should the validation constraints still be applied
     *
     * @return bool
     **/
    public function validationApplies()
    {
        $return = true;

        /* @var $criteria ValidationLogicCriteria */
        if ($criteria = $this->owner->validationLogicCriteria) {
            $fields = $this->owner->rootFieldList();
            if (eval($criteria->phpEvalString()) === false) {
                throw new Exception("There is a syntax error in the constaint logic phpEvalString \"{$criteria->phpEvalString()}\"");
            }
            $return = eval('return ' . $criteria->phpEvalString());
        }

        return $return;
    }
}
