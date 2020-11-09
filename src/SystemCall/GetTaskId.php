<?php


namespace Core\SystemCall;


use Core\Task\Task;
use Core\Schedule\Schedule;

/**
 * Class GetTaskId
 * @package Core\SystemCall
 * 获取任务id的系统调用
 */
class GetTaskId implements SystemCallInterface
{
    public function execute(Task $task, Schedule $schedule) {
        $task->setSendValue($task->getTaskId()); //把taskId传入协程任务中
        $schedule->inSchedule($task); //让task重新参与调度
    }
}