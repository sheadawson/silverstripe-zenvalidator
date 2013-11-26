<?php

class ZenValidatorFormFieldExtension extends Extension{

	public function onBeforeRender($field){		
		$validator = $this->owner->getForm()->getValidator();
		if(!is_a($validator, 'ZenValidator')){
			return;
		}

		$constraints = $validator->getConstraints($this->owner->getName());

		if (!count($constraints)) {
			return;
		}

		$masters = array();

		foreach ($constraints as $constraint) {
			if ($constraintMasters = $constraint->ValidationLogicMasters()) {
				$masters = array_merge($masters, $constraintMasters);		
			}
		}

		if(count($masters)){
			$this->owner->setAttribute('data-validation-logic-masters', implode(',', $masters));
		}
	}

}