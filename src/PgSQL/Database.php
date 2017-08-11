<?php

namespace Dazzle\PgSQL;

use Dazzle\Event\BaseEventEmitter;
use Dazzle\Loop\LoopAwareTrait;
use Dazzle\Loop\LoopInterface;
use Dazzle\PgSQL\Support\Transaction\TransactionBox;
use Dazzle\PgSQL\Support\Transaction\TransactionBoxInterface;
use Dazzle\Promise\Promise;
use Dazzle\Promise\PromiseInterface;
use Dazzle\Socket\Socket;
use Dazzle\Throwable\Exception\Runtime\ExecutionException;

class Database extends BaseEventEmitter implements DatabaseInterface
{
    use LoopAwareTrait;

    /**
     * @var int
     */
    const STATE_INIT                 = 0;

    /**
     * @var int
     */
    const STATE_CONNECT_PENDING      = 4;

    /**
     * @var int
     */
    const STATE_CONNECT_FAILED       = 2;

    /**
     * @var int
     */
    const STATE_CONNECT_SUCCEEDED    = 6;

    /**
     * @var int
     */
    const STATE_AUTH_PENDING         = 5;

    /**
     * @var int
     */
    const STATE_AUTH_FAILED          = 3;

    /**
     * @var int
     */
    const STATE_AUTH_SUCCEEDED       = 7;

    /**
     * @var int
     */
    const STATE_DISCONNECT_PENDING   = 8;

    /**
     * @var int
     */
    const STATE_DISCONNECT_SUCCEEDED = 1;

    /**
     * @var mixed[]
     */
    protected $config;

    /**
     * @var mixed[]
     */
    protected $serverInfo;

    /**
     * @var int
     */
    protected $state;

    /**
     * @var
     */
    protected $queue;

    /**
     * @var TransactionBoxInterface
     */
    protected $transBox;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @param LoopInterface $loop
     * @param mixed[] $config
     */
    public function __construct(LoopInterface $loop, $config = [])
    {
        $this->loop = $loop;
        $this->config = $this->createConfig($config);
        $this->serverInfo = [];
        $this->state = self::STATE_INIT;
        $this->transBox = $this->createTransactionBox();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPaused()
    {
        // TODO
        return false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pause()
    {
        // TODO
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        // TODO
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isStarted()
    {
        return $this->state >= self::STATE_CONNECT_PENDING;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start()
    {
        if ($this->isStarted())
        {
            return Promise::doResolve($this);
        }

        $stream = pg_connect('host=192.168.99.100 port=35432 user=postgres dbname=postgres');

        $this->stream = $stream;

        $this->loop->addReadStream($stream, function () {
            echo 'read'.PHP_EOL;
        });
        $this->loop->addWriteStream($stream, function () {
            echo 'write'.PHP_EOL;
        });

        return Promise::doResolve($stream);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        if (!$this->isStarted())
        {
            return Promise::doResolve($this);
        }
        // TODO
        return Promise::doReject(new ExecutionException('Not yet implemented.'));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getInfo()
    {
        return $this->serverInfo;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setDatabase($dbname)
    {
        // TODO
        return $this->query(sprintf('USE `%s`', $dbname));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDatabase()
    {
        // TODO
    }

    /**
     * @override
     * @inheritDoc
     */
    public function query($sql, $sqlParams = [])
    {
        return Promise::doResolve(pg_query_params($sql, $sqlParams));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function execute($sql, $sqlParams = [])
    {
        return $this->query($sql, $sqlParams)->then(function($ret) {
            return pg_fetch_row($ret);
        });
    }

    /**
     * @override
     * @inheritDoc
     */
    public function ping()
    {
        return Promise::doResolve(pg_ping($this->stream));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function beginTransaction()
    {
        $trans = new Transaction($this);

        $trans->on('commit', function(TransactionInterface $trans, array $queue) {
            $this->commitTransaction($queue)->then(
                function() use($trans) {
                    return $trans->emit('success', [ $trans ]);
                },
                function($ex) use($trans) {
                    return $trans->emit('error', [ $trans, $ex ]);
                }
            );
            $this->transBox->remove($trans);
        });
        $trans->on('rollback', function(TransactionInterface $trans) {
            $this->transBox->remove($trans);
        });

        return $this->transBox->add($trans);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function endTransaction(TransactionInterface $trans)
    {
        return $trans->rollback();
    }

    /**
     * Try to commit a transaction.
     *
     * @param mixed[] $queue
     * @return PromiseInterface
     */
    protected function commitTransaction($queue)
    {
        // TODO
        return Promise::doReject(new ExecutionException('Not yet implemented.'));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function inTransaction()
    {
        return !$this->transBox->isEmpty();
    }

    /**
     * Create transaction box.
     *
     * @return TransactionBoxInterface
     */
    protected function createTransactionBox()
    {
        return new TransactionBox();
    }

    /**
     * Create configuration file.
     *
     * @param mixed[] $config
     * @return mixed[]
     */
    protected function createConfig($config = [])
    {
        $default = [
            'endpoint' => 'tcp://127.0.0.1:5432',
            'user'     => 'root',
            'pass'     => '',
            'dbname'   => '',
        ];
        return array_merge($default, $config);
    }
}
