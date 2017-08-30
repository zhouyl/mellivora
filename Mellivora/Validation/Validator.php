<?php

namespace Mellivora\Validation;

use BadMethodCallException;
use Closure;
use Mellivora\Support\Arr;
use Mellivora\Validation\Valid;
use ReflectionFunction;
use ReflectionMethod;

/**
 * 数据校验器
 *
 * <example>
 * $validator = Validator::factory($post)
 *     ->required('url', 'url is required')
 *     ->url('url', 'invalid url format')
 *     ->inArray('key', ['x','y','z'], 'invalid key')
 *     ->rule('foo', 'Foo::bar', [$p1, $p2], 'invalid foo value')
 *     ->rule('foo', [Foo::class, 'bar'], [$p1, $p2], 'invalid foo value')
 *     ->rule('foo', function($value, $p1, $p2) {
 *         return false;
 *     }, [$p1, $p2], 'invalid foo value');
 *
 * print_r([
 *     $validator->check(), // boolean
 *     $validator->errors(), // array
 *     $validator->firstError(), // string
 *     $validator->firstErrorField(), // string
 *     $validator->lastError(),
 *     $validator->lastErrorField(),
 * ]);
 * </example>
 */
class Validator
{

    /**
     * 待校验数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 数据校验规则集
     *
     * @var array
     */
    protected $rules = [];

    /**
     * 错误消息集
     *
     * @var array
     */
    protected $errors = [];

    /**
     * 工厂方法，快速创建一个数据校验器
     *
     * @param  array                             $data
     * @return \Mellivora\Validation\Validator
     */
    public static function factory(array $data)
    {
        return new static($data);
    }

    /**
     * 构造方法
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 新增校验规则
     *
     * @param  string                            $field
     * @param  callable                          $rule
     * @param  mixed                             $params
     * @param  string|null                       $message
     * @return \Mellivora\Validation\Validator
     */
    public function rule($field, callable $rule, $params = null, $message = null)
    {
        if ($message === null) {
            $message = $params;
            $params  = [];
        }

        $this->rules[] = [$field, $rule, $params, $message];

        return $this;
    }

    /**
     * 检查校验结果是否存在错误
     *
     * @return boolean
     */
    public function check()
    {
        $required = Valid::class . '::required';

        foreach ($this->rules as $rule) {
            list($field, $rule, $params, $message) = $rule;

            // 如果已经存在一条错误了，则后面的错误不再验证
            if (isset($this->errors[$field])) {
                continue;
            }

            $value = Arr::get($this->data, $field);

            // 当规则为非空校验时，如果值为空，则不进行判断
            if ($rule != $required && !Valid::required($value)) {
                continue;
            }

            $params = $this->normalizeParams($params, $rule, $value);

            if (!call_user_func_array($rule, $params)) {
                $this->errors[$field] = $message;
            }
        }

        return count($this->errors) === 0;
    }

    /**
     * 获得有效的验证参数
     *
     * @param  mixed   $params
     * @param  mixed   $rule
     * @param  mixed   $value
     * @return array
     */
    protected function normalizeParams($params, $rule, $value)
    {
        if (is_array($params)) {
            if (is_array($rule)) {
                var_dump($rule);
                $ref = new ReflectionMethod(...$rule);
            } elseif (is_string($rule) && strpos($rule, '::') !== false) {
                $ref = new ReflectionMethod($rule);
            } elseif ($rule instanceof Closure) {
                $ref = new ReflectionFunction($rule);
            }

            if (isset($ref)) {
                $parameters = $ref->getParameters();
                if (isset($parameters[1]) && $parameters[1]->isArray()) {
                    $params = [$params];
                }
            } else {
                $params = [$params];
            }
        } else {
            $params = is_null($params) ? [] : [$params];
        }

        array_unshift($params, $value);

        return $params;
    }

    /**
     * 获取所有的错误消息
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * 获取第一条错误消息
     *
     * @return string
     */
    public function firstError()
    {
        return Arr::first($this->errors);
    }

    /**
     * 获取第一条错误的字段名
     *
     * @return string
     */
    public function firstErrorField()
    {
        return Arr::first(array_keys($this->errors));
    }

    /**
     * 获取最后一条错误消息
     *
     * @return string
     */
    public function lastError()
    {
        return Arr::last($this->errors);
    }

    /**
     * 获取最后一条错误的字段名
     *
     * @return string
     */
    public function lastErrorField()
    {
        return Arr::last(array_keys($this->errors));
    }

    /**
     * 通过魔术方法，调用 Valid 类进行校验
     *
     * @param  string                            $method
     * @param  array                             $arguments
     * @throws \BadMethodCallException
     * @return \Mellivora\Validation\Validator
     */
    public function __call($method, array $arguments)
    {
        $rule = Valid::class . '::' . $method;

        if (!is_callable($rule)) {
            throw new BadMethodCallException("Invalid validation rule: $rule");
        }

        $arguments = array_merge(
            array_slice($arguments, 0, 1),
            [$rule],
            array_slice($arguments, 1)
        );

        return $this->rule(...$arguments);
    }
}
