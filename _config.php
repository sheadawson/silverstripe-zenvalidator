<?php

if(!defined('ZENVALIDATOR_PATH')) define('ZENVALIDATOR_PATH', rtrim(basename(dirname(__FILE__))));

// Ensure compatibility with PHP 7.2 ("object" is a reserved word),
// with SilverStripe 3.6 (using Object) and SilverStripe 3.7 (using SS_Object)
if (!class_exists('SS_Object')) class_alias('Object', 'SS_Object');
