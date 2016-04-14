<?php

class ZenValidatorTestController extends Controller
{
    private static $allowed_actions = array(
        'remotetitlecheck'
    );

    public function remotetitlecheck($r)
    {
        if ($r->requestVar('Title') != 'valid title') {
            return $this->httpError(400, 'Sorry, not valid.');
        } else {
            return $this->getResponse()->setBody('OK');
        }
    }

    public function Link($action = '') {
		return get_class($this) . '/' . $action;
	}
}
