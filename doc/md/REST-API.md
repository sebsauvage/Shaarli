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


### Complete examples

### PHP

This example uses the [PHP cURL](http://php.net/manual/en/book.curl.php) library.

```php
<?php
$baseUrl = 'https://shaarli.mydomain.net';
$secret = 'thats_my_api_secret';

function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function generateToken($secret) {
    $header = base64url_encode('{
        "typ": "JWT",
        "alg": "HS512"
    }');
    $payload = base64url_encode('{
        "iat": '. time() .'
    }');
    $signature = base64url_encode(hash_hmac('sha512', $header .'.'. $payload , $secret, true));
    return $header . '.' . $payload . '.' . $signature;
}


function getInfo($baseUrl, $secret) {
    $token = generateToken($secret);
    $endpoint = rtrim($baseUrl, '/') . '/api/v1/info';

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Authorization: Bearer ' . $token,
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

var_dump(getInfo($baseUrl, $secret));
```
