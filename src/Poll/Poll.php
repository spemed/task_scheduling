<?php


namespace Core\Poll;


use Core\Exception\SystemException;

class Poll implements PollInterface
{
    /**
     * @param array $read
     * @param array $write
     * @param array $error
     * @param int $timeout
     * @return SocketCollection
     * @throws SystemException
     */
    public function polling(array $read,array $write,array $error,?int $timeout = 0): SocketCollection
    {
        $result = stream_select($read,$write,$error,$timeout);
        if (!$result) {
            throw new SystemException("failed to stream_select");
        }
        return new SocketCollection($read,$write,$error);
    }
}