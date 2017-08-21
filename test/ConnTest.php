<?php
namespace Dazzle\PgSQL\Test;

require_once __DIR__.'/../vendor/autoload.php';

use Dazzle\Loop\Loop;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\Throwable\Test\TModule;
use Dazzle\PgSQL\Database;

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

$loop = new Loop(new SelectLoop());

$db = new Database($loop, [
    'host' => '192.168.99.100',
    'port' => 35432,
    'user' => 'postgres',
    'dbname' => 'postgres'
]);

$db->start()->then(function ($conn) use ($loop) {
    $send = pg_send_query($conn, 'insert into demo DEFAULT VALUES');
})->then(function () use ($loop) {
//    $loop->stop();
});

$loop->start();