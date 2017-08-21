<?php

namespace Dazzle\PgSQL\Support\Connection;

class AsyncConnector implements ConnectorInterface
{
    /**
     * @inheritDoc
     */
    public function query($sql, $sqlParams = [])
    {
        // TODO: Implement query() method.
    }

    /**
     * @inheritDoc
     */
    public function execute($sql, $sqlParams = [])
    {
        // TODO: Implement execute() method.
    }

    public function getSock()
    {
        return \pg_socket($this->stream);
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function asyncConnProcedure()
    {

    }
}