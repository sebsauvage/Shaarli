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
 *  list($headers, $data) = get_http_url('http://sebauvage.net/');
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
function get_http_url($url, $timeout = 30, $maxBytes = 4194304)
{
    $options = array(
        'http' => array(
            'method' => 'GET',
            'timeout' => $timeout,
            'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:23.0)'
                         .' Gecko/20100101 Firefox/23.0'
        )
    );

    $context = stream_context_create($options);

    try {
        // TODO: catch Exception in calling code (thumbnailer)
        $content = file_get_contents($url, false, $context, -1, $maxBytes);
    } catch (Exception $exc) {
        return array(array(0 => 'HTTP Error'), $exc->getMessage());
    }

    if (!$content) {
        return array(array(0 => 'HTTP Error'), '');
    }

    return array(get_headers($url, 1), $content);
}
