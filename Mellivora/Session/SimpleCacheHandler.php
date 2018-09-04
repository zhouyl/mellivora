<?php

namespace Mellivora\Session;

use InvalidArgumentException;
use Mellivora\Cache\Connector;
use Mellivora\Cache\NullConnector;
use SessionHandlerInterface;

/**
 * 使用 symfony 的 simple-cache 来进行 session 管理
 */
class SimpleCacheHandler implements SessionHandlerInterface
{
    /**
     * simple cache 适配器
     *
     * @var \Symfony\Component\Cache\Simple\AbstractCache
     */
    protected $simpleCache;

    /**
     * cache 连接参数
     *
     * @var array
     */
    protected $options = [
        'namespace' => 'session',            // 独立的 session namespace
        'lifetime'  => 259200,               // session 生命周期
        'connector' => NullConnector::class, // 缓存连接器
    ];

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        $connector = $this->options['connector'];

        if (!is_subclass_of($connector, Connector::class)) {
            throw new InvalidArgumentException(
                $connector . ' must implement of ' . Connector::class
            );
        }

        $this->simpleCache = (new $connector($this->options))->getSimpleCacheAdapter();
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return (string) $this->simpleCache->get($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData)
    {
        return $this->simpleCache->set($sessionId, $sessionData);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return $this->simpleCache->has($id) ? $this->cache->delete($id) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
