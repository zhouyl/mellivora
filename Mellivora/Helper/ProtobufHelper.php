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
     * @param  string|object                              $message
     * @param  string|array                               $data
     * @return Mellivora\Helper\Protobuf\MessageWrapper
     */
    public function wrapper($message, $data = [])
    {
        return new MessageWrapper($message, $data);
    }

    /**
     * 对 Message 类进行包裹，并返回源 Message 类
     *
     * @param  string|object                      $message
     * @param  string|array                       $data
     * @return Google\Protobuf\Internal\Message
     */
    public function rawWrapper($message, $data = [])
    {
        return $this->wrapper($message, $data)->raw();
    }

}
