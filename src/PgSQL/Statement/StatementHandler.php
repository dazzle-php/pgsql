<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Connection\ConnectorInterface;

interface StatementHandler 
{
    public function execute(ConnectorInterface $connector);
    public function handle($result);
}