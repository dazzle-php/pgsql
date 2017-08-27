<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\PgSQL\Statement\Prepare;
use Dazzle\PgSQL\Statement\PreparedStatement;
use Dazzle\PgSQL\Statement\Tuple;
use Dazzle\Promise\Deferred;
use Dazzle\Promise\DeferredInterface;
use Dazzle\PgSQL\Statement\Query;
use Dazzle\PgSQL\Statement\Statement;
use Dazzle\PgSQL\Statement\CommandResult;
use Dazzle\PgSQL\Statement\QueryStatement;
use Dazzle\Throwable\Exception;

class Connector implements ConnectorInterface
{
    protected $connected;

    protected $queryQueue;

    protected $retQueue;

    protected $await;

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

    public function getSock()
    {
        return \pg_socket($this->stream);
    }

    public function getConnection()
    {
        //rfc: could passed stream to each connection,add connection resolver and pool
        $connection = $this->conn;

        return $this->conn->getPromise()
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
                $ret = \pg_get_result($this->stream);
                if ($ret != false) {
                    $this->handleResult($ret);
                } else {
                    $this->handleQuery();
                }

                return;
        }
    }

    public function prepare($sql)
    {        
        $stmt = new Prepare($sql);
        $promise = $stmt->getPromise();
        $this->appendQuery($stmt, $stmt);

        return $promise->success(function () use ($stmt) {

            return $stmt;
        });
    }

    /**
     * @inheritDoc
     */
    public function query($sql, $sqlParams = [])
    {
        $stmt = new Query($sql, $sqlParams);
        $deferred = new Deferred();
        $promise = $deferred->getPromise();
        $this->appendQuery($stmt, $deferred);

        return $promise->success(function (Connector $conn) {
            return new Tuple($conn->getAwait());
        });
    }

    /**
     * @inheritDoc
     */
    public function execute($sql, $sqlParams = [])
    {
        $stmt = new Query($sql, $sqlParams);
        $deferred = new Deferred();
        $promise = $deferred->getPromise();
        $this->asyncQuery($stmt, $deferred);

        return $promise->success(function (Connector $conn) {
            return new CommandResult($conn->getAwait());
        });
    }

    public function appendQuery(Statement $stmt, DeferredInterface $ret = null)
    {
        $this->queryQueue[] = $stmt;
        if ($ret) {
            $this->retQueue[] = $ret;
        }
    }

    public function getAwait()
    {
        return $this->await;
    }

    protected function poll()
    {
        return \pg_connect_poll($this->stream);
    }

    protected function isConnected()
    {
        return $this->connected;
    }

    protected function asyncPrepare(PreparedStatement $stmt)
    {
        return \pg_send_prepare($this->stream, $stmt->getName(), $stmt->getSQL());
    }

    protected function asyncExec(PreparedStatement $stmt)
    {
        return \pg_send_execute($this->stream, $stmt->getName(), $stmt->getParams());
    }

    protected function asyncQuery(QueryStatement $stmt)
    {
        return \pg_send_query_params($this->stream, $stmt->getSQL(), $stmt->getParams());
    }

    protected function handleQuery()
    {
        if (empty($this->queryQueue)) {

            return;
        }
        $stmt = array_shift($this->queryQueue);
        if ($stmt instanceof Prepare) {
            if ($stmt->isPrepared()) {
                $send = $this->asyncExec($stmt);
            } else {
                $stmt->prepare();
                $stmt->setConnector($this);
                $send = $this->asyncPrepare($stmt);
            }
        } else {
            $send = $this->asyncQuery($stmt);
        }
        if (!$send) {
            $result = array_shift($this->retQueue);
            $result->reject(new Exception('query failed'));
        }
    }

    protected function handleResult($ret)
    {
        if (empty($this->retQueue)) {

            return;
        }
        $result = array_shift($this->retQueue);
        $stat = pg_result_status($ret, \PGSQL_STATUS_LONG);
        switch ($stat) {
            case PGSQL_EMPTY_QUERY:
                break;
            case PGSQL_COMMAND_OK:
            case PGSQL_TUPLES_OK:
                $this->await = $ret;
                $result->resolve($this);
                break;
            case PGSQL_BAD_RESPONSE:
            case PGSQL_NONFATAL_ERROR:
            case PGSQL_FATAL_ERROR:
                $result->reject(new Exception(pg_last_error($this->stream)));
                break;
            default:
                break;
        }
    }
}