This project is a fork of
[Shaarli](http://sebsauvage.net/wiki/doku.php?id=php:shaarli) by Sebsauvage.

The lastest version can be found on
[Github](https://github.com/abeaumet/shaarli). You can see a hosted version
[on my website](http://shaarli.beaumet.fr).

#Features

* Links description
  * Support Markdown (complete syntax
  [described here](https://daringfireball.net/projects/markdown/syntax))
  * Support [Github Gist](https://gist.github.com/)
  * Add a margin above and below each description
* Enlarge description height while adding/editing a link
* Menu (only when not logged in)
  * Delete "Login" button (`/?do=login` do the job... We don't need a public
    link, it attracts curious people. Moreover, Shaarli asks to log in when
    adding a link.)
  * Delete "Picture wall" button
  * Delete "Daily" button
  * Reorder the remaining elements

Note: you can retrieve the original menu by simply restoring
`tpl/page.header.html` (the backup file is here: `tpl/page.header.html.bak`).

#Install

Type the following commands:

```
git clone git://github.com/abeaumet/shaarli.git links
cd links
git submodule update --init
cd ..
```

Or just copy/paste the following snippet:

`git clone git://github.com/abeaumet/shaarli.git links && cd links && git
submodule update --init && cd ..`

Then move the `links` folder in a place recognized by your HTTP server.

You're done! Test it through your web browser.
