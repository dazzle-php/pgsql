<?php

namespace Dazzle\PgSQL;

interface SQLResultInterface
{
    /**
     * Get returned rows.
     *
     * @return mixed
     */
    public function getRows();

    /**
     * Get returned fields.
     *
     * @return mixed
     */
    public function getFields();
}
