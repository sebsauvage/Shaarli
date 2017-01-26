*Example virtual host configurations for popular web servers*

- [Apache](#apache)
- [Nginx](#nginx)

## Prerequisites
### Shaarli
* Shaarli is installed in a directory readable/writeable by the user
* the correct read/write permissions have been granted to the web server _user and/or group_
* for HTTPS / SSL:
 * a key pair (public, private) and a certificate have been generated
 * the appropriate server SSL extension is installed and active

### HTTPS, TLS and self-signed certificates
Related guides:
* [How to Create Self-Signed SSL Certificates with OpenSSL](http://www.xenocafe.com/tutorials/linux/centos/openssl/self_signed_certificates/index.php)
* [How do I create my own Certificate Authority?](https://workaround.org/certificate-authority)
* Generate a self-signed certificate (will trigger browser warnings) with apache2: `make-ssl-cert generate-default-snakeoil --force-overwrite` will create `/etc/ssl/certs/ssl-cert-snakeoil.pem` and `/etc/ssl/private/ssl-cert-snakeoil.key`

### Proxies
If Shaarli is served behind a proxy (i.e. there is a proxy server between clients and the web server hosting Shaarli), please refer to the proxy server documentation for proper configuration. In particular, you have to ensure that the following server variables are properly set:
- `X-Forwarded-Proto`;
- `X-Forwarded-Host`;
- `X-Forwarded-For`.

See also [proxy-related](https://github.com/shaarli/Shaarli/issues?utf8=%E2%9C%93&q=label%3Aproxy+) issues.

## Apache
### Minimal
```apache
<VirtualHost *:80>
    ServerName   shaarli.my-domain.org
    DocumentRoot /absolute/path/to/shaarli/
</VirtualHost>
```
### Debug - Log all the things!
This configuration will log both Apache and PHP errors, which may prove useful to identify server configuration errors.

See:
* [Apache/PHP - error log per VirtualHost](http://stackoverflow.com/q/176) (StackOverflow)
* [PHP: php_value vs php_admin_value and the use of php_flag explained](https://ma.ttias.be/php-php_value-vs-php_admin_value-and-the-use-of-php_flag-explained/)

```apache
<VirtualHost *:80>
    ServerName   shaarli.my-domain.org
    DocumentRoot /absolute/path/to/shaarli/

    LogLevel  warn
    ErrorLog  /var/log/apache2/shaarli-error.log
    CustomLog /var/log/apache2/shaarli-access.log combined

    php_flag  log_errors on
    php_flag  display_errors on
    php_value error_reporting 2147483647
    php_value error_log /var/log/apache2/shaarli-php-error.log
</VirtualHost>
```

### Standard - Keep access and error logs
```apache
<VirtualHost *:80>
    ServerName   shaarli.my-domain.org
    DocumentRoot /absolute/path/to/shaarli/

    LogLevel  warn
    ErrorLog  /var/log/apache2/shaarli-error.log
    CustomLog /var/log/apache2/shaarli-access.log combined
</VirtualHost>
```

### Paranoid - Redirect HTTP (:80) to HTTPS (:443)
See [Server-side TLS](https://wiki.mozilla.org/Security/Server_Side_TLS#Apache) (Mozilla).

```apache
<VirtualHost *:443>
    ServerName   shaarli.my-domain.org
    DocumentRoot /absolute/path/to/shaarli/

    SSLEngine             on
    SSLCertificateFile    /absolute/path/to/the/website/certificate.pem
    SSLCertificateKeyFile /absolute/path/to/the/website/key.key

    <Directory /absolute/path/to/shaarli/>
        AllowOverride All
        Options Indexes FollowSymLinks MultiViews
        Order allow,deny
        allow from all
    </Directory>

    LogLevel  warn
    ErrorLog  /var/log/apache2/shaarli-error.log
    CustomLog /var/log/apache2/shaarli-access.log combined
</VirtualHost>
<VirtualHost *:80>
    ServerName   shaarli.my-domain.org
    Redirect 301 / https://shaarli.my-domain.org

    LogLevel  warn
    ErrorLog  /var/log/apache2/shaarli-error.log
    CustomLog /var/log/apache2/shaarli-access.log combined
</VirtualHost>
```

### .htaccess

Shaarli use `.htaccess` Apache files to deny access to files that shouldn't be directly accessed (datastore, config, etc.). You need the directive `AllowOverride All` in your virtual host configuration for them to work.

**Warning**: If you use Apache 2.2 or lower, you need [mod_version](https://httpd.apache.org/docs/current/mod/mod_version.html) to be installed and enabled.
 
Apache module `mod_rewrite` **must** be enabled to use the REST API. URL rewriting rules for the Slim microframework are stated in the root `.htaccess` file.

## LightHttpd

## Nginx
### Foreword
Nginx does not natively interpret PHP scripts; to this effect, we will run a [FastCGI](https://en.wikipedia.org/wiki/FastCGI) service, to which Nginx's FastCGI module will proxy all requests to PHP resources.

Required packages:
- [nginx](http://nginx.org)
- [php-fpm](http://php-fpm.org) - PHP FastCGI Process Manager

Official documentation:
- [Beginner's guide](http://nginx.org/en/docs/beginners_guide.html)
- [ngx_http_fastcgi_module](http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html)
- [Pitfalls](http://wiki.nginx.org/Pitfalls)

Community resources:
- [Server-side TLS (Nginx)](https://wiki.mozilla.org/Security/Server_Side_TLS#Nginx) (Mozilla)
- [PHP configuration examples](http://kbeezie.com/nginx-configuration-examples/) (Karl Blessing)

### Common setup
Once Nginx and PHP-FPM are installed, we need to ensure:
- Nginx and PHP-FPM are running using the _same user and group_
- both these user and group have
    - `read` permissions for Shaarli resources
    - `execute` permissions for Shaarli directories _AND_ their parent directories

On a production server:
- `user:group` will likely be `http:http`, `www:www` or `www-data:www-data`
- files will be located under `/var/www`, `/var/http` or `/usr/share/nginx`

On a development server:
- files may be located in a user's home directory
- in this case, make sure both Nginx and PHP-FPM are running as the local user/group!

For all following configuration examples, this user/group pair will be used:
- `user:group = john:users`,

which corresponds to the following service configuration:

```ini
; /etc/php/php-fpm.conf
user = john
group = users

[...]
listen.owner = john
listen.group = users
```

```nginx
# /etc/nginx/nginx.conf
user john users;

http {
    [...]
}
```

### (Optional) Increase the maximum file upload size
Some bookmark dumps generated by web browsers can be _huge_ due to the presence of Base64-encoded images and favicons, as well as extra verbosity when nesting links in (sub-)folders.

To increase upload size, you will need to modify both nginx and PHP configuration:

```nginx
# /etc/nginx/nginx.conf

http {
    [...]

    client_max_body_size 10m;

    [...]
}
```

```ini
# /etc/php5/fpm/php.ini

[...]
post_max_size = 10M
[...]
upload_max_filesize = 10M
```

### Minimal
_WARNING: Use for development only!_ 

```nginx
user john users;
worker_processes  1;
events {
    worker_connections  1024;
}

http {
    include            mime.types;
    default_type       application/octet-stream;
    keepalive_timeout  20;

    index index.html index.php;

    server {
        listen       80;
        server_name  localhost;
        root         /home/john/web;

        access_log  /var/log/nginx/access.log;
        error_log   /var/log/nginx/error.log;

        location /shaarli/ {
            try_files $uri /shaarli/index.php$is_args$args;
            access_log  /var/log/nginx/shaarli.access.log;
            error_log   /var/log/nginx/shaarli.error.log;
        }

        location ~ (index)\.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass   unix:/var/run/php-fpm/php-fpm.sock;
            fastcgi_index  index.php;
            include        fastcgi.conf;
        }
    }
}
```

### Modular
The previous setup is sufficient for development purposes, but has several major caveats:
- every content that does not match the PHP rule will be sent to client browsers:
    - dotfiles - in our case, `.htaccess`
    - temporary files, e.g. Vim or Emacs files: `index.php~`
- asset / static resource caching is not optimized
- if serving several PHP sites, there will be a lot of duplication: `location /shaarli/`, `location /mysite/`, etc.

To solve this, we will split Nginx configuration in several parts, that will be included when needed:

```nginx
# /etc/nginx/deny.conf
location ~ /\. {
    # deny access to dotfiles
    access_log off;
    log_not_found off;
    deny all;
}

location ~ ~$ {
    # deny access to temp editor files, e.g. "script.php~"
    access_log off;
    log_not_found off;
    deny all;
}
```

```nginx
# /etc/nginx/php.conf
location ~ (index)\.php$ {
    # Slim - split URL path into (script_filename, path_info)
    try_files $uri =404;
    fastcgi_split_path_info ^(.+\.php)(/.+)$;

    # filter and proxy PHP requests to PHP-FPM
    fastcgi_pass   unix:/var/run/php-fpm/php-fpm.sock;
    fastcgi_index  index.php;
    include        fastcgi.conf;
}

location ~ \.php$ {
    # deny access to all other PHP scripts
    deny all;
}
```

```nginx
# /etc/nginx/static_assets.conf
location ~* \.(?:ico|css|js|gif|jpe?g|png)$ {
    expires    max;
    add_header Pragma public;
    add_header Cache-Control "public, must-revalidate, proxy-revalidate";
}
```

```nginx
# /etc/nginx/nginx.conf
[...]

http {
    [...]

    root        /home/john/web;
    access_log  /var/log/nginx/access.log;
    error_log   /var/log/nginx/error.log;

    server {
        # virtual host for a first domain
        listen       80;
        server_name  my.first.domain.org;

        location /shaarli/ {
            # Slim - rewrite URLs
            try_files $uri /shaarli/index.php$is_args$args;

            access_log  /var/log/nginx/shaarli.access.log;
            error_log   /var/log/nginx/shaarli.error.log;
        }

        location = /shaarli/favicon.ico {
            # serve the Shaarli favicon from its custom location
            alias /var/www/shaarli/images/favicon.ico;
        }

        include deny.conf;
        include static_assets.conf;
        include php.conf;
    }

    server {
        # virtual host for a second domain
        listen       80;
        server_name  second.domain.com;

        location /minigal/ {
            access_log  /var/log/nginx/minigal.access.log;
            error_log   /var/log/nginx/minigal.error.log;
        }

        include deny.conf;
        include static_assets.conf;
        include php.conf;
    }
}
```

### Redirect HTTP to HTTPS
Assuming you have generated a (self-signed) key and certificate, and they are located under `/home/john/ssl/localhost.{key,crt}`, it is pretty straightforward to set an HTTP (:80) to HTTPS (:443) redirection to force SSL/TLS usage.

```nginx
# /etc/nginx/nginx.conf
[...]

http {
    [...]

    index index.html index.php;

    root        /home/john/web;
    access_log  /var/log/nginx/access.log;
    error_log   /var/log/nginx/error.log;

    server {
        listen       80;
        server_name  localhost;

        return 301 https://localhost$request_uri;
    }

    server {
        listen       443 ssl;
        server_name  localhost;

        ssl_certificate      /home/john/ssl/localhost.crt;
        ssl_certificate_key  /home/john/ssl/localhost.key;

        location /shaarli/ {
            # Slim - rewrite URLs
            try_files $uri /index.php$is_args$args;

            access_log  /var/log/nginx/shaarli.access.log;
            error_log   /var/log/nginx/shaarli.error.log;
        }

        location = /shaarli/favicon.ico {
            # serve the Shaarli favicon from its custom location
            alias /var/www/shaarli/images/favicon.ico;
        }

        include deny.conf;
        include static_assets.conf;
        include php.conf;
    }
}
```
