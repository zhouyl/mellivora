<?php

namespace Mellivora\Validation;

use Mellivora\Support\Arr;
use Mellivora\Support\Traits\Macroable;

class Valid
{
    use Macroable;

    /**
     * 检查结果是否为非空
     *
     * @param  mixed     $value
     * @return boolean
     */
    public static function required($value)
    {
        if (is_object($value)) {
            $value = Arr::convert($value);
        }

        return !in_array($value, [null, false, '', []], true);
    }

    /**
     * 判断是否在数组范围内
     *
     * @param  mixed     $value
     * @param  array     $array
     * @return boolean
     */
    public static function inArray($value, array $array)
    {
        return in_array($value, $array);
    }

    /**
     * 判断是否不在数组范围内
     *
     * @param  mixed     $value
     * @param  array     $array
     * @return boolean
     */
    public static function notInArray($value, array $array)
    {
        return !in_array($value, $array);
    }

    /**
     * 正式表达式是否匹配
     *
     * @param  string    $value
     * @param  string    $expression
     * @return boolean
     */
    public static function regex($value, $expression)
    {
        return (bool) preg_match($expression, (string) $value);
    }

    /**
     * 检测字符串是否符合长度范围
     *
     * @param  string    $value
     * @param  integer   $min
     * @param  integer   $max
     * @return boolean
     */
    public static function length($value, $min = null, $max = null)
    {
        $length = mb_strlen($value);

        if (is_numeric($min) && $min > 0 && $length < $min) {
            return false;
        }

        if (is_numeric($max) && $max > 0 && $length > $max) {
            return false;
        }

        return true;
    }

    /**
     * 检测字符串是否符合指定的长度
     *
     *     Valid::exactLength($value, [10, 20]);
     *     Valid::exactLength($value, 10);
     *
     * @param  string        $value
     * @param  integer|array $length
     * @return boolean
     */
    public static function lengths($value, $length)
    {
        if (is_array($length)) {
            foreach ($length as $strlen) {
                if (mb_strlen($value) === $strlen) {
                    return true;
                }
            }
            return false;
        }

        return mb_strlen($value) === $length;
    }

    /**
     * 检测两个值是否完全相等
     *
     * @param  string    $value
     * @param  string    $target
     * @return boolean
     */
    public static function equal($value, $target, $ignoreCase = false)
    {
        if ($ignoreCase) {
            if (is_string($value)) {
                $value = strtolower($value);
            }

            if (is_string($target)) {
                $target = strtolower($target);
            }
        }

        return ($value === $target);
    }

    /**
     * 检测两个值是否完全不相等
     *
     * @param  mixed     $value
     * @param  mixed     $target
     * @return boolean
     */
    public static function notEqual($value, $target, $ignoreCase = false)
    {
        return !static::equal($value, $target);
    }

    /**
     * 检测是否有效的 email 地址
     *
     * @link  http://www.iamcal.com/publish/articles/php/parsing_email/
     * @link  http://www.w3.org/Protocols/rfc822/
     *
     * @param  string    $value
     * @param  boolean   $strict
     * @return boolean
     */
    public static function email($value, $strict = false)
    {
        if (mb_strlen($value) > 254) {
            return false;
        }

        if ($strict === true) {
            $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            $atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            $pair  = '\\x5c[\\x00-\\x7f]';

            $domain_literal = "\\x5b($dtext|$pair)*\\x5d";
            $quoted_string  = "\\x22($qtext|$pair)*\\x22";
            $sub_domain     = "($atom|$domain_literal)";
            $word           = "($atom|$quoted_string)";
            $domain         = "$sub_domain(\\x2e$sub_domain)*";
            $local_part     = "$word(\\x2e$word)*";

            $expression = "/^$local_part\\x40$domain$/D";
        } else {
            $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})$/iD';
        }

        return (bool) preg_match($expression, (string) $value);
    }

    /**
     * 检测是否有效的 url 地址
     *
     * @param  string    $value
     * @return boolean
     */
    public static function url($value)
    {
        if (!is_string($value)) {
            return false;
        }

        /*
         * This pattern is derived from Symfony\Component\Validator\Constraints\UrlValidator (2.7.4).
         *
         * (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
         */
        $pattern = '~^
            ((aaa|aaas|about|acap|acct|acr|adiumxtra|afp|afs|aim|apt|attachment|aw|barion|beshare|bitcoin|blob|bolo|callto|cap|chrome|chrome-extension|cid|coap|coaps|com-eventbrite-attendee|content|crid|cvs|data|dav|dict|dlna-playcontainer|dlna-playsingle|dns|dntp|dtn|dvb|ed2k|example|facetime|fax|feed|feedready|file|filesystem|finger|fish|ftp|geo|gg|git|gizmoproject|go|gopher|gtalk|h323|ham|hcp|http|https|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris.beep|iris.lwz|iris.xpc|iris.xpcs|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|ms-help|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|msnim|msrp|msrps|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|oid|opaquelocktoken|pack|palm|paparazzi|pkcs11|platform|pop|pres|prospero|proxy|psyc|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|secondlife|service|session|sftp|sgn|shttp|sieve|sip|sips|skype|smb|sms|smtp|snews|snmp|soap.beep|soap.beeps|soldat|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|things|thismessage|tip|tn3270|turn|turns|tv|udp|unreal|urn|ut2004|vemmi|ventrilo|videotex|view-source|wais|webcal|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s))://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # an IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+|\?\S*|\#\S*)                   # a /, nothing, a / with something, a query or a fragment
        $~ixu';

        return preg_match($pattern, $value) > 0;
    }

    /**
     * 检测是否有效的 ip 地址
     *
     * @param  string    $value
     * @return boolean
     */
    public static function ip($value)
    {
        return (bool) filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 检测是否有效的 ipv4 地址
     *
     * @param  string    $value
     * @return boolean
     */
    public static function ipv4($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * 检测是否有效的 ipv6 地址
     *
     * @param  string    $value
     * @return boolean
     */
    public static function ipv6($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 检测是否有效的日期格式
     *
     * @param  string    $value
     * @return boolean
     */
    public static function date($value)
    {
        if ($value instanceof \DateTime) {
            return true;
        }

        if ((!is_string($value) && !is_numeric($value)) || strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * 检测是否为纯字母字符 [a-zA-Z]
     *
     * @param  string    $value
     * @return boolean
     */
    public static function alpha($value)
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * 检测是否为纯字母+数字的字符 [a-zA-Z0-9]
     *
     * @param  string    $value
     * @return boolean
     */
    public static function alphaNumeric($value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }

    /**
     * 检测是否为纯字母+数字+下划线+中划线的字符 [a-zA-Z0-9]
     *
     * @param  string    $value
     * @return boolean
     */
    public static function alphaDash($value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) > 0;
    }

    /**
     * 检查字符串是否仅由数字组成
     *
     * @param  string    $value
     * @return boolean
     */
    public static function digit($value)
    {
        return (bool) preg_match('/^\pN++$/uD', $value);
    }

    /**
     * 检测是否有效的数字 (包括本地化字符格式)
     *
     * Uses {@link http://www.php.net/manual/en/function.localeconv.php locale conversion}
     * to allow decimal point to be locale specific.
     *
     * @param  string    input string
     * @return boolean
     */
    public static function numeric($value)
    {
        // Get the decimal point for the current locale
        list($decimal) = array_values(localeconv());

        // A lookahead is used to make sure the string contains at least one digit (before or after the decimal point)
        return (bool) preg_match('/^-?+(?=.*[0-9])[0-9]*+' . preg_quote($decimal) . '?+[0-9]*+$/D', (string) $value);
    }

    /**
     * 检测数字是否在指定的大小范围内
     *
     * @param  string    $value
     * @param  integer   $min
     * @param  integer   $max
     * @return boolean
     */
    public static function range($value, $min, $max)
    {
        return ($value >= $min && $value <= $max);
    }

    /**
     * 检测数字是否在指定的大小范围内
     *
     * @param  string    $value
     * @param  integer   $min
     * @param  integer   $max
     * @return boolean
     */
    public static function between($value, $min, $max)
    {
        return ($value >= $min && $value <= $max);
    }

    /**
     * 检测是否大于指定的值
     *
     * @param  integer   $value
     * @param  integer   $min
     * @return boolean
     */
    public static function min($value, $min = 0)
    {
        return $value > $min;
    }

    /**
     * 检测是否小于指定的值
     *
     * @param  integer   $value
     * @param  integer   $max
     * @return boolean
     */
    public static function max($value, $max = 0)
    {
        return $value < $max;
    }

    /**
     * 检测数字是否满足有效的位数
     *
     * @param  string    $value
     * @param  integer   $places  小数点后的位数
     * @param  integer   $digits  小数点前的位数
     * @return boolean
     */
    public static function decimal($value, $places = 2, $digits = null)
    {
        if ($digits > 0) {
            $digits = '{' . ((int) $digits) . '}';
        } else {
            $digits = '+';
        }

        // Get the decimal point for the current locale
        list($decimal) = array_values(localeconv());

        return (bool) preg_match('/^[+-]?[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . ((int) $places) . '}$/D', $value);
    }

    /**
     * 检测是否合法的 html color 值
     *
     * @param  string    $value
     * @return boolean
     */
    public static function color($value)
    {
        return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $value);
    }

    /**
     * 检测是否有效的 json 数据
     *
     * @param  string    $value
     * @return boolean
     */
    public static function json($value)
    {
        if (!is_scalar($value) && !method_exists($value, '__toString')) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 检测数组中两个不同 key 的值是否相同
     *
     * @param  array     $array
     * @param  string    $field
     * @param  string    $match
     * @return boolean
     */
    public static function matches($array, $field, $match)
    {
        return ($array[$field] === $array[$match]);
    }
}
