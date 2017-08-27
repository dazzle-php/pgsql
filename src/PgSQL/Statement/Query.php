<?php
namespace Dazzle\PgSQL\Statement;

class Query implements QueryStatement
{
    use StatementTrait;

    public function __construct($sql, array $params = [])
    {
        $this->sql = $sql;
        $this->params = $params;
    }

    public function getSQL()
    {
        return $this->sql;
    }

    public function getParams()
    {
        return $this->params;
    }
}