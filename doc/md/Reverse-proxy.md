# Reverse proxy

If Shaarli is hosted on a server behind a [reverse proxy](https://en.wikipedia.org/wiki/Reverse_proxy) (i.e. there is a proxy server between clients and the web server hosting Shaarli), configure it accordingly. See [Reverse proxy](Reverse-proxy.md) configuration. In this example:

- The Shaarli application server exposes port `10080` to the proxy (for example docker container started with `--publish 127.0.0.1:10080:80`).
- The Shaarli application server runs at `127.0.0.1` (container). Replace with the server's IP address if running on a different machine.
- Shaarli's Fully Qualified Domain Name (FQDN) is `shaarli.mydomain.org`.
- No HTTPS is setup on the application server, SSL termination is done at the reverse proxy.

In your [Shaarli configuration](Shaarli-configuration) `data/config.json.php`, add the public IP of your proxy under `security.trusted_proxies`.

See also [proxy-related](https://github.com/shaarli/Shaarli/issues?utf8=%E2%9C%93&q=label%3Aproxy+) issues.


## Apache

```apache
<VirtualHost *:80>
    ServerName shaarli.mydomain.org
    # Redirect HTTP to HTTPS
    Redirect permanent / https://shaarli.mydomain.org
</VirtualHost>

<VirtualHost *:443>
    ServerName shaarli.mydomain.org

    SSLEngine on
    SSLCertificateFile    /path/to/certificate
    SSLCertificateKeyFile /path/to/private/key

    LogLevel warn
    ErrorLog  /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined

    # let the proxied shaarli server/container know HTTPS URLs should be served
    RequestHeader set X-Forwarded-Proto "https"

    # send the original SERVER_NAME to the proxied host
    ProxyPreserveHost On
    
    # pass requests to the proxied host
    # sets X-Forwarded-For, X-Forwarded-Host and X-Forwarded-Server headers
    ProxyPass        / http://127.0.0.1:10080/
    ProxyPassReverse / http://127.0.0.1:10080/
</VirtualHost>
```


## HAProxy


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


```nginx
http {
    [...]

    index index.html index.php;

    root        /home/john/web;
    access_log  /var/log/nginx/access.log combined;
    error_log   /var/log/nginx/error.log;

    server {
        listen       80;
        server_name  shaarli.mydomain.org;
        # redirect HTTP to HTTPS
        return       301 https://shaarli.mydomain.org$request_uri;
    }

    server {
        listen       443 ssl http2;
        server_name  shaarli.mydomain.org;

        ssl_certificate       /path/to/certificate
        ssl_certificate_key   /path/to/private/key

        location / {
            proxy_set_header  X-Real-IP         $remote_addr;
            proxy_set_header  X-Forwarded-For   $proxy_add_x_forwarded_for;
            proxy_set_header  X-Forwarded-Proto $scheme;
            proxy_set_header  X-Forwarded-Host  $host;

            # pass requests to the proxied host
            proxy_pass             http://localhost:10080/;
            proxy_set_header Host  $host;
            proxy_connect_timeout  30s;
            proxy_read_timeout     120s;
        }
    }
}
```

