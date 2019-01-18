<?php
/**
 * Old-style mock for cURL, as PHPUnit doesn't allow to mock global functions
 */

/**
 * Returns code 200 or html content type.
 *
 * @param resource $ch   cURL resource
 * @param int      $type cURL info type
 *
 * @return int|string 200 or 'text/html'
 */
function ut_curl_getinfo_ok($ch, $type)
{
    switch ($type) {
        case CURLINFO_RESPONSE_CODE:
            return 200;
        case CURLINFO_CONTENT_TYPE:
            return 'text/html; charset=utf-8';
    }
}

/**
 * Returns code 200 or html content type without charset.
 *
 * @param resource $ch   cURL resource
 * @param int      $type cURL info type
 *
 * @return int|string 200 or 'text/html'
 */
function ut_curl_getinfo_no_charset($ch, $type)
{
    switch ($type) {
        case CURLINFO_RESPONSE_CODE:
            return 200;
        case CURLINFO_CONTENT_TYPE:
            return 'text/html';
    }
}

/**
 * Invalid response code.
 *
 * @param resource $ch   cURL resource
 * @param int      $type cURL info type
 *
 * @return int|string 404 or 'text/html'
 */
function ut_curl_getinfo_rc_ko($ch, $type)
{
    switch ($type) {
        case CURLINFO_RESPONSE_CODE:
            return 404;
        case CURLINFO_CONTENT_TYPE:
            return 'text/html; charset=utf-8';
    }
}

/**
 * Invalid content type.
 *
 * @param resource $ch   cURL resource
 * @param int      $type cURL info type
 *
 * @return int|string 200 or 'text/plain'
 */
function ut_curl_getinfo_ct_ko($ch, $type)
{
    switch ($type) {
        case CURLINFO_RESPONSE_CODE:
            return 200;
        case CURLINFO_CONTENT_TYPE:
            return 'text/plain';
    }
}

/**
 * Invalid response code and content type.
 *
 * @param resource $ch   cURL resource
 * @param int      $type cURL info type
 *
 * @return int|string 404 or 'text/plain'
 */
function ut_curl_getinfo_rs_ct_ko($ch, $type)
{
    switch ($type) {
        case CURLINFO_RESPONSE_CODE:
            return 404;
        case CURLINFO_CONTENT_TYPE:
            return 'text/plain';
    }
}
