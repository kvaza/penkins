<?php

/**
 *
 * @version $Id: PageFilter.php v1.0.0 2024-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests\Filters;

trait PageFilter{

    /**
    * sets current Page , int , >= 0
    *
    * @param string $string
    * @return int
    */
    protected function filter_PageValue($string)
    {
        $string = abs(intval($string));

        if (empty($string)) {

            $string = 0;
        }
        return $string;
    }
}
