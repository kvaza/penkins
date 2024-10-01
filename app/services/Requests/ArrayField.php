<?php

/**
 * instance of form field that should contain multiple values
 *
 * @version $Id: Field.php v2.2.0 2016-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests;

class ArrayField extends Field
{
    public function setFromRequest($name)
    {
        $this->value = $this->getRequestArray($name);
    }

    /**
     * returns single level array from $_REQUEST
     *
     * @param string $name
     * @return array single level array or empty array
     */
    private function getRequestArray($name)
    {
        if (isset($_REQUEST[$name])) {
            $r = $_REQUEST[$name];
        } else {
            return array();
        }

        if (is_array($_REQUEST[$name])) {

            foreach ($r as $k => $v) {

                if (is_string($v) or is_numeric($v)) {
                    $r[$k] = $v;
                } else {
                    $r[$k] = null;
                }
            }

            return $r;
        }
        return [];
    }
}
