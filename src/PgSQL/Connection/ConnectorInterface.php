<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\PgSQL\Statement\QueryStatement;

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
     * @param QueryStatement $stmt
     * @return PromiseInterface
     */
    public function execute(QueryStatement $stmt);

    /**
     * Get connection resource
     * @return mixed
     */
    public function getStream();

    public function connect();

    public function getSock();
}