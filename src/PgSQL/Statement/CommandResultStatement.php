<?php

interface CommandResultStatement
{
    public function getAffectedRows();
    public function getLastId();
}