<?php
namespace Dazzle\PgSQL\Statement;

interface TupleResultStatement extends Statement
{
    public function fetchColumn();
    public function fetchRow();
    public function fetchAll();
    public function fetchAssoc();
    public function fetchObject();
}