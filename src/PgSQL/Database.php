<?php

namespace Dazzle\PgSQL;

use Dazzle\Event\BaseEventEmitter;
use Dazzle\Loop\LoopAwareTrait;
use Dazzle\Loop\LoopInterface;
use Dazzle\PgSQL\Connection\Connector;
use Dazzle\PgSQL\Connection\Connection;
use Dazzle\PgSQL\Transaction\TransactionBox;
use Dazzle\PgSQL\Transaction\TransactionBoxInterface;
use Dazzle\Promise\Deferred;
use Dazzle\Promise\Promise;
use Dazzle\Promise\PromiseInterface;
use Dazzle\Throwable\Exception;
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
     * @var Connector
     */
    protected $conn;

    protected $readSock;

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
        $this->conn = new Connector($this->config);
        $this->state = self::STATE_INIT;
//        $this->transBox = $this->createTransactionBox();
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
            return Promise::doResolve($this->conn);
        }
        $this->state = self::STATE_CONNECT_PENDING;
        $promise = $this->conn->getConnection();
        if (!($sock = $this->conn->getSock())) {
            throw new \Exception();
        }
        $this->loop->addReadStream($sock, [$this->conn, 'connect']);
        $this->loop->addWriteStream($sock, [$this->conn, 'connect']);

        return $promise;
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
        return $this->query(sprintf('USE `%s`', $dbname));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDatabase()
    {
        return pg_dbname($this->stream);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function query($sql, $sqlParams = [])
    {
        $query = new Deferred();
        $promise = $query->getPromise();
        //TODO: $query should be a QueryCommand object
        $this->queue[] = $query;

        return $promise;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function execute($sql, $sqlParams = [])
    {
        $exec = new Deferred();
        $promise = $exec->getPromise();
        //TODO: $exec should be a ExecCommand object
        $this->queue[] = $exec;

        return $promise;
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
            'host' => 'tcp://127.0.0.1:5432',
            'user'     => 'root',
            'password'     => '',
            'dbname'   => '',
        ];
        $config = array_merge($default, $config);
        foreach ($config as $key => &$value) {
            if (!$value) {
                unset($config[$key]);
                continue;
            }
            $value = "$key=$value";
        }

        return implode(' ', $config);
    }

    public function asyncConnProcedure()
    {
        switch (\pg_connect_poll($this->stream)) {
            case \PGSQL_POLLING_FAILED:
                return;
            case \PGSQL_POLLING_OK:
                if ($this->state < self::STATE_CONNECT_SUCCEEDED) {
                    $this->state = self::STATE_CONNECT_SUCCEEDED;
                    $this->conn->resolve($this->stream);
                } else {
                    $ret = pg_get_result($this->stream);
                    if ($ret != false) {
                        $stat = pg_result_status($ret, \PGSQL_STATUS_LONG);
                        switch ($stat) {
                            case PGSQL_TUPLES_OK:
                                //TODO:
                                var_dump(pg_fetch_row($ret));
                                break;
                            case PGSQL_COMMAND_OK:
                                //TODO:
                                var_export(pg_affected_rows($ret));
                                break;
                            case PGSQL_EMPTY_QUERY:
                                break;
                            case PGSQL_BAD_RESPONSE:
                            case PGSQL_NONFATAL_ERROR:
                            case PGSQL_FATAL_ERROR:
                                throw new Exception(pg_last_error($this->stream));
                                break;
                            default:
                                break;
                        }
                        die;
                    }
                }
                return;
            case \PGSQL_POLLING_ACTIVE:
                return;

            default:
                return;
        }
    }
}
