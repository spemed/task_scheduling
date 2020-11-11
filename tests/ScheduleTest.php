<?php


use Core\Exception\Result;
use Core\Poll\Poll;
use Core\Queue\FifoQueue;
use Core\SystemCall\Fork;
use Core\SystemCall\GetTaskId;
use Core\SystemCall\KillTask;
use Core\SystemCall\NewTask;
use Core\SystemCall\Wait;
use Core\SystemCall\waitForRead;
use Core\SystemCall\waitForWrite;
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

    public function testFork()
    {
        function task() {
            $tid = (yield new GetTaskId());
            print_r("my tid is ".$tid.PHP_EOL);
            ob_flush();
            $tid = (yield new Fork());
            if (0 == $tid) {
                print_r("我是被fork的子进程".PHP_EOL);
            } else {
                print_r("我是父进程,我fork出来的子进程pid=".$tid.PHP_EOL);
            }
        }

        $schedule = new Schedule(new FifoQueue());
        $schedule->newTask(task());
        $schedule->run();
        self::assertEquals(true,true,"打印current_taskId");
    }

    public function testNotBlockHttpServer()
    {
        function server($port) {
            echo "Starting server at port $port...\n";
            ob_flush();
            $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
            if (!$socket) throw new Exception($errStr, $errNo);
            stream_set_blocking($socket, 1); //同步非阻塞io
            while (true) {
                yield new waitForRead($socket);
                $clientSocket = stream_socket_accept($socket, 0);
                yield new NewTask(handleClient($clientSocket));
            }
        }
        function handleClient($socket) {
            yield new WaitForRead($socket);
            $data = fread($socket, 8192);
            $msg = <<<START
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>Hello world</h1>
</body>
</html>
START;

            $msgLength = strlen($msg);
            $response = <<<START
HTTP/1.1 200 OK\r
Content-Type: text/html\r
Content-Length: $msgLength\r
Connection: close\r
\r\n
$msg
START;
            yield new WaitForWrite($socket);
            fwrite($socket, $response);
            fclose($socket);
}
            $scheduler = new Schedule(new FifoQueue(),new Poll());
            $scheduler->newTask(server(8888));
            $scheduler->run();
    }
}

