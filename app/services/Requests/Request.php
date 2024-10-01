<?php

/**
 * Request for request data, and other data groups,
 * Request contains list of fields with their filters and validators
 *
 * @version $Id: Request.php v1.4.6 2014-04-23 12:00:00 ks $
 * @package Requests
 *
 */

namespace App\Services\Requests;

abstract class Request
{
    /**
     * magic name if you want to get field error, i.e.  $request->fieldNameError
     */
    private const FIELD_ERROR = 'Error';

    /**
     * magic name if you want to get field value, i.e.  $request->fieldNameValue
     */
    private const FIELD_VALUE = 'Value';

    /**
     * magic name if you want to get htmlencoded field value, i.e.  $request->fieldHtmlValue
     */
    private const FIELD_HTML_VALUE = 'HtmlValue';

    private const DEFAULT_VALIDATOR_MESSAGE = 'Wrong data provided';

    /**
     * Request fields
     *
     * @var array App\Services\Requests\Field array
     */
    protected $fields = array();

    /**
     * map of elements
     *
     * @var array [field_name => data_array_name]
     */
    protected $map = [];

    /**
     * Request fields errors
     *
     * @var array [field_name => [validator1, validator2]);
     */
    protected $errors = [];

    /**
     * default error decorator
     *
     * @var string
     */
    protected $errorDecorator = '%s';

    /**
     *
     * @var string
     */
    protected $errorMessagesSeparator = '<br />';

    /**
     * constructor
     */
    public function __construct()
    {
    }

    /**
     * magic function for mising methods
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        $a = 1; // debug line
    }

    /**
     * magic function for private and missing properties
     * - returns instance of App\Services\Requests
     * - returns field value after htmlspecialchars() function
     * - returns field value asa is
     * - returns error message if exists
     *
     * @param string $name
     * @param array $arguments
     * @return Field|string|null
     */
    public function __get($name)
    {
        if (isset($this->fields[$name])) { // return instance of field if requested by name

            return $this->fields[$name];

        } elseif (! preg_match('/(.*?)('. self::FIELD_HTML_VALUE. '|'. self::FIELD_VALUE. '|'.self::FIELD_ERROR.')\Z/i', $name, $m)) { // unknown request, return null

            return null;
        }

        $type = $m[2];
        $var = $m[1];

        if ($type == self::FIELD_ERROR and isset($this->fields[$var])) {

            return $this->getErrorMessage($var);

        } elseif ($type == self::FIELD_VALUE and isset($this->fields[$var])) {

            return $this->getValue($var);

        } elseif ($type == self::FIELD_HTML_VALUE and isset($this->fields[$var])) {

            return $this->getHtmlValue($var);
        }

        return null;
    }

    public function __set($name, $value)
    {
        // something here
        $a = 1; // debug line
    }

    /**
     * add Request field
     *
     * @param string $name field name
     * @param string $default default value
     * @return App\Services\Requests\Field
     */
    protected function addField($name, $default = null)
    {
        $field = new Field($name, $default);
        $this->fields[$name] = $field;

        return $field;
    }

    protected function addArrayField($name, $default = null)
    {
        $field = new ArrayField($name, $default);
        $this->fields[$name] = $field;

        return $field;
    }

    protected function addFileField($name)
    {
        $field = new FileField($name);
        $this->fields[$name] = $field;

        return $field;
    }

    /**
     * - sets field value
     * - filters fiel
     *
     * @param string $name field name
     * @param mixed $value
     */
    public function setValue($name, $value)
    {
        if (isset($this->fields[$name])) {
            $this->fields[$name]->setValue($value);
        }

        $this->filterRequest($name);
    }

    /**
     * sets name of failed validators
     *
     * @param string $name name of  field
     * @param string $validator name of validator
     * @param array $arguments message arguments
     */
    protected function setError($name, $validator, $arguments = [])
    {
        $this->errors[$name][] = $validator;

        if (!empty($arguments)) { // updates message with arguments

            if(!is_array($arguments)){
                $arguments = [$arguments];
            }

            $message = vsprintf($this->fields[$name]->getErrorMessages()[$validator], $arguments);
            $this->fields[$name]->addErrorMessage($message, $validator);
        }
    }

    /**
     * returns all Request fields
     *
     * @return array array of App\Services\Requests\Field objects
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * return Request data in associated array array(field_name => field_value)
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        foreach ($this->fields as $field) {
            $data[$field->name] = $field->value;
        }

        return $data;
    }

    /**
     * assigns POST/GET data into Request fields
     *
     * @return void
     */
    public function getRequest()
    {
        foreach ($this->fields as $name => $field) {
            $field->setFromRequest($name);
        }

        $this->filterRequest();
    }

    /**
     * sets $map of elements
     *
     * @param array $map
     *            array(element_name => alias_from_data_array)
     * @see self::setFromArray
     * @see self::elementMap
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }

    /**
     * set Request values from array
     *
     * @param array $data
     */
    public function setFromArray(array $data)
    {
        foreach ($this->fields as $fname => $field) {

            $name = $this->elementMap($fname);
            if (isset($data[$name]) and (is_numeric($data[$name]) or is_string($data[$name]))) {

                $field->setValue($data[$name]);
            }
        }

        $this->filterRequest();
    }

    /**
     * set Request values from array
     *
     * @param array $data
     */
    public function setFromObject(\stdClass $data)
    {
        foreach ($this->fields as $fname => $field) {

            $name = $this->elementMap($fname);
            if (isset($data->$name) and is_scalar($data->$name)) {

                $field->setValue($data->$name);
            }
        }

        $this->filterRequest();
    }

    /**
     * returns element frmo fields map
     *
     * @param string $name
     * @return string
     */
    private function elementMap($fname)
    {
        if (isset($this->map[$fname])) {
            return $this->map[$fname];
        }

        return $fname;
    }

    /**
     * accept Request filters
     */
    private function filterRequest($fname = null)
    {
        if (! is_null($fname) and isset($this->fields[$fname])) { // only requested Request field

            $fields = [$fname => $this->fields[$fname]];

        } else { // all Request fields

            $fields = $this->fields;
        }

        foreach ($fields as $field) {

            foreach ($field->getFilters() as $filter => $arguments) {

                if (function_exists($filter)) { // global php functions

                    $value = $field->getValue();

                    // PHP8 : Deprecated: ...: Passing null to parameter #1 ($string) of type string is deprecated
                    if(is_null($value)){
                        $value = '';
                    }

                    if (! is_array($arguments)) {
                        $arguments = [0 => $arguments];
                    }

                    $value = call_user_func_array($filter, array_merge([$value], $arguments));
                    $field->setValue($value);

                } elseif (method_exists($this, $f = 'filter_' . $filter)) { // Request filters

                    $value = $this->$f($field->getValue(), $arguments);
                    $field->setValue($value);

                } else { // unknown filter

                    throw new \Exception('Filter "' . $filter . '" is not found.');
                }
            }
        }
    }

    /**
     * validate Request using assigned validators
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->fields as $name => $field) {

            foreach ($field->getValidators() as $validator => $arguments) {

                if (function_exists($validator)) { // global php functions without extra arguments

                    $result = $validator($field->getValue());
                } elseif (method_exists($this, $v = 'validator_' . $validator)) { // Request validators

                    $result = $this->$v($field->getValue(), $arguments);
                } else { // unknown validator

                    throw new \Exception('Validator "' . $validator . '" is not found.');
                    // $result = false;
                }

                if (! $result) {
                    $this->setError($name, $validator, $arguments);
                }

                if (! $result and empty($arguments['chain'])) {
                    break; // stop validatation after first failed validator
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public function isPost()
    {
        if (!empty($_SERVER['REQUEST_METHOD']) and 'POST' == $_SERVER['REQUEST_METHOD']) {
            return true;
        }

        return false;
    }

    public function resetValues()
    {
        foreach ($this->fields as $name => $element) {
            $this->fields[$name]->setValue(null);
        }

        $this->errors = [];
    }

    /**
     * returns field value
     *
     * @return mixed
     */
    public function getValue($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name]->getValue();
        }

        return null;
    }

    /**
     * returns field value after htmlspecialchars()
     *
     * @param mixed $name
     * @return mixed
     */
    public function getHtmlValue($name)
    {
        $value = $this->getValue($name);
        if (is_null($value)) {
            return '';
        } else {
            return htmlspecialchars($value);
        }
    }

    /**
     * return error message for requested field
     *
     * @param string $name field name
     * @param string $addDecorator
     * @return string errors available for requested field
     */
    public function getErrorMessage($name, $addDecorator = true)
    {
        $messages = [];
        if (! empty($this->errors[$name])) {

            $err = $this->fields[$name]->getErrorMessages();

            foreach ($this->errors[$name] as $validator) {

                if (! empty($err[$validator])) { // error message for specific validator

                    $messages[$validator] = $err[$validator];
                } elseif (! empty($err['global'])) { // global field error message

                    $messages['global'] = $err['global'];
                } else { // default error message
                    $messages['global'] = self::DEFAULT_VALIDATOR_MESSAGE;
                }
            }
        }

        if (! empty($this->errorDecorator) and $addDecorator) {

            foreach ($messages as $key => $message) {
                $messages[$key] = sprintf($this->errorDecorator, $message);
            }
        }

        return join($this->errorMessagesSeparator, $messages);
    }

    /**
     * validator names which errored
     * @return array  [field_name => [validator_name1, validator_name2]]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * array with all error messages
     *
     * @param string $singleFieldMessageSeparator
     * @return array
     */
    public function getErrorMessages($singleFieldMessageSeparator = null)
    {
        if (!is_null($singleFieldMessageSeparator)) {
            $this->errorMessagesSeparator = $singleFieldMessageSeparator; // used in getErrorMessage()
        }

        $messages = [];

        $errors = $this->getErrors();
        foreach ($errors as $name => $validators) {

            $messages[] = $this->getErrorMessage($name, false);
        }

        return $messages;
    }
}
