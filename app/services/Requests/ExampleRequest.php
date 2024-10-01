<?php

/**
* @version $Id: VinsSearchRequest.php v1.0.0 2023-05-08 12:00:00 ks $
* @package gmprog
*/

namespace App\Services\Requests;

use App\Services\Requests\Request;
use App\Services\Requests\Field;
use App\Services\Requests\ArrayField;
use App\Services\Requests\FileField;

use App\Services\Requests\Filters\StripTagAttributesFilter;
use App\Services\Requests\Filters\OnlyKnownFilter;

use App\Services\Requests\Validators\NotEmptyValidator;
use App\Services\Requests\Validators\StringLengthValidator;

class ExampleRequest extends Request
{
    use StripTagAttributesFilter;
    use OnlyKnownFilter;

    use NotEmptyValidator;
    use StringLengthValidator;

    /**
    * default Request filters
    *
    * @var array
    */
    protected $filters = [
        'trim',
        'strip_tags'
    ];

    /**
    * constructor
    */
    public function __construct()
    {
        $this->addField('name')
            ->addFilters($this->filters)
            ->addValidator('NotEmpty');
            ;

        $this->addField('city')
            ->addFilters($this->filters)
            ->addFilter('CityBraces')
            ->addValidator('StringLength', ['max' => 50, 'min' => 15])
            ->addErrorMessage('Wrong size of city', 'StringLength');
            ;

        $this->addField('cost')
            ->addFilter('round', 2)
            ->addValidator('CostLimits', ['max' => 100, 'min' => 10])
            ->addErrorMessage('Cost should be between %s and %s', 'CostLimits')
            ;

        $this->addFileField('report')
         ->addValidator('NotEmpty')
         ->addValidator('LessThan', 4000)
         ->addErrorMessage('Report should be attached', 'NotEmpty')
         ->addErrorMessage('Size of report should be less than %s bytes', 'LessThan')
         ;


        $this->addField('message')
         ->addFilter('StripTagAttributes')
         ;

         $this->addField('feature')
            ->addFilter('OnlyKnown', ['a', 'd', 'e'])
         ;

         $this->addArrayField('options')
          ->addFilter('MaxSize', 5);
    }

    protected function filter_CityBraces($string)
    {
        return '['. $string. ']';
    }

    protected function filter_MaxSize($value, $arguments)
    {
        return array_slice($value, 0, $arguments);
    }

    protected function validator_LessThan($value, $max)
    {
        return (($value['size']?? 0) < $max);
    }

    protected function validator_CostLimits($value, $arguments)
    {
        if($value <= $arguments['max'] and $value >= $arguments['min']) {
            return true;
        }

        return false;
    }
}


/*

// test data

$errors = [];
$messages = [];


$_REQUEST = [

    'name' => '             Alex ',
    'city' => '    Wetumpka ',
    'cost' => 1000.234234,
    'message' => ' here is message with tags <b class="sss"> tag 1</b> ',
    'feature' => 'g',
    'options' => [4,5,6,7,8,9,9,5,4,3]
];


$_FILES = [
    'report' => [

        'name' => 'grid.jpg',
        'full_path' => 'grid.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => 'D:\projects\tmp\phpE485.tmp',
        'error' => '0',
        'size' => '44625',
    ]
];


$request = new App\Services\Requests\TestRequest();

$request->getRequest();

$post = $request->isPost();

if($request->isValid()){

    $valid = true;

}else{

    $valid = false;

    $errors = $request->getErrors();
    $messages = $request->getErrorMessages();

    $fieldMessage = $request->getErrorMessage('report');
}


*/