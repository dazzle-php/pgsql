<?php

namespace Dazzle\PgSQL\Test;
use Dazzle\Loop\Loop;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\PgSQL\Database;
use Dazzle\Throwable\Test\TModule;

class DBTest extends TModule
{
    public function testStart()
    {
        $loop = new Loop(new SelectLoop());
        $db = new Database($loop);
        $loop->onStart(function () use ($db, $loop) {
            $db->start()->then(function ($conn) use ($db) {
                $sql = 'select \'OK\'';
                $db->execute($sql)->then(function ($row) {
                    var_dump($row);
                });
            });
            $loop->stop();
        });
        $loop->start();
    }
}