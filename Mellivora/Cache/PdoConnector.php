<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Simple\PdoCache;

/**
 * PDO 数据库缓存连接器
 *
 * @link https://symfony.com/doc/current/components/cache/adapters/phpfiles_adapter.html
 */
class PdoConnector extends Connector
{

    /**
     * 配置参数
     *
     * @var array
     */
    protected $config = [
        // DSN or instance of \PDO
        'connection' => 'mysql:dbname=test;host=localhost;charset=utf8',

        // 数据库选项
        'options'    => [
            'db_table'              => 'cache_items',
            'db_id_col'             => 'item_id',
            'db_data_col'           => 'item_data',
            'db_lifetime_col'       => 'item_lifetime',
            'db_time_col'           => 'item_time',
            'db_username'           => '',
            'db_password'           => '',
            'db_connection_options' => [],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        return $this->autoCreateTable(new PdoAdapter(
            $this->config['connection'],
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['options']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        return $this->autoCreateTable(new PdoCache(
            $this->config['connection'],
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['options']
        ));
    }

    /**
     * 自动创建数据表
     *
     * @param  object   $cache
     * @return object
     */
    protected function autoCreateTable($cache)
    {
        $lockfile = '/tmp/mellivora_cache_pdo_connector.lock';
        if (!is_file($lockfile)) {
            try {
                $cache->createTable();
            } catch (\PDOException $e) {
            } finally {
                @file_put_contents($lockfile, time());
            }
        }

        return $cache;
    }
}
