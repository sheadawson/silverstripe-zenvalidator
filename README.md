# SilverStripe ZenValidator

[![Build Status](https://travis-ci.org/sheadawson/silverstripe-zenvalidator.png?branch=master)](https://travis-ci.org/sheadawson/silverstripe-zenvalidator)


## Description

ZenValidator aims to make silverstripe form validation as painless as possible, by allowing configuration of serverside and clientside validation through one simple API.
[Parsley.js](http://parsleyjs.org/doc/index.html) is used for the clientside validation in the frontend.

## Validation Constraints

Out of the box constraints include:

* required
* value (min, max, range)
* length (min, max, range)
* check (min, max, range)
* type (email, url, number, integer, digits, alphanumeric)
* equalto (equal to the value of another field)
* regex
* remote (validate remotely via ajax)
* dimension (image width, height, aspect ratio. CMS only)


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
    'Name'  => 'Please enter your name',
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

##### Check Constraints

Test for a min, max or range of elements checked

Min
```php
$validator->setConstraint('Options', Constraint_check::create('min', 3));
```
Max
```php
$validator->setConstraint('Options', Constraint_check::create('max', 5));
```
Range
```php
$validator->setConstraint('Options', Constraint_check::create('range', 3, 5));
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

Validate based on the response from a remote url. Validation is based on the response http status code. A status of 200 will validate, anything else will cause a validation error.

```php
$validator->setConstraint('Username', Constraint_remote::create($this->Link('checkusername')));
```

The above example will send an ajax request to my checkusername method on the same controller as the Form. A request var with a key the same as the field name will contain the value to test. So my checkusername method might look something like this:

```php
public function checkusername($request) {
    $username = $request->requestVar('Username');

    // check for existing user with same username
    if (Member::get()->filter('Username', $username)->count()) {
        return $this->httpError(400);
    } else {
        return $this->getResponse()->setBody('');
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
    Constraint_regex::create("/^#(?:[0-9a-fA-F]{3}){1,2}$/")
        ->setMessage('Please enter a valid HEX color code, starting with a #')
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
        return $(elem).parents('.field:first');
    },
    excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], :hidden, .ignore-validation'
});
```

Warning: $(elem).parsley() can return an array (in which can, attaching subscribe will fail) in case you match multiple forms. Use the following syntax instead if you need to match multiple elements:

```javascript
$.each($('form.parsley').parsley(),function(i,parsleyForm) {
    parsleyForm.subscribe('parsley:field:validate', function(field) { ... });
});
```

See [Parsley.js](http://parsleyjs.org/doc/index.html) for the full list of configuration settings

### CMS Usage

To use ZenValidator in the CMS, simply implement a getCMSValidator() method on your custom Page type or DataObject class

```php
public function getCMSValidator(){
    return ZenValidator::create()->setConstraint(
        'Content',
        Constraint_required::create()->setMessage('Please enter some content')
    );

    // currently parsley validation doesn't work so well in the cms, so disable.
    $validator->disableParsley();
}
```
#### Image Dimension Constraints (CMS only)

You can add constraints to image selection/upload fields to ensure that users are saving images of the correct size or shape, or that the image is of a minimum/maximum width and height to prevent issues with the display of the image in the site.

Note: the validation is run when the page is saved, not at time of image choosing or upload.

##### Width

Use this to require the width of an image to be a certain number of pixels. Handy if you require images to be an exact size.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('width', 100));
```

##### Height

Use this to require the height of an image is the specified pixels.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('height', 150));
```

##### Width and Height

Use this to require both the width and height are the specified pixels.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('width_height', 100, 150));
```

##### Ratio

Use this to require images to be a certain shape, for example 6:4 photo, 5:5 square etc. Handy when you need an image to be a particular shape but are relaxed about the size as that might be dealt with by CSS.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('ratio', 16, 9));
```

##### Min width

Use this to ensure the width of an image is equal to or greater than the specified pixels, handy to ensure that users don't use images which are too small and might loose quality if stretched by CSS.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('min_width', 50));
```

##### Min height

Use this to ensure that the height of an image is equal to or greater than the specified pixels.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('min_height', 75));
```

##### Min width and height

Use this to ensure that the width and the height of the image are equal to or greater than the specified pixels.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('min_width_height', 50, 75));
```

##### Max width

Use this to ensure that the width of an image is less than or equal to the specified pixels. Handy to ensure that users don't select images far larger than required, especially if these images have a max-width set by CSS as that would result in a lot of wasted bandwidth.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('max_width', 300));
```

##### Max height

Use this to ensure the height of an image is less than or equal to the specified pixels.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('max_height', 200));
```

##### Max width and height

Use this to ensure the width and height is of an image does not exceed the specified pixels.
```php
$validator->setConstraint('HeroImage', Constraint_dimension::create('max_width_height', 300, 200));
```

## Validation Logic - Conditional Constraints

This feature allows you to specify under what conditions a field should or should not have it's validation constraints applied, based on the value(s) of other fields on the form. The concept borrows heavily from and compliments Uncle Cheese's [Display Logic module](https://github.com/unclecheese/silverstripe-display-logic).

### Validation Logic Examples

You can use the following conditional:

- isEqualTo
- isEmpty
- isGreaterThan
- isLessThan
- contains
- isChecked

```php
$country->validateIf('EmailAddress')->isEqualTo('s')->orIf('EmailAddress')->isEqualTo('v');
```

```php
$field->validateIf('IsChecked')->isEmpty();
```

## Extra validators

You can also use extra validators crafted by the community. The module is shipped with the default validators:
- Comparison
- Words
- Date Iso

Javascript and language files are loaded only if you use these validators.


## No validation

In some scenarios, you don't want to trigger validation when submitting the form (i.e.: "previous" button in a multi step form).
Although this is easy to achieve by yourself, the module provides a standard implementation for doing this.

Instead of using the standard FormAction class, you can use its subclass "FormActionNoValidation". It will prevent the client and server side validation from happening.


## Extending

You can create your own validation constraints by subclassing the abstract ZenValidatorConstraint class. For frontend implementation of your custom validator, see [Parsley.extend.js](https://github.com/guillaumepotier/Parsley.js/blob/master/parsley.extend.js). Unfortunately there is no real documentation other than the code itself at this stage so good luck!

For everything else in the frontend (triggers, error classes, error placement, etc) See the [Parsley.js documentation](http://parsleyjs.org/documentation.html#javascript)




## Requirements

* SilverStripe 3.*



## Maintainers

* shea@silverstripe.com.au


##TODO

* Parsley validation in CMS (currently only serverside) (ajax)
* Finish conditional validation ie. only validate constraint if field x value is y, document
