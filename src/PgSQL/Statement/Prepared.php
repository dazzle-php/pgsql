<?php

namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Connection\ConnectorInterface;
use Dazzle\Promise\Deferred;

class Prepared extends Deferred implements PreparedStatement
{
    use StatementAwareTrait;

    protected $prepare;

    public function __construct(PrepareQuery $prepare)
    {
        parent::__construct();
        $this->prepare = $prepare;
    }

    public function getParams()
    {
        return $this->prepare->getParams();
    }

    public function setParams(array $params = [])
    {
        $this->prepare->setParams($params);

        return $this;
    }

    public function execute(ConnectorInterface $connector)
    {
        return \pg_send_execute($connector->getStream(), $this->prepare->getName(), $this->getParams());
    }

    public function execPreparedStmt()
    {
        $connector = $this->prepare->getPipe();
        $promise = $this->getPromise();
        $connector->appendQuery($this);

        return $promise;
    }
}