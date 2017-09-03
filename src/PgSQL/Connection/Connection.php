<?php
namespace Dazzle\PgSQL\Connection;

use Dazzle\Promise\Deferred;
use Dazzle\Promise\PromiseInterface;
use Dazzle\PgSQL\Transaction\Transaction;
use Dazzle\PgSQL\Connection\ConnectorInterface;

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

    public function execute($sql, $params = [])
    {
        return $this->connector->execute($sql, $params = []);
    }

    /**
     * @param $sql
     * @return PromiseInterface
     */
    public function prepare($sql)
    {
        return $this->connector->prepare($sql);
    }

    public function beginTransaction()
    {
        $transac = new Transaction($this->connector);

        return $transac->begin();
    }
}