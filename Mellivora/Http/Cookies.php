<?php

namespace Mellivora\Http;

use ArrayAccess;
use Mellivora\Support\Interfaces\EncryptionInterface;

/**
 * Cookies 管理
 */
class Cookies implements ArrayAccess
{

    /**
     * 默认配置选项
     *
     * @var array
     */
    protected $defaults = [
        'lifetime' => 86400, // 默认生存周期 1 天，单位：秒
        'path'     => '/',   // 存储路径
        'domain'   => null,  // 域名
        'httponly' => null,  // 仅允许 http 访问，禁止 javascript 访问
        'secure'   => false, // 启用 https 连接传输
        'encrypt'  => false, // 是否使用 crypt 加密
    ];

    /**
     * crypt 加密类
     *
     * @var Mellivora\Support\Interfaces\EncryptionInterface
     */
    protected $encryption;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * 设置 Cookie 选项
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->defaults)) {
                $this->defaults[$key] = $value;
            }
        }
    }

    /**
     * 设定 crypt 加密类
     *
     * @param Mellivora\Support\Interfaces\EncryptionInterface $encryption
     */
    public function setEncryption(EncryptionInterface $encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * 获取 crypt 加密类
     *
     * @return Mellivora\Support\Interfaces\EncryptionInterface
     */
    public function getEncryption()
    {
        if (!$this->encryption) {
            throw new \RuntimeException('The instance for encryption is not registered');
        }

        return $this->encryption;
    }

    /**
     * 设置 cookie 值
     *
     * @param string  $key
     * @param mixed   $value
     * @param integer $minutes
     */
    public function set($key, $value, $minutes = null)
    {
        if ($minutes === null) {
            $minutes = $this->defaults['lifetime'];
        }

        if ($this->defaults['encrypt']) {
            $value = $this->getEncryption()->encryptBase64($value);
        }

        setcookie(
            $key,
            $value,
            time() + $minutes,
            $this->defaults['path'],
            $this->defaults['domain'],
            $this->defaults['secure'],
            $this->defaults['httponly']
        );

        $_COOKIE[$key] = $value;
    }

    /**
     * 获取 cookie 值
     *
     * @param  array   $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $_COOKIE[$key] ?? $default;

        if ($this->defaults['encrypt']) {
            $value = $this->getEncryption()->decryptBase64($value);
        }

        return $value;
    }

    /**
     * 判断 cookie 是否存在
     *
     * @param  string    $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * 删除 cookie
     *
     * @param  string    $key
     * @return boolean
     */
    public function delete($key)
    {
        setcookie(
            $key,
            null,
            -86400,
            $this->defaults['path'],
            $this->defaults['domain'],
            $this->defaults['secure'],
            $this->defaults['httponly']
        );

        unset($_COOKIE[$key]);

        return true;
    }

    /**
     * 清空所有 cookie
     */
    public function clear()
    {
        foreach ($_COOKIE as $key => $value) {
            $this->delete($key);
        }

        $_COOKIE = null;
    }

    /**
     * 返回所有 cookie
     */
    public function toArray()
    {
        return $_COOKIE;
    }

    /********************************************************************************
     * ArrayAccess 接口实现
     *******************************************************************************/

    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetUnset($key)
    {
        return $this->delete($key);
    }

    /********************************************************************************
     * 魔术方法实现
     *******************************************************************************/

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        return $this->delete($key);
    }
}
