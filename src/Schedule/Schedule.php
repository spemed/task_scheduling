<?php

namespace Core\Schedule;
use Core\Queue\Queue;
use Core\SystemCall\SystemCallInterface;
use Core\Task\Task;
use Generator;
use Core\Exception\SystemException;

class Schedule
{
    protected Queue $queue; //任务队列
    protected array $scheduleTable = []; //调度表
    protected int $maxTaskId = 0; //taskId,每次生成新的task自动加1
    protected array $sleepTable = []; //休眠表
    protected array $usingTimeTable = []; //任务使用时间调度

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function newTask(Generator $generator):int
    {
        //获得当前系统中的taskId
        $taskId = ++$this->maxTaskId;
        $task = new Task($taskId,$generator);
        //使用taskId标识每一个task,同时task内也要有属性标记自己的id,这是非常经典的类的设计
        $this->scheduleTable[$taskId] = $task;
        //初始化休眠表
        $this->sleepTable[$taskId] = null;
        //初始化最近一次调度时间记录
        $this->usingTimeTable[$taskId] = null;
        $this->inSchedule($task);
        return $taskId;
    }

    /**
     * @param Task $task
     * 把任务列表加入任务队列中
     * 为什么需要单独拆分一个方法？
     * 因为inSchedule这个方法日后是有可能扩展的
     * 在任务入队的时候可能需要做额外的操作,并不一定是直接加入任务队列的队尾
     * 同时inSchedule需要暴露给系统调用对象,为了封装性不适合把任务队列直接暴露给其他对象
     */
    public function inSchedule(Task $task)
    {
        $this->queue->enqueue($task);
    }

    /**
     * 调度器开始调度
     */
    public function run()
    {
        //如果任务队列非空
        while ($this->queue->isEmpty() === false) {
            $task = $this->queue->dequeue(); //从任务队列中弹出一个元素
            //如果该任务已经不在任务统计表中
            if (!isset($this->scheduleTable[$task->getTaskId()])) {
                continue;
            }
            //该任务尚未到达唤醒时间
            $sleepIn = $this->sleepTable[$task->getTaskId()];
            if (!is_null($sleepIn) && time() < $sleepIn) {
                //尚未到达唤醒时间
                $this->inSchedule($task);
                continue;
            }
            //如果任务尚未执行完毕
            if ($task->isFinished() === false) {
                $this->usingTimeTable[$task->getTaskId()] = time();
                //启动任务,直到下一次中断到达
                $systemCaller = $task->run();
                if ($systemCaller instanceof SystemCallInterface) {
                    $systemCaller->execute($task,$this);
                    //todo 此处为何从系统调用中返回需要continue呢？
                    //todo 为何需要在每个系统调用中把任务重新加入调度计划,而不是在调度中心对象中统一加入？思考一下？
                    // 是否是某些系统调用执行完可以马上恢复任务,而不是重新让任务加入调度呢？
                    continue;
                }
                $this->inSchedule($task); //将任务重新加入调度列表
            } else {
                //如果任务调用结束,需要把任务从tcb[这里是scheduleTable]中清除
                $this->destroyTask($task->getTaskId());
            }
        }
    }

    /**
     * @param int $tid
     * @throws SystemException
     * 杀死协程
     */
    public function killTask(int $tid)
    {
        //todo 可以优化为软删除,给task打标记,等待被调度到的时候在删除
        //todo 不然每次删除都是o(n)的时间复杂度
        //不在任务调度表中
        if (!isset($this->scheduleTable[$tid])) {
            throw new SystemException("no such task");
        }
        //删除任务信息
        unset($this->scheduleTable[$tid]);
    }

    /**
     * @param int $taskId
     * @param int $seconds
     * 协程休眠
     */
    public function wait(int $taskId,int $seconds)
    {
        $this->sleepTable[$taskId] = time() + $seconds;
    }

    /**
     * @param int $tid
     * @descpriton 销毁任务的相关信息[调度表,时间表等等]
     */
    public function destroyTask(int $tid)
    {
        unset($this->scheduleTable[$tid]);
        unset($this->usingTimeTable[$tid]);
        unset($this->sleepTable[$tid]);
    }
}