<?php
namespace Respond\Socket;

use Evenement\EventEmitter;

class UVSocket extends EventEmitter
{
    private $socket;

    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function initCallback()
    {
        $this->socket->setCallback(
            function($socket, $data){
                $this->emit('recv', array(
                    $this,
                    $data,
                ));
            },
            function($socket, $status){
                $this->emit('write', array(
                    $this,
                    $status,
                ));
            },
            function($socket){
                $this->emit('error', array(
                    $this,
                ));
            }
        );
    }

    public function write($data)
    {
        return $this->socket->write($data);
    }

    public function close()
    {
        $this->socket->close();
        $this->removeAllListeners();
    }

    public function getSockname()
    {
        return $this->socket->getSockname();
    }

    public function getSockport()
    {
        return $this->socket->getSockport();
    }

    public function getPeername()
    {
        return $this->socket->getPeername();
    }

    public function getPeerport()
    {
        return $this->socket->getPeerport();
    }
}