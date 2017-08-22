<?php

namespace Dazzle\PgSQL\Statement;

interface CommandResultStatement
{
    public function getAffectedRows();
    public function getLastId();
}