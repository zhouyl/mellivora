<?php

namespace Mellivora\Helper\Protobuf;

use ArrayAccess;
use BadMethodCallException;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\RepeatedField;
use InvalidArgumentException;
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
     * @var \Google\Protobuf\Internal\Message
     */
    protected $message;

    /**
     * @var \ReflectionClass
     */
    protected $ref;

    /**
     * Constructor
     *
     * @param  string|object               $message
     * @param  string|array                $data
     * @throws \InvalidArgumentException
     */
    public function __construct($message, $data = null)
    {
        if (!$message instanceof Message) {
            if (is_string($message) && is_subclass_of($message, Message::class)) {
                $message = new $message;
            } else {
                throw new InvalidArgumentException("Invalid message argument [$message]");
            }
        }

        $this->message = $message;
        $this->ref     = new ReflectionClass($message);

        is_null($data) || $this->from($data);
    }

    /**
     * 获取源 Message 实例
     *
     * @return \Google\Protobuf\Internal\Message
     */
    public function raw()
    {
        return $this->message;
    }

    /**
     * 设定来源数据，可以是序列化之后的字符，也可以是数组数据
     *
     * @param  mixed                                       $data
     * @return \Mellivora\Helper\Protobuf\MessageWrapper
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
     * @param  mixed                                       $property
     * @param  mixed                                       $data
     * @return \Mellivora\Helper\Protobuf\MessageWrapper
     */
    public function set($property, $value = null)
    {
        if (is_array($property)) {
            foreach ($property as $k => $v) {
                $this->set($k, $v);
            }

            return $this;
        }

        if ($this->has($property)) {
            $method = 'set' . Str::studly($property);
            $value  = $this->sanitizeSetterValue($method, $value);

            $this->message->$method($value);
        }

        return $this;
    }

    /**
     * 从 Message 中获取数据
     *
     * @param  string  $property
     * @param  mixed   $default
     * @return mixed
     */
    public function get($property, $default = null)
    {
        if ($this->has($property)) {
            $method = 'get' . Str::studly($property);
            $return = $this->message->$method();

            return is_null($return) ? $default : $return;
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
        return $this->ref->hasProperty($key) || $this->ref->hasProperty(Str::snake($key));
    }

    /**
     * 删除指定的属性数据，设置为 NULL
     *
     * @param  string                                      $key
     * @return \Mellivora\Helper\Protobuf\MessageWrapper
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

        foreach ($this->ref->getProperties() as $property) {
            $name        = $property->getName();
            $data[$name] = $this->sanitizeToArrayValue($this->get($name));
        }

        return $data;
    }

    /**
     * 允许直接调用 Message 类的方法
     *
     * @param  string                    $method
     * @param  array                     $args
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->message, $method)) {
            throw new BadMethodCallException(
                sprintf('Call to undefined method %s::%s()', get_class($this->message), $method));
        }

        if (substr($method, 0, 3) === 'set') {
            $this->set(substr($method, 3), $args[0]);

            return $this;
        }

        if (substr($method, 0, 3) === 'get') {
            return $this->get(substr($method, 3));
        }

        return $this->message->$method(...$args);
    }

    /**
     * 根据 protobuf 生成的代码，尝试从注释中匹配参数的约束类
     *
     * 例如
     *     <code>.Proto.PayCenter.StatusInfo status_info = 1;</code>
     *     <code>repeated .Proto.PayCenter.PayPaymentItem payment_list = 3;</code>
     *
     * @param  string        $method
     * @return array|false
     */
    protected function getSetterRestrict($method)
    {
        $namespace = $this->ref->getNamespaceName();
        $document  = $this->ref->getMethod($method)->getDocComment();

        $regex = sprintf('~<code>(repeated\s*)?\.?(%s\.[\w\-]+).*<\/code>~',
            preg_quote(str_replace('\\', '.', $namespace)));

        preg_match($regex, $document, $matches);

        if (isset($matches[2])) {
            $class = str_replace('.', '\\', $matches[2]);
            if (class_exists($class) && is_subclass_of($class, Message::class)) {
                return [!empty($matches[1]), $class];
            }
        }

        return false;
    }

    /**
     * 应用于 setter 方法的 value 转换
     *
     * @param  string  $setter
     * @param  mixed   $value
     * @return mixed
     */
    protected function sanitizeSetterValue($setter, $value)
    {
        if ($value instanceof self) {
            return $value->raw();
        }

        // 获取 setter 方法的约束条件
        if (!(is_array($value) && $restrict = $this->getSetterRestrict($setter))) {
            return $value;
        }

        list($repeated, $class) = $restrict;

        if ($repeated) {
            $values = [];
            foreach ($value as $val) {
                if (is_array($val)) {
                    $val = new self($class, $val);
                }

                if ($val instanceof self) {
                    $val = $val->raw();
                }

                $values[] = $val;
            }

            return $values;
        }

        return (new self($class, $value))->raw();
    }

    /**
     * 应用于 toArray 方法的 value 转换
     *
     * @param  mixed   $value
     * @return mixed
     */
    protected function sanitizeToArrayValue($value)
    {
        if ($value instanceof Message) {
            return (new self($value))->toArray();
        }

        if ($value instanceof RepeatedField) {
            $values = [];
            foreach ($value as $k => $v) {
                $values[] = $this->sanitizeToArrayValue($v);
            }

            return $values;
        }

        if ($value instanceof self) {
            return $value->toArray();
        }

        return $value;
    }
}
