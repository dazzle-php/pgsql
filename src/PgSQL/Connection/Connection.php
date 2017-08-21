<?php

namespace Dazzle\PgSQL\Support\Connection;


use Dazzle\Promise\Deferred;

class Connection extends Deferred implements ConnectionInterface
{
    public function __construct(ConnectorInterface $conn, $canceller = null)
    {
        parent::__construct($canceller);
        $this->conn = $conn;
    }

    public function query($sql, $params = [])
    {
        return $this->conn->query($sql, $params);
    }

    public function execute($sql, $params = [])
    {
        return $this->conn->execute($sql, $params);
    }

    /**
     * @param $sql
     * @param array $params
     * @return \QueryStatement
     */
    public function prepare($sql, $params = [])
    {
        return new \Statement($sql, $params);
    }
}