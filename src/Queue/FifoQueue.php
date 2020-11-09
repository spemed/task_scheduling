<?php


namespace Core\Queue;


use Core\Task\Task;
use phpDocumentor\Reflection\Types\Mixed_;
use SplQueue;

/**
 * Class FifoQueue
 * @package Core\Queue
 * 基于fifo的任务队列
 * 实际上使用了适配器模式,通过接口+依赖注入的形式适配已有的类
 */
class FifoQueue implements Queue
{
    private SplQueue $queue;
    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function enqueue($data)
    {
        $this->queue->enqueue($data);
    }

    public function isEmpty(): bool
    {
       return $this->queue->isEmpty();
    }

    public function dequeue():Task
    {
        return $this->queue->dequeue();
    }
}