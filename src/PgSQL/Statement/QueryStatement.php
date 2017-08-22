<?php

namespace Dazzle\PgSQL\Statement;

interface QueryStatement
{
    public function getName();
    public function getSQL();
    public function getParams();
}