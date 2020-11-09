<?php


namespace Core\SystemCall;


use Core\Schedule\Schedule;
use Core\Task\Task;

interface SystemCallInterface
{
    //$task代表的是陷入了系统调用的任务,回调完必须将其放入任务队列。$schedule代表的是调度器实体
    public function execute(Task $task, Schedule $schedule);
}