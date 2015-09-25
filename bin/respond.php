#!/usr/bin/env php
<?php
use Respond\Socket;
use Respond\Http;

$loader = include __DIR__.'/../vendor/autoload.php';

if (defined('HHVM_VERSION')) {
    $psr4Prefix = $loader->getPrefixesPsr4();
    foreach($psr4Prefix['WebUtil\\'] as $index => $path){
        $psr4Prefix['WebUtil\\'][$index] = $path.'hack';
    }
    $loader->setPsr4('WebUtil\\', $psr4Prefix['WebUtil\\']);
}

$app = include $_SERVER['argv'][1];

if(!($app instanceof \Respond\App\WebApp)){
    die;
}

if(!($loop = $app->getLoop())){
    $loop = new \UVLoop();
    $app->setLoop($loop);
}

$socket = new Socket\UVServer($loop);
$socket->listen($app->getHost(), $app->getPort());

$httpServer = new Http\Server($socket);

foreach($app->getRoutes() as $route)
{
    $httpServer->match($route[0], $route[1], $route[2]);
}

$httpServer->any($app->getDefautCallback());

$loop->run();