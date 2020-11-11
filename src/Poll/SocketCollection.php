<?php


namespace Core\Poll;


class SocketCollection
{
    private array $read;
    private array $write;
    private array $error;

    public function __construct(array $read=[],array $write=[],array $error=[])
    {
        $this->read = $read;
        $this->write = $write;
        $this->error = $error;
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function getWrite(): array
    {
        return $this->write;
    }

    /**
     * @return array
     */
    public function getRead(): array
    {
        return $this->read;
    }
}