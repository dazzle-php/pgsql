<?php
namespace Dazzle\PgSQL\Result;

class CommandResult implements CommandResultStatement
{
    use ResultAwareTrait;

    public function __construct($ret)
    {
        $this->ret = $ret;
    }
    
    public function getAffectedRows()
    {
        return \pg_affected_rows($this->ret);
    }

    public function getLastId()
    {
        return \pg_last_oid($this->ret);
    }
}