<?php

namespace Dazzle\PgSQL\Support\Transaction;

use Dazzle\PgSQL\TransactionInterface;

interface TransactionBoxInterface
{
    public function isEmpty();

    public function add(TransactionInterface $trans);

    public function remove(TransactionInterface $trans);
}
