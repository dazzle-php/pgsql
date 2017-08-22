<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\Promise\Deferred;

class Connection extends Deferred implements ConnectionInterface
{
    /**
     * @var ConnectorInterface
     */
    protected $connector;

    public function getConnector()
    {
        return $this->connector;
    }

    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function query($sql, $params = [])
    {
        return $this->connector->query($sql, $params);
    }

    public function connect()
    {
        return $this->connector->connect();
    }

    public function execute($sql, $params = [])
    {
//        $stmt = new \Statement($sql, $params);

//        return $this->connector->execute($stmt);
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