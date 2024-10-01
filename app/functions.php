<?php

/**
 * classes autoloader
 *
 * @param string $class
 */
function PenkinsAutoloader($class)
{
    if(stripos($class, 'App\\') === 0){ // application namespaces, top  App namespace and subnames expected in lowercase , App\subnamespace\,  App\services\

        $n = strpos($class, '\\', 5);
        $cname = str_replace('\\', '/', strtolower(substr($class, 4, $n -4)). substr($class, $n));

        if(is_file($f = DIR_APP . $cname. '.php')){

            require_once $f;
        }

    }else{ // composer autoloader

        require_once DIR_APP. 'vendor/autoload.php';

        // do this call "spl_autoload_call($class);" if  $loader->register(true) is "true"; in composer/autoload_real.php , line 53
        // spl_autoload_call($class);
    }
}

spl_autoload_register('PenkinsAutoloader', true, true);

