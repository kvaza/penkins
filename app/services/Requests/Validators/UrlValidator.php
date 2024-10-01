<?php

/**
 * checks if string in URL valid format
 *
 * @version $Id: UrlValidator.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Validators;

trait UrlValidator{

    /**
     * checks if string is URL in valid format
     *
     * @param string $value
     * @return bool
     */
    protected function validator_Url($value)
    {
        $r = filter_var($value, FILTER_VALIDATE_URL);
        return !empty($r);
    }
}
