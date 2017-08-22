<?php

use Dazzle\Promise\Deferred;

class Statement extends Deferred implements QueryStatement
{
    public function __construct($sql, array $params = [], $canceller = null)
    {
        parent::__construct($canceller);
        $this->sql = $sql;
        $this->params = $params;
        $this->name = $this->generateName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSQL()
    {
        return $this->sql;
    }

    public function getParams()
    {
        return $this->params;
    }

    private function generateName($length = 6)
    {
        $id = md5(microtime());
        $size = strlen($id);
        $offset = mt_rand(0, $size - $length);
        $end = mt_rand($offset + $length, $size);

        return substr($id, $offset, $end);
    }

}