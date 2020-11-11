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
     */
    public function polling(array $read,array $write,array $error,?int $timeout): SocketCollection
    {
        //忽略传递空数组时的错误
        $result = @stream_select($read,$write,$error,$timeout);
        if (!$result) {
            return new SocketCollection();
            //throw new SystemException("failed to stream_select");
        }
        return new SocketCollection($read,$write,$error);
    }
}