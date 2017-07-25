<?php

namespace Mellivora\Http;

use ArrayAccess;
use Mellivora\Encryption\EncryptionInterface;
use Mellivora\Support\Arr;
use Mellivora\Support\Traits\MagicAccess;

/**
 * Cookies 管理
 */
class Cookies implements ArrayAccess
{

    use MagicAccess;

    /**
     * 默认配置选项
     *
     * @var array
     */
    protected $defaults = [
        'lifetime' => 604800, // 默认生存周期 7 天，单位：秒
        'path'     => '/',    // 存储路径
        'domain'   => null,   // 域名
        'httponly' => false,  // 仅允许 http 访问，禁止 javascript 访问
        'secure'   => false,  // 启用 https 连接传输
        'encrypt'  => false,  // 是否使用 crypt 加密
    ];

    /**
     * crypt 加密类
     *
     * @var \Mellivora\Encryption\EncryptionInterface
     */
    protected $encryption;

    /**
     * Constructor
     *
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
     * @param \Mellivora\Encryption\EncryptionInterface $encryption
     */
    public function setEncryption(EncryptionInterface $encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * 获取 crypt 加密类
     *
     * @return \Mellivora\Encryption\EncryptionInterface
     */
    public function getEncryption()
    {
        if (!$this->encryption) {
            throw new \RuntimeException('The instance for encryption is not registered');
        }

        return $this->encryption;
    }

    /**
     * ookie 设置
     *
     * @param string  $key
     * @param mixed   $value
     * @param integer $expire
     */
    protected function setCookie($key, $value = null, $expire = 0)
    {
        if ($value === null) {
            Arr::forget($_COOKIE, $key);
        } else {
            if ($this->defaults['encrypt']) {
                $value = $this->getEncryption()->encryptBase64($value);
            }

            Arr::set($_COOKIE, $key, $value);
        }

        setcookie($key, $value, $expire,
            $this->defaults['path'],
            $this->defaults['domain'],
            $this->defaults['secure'],
            $this->defaults['httponly']
        );
    }

    /**
     * 设置 cookie 值
     *
     * @param  string                    $key
     * @param  mixed                     $value
     * @param  integer                   $minutes
     * @return \Mellivora\Http\Cookies
     */
    public function set($key, $value = null, $minutes = null)
    {
        if ($minutes === null) {
            $minutes = $this->defaults['lifetime'];
        }

        $data = is_array($key) ? $key : [$key => $value];

        foreach ($data as $key => $value) {
            $this->setCookie($key, $value, time() + $minutes);
        }

        return $this;
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
        $value = Arr::get($_COOKIE, $key);

        if ($value === null) {
            return $default;
        }

        return $this->defaults['encrypt']
            ? $this->getEncryption()->decryptBase64($value)
            : $value;
    }

    /**
     * 判断 cookie 是否存在
     *
     * @param  string    $key
     * @return boolean
     */
    public function has($key)
    {
        return Arr::exists($_COOKIE, $key);
    }

    /**
     * 删除 cookie
     *
     * @param  string                    $key
     * @return \Mellivora\Http\Cookies
     */
    public function delete($key)
    {
        $this->setCookie($key, null, -86400);

        return $this;
    }

    /**
     * 清空所有 cookie
     *
     * @return \Mellivora\Http\Cookies
     */
    public function clear()
    {
        foreach ($_COOKIE as $key => $value) {
            $this->delete($key);
        }

        return $this;
    }

    /**
     * 返回所有 cookie
     *
     * @return array
     */
    public function toArray()
    {
        return $_COOKIE;
    }
}
