## Usage and Prerequisites

See the [REST API documentation](http://shaarli.github.io/api-documentation/)
for a list of available endpoints and parameters.

Please ensure that your server meets the
[requirements](Server-configuration#prerequisites) and is properly
[configured](Server-configuration):

- URL rewriting is enabled (see specific Apache and Nginx sections)
- the server's timezone is properly defined
- the server's clock is synchronized with
  [NTP](https://en.wikipedia.org/wiki/Network_Time_Protocol)

The host where the API client is invoked should also be synchronized with NTP,
see [token expiration](#payload).

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

**Token expiration**

To avoid infinite token validity, JWT tokens must include their creation date
in UNIX timestamp format (timezone independent - UTC) under the key `iat` (issued at).
This token will be valid during **9 minutes**.

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


## Clients and examples
### Android, Java, Kotlin

- [Android client example with Kotlin](https://gitlab.com/snippets/1665808)
  by [Braincoke](https://github.com/Braincoke)

### Javascript, NodeJS

- [shaarli-client](https://www.npmjs.com/package/shaarli-client)
  ([source code](https://github.com/laBecasse/shaarli-client))
  by [laBecasse](https://github.com/laBecasse)

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


### Python

See the reference API client:

- [Documentation](http://python-shaarli-client.readthedocs.io/en/latest/) on ReadTheDocs
- [python-shaarli-client](https://github.com/shaarli/python-shaarli-client) on Github

## Troubleshooting

### Debug mode

> This should never be used in a production environment.

For security reasons, authentication issues will always return an `HTTP 401` error code without any detail.

It is possible to enable the debug mode in `config.json.php` 
to get the actual error message in the HTTP response body with:

```json
{
  "dev": {
    "debug": true
  }
}
```
