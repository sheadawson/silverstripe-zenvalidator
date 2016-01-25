<?php

class ZenValidatorFormFieldExtension extends Extension
{

    /**
     * @var ValidationLogicCriteria
     **/
    private $validationLogicCriteria;


    public function validateIf($master)
    {
        $this->owner->addExtraClass("validation-logic validation-logic-exclude validation-logic-validate");
        return $this->validationLogicCriteria = ValidationLogicCriteria::create($this->owner, $master);
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

        if ($criteria = $this->validationLogicCriteria) {
            $fields = $this->owner->rootFieldList();
            if (eval($criteria->phpEvalString()) === false) {
                user_error(
                    "There is a syntax error in the constaint logic phpEvalString \"{$criteria->phpEvalString()}\"",
                    E_USER_ERROR
                );
            }
            $return = eval('return ' . $criteria->phpEvalString());
        }

        return $return;
    }


    public function onBeforeRender($field)
    {
        if (!$this->validationLogicCriteria) {
            return;
        }

        $masters = array_unique($this->validationLogicCriteria->getMasterList());

        if (count($masters)) {
            $this->owner->setAttribute('data-validation-logic-masters', implode(',', $masters));
            $this->owner->setAttribute('data-validation-logic-eval', $this->validationLogicCriteria->toScript());
        }
    }
}
