<?php
namespace Respond\Http;

use Evenement\EventEmitter;
use WebUtil\Http\Response\Response;
use WebUtil\Http\Request\ServerRequest;
use WebUtil\Route\RouteFactory;
use Respond\Socket\ServerInterface as SocketServerInterface;
use WebUtil\Parser;

class Server
{
    protected $socketServer;
    protected $defaultRequest;
    protected $route;
    protected $anyCallback;
    protected $serverPram;

    public function __construct(SocketServerInterface $socketServer, $serverParam = array())
    {
        $this->socketServer = $socketServer;
        $this->serverPram = array_merge($socketServer->getServerParam(), $serverParam);
        $this->socketServer->on('connect', function(SocketServerInterface $server){
            $client = $server->accept();
            $this->initHttpParser($client);
            $client->on('recv', function($client, $data){
                $this->feedHttpParser($client, $data);
            });
            $client->on('write', function($client){
                unset($client->parser);
                $client->close();
            });
        });
        $this->route = RouteFactory::create();
    }

    public function match($method, $pattern, $callback)
    {
        $this->route->addRoute($method, $pattern, $callback);
        return $this;
    }

    public function any($callback)
    {
        $this->route->compile();
        $this->anyCallback = $callback;
    }

    protected function initHttpParser($client)
    {
//        $parser = new Parser\RequestHeaderParser();
        $parser = new \WebUtil\Parser\HttpParser();
        $parser
  //          ->setNextHook(new Parser\RequestParamParser())
            //->setNextHook(new Parser\RequestMultipartAsyncParser())
            ->setOnParsedCallback(function($data) use($client){
                $request = ServerRequest::createFromArray($data, $this->serverPram);
                $match = $this->route->match($request->getMethod(), $request->getUri()->getPath());
                $callback = $this->anyCallback;
                if($match){
                    foreach($match[1] as $key => $value){
                        $request->withAttribute($key, $value);
                    }
                    $callback = $match[0];
                }
                $this->serverRequest($client, $request, $callback);
            });
        $client->parser = $parser;
    }

    protected function feedHttpParser($client, $data)
    {
        $client->parser->feed($data);
    }

    protected function serverRequest($client, $request, $callback)
    {
        $response = $callback($request);
        if(!($response instanceof Response)){
            $response = new Response($response);
        }
        $client->write($response);
    }
}