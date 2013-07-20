# SilverStripe ZenValidator

## Requirements

* SilverStripe 3.*

## Maintainers

* shea@silverstripe.com.au

## Description

ZenValidator aims to make silverstripe form validation as painless as possible, by allowing configuration of serverside and clientside validation through a simple API. 

Parsley.js is used for the clientside validation.

## Quick example

	public function Form(){
		$fields = FieldList::create(array(
			DropdownField::create('Required', null, array('' => '', 'Option2' => 'Option2'))
				->setConstraint(Constraint_required::create()->setMessage('This is a custom message on a required field')),
			TextField::create('MinLength')
				->setConstraint(Constraint_minlength::create(3)),
		));

		$actions = FieldList::create(
			FormAction::create('submit', 'submit')
		);

		$form = Form::create($this, 'Form', $fields, $actions);

		$form->setValidator(ZenValidator::create());

		return $form;
	}

##TODO

* Will / how will this work in the cms?
* How much of the js configuration do we allow in the php code
* Quick add required fields ->addRequired(array())
* Implement all standard parsley validation constraints
* Implement conditional validation ie. only validate constraint if field x value is y
* Usage examples / docs