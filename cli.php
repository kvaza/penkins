<?php

/**
* main controller,
* this project will check modifications between 2 commits in selected git branch and copy/delete affected files via ftp
*
* @version v1.0.2 2024-02-09 ks
* @package penkins
*/

include_once 'app/config.php';

use App\Services\CliController;
use App\Services\Exceptions\CliException;

/*
echo '<pre>';
print_r($argv);
echo '</pre>';
/**/

/*
$argv = [

    0 => 'cli.php',
    1 => '--controller=Checker',
    2 => '--configuration=test1'
];

/**/

    /*
$argv = [

    0 => 'cli.php',
    1 => '--controller=Deployer',
    2 => '--configuration=test1'
];
/* */
    /*
$argv = [

    0 => 'cli.php',
    1 => '--controller=Manager',
    2 => '--view=add',
    3 => '--name=config1',
    3 => '--repository=d:/projects/www/test1/',
];
 /**/
 /*
$argv = [

    0 => 'cli.php',
    1 => '--controller=Manager',
    2 => '--view=delete',
    3 => '--name=test',
]; /**/


try{

    CliController::setArguments($argv);

    $m = CliController::getControllerName();

    $module = new $m;
    $module->run();

    ECHO PHP_EOL;

}catch(CliException $t){ // known CLI exception

    CliController::log($t->getMessage());

    echo PHP_EOL;
    echo $t->getMessage();
    echo PHP_EOL. PHP_EOL;

}catch(Throwable $t){ // something uknown happened

    CliController::log($t->getMessage());

    echo PHP_EOL;
    echo $t->getMessage();
    echo PHP_EOL. PHP_EOL;
}
