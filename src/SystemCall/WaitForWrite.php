<?php


namespace Core\SystemCall;


use Core\Schedule\Schedule;
use Core\Task\Task;

class WaitForWrite implements SystemCallInterface
{
    private $socket;
    public function __construct($socket)
    {
        $this->socket = $socket;
    }
    public function execute(Task $task, Schedule $schedule)
    {
        $schedule->waitForWrite($this->socket,$task);
    }
}