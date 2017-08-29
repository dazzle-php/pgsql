<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\Promise\PromiseInterface;

interface PreparedStatement extends Statement
{
    /**
     * @return PromiseInterface
     */
    public function execPreparedStmt();
}