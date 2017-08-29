<?php
namespace Dazzle\PgSQL\Statement;

use Dazzle\PgSQL\Connection\ConnectorInterface;
use Dazzle\Promise\Deferred;

class PrepareQuery extends Deferred implements Statement
{
    use StatementAwareTrait;
    
    protected $name;
    /**
     * @var ConnectorInterface
     */
    protected $pipeline;

    public function __construct($sql)
    {
        parent::__construct();
        $this->sql = $sql;
        $this->name = $this->generateName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getPipe()
    {
        return $this->pipeline;
    }

    public function execute(ConnectorInterface $connector)
    {
        $this->pipeline = $connector;

        return \pg_send_prepare($connector->getStream(), $this->name, $this->getSQL());
    }

    /**
     * @overwrite
     * @param resource $result
     * @return mixed
     */
    public function handle($result)
    {
        $stat = pg_result_status($result, \PGSQL_STATUS_LONG);
        if ($stat != PGSQL_COMMAND_OK) {
            return new \Exception('error');
        }

        return new Prepared($this);
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