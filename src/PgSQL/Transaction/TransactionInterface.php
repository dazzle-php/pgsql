<?php
namespace Dazzle\PgSQL\Transaction;

use Dazzle\Promise\PromiseInterface;
use Dazzle\Event\EventEmitterInterface;

interface TransactionInterface extends EventEmitterInterface
{
    /**
     * @return boolean
     */
    public function isOpen();

    /**
     * @param string $sql
     * @param array $sqlParams
     * @return PromiseInterface
     */
    public function query($sql, array $sqlParams = []);

    /**
     * @param string $sql
     * @param array $sqlParams
     * @return PromiseInterface
     */
    public function execute($sql, array $sqlParams = []);

    /**
     * @return PromiseInterface
     */
    public function commit();

    /**
     * @return PromiseInterface
     */
    public function rollback();
}
