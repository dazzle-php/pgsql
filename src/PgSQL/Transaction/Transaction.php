<?php
namespace Dazzle\PgSQL\Transaction;

use Dazzle\Event\BaseEventEmitter;
use Dazzle\Promise\Promise;
use Dazzle\Promise\PromiseInterface;
use Dazzle\Throwable\Exception\Runtime\ExecutionException;

class Transaction extends BaseEventEmitter implements TransactionInterface
{
    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * @var mixed[]
     */
    protected $queue;

    /**
     * @var bool
     */
    protected $open;

    /**
     * @param DatabaseInterface $database
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
        $this->queue = [];
        $this->open = true;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isOpen()
    {
        return $this->open;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function query($sql, array $sqlParams = [])
    {
        return $this->connector->query($sql, $sqlParams);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function execute($sql, array $sqlParams = [])
    {
        return $this->connector->execute($sql, $sqlParams);
    }

    public function begin()
    {
        return $this->connector->execute('begin');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function commit()
    {
       return $this->connector->execute('commit');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function rollback()
    {
       return $this->connector->execute('rollback');
    }
}
