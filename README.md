This project is a fork of [Shaarli](http://sebsauvage.net/wiki/doku.php?id=php:shaarli) by Sebsauvage.

The lastest version is on [Github](https://github.com/abeaumet/shaarli). You can see the
result [on my website](http://shaarli.beaumet.fr).

#Features

* Support Markdown in links description
* Rearrange menu (only when not logged in)
  * Delete "Login" button (`/?do=login` do the job... We don't need a public
    link, it attracts curious people)
  * Delete "Picture wall" button
  * Delete "Daily" button
  * Reorder the remaining elements
* Add a margin above each link description
* Enlarge description height while adding/editing a link

Note: you can retrieve the original menu by simply restoring
`tpl/page.header.html` (backup file here: `tpl/page.header.html.bak`).

#Install

Type the following commands:

```
git clone git@github.com:abeaumet/shaarli.git
cd shaarli
git submodule update --init
cd ..
```

Or just copy/paste the following snippet (lazy way...):

`git clone git@github.com:abeaumet/shaarli.git && cd shaarli && git submodule
update --init && cd ..`

Move the shaarli folder in a place recognized by your HTTP server.

You're done! Open your web browser.

#To do

* Fix style in link description:
  * [H[2-6]](https://daringfireball.net/projects/markdown/syntax#header) ;
  * [Blockquote](https://daringfireball.net/projects/markdown/syntax#blockquote) ;
  * [Code block](https://daringfireball.net/projects/markdown/syntax#precode) (`inline code in back quotes works`).
