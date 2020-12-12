<?php

namespace Core\Socket;

use Core\CoStack\Retval;
use Core\SystemCall\WaitForRead;
use Core\SystemCall\WaitForWrite;

class CoSocket
{
    protected $socket;
    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    public function accept()
    {
        //把被动套接字投入读的等待队列
        yield new WaitForRead($this->socket);
        yield Retval::wrapper(@socket_accept($this->socket));
    }

    public function write(string $buffer,int $length)
    {
        yield new WaitForWrite($this->socket);
        yield Retval::wrapper(@socket_write($this->socket,$buffer,$length));
    }

    public function read(int $length)
    {
        yield new WaitForRead($this->socket);
        yield Retval::wrapper(@socket_read($this->socket,$length));
    }

    public function close()
    {
        socket_close($this->socket);
    }

    /**
     * @return mixed
     */
    public function getSocket()
    {
        return $this->socket;
    }
}