<?php

namespace Core\Poll;
interface PollInterface
{
    public function polling(array $read,array $write,array $error,?int $timeout):SocketCollection;
}