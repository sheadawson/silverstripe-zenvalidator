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
			DropdownField::create('Required', null, array('' => '', 'Option1' => 'Option1')),
			TextField::create('MinLength'),
			TextField::create('MaxLength'),
			TextField::create('RangeLength'),
			TextField::create('Min'),
			TextField::create('Max'),
			TextField::create('Range'),
			TextField::create('Email'),
		));

		$actions = FieldList::create(
			FormAction::create('submit', 'submit')
		);
		
		$validator = ZenValidator::create($fields)
			->addConstraint('Required', Constraint_required::create()->setMessage('This is a custom message on a required field'))
			->addConstraint('MinLength', Constraint_minlength::create(5))
			->addConstraint('MaxLength', Constraint_maxlength::create(5))
			->addConstraint('RangeLength', Constraint_rangelength::create(5, 10))
			->addConstraint('Min', Constraint_min::create(5))
			->addConstraint('Max', Constraint_max::create(5))
			->addConstraint('Range', Constraint_range::create(5, 10))
			->addConstraint('Email', Constraint_email::create());

		return Form::create($this, 'Form', $fields, $actions, $validator);
	}

##TODO

* Will / how will this work in the cms?
* How much of the js configuration do we allow in the php code
* Implement all standard parsley validation constraints
* Usage examples / docs