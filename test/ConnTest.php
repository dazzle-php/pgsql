<?php
namespace Dazzle\PgSQL\Test;

use Dazzle\Throwable\Test\TModule;

class ConnTest extends TModule
{
    /**
     * @group testing
     */
    public function testConn()
    {
        $cs = 'host=192.168.99.100 port=35432 user=postgres dbname=postgres';
    }
}

