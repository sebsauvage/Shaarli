<?php
/**
 * GET an HTTP URL to retrieve its content
 *
 * @param string $url      URL to get (http://...)
 * @param int    $timeout  network timeout (in seconds)
 * @param int    $maxBytes maximum downloaded bytes (default: 4 MiB)
 *
 * @return array HTTP response headers, downloaded content
 *
 * Output format:
 *  [0] = associative array containing HTTP response headers
 *  [1] = URL content (downloaded data)
 *
 * Example:
 *  list($headers, $data) = get_http_response('http://sebauvage.net/');
 *  if (strpos($headers[0], '200 OK') !== false) {
 *      echo 'Data type: '.htmlspecialchars($headers['Content-Type']);
 *  } else {
 *      echo 'There was an error: '.htmlspecialchars($headers[0]);
 *  }
 *
 * @see http://php.net/manual/en/function.file-get-contents.php
 * @see http://php.net/manual/en/function.stream-context-create.php
 * @see http://php.net/manual/en/function.get-headers.php
 */
function get_http_response($url, $timeout = 30, $maxBytes = 4194304)
{
    $urlObj = new Url($url);
    if (! filter_var($url, FILTER_VALIDATE_URL) || ! $urlObj->isHttp()) {
        return array(array(0 => 'Invalid HTTP Url'), false);
    }

    $options = array(
        'http' => array(
            'method' => 'GET',
            'timeout' => $timeout,
            'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:23.0)'
                         .' Gecko/20100101 Firefox/23.0',
            'request_fulluri' => true,
        )
    );

    $context = stream_context_create($options);
    stream_context_set_default($options);

    list($headers, $finalUrl) = get_redirected_headers($urlObj->cleanup());
    if (! $headers || strpos($headers[0], '200 OK') === false) {
        return array($headers, false);
    }

    try {
        // TODO: catch Exception in calling code (thumbnailer)
        $content = file_get_contents($finalUrl, false, $context, -1, $maxBytes);
    } catch (Exception $exc) {
        return array(array(0 => 'HTTP Error'), $exc->getMessage());
    }

    return array($headers, $content);
}

/**
 * Retrieve HTTP headers, following n redirections (temporary and permanent).
 *
 * @param string $url initial URL to reach.
 * @param int $redirectionLimit max redirection follow..
 *
 * @return array
 */
function get_redirected_headers($url, $redirectionLimit = 3)
{
    $headers = get_headers($url, 1);

    // Headers found, redirection found, and limit not reached.
    if ($redirectionLimit-- > 0
        && !empty($headers)
        && (strpos($headers[0], '301') !== false || strpos($headers[0], '302') !== false)
        && !empty($headers['Location'])) {

        $redirection = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
        if ($redirection != $url) {
            return get_redirected_headers($redirection, $redirectionLimit);
        }
    }

    return array($headers, $url);
}

/**
 * Returns the server's base URL: scheme://domain.tld[:port]
 *
 * @param array $server the $_SERVER array
 *
 * @return string the server's base URL
 *
 * @see http://www.ietf.org/rfc/rfc7239.txt
 * @see http://www.ietf.org/rfc/rfc6648.txt
 * @see http://stackoverflow.com/a/3561399
 * @see http://stackoverflow.com/q/452375
 */
function server_url($server)
{
    $scheme = 'http';
    $port = '';

    // Shaarli is served behind a proxy
    if (isset($server['HTTP_X_FORWARDED_PROTO'])) {
        // Keep forwarded scheme
        if (strpos($server['HTTP_X_FORWARDED_PROTO'], ',') !== false) {
            $schemes = explode(',', $server['HTTP_X_FORWARDED_PROTO']);
            $scheme = trim($schemes[0]);
        } else {
            $scheme = $server['HTTP_X_FORWARDED_PROTO'];
        }

        if (isset($server['HTTP_X_FORWARDED_PORT'])) {
            // Keep forwarded port
            if (strpos($server['HTTP_X_FORWARDED_PORT'], ',') !== false) {
                $ports = explode(',', $server['HTTP_X_FORWARDED_PORT']);
                $port = ':' . trim($ports[0]);
            } else {
                $port = ':' . $server['HTTP_X_FORWARDED_PORT'];
            }
        }

        return $scheme.'://'.$server['SERVER_NAME'].$port;
    }

    // SSL detection
    if ((! empty($server['HTTPS']) && strtolower($server['HTTPS']) == 'on')
        || (isset($server['SERVER_PORT']) && $server['SERVER_PORT'] == '443')) {
        $scheme = 'https';
    }

    // Do not append standard port values
    if (($scheme == 'http' && $server['SERVER_PORT'] != '80')
        || ($scheme == 'https' && $server['SERVER_PORT'] != '443')) {
        $port = ':'.$server['SERVER_PORT'];
    }

    return $scheme.'://'.$server['SERVER_NAME'].$port;
}

/**
 * Returns the absolute URL of the current script, without the query
 *
 * If the resource is "index.php", then it is removed (for better-looking URLs)
 *
 * @param array $server the $_SERVER array
 *
 * @return string the absolute URL of the current script, without the query
 */
function index_url($server)
{
    $scriptname = $server['SCRIPT_NAME'];
    if (endswith($scriptname, 'index.php')) {
        $scriptname = substr($scriptname, 0, -9);
    }
    return server_url($server) . $scriptname;
}

/**
 * Returns the absolute URL of the current script, with the query
 *
 * If the resource is "index.php", then it is removed (for better-looking URLs)
 *
 * @param array $server the $_SERVER array
 *
 * @return string the absolute URL of the current script, with the query
 */
function page_url($server)
{
    if (! empty($server['QUERY_STRING'])) {
        return index_url($server).'?'.$server['QUERY_STRING'];
    }
    return index_url($server);
}
