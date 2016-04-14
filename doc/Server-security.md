#Server security
## php.ini
PHP settings are defined in:
- a main configuration file, usually found under `/etc/php5/php.ini`; some distributions provide different configuration environments, e.g.
    - `/etc/php5/php.ini` - used when running console scripts
    - `/etc/php5/apache2/php.ini` - used when a client requests PHP resources from Apache
    - `/etc/php5/php-fpm.conf` - used when PHP requests are proxied to PHP-FPM
- additional configuration files/entries, depending on the installed/enabled extensions:
    - `/etc/php/conf.d/xdebug.ini`

### Locate .ini files
#### Console environment
```bash
$ php --ini
Configuration File (php.ini) Path: /etc/php
Loaded Configuration File:         /etc/php/php.ini
Scan for additional .ini files in: /etc/php/conf.d
Additional .ini files parsed:      /etc/php/conf.d/xdebug.ini
```

#### Server environment
- create a `phpinfo.php` script located in a path supported by the web server, e.g.
    - Apache (with user dirs enabled): `/home/myself/public_html/phpinfo.php`
    - `/var/www/test/phpinfo.php`
- make sure the script is readable by the web server user/group (usually, `www`, `www-data` or `httpd`)
- access the script from a web browser
- look at the _Loaded Configuration File_ and _Scan this dir for additional .ini files_ entries
```php
<?php phpinfo(); ?>
```

## fail2ban
`fail2ban` is an intrusion prevention framework that reads server (Apache, SSH, etc.) and uses `iptables` profiles to block brute-force attempts:
- [Official website](http://www.fail2ban.org/wiki/index.php/Main_Page)[](.html)
- [Source code](https://github.com/fail2ban/fail2ban)[](.html)

### Read Shaarli logs to ban IPs
Example configuration:
- allow 3 login attempts per IP address
- after 3 failures, permanently ban the corresponding IP adddress

`/etc/fail2ban/jail.local`
```ini
[shaarli-auth][](.html)
enabled  = true
port     = https,http
filter   = shaarli-auth
logpath  = /var/www/path/to/shaarli/data/log.txt
maxretry = 3
bantime = -1
```

`/etc/fail2ban/filter.d/shaarli-auth.conf`
```ini
[INCLUDES][](.html)
before = common.conf
[Definition][](.html)
failregex = \s-\s<HOST>\s-\sLogin failed for user.*$
ignoreregex = 
```
