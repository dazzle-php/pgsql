<?php

namespace Dazzle\PgSQL\Statement;

interface CommandResultStatement extends Statement
{
    public function getAffectedRows();
    public function getLastId();
}