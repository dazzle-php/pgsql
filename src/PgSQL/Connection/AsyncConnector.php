<?php

namespace Dazzle\PgSQL\Connection;

use Dazzle\Promise\Deferred;
use Dazzle\Promise\Promise;

class AsyncConnector implements ConnectorInterface
{
    protected $connected;

    protected $queryQueue;

    /**
     * @var Deferred
     */
    protected $conn;

    protected $stream;

    public function __construct()
    {
        $this->conn = new Deferred();
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
        $promise = new Promise(function () use ($sql, $sqlParams) {
            return $this->asyncQuery($sql, $sqlParams);
        });
        $this->queryQueue[] = $promise;

        return $promise;
    }

    /**
     * @inheritDoc
     */
    public function execute(\QueryStatement $stmt)
    {
        $promise = new Promise(function () use ($stmt) {
            return $this->asyncExec($stmt);
        });
        $this->queryQueue[] = $promise;

        return $promise;
    }

    protected function poll()
    {
        return \pg_connect_poll($this->stream);
    }

    protected function getSock()
    {
        return \pg_socket($this->stream);
    }

    public function connect()
    {
        switch ($this->poll()) {
            case \PGSQL_POLLING_FAILED:
                return;
            case \PGSQL_POLLING_OK:
                if ($this->isConnected() != true) {
                    $this->connected = true;
                    $this->conn->resolve($this->stream);
                } else {
                    $ret = pg_get_result($this->stream);
                    if ($ret != false) {
                        $stat = pg_result_status($ret, \PGSQL_STATUS_LONG);
                        switch ($stat) {
                            case PGSQL_TUPLES_OK:
                                //TODO:
                                var_dump(pg_fetch_row($ret));
                                break;
                            case PGSQL_COMMAND_OK:
                                //TODO:
                                var_export(pg_affected_rows($ret));
                                break;
                            case PGSQL_EMPTY_QUERY:
                                break;
                            case PGSQL_BAD_RESPONSE:
                            case PGSQL_NONFATAL_ERROR:
                            case PGSQL_FATAL_ERROR:
                                throw new Exception(pg_last_error($this->stream));
                                break;
                            default:
                                break;
                        }
                        die;
                    }
                }
                return;
            case \PGSQL_POLLING_ACTIVE:
                return;

            default:
                return;
        }
    }

    protected function isConnected()
    {
        return $this->connected;
    }

    protected function asyncExec(\QueryStatement $stmt)
    {
        return \pg_send_execute($this->stream, $stmt->getName(), $stmt->getParams());
    }

    protected function asyncQuery($sql, $params = [])
    {
        return \pg_send_query_params($this->stream, $sql, $params);
    }
}