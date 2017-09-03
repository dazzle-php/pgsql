<?php
namespace Dazzle\PgSQL\Connection;

use Dazzle\PgSQL\Statement\Statement;
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
     * @return resource
     */
    public function getStream();

    /**
     * @return mixed
     */
    public function connect();

    /**
     * @return resource
     */
    public function getSock();

    /**
     * @param Statement $stmt
     * @return
     */
    public function appendQuery(Statement $stmt);
}