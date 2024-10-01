<?php

/**
 *
 * @version $Id: NotEmptyValidator.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Validators;

trait NotEmptyValidator{

    /**
     * checks if value is not empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function validator_NotEmpty($value)
    {
        return !empty($value);
    }
}
