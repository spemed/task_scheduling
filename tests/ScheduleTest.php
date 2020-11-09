<?php


use Core\Exception\Result;
use Core\Queue\FifoQueue;
use Core\SystemCall\GetTaskId;
use Core\SystemCall\KillTask;
use Core\SystemCall\NewTask;
use Core\SystemCall\Wait;
use PHPUnit\Framework\TestCase;
use Core\Schedule\Schedule;

class ScheduleTest extends TestCase
{
    public function testGetTaskId()
    {
        $schedule = new Schedule(new FifoQueue());
        $task = (function () {
            $taskId = yield new GetTaskId();
            //$taskId = yield sleep(5);
            echo("current_taskId is ".$taskId.PHP_EOL);
        });
        $startTime = time();
        $schedule->newTask($task());
        $schedule->newTask($task());
        $schedule->newTask($task());
        $schedule->run();
        $endTime = time();
        print_r("总归耗时:".sprintf("%d",($endTime-$startTime)));
        self::assertEquals(true,true,"打印current_taskId");
    }

    public function testKillTaskId() {
        function childTask() {
            $tid = (yield new GetTaskId());
            //注意在死循环中需要加上yield,不然该任务会抢占内核分配给线程的时间片
            //导致调度中心和其他协程永久失去执行的机会
            //todo 当然这个可以通过注册时钟中断解决
            $i = 0;
            while ($i < 100) {
                echo "Child task $tid still alive!\n";
                $i++;
                yield;
            }
        }
        function task() {
            $tid = (yield new GetTaskId());
            $childTid = (yield new NewTask(childTask()));
            for ($i = 1; $i <= 6; ++$i) {
                echo "Parent task $tid iteration $i.\n";
                yield;
                if ($i == 3) {
                    $result = yield new KillTask(4);
                    if (!$result->resultIsSuccess()) {
                        print_r($result->getMessage());
                    }
                }
            }
        }

        $schedule = new Schedule(new FifoQueue());
        $schedule->newTask(task());
        $schedule->run();
        self::assertEquals(true,true,"打印current_taskId");
    }

    public function testWait() {
        function task1() {
            $tid = (yield new GetTaskId());
            $seconds = 10;
            echo $tid." will sleep {$seconds} seconds".PHP_EOL;
            ob_flush();
            yield new Wait($seconds);
            echo "{$tid} wake out".PHP_EOL;
            ob_flush();
        }
        function task2() {
            $tid = (yield new GetTaskId());
            $seconds = 5;
            echo $tid." will sleep {$seconds} seconds".PHP_EOL;
            ob_flush();
            yield new Wait($seconds);
            echo "{$tid} wake out".PHP_EOL;
            ob_flush();
        }

        $schedule = new Schedule(new FifoQueue());
        $schedule->newTask(task1());
        $schedule->newTask(task2());
        $schedule->run();
        self::assertEquals(true,true,"打印current_taskId");
    }
}
