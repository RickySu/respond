<?php
namespace Respond\Socket;

use Evenement\EventEmitterInterface;

interface ServerInterface extends EventEmitterInterface
{
    public function accept();
    public function close();
    public function listen($host, $port);
}