<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

# FPM IP echo $_SERVER['SERVER_ADDR'];

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
//$whiteListedAddresses = ['10.10.10.20','127.0.0.1','fe80::1', '::1'];
//if ($dockerBridgeIp = getenv('DOCKER_BRIDGE_IP')) {
//    $whiteListedAddresses[] = $dockerBridgeIp;
//}
//
//if (!(in_array(@$_SERVER['REMOTE_ADDR'], $whiteListedAddresses))) {
//    header('HTTP/1.0 403 Forbidden');
//    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//}

/** @var \Composer\Autoload\ClassLoader $loader */
require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    (new Dotenv())->load(__DIR__.'/../.env');
}

$env = isset($_SERVER['SYMFONY_ENV'])? $_SERVER['SYMFONY_ENV'] : 'dev';
$debug = isset($_SERVER['SYMFONY_DEBUG'])? $_SERVER['SYMFONY_DEBUG'] : 'true';

if ($debug) {
    Debug::enable();
}

$kernel = new AppKernel('dev', true);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
