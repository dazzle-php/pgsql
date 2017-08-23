<?php

namespace Dazzle\PgSQL\Statement;

trait StatementTrait
{
    protected $ret;

    public function getResult()
    {
        return $this->ret;
    }

    public function setResult($ret)
    {
        $this->$ret = $ret;

        return $this;
    }

    public function getStatus()
    {
        return \pg_result_status($this->ret);
    }
}