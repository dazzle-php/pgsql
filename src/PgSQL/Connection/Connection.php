<?php

namespace Dazzle\PgSQL\Connection;


use Dazzle\Promise\Deferred;

class Connection extends Deferred implements ConnectionInterface
{
    protected $connector;

    public function __construct(ConnectorInterface $connector, $canceller = null)
    {
        parent::__construct($canceller);
        $this->connector = $connector;
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function query($sql, $params = [])
    {
        return $this->connector->query($sql, $params);
    }

    public function execute($sql, $params = [])
    {
        $stmt = new \Statement($sql, $params);

        return $this->connector->execute($stmt);
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