<?php
declare(strict_types=1);

namespace Cache\redis;

abstract class Base
{
    protected static Base $instance;

    /** @var array 服务器链接 */
    protected array $server = [];

    /** @var bool 是否Swoole */
    protected bool $swoole = false;

    /** @var bool 是否开启连接池 */
    protected bool $poolOpen = false;

    /** @var int 连接池数量 */
    protected int $poolSize = 1024;

    protected function __construct()
    {}

    public static function getInstance(): Base
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    abstract public function make(array $redisConfig, int $dbIndex = 0);

    /**
     * 开启关闭swoole
     * @param  bool $status 开启关闭
     * @return $this
     */
    public function withSwoole(bool $status = false): static
    {
        $this->swoole = $status;
        return $this;
    }

    /**
     * 是否swoole模式
     * @return bool
     */
    public function isSwoole(): bool
    {
        return $this->swoole;
    }

    /**
     * 连接池配置
     * @param  bool $status 是否开启
     * @param  int  $size   连接池数量
     * @return $this
     */
    public function withPool(bool $status = false, int $size = 1024): static
    {
        $this->poolOpen = $status;
        $this->poolSize = $size;
        return $this;
    }

    /**
     * 是否开启连接池
     * @return bool
     */
    public function isPoole(): bool
    {
        return $this->poolOpen;
    }
}
