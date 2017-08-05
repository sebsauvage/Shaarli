## Usage

See the [REST API documentation](http://shaarli.github.io/api-documentation/).

## Authentication

All requests to Shaarli's API must include a JWT token to verify their authenticity.

This token has to be included as an HTTP header called `Authentication: Bearer <jwt token>`.

JWT resources :

- [jwt.io](https://jwt.io) (including a list of client per language).
- RFC : https://tools.ietf.org/html/rfc7519
- https://float-middle.com/json-web-tokens-jwt-vs-sessions/
- HackerNews thread: https://news.ycombinator.com/item?id=11929267


### Shaarli JWT Token

JWT tokens are composed by three parts, separated by a dot `.` and encoded in base64:

```
[header].[payload].[signature]
```

#### Header

Shaarli only allow one hash algorithm, so the header will always be the same:

```json
{
    "typ": "JWT",
    "alg": "HS512"
}
```

Encoded in base64, it gives:

```
ewogICAgICAgICJ0eXAiOiAiSldUIiwKICAgICAgICAiYWxnIjogIkhTNTEyIgogICAgfQ==
```

#### Payload

**Validity duration**

To avoid infinite token validity, JWT tokens must include their creation date in UNIX timestamp format (timezone independant - UTC) under the key `iat` (issued at). This token will be accepted during 9 minutes.

```json
{
    "iat": 1468663519
}
```

See [RFC reference](https://tools.ietf.org/html/rfc7519#section-4.1.6).


#### Signature

The signature authenticate the token validity. It contains the base64 of the header and the body, separated by a dot `.`, hashed in SHA512 with the API secret available in Shaarli administration page.

Signature example with PHP:

```php
$content = base64_encode($header) . '.' . base64_encode($payload);
$signature = hash_hmac('sha512', $content, $secret);
```


### Complete example

#### PHP

```php
function generateToken($secret) {
    $header = base64_encode('{
        "typ": "JWT",
        "alg": "HS512"
    }');
    $payload = base64_encode('{
        "iat": '. time() .'
    }');
    $signature = hash_hmac('sha512', $header .'.'. $payload , $secret);
    return $header .'.'. $payload .'.'. $signature;
}

$secret = 'mysecret';
$token = generateToken($secret);
echo $token;
```

> `ewogICAgICAgICJ0eXAiOiAiSldUIiwKICAgICAgICAiYWxnIjogIkhTNTEyIgogICAgfQ==.ewogICAgICAgICJpYXQiOiAxNDY4NjY3MDQ3CiAgICB9.1d2c54fa947daf594fdbf7591796195652c8bc63bffad7f6a6db2a41c313f495a542cbfb595acade79e83f3810d709b4251d7b940bbc10b531a6e6134af63a68`

```php
$options = [
    'http' => [
        'method' => 'GET',
        'jwt' => $token,
    ],
];
$context = stream_context_create($options);
file_get_contents($apiEndpoint, false, $context);
```
