<?php

/**
 * Get cURL callback function for CURLOPT_WRITEFUNCTION
 *
 * @param string $charset     to extract from the downloaded page (reference)
 * @param string $title       to extract from the downloaded page (reference)
 * @param string $curlGetInfo Optionnaly overrides curl_getinfo function
 *
 * @return Closure
 */
function get_curl_download_callback(&$charset, &$title, $curlGetInfo = 'curl_getinfo')
{
    $isRedirected = false;
    /**
     * cURL callback function for CURLOPT_WRITEFUNCTION (called during the download).
     *
     * While downloading the remote page, we check that the HTTP code is 200 and content type is 'html/text'
     * Then we extract the title and the charset and stop the download when it's done.
     *
     * @param resource $ch   cURL resource
     * @param string   $data chunk of data being downloaded
     *
     * @return int|bool length of $data or false if we need to stop the download
     */
    return function (&$ch, $data) use ($curlGetInfo, &$charset, &$title, &$isRedirected) {
        $responseCode = $curlGetInfo($ch, CURLINFO_RESPONSE_CODE);
        if (!empty($responseCode) && in_array($responseCode, [301, 302])) {
            $isRedirected = true;
            return strlen($data);
        }
        if (!empty($responseCode) && $responseCode !== 200) {
            return false;
        }
        // After a redirection, the content type will keep the previous request value
        // until it finds the next content-type header.
        if (! $isRedirected || strpos(strtolower($data), 'content-type') !== false) {
            $contentType = $curlGetInfo($ch, CURLINFO_CONTENT_TYPE);
        }
        if (!empty($contentType) && strpos($contentType, 'text/html') === false) {
            return false;
        }
        if (!empty($contentType) && empty($charset)) {
            $charset = header_extract_charset($contentType);
        }
        if (empty($charset)) {
            $charset = html_extract_charset($data);
        }
        if (empty($title)) {
            $title = html_extract_title($data);
        }
        // We got everything we want, stop the download.
        if (!empty($responseCode) && !empty($contentType) && !empty($charset) && !empty($title)) {
            return false;
        }

        return strlen($data);
    };
}

/**
 * Extract title from an HTML document.
 *
 * @param string $html HTML content where to look for a title.
 *
 * @return bool|string Extracted title if found, false otherwise.
 */
function html_extract_title($html)
{
    if (preg_match('!<title.*?>(.*?)</title>!is', $html, $matches)) {
        return trim(str_replace("\n", '', $matches[1]));
    }
    return false;
}

/**
 * Extract charset from HTTP header if it's defined.
 *
 * @param string $header HTTP header Content-Type line.
 *
 * @return bool|string Charset string if found (lowercase), false otherwise.
 */
function header_extract_charset($header)
{
    preg_match('/charset="?([^; ]+)/i', $header, $match);
    if (! empty($match[1])) {
        return strtolower(trim($match[1]));
    }

    return false;
}

/**
 * Extract charset HTML content (tag <meta charset>).
 *
 * @param string $html HTML content where to look for charset.
 *
 * @return bool|string Charset string if found, false otherwise.
 */
function html_extract_charset($html)
{
    // Get encoding specified in HTML header.
    preg_match('#<meta .*charset=["\']?([^";\'>/]+)["\']? */?>#Usi', $html, $enc);
    if (!empty($enc[1])) {
        return strtolower($enc[1]);
    }

    return false;
}

/**
 * Count private links in given linklist.
 *
 * @param array|Countable $links Linklist.
 *
 * @return int Number of private links.
 */
function count_private($links)
{
    $cpt = 0;
    foreach ($links as $link) {
        if ($link['private']) {
            $cpt += 1;
        }
    }

    return $cpt;
}

/**
 * In a string, converts URLs to clickable links.
 *
 * @param string $text       input string.
 * @param string $redirector if a redirector is set, use it to gerenate links.
 * @param bool   $urlEncode  Use `urlencode()` on the URL after the redirector or not.
 *
 * @return string returns $text with all links converted to HTML links.
 *
 * @see Function inspired from http://www.php.net/manual/en/function.preg-replace.php#85722
 */
function text2clickable($text, $redirector = '', $urlEncode = true)
{
    $regex = '!(((?:https?|ftp|file)://|apt:|magnet:)\S+[a-z0-9\(\)]/?)!si';

    if (empty($redirector)) {
        return preg_replace($regex, '<a href="$1">$1</a>', $text);
    }
    // Redirector is set, urlencode the final URL.
    return preg_replace_callback(
        $regex,
        function ($matches) use ($redirector, $urlEncode) {
            $url = $urlEncode ? urlencode($matches[1]) : $matches[1];
            return '<a href="' . $redirector . $url .'">'. $matches[1] .'</a>';
        },
        $text
    );
}

/**
 * Auto-link hashtags.
 *
 * @param string $description Given description.
 * @param string $indexUrl    Root URL.
 *
 * @return string Description with auto-linked hashtags.
 */
function hashtag_autolink($description, $indexUrl = '')
{
    /*
     * To support unicode: http://stackoverflow.com/a/35498078/1484919
     * \p{Pc} - to match underscore
     * \p{N} - numeric character in any script
     * \p{L} - letter from any language
     * \p{Mn} - any non marking space (accents, umlauts, etc)
     */
    $regex = '/(^|\s)#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/mui';
    $replacement = '$1<a href="'. $indexUrl .'?addtag=$2" title="Hashtag $2">#$2</a>';
    return preg_replace($regex, $replacement, $description);
}

/**
 * This function inserts &nbsp; where relevant so that multiple spaces are properly displayed in HTML
 * even in the absence of <pre>  (This is used in description to keep text formatting).
 *
 * @param string $text input text.
 *
 * @return string formatted text.
 */
function space2nbsp($text)
{
    return preg_replace('/(^| ) /m', '$1&nbsp;', $text);
}

/**
 * Format Shaarli's description
 *
 * @param string $description shaare's description.
 * @param string $redirector  if a redirector is set, use it to gerenate links.
 * @param bool   $urlEncode  Use `urlencode()` on the URL after the redirector or not.
 * @param string $indexUrl    URL to Shaarli's index.

 * @return string formatted description.
 */
function format_description($description, $redirector = '', $urlEncode = true, $indexUrl = '')
{
    return nl2br(space2nbsp(hashtag_autolink(text2clickable($description, $redirector, $urlEncode), $indexUrl)));
}

/**
 * Generate a small hash for a link.
 *
 * @param DateTime $date Link creation date.
 * @param int      $id   Link ID.
 *
 * @return string the small hash generated from link data.
 */
function link_small_hash($date, $id)
{
    return smallHash($date->format(LinkDB::LINK_DATE_FORMAT) . $id);
}
