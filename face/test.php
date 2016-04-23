<?php

require_once __DIR__.'/vendor/autoload.php';


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new \Slim\App();
// Define app routes...
$app->get('/', function ($req,$res,$args){
	echo 'test';	
})->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    // Use the PSR 7 $request object
    return $next($request, $response);
});

$app->run();