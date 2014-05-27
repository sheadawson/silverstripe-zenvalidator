# SilverStripe ZenValidator

[![Build Status](https://travis-ci.org/sheadawson/silverstripe-zenvalidator.png?branch=master)](https://travis-ci.org/sheadawson/silverstripe-zenvalidator) 


## Description

ZenValidator aims to make silverstripe form validation as painless as possible, by allowing configuration of serverside and clientside validation through a comprehensive yet simple API. 
[Parsley.js](http://parsleyjs.org/documentation.html) is used for the clientside validation in the frontend.

## Validation Constraints

Out of the box constraints include:

* required
* value (min, max, range)
* length (min, max, range)
* type (email, url, number, integer, digits, alphanumeric)
* equalto (equal to the value of another field)
* regex
* remote (validate remotely via ajax)


## Usage examples
	
### Create a form, add ZenValidator.  

```php
public function Form(){
	$fields = FieldList::create(array(
		TestField::create('Name'),
		TextField::create('Username'),
		TextField::create('Email'),
		TextField::create('FavoriteColor'),
		TextField::create('Age'),
		TextField::create('Website')
	));

	$actions = FieldList::create(FormAction::create('submit', 'submit'));

	$validator = ZenValidator::create();
	
	return Form::create($this, 'Form', $fields, $actions, $validator);
}
```

The following examples demonstrate various ways to add constraints to the above form example.

### Required fields

The addRequiredFields() is available for quick and clean adding of required fields.

##### Required Fields - Basic

```php
$validator->addRequiredFields(array(
	'Name',
	'Email'
));
```
	
##### Required Fields - Custom Messages

```php
	$validator->addRequiredFields(array(
		'Name' 	=> 'Please enter your name',
		'Email' => 'Please enter your email'
	));
```

### Other Constraints

All other constraints are added via the setConstraint() method. This method takes 2 parameters: $fieldName and $constraint. See examples below.

##### Value Constraints

Test for number of min, max or between range value
	
Min
```php	
$validator->setConstraint('Age', Constraint_value::create('min', 18));
```
Max
```php	
$validator->setConstraint('Age', Constraint_value::create('max', 25));
```
Range
```php
$validator->setConstraint('Age', Constraint_value::create('range', 18, 25));
```
	
##### Length Constraints

Test for a min, max or between range length of string

Min
```php
$validator->setConstraint('Username', Constraint_length::create('min', 3));
```
Max
```php
$validator->setConstraint('Username', Constraint_length::create('max', 5));	
```
Range
```php
$validator->setConstraint('Username', Constraint_length::create('range', 3, 5));
```
	

##### Type Constraints

The Constraint_type constraint can be used to validate inputs of type email, url, number, integer, digits or alphanum. Pass one of said options as the first parameter into the constructor.

Email
```php
$validator->setConstraint('Email', Constraint_type::create('email'));
```

URL
```php
$validator->setConstraint('Website', Constraint_type::create('url'));
```
	
Number
```php
$validator->setConstraint('Age', Constraint_type::create('number'));
```

Integer
```php
$validator->setConstraint('Age', Constraint_type::create('integer'));
```

Digits
```php
$validator->setConstraint('Age', Constraint_type::create('digits'));
```
	
Alphanum
```php
$validator->setConstraint('Username', Constraint_type::create('alphanum'));
```


##### Equal To Constraint

Check for a value equal to that of another field

```php
$validator->setConstraint('Username', Constraint_equalto::create('Name'));
```

##### Regex validation

Check for a valid hex color, for example…

```php
$validator->setConstraint('FavoriteColor', Constraint_regex::create("/^#(?:[0-9a-fA-F]{3}){1,2}$/"));
```
	
##### Remote validation

Validate based on the response from a remote url. The following are valid responses from the remote url, with a 200 response code: 1, true, { "success": "..." } and assume false otherwise. You can show a specific specific error message by returning { "error": "your custom message" } or { "message": "your custom message" } 

```php
$validator->setConstraint('Username', Constraint_remote::create($this->Link('checkusername')));
```

The above example will send an ajax request to my checkusername method on the same controller as the Form. A request var with a key the same as the field name will contain the value to test. So my checkusername method might look something like this:

```php
public function checkusername($request){
	$username = $request->requestVar('Username');
	
	// check for existing user with same username
	if(Member::get()->filter('Username', $username)->count()){
		return Convert::array2json(array(
			'error' => 'Sorry, that username is already taken.'
		));	
	}else{
		return true;
	}	
}
```
	
All arguments/settings for the Constraint_remote constructor:

* $url - The URL to send the validation request to (do not include a query string, use $params)
* $params - An array of request vars to be sent with the request
* $options - An array of key => value options for the validator (eg: ['type' : 'POST', 'dataType' : 'jsonp'])
* $validator - Use a specific remote validator. Default validators are 'default' and 'reverse'

For serverside validation: if a relative url is given the response will be obtained internally using Director::test, otherwise curl will be used to get the response from the remote url.
	
### Setting Custom Messages

Any of the above examples can be configured to display a custom error message. For example:

```php
$validator->setConstraint(
	'FavoriteColor', 
	Constraint_regex::create("/^#(?:[0-9a-fA-F]{3}){1,2}$/")->setMessage('Please enter a valid HEX color code, starting with a #')
);
```
	
### Bulk setting of constraints

```php
$validator->setConstraints(array(
	'Name' => 'Age', Constraint_value::create('min', 18),
	'Username' => array(
		Constraint_required::create(),
		Constraint_type::create('alphanum'),
	)
));
```	

The setConstraint() method is also chainable so you can:

```php
$validator
	->setConstraint('Website', Constraint_type::create('url'))
	->setConstraint('Content', Constraint_required::create());
```

### Removing constaints

```php
$validator->removeConstraint(string $fieldName, string $constraintClassname);
```	
OR
```php
$validator->removeConstraints(string $fieldName);
```

### Customising frontend validation behaviour (Parsley)

It is likely that you may want to have your validation messages displaying in a custom element, with custom classes or any other custom frontend validation behaviour that is configurable with Parsley. In this case, you can set the third parameter ($defaultJS) of Zenvalidator's __construct to false.
	
```php
$validator = ZenValidator::create(null, true, false);
```

Or set globally via yml

```
ZenValidator:
  default_js: false
```

This will tell ZenValidator not to initialise with the default settings, and also to add the class "custom-parsley" to the form. You'll then need to add some custom javascript to the page with your own settings, for example:

```javascript
$('form.custom-parsley').parsley({
	errorsWrapper: '<div></div>',
	errorTemplate: '<small class="error"></small>',
	errorClass: 'error',
	errorsContainer: function ( elem, isRadioOrCheckbox ) {
    	return elem.parents('.field:first');
    },	
}).subscribe('parsley:field:validate', function(field) {
	var $el = field.$element;
	if ( el.hasClass('ignore-validation') || !el.is( ':visible' ) ) {
	    return true;
	}
	return false;
});
```

See [Parsley.js](http://parsleyjs.org/doc/index.html) for the full list of configuration settings
	
	
### CMS Usage

To use ZenValidator in the CMS, simply implement a getCMSValidator() method on your custom Page type or DataObject class 

```php
public function getCMSValidator(){
	return ZenValidator::create()->setConstraint('Content', Constraint_required::create()
		->setMessage('Please enter some content'));
} 
```


## Validation Logic - Conditional Constraints

This feature allows you to specify under what conditions a field should or should not have it's validation constraints applied, based on the value(s) of other fields on the form. The concept borrows heavily from and compliments Uncle Cheese's [Display Logic module](https://github.com/unclecheese/silverstripe-display-logic).

Caution: The Conditional Constraint feature relies on a line of code in the SilverStripe Framework that has not yet been released. If you'd like to use this feature before the next release of Framework, you can [patch FormField.php yourself](https://github.com/silverstripe/silverstripe-framework/commit/eaa390b1de9f7c4f895dfe56eaeb4db9364f6c31)

### TODO - Validation Logic Examples

```php
$country->validateIf('EmailAddress')->isEqualTo('s');
```
	
## Extending

You can create your own validation constraints by subclassing the abstract ZenValidatorConstraint class. For frontend implementation of your custom validator, see [Parsley.extend.js](https://github.com/guillaumepotier/Parsley.js/blob/master/parsley.extend.js). Unfortunately there is no real documentation other than the code itself at this stage so good luck!

For everything else in the frontend (triggers, error classes, error placement, etc) See the [Parsley.js documentation](http://parsleyjs.org/documentation.html#javascript) 

	


## Requirements

* SilverStripe 3.*



## Maintainers

* shea@silverstripe.com.au


##TODO

* Parsley validation in CMS (currently only serverside) (ajax)
* Implement Parsley's "Extra validators" - http://parsleyjs.org/documentation.html
* Finish conditional validation ie. only validate constraint if field x value is y, document
* Add language files for extra validators
