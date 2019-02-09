<?php
namespace Shaarli\Api;

use Shaarli\Api\Exceptions\ApiAuthorizationException;
use Shaarli\Http\Base64Url;

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
        if (! is_note($link['url'])) {
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

    /**
     * Convert a link given through a request, to a valid link for LinkDB.
     *
     * If no URL is provided, it will generate a local note URL.
     * If no title is provided, it will use the URL as title.
     *
     * @param array  $input          Request Link.
     * @param bool   $defaultPrivate Request Link.
     *
     * @return array Formatted link.
     */
    public static function buildLinkFromRequest($input, $defaultPrivate)
    {
        $input['url'] = ! empty($input['url']) ? cleanup_url($input['url']) : '';
        if (isset($input['private'])) {
            $private = filter_var($input['private'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $private = $defaultPrivate;
        }

        $link = [
            'title'         => ! empty($input['title']) ? $input['title'] : $input['url'],
            'url'           => $input['url'],
            'description'   => ! empty($input['description']) ? $input['description'] : '',
            'tags'          => ! empty($input['tags']) ? implode(' ', $input['tags']) : '',
            'private'       => $private,
            'created'       => new \DateTime(),
        ];
        return $link;
    }

    /**
     * Update link fields using an updated link object.
     *
     * @param array $oldLink data
     * @param array $newLink data
     *
     * @return array $oldLink updated with $newLink values
     */
    public static function updateLink($oldLink, $newLink)
    {
        foreach (['title', 'url', 'description', 'tags', 'private'] as $field) {
            $oldLink[$field] = $newLink[$field];
        }
        $oldLink['updated'] = new \DateTime();

        if (empty($oldLink['url'])) {
            $oldLink['url'] = '?' . $oldLink['shorturl'];
        }

        if (empty($oldLink['title'])) {
            $oldLink['title'] = $oldLink['url'];
        }

        return $oldLink;
    }

    /**
     * Format a Tag for the REST API.
     *
     * @param string $tag         Tag name
     * @param int    $occurrences Number of links using this tag
     *
     * @return array Link data formatted for the REST API.
     */
    public static function formatTag($tag, $occurences)
    {
        return [
            'name'       => $tag,
            'occurrences' => $occurences,
        ];
    }
}
