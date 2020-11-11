<?php


namespace Core\SystemCall;


use Core\Schedule\Schedule;
use Core\Task\Task;

class Fork implements SystemCallInterface
{

    public function execute(Task $task, Schedule $schedule)
    {
        $newTask = $schedule->fork($task);
        $newTask->setSendValue(0); //新协程返回0
        $task->setSendValue($newTask->getTaskId()); //老协程返回新协程的id
        $schedule->inSchedule($newTask);
        $schedule->inSchedule($task);
    }
}