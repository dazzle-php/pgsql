<?php

namespace Dazzle\PgSQL\Support\Connection;


interface ConnectionInterface
{
    public function query($sql, $params = []);

    public function execute($sql, $params = []);
}