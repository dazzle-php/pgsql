<?php
namespace Dazzle\PgSQL;

use Dazzle\Event\BaseEventEmitter;
use Dazzle\Loop\LoopAwareTrait;
use Dazzle\Loop\LoopInterface;
use Dazzle\PgSQL\Connection\Connector;
use Dazzle\PgSQL\Transaction\Transaction;
use Dazzle\Promise\Promise;
use Dazzle\Throwable\Exception;
use Dazzle\PgSQL\Transaction\TransactionInterface ;
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
     * @var int
     */
    protected $state;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @param LoopInterface $loop
     * @param mixed[] $config
     */
    public function __construct(LoopInterface $loop, $config = [])
    {
        $this->loop = $loop;
        $this->config = $this->createConfig($config);
        $this->state = self::STATE_INIT;
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
    public function start($conn = 'default')
    {
        if ($this->isStarted())
        {
            return Promise::doResolve($this->connector->getConnection());
        }
        $this->connector = new Connector($this->config);
        $this->state = self::STATE_CONNECT_PENDING;
        $promise = $this->connector->getConnection();
        if (!($sock = $this->connector->getSock())) {
            return $promise->reject(new Exception('No server connection is currently open'));
        }
        $this->loop->addReadStream($sock, [$this->connector, 'connect']);
        $this->loop->addWriteStream($sock, [$this->connector, 'connect']);

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
     * @inheritDoc
     */
    public function getInfo()
    {
        // TODO: Implement getInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function setDatabase($dbname)
    {
        // TODO: Implement setDatabase() method.
    }

    /**
     * @inheritDoc
     */
    public function getDatabase()
    {
        // TODO: Implement getDatabase() method.
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction()
    {
        $trans = new Transaction($this->connector, $this);
        if ($this->isStarted()) {
            return Promise::doResolve($trans);
        }
        //bad method: append 1 callback
        return $this->start()->success(function ($_) use ($trans) {
            return $trans;
        });
    }

    /**
     * @inheritDoc
     */
    public function endTransaction(TransactionInterface $trans)
    {
        // TODO: Implement endTransaction() method.
    }

    /**
     * @inheritDoc
     */
    public function inTransaction()
    {
        // TODO: Implement inTransaction() method.
    }

    public function getConnection()
    {
        return $this->connector->getConnection();
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
}
