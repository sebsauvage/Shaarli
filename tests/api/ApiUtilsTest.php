<?php

namespace Shaarli\Api;

use Shaarli\Bookmark\Bookmark;
use Shaarli\Http\Base64Url;

/**
 * Class ApiUtilsTest
 */
class ApiUtilsTest extends \Shaarli\TestCase
{
    /**
     * Force the timezone for ISO datetimes.
     */
    public static function setUpBeforeClass(): void
    {
        date_default_timezone_set('UTC');
    }

    /**
     * Generate a valid JWT token.
     *
     * @param string $secret API secret used to generate the signature.
     *
     * @return string Generated token.
     */
    public static function generateValidJwtToken($secret)
    {
        $header = Base64Url::encode('{
            "typ": "JWT",
            "alg": "HS512"
        }');
        $payload = Base64Url::encode('{
            "iat": '. time() .'
        }');
        $signature = Base64Url::encode(hash_hmac('sha512', $header .'.'. $payload, $secret, true));
        return $header .'.'. $payload .'.'. $signature;
    }

    /**
     * Generate a JWT token from given header and payload.
     *
     * @param string $header  Header in JSON format.
     * @param string $payload Payload in JSON format.
     * @param string $secret  API secret used to hash the signature.
     *
     * @return string JWT token.
     */
    public static function generateCustomJwtToken($header, $payload, $secret)
    {
        $header = Base64Url::encode($header);
        $payload = Base64Url::encode($payload);
        $signature = Base64Url::encode(hash_hmac('sha512', $header . '.' . $payload, $secret, true));
        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Test validateJwtToken() with a valid JWT token.
     */
    public function testValidateJwtTokenValid()
    {
        $secret = 'WarIsPeace';
        $this->assertTrue(ApiUtils::validateJwtToken(self::generateValidJwtToken($secret), $secret));
    }

    /**
     * Test validateJwtToken() with a malformed JWT token.
     */
    public function testValidateJwtTokenMalformed()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Malformed JWT token');

        $token = 'ABC.DEF';
        ApiUtils::validateJwtToken($token, 'foo');
    }

    /**
     * Test validateJwtToken() with an empty JWT token.
     */
    public function testValidateJwtTokenMalformedEmpty()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Malformed JWT token');

        $token = false;
        ApiUtils::validateJwtToken($token, 'foo');
    }

    /**
     * Test validateJwtToken() with a JWT token without header.
     */
    public function testValidateJwtTokenMalformedEmptyHeader()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Malformed JWT token');

        $token = '.payload.signature';
        ApiUtils::validateJwtToken($token, 'foo');
    }

    /**
     * Test validateJwtToken() with a JWT token without payload
     */
    public function testValidateJwtTokenMalformedEmptyPayload()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Malformed JWT token');

        $token = 'header..signature';
        ApiUtils::validateJwtToken($token, 'foo');
    }

    /**
     * Test validateJwtToken() with a JWT token with an empty signature.
     */
    public function testValidateJwtTokenInvalidSignatureEmpty()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT signature');

        $token = 'header.payload.';
        ApiUtils::validateJwtToken($token, 'foo');
    }

    /**
     * Test validateJwtToken() with a JWT token with an invalid signature.
     */
    public function testValidateJwtTokenInvalidSignature()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT signature');

        $token = 'header.payload.nope';
        ApiUtils::validateJwtToken($token, 'foo');
    }

    /**
     * Test validateJwtToken() with a JWT token with a signature generated with the wrong API secret.
     */
    public function testValidateJwtTokenInvalidSignatureSecret()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT signature');

        ApiUtils::validateJwtToken(self::generateValidJwtToken('foo'), 'bar');
    }

    /**
     * Test validateJwtToken() with a JWT token with a an invalid header (not JSON).
     */
    public function testValidateJwtTokenInvalidHeader()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT header');

        $token = $this->generateCustomJwtToken('notJSON', '{"JSON":1}', 'secret');
        ApiUtils::validateJwtToken($token, 'secret');
    }

    /**
     * Test validateJwtToken() with a JWT token with a an invalid payload (not JSON).
     */
    public function testValidateJwtTokenInvalidPayload()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT payload');

        $token = $this->generateCustomJwtToken('{"JSON":1}', 'notJSON', 'secret');
        ApiUtils::validateJwtToken($token, 'secret');
    }

    /**
     * Test validateJwtToken() with a JWT token without issued time.
     */
    public function testValidateJwtTokenInvalidTimeEmpty()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT issued time');

        $token = $this->generateCustomJwtToken('{"JSON":1}', '{"JSON":1}', 'secret');
        ApiUtils::validateJwtToken($token, 'secret');
    }

    /**
     * Test validateJwtToken() with an expired JWT token.
     */
    public function testValidateJwtTokenInvalidTimeExpired()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT issued time');

        $token = $this->generateCustomJwtToken('{"JSON":1}', '{"iat":' . (time() - 600) . '}', 'secret');
        ApiUtils::validateJwtToken($token, 'secret');
    }

    /**
     * Test validateJwtToken() with a JWT token issued in the future.
     */
    public function testValidateJwtTokenInvalidTimeFuture()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiAuthorizationException::class);
        $this->expectExceptionMessage('Invalid JWT issued time');

        $token = $this->generateCustomJwtToken('{"JSON":1}', '{"iat":' . (time() + 60) . '}', 'secret');
        ApiUtils::validateJwtToken($token, 'secret');
    }

    /**
     * Test formatLink() with a link using all useful fields.
     */
    public function testFormatLinkComplete()
    {
        $indexUrl = 'https://domain.tld/sub/';
        $data = [
            'id' => 12,
            'url' => 'http://lol.lol',
            'shorturl' => 'abc',
            'title' => 'Important Title',
            'description' => 'It is very lol<tag>' . PHP_EOL . 'new line',
            'tags' => 'blip   .blop ',
            'private' => '1',
            'created' => \DateTime::createFromFormat('Ymd_His', '20170107_160102'),
            'updated' => \DateTime::createFromFormat('Ymd_His', '20170107_160612'),
        ];
        $bookmark = new Bookmark();
        $bookmark->fromArray($data);

        $expected = [
            'id' => 12,
            'url' => 'http://lol.lol',
            'shorturl' => 'abc',
            'title' => 'Important Title',
            'description' => 'It is very lol<tag>' . PHP_EOL . 'new line',
            'tags' => ['blip', '.blop'],
            'private' => true,
            'created' => '2017-01-07T16:01:02+00:00',
            'updated' => '2017-01-07T16:06:12+00:00',
        ];

        $this->assertEquals($expected, ApiUtils::formatLink($bookmark, $indexUrl));
    }

    /**
     * Test formatLink() with only minimal fields filled, and internal link.
     */
    public function testFormatLinkMinimalNote()
    {
        $indexUrl = 'https://domain.tld/sub/';
        $data = [
            'id' => 12,
            'url' => '?abc',
            'shorturl' => 'abc',
            'title' => 'Note',
            'description' => '',
            'tags' => '',
            'private' => '',
            'created' => \DateTime::createFromFormat('Ymd_His', '20170107_160102'),
        ];
        $bookmark = new Bookmark();
        $bookmark->fromArray($data);

        $expected = [
            'id' => 12,
            'url' => 'https://domain.tld/sub/?abc',
            'shorturl' => 'abc',
            'title' => 'Note',
            'description' => '',
            'tags' => [],
            'private' => false,
            'created' => '2017-01-07T16:01:02+00:00',
            'updated' => '',
        ];

        $this->assertEquals($expected, ApiUtils::formatLink($bookmark, $indexUrl));
    }

    /**
     * Test updateLink with valid data, and also unnecessary fields.
     */
    public function testUpdateLink()
    {
        $created = \DateTime::createFromFormat('Ymd_His', '20170107_160102');
        $data = [
            'id' => 12,
            'url' => '?abc',
            'shorturl' => 'abc',
            'title' => 'Note',
            'description' => '',
            'tags' => '',
            'private' => '',
            'created' => $created,
        ];
        $old = new Bookmark();
        $old->fromArray($data);

        $data = [
            'id' => 13,
            'shorturl' => 'nope',
            'url' => 'http://somewhere.else',
            'title' => 'Le Cid',
            'description' => 'Percé jusques au fond du cœur [...]',
            'tags' => 'corneille rodrigue',
            'private' => true,
            'created' => 'creation',
            'updated' => 'updation',
        ];
        $new = new Bookmark();
        $new->fromArray($data);

        $result = ApiUtils::updateLink($old, $new);
        $this->assertEquals(12, $result->getId());
        $this->assertEquals('http://somewhere.else', $result->getUrl());
        $this->assertEquals('abc', $result->getShortUrl());
        $this->assertEquals('Le Cid', $result->getTitle());
        $this->assertEquals('Percé jusques au fond du cœur [...]', $result->getDescription());
        $this->assertEquals('corneille rodrigue', $result->getTagsString());
        $this->assertEquals(true, $result->isPrivate());
        $this->assertEquals($created, $result->getCreated());
    }

    /**
     * Test updateLink with minimal data.
     */
    public function testUpdateLinkMinimal()
    {
        $created = \DateTime::createFromFormat('Ymd_His', '20170107_160102');
        $data = [
            'id' => 12,
            'url' => '?abc',
            'shorturl' => 'abc',
            'title' => 'Note',
            'description' => 'Interesting description!',
            'tags' => 'doggo',
            'private' => true,
            'created' => $created,
        ];
        $old = new Bookmark();
        $old->fromArray($data);

        $new = new Bookmark();

        $result = ApiUtils::updateLink($old, $new);
        $this->assertEquals(12, $result->getId());
        $this->assertEquals('', $result->getUrl());
        $this->assertEquals('abc', $result->getShortUrl());
        $this->assertEquals('', $result->getTitle());
        $this->assertEquals('', $result->getDescription());
        $this->assertEquals('', $result->getTagsString());
        $this->assertEquals(false, $result->isPrivate());
        $this->assertEquals($created, $result->getCreated());
    }
}
