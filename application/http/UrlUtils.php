<?php

/**
 * Converts an array-represented URL to a string
 *
 * Source: http://php.net/manual/en/function.parse-url.php#106731
 *
 * @see http://php.net/manual/en/function.parse-url.php
 *
 * @param array $parsedUrl an array-represented URL
 *
 * @return string the string representation of the URL
 */
function unparse_url($parsedUrl)
{
    $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
    $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
    $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
    $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
    $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
    $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

    return "$scheme$user$pass$host$port$path$query$fragment";
}

/**
 * Removes undesired query parameters and fragments
 *
 * @param string url UrlUtils to be cleaned
 *
 * @return string the string representation of this URL after cleanup
 */
function cleanup_url($url)
{
    $obj_url = new \Shaarli\Http\Url($url);
    return $obj_url->cleanup();
}

/**
 * Get URL scheme.
 *
 * @param string url UrlUtils for which the scheme is requested
 *
 * @return mixed the URL scheme or false if none is provided.
 */
function get_url_scheme($url)
{
    $obj_url = new \Shaarli\Http\Url($url);
    return $obj_url->getScheme();
}

/**
 * Adds a trailing slash at the end of URL if necessary.
 *
 * @param string $url URL to check/edit.
 *
 * @return string $url URL with a end trailing slash.
 */
function add_trailing_slash($url)
{
    return $url . (!endsWith($url, '/') ? '/' : '');
}

/**
 * Replace not whitelisted protocols by 'http://' from given URL.
 *
 * @param string $url       URL to clean
 * @param array  $protocols List of allowed protocols (aside from http(s)).
 *
 * @return string URL with allowed protocol
 */
function whitelist_protocols($url, $protocols)
{
    if (startsWith($url, '?') || startsWith($url, '/') || startsWith($url, '#')) {
        return $url;
    }
    $protocols = array_merge(['http', 'https'], $protocols);
    $protocol = preg_match('#^(\w+):/?/?#', $url, $match);
    // Protocol not allowed: we remove it and replace it with http
    if ($protocol === 1 && ! in_array($match[1], $protocols)) {
        $url = str_replace($match[0], 'http://', $url);
    } elseif ($protocol !== 1) {
        $url = 'http://' . $url;
    }
    return $url;
}
