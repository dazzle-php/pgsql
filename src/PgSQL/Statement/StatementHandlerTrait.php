<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Result\Tuple;
use Dazzle\PgSQL\Result\CommandResult;

trait StatementHandlerTrait
{
    public function handle($result)
    {
        $stat = pg_result_status($result, \PGSQL_STATUS_LONG);
        switch ($stat) {
            case PGSQL_EMPTY_QUERY:
                break;
            case PGSQL_COMMAND_OK:
                return new CommandResult($result);
            case PGSQL_TUPLES_OK:
                return new Tuple($result);
            case PGSQL_BAD_RESPONSE:
            case PGSQL_NONFATAL_ERROR:
            case PGSQL_FATAL_ERROR:
                return new \Exception('error');
        }
    }
}