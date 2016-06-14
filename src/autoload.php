<?php

function autoload($className)
{
    if(is_file( $className . '.php')) {
        include_once  $className . '.php';
    }elseif(is_file( dirname(__FILE__).DIRECTORY_SEPARATOR.$className . '.php')){
        include_once  dirname(__FILE__).DIRECTORY_SEPARATOR.$className . '.php';
    }else{
        return;
    }
}

spl_autoload_register('autoload', true, true);