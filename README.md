This project is a fork of
[Shaarli](http://sebsauvage.net/wiki/doku.php?id=php:shaarli) by Sebsauvage:

> You want to share the links you discover ? Shaarli is a minimalist delicious
clone you can install on your own website. It is designed to be personal
(single-user), fast and handy.

The latest version can be found on my
[Github](https://github.com/abeaumet/shaarli). You can see a hosted version
[on my website](http://shaarli.beaumet.fr).

#Fork enhancement

* Links description
  * Support 
    [Markdown](https://daringfireball.net/projects/markdown/syntax) in
    description
  * Support [Github Gist](https://gist.github.com/) in description
  * Add preview when adding/editing a link
* Rearrange buttons while adding/editing a link
* Offline menu
  * Remove "Login" button (Typing `/?do=login` do the job... We don't need a
    public link, it attracts curious people. Moreover, Shaarli asks to log in
    when adding a link.)
  * Remove "Picture wall" button
  * Remove "Daily" button
  * Reorder the remaining elements
* Links are opened in a new tab/window

Note: you can retrieve the original menu by simply erasing
`tpl/page.header.html` with `tpl/page.header.html.bak` (the functionalities
are still present in `index.php`).

#Install

1.  Type the following commands:

     ```
     git clone git://github.com/abeaumet/shaarli.git links
     cd links
     git submodule update --init
     cd ..
     ```

    Or just copy/paste the following snippet:

     `git clone git://github.com/abeaumet/shaarli.git links && cd links && git
     submodule update --init && cd ..`

2. Then move the `links` folder in a place recognized by your HTTP server.

3. You're done! Test it through your web browser.
