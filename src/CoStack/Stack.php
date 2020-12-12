<?php

namespace Core\CoStack;

use Generator;
use SplStack;

class Stack
{
    /**
     * @param Generator $generator
     * @return void | Generator
     * 将一个裸协程栈化
     */
    public static function stackedCoroutine(Generator $generator)
    {
        //协程调用栈
        $stack = new SplStack();
        while (true) {
            $value = $generator->current(); //取得形参协程的返回值
            //发生了协程的嵌套调用
            if ($value instanceof Generator) {
                //先将当前生成器对象入栈
                $stack->push($generator);
                //把需要遍历的$generator替换成新的生成器
                //实际上是在做深度搜索
                $generator = $value;
                //从新的生成器开始做遍历
                continue;
            }

            //判断生成器的返回值是否是CoReturn对象
            $isReturnVal = Retval::isCoReturn($value);
            //如果生成器已经遍历完成[经过所有的yield,$generator->valid()返回false]
            //或者生成器的返回值是一个CoReturn对象
            if (!$generator->valid() || $isReturnVal) {
                //如果栈为空,说明所有搜索已经完成
                if ($stack->isEmpty()) {
                    return;
                }
                $generator = $stack->pop(); //此时从协程调用栈中弹出上一个入栈的生成器
                //如果生成器的返回值是一个CoReturn对象,则取出被包装的value透传到外部生成器
                //即使把子协程的返回值传递给父协程
                $generator->send($isReturnVal ? $value->getValue() : null);
                continue; //从父协程继续搜索
            }

            //如果生成器的返回值既不是新的生成器对象,也不是代表着终止流程的CoReturn对象
            //stack就承担了一个代理的角色，和外部进行通信
            //讲当前生成器的值返回给调用方[通过yield返回了一个新的生成器对象],使用$value接受调用方使用send传递的值
            //并将该值($value)透传到生成器的内部推动协程继续向前执行
            $generator->send(yield $generator->key() => $value);
        }
    }
}