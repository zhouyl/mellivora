<?php

namespace Mellivora\Helper\Protobuf;

use ArrayAccess;
use Google\Protobuf\Internal\Message as ProtobufMessage;
use Mellivora\Support\Arr;
use Mellivora\Support\Str;
use Mellivora\Support\Traits\MagicAccess;
use ReflectionClass;

/**
 * 将 protobuf 数据转换为对应的 messge 类，并进行包裹
 *
 * <code>
 * // 使用数组创建类
 * new MessageWrapper(HelloWorld::class, ['foo' => 'bar']);
 *
 * // 使用序列化字符串
 * new MessageWrapper(Helloworld::class, fromSerializeString());
 * </code>
 *
 * @return
 */
class MessageWrapper implements ArrayAccess
{

    use MagicAccess;

    /**
     * @var Google\Protobuf\Internal\Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Constructor
     *
     * @param  string|object              $message
     * @param  string|array               $data
     * @throws InvalidArgumentException
     */
    public function __construct($message, $data = null)
    {
        if (!$message instanceof ProtobufMessage) {
            if (is_string($message) && is_subclass_of($message, ProtobufMessage::class)) {
                $message = new $message;
            } else {
                throw new InvalidArgumentException('Invalid argument for "$message"');
            }
        }

        // 获取可用的属性值
        $ref = new ReflectionClass($message);
        foreach ($ref->getProperties() as $property) {
            $this->properties[] = $property->getName();
        }

        $this->message = $message;

        is_null($data) || $this->from($data);
    }

    /**
     * 获取源 Message 实例
     *
     * @return Google\Protobuf\Internal\Message
     */
    public function raw()
    {
        return $this->message;
    }

    /**
     * 设定来源数据，可以是序列化之后的字符，也可以是数组数据
     *
     * @param  mixed                                      $data
     * @return Mellivora\Helper\Protobuf\MessageWrapper
     */
    public function from($data)
    {
        if (is_string($data)) {
            $this->message->mergeFromString($data);
        } elseif (!empty($data)) {
            $this->set(Arr::convert($data));
        }

        return $this;
    }

    /**
     * 将数据转换为序列化字符
     *
     * @return string
     */
    public function toString()
    {
        return $this->message->serializeToString();
    }

    /**
     * 将数据转换为序列化字符
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * 为 Message 设定数据
     *
     * @param  mixed                                      $key
     * @param  mixed                                      $value
     * @return Mellivora\Helper\Protobuf\MessageWrapper
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } elseif ($this->has($key)) {
            $method = 'set' . Str::studly($key);
            if (method_exists($this->message, $method)) {
                $this->message->$method($value);
            }
        }

        return $this;
    }

    /**
     * 从 Message 中获取数据
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            $method = 'get' . Str::studly($key);
            if (method_exists($this->message, $method)) {
                $data = $this->message->$method();

                return is_null($data) ? $default : $data;
            }
        }

        return $default;
    }

    /**
     * 判断 Message 中是否存在指定的属性
     *
     * @param  string    $key
     * @return boolean
     */
    public function has($key)
    {
        return in_array($key, $this->properties);
    }

    /**
     * 删除指定的属性数据，设置为 NULL
     *
     * @param  string                                     $key
     * @return Mellivora\Helper\Protobuf\MessageWrapper
     */
    public function delete($key)
    {
        return $this->set($key, null);
    }

    /**
     * 将 Message 属性数据转换为 array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->properties as $key) {
            $result = $this->get($key);

            if ($result instanceof ProtobufMessage) {
                $data[$key] = (new self($result))->toArray();
            } else {
                $data[$key] = $result;
            }
        }

        return $data;
    }
}
