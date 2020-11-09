<?php


namespace Core\Exception;


class Result
{
    CONST ERR_SUCCESS = 0;
    CONST ERR_FAILED = 1;
    private int $code;
    private string $message;

    public function __construct(int $code,string $message = "")
    {
        $this->code = $code;
        $this->message = $message;
    }

    public static function success():Result
    {
        return new Result(self::ERR_SUCCESS);
    }

    public static function error(string $message,int $code = self::ERR_FAILED):Result
    {
        return new Result($code,$message);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function resultIsSuccess():bool
    {
        return $this->code === self::ERR_SUCCESS;
    }
}

