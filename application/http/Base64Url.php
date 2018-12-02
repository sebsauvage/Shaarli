<?php

namespace Shaarli\Http;

/**
 * URL-safe Base64 operations
 *
 * @see https://en.wikipedia.org/wiki/Base64#URL_applications
 */
class Base64Url
{
    /**
     * Base64Url-encodes data
     *
     * @param string $data Data to encode
     *
     * @return string Base64Url-encoded data
     */
    public static function encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodes Base64Url-encoded data
     *
     * @param string $data Data to decode
     *
     * @return string Decoded data
     */
    public static function decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
