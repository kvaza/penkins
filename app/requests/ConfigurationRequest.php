<?php

/**
* @version $Id: VinsSearchForm.php v1.0.2 2024-08-28 12:00:00 ks $
* @package gmprog
*/

namespace App\Requests;

use App\Services\Requests\Request;
use App\Services\Requests\Validators\NotEmptyValidator;

class ConfigurationRequest extends Request
{
    use NotEmptyValidator;

    protected const GIT_DIRECTORY = '.git';

    public function __construct()
    {
        $this->addField('name', '')
        ->addFilter('trim')
        ->addValidator('NotEmpty')
        ->addErrorMessage('Name is required', 'NotEmpty')
        ;

        $this->addField('repository', '')
        ->addFilter('trim')
        ->addFilter('TrimEndSlash')
        ->addValidator('NotEmpty')
        ->addValidator('IsGitExists')
        ->addErrorMessage('Repository is required', 'NotEmpty')
        ->addErrorMessage('.git directory not found on provided path', 'IsGitExists')
        ;

        $this->addField('branch', '')
        ->addFilter('trim')
        ->addValidator('NotEmpty')
        ->addErrorMessage('Branch is required', 'NotEmpty')
        ;

        $this->addField('ftp_host', '')
        ->addFilter('trim')
        ->addValidator('NotEmpty')
        ->addErrorMessage('FTP Host is required', 'NotEmpty')
        ;

        $this->addField('ftp_login', '')
        ->addFilter('trim')
        ->addValidator('NotEmpty')
        ->addErrorMessage('FTP Login is required', 'NotEmpty')
        ;

        $this->addField('ftp_password', '')
        ->addValidator('NotEmpty')
        ->addErrorMessage('FTP Password is required', 'NotEmpty')
        ;

        $this->addField('ftp_path', '')
        ->addFilter('trim')
        ;
    }

    protected function filter_TrimEndSlash($string)
    {
        if(substr($string, -1) == '/' or substr($string, -1) == '\\'){
            $string = substr($string, 0, -1);
        }

        return $string;
    }

    protected function validator_IsGitExists($string)
    {
        return is_dir($string. '/'. self::GIT_DIRECTORY);
    }
}
