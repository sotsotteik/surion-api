<?php

/**
 * Main Config File
 */
define('ENV', 'dev'); //dev, prd, beta, demo
date_default_timezone_set('Asia/Singapore');
$config = [];

$envConfig = dirname(__FILE__, 2) . '/.env_' . ENV;

if (file_exists($envConfig)) {
    $jConfig = file_get_contents($envConfig);
    $config = json_decode($jConfig, true);
    $config['authKey'] = sha1(date('d-m-Y') . 'V2');
}

define('BASEPATH', $config['basePath']);
define('BASEURL', $config['baseUrl']);
define('WEB',$config['baseUrl'] . 'web/');

if (ENV === 'dev') {
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 0);
    set_time_limit(0);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
########## Set up for INI Configurations Starts here
ini_set('serialize_precision', -1);
//ini_set('memory_limit', '1024MB');
########## Set up for INI Configurations Ends here

$compAL = dirname(__FILE__, 2) . '/vendor/autoload.php';
if (file_exists($compAL)) {
    include_once $compAL;
}

spl_autoload_register(function($class) {
    $exactClass = explode('\\', $class);
    //List of files to be skipped
    $vendorInc = [];
    if (in_array(end($exactClass), ['PDO'])) {
        return;
    }
    if (!file_exists(str_replace('\\', '/', $class) . '.php')) {
        if (isset($vendorInc[$exactClass[0]])) {
            @require_once $vendorInc[$exactClass[0]];
        } else {
            throw new Exception($class . " Route Not Found", 404);
        }
    } else {
        include_once str_replace('\\', '/', $class) . '.php';
    }
});
