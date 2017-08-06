<?php

namespace Dazzle\PgSQL;

use Dazzle\Event\BaseEventEmitter;
use Dazzle\Promise\Promise;
use Dazzle\Promise\PromiseInterface;
use Dazzle\Throwable\Exception\Runtime\ExecutionException;

class Transaction extends BaseEventEmitter implements TransactionInterface
{
    /**
     * @var DatabaseInterface
     */
    protected $database;

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
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
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
    public function query($sql, $sqlParams = [])
    {
        if (!$this->isOpen())
        {
            return Promise::doReject(new ExecutionException('This transaction is no longer open.'));
        }
        // TODO
        return Promise::doReject(new ExecutionException('Not yet implemented.'));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function execute($sql, $sqlParams = [])
    {
        // TODO
        return $this->query($sql, $sqlParams)->then(function($command) {
            return $command->affectedRows;
        });
    }

    /**
     * @override
     * @inheritDoc
     */
    public function commit()
    {
        if (!$this->isOpen())
        {
            return Promise::doReject(new ExecutionException('This transaction is no longer open.'));
        }

        $promise = new Promise();

        $this->on('error', function ($trans, $err) use ($promise) {
            return $promise->reject($err);
        });
        $this->on('success', function ($trans) use ($promise) {
            return $promise->resolve();
        });

        $this->open = false;
        $this->emit('commit', [ $this, $this->queue ]);
        $this->queue = [];

        return $promise;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function rollback()
    {
        if (!$this->isOpen())
        {
            return Promise::doReject(new ExecutionException('This transaction is no longer open.'));
        }

        $this->open = false;
        $this->emit('rollback', [ $this ]);
        $this->queue = [];

        return Promise::doResolve();
    }
}
