<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Connection\ConnectorInterface;
use Dazzle\Promise\Deferred;

class Query extends Deferred implements Statement
{
    use StatementAwareTrait;
    use StatementHandlerTrait;

    public function __construct($sql, array $params = [])
    {
        parent::__construct();
        $this->sql = $sql;
        $this->params = $params;
    }

    public function execute(ConnectorInterface $connector)
    {
        return \pg_send_query_params($connector->getStream(), $this->getSQL(), $this->getParams());
    }
}