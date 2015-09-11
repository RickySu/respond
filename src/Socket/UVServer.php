<?php
namespace Respond\Socket;

use Evenement\EventEmitter;

class UVServer extends EventEmitter implements ServerInterface
{
    private $socket;
    private $isSSL = false;

    public function __construct($loop)
    {
        $this->socket = new \UVTcp($loop);
    }

    public function getServerParam()
    {
        return array(
            'Https' => $this->isSSL,
            'Server-Name' => $this->socket->getSockname(),
            'Server-Port' => $this->socket->getSockport(),
        );
    }

    public function listen($host, $port)
    {
        $this->socket->listen($host, $port, function($socket){
            $this->emit('connect', array(
                $this,
            ));
        });
    }

    public function accept()
    {
        $client = new UVSocket($this->socket->accept());
        $client->initCallback();
        return $client;
    }

    public function close()
    {
        $this->close();
    }

}