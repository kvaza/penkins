<?php

/**
 *
 * @version $Id: EmailAddressValidator.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Validators;

trait EmailAddressValidator{

    /**
     * checks if string is email address in valid format
     *
     * @param mixed $value
     * @return bool
     */
    protected function validator_EmailAddress($value)
    {
        $r = filter_var($value, FILTER_VALIDATE_EMAIL);
        return !empty($r);
    }
}
