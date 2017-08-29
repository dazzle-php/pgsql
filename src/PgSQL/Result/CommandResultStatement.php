<?php

namespace Dazzle\PgSQL\Result;

interface CommandResultStatement
{
    public function getAffectedRows();
    public function getLastId();
}