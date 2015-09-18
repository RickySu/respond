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

$server = include __DIR__.'/../test.php';
$defaultConfig = [
    'listen' => '0.0.0.0',
    'port' => 8080,
];

$config = isset($server['config'])?$server['config']:[];

$config = array_merge($defaultConfig, $config);

$loop = isset($config['loop'])?$config['loop']:new \UVLoop();

$socket = isset($config['socket'])?$config['socket']:new Socket\UVServer($loop);

$socket->listen($config['listen'], $config['port']);

$httpServer = new Http\Server($socket);

foreach($server['app'] as $route)
{
    if($route[0] != 'ANY'){
        $httpServer->match($route[0], $route[1], $route[2]);
    }
    else{
        $httpServer->any($route[1]);
    }
}

$loop->run();