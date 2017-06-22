<?php

namespace Mellivora\Encryption;

use InvalidArgumentException;
use Mellivora\Support\Interfaces\EncryptionInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * Mellivora\Encryption\Crypt
 *
 * <code>
 * $crypt = new \Mellivora\Encryption\Crypt();
 *
 * $key  = "le password";
 * $text = "This is a secret text";
 *
 * $encrypted = $crypt->encrypt($text, $key);
 *
 * echo $crypt->decrypt($encrypted, $key);
 * </code>
 */
class Crypt implements EncryptionInterface
{

    const PADDING_DEFAULT = 0;

    const PADDING_ANSI_X_923 = 1;

    const PADDING_PKCS7 = 2;

    const PADDING_ISO_10126 = 3;

    const PADDING_ISO_IEC_7816_4 = 4;

    const PADDING_ZERO = 5;

    const PADDING_SPACE = 6;

    protected $key;

    protected $padding;

    protected $cipher;

    /**
     * Constructor
     *
     * @param string  $key
     * @param string  $cipher
     * @param integer $padding
     */
    public function __construct($key = '', $cipher = 'aes-256-cfb', $padding = 0)
    {
        $this->setKey($key);
        $this->setCipher($cipher);
        $this->setPadding($padding);
    }

    /**
     * Changes the padding scheme used
     */
    public function setPadding($scheme)
    {
        $this->padding = $scheme;

        return $this;
    }

    /**
     * Sets the cipher algorithm
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;

        return $this;
    }

    /**
     * Returns the current cipher
     */
    public function getCipher()
    {
        return $this->cipher;
    }

    /**
     * Sets the encryption key
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Returns the encryption key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Pads texts before encryption
     *
     * @see http://www.di-mgt.com.au/cryptopad.html
     */
    protected function padText($text, $mode, $blockSize, $paddingType)
    {
        $paddingSize = 0;
        $padding     = '';

        if ($mode == 'cbc' || $mode == 'ecb') {
            $paddingSize = $blockSize - (strlen($text) % $blockSize);
            if ($paddingSize >= 256) {
                throw new InvalidArgumentException("Block size [$blockSize] is bigger than 256");
            }

            switch ($paddingType) {
                case self::PADDING_ANSI_X_923:
                    $padding = str_repeat(chr(0), $paddingSize - 1) . chr($paddingSize);
                    break;

                case self::PADDING_PKCS7:
                    $padding = str_repeat(chr($paddingSize), $paddingSize);
                    break;

                case self::PADDING_ISO_10126:
                    $padding = '';
                    foreach (range(0, $paddingSize - 2) as $i) {
                        $padding .= chr(rand());
                    }
                    $padding .= chr($paddingSize);
                    break;

                case self::PADDING_ISO_IEC_7816_4:
                    $padding = chr(0x80) . str_repeat(chr(0), $paddingSize - 1);
                    break;

                case self::PADDING_ZERO:
                    $padding = str_repeat(chr(0), $paddingSize);
                    break;

                case self::PADDING_SPACE:
                    $padding = str_repeat(' ', $paddingSize);
                    break;

                default:
                    $paddingSize = 0;
                    break;
            }
        }

        if (!$paddingSize) {
            return $text;
        }

        if ($paddingSize > $blockSize) {
            throw new InvalidArgumentException("Invalid padding size [$paddingSize]");
        }

        return $text . substr($padding, 0, $paddingSize);
    }

    /**
     * Removes padding @a padding_type from @a text
     * If the function detects that the text was not padded, it will return it unmodified
     *
     * @param string text         Message to be unpadded
     * @param string mode         Encryption mode; unpadding is applied only in CBC or ECB mode
     * @param int    $blockSize   Cipher block size
     * @param int    $paddingType Padding scheme
     */
    protected function unpadText($text, $mode, $blockSize, $paddingType)
    {
        $paddingSize = 0;
        $length      = strlen(text);
        if ($length > 0 && ($length % $blockSize == 0) && ($mode == 'cbc' || $mode == 'ecb')) {
            switch ($paddingType) {
                case self::PADDING_ANSI_X_923:
                    $last = substr($text, $length - 1, 1);
                    $ord  = (int) ord($last);
                    if ($ord <= $blockSize) {
                        $paddingSize = $ord;
                        $padding     = str_repeat(chr(0), $paddingSize - 1) . $last;
                        if (substr($text, $length - $paddingSize) != $padding) {
                            $paddingSize = 0;
                        }
                    }
                    break;

                case self::PADDING_PKCS7:
                    $last = substr($text, $length - 1, 1);
                    $ord  = (int) ord($last);
                    if ($ord <= $blockSize) {
                        $paddingSize = $ord;
                        $padding     = str_repeat(chr($paddingSize), $paddingSize);
                        if (substr($text, $length - $paddingSize) != $padding) {
                            $paddingSize = 0;
                        }
                    }
                    break;

                case self::PADDING_ISO_10126:
                    $last        = substr($text, $length - 1, 1);
                    $paddingSize = (int) ord($last);
                    break;

                case self::PADDING_ISO_IEC_7816_4:
                    $i = $length - 1;
                    while ($i > 0 && $text[$i] == 0x00 && $paddingSize < $blockSize) {
                        $paddingSize++;
                        $i--;
                    }
                    if ($text[$i] == 0x80) {
                        $paddingSize++;
                    } else {
                        $paddingSize = 0;
                    }
                    break;

                case self::PADDING_ZERO:
                    $i = $length - 1;
                    while ($i >= 0 && $text[$i] == 0x00 && $paddingSize <= $blockSize) {
                        $paddingSize++;
                        $i--;
                    }
                    break;

                case self::PADDING_SPACE:
                    $i = $length - 1;
                    while ($i >= 0 && $text[$i] == 0x20 && $paddingSize <= $blockSize) {
                        $paddingSize++;
                        $i--;
                    }
                    break;

                default:
                    break;
            }

            if ($paddingSize && $paddingSize <= $blockSize) {
                if ($paddingSize < $length) {
                    return substr($text, 0, $length - $paddingSize);
                }

                return '';
            } else {
                $paddingSize = 0;
            }
        }

        if (!$paddingSize) {
            return $text;
        }

        return '';
    }

    /**
     * Encrypts a text
     *
     * <code>
     * $encrypted = $crypt->encrypt("Ultra-secret text", "encrypt password");
     * </code>
     */
    public function encrypt($text, $key = null)
    {
        if (!function_exists('openssl_cipher_iv_length')) {
            throw new RuntimeException('openssl extension is required');
        }

        if ($key === null) {
            $encryptKey = $this->key;
        } else {
            $encryptKey = $key;
        }

        if (empty($encryptKey)) {
            throw new InvalidArgumentException('Encryption key cannot be empty');
        }

        $cipher = $this->cipher;
        $mode   = strtolower(substr($cipher, strrpos($cipher, '-') - strlen($cipher)));

        if (!in_array($cipher, openssl_get_cipher_methods(true))) {
            throw new UnexpectedValueException("Cipher algorithm is unknown [$cipher]");
        }

        $ivSize = openssl_cipher_iv_length($cipher);
        if ($ivSize > 0) {
            $blockSize = $ivSize;
        } else {
            $blockSize = openssl_cipher_iv_length(str_ireplace('-' . $mode, '', $cipher));
        }

        $iv          = openssl_random_pseudo_bytes($ivSize);
        $paddingType = $this->padding;

        if ($paddingType != 0 && ($mode == 'cbc' || $mode == 'ecb')) {
            $padded = $this->padText($text, $mode, $blockSize, $paddingType);
        } else {
            $padded = $text;
        }

        return $iv . openssl_encrypt($padded, $cipher, $encryptKey, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Decrypts an encrypted text
     *
     * <code>
     * echo $crypt->decrypt($encrypted, "decrypt password");
     * </code>
     */
    public function decrypt($text, $key = null)
    {
        if (!function_exists('openssl_cipher_iv_length')) {
            throw new RuntimeException('openssl extension is required');
        }

        if ($key === null) {
            $decryptKey = $this->key;
        } else {
            $decryptKey = $key;
        }

        if (empty($decryptKey)) {
            throw new InvalidArgumentException('Decryption key cannot be empty');
        }

        $cipher = $this->cipher;
        $mode   = strtolower(substr($cipher, strrpos($cipher, '-') - strlen($cipher)));

        if (!in_array($cipher, openssl_get_cipher_methods(true))) {
            throw new UnexpectedValueException("Cipher algorithm is unknown [$cipher]");
        }

        $ivSize = openssl_cipher_iv_length($cipher);
        if ($ivSize > 0) {
            $blockSize = $ivSize;
        } else {
            $blockSize = openssl_cipher_iv_length(str_ireplace('-' . $mode, '', $cipher));
        }

        $decrypted = openssl_decrypt(substr($text, $ivSize), $cipher, $decryptKey, OPENSSL_RAW_DATA, substr($text, 0, $ivSize));

        $paddingType = $this->padding;

        if ($mode == 'cbc' || $mode == 'ecb') {
            return $this->unpadText($decrypted, $mode, $blockSize, $paddingType);
        }

        return $decrypted;
    }

    /**
     * Encrypts a text returning the result as a base64 string
     */
    public function encryptBase64($text, $key = null, $safe = false)
    {
        if ($safe == true) {
            return strtr(base64_encode($this->encrypt($text, $key)), '+/', '-_');
        }

        return base64_encode($this->encrypt($text, $key));
    }

    /**
     * Decrypt a text that is coded as a base64 string
     */
    public function decryptBase64($text, $key = null, $safe = false)
    {
        if ($safe == true) {
            return $this->decrypt(base64_decode(strtr($text, '-_', '+/')), $key);
        }

        return $this->decrypt(base64_decode($text), $key);
    }

    /**
     * Returns a list of available ciphers
     */
    public function getAvailableCiphers()
    {
        return openssl_get_cipher_methods(true);
    }
}
