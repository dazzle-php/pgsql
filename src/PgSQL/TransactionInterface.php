<?php

namespace Dazzle\PgSQL;

use Dazzle\Event\EventEmitterInterface;

interface TransactionInterface extends EventEmitterInterface
{
    public function isOpen();

    public function commit();

    public function rollback();
}
