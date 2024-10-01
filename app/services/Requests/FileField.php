<?php

/**
 * file field for $_FILES
 *
 * @version $Id: FileField.php v2.2.0 2016-04-23 12:00:00 ks $
 * @package forms
 * $_FILES = Array
    (
        [report] => Array
            (
                [name] => grid.jpg
                [full_path] => grid.jpg
                [type] => image/jpeg
                [tmp_name] => D:\projects\tmp\phpE485.tmp
                [error] => 0
                [size] => 44625
            )
    )
 *
 */

namespace App\Services\Requests;

class FileField extends Field
{

    /**
     * @param string $name
     */
    public function setFromRequest($name)
    {
        if(isset($_FILES[$name]) and !empty($_FILES[$name]['tmp_name']) and is_uploaded_file($_FILES[$name]['tmp_name'])){

            $this->value = $_FILES[$name];
        }
    }

    public function getTmpName()
    {
        return $this->value['tmp_name'] ?? '';
    }

    public function getSize()
    {
        return $this->value['size'] ?? 0;
    }

    public function getName()
    {
        return $this->value['name'] ?? '';
    }

    public function getType()
    {
        return $this->value['type'] ?? '';
    }
}
