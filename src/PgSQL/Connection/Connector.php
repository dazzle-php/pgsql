<?php
namespace Dazzle\PgSQL\Connection;

use Dazzle\Promise\Deferred;
use Dazzle\PgSQL\Statement\Query;
use Dazzle\PgSQL\Statement\PrepareQuery;
use Dazzle\PgSQL\Statement\Statement;
use Dazzle\Throwable\Exception;

class Connector implements ConnectorInterface
{
    protected $connected;

    protected $queryQueue;

    protected $retQueue;

    /**
     * @var Connection $conn
     */
    protected $conn;

    protected $stream;

    public function __construct($config)
    {
        $this->stream = pg_connect($config, \PGSQL_CONNECT_ASYNC|\PGSQL_CONNECT_FORCE_NEW);
        $this->conn = new Connection();
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function getSock()
    {
        return \pg_socket($this->stream);
    }

    public function getConnection()
    {
        //rfc: could passed stream to each connection,add connection resolver and pool
        $connection = $this->conn;
        $promise = $this->conn->getPromise();

        return $promise
               ->success(function (ConnectorInterface $connector) use ($connection) {
                   $connection->setConnector($connector);

                   return $connection;
               });
    }

    public function connect()
    {
        switch ($polled = $this->poll()) {
            case \PGSQL_POLLING_FAILED:
                return;
            case \PGSQL_POLLING_OK:
                if ($this->isConnected() != true) {
                    $this->connected = true;
                    $this->conn->resolve($this);

                    return;
                }
                $retRsrc = \pg_get_result($this->stream);
                if ($retRsrc) {
                    if (empty($this->retQueue)) {
                        
                        return;
                    }
                    $ret = array_shift($this->retQueue);
                    $ret->resolve($ret->handle($retRsrc));
                } else {
                    if (empty($this->queryQueue)) {

                        return;
                    }
                    $query = array_shift($this->queryQueue);
                    $ok = $query->execute($this);
                    if (!$ok) {
                        $query->reject(new \Exception('failed'));

                        return;
                    }
                    $this->retQueue[] = $query;
                }

                return;
        }
    }

    public function prepare($sql)
    {        
        $prepare = new PrepareQuery($sql);
        $this->appendQuery($prepare);
        $promise = $prepare->getPromise();

        return $promise;
    }

    /**
     * @inheritDoc
     */
    public function query($sql, $sqlParams = [])
    {
        $stmt = new Query($sql, $sqlParams);
        $promise = $stmt->getPromise();
        $this->appendQuery($stmt);

        return $promise;
    }

    /**
     * @inheritDoc
     */
    public function execute($sql, $sqlParams = [])
    {
        return $this->query($sql, $sqlParams);
    }

    public function appendQuery(Statement $stmt)
    {
        $this->queryQueue[] = $stmt;
    }

    protected function poll()
    {
        return \pg_connect_poll($this->stream);
    }

    protected function isConnected()
    {
        return $this->connected;
    }
}