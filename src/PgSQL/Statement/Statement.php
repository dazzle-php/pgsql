<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Connection\ConnectorInterface;
use Dazzle\Promise\DeferredInterface;

interface Statement extends DeferredInterface,StatementHandler
{
    public function getSQL();
    public function setSQL($sql);
    public function getParams();
    public function setParams(array $params);
    // public function execute(ConnectorInterface $connector);
    // public function handle($result);
}