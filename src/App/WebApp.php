<?php
namespace Respond\App;

class WebApp
{
    protected $loop;
    protected $host = '127.0.0.1';
    protected $port = 8000;
    protected $routes = [];
    protected $defaultCallback;

    public function __construct()
    {
        $this->defaultCallback = function(){};
    }

    public function setLoop(\UVLoop $loop)
    {
        $this->loop = $loop;
        return $this;
    }

    public function getLoop()
    {
        return $this->loop;
    }

    public function listen($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function request($method, $route, $callback)
    {
        $this->routes[] = array($method, $route, $callback);
        return $this;
    }

    public function defaultRequest($callback)
    {
        $this->defaultCallback = $callback;
        return $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getDefautCallback()
    {
        return $this->defaultCallback;
    }

    public function __call($name, $arguments)
    {
        if(in_array($name, array('get', 'post', 'put', 'delete', 'head', 'info'))){
            return $this->request([strtoupper($name)], $arguments[0], $arguments[1]);
        }
    }

}
