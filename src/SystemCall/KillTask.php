<?php


namespace Core\SystemCall;


use Core\Exception\Result;
use Core\Exception\SystemException;
use Core\Schedule\Schedule;
use Core\Task\Task;
use Exception;

class KillTask implements SystemCallInterface
{
    private int $taskId;

    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
    }

    public function execute(Task $task, Schedule $schedule)
    {
        try {
            $schedule->killTask($this->taskId);
            $result = Result::success();
            $task->setSendValue($result);
        } catch (SystemException $exception) {
            $msg = $exception->getMessage();
            $result = Result::error($msg);
            $task->setSendValue($result);
        } catch (Exception $exception) {
            $msg = "system error";
            $result = Result::error($msg);
            $task->setSendValue($result);
        } finally {
            $schedule->inSchedule($task);
        }
    }
}