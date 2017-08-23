<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\PgSQL\Statement\QueryStatement;
use Dazzle\Promise\PromiseInterface;

interface ConnectorInterface
{
    /**
     * Execute an async query.
     *
     * @param string $sql
     * @param mixed[] $sqlParams
     * @return PromiseInterface
     */
    public function query($sql, $sqlParams = []);

    /**
     * Execute an async query and return number of affected rows.
     *
     * @param string $sql
     * @param mixed[] $sqlParams
     * @return PromiseInterface
     */
    public function execute($sql, $sqlParams = []);

    /**
     * Get connection resource
     * @return mixed
     */
    public function getStream();

    public function connect();

    public function getSock();
}