# Troubleshooting

## Browser

### Redirection issues (HTTP Referer)

Depending on its configuration and installed plugins, the browser may remove or alter (spoof) HTTP referers, thus preventing Shaarli from properly redirecting between pages.

See:

- [HTTP referer](https://en.wikipedia.org/wiki/HTTP_referer) (Wikipedia)
- [Improve online privacy by controlling referrer information](http://www.ghacks.net/2015/01/22/improve-online-privacy-by-controlling-referrer-information/)
- [Better security, privacy and anonymity in Firefox](http://b.agilob.net/better-security-privacy-and-anonymity-in-firefox/)

### Firefox HTTP Referer options

HTTP settings are available by browsing `about:config`, here are the available settings and their values.

`network.http.sendRefererHeader` - determines when to send the Referer HTTP header

- `0`: Never send the referring URL
    - not recommended, may break some sites
- `1`: Send only on clicked links
- `2` (default): Send for links and images

`network.http.referer.XOriginPolicy` - Cross-domain origin policy

- `0` (default): Always send
- `1`: Send if base domains match
- `2`: Send if hosts match

`network.http.referer.spoofSource` - Referer spoofing (~faking)

- `false` (default): real referer
- `true`: spoof referer (use target URI as referer)
    - known to break some functionality in Shaarli

`network.http.referer.trimmingPolicy` - trim the URI not to send a full Referer

- `0`: (default): send full URI
- `1`: scheme+host+port+path
- `2`: scheme+host+port

### Firefox, localhost and redirections

`localhost` is not a proper Fully Qualified Domain Name (FQDN); if Firefox has
been set up to spoof referers, or only accept requests from the same base domain/host,
Shaarli redirections will not work properly.

To solve this, assign a local domain to your host, e.g.
```
127.0.0.1 localhost desktop localhost.lan
::1       localhost desktop localhost.lan
```

and browse Shaarli at http://localhost.lan/.

Related threads:
- [What is localhost.localdomain for?](https://bbs.archlinux.org/viewtopic.php?id=156064)
- [Stop returning to the first page after editing a bookmark from another page](https://github.com/shaarli/Shaarli/issues/311)

## Login

### I forgot my password!

Delete the file `data/config.json.php` and display the page again. You will be asked for a new login/password.

### I'm locked out - Login bruteforce protection

Login form is protected against brute force attacks: 4 failed logins will ban the IP address from login for 30 minutes. Banned IPs can still browse links.

To remove the current IP bans, delete the file `data/ipbans.php`

### List of all login attempts

The file `data/log.txt` shows all logins (successful or failed) and bans/lifted bans.
Search for `failed` in this file to look for unauthorized login attempts.

## Hosting problems

### Old PHP versions

On **free.fr**: free.fr now supports php 5.6.x([link](http://les.pages.perso.chez.free.fr/migrations/php5v6.io))
and so support now the tag autocompletion but you have to do the following.

At the root of your webspace create a `sessions` directory and a `.htaccess` file containing:

```xml
<IfDefine Free>
php56 1
</IfDefine>
```

- If you have an error such as: `Parse error: syntax error, unexpected '=', expecting '(' in /links/index.php on line xxx`, it means that your host is using php4, not php5. Shaarli requires php 5.1. Try changing the file extension to `.php5`
- On **1and1** : If you add the link from the page (and not from the bookmarklet), Shaarli will no be able to get the title of the page. You will have to enter it manually. (Because they have disabled the ability to download a file through HTTP).
- If you have the error `Warning: file_get_contents() [function.file-get-contents]: URL file-access is disabled in the server configuration in /…/index.php on line xxx`, it means that your host has disabled the ability to fetch a file by HTTP in the php config (Typically in 1and1 hosting). Bad host. Change host. Or comment the following lines:

```php
//list($status,$headers,$data) = getHTTP($url,4); // Short timeout to keep the application responsive.
// FIXME: Decode charset according to charset specified in either 1) HTTP response headers or 2) <head> in html
//if (strpos($status,'200 OK')) $title=html_extract_title($data);
```

- On hosts which forbid outgoing HTTP requests (such as free.fr), some thumbnails will not work.
- On **lost-oasis**, RSS doesn't work correctly, because of this message at the begining of the RSS/ATOM feed : `<? // tout ce qui est charge ici (generalement des includes et require) est charge en permanence. ?>`. To fix this, remove this message from `php-include/prepend.php`

### Dates are not properly formatted

Shaarli tries to sniff the language of the browser (using `HTTP_ACCEPT_LANGUAGE` headers)
and choose a date format accordingly. But Shaarli can only use the date formats
(and more generally speaking, the locales) provided by the webserver.
So even if you have a browser in French, you may end up with dates in US format
(it's the case on sebsauvage.net :-( )

### My session expires! I can't stay logged in

This can be caused by several things:

- Your php installation may not have a proper directory setup for session files. (eg. on Free.fr you need to create a  `session` directory on the root of your website.) You may need to create the session directory of set it up.
- Most hosts regularly clean the temporary and session directories. Your host may be cleaning those directories too aggressively (eg.OVH hosts), forcing an expire of the session. You may want to set the session directory in your web root. (eg. Create the `sessions` subdirectory and add `ini_set('session.save_path', $_SERVER['DOCUMENT_ROOT'].'/../sessions');`. Make sure this directory is not browsable !)
- If your IP address changes during surfing, Shaarli will force expire your session for security reasons (to prevent session cookie hijacking). This can happen when surfing from WiFi or 3G (you may have switched WiFi/3G access point), or in some corporate/university proxies which use load balancing (and may have proxies with several external IP addresses).
- Some browser addons may interfer with HTTP headers (ipfuck/ipflood/GreaseMonkey…). Try disabling those.
- You may be using OperaTurbo or OperaMini, which use their own proxies which may change from time to time.
- If you have another application on the same webserver where Shaarli is installed, these application may forcefully expire php sessions.

## Sessions do not seem to work correctly on your server

Follow the instructions in the error message. Make sure you are accessing shaarli via a direct IP address or a proper hostname. If you have **no dots** in the hostname (e.g. `localhost` or `http://my-webserver/shaarli/`), some browsers will not store cookies at all (this respects the [HTTP cookie specification](http://curl.haxx.se/rfc/cookie_spec.html)).
