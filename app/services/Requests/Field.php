<?php

/**
 * instance of form field
 *
 * @version $Id: Field.php v2.2.0 2016-04-23 12:00:00 ks $
 * @package forms
 *
 */

namespace App\Services\Requests;

class Field
{

    /**
     * field name
     *
     * @var string
     */
    protected $name;

    /**
     * field value, data recevied when form submitted
     *
     * @var string
     */
    protected $value = null;

    /**
     * field style
     *
     * @var string
     */
    protected $attributes = array();

    /**
     * field validators
     *
     * @var array
     */
    protected $validators = array();

    /**
     * field filters
     *
     * @var array
     */
    protected $filters = array();

    /**
     * field error messages
     *
     * @var array
     */
    protected $errorMessages = [];

    /**
     * constructor
     */
    public function __construct($name, $default = null)
    {
        $this->name = $name;

        if(!is_null($default)){
            $this->value = $default;
        }
    }

    /**
     * sets name, value, or attributes
     *
     * @param string $name
     *            [name|value|attributes]
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if ($name == 'name' or $name == 'value' or $name == 'attributes') {

            $this->$name = $value;
        }
    }

    public function __get($name)
    {
        if ($name == 'name' or $name == 'value' or $name == 'attributes') {

            return $this->$name;
        }

        return null;
    }

    /*
    * public function __toString(){

    */
    public function setAttributes($attributes)
    {
        if (is_array($attributes)) {
            $this->attributes = $attributes;
        } elseif (is_scalar($attributes)) {
            $this->attributes['value'] = $attributes;
        }

        if (! empty($this->attributes['value'])) {

            $this->value = $this->attributes['value'];
        }
    }

    /**
     * add single field filter
     *
     * @param string $name
     * @param $arguments array
     * @return $this
     */
    public function addFilter($name, $arguments = [])
    {
        if (is_scalar($name)) {
            $this->filters[$name] = $arguments;
        }

        return $this;
    }

    public function removeFilter($filter)
    {
        if (isset($this->filters[$filter])) {
            unset($this->filters[$filter]);
        }

        return $this;
    }

    /**
     * list of filters
     *
     * @param array|string $filters [filter1, filter2] or [filter1 => arguments1, filter2 => arguments2]
     * @return $this
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filter => $arguments) {

            if (is_numeric($filter)) {
                $this->filters[$arguments] = [];
            } else {
                $this->filters[$filter] = $arguments;
            }
        }

        return $this;
    }

    /**
     * field validators
     *
     * @param string $name
     * @return $this
     */
    public function addValidator($name, $arguments = [])
    {
        $this->validators[$name] = $arguments;
        return $this;
    }

    /**
     * list of validators
     *
     * @param array $validators [validator1_name => validator1_arguments, validator2_name => validator2_arguments] or [validator1, validator2]
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $validator => $arguments) {

            if (is_numeric($validator)) { // $arguments is validator name
                $this->validators[$arguments] = [];
            } else {
                $this->validators[$validator] = $arguments;
            }
        }

        return $this;
    }

    /**
     * return filters
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * returns validators
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * returns error messages
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * adds error message, adds 'global' error message if validator name is missing
     *
     * @param string $message error message, will be used for all validators if validaor name not provided
     * @param string $validator validator name, set validaotr name if specific message required for it
     */
    public function addErrorMessage($message, $validator = '')
    {
        if (empty($validator)) {
            $this->errorMessages['global'] = $message;
        } else {
            $this->errorMessages[$validator] = $message;
        }

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setFromRequest($name)
    {
        $this->value = $this->getRequestVar($name);
    }

    /**
     * get variable from $_REQUEST array
     *
     * @param string $name
     * @param array $def default value
     * @return mixed
     */
    private function getRequestVar($name, $def = null)
    {
        if (isset($_REQUEST[$name])) {
            $r = $_REQUEST[$name];
        } else {
            return $def;
        }

        if (is_string($r)) {

            return $r;
        } elseif (is_numeric($r)) {

            return $r;
        }

        return $def;
    }
}
