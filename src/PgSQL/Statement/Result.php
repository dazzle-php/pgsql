<?php

namespace Dazzle\PgSQL\Statement;

class Result implements CommandResultStatement,TupleResultStatement,ResultStatement
{
    protected $result;

    public function __construct($ret)
    {
        $this->result = $ret;
    }

    public function getAffectedRows()
    {
        return \pg_affected_rows($this->result);
    }

    public function getLastId()
    {
        return \pg_last_oid($this->result);
    }

    public function fetchColumn($filed = '')
    {
        return \pg_fetch_result($this->result, 0, $filed);
    }

    public function fetchRow($filed = '')
    {
        return \pg_fetch_row($this->result, 0);
    }

    public function fetchAll()
    {
        return \pg_fetch_all($this->result);
    }

    public function fetchAssoc()
    {
        return \pg_fetch_assoc($this->result);
    }

    public function fetchObject()
    {
        return \pg_fetch_object($this->result);
    }

    public function getStatus()
    {
        return \pg_result_status($this->result);
    }
}