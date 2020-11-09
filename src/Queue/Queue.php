<?php


namespace Core\Queue;


use Core\Task\Task;

interface Queue
{
    public function enqueue($data);
    public function dequeue():Task;
    public function isEmpty():bool ;
}