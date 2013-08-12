# SilverStripe ZenValidator


## Description

ZenValidator aims to make silverstripe form validation as painless as possible, by allowing configuration of serverside and clientside validation through a simple API. 
Parsley.js is used for the clientside validation in the frontend.

## Validation Constraints

Out of the box constraints include:

* required
* type (email, url, number, alphanumeric)
* minlength
* maxlength
* rangelength
* min
* max
* range
* regex
* remote (validate remotely via ajax)


## Usage examples
	
### Create a form, add ZenValidator.  

	public function Form(){
		$fields = FieldList::create(array(
			TestField::create('Name'),
			TextField::create('Username'),
			TextField::create('Email'),
			TextField::create('Age'),
			TextField::create('Website')
		));

		$actions = FieldList::create(FormAction::create('submit', 'submit'));

		$validator = ZenValidator::create();
		
		return Form::create($this, 'Form', $fields, $actions, $validator);
	}

The following examples demonstrate various ways to add constraints to the above form example.

### Required fields

The addRequiredFields() is available for quick and clean adding of required fields.

##### Required Fields - Basic

	$validator->addRequiredFields(array(
		'Name',
		'Email'
	));
	
##### Required Fields - Custom Messages

	$validator->addRequiredFields(array(
		'Name' 	=> 'Please enter your name',
		'Email' => 'Please enter your email'
	));

### Other Constraints

All other constraints are added via the setConstraint() method. This method takes 2 parameters: $fieldName and $constraint. See examples below.

###### Value Constraints

Test for number of min, max or between range value
	
Min

	$validator->setConstraint('Age', Constraint_value::create('min', 18));
Max
	
	$validator->setConstraint('Age', Constraint_value::create('max', 25));
Range
	
	$validator->setConstraint('Age', Constraint_value::create('range', 18, 25));
	
###### Length Constraints

Test for a min, max or between range length of string

Min

	$validator->setConstraint('Username', Constraint_length::create('min', 3));
Max
	
	$validator->setConstraint('Username', Constraint_length::create('max', 5));	
Range
	
	$validator->setConstraint('Username', Constraint_length::create('range', 3, 5));
	

###### Type Constraints

The Constraint_type constraint can be used to validate inputs of type email, url, number or alphanum. Pass one of said options as the first parameter into the constructor.

Email
	
	$validator->setConstraint('Email', Constraint_type::create('email'));

URL

	$validator->setConstraint('Website', Constraint_type::create('url'));

###### Regex validation

Check for a valid hex color, for example…

	$validator->setConstraint('FavoriteColor', Constraint_regex::create("/^#(?:[0-9a-fA-F]{3}){1,2}$/"));

	
###### Remote validation

Validate based on the response from a remote url. The following are valid responses from the remote url, with a 200 response code: 1, true, { "success": "..." } and assume false otherwise. You can show a specific specific error message by returning { "error": "your custom message" } or { "message": "your custom message" } 

	$validator->setConstraint('Username', Constraint_remote::create($this->Link('checkusername')));

The above example will send an ajax request to my checkusername method on the same controller as the Form. A request var with a key the same as the field name will contain the value to test. So my checkusername method might look something like this:

	public function checkusername($requst){
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
	
All arguments/settings for the Constraint_remote constructor:

* $url - The URL to send the validation request to (do not include a query string, use $params)
* $params - An array of request vars to be sent with the request
* $method - "POST" or "GET". Defaults to GET
* $jsonp - Boolean, use this for cross domain ajax requests. Defaults to false
	
### Setting Custom Messages

Any of the above examples can be configured to display a custom error message. For example:

	$validator->setConstraint(
		'FavoriteColor', 
		Constraint_regex::create("/^#(?:[0-9a-fA-F]{3}){1,2}$/")->setMessage('Please enter a valid HEX color code, starting with a #')
	);
	
### CMS Usage

To use ZenValidator in the CMS, simply implement a getCMSValidator() method on your custom Page type or DataObject class 

	public function getCMSValidator(){
		return ZenValidator::create()->setConstraint('Content', Constraint_required::create()
			->setMessage('Please enter some content'));
	} 

	


## Requirements

* SilverStripe 3.*



## Maintainers

* shea@silverstripe.com.au


##TODO

* Parsley validation in CMS (currently only serverside)
* Implement Parsley's "Extra validators" - http://parsleyjs.org/documentation.html
* Implement conditional validation ie. only validate constraint if field x value is y
* Add language files