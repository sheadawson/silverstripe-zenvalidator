# SilverStripe ZenValidator

## Requirements

* SilverStripe 3.*

## Maintainers

* shea@silverstripe.com.au

## Description

ZenValidator aims to make silverstripe form validation as painless as possible, by allowing configuration of serverside and clientside validation through a simple API. Validation in the cms is via ajax as usual.

Parsley.js is used for the clientside validation.

## Quick example
	
... comming soon!

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

* How much of the js configuration do we allow in the php code? triggers? containers?
* Implement Parsley's "Extra validators" - http://parsleyjs.org/documentation.html
* Implement conditional validation ie. only validate constraint if field x value is y
* Usage examples / docs