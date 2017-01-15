<?php
namespace Shaarli\Api;

use Shaarli\Base64Url;
use Shaarli\Api\Exceptions\ApiAuthorizationException;

/**
 * REST API utilities
 */
class ApiUtils
{
    /**
     * Validates a JWT token authenticity.
     *
     * @param string $token  JWT token extracted from the headers.
     * @param string $secret API secret set in the settings.
     *
     * @throws ApiAuthorizationException the token is not valid.
     */
    public static function validateJwtToken($token, $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3 || strlen($parts[0]) == 0 || strlen($parts[1]) == 0) {
            throw new ApiAuthorizationException('Malformed JWT token');
        }

        $genSign = Base64Url::encode(hash_hmac('sha512', $parts[0] .'.'. $parts[1], $secret, true));
        if ($parts[2] != $genSign) {
            throw new ApiAuthorizationException('Invalid JWT signature');
        }

        $header = json_decode(Base64Url::decode($parts[0]));
        if ($header === null) {
            throw new ApiAuthorizationException('Invalid JWT header');
        }

        $payload = json_decode(Base64Url::decode($parts[1]));
        if ($payload === null) {
            throw new ApiAuthorizationException('Invalid JWT payload');
        }

        if (empty($payload->iat)
            || $payload->iat > time()
            || time() - $payload->iat > ApiMiddleware::$TOKEN_DURATION
        ) {
            throw new ApiAuthorizationException('Invalid JWT issued time');
        }
    }

    /**
     * Format a Link for the REST API.
     *
     * @param array  $link     Link data read from the datastore.
     * @param string $indexUrl Shaarli's index URL (used for relative URL).
     *
     * @return array Link data formatted for the REST API.
     */
    public static function formatLink($link, $indexUrl)
    {
        $out['id'] = $link['id'];
        // Not an internal link
        if ($link['url'][0] != '?') {
            $out['url'] = $link['url'];
        } else {
            $out['url'] = $indexUrl . $link['url'];
        }
        $out['shorturl'] = $link['shorturl'];
        $out['title'] = $link['title'];
        $out['description'] = $link['description'];
        $out['tags'] = preg_split('/\s+/', $link['tags'], -1, PREG_SPLIT_NO_EMPTY);
        $out['private'] = $link['private'] == true;
        $out['created'] = $link['created']->format(\DateTime::ATOM);
        if (! empty($link['updated'])) {
            $out['updated'] = $link['updated']->format(\DateTime::ATOM);
        } else {
            $out['updated'] = '';
        }
        return $out;
    }
}
