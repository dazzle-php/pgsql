<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dazzle\Loop\Loop;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\PgSQL\Connection\Connection;
use Dazzle\PgSQL\Result\Tuple;
use Dazzle\PgSQL\Result\TupleResultStatement;
use Dazzle\PgSQL\Statement\Prepared;
use Dazzle\PgSQL\Database;

$loop = new Loop(new SelectLoop());

$db = new Database($loop, [
    'host' => '192.168.99.100',
    'port' => 35432,
    'user' => 'postgres',
    'dbname' => 'postgres'
]);

$db->start()->then(function (Connection $conn) use ($loop) {
    $conn->query('select \'ok\'')->then(function (Tuple $ret) use ($loop) {
        print_r($ret->fetchRow());
    });
    $conn->prepare('select \'ok\'')->then(function (Prepared $prepared) use ($loop) {
        $prepared->execPreparedStmt()->then(function (TupleResultStatement $ret) use ($loop) {
            print_r($ret->fetchRow());
            $loop->stop();
        });
    });
});

$loop->start();