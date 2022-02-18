<?php
declare(strict_types=1);

namespace src;

use RuntimeException;

class RedisPool
{
    private static $instance;
    private $pool;
    private $config;

    public static function getInstance($config = null)
    {
        if (empty(self::$instance)) {
            if (empty($config)) {
                throw new RuntimeException("Redis config empty");
            }
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    public function __construct($config)
    {
        if (empty($this->pool)) {
            $this->config = $config;
            $this->pool = new chan($config['master']['pool_size']);
            for ($i = 0; $i < $config['master']['pool_size']; $i++) {
                go(function() use ($config) {
                    $redis = new RedisDB();
                    $res = $redis->connect($config);
                    if ($res === false) {
                        throw new RuntimeException("Failed to connect redis server");
                    } else {
                        $this->pool->push($redis);
                    }
                });
            }
        }
    }

    public function get()
    {
        if ($this->pool->length() > 0) {
            $redis = $this->pool->pop($this->config['master']['pool_get_timeout']);
            if (false === $redis) {
                throw new RuntimeException("Pop redis timeout");
            }
            defer(function () use ($redis) { //释放
                $this->pool->push($redis);
            });
            return $redis;
        } else {
            throw new RuntimeException("Pool length <= 0");
        }
    }
}