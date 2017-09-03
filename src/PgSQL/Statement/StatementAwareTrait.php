<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Result\Tuple;
use Dazzle\PgSQL\Result\CommandResult;

trait StatementAwareTrait
{
    protected $sql;
    protected $params;

    public function getSQL()
    {
        return $this->sql;
    }

    public function getParams()
    {
        return $this->params?:[];
    }

    public function setSQL($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    public function setParams(array $params = [])
    {
        $this->params = $params;
        
        return $this;
    }
}