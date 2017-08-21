<?php

namespace Dazzle\PgSQL\Support\Connection;


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
     * Get readonly sock for watching
     * @return mixed
     */
    public function getSock();

    /**
     * Get connection resource
     * @return mixed
     */
    public function getStream();
}