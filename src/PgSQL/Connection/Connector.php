<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\Promise\Deferred;
use Dazzle\Promise\Promise;
use Dazzle\PgSQL\Statement\Statement;
use Dazzle\PgSQL\Statement\QueryStatement;
use Dazzle\PgSQL\Statement\Result;
use Dazzle\Throwable\Exception;

class Connector implements ConnectorInterface
{
    protected $connected;

    protected $queryQueue;

    protected $retQueue;

    /**
     * @var Deferred
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

    /**
     * @inheritDoc
     */
    public function query($sql, $sqlParams = [])
    {
        $stmt = new Statement($sql, $sqlParams);
        $deferred = new Deferred();
        $promise = $deferred->getPromise();
        $this->queryQueue[] = $stmt;
        $this->retQueue[] = $deferred;

        return $promise->success(function ($ret) {
            return new Result($ret);
        });
    }

    /**
     * @inheritDoc
     */
    public function execute(QueryStatement $stmt)
    {
        //todo
    }

    protected function poll()
    {
        return \pg_connect_poll($this->stream);
    }

    public function getSock()
    {
        return \pg_socket($this->stream);
    }

    public function getConnection()
    {
        $connection = $this->conn;

        return $this->conn->getPromise()
            ->success(function (ConnectorInterface $connector) use ($connection) {
                $connection->setConnector($connector);

                return $connection;
        });
    }

    public function connect()
    {
        switch ($this->poll()) {
            case \PGSQL_POLLING_FAILED:
                return;
            case \PGSQL_POLLING_OK:
                if ($this->isConnected() != true) {
                    $this->connected = true;
                    $this->conn->resolve($this);

                    return;
                }
                $ret = \pg_get_result($this->stream);
                if ($ret != false) {
                    $stat = pg_result_status($ret, \PGSQL_STATUS_LONG);
                    switch ($stat) {
                        case PGSQL_EMPTY_QUERY:
                            break;
                        case PGSQL_COMMAND_OK:
                        case PGSQL_TUPLES_OK:
                            $result = array_shift($this->retQueue);
                            $result->resolve($ret);
                            break;
                        case PGSQL_BAD_RESPONSE:
                        case PGSQL_NONFATAL_ERROR:
                        case PGSQL_FATAL_ERROR:
                            throw new Exception(pg_last_error($this->stream));
                            break;
                        default:
                            break;
                    }
                } elseif (!empty($this->queryQueue)) {
                    $stmt = array_shift($this->queryQueue);
                    if (!$this->asyncQuery($stmt)) {
                        $result = array_shift($this->retQueue);
                        $result->reject(new Exception('query failed'));
                    }
                }

                return;
        }
    }

    protected function isConnected()
    {
        return $this->connected;
    }

    protected function asyncExec(QueryStatement $stmt)
    {
        return \pg_send_execute($this->stream, $stmt->getName(), $stmt->getParams());
    }

    protected function asyncQuery(QueryStatement $stmt)
    {
        return \pg_send_query_params($this->stream, $stmt->getSQL(), $stmt->getParams());
    }
}