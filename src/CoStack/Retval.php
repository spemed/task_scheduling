<?php

namespace Core\CoStack;

class Retval
{
    //包装了value的CoReturn对象,用于告知CoStack应该把内层协程的返回结果传递给外层协程作为其运行参数
    protected $value;
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return Retval
     * 把value包装成一个wrapper对象,并返回
     */
    public static function wrapper($value):Retval
    {
        return new Retval($value);
    }

    /**
     * @param $retval
     * @return bool
     * 判断返回一个协程的返回值是否是一个CoReturn对象
     */
    public static function isCoReturn($retval):bool
    {
        return !is_null($retval) && $retval instanceof self;
    }

}