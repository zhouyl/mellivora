<?php

namespace Mellivora\Http;

use Mellivora\Support\Interfaces\EncryptionInterface;
use Mellivora\Support\MagicAccess;

/**
 * Cookies 管理
 */
class Cookies extends MagicAccess
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
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($_COOKIE);

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
     * ookie 设置
     *
     * @param  string    $key
     * @param  mixed     $value
     * @param  integer   $expire
     * @return boolean
     */
    protected function setCookie($key, $value = null, $expire = 0)
    {
        if ($value === null) {
            unset($_COOKIE[$key]);
        } else {
            $_COOKIE[$key] = $value;
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
     * @param  string                   $key
     * @param  mixed                    $value
     * @param  integer                  $minutes
     * @return Mellivora\Http\Cookies
     */
    public function set($key, $value, $minutes = null)
    {
        if ($minutes === null) {
            $minutes = $this->defaults['lifetime'];
        }

        if ($this->defaults['encrypt']) {
            $value = $this->getEncryption()->encryptBase64($value);
        }

        $this->setCookie($key, $value, time() + $minutes);

        return parent::set($key, $value);
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
        $value = parent::get($key, $default);

        if ($this->defaults['encrypt']) {
            $value = $this->getEncryption()->decryptBase64($value);
        }

        return $value;
    }

    /**
     * 删除 cookie
     *
     * @param  string                   $key
     * @return boolean
     * @return Mellivora\Http\Cookies
     */
    public function delete($key)
    {
        $this->setCookie($key, null, -86400);

        return parent::delete($key);
    }
}
