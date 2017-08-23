<?php

namespace Dazzle\PgSQL\Statement;

interface QueryStatement extends Statement
{
    public function getName();
    public function getSQL();
    public function getParams();
}