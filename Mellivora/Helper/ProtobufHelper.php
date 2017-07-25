<?php

namespace Mellivora\Helper;

use Mellivora\Helper\Protobuf\MessageWrapper;

/**
 * Protobuf 助手类
 */
class ProtobufHelper extends AbstractHelper
{

    /**
     * 对 Message 类进行包裹，并返回包裹类
     *
     * @param  string|object                               $message
     * @param  string|array|null                           $data
     * @return \Mellivora\Helper\Protobuf\MessageWrapper
     */
    public function wrapper($message, $data = null)
    {
        return new MessageWrapper($message, $data);
    }

    /**
     * 对 Message 类进行包裹，并返回源 Message 类
     *
     * @param  string|object                       $message
     * @param  string|array|null                   $data
     * @return \Google\Protobuf\Internal\Message
     */
    public function rawWrapper($message, $data = null)
    {
        return $this->wrapper($message, $data)->raw();
    }

    /**
     * 对 repeated 数据进行包裹，并返回数组
     *
     * @param  string|object $message
     * @param  array         $multiples
     * @return array
     */
    public function repeatWrapper($message, array $multiples)
    {
        $repeated = [];
        foreach ($multiples as $array) {
            $repeated[] = $this->wrapper($message, $array)->raw();
        }

        return $repeated;
    }
}
