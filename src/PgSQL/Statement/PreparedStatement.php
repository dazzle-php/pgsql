<?php

namespace Dazzle\PgSQL\Statement;

interface PreparedStatement extends Statement
{
    public function execute();

    public function getName();

    public function getParams();
}