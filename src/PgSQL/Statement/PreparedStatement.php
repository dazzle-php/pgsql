<?php

namespace Dazzle\PgSQL\Statement;

interface PreparedStatement extends Statement
{
    public function execute();
}