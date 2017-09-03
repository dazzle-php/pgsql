<?php
namespace Dazzle\PgSQL\Transaction;

use Dazzle\Event\EventEmitterInterface;
use Dazzle\PgSQL\Statement\StatementHandler;

interface TransactionInterface extends EventEmitterInterface
{
    public function isOpen();

    public function commit();

    public function rollback();
}
