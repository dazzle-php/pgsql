<?php
namespace Dazzle\PgSQL\Transaction;

use Dazzle\Event\BaseEventEmitter;
use Dazzle\Event\EventEmitterInterface;
use Dazzle\PgSQL\Result\CommandResultStatement;
use Dazzle\Promise\Promise;
use Dazzle\PgSQL\Connection\ConnectorInterface;

class Transaction extends BaseEventEmitter implements TransactionInterface
{
    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * @var bool
     */
    protected $open;

    /**
     * @param ConnectorInterface $connector
     * @param EventEmitterInterface $emitter
     */
    public function __construct(ConnectorInterface $connector, EventEmitterInterface $emitter)
    {
        $this->connector = $connector;
        $this->emitter = $emitter;
        $this->open = false;
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
        if ($this->isOpen()) {
            return Promise::doResolve($this);
        }

        return $this->connector->execute('begin')->success(function (CommandResultStatement $result) {

            if ($result) {
                $this->emitter->emit('transaction:begin');
            }

            return $this;
        });
    }

    /**
     * @override
     * @inheritDoc
     */
    public function commit()
    {
       return $this->connector->execute('commit')->success(function (CommandResultStatement $result) {
           if ($result) {
               $this->emitter->emit('transaction:end');
           }

           return $result;
       });
    }

    /**
     * @override
     * @inheritDoc
     */
    public function rollback()
    {
       return $this->connector->execute('rollback')->success(function (CommandResultStatement $result) {
           if ($result) {
               $this->emitter->emit('transaction:end');
           }

           return $result;
       });
    }
}
