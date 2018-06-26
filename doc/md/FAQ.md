### Why did you create Shaarli ?

I was a StumbleUpon user. Then I got fed up with they big toolbar. I switched to delicious, which was lighter, faster and more beautiful. Until Yahoo bought it. Then the export API broke all the time, delicious became slow and was ditched by Yahoo. I switched to Diigo, which is not bad, but does too much. And Diigo is sslllooooowww and their Firefox extension a bit buggy. And… oh… **their Firefox addon sends to Diigo every single URL you visit** (Don't believe me ? Use [Tamper Data](https://addons.mozilla.org/en-US/firefox/addon/tamper-data/) and open any page).

Enough is enough. Saving simple links should not be a complicated heavy thing. I ditched them all and wrote my own: Shaarli. It's simple, but it does the job and does it well. And my data is not hosted on a foreign server, but on my server.

### Why use Shaarli and not Delicious/Diigo ?

With Shaarli:

- The data is yours: It's hosted on your server.
- Never fear of having your data locked-in.
- Never fear to have your data sold to third party.
- Your private links are not hosted on a third party server.
- You are not tracked by browser addons (like Diigo does)
- You can change the look and feel of the pages if you want.
- You can change the behaviour of the program.
- It's magnitude faster than most bookmarking services.

### What does Shaarli mean?

Shaarli stands for _shaaring_ your _links_.

### My Shaarli is broken!
First of all, ensure that both the [web server](Server-configuration) and
[Shaarli](Shaarli-configuration) are correctly configured, and that your
installation is [supported](Server-configuration).

If everything looks right but the issue(s) remain(s), please:

- take a look at the [troubleshooting](Troubleshooting) section
- come [chat with us](https://gitter.im/shaarli/Shaarli) on Gitter, we'll be happy to help ;-)
- browse active [issues](https://github.com/shaarli/Shaarli/issues) and [Pull Requests](https://github.com/shaarli/Shaarli/pulls)
    - if you find one that is related to the issue, feel free to comment and provide additional details (host/Shaarli setup)
    - else, [open a new issue](https://github.com/shaarli/Shaarli/issues/new), and provide information about the problem:
        - _what happens?_ - display glitches, invalid data, security flaws...
        - _what is your configuration?_  - OS, server version, activated extensions, web browser...
        - _is it reproducible?_

### Why not use a real database? Files are slow!

Does browsing [this page](http://sebsauvage.net/links/) feel slow? Try browsing older pages, too.

It's not slow at all, is it? And don't forget the database contains more than 16000 links, and it's on a shared host, with 32000 visitors/day for my website alone. And it's still damn fast. Why?

The data file is only 3.7 Mb. It's read 99% of the time, and is probably already in the operation system disk cache. So generating a page involves no I/O at all most of the time.
