<?php

namespace Dazzle\PgSQL\Statement;

interface QueryStatement extends Statement
{
    public function getSQL();
    public function getParams();
}