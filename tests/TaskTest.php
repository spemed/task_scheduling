<?php

namespace Test;

use Core\Task\Task;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Report\PHP;

function a(){
    $number = yield 1;
    print_r($number.PHP_EOL);
    yield $number;
}

class TaskTest extends TestCase
{
    private Task $task;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->task = new Task("123",a());
    }

    public function testRunWithOnce(){
        self::assertEquals(1, $this->task->run(),"failed to get yield");
    }

    public function testRunWithSecond(){
        self::assertEquals(1, $this->task->run(),"failed to get yield");
        $this->task->setSendValue(2);
        self::assertEquals(2, $this->task->run(),"failed to get yield");

    }
}
