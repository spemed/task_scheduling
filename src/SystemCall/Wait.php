<?php


namespace Core\SystemCall;


use Core\Schedule\Schedule;
use Core\Task\Task;

class Wait implements SystemCallInterface
{

    private int $seconds;

    public function __construct(int $seconds = 0)
    {
        $this->seconds = $seconds;
    }

    public function execute(Task $task, Schedule $schedule)
    {
       $taskId = $task->getTaskId();
       $schedule->wait($taskId,$this->seconds);
       $schedule->inSchedule($task);
    }
}