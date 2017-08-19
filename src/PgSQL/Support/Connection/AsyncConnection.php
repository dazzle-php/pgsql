<?php

namespace Dazzle\PgSQL\Support\Connection;


use Dazzle\Promise\Deferred;

class AsyncConnection extends Deferred
{
    public function getSock(){}
    public function getStreamLink(){}
    public function query(){}
    public function exectue(){}
    public function asyncConnProcedure(){}
}