## Foreword

This guide assumes that:

- Shaarli runs in a Docker container
- The host's `10080` port is mapped to the container's `80` port
- Shaarli's Fully Qualified Domain Name (FQDN) is `shaarli.domain.tld`
- HTTP traffic is redirected to HTTPS

## Apache

- [Apache 2.4 documentation](https://httpd.apache.org/docs/2.4/)
    - [mod_proxy](https://httpd.apache.org/docs/2.4/mod/mod_proxy.html)
    - [Reverse Proxy Request Headers](https://httpd.apache.org/docs/2.4/mod/mod_proxy.html#x-headers)

The following HTTP headers are set when the `ProxyPass` directive is set:

- `X-Forwarded-For`
- `X-Forwarded-Host`
- `X-Forwarded-Server`

The original `SERVER_NAME` can be sent to the proxied host by setting the [`ProxyPreserveHost`](https://httpd.apache.org/docs/2.4/mod/mod_proxy.html#ProxyPreserveHost) directive to `On`.

```apache
<VirtualHost *:80>
    ServerName shaarli.domain.tld
    Redirect permanent / https://shaarli.domain.tld
</VirtualHost>

<VirtualHost *:443>
    ServerName shaarli.domain.tld

    SSLEngine on
    SSLCertificateFile    /path/to/cert
    SSLCertificateKeyFile /path/to/certkey

    LogLevel warn
    ErrorLog  /var/log/apache2/shaarli-error.log
    CustomLog /var/log/apache2/shaarli-access.log combined

    RequestHeader set X-Forwarded-Proto "https"
    ProxyPreserveHost On
    
    ProxyPass        / http://127.0.0.1:10080/
    ProxyPassReverse / http://127.0.0.1:10080/
</VirtualHost>
```


## HAProxy

- [HAProxy documentation](https://cbonte.github.io/haproxy-dconv/)

```conf
global
    [...]

defaults
    [...]

frontend http-in
    bind :80
	redirect scheme https code 301 if !{ ssl_fc }

	bind :443 ssl crt /path/to/cert.pem

	default_backend shaarli


backend shaarli
    mode http
    option http-server-close
    option forwardfor
    reqadd X-Forwarded-Proto: https

    server shaarli1 127.0.0.1:10080
```


## Nginx

- [Nginx documentation](https://nginx.org/en/docs/)

```nginx
http {
    [...]

    index index.html index.php;

    root        /home/john/web;
    access_log  /var/log/nginx/access.log;
    error_log   /var/log/nginx/error.log;

	server {
		listen       80;
		server_name  shaarli.domain.tld;
		return       301 https://shaarli.domain.tld$request_uri;
	}

	server {
		listen       443 ssl http2;
		server_name  shaarli.domain.tld;

        ssl_certificate       /path/to/cert
        ssl_certificate_key   /path/to/certkey

		location / {
			proxy_set_header  X-Real-IP         $remote_addr;
			proxy_set_header  X-Forwarded-For   $proxy_add_x_forwarded_for;
			proxy_set_header  X-Forwarded-Proto $scheme;
			proxy_set_header  X-Forwarded-Host  $host;

			proxy_pass             http://localhost:10080/;
			proxy_set_header Host  $host;
			proxy_connect_timeout  30s;
			proxy_read_timeout     120s;

			access_log      /var/log/nginx/shaarli.access.log;
			error_log       /var/log/nginx/shaarli.error.log;
		}
	}
}
```
