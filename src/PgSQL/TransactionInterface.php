<?php

namespace Dazzle\PgSQL;

use Dazzle\Event\EventEmitterInterface;

interface TransactionInterface extends SQLClientInterface, EventEmitterInterface
{
    public function isOpen();

    public function commit();

    public function rollback();
}
