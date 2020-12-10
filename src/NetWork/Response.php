<?php


namespace EasySwoole\Rpc\NetWork;


class Response
{
    const STATUS_OK = 0;
    const STATUS_NODES_EMPTY = 1001;
    const STATUS_CONNECT_TIMEOUT = 1002;
    const STATUS_SERVER_TIMEOUT = 1003;
    const STATUS_SERVICE_NOT_EXIST = 2001;
    const STATUS_SERVICE_ACTION_NOT_FOUND = 2002;
    const STATUS_SERVICE_ERROR = 2003;
    const STATUS_ILLEGAL_PACKAGE = 2004;

    /** @var int $status */
    protected $status = self::STATUS_OK;
    /** @var mixed $result */
    protected $result;
    /** @var mixed $msg */
    protected $msg;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param $msg
     * @return $this
     */
    public function setMsg($msg): self
    {
        $this->msg = $msg;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $result
     * @return $this
     */
    public function setResult($result): self
    {
        $this->result = $result;
        return $this;
    }
}