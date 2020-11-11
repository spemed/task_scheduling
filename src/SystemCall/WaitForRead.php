<?php


namespace Core\SystemCall;


namespace Core\SystemCall;


use Core\Schedule\Schedule;
use Core\Task\Task;

class WaitForRead implements SystemCallInterface
{
    private $socket;
    public function __construct($socket)
    {
        $this->socket = $socket;
    }
    public function execute(Task $task, Schedule $schedule)
    {
        $schedule->waitForRead($this->socket,$task);
    }
}