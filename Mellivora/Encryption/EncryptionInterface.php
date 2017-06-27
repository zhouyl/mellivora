<?php

namespace Mellivora\Support\Interfaces;

interface EncryptionInterface
{

    /**
     * Sets the cipher algorithm
     */
    public function setCipher($cipher);

    /**
     * Returns the current cipher
     */
    public function getCipher();

    /**
     * Sets the encryption key
     */
    public function setKey($key);

    /**
     * Returns the encryption key
     */
    public function getKey();

    /**
     * Encrypts a text
     */
    public function encrypt($text, $key = null);

    /**
     * Decrypts a text
     */
    public function decrypt($text, $key = null);

    /**
     * Encrypts a text returning the result as a base64 string
     */
    public function encryptBase64($text, $key = null);

    /**
     * Decrypt a text that is coded as a base64 string
     */
    public function decryptBase64($text, $key = null);

    /**
     * Returns a list of available cyphers
     */
    public function getAvailableCiphers();

}
