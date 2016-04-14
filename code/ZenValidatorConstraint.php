<?php

/**
 * @package ZenValidator
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 * */
abstract class ZenValidatorConstraint extends Object
{

    /**
     * @var FormField
     * */
    protected $field;

    /**
     * @var string
     * */
    protected $customMessage;

    /**
     * @var boolean
     * */
    protected $parsleyApplied;

    /**
     * Set the field this constraint is applied to
     * @param FormField $field
     * @return this
     * */
    public function setField(FormField $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return FormField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set a custom message for this constraint
     * @param string $message
     * @return this
     * */
    public function setMessage($message)
    {
        $this->customMessage = $message;
        return $this;
    }

    /**
     * Get's the message that was set on the constrctor or falls back to default
     * @return string
     * */
    public function getMessage()
    {
        return $this->customMessage ? $this->customMessage : $this->getDefaultMessage();
    }

    /**
     * Load extra validator
     * @param string $name
     * */
    public function loadExtra($name)
    {
        Requirements::javascript(ZENVALIDATOR_PATH . '/javascript/parsley/extra/validator/' . $name . '.js');

        $lang = i18n::get_lang_from_locale(i18n::get_locale());
        Requirements::javascript(ZENVALIDATOR_PATH . '/javascript/parsley/i18n/' . $lang . '.extra.js');
    }

    /**
     * Return the default message for this constraint
     * @return string
     * */
    abstract public function getDefaultMessage();

    /**
     * Sets the html attributes required for frontend validation
     * Subclasses should call parent::applyParsley
     * @return void
     * */
    public function applyParsley()
    {
        if (!$this->field) {
            user_error("A constrained Field does not exist on the FieldSet, check you have the right field name for your ZenValidatorConstraint.", E_USER_ERROR);
        }
        $this->parsleyApplied = true;
        if ($this->customMessage) {
            $this->field->setAttribute(sprintf('data-parsley-%s-message', $this->getConstraintName()), $this->customMessage);
        }
        // CheckboxSetField might not have a unique name, so set parsley-multiple attribute
        if (get_class($this->field) === 'CheckboxSetField') {
            $this->field->setAttribute('data-parsley-multiple', $this->field->getName());
        }
    }

    /**
     * Removes the html attributes required for frontend validation
     * Subclasses should call parent::removeParsley
     * @return void
     * */
    public function removeParsley()
    {
        $this->parsleyApplied = false;
        if ($this->field && $this->customMessage) {
            $this->field->setAttribute(sprintf('data-parsley-%s-message', $this->getConstraintName()), '');
        }
        if (get_class($this->field) === 'CheckboxSetField') {
            $this->field->setAttribute('data-parsley-multiple', '');
        }
    }

    /**
     * Performs php validation on the value
     * @param $value
     * @return bool
     * */
    abstract public function validate($value);

    /**
     * Gets the name of this constraint from it's classname which should correspond
     * to the string that parsley uses to identify a constraint type
     * @return string
     * */
    public function getConstraintName()
    {
        return str_replace('Constraint_', '', $this->class);
    }
}

/**
 * Constraint_required
 * Basic required field form validation
 * */
class Constraint_required extends ZenValidatorConstraint
{

    public function applyParsley()
    {
        parent::applyParsley();
        $this->field->setAttribute('data-parsley-required', 'true');
        $this->field->addExtraClass('required');
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-required', 'false');
        $this->field->removeExtraClass('required');
    }

    public function validate($value)
    {
        return $value != '';
    }

    public function getDefaultMessage()
    {
        return _t('ZenValidator.REQUIRED', 'This field is required');
    }
}

/**
 * Constraint_length
 * Constrain a field value to be a of a min length, max length or between a range
 *
 * @example Constraint_length::create('min', 5); // minimum length of 5 characters
 * @example Constraint_length::create('max', 5); // maximum length of 5 characters
 * @example Constraint_length::create('range', 5, 10); // length between 5 and 10 characters
 * */
class Constraint_length extends ZenValidatorConstraint
{

    const MIN = 'min';
    const MAX = 'max';
    const RANGE = 'range';

    /**
     * @var string
     * */
    protected $type;

    /**
     * @var int
     * */
    protected $val1, $val2;

    /**
     * @param string $type (min,max,range)
     * @param int $val1
     * @param int $val2
     * */
    public function __construct($type, $val1, $val2 = null)
    {
        $this->type = $type;
        $this->val1 = (int) $val1;
        $this->val2 = (int) $val2;
        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        switch ($this->type) {
            case 'min':
                $this->field->setAttribute('data-parsley-minlength', $this->val1);
                break;
            case 'max':
                $this->field->setAttribute('data-parsley-maxlength', $this->val1);
                break;
            case 'range':
                $this->field->setAttribute('data-parsley-length', sprintf("[%s,%s]", $this->val1, $this->val2));
                break;
        }
    }

    public function getConstraintName()
    {
        return $this->type;
    }

    public function removeParsley()
    {
        parent::removeParsley();
        switch ($this->type) {
            case 'min':
                $this->field->setAttribute('data-parsley-minlength', '');
                break;
            case 'max':
                $this->field->setAttribute('data-parsley-maxlength', '');
                break;
            case 'range':
                $this->field->setAttribute('data-parsley-length', '');
                break;
        }
    }

    public function validate($value)
    {
        if (!$value) {
            return true;
        }

        switch ($this->type) {
            case 'min':
                return strlen(trim($value)) >= $this->val1;
            case 'max':
                return strlen(trim($value)) <= $this->val1;
            case 'range':
                return strlen(trim($value)) >= $this->val1 && strlen(trim($value)) <= $this->val2;
        }
    }

    public function getDefaultMessage()
    {
        switch ($this->type) {
            case 'min':
                return sprintf(_t('ZenValidator.MINLENGTH', 'This value is too short. It should have %s characters or more'), $this->val1);
            case 'max':
                return sprintf(_t('ZenValidator.MAXLENGTH', 'This value is too long. It should have %s characters or less'), $this->val1);
            case 'range':
                return sprintf(_t('ZenValidator.RANGELENGTH', 'This value length is invalid. It should be between %s and %s characters long'), $this->val1, $this->val2);
        }
    }
}

/**
 * Constraint_check
 * Constrain checkbox set field to have a minimum or maximum number of elements checked
 *
 * @example Constraint_length::create('min', 5); // minimum of 5 elements checked
 * @example Constraint_length::create('max', 5); // maximum of 5 elements checked
 * @example Constraint_length::create('range', 5, 10); // between 5 and 10 elements checked
 * */
class Constraint_check extends ZenValidatorConstraint
{

    const MIN = 'min';
    const MAX = 'max';
    const RANGE = 'range';

    /**
     * @var string
     * */
    protected $type;

    /**
     * @var int
     * */
    protected $val1, $val2;

    /**
     * @param string $type (min,max,check)
     * @param int $val1
     * @param int $val2
     * */
    public function __construct($type, $val1, $val2 = null)
    {
        $this->type = $type;
        $this->val1 = (int) $val1;
        $this->val2 = (int) $val2;
        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        switch ($this->type) {
            case 'min':
                $this->field->setAttribute('data-parsley-mincheck', $this->val1);
                break;
            case 'max':
                $this->field->setAttribute('data-parsley-maxcheck', $this->val1);
                break;
            case 'range':
                $this->field->setAttribute('data-parsley-check', sprintf("[%s,%s]", $this->val1, $this->val2));
                break;
        }
    }

    public function getConstraintName()
    {
        return $this->type;
    }

    public function removeParsley()
    {
        parent::removeParsley();
        switch ($this->type) {
            case 'min':
                $this->field->setAttribute('data-parsley-mincheck', '');
                break;
            case 'max':
                $this->field->setAttribute('data-parsley-maxcheck', '');
                break;
            case 'range':
                $this->field->setAttribute('data-parsley-check', '');
                break;
        }
    }

    public function validate($value)
    {
        $array = array_filter(explode(',', $value));
        if (empty($array)) {
            return; //you should use required instead
        }

        switch ($this->type) {
            case 'min':
                return count($array) >= $this->val1;
            case 'max':
                return count($array) <= $this->val1;
            case 'range':
                return count($array) >= $this->val1 && count($array) <= $this->val2;
        }
    }

    public function getDefaultMessage()
    {
        switch ($this->type) {
            case 'min':
                return sprintf(_t('ZenValidator.MINCHECK', 'You must select at least %s choices'), $this->val1);
            case 'max':
                return sprintf(_t('ZenValidator.MAXCHECK', 'You must select %s choices or fewer'), $this->val1);
            case 'range':
                return sprintf(_t('ZenValidator.RANGECHECK', 'You must select between %s and %s choices'), $this->val1, $this->val2);
        }
    }
}

/**
 * Constraint_value
 * Constrain a field value to be a of a min value, max value or between a range
 *
 * @example Constraint_value::create('min', 5); // minimum value of 5
 * @example Constraint_value::create('max', 5); // maximum value of 5
 * @example Constraint_value::create('range', 5, 10); // value between 5 and 10 characters
 * */
class Constraint_value extends ZenValidatorConstraint
{

    const MIN = 'min';
    const MAX = 'max';
    const RANGE = 'range';

    /**
     * @var string
     * */
    protected $type;

    /**
     * @var int
     * */
    protected $val1, $val2;

    /**
     * @param srting $type (min,max,range)
     * @param int $val1
     * @param int $val2
     * */
    public function __construct($type, $val1, $val2 = null)
    {
        $this->type = $type;
        $this->val1 = (int) $val1;
        $this->val2 = (int) $val2;
        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        switch ($this->type) {
            case 'min':
                $this->field->setAttribute('data-parsley-min', $this->val1);
                break;
            case 'max':
                $this->field->setAttribute('data-parsley-max', $this->val1);
                break;
            case 'range':
                $this->field->setAttribute('data-parsley-range', sprintf("[%s,%s]", $this->val1, $this->val2));
                break;
        }
    }

    public function getConstraintName()
    {
        return $this->type;
    }

    public function removeParsley()
    {
        parent::removeParsley();
        switch ($this->type) {
            case 'min':
                $this->field->setAttribute('data-parsley-min', '');
                break;
            case 'max':
                $this->field->setAttribute('data-parsley-max', '');
                break;
            case 'range':
                $this->field->setAttribute('data-parsley-range', '');
                break;
        }
    }

    public function validate($value)
    {
        if (!is_numeric($value)) {
            return true;
        }

        switch ($this->type) {
            case 'min':
                return (int) $value >= $this->val1;
            case 'max':
                return (int) $value <= $this->val1;
            case 'range':
                return (int) $value >= $this->val1 && (int) $value <= $this->val2;
        }
    }

    public function getDefaultMessage()
    {
        switch ($this->type) {
            case 'min':
                return sprintf(_t('ZenValidator.MIN', 'This value should be greater than or equal to %s'), $this->val1);
            case 'max':
                return sprintf(_t('ZenValidator.MAX', 'This value should be less than or equal to %s'), $this->val1);
            case 'range':
                return sprintf(_t('ZenValidator.RANGE', 'This value should be between %s and %s'), $this->val1, $this->val2);
        }
    }
}

/**
 * Constraint_regex
 * Constrain a field to match a regular expression
 *
 * @example Constraint_regex::create("/^#(?:[0-9a-fA-F]{3}){1,2}$/"); // value must be a valid hex color
 * */
class Constraint_regex extends ZenValidatorConstraint
{

    /**
     * @var string
     * */
    protected $regex;

    /**
     * @param string $regex
     * */
    public function __construct($regex)
    {
        $this->regex = $regex;
        parent::__construct();
    }

    public function getConstraintName()
    {
        return 'pattern';
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $this->field->setAttribute('data-parsley-pattern', trim($this->regex, '/'));
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-pattern', '');
    }

    public function validate($value)
    {
        if (!$value) {
            return true;
        }
        return preg_match($this->regex, $value);
    }

    public function getDefaultMessage()
    {
        return _t('ZenValidator.REGEXP', 'This value seems to be invalid');
    }
}

/**
 * Constraint_remote
 * Validate a field remotely via ajax
 *
 * See readme for example
 * */
class Constraint_remote extends ZenValidatorConstraint
{

    /**
     * @var string
     * */
    protected $url;

    /**
     * @var array
     * */
    protected $params;

    /**
     * @var array
     * */
    protected $options;

    /**
     * @var string
     * */
    protected $validator;

    /**
     * @var string
     * */
    protected $method = 'GET';

    /**
     * @param string $url - the url to call via ajax
     * @param array $params - request vars
     * @param string $options - array of options like { "type": "POST", "dataType": "jsonp", "data": { "token": "value" } }
     * @param boolean|string $validator  - custom validator or "reverse"
     * The following are valid responses from the remote url, with a 200 response code: 1, true, { "success": "..." } and assume false otherwise
     * You can show frontend server-side specific error messages by returning { "error": "your custom message" } or { "message": "your custom message" }
     * */
    public function __construct($url, $params = array(), $options = true, $validator = null)
    {
        $this->url = $url;
        $this->params = $params;
        $this->options = $options;
        $this->validator = $validator;

        if (is_array($options) && isset($this->options['type'])) {
            $this->method = $this->options['type'];
        }

        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $url = count($this->params) ? $this->url . '?' . http_build_query($this->params) : $this->url;
        $this->field->setAttribute('data-parsley-remote', $url);
        if (!empty($this->options)) {
            $this->field->setAttribute('data-parsley-remote-options', json_encode($this->options));
        }
        if ($this->validator) {
            $this->field->setAttribute('data-parsley-remote-validator', $this->validator);
        }
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-remote', '');
        if ($this->field->getAttribute('data-parsley-remote-options')) {
            $this->field->setAttribute('data-parsley-remote-options', '');
        }
        if ($this->field->getAttribute('data-parsley-remote-validator')) {
            $this->field->setAttribute('data-parsley-remote-validator', '');
        }
    }

    public function validate($value)
    {
        if (!$value) {
            return true;
        }

        $this->params[$this->field->getName()] = $value;
        $query = http_build_query($this->params);
        $url = $this->method == 'GET' ? $this->url . '?' . $query : $this->url;

        // If the url is a relative one, use Director::test() to get the response
        if (Director::is_relative_url($url)) {
            $url = Director::makeRelative($url);
            $postVars = $this->method == 'POST' ? $this->params : null;
            $response = Director::test($url, $postVars = null, Controller::curr()->getSession(), $this->method);
            $result = ($response->getStatusCode() == 200) ? true : false;

            // Otherwise CURL to remote url
        } else {
            $ch = curl_init();
            if ($this->method == 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZENVALIDATOR');
            curl_setopt($ch, CURLOPT_HEADER, true);
            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = ($status == 200) ? true : false;
        }

        return $this->validator == 'reverse' ? !$result : $result;

        // $isJson = ((is_string($result) && (is_object(json_decode($result)) || is_array(json_decode($result)))));
        //
        // if ($isJson) {
        //     $result = Convert::json2obj($result);
        //     if (isset($result->success)) {
        //         return true;
        //     } else {
        //         if (isset($result->message)) {
        //             $this->setMessage($result->message);
        //         } elseif (isset($result->error)) {
        //             $this->setMessage($result->error);
        //         }
        //     }
        // }

        return false;
    }

    public function getDefaultMessage()
    {
        return _t('ZenValidator.REMOTE', 'This value seems to be invalid');
    }
}

/**
 * Constraint_type
 * Constrain a field value to be a of a min value, max value or between a range
 *
 * @example Constraint_type::create('email'); // require valid email
 * @example Constraint_type::create('url'); // require valid url
 * @example Constraint_type::create('number'); // require valid number
 * @example Constraint_type::create('integer'); // require valid integer
 * @example Constraint_type::create('digits'); // require only digits
 * @example Constraint_type::create('alphanum'); // require valid alphanumeric string
 * */
class Constraint_type extends ZenValidatorConstraint
{

    const EMAIL = 'email';
    const URL = 'url';
    const NUMBER = 'number';
    const INTEGER = 'integer';
    const DIGITS = 'digits';
    const ALPHANUM = 'alphanum';

    /**
     * @var string
     * */
    protected $type;

    /**
     * @param string $type - allowed datatype
     * */
    public function __construct($type)
    {
        $this->type = $type;
        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $this->field->setAttribute('data-parsley-type', $this->type);
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-type', '');
    }

    public function validate($value)
    {
        if (!$value) {
            return true;
        }

        switch ($this->type) {
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            case 'number':
                return is_numeric($value);
            case 'integer':
                return is_int($value);
            case 'digits':
                return preg_match('/^[0-9]*$/', $value);
            case 'alphanum':
                return ctype_alnum($value);
        }
    }

    public function getDefaultMessage()
    {
        switch ($this->type) {
            case 'url':
                return _t('ZenValidator.URL', 'This value should be a valid URL');
            case 'email':
                return _t('ZenValidator.EMAIL', 'This value should be a valid email');
            case 'number':
                return _t('ZenValidator.NUMBER', 'This value should be a number');
            case 'integer':
                return _t('ZenValidator.INTEGER', 'This value should be a number');
            case 'digits':
                return _t('ZenValidator.DIGITS', 'This value should be a number');
            case 'alphanum':
                return _t('ZenValidator.ALPHANUMERIC', 'This value should be alphanumeric');
        }
    }
}

/**
 * Constraint_equalto
 * Constrain a field value to be the same as another field
 *
 * @example Constraint_equalto::create('OtherField');
 * */
class Constraint_equalto extends ZenValidatorConstraint
{

    /**
     * @var string
     * */
    protected $targetField;

    /**
     * @param string $field the Name of the field to match
     * */
    public function __construct($field)
    {
        $this->targetField = $field;
        parent::__construct();
    }

    /**
     * @return FormField
     */
    public function getTargetField()
    {
        return $this->field->getForm()->Fields()->dataFieldByName($this->targetField);
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $this->field->setAttribute('data-parsley-equalto', '#' . $this->getTargetField()->getAttribute('id'));
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-equalto', '');
    }

    public function validate($value)
    {
        return $this->getTargetField()->dataValue() == $value;
    }

    public function getDefaultMessage()
    {
        return sprintf(_t('ZenValidator.EQUALTO', 'This value should be the same as the field %s'), $this->getTargetField()->Title());
    }
}

/**
 * Constraint_comparison
 * Compare the value from one field to another field
 *
 * @example Constraint_comparison::create('gt','OtherField');
 * @example Constraint_comparison::create('gte','OtherField');
 * @example Constraint_comparison::create('lt','OtherField');
 * @example Constraint_comparison::create('lte','OtherField');
 * */
class Constraint_comparison extends ZenValidatorConstraint
{

    const GREATER = 'gt';
    const GREATER_OR_EQUAL = 'gte';
    const LESS = 'lt';
    const LESS_OR_EQUAL = 'lte';

    /**
     * @var string
     * */
    protected $targetField;

    /**
     * @var type
     * */
    protected $type;

    /**
     * @param string $type Type of validation
     * @param string $field the Name of the field to match
     * */
    public function __construct($type, $field)
    {
        $this->loadExtra('comparison');
        $this->type = $type;
        $this->targetField = $field;
        parent::__construct();
    }

    /**
     * @return FormField
     * */
    public function getTargetField()
    {
        return $this->field->getForm()->Fields()->dataFieldByName($this->targetField);
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $this->field->setAttribute('data-parsley-' . $this->type, '#' . $this->getTargetField()->getAttribute('id'));
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-' . $this->type, '');
    }

    public function validate($value)
    {
        switch ($this->type) {
            //Validates that the value is greater than another field's one
            case self::GREATER:
                return $value > $this->getTargetField()->dataValue();
            //Validates that the value is greater than or equal to another field's one
            case self::GREATER_OR_EQUAL:
                return $value >= $this->getTargetField()->dataValue();
            //Validates that the value is less than another field's one
            case self::LESS:
                return $value < $this->getTargetField()->dataValue();
            //Validates that the value is less than or equal to another field's one
            case self::LESS_OR_EQUAL:
                return $value <= $this->getTargetField()->dataValue();
            default:
                throw new Exception('Invalid type : ' . $this->type);
        }
    }

    public function getDefaultMessage()
    {
        switch ($this->type) {
            case self::GREATER:
                return sprintf(_t('ZenValidator.GREATER', 'This value should be greater than the field %s'), $this->getTargetField()->Title());
            case self::GREATER_OR_EQUAL:
                return sprintf(_t('ZenValidator.GREATEROREQUAL', 'This value should be greater or equal than the field %s'), $this->getTargetField()->Title());
            case self::LESS:
                return sprintf(_t('ZenValidator.LESS', 'This value should be less than the field %s'), $this->getTargetField()->Title());
            case self::LESS_OR_EQUAL:
                return sprintf(_t('ZenValidator.LESSOREQUAL', 'This value should be less than or equal to the field %s'), $this->getTargetField()->Title());
        }
    }
}


/**
 * Constraint_words
 * Validates the number of words in the field
 *
 * @example Constraint_words::create('minwords','200');
 * @example Constraint_words::create('maxwords','200');
 * @example Constraint_words::create('words','200',600);
 * */
class Constraint_words extends ZenValidatorConstraint
{

    const MINWORDS = 'minwords';
    const MAXWORDS = 'maxwords';
    const WORDS = 'words';

    /**
     * @var int
     * */
    protected $val1;
    /**
     * @var int
     * */
    protected $val2;

    /**
     * @var type
     * */
    protected $type;

    /**
     * @param string $type type of validation
     * @param int $val1 number of words
     * @param int $val2 maximum number of words
     * */
    public function __construct($type, $val1, $val2 = null)
    {
        $this->loadExtra('words');
        $this->type = $type;
        $this->val1 = $val1;
        $this->val2 = $val2;
        if ($type == self::WORDS && $val2 === null) {
            throw new Exception('You must specify a range of words');
        }
        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $value = $this->val1;
        if ($this->val2) {
            $value = '[' . $value . ',' . $this->val2 . ']';
        }
        $this->field->setAttribute('data-parsley-' . $this->type, $value);
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-' . $this->type, '');
    }

    public function validate($value)
    {
        $count = str_word_count($value);
        switch ($this->type) {
            //Validates that the value have at least a certain amount of words
            case self::MINWORDS:
                return $count >= $this->val1;
            //Validates that the value have a maximum of a certain amount of words
            case self::MAXWORDS:
                return $count <= $this->val1;
            //Validates that the value is within a certain range of words
            case self::WORDS:
                return $count >= $this->val1 && $count <= $this->val2;
            default:
                throw new Exception('Invalid type : ' . $this->type);
        }
    }

    public function getDefaultMessage()
    {
        switch ($this->type) {
            case self::MINWORDS:
                return sprintf(_t('ZenValidator.MINWORDS', 'This value should have at least %s words'), $this->val1);
            case self::MAXWORDS:
                return sprintf(_t('ZenValidator.MAXWORDS', 'This value should have a maximum of %s words'), $this->val1);
            case self::WORDS:
                return sprintf(_t('ZenValidator.WORDS', 'This value should be between %s and %s words'), $this->val1, $this->val2);
        }
    }
}

/**
 * Constraint_date
 * Validates the the field is a date
 *
 * @example Constraint_date::create();
 * */
class Constraint_date extends ZenValidatorConstraint
{

    /**
     * */
    public function __construct()
    {
        $this->loadExtra('dateiso');
        parent::__construct();
    }

    public function applyParsley()
    {
        parent::applyParsley();
        $this->field->setAttribute('data-parsley-dateiso', 'true');
    }

    public function removeParsley()
    {
        parent::removeParsley();
        $this->field->setAttribute('data-parsley-dateiso', '');
    }

    public function validate($value)
    {
        return preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $value);
    }

    public function getDefaultMessage()
    {
        return _t('ZenValidator.DATEISO', 'This value should be a date');
    }
}

/**
 * Constraint_Dimension
 * Constrain an image field to have the specified dimension(s)
 *
 * @example Constraint_dimension::create('width', 100);
 * */
class Constraint_dimension extends ZenValidatorConstraint
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $val1;

    /**
     * @var int
     */
    protected $val2;

    /**
     * Validation type constants.
     */
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const WIDTH_HEIGHT = 'width_height';
    const RATIO = 'ratio';
    const MIN_WIDTH = 'min_width';
    const MIN_HEIGHT = 'min_height';
    const MIN_WIDTH_HEIGHT = 'min_width_height';
    const MAX_WIDTH = 'max_width';
    const MAX_HEIGHT = 'max_height';
    const MAX_WIDTH_HEIGHT = 'max_width_height';

    /**
     * Constructor
     * @param string $type Type of validation
     * @param int $val1 First value
     * @param int $val2 Second value
     */
    public function __construct($type,$val1,$val2=null)
    {
        $this->type = $type;
        $this->val1 = $val1;
        $this->val2 = $val2;

        parent::__construct();
    }

    /**
     * Validate function called for validator.
     * @param Mixed $value the value of the field being validated.
     * @return boolean
     */
    public function validate($value)
    {
        // The value which comes in is a files array so we can look through this
        // to and then get the files to test aspects of them.
        if (isset($value['Files'])) {
            foreach($value['Files'] as $fileID) {
                $file = File::get()->byId($fileID);

                // Now have the file double-check it is an image and if so then
                // get some information about the image using PHPs getimagesize()
                if ($file->ClassName == 'Image') {
                    $info = getimagesize(BASE_PATH . "/" . $file->Filename);

                    if ($info && is_array($info)) {
                        $width = $info[0];
                        $height = $info[1];

                        switch ($this->type) {
                            case self::WIDTH:
                                return $width == $this->val1;
                                break;
                            case self::HEIGHT:
                                return $height == $this->val1;
                                break;
                            case self::WIDTH_HEIGHT:
                                return (($width == $this->val1) && ($height == $this->val2));
                                break;
                            case self::RATIO:
                                $baseWidth = floor($width / $this->val1);
                                $baseHeight = floor($height / $this->val2);
                                return $baseWidth == $baseHeight;
                                break;
                            case self::MIN_WIDTH:
                                return $width >= $this->val1;
                                break;
                            case self::MIN_HEIGHT:
                                return $height >= $this->val1;
                                break;
                            case self::MIN_WIDTH_HEIGHT:
                                return (($width >= $this->val1) && ($height >= $this->val2));
                                break;
                            case self::MAX_WIDTH:
                                return $width <= $this->val1;
                                break;
                            case self::MAX_HEIGHT:
                                return $height <= $this->val1;
                                break;
                            case self::MAX_WIDTH_HEIGHT:
                                return (($width <= $this->val1) && ($height <= $this->val2));
                                break;
                            default:
                                throw new Exception('Invalid type : ' . $this->type);
                        }
                    }
                }
            }
        } else {
            // Return true so if no file selected then not shown validation message
            // when the field is optional. If required dev should add to required fields as well.
            return true;
        }
    }

    /**
     * Gets the default message for the validator
     * @return string the validation message
     */
    public function getDefaultMessage()
    {
        switch ($this->type) {
            case self::WIDTH:
                return sprintf(
                    _t(
                        'ZenValidator.DIMWIDTH',
                        'Image width must be %s pixels'
                    ),
                    $this->val1
                );
                break;
            case self::HEIGHT:
                return sprintf(
                    _t(
                        'ZenValidator.DIMHEIGHT',
                        'Image height must be %s pixels'
                    ),
                    $this->val1
                );
                break;
            case self::WIDTH_HEIGHT:
                return sprintf(
                    _t(
                        'ZenValidator.DIMWIDTHHEIGHT',
                        'Image width must be %s pixels and Image height must be %s pixels'
                    ),
                    $this->val1,
                    $this->val2
                );
                break;
            case self::RATIO:
                return sprintf(
                    _t(
                        'ZenValidator.DIMRATIO',
                        'Image aspect ratio (shape) must be %s:%s'
                    ),
                    $this->val1,
                    $this->val2
                );
                break;
            case self::MIN_WIDTH:
                return sprintf(
                    _t(
                        'ZenValidator.DIMMINWIDTH',
                        'Image width must be greater than or equal to %s pixels'
                    ),
                    $this->val1
                );
                break;
            case self::MIN_HEIGHT:
                return sprintf(
                    _t(
                        'ZenValidator.DIMMINHEIGHT',
                        'Image height must be greater than or equal to %s pixels'
                    ),
                    $this->val1
                );
                break;
            case self::MIN_WIDTH_HEIGHT:
                return sprintf(
                    _t(
                        'ZenValidator.DIMMINWIDTHHEIGHT',
                        'Image width must be greater than or equal to %s pixels and Image height must be greater than or equal to %s pixels'
                    ),
                    $this->val1,
                    $this->val2
                );
                break;
            case self::MAX_WIDTH:
                return sprintf(
                    _t(
                        'ZenValidator.DIMMAXWIDTH',
                        'Image width must be less than or equal to %s pixels'
                    ),
                    $this->val1
                );
                break;
            case self::MAX_HEIGHT:
                return sprintf(
                    _t(
                        'ZenValidator.DIMMAXHEIGHT',
                        'Image height must be less than or equal to %s pixels'
                    ),
                    $this->val1
                );
                break;
            case self::MAX_WIDTH_HEIGHT:
                return sprintf(
                    _t(
                        'ZenValidator.DIMMAXWIDTHHEIGHT',
                        'Image width must be less than or equal to %s pixels and Image height must be less than or equal to %s pixels'
                    ),
                    $this->val1,
                    $this->val2
                );
                break;
        }
    }
}
