<?php

namespace Dazzle\PgSQL\Statement;

class CommandResult implements CommandResultStatement
{
    use StatementTrait;

    public function getAffectedRows()
    {
        return \pg_affected_rows($this->ret);
    }

    public function getLastId()
    {
        return \pg_last_oid($this->ret);
    }
}