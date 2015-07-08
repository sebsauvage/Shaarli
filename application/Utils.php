<?php
/**
 * Shaarli utilities
 */

/**
 *  Returns the small hash of a string, using RFC 4648 base64url format
 *
 *  Small hashes:
 *   - are unique (well, as unique as crc32, at last)
 *   - are always 6 characters long.
 *   - only use the following characters: a-z A-Z 0-9 - _ @
 *   - are NOT cryptographically secure (they CAN be forged)
 *
 *  In Shaarli, they are used as a tinyurl-like link to individual entries,
 *  e.g. smallHash('20111006_131924') --> yZH23w
 */
function smallHash($text)
{
    $t = rtrim(base64_encode(hash('crc32', $text, true)), '=');
    return strtr($t, '+/', '-_');
}

/**
 * Tells if a string start with a substring
 */
function startsWith($haystack, $needle, $case=true)
{
    if ($case) {
        return (strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
    }
    return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
}

/**
 * Tells if a string ends with a substring
 */
function endsWith($haystack, $needle, $case=true)
{
    if ($case) {
        return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
}

/**
 * Same as nl2br(), but escapes < and >
 */
function nl2br_escaped($html)
{
    return str_replace('>','&gt;',str_replace('<','&lt;',nl2br($html)));
}

/**
 * htmlspecialchars wrapper
 */
function escape($str)
{
    return htmlspecialchars($str, ENT_COMPAT, 'UTF-8', false);
}

/**
 * Link sanitization before templating
 */
function sanitizeLink(&$link)
{
    $link['url'] = escape($link['url']); // useful?
    $link['title'] = escape($link['title']);
    $link['description'] = escape($link['description']);
    $link['tags'] = escape($link['tags']);
}

/**
 * Checks if a string represents a valid date
 *
 * @param string        a string-formatted date
 * @param format        the expected DateTime format of the string
 * @return              whether the string is a valid date
 * @see                 http://php.net/manual/en/class.datetime.php
 * @see                 http://php.net/manual/en/datetime.createfromformat.php
 */
function checkDateFormat($format, $string)
{
    $date = DateTime::createFromFormat($format, $string);
    return $date && $date->format($string) == $string;
}
?>
