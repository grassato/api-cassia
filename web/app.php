<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

if (file_exists(__DIR__.'/../.env')) {
    (new Dotenv())->load(__DIR__.'/../.env');
}

$env = isset($_SERVER['SYMFONY_ENV'])? $_SERVER['SYMFONY_ENV'] : 'prod';
$debug = isset($_SERVER['SYMFONY_DEBUG'])? $_SERVER['SYMFONY_DEBUG'] : 'false';

require __DIR__.'/../vendor/autoload.php';
if (PHP_VERSION_ID < 70000) {
    include_once __DIR__.'/../var/bootstrap.php.cache';
}

$kernel = new AppKernel($env, $debug);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
