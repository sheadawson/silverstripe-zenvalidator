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

		// note that the validator must be set on the $form after it has been created. You can not pass a ZenValidator
		// into the Form constructor
		$form->setValidator($validator = ZenValidator::create());


		// alternatively add field validation via the validator.  
		$validator->setConstraint('MinLength', Constraint_minlength::create(3));

		// addRequiredFields is a quick way to add bulk required fields
		// if you don't need to set custom messages, just pass in an indexed array of fieldnames rather than associative
		$validator->addRequiredFields(array(
			'Required' => 'Custom Required message',
			'MinLength' => 'Custom MinLength message'
		));

		return $form;
	}

## Validation constraints implemented (so far)

* required
* minlength
* maxlength
* rangelength
* min
* max
* range
* regex
* remote (validate remotely via ajax)
* type (email, url, number, alphanumeric)

##TODO

* Enable for CMS
* How much of the js configuration do we allow in the php code? triggers? containers?
* Quick add required fields ->addRequired(array())
* Implement Parsley's "Extra validators" - http://parsleyjs.org/documentation.html
* Implement conditional validation ie. only validate constraint if field x value is y
* Usage examples / docs