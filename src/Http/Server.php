<?php
namespace Respond\Http;

use WebUtil\Http\Response\Response;
use WebUtil\Http\Request\ServerRequest;
use WebUtil\Route\RouteFactory;
use Respond\Socket\ServerInterface as SocketServerInterface;

class Server
{
    protected $socketServer;
    protected $defaultRequest;
    protected $route;
    protected $anyCallback;
    protected $serverPram;
    protected $nRoutes = 0;

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
            $client->on('error', function($client){
                $client->shutdown();
            });
            $client->on('shutdown', function($client){
                unset($client->parser);
                $client->close();
            });
        });
        $this->route = RouteFactory::create();
    }

    protected function resetClient($client)
    {
        $client->reset();
        $client->parser->reset();
    }

    public function match($method, $pattern, $callback)
    {
        $this->nRoutes++;
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
        $parser = \WebUtil\Parser\RequestFactory::create();
        $parser
            ->setOnParsedCallback(function($data) use($client){
                $request = ServerRequest::createFromArray($data, $this->serverPram);
                $callback = $this->anyCallback;
                if($this->nRoutes){
                    $match = $this->route->match($request->getMethod(), $request->getUri()->getPath());
                    if($match){
                        foreach($match[1] as $key => $value){
                            $request->withAttribute($key, $value);
                        }
                        $callback = $match[0];
                    }
                }
                $this->serverRequest($client, $request, $callback);
            });
        $client->parser = $parser;
    }

    protected function feedHttpParser($client, $data)
    {
        $client->parser->feed($data);
    }

    protected function serverRequest($client, ServerRequest $request, $callback)
    {
        $response = $callback($request);

        if(!($response instanceof Response)){
            $response = new Response($response);
        }

        $keepAlive = $request->isKeepAlive();
        $response->withKeepAlive($keepAlive);
        $client->write($response->getOutput());
        if($keepAlive){
            $this->resetClient($client);
            return;
        }
        $client->shutdown();
    }

}