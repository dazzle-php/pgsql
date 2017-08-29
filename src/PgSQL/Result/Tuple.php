<?php

namespace Dazzle\PgSQL\Result;

class Tuple implements TupleResultStatement
{

    use ResultAwareTrait;

    public function __construct($ret)
    {
        $this->ret = $ret;
    }

    public function fetchColumn($filed = '')
    {
        return \pg_fetch_result($this->ret, 0, $filed);
    }

    public function fetchRow($filed = '')
    {
        return \pg_fetch_row($this->ret, 0);
    }

    public function fetchAll()
    {
        return \pg_fetch_all($this->ret);
    }

    public function fetchAssoc()
    {
        return \pg_fetch_assoc($this->ret);
    }

    public function fetchObject()
    {
        return \pg_fetch_object($this->ret);
    }

    public function getStatus()
    {
        return \pg_result_status($this->ret);
    }
}