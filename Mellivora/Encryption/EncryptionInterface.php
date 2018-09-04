<?php

namespace Mellivora\Support\Interfaces;

interface EncryptionInterface
{
    /**
     * Sets the cipher algorithm
     *
     * @param mixed $cipher
     */
    public function setCipher($cipher);

    /**
     * Returns the current cipher
     */
    public function getCipher();

    /**
     * Sets the encryption key
     *
     * @param mixed $key
     */
    public function setKey($key);

    /**
     * Returns the encryption key
     */
    public function getKey();

    /**
     * Encrypts a text
     *
     * @param mixed      $text
     * @param null|mixed $key
     */
    public function encrypt($text, $key = null);

    /**
     * Decrypts a text
     *
     * @param mixed      $text
     * @param null|mixed $key
     */
    public function decrypt($text, $key = null);

    /**
     * Encrypts a text returning the result as a base64 string
     *
     * @param mixed      $text
     * @param null|mixed $key
     */
    public function encryptBase64($text, $key = null);

    /**
     * Decrypt a text that is coded as a base64 string
     *
     * @param mixed      $text
     * @param null|mixed $key
     */
    public function decryptBase64($text, $key = null);

    /**
     * Returns a list of available cyphers
     */
    public function getAvailableCiphers();
}
