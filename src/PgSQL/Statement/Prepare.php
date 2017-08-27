<?php

namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Connection\ConnectorInterface;
use Dazzle\Promise\Deferred;
use Dazzle\Promise\DeferredInterface;

class Prepare extends Deferred implements PreparedStatement,DeferredInterface
{
    use StatementTrait;

    protected $prepared;

    protected $params;
    /**
     * @var ConnectorInterface $connector
     */
    protected $connector;

    public function __construct($sql)
    {
        parent::__construct();
        $this->sql = $sql;
        $this->name = $this->generateName();
    }

    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function execute(array $params = [])
    {
        $deferred = new Deferred();
        $this->connector->appendQuery($this, $deferred);

        return $deferred->getPromise()->success(function (ConnectorInterface $connector) {
            return \pg_fetch_row($connector->getAwait());
        });
    }

    public function prepare()
    {
        $this->prepared = true;
    }

    public function isPrepared()
    {
        return $this->prepared;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParams()
    {
        return $this->params?:[];
    }

    public function getSQL()
    {
        return $this->sql;
    }

    private function generateName($length = 6)
    {
        $id = md5(microtime());
        $size = strlen($id);
        $offset = mt_rand(0, $size - $length);
        $end = mt_rand($offset + $length, $size);

        return substr($id, $offset, $end);
    }
}