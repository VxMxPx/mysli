<?php

namespace mysli\util\crypt;

class crypt
{
    /**
     * Encrypt wrapper
     * @param  string $text
     * @param  string $key
     * @return string
     */
    static function encrypt($text, $key)
    {
        return self::AES_encrypt($text, $key);
    }
    /**
     * Decrypt wrapper
     * @param  string $text
     * @param  string $key
     * @return string
     */
    static function decrypt($text, $key)
    {
        return self::AES_decrypt($text, $key);
    }

    /**
     * Encrypt string using AES (Advanced Encryption Standard)
     * @author (original) http://www.zimuel.it/
     * @param  string $text
     * @param  string $key
     * @return string
     */
    static function AES_encrypt($text, $key)
    {
        // Padding PKCS#7
        $text    = self::PKCS7_pad($text, 16);
        // Random IV
        $iv      = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        // Encryption key generated by PBKDF2 (since PHP 5.5)
        $keys    = hash_pbkdf2('sha256', $key, $iv, 10000, 64);
        $encKey  = substr($keys, 0, 32); // 256 bit encryption key
        $hmacKey = substr($keys, 32);    // 256 bit hmac key
        // Encryption
        if (function_exists('openssl_encrypt'))
        {
            $ciphertext = openssl_encrypt(
                $text, 'AES-256-CBC', $encKey, OPENSSL_NO_PADDING, $iv
            );
        }
        else
        {
            $ciphertext = mcrypt_encrypt(
                'rijndael-128', $encKey, $text, 'cbc', $iv
            );
        }
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $hmacKey);

        return $hmac . $iv . $ciphertext;
    }
    /**
     * Decrypt string using AES
     * @author (original) http://www.zimuel.it/
     * @param  string $text
     * @param  string $key
     * @return string
     */
    static function AES_decrypt($text, $key)
    {
        $hmac = substr($text, 0, 64);  // 64 bytes HMAC size
        $iv   = substr($text, 64, 16); // 16 bytes IV size
        $text = substr($text, 80);
        // Generate the encryption and hmac keys
        $keys    = hash_pbkdf2('sha256', $key, $iv, 10000, 64);
        $encKey  = substr($keys, 0, 32); // 256 bit encryption key
        $hmacNew = hash_hmac('sha256', $iv . $text, substr($keys, 32));

        if (!self::compare_strings($hmac, $hmacNew))
        {
            // to prevent timing attacks
            return false;
        }

        // Decryption
        if (function_exists('openssl_decrypt'))
        {
            $result = openssl_decrypt(
                $text, 'AES-256-CBC', $encKey, OPENSSL_NO_PADDING, $iv
            );
        }
        else
        {
            $result = mcrypt_decrypt(
                'rijndael-128', $encKey, $text, 'cbc', $iv
            );
        }

        return self::PKCS7_unpad($result);
    }
    private static function PKCS7_pad($text, $blockSize)
    {
        $pad = $blockSize - (strlen($text) % $blockSize);
        return $text . str_repeat(chr($pad), $pad);
    }
    private static function PKCS7_unpad($text)
    {
        $end  = substr($text, -1);
        $last = ord($end);
        $len  = strlen($text) - $last;

        if (substr($text, $len) == str_repeat($end, $last))
        {
            return substr($text, 0, $len);
        }

        return false;
    }
    /**
     * Compare two strings to avoid timing attacks
     *
     * C function memcmp() internally used by PHP, exits as soon as a difference
     * is found in the two buffers. That makes possible of leaking
     * timing information useful to an attacker attempting to iteratively guess
     * the unknown string (e.g. password).
     *
     * Zend Framework (http://framework.zend.com/)
     *
     * @link      http://github.com/zendframework/zf2
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @param  string $expected
     * @param  string $actual
     * @return bool
     */
    private static function compare_strings($expected, $actual)
    {
        $expected     = (string) $expected;
        $actual       = (string) $actual;
        $lenExpected  = strlen($expected);
        $lenActual    = strlen($actual);
        $len          = min($lenExpected, $lenActual);

        $result = 0;

        for ($i = 0; $i < $len; $i++)
        {
            $result |= ord($expected[$i]) ^ ord($actual[$i]);
        }

        $result |= $lenExpected ^ $lenActual;

        return ($result === 0);
    }
}
