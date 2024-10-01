<?php

/**
 * project parameters
 *
 * @version $Id: config.php v1.0.0 2018-07-20 12:00:00 ks $
 * @package penkins
 */

error_reporting(E_ALL | E_DEPRECATED | E_STRICT | E_USER_DEPRECATED | E_RECOVERABLE_ERROR);

date_default_timezone_set('Europe/Minsk');

/**
 * curent application directory where config.php is located
 *
 */
define('DIR_APP', __DIR__. '/');

/**
 * directory with project configurations
 *
 */
define('DIR_CONFIGURATIONS', __DIR__. '/storage/configurations');

/**
 * directory with project database
 *
 */
//define('DIR_DATABASE', __DIR__. '/storage/database/');

/**
 * directory with logs
 *
 */
define('DIR_LOGS', __DIR__. '/storage/logs/');

/**
 * temporay files directory
 */
//define('DIR_TMP', __DIR__. '/storage/tmp/');


include_once (DIR_APP . 'functions.php');