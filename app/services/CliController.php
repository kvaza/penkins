<?php

/**
 * CLI base
 *
 * @version $Id: CliController.php v1.0.0 2012-12-18 12:00:00 ks $
 * @package penkins
 */

namespace App\Services;

use App\Services\Exceptions\CliException;

abstract class CliController
{
    private const CONTROLLERS_NAMESPACE = 'App\Controllers\\';

    /**
     * daily log file name
     */
    private const LOG_FILE_PATTERN = '%s-log.txt';

    /**
     * parsed CLI arguments
     *
     * @var array
     */
    protected static $arguments = [];


    public function __construct()
    {
    }

    /**
     * parses arguments and sets in class $arguments
     *
     * @param array $argv
     */
    public static function setArguments($argv)
    {
        unset($argv[0]);
        foreach($argv as $key => $arg){

            if(substr($arg, 0, 2) == '--') { // named arguments

                $parts = explode('=', substr($arg, 2));
                self::$arguments[$parts[0]] = $parts[1];

            }elseif(substr($arg, 0, 1) == '-') { // single character arguments

                $parts = preg_split('/(?=\w)(?<=\w)/', substr($arg, 1));
                foreach($parts as $value) {

                    self::$arguments[$value] =true;
                }
            }
        }
    }

    /**
     * returns name of currently requested controller
     *
     * @return string
     */
    public static function getControllerName()
    {
        if(empty(self::$arguments['controller'])){

            throw new CliException('Unknown controller');
        }

        $m = self::CONTROLLERS_NAMESPACE. self::$arguments['controller'];

        if(!class_exists($m)){
            throw new CliException('Unknown controller');
        }

        return $m;
    }

    /**
     * works with received arguments
     *
     */
    abstract public function run();

    /**
     * displays something , also templates could be used here
     *
     */
    protected function display($lines, $newline = true)
    {
        if($newline) {
            $nl = PHP_EOL;
        } else {
            $nl = '';
        }

        $message = join(PHP_EOL, $lines);

        echo $message. $nl;
        self::log($message. $nl);
    }

    /**
     * stores log messages
     *
     * @param string $message
     */
    public static function log(string $message)
    {
        file_put_contents(DIR_LOGS. sprintf(self::LOG_FILE_PATTERN, date('Ymd')), $message. PHP_EOL, FILE_APPEND);
    }
}
