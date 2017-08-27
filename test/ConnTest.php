<?php
namespace Dazzle\PgSQL\Test;

require_once __DIR__.'/../vendor/autoload.php';

use Dazzle\Loop\Loop;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\PgSQL\Connection\Connection;
use Dazzle\PgSQL\Statement\Prepare;
use Dazzle\PgSQL\Statement\Tuple;
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

$db->start()->then(function (Connection $conn) use ($loop) {
//     $conn->query('select \'ok\'')->then(function (Tuple $ret) use ($loop) {
//         print_r($ret->fetchRow());
//         $loop->stop();
//     });
    $conn->prepare('select \'ok\'')->then(function (Prepare $prepare) use ($loop) {
        $prepare->execute()->then(function ($row) use ($loop) {
            var_export($row);
            $loop->stop();
        });
    });
});

$loop->start();