<?php
declare(strict_types=1); // php 7 strict mode

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

// Note that the following line is replaced by the bake script on a per-environment basis
// for the INT and TEST environments
$appEnv = 'prod';
if (($appEnv === 'prod_int' || $appEnv === 'prod_test') && !empty($_GET['__scenario'])) {
    $appEnv .= '_fixture';
}
$kernel = new Kernel($appEnv, false);
//$kernel = new AppCache($kernel);


/*
 * X-BBC-EDGE-* headers are added by Varnish, we need to convert them into forward headers that Symfony can act upon
 * https://confluence.dev.bbc.co.uk/display/ta/Request+and+response+HTTP+headers+-+standardised+format+for+BBC+traffic+management+services
 */
if (isset($_SERVER['HTTP_X_BBC_EDGE_CLIENT_IP'])) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_X_BBC_EDGE_CLIENT_IP'];
}
if (isset($_SERVER['HTTP_X_BBC_EDGE_HOST'])) {
    $_SERVER['HTTP_X_FORWARDED_HOST'] = $_SERVER['HTTP_X_BBC_EDGE_HOST'];
}
if (isset($_SERVER['HTTP_X_BBC_EDGE_SCHEME'])) {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = $_SERVER['HTTP_X_BBC_EDGE_SCHEME'];
    $_SERVER['HTTP_X_FORWARDED_PORT']  = $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ? 443 : 80;
} else {
    $_SERVER['HTTP_X_FORWARDED_PORT'] = $_SERVER['REQUEST_SCHEME'] == 'https' ? 443 : 80;
}

// Covers our subnets in dev and live
Request::setTrustedProxies(['10.0.0.0/8'], Request::HEADER_X_FORWARDED_ALL);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
