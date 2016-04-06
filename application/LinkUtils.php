<?php

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
 * Determine charset from downloaded page.
 * Priority:
 *   1. HTTP headers (Content type).
 *   2. HTML content page (tag <meta charset>).
 *   3. Use a default charset (default: UTF-8).
 *
 * @param array  $headers           HTTP headers array.
 * @param string $htmlContent       HTML content where to look for charset.
 * @param string $defaultCharset    Default charset to apply if other methods failed.
 *
 * @return string Determined charset.
 */
function get_charset($headers, $htmlContent, $defaultCharset = 'utf-8')
{
    if ($charset = headers_extract_charset($headers)) {
        return $charset;
    }

    if ($charset = html_extract_charset($htmlContent)) {
        return $charset;
    }

    return $defaultCharset;
}

/**
 * Extract charset from HTTP headers if it's defined.
 *
 * @param array $headers HTTP headers array.
 *
 * @return bool|string Charset string if found (lowercase), false otherwise.
 */
function headers_extract_charset($headers)
{
    if (! empty($headers['Content-Type']) && strpos($headers['Content-Type'], 'charset=') !== false) {
        preg_match('/charset="?([^; ]+)/i', $headers['Content-Type'], $match);
        if (! empty($match[1])) {
            return strtolower(trim($match[1]));
        }
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
