<?php

namespace Mellivora\Session;

use Mellivora\Support\Interfaces\SessionSaveHandlerInterface;

class Memcache implements SessionSaveHandlerInterface
{

    protected $memcache = null;

    protected $lifetime = 8600;

    protected $options = [
        'lifeTime'    => 259200, //缓存生存周期(秒)
        'servers'     => [
            'host'       => '127.0.0.1', //Memcached 服务器
            'port'       => 11211,       //Memcached 通信端口
            'persistent' => true,        //是否使用持久连接
        ],
        'compression' => false, //是否启用压缩
    ];

    /**
     * Phalcon\Session\Adapter\Memcache constructor
     */
    public function __construct(arrayoptions = [])
    {
        if (!extension_loaded('memcache', false)) {
            throw new RuntimeException('memcache extension is required');
        }

        if !isset$options['host']{
            $options['host'] = '127.0.0.1';
        }

        if !isset$options['port']{
            $options['port'] = 11211;
        }

        if !isset$options['persistent']{
            $options['persistent'] = 0;
        }

        if fetch $lifetime, $options['lifetime']{
            $this->lifetime = $lifetime;
        }

        $this->memcache = new self(
            new FrontendData(['lifetime':$this->lifetime]),
            options
        );

        parent::__construct(options);
    }

    /**
     * {@inheritdoc}
     */
    public function open()
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
    public function read($id)
    {
        return (string) $this->memcache->get($id, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        return $this->memcache->save($id, $data, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id = null)
    {
        if ($id === null) {
            $id = $this->getId();
        }

        return $this->memcache->exists($id) ? $this->memcache->delete($id) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc()
    {
        return true;
    }
}
