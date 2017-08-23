<?php
namespace Dazzle\PgSQL\Statement;

interface Statement
{
    public function getResult();
    public function setResult($ret);
    public function getStatus();
}