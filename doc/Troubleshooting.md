#Troubleshooting
## Login
### I forgot my password!

Delete the file `data/config.php` and display the page again. You will be asked for a new login/password.

### I'm locked out - Login bruteforce protection
Login form is protected against brute force attacks: 4 failed logins will ban the IP address from login for 30 minutes. Banned IPs can still browse links.

To remove the current IP bans, delete the file `data/ipbans.php`

### List of all login attempts

The file `data/log.txt` shows all logins (successful or failed) and bans/lifted bans.
Search for `failed` in this file to look for unauthorized login attempts.

## Hosting problems
 * On **free.fr** : Please note that free uses php 5.1 and thus you will not have autocomplete in tag editing.  Don't forget to create a `sessions` directory at the root of your webspace. Change the file extension to `.php5` or create a `.htaccess` file in the directory where Shaarli is located containing:

```ini
php 1
SetEnv PHP_VER 5
```

 * If you have an error such as: `Parse error: syntax error, unexpected '=', expecting '(' in /links/index.php on line xxx`, it means that your host is using php4, not php5. Shaarli requires php 5.1. Try changing the file extension to `.php5`
 * On **1and1** : If you add the link from the page (and not from the bookmarklet), Shaarli will no be able to get the title of the page. You will have to enter it manually. (Because they have disabled the ability to download a file through HTTP).
 * If you have the error `Warning: file_get_contents() [function.file-get-contents]: URL file-access is disabled in the server configuration in /…/index.php on line xxx`, it means that your host has disabled the ability to fetch a file by HTTP in the php config (Typically in 1and1 hosting). Bad host. Change host. Or comment the following lines:[](.html)

```php
//list($status,$headers,$data) = getHTTP($url,4); // Short timeout to keep the application responsive.
// FIXME: Decode charset according to charset specified in either 1) HTTP response headers or 2) <head> in html 
//if (strpos($status,'200 OK')) $title=html_extract_title($data);
```

 * On hosts which forbid outgoing HTTP requests (such as free.fr), some thumbnails will not work.
 * On **lost-oasis**, RSS doesn't work correctly, because of this message at the begining of the RSS/ATOM feed : `<? // tout ce qui est charge ici (generalement des includes et require) est charge en permanence. ?>`. To fix this, remove this message from `php-include/prepend.php`

### Dates are not properly formatted
Shaarli tries to sniff the language of the browser (using HTTP_ACCEPT_LANGUAGE headers) and choose a date format accordingly. But Shaarli can only use the date formats (and more generaly speaking, the locales) provided by the webserver. So even if you have a browser in French, you may end up with dates in US format (it's the case on sebsauvage.net :-( )

### Problems on CentOS servers
On **CentOS**/RedHat derivatives, you may need to install the `php-mbstring` package.


### My session expires! I can't stay logged in
This can be caused by several things:

* Your php installation may not have a proper directory setup for session files. (eg. on Free.fr you need to create a  `session` directory on the root of your website.) You may need to create the session directory of set it up.
* Most hosts regularly clean the temporary and session directories. Your host may be cleaning those directories too aggressively (eg.OVH hosts), forcing an expire of the session. You may want to set the session directory in your web root. (eg. Create the `sessions` subdirectory and add `ini_set('session.save_path', $_SERVER['DOCUMENT_ROOT'].'/../sessions');`. Make sure this directory is not browsable !)[](.html)
* If your IP address changes during surfing, Shaarli will force expire your session for security reasons (to prevent session cookie hijacking). This can happen when surfing from WiFi or 3G (you may have switched WiFi/3G access point), or in some corporate/university proxies which use load balancing (and may have proxies with several external IP addresses).
* Some browser addons may interfer with HTTP headers (ipfuck/ipflood/GreaseMonkey…). Try disabling those.
* You may be using OperaTurbo or OperaMini, which use their own proxies which may change from time to time.
* If you have another application on the same webserver where Shaarli is installed, these application may forcefully expire php sessions.

## Sessions do not seem to work correctly on your server
Follow the instructions in the error message. Make sure you are accessing shaarli via a direct IP address or a proper hostname. If you have **no dots** in the hostname (e.g. `localhost` or `http://my-webserver/shaarli/`), some browsers will not store cookies at all (this respects the [HTTP cookie specification](http://curl.haxx.se/rfc/cookie_spec.html)).[](.html)

### pubsubhubbub support

Download [publisher.php](https://pubsubhubbub.googlecode.com/git/publisher_clients/php/library/publisher.php) at the root of your Shaarli installation and set `$GLOBALS['config'['PUBSUBHUB_URL']` in your `config.php`]('PUBSUBHUB_URL']`-in-your-`config.php`.html)
