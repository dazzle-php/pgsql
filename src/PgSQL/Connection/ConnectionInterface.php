<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\Promise\PromiseInterface;

interface ConnectionInterface
{
    /**
     * @param $sql
     * @param array $params
     * @return PromiseInterface
     */
    public function query($sql, $params = []);

    /**
     * @param $sql
     * @param array $params
     * @return PromiseInterface
     */
    public function execute($sql, $params = []);

    /**
     * @param $sql
     * @return PromiseInterface
     */
    public function prepare($sql);
}