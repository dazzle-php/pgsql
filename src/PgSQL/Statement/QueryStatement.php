<?php

interface QueryStatement
{
    public function getName();
    public function getSQL();
}