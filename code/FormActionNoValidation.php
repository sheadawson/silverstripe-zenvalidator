<?php

/**
 * A form action that does not triggers parsley validation
 *
 * @author Koala
 */
class FormActionNoValidation extends FormAction
{
    public function __construct($action, $title = "", $form = null)
    {
        parent::__construct($action, $title, $form);
    }

    public function Field($properties = array())
    {
        Requirements::customScript("jQuery('#".$this->ID()."').click(function() { jQuery(this).parents('form').parsley().destroy();})");
        return parent::Field($properties);
    }
}