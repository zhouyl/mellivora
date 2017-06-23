<?php

namespace Mellivora\Session;

use Mellivora\Support\Arr;
use Mellivora\Support\Interfaces\SessionSaveHandlerInterface;
use Mellivora\Support\MagicAccess;

/**
 * Session 处理，使用 php.ini 指定的处理方式
 */
class Session extends MagicAccess
{
    /**
     * @var boolean
     */
    protected $started = false;

    /**
     * Constructor
     *
     * @param Mellivora\Support\Interfaces\SessionSaveHandlerInterface saveHandler
     */
    public function __construct(SessionSaveHandlerInterface $saveHandler = null)
    {
        if ($saveHandler) {
            $this->setSaveHandler($saveHandler);
        }
    }

    /**
     * 启动 session
     *
     * @return boolean
     */
    public function start()
    {
        if (!headers_sent()) {
            if (!$this->started && $this->status() !== PHP_SESSION_ACTIVE) {
                session_start();
                $this->started = true;

                return true;
            }
        }

        return false;
    }

    /**
     * 设置 session name
     *
     * @param  string                      $name
     * @return Mellivora\Session\Session
     */
    public function setName($name)
    {
        session_name($name);

        return $this;
    }

    /**
     * 获取 session name
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * 重新生成 session id
     *
     * @param  boolean                     $deleteOldSession
     * @return Mellivora\Session\Session
     */
    public function regenerateId($deleteOldSession = true)
    {
        session_regenerate_id($deleteOldSession);

        return $this;
    }

    /**
     * 获取 session 数据
     *
     * <code>
     * $session->get("foo");
     * $session->get("foo.bar");
     * </code>
     *
     * @param  string   $key
     * @param  mixed    $default
     * @param  boolean  $remove
     * @return mixed
     */
    public function get($key, $default = null, $remove = false)
    {
        $data = Arr::get($_SESSION, $key, $default);

        if ($remove) {
            $this->delete($key);
        }

        return $data;
    }

    /**
     * 设定 session 数据
     *
     * <code>
     * $session->has("foo", "bar");
     * $session->has("foo.bar", 1);
     * </code>
     *
     * @param  string                      $key
     * @param  mixed                       $value
     * @return Mellivora\Session\Session
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            Arr::set($_SESSION, $key, $value);
        }

        return $this;
    }

    /**
     * 检测 session 是否存在指定的键值
     *
     * <code>
     * $session->has("foo");
     * $session->has("foo.*");
     * </code>
     *
     * @param  string    $key
     * @return boolean
     */
    public function has($key)
    {
        return Arr::exists($_SESSION, $key);
    }

    /**
     * 删除 session 数据
     *
     * <code>
     * $session->delete("foo");
     * $session->delete("foo.*");
     * </code>
     *
     * @param  string                      $key
     * @return Mellivora\Session\Session
     */
    public function delete($key)
    {
        Arr::forget($_SESSION, $key);

        return $this;
    }

    /**
     * 清空 session 数据
     *
     * @return Mellivora\Session\Session
     */
    public function clear()
    {
        $_SESSION = [];

        return $this;
    }

    /**
     * 获取 session id
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * 设置 session id
     *
     * @param  string                      $id
     * @return Mellivora\Session\Session
     */
    public function setId($id)
    {
        session_id($id);

        return $this;
    }

    /**
     * 检测 session 是否已启动
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * 销毁 session
     *
     * @param  boolean                     $removeData
     * @return Mellivora\Session\Session
     */
    public function destroy($removeData = false)
    {
        if ($removeData) {
            $_SESSION = [];
        }

        $this->started = false;

        session_destroy();

        return $this;
    }

    /**
     * 获取当前 session 状态
     *
     * @return integer
     */
    public function status()
    {
        return session_status();
    }

    /**
     * 设定自定义的 session handler
     *
     * @param Mellivora\Support\Interfaces\SessionSaveHandlerInterface $saveHandler
     */
    public function setSaveHandler(SessionSaveHandlerInterface $saveHandler)
    {
        session_set_save_handler(
            [ & $saveHandler, 'open'],
            [ & $saveHandler, 'close'],
            [ & $saveHandler, 'read'],
            [ & $saveHandler, 'write'],
            [ & $saveHandler, 'destroy'],
            [ & $saveHandler, 'gc']
        );
    }

    /**
     * Destrctor
     */
    public function __destruct()
    {
        if ($this->started) {
            session_write_close();
            $this->started = false;
        }
    }
}
