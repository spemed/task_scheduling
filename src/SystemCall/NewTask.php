<?php


namespace Core\SystemCall;


use Core\Schedule\Schedule;
use Core\Task\Task;
use Generator;

class NewTask implements SystemCallInterface
{
    private Generator $coroutine;
    public function __construct(Generator $coroutine)
    {
        $this->coroutine = $coroutine;
    }

    public function execute(Task $task, Schedule $schedule)
    {
        $tid = $schedule->newTask($this->coroutine); //调用Schedule的newTask方法产生新任务,并获取任务id
        $task->setSendValue($tid); //恢复用户态时会获得新创建协程的id
        $schedule->inSchedule($task);
    }
}