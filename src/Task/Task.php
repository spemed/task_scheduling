<?php /** @noinspection PhpMissingFieldTypeInspection */


namespace Core\Task;

use Generator;

/**
 * Class Task
 * @package Core\Task
 * @description 包装了协程处理流程的任务实体
 * 使用的php是nts[非线程安全]版本,不考虑线程安全问题
 */
class Task
{
    protected int $taskId; //任务id,任务的唯一标识
    protected Generator $coroutine; //协程的调度实体,实际上是一个迭代器
    protected $sendValue; //需要向迭代器中传递的值
    protected $isFirstExecute = true; //第一次执行时,仅返回协程中yield的值
    protected $runtimeError = ""; //运行出错时的系统信息

    public function __construct(string $taskId,Generator $coroutine)
    {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }

    /**
     * @param $sendValue
     * 设置每次执行任务时需要发送的值
     */
    public function setSendValue($sendValue)
    {
        $this->sendValue = $sendValue;
    }

    /**
     * @return string
     * 返回taskId
     */
    public function getTaskId():string
    {
        return  $this->taskId;
    }

    //任务是否执行结束
    public function isFinished():bool
    {
        return !$this->coroutine->valid();
    }

    /**
     * 协程开始运行,往协程中发送sendValue使得协程继续工作
     * 并在下一个yield暂停并返回yield的右值
     */
    public function run()
    {
        if ($this->isFirstExecute) {
            $this->isFirstExecute = false;
            return $this->coroutine->current();
        } else {
            $sendValue = $this->sendValue;
            $this->sendValue = null;
            return $this->coroutine->send($sendValue);
        }
    }

    /**
     * @param string $runtimeError
     */
    public function setRuntimeError(string $runtimeError): void
    {
        $this->runtimeError = $runtimeError;
    }

    /**
     * @return string
     */
    public function getRuntimeError(): string
    {
        return $this->runtimeError;
    }

    /**
     * @return Generator
     */
    public function getCoroutine(): Generator
    {
        return $this->coroutine;
    }
}