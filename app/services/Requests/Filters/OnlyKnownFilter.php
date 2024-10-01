<?php

/**
 *
 * @version $Id: OnlyKnownFilter.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Filters;

trait OnlyKnownFilter{

    /**
    * checks if valus in the list of known values, and sets first value from known list if no
    *
    * @param string $string
    * @return int
    */
    protected function filter_OnlyKnown($value, array $list)
    {
        if(in_array($value, $list)){

            return $value;
        }

        return current($list);
    }
}
