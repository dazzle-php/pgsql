<?php

class Result implements CommandResultStatement,TupleResultStatement,ResultStatement
{
    public function __construct($pgSqlRet)
    {
        $this->result = $pgSqlRet;
    }

    public function getAffectedRows()
    {
        // TODO: Implement getAffectedRows() method.
    }

    public function getLastId()
    {
        // TODO: Implement getLastId() method.
    }

    public function fetchColumn()
    {
        // TODO: Implement fetchColumn() method.
    }

    public function fetchRow()
    {
        // TODO: Implement fetchRow() method.
    }

    public function fetchAll()
    {
        // TODO: Implement fetchAll() method.
    }

    public function fetchAssoc()
    {
        // TODO: Implement fetchAssoc() method.
    }

    public function fetchObject()
    {
        // TODO: Implement fetchObject() method.
    }

    public function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

}