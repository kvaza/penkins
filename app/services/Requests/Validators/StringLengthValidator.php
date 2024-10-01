<?php

/**
 *
 * @version $Id: StringLengthValidator.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Validators;

trait StringLengthValidator{

    /**
     * checks if string has expected length
     *
     * @param string $value
     * @param array $options [max, min]
     *
     * @return bool
     */
    protected function validator_StringLength($value, $options)
    {
        $length = strlen($value);

        if (isset($options['max']) and $length > $options['max']) {

            return false;
        } elseif (isset($options['min']) and $length < $options['min']) {

            return false;
        }
        return true;
    }
}
