<?php

use Symfony\Component\HttpFoundation\Request;
use Dotenv\Dotenv;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';
$dotenv = new Dotenv(__DIR__ . '/../');
if(file_exists(".env")) {
    $dotenv->load();
}

$env = isset($_SERVER['SYMFONY_ENV'])? $_SERVER['SYMFONY_ENV'] : 'prod';
$debug = isset($_SERVER['SYMFONY_DEBUG'])? $_SERVER['SYMFONY_DEBUG'] : 'false';

$kernel = new AppKernel($env, (bool)$debug);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
