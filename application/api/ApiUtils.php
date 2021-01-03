<?php

namespace Shaarli\Api;

use Shaarli\Api\Exceptions\ApiAuthorizationException;
use Shaarli\Bookmark\Bookmark;
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
     * @return bool true on success
     *
     * @throws ApiAuthorizationException the token is not valid.
     */
    public static function validateJwtToken($token, $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3 || strlen($parts[0]) == 0 || strlen($parts[1]) == 0) {
            throw new ApiAuthorizationException('Malformed JWT token');
        }

        $genSign = Base64Url::encode(hash_hmac('sha512', $parts[0] . '.' . $parts[1], $secret, true));
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

        if (
            empty($payload->iat)
            || $payload->iat > time()
            || time() - $payload->iat > ApiMiddleware::$TOKEN_DURATION
        ) {
            throw new ApiAuthorizationException('Invalid JWT issued time');
        }

        return true;
    }

    /**
     * Format a Link for the REST API.
     *
     * @param Bookmark $bookmark Bookmark data read from the datastore.
     * @param string   $indexUrl Shaarli's index URL (used for relative URL).
     *
     * @return array Link data formatted for the REST API.
     */
    public static function formatLink($bookmark, $indexUrl)
    {
        $out['id'] = $bookmark->getId();
        // Not an internal link
        if (! $bookmark->isNote()) {
            $out['url'] = $bookmark->getUrl();
        } else {
            $out['url'] = rtrim($indexUrl, '/') . '/' . ltrim($bookmark->getUrl(), '/');
        }
        $out['shorturl'] = $bookmark->getShortUrl();
        $out['title'] = $bookmark->getTitle();
        $out['description'] = $bookmark->getDescription();
        $out['tags'] = $bookmark->getTags();
        $out['private'] = $bookmark->isPrivate();
        $out['created'] = $bookmark->getCreated()->format(\DateTime::ATOM);
        if (! empty($bookmark->getUpdated())) {
            $out['updated'] = $bookmark->getUpdated()->format(\DateTime::ATOM);
        } else {
            $out['updated'] = '';
        }
        return $out;
    }

    /**
     * Convert a link given through a request, to a valid Bookmark for the datastore.
     *
     * If no URL is provided, it will generate a local note URL.
     * If no title is provided, it will use the URL as title.
     *
     * @param array|null $input          Request Link.
     * @param bool       $defaultPrivate Setting defined if a bookmark is private by default.
     * @param string     $tagsSeparator  Tags separator loaded from the config file.
     *
     * @return Bookmark instance.
     */
    public static function buildBookmarkFromRequest(
        ?array $input,
        bool $defaultPrivate,
        string $tagsSeparator
    ): Bookmark {
        $bookmark = new Bookmark();
        $url = ! empty($input['url']) ? cleanup_url($input['url']) : '';
        if (isset($input['private'])) {
            $private = filter_var($input['private'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $private = $defaultPrivate;
        }

        $bookmark->setTitle(! empty($input['title']) ? $input['title'] : '');
        $bookmark->setUrl($url);
        $bookmark->setDescription(! empty($input['description']) ? $input['description'] : '');

        // Be permissive with provided tags format
        if (is_string($input['tags'] ?? null)) {
            $input['tags'] = tags_str2array($input['tags'], $tagsSeparator);
        }
        if (is_array($input['tags'] ?? null) && count($input['tags']) === 1 && is_string($input['tags'][0])) {
            $input['tags'] = tags_str2array($input['tags'][0], $tagsSeparator);
        }

        $bookmark->setTags(! empty($input['tags']) ? $input['tags'] : []);
        $bookmark->setPrivate($private);

        $created = \DateTime::createFromFormat(\DateTime::ATOM, $input['created'] ?? '');
        if ($created instanceof \DateTimeInterface) {
            $bookmark->setCreated($created);
        }
        $updated = \DateTime::createFromFormat(\DateTime::ATOM, $input['updated'] ?? '');
        if ($updated instanceof \DateTimeInterface) {
            $bookmark->setUpdated($updated);
        }

        return $bookmark;
    }

    /**
     * Update link fields using an updated link object.
     *
     * @param Bookmark $oldLink data
     * @param Bookmark $newLink data
     *
     * @return Bookmark $oldLink updated with $newLink values
     */
    public static function updateLink($oldLink, $newLink)
    {
        $oldLink->setTitle($newLink->getTitle());
        $oldLink->setUrl($newLink->getUrl());
        $oldLink->setDescription($newLink->getDescription());
        $oldLink->setTags($newLink->getTags());
        $oldLink->setPrivate($newLink->isPrivate());

        return $oldLink;
    }

    /**
     * Format a Tag for the REST API.
     *
     * @param string $tag         Tag name
     * @param int    $occurrences Number of bookmarks using this tag
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
