<?php

namespace Dazzle\PgSQL\Connection;


interface ConnectionInterface
{
    public function query($sql, $params = []);

    public function execute($sql, $params = []);
}