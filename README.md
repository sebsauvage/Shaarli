> You want to share the links you discover ? Shaarli is a minimalist delicious
clone you can install on your own website. It is designed to be personal
(single-user), fast and handy.

This project is a fork of
[Shaarli](http://sebsauvage.net/wiki/doku.php?id=php:shaarli) by Sebsauvage.

The latest version can be found on my
[Github](https://github.com/abeaumet/shaarli). You can see a hosted version
[on my website](http://shaarli.beaumet.fr).

#Enhancements

* Support [Markdown](https://daringfireball.net/projects/markdown/syntax)
  in link description
* When adding or editing a link:
  * Add preview functionality
  * Rearrange post form buttons
* Rearrange menu
* Links are opened in a new tab/window

#Installation

1.  Type the following commands:

     ```
     git clone git://github.com/abeaumet/shaarli.git links
     cd links
     git submodule update --init
     cd ..
     ```

    Or just copy/paste the following code snippet (lazy way):

     `git clone git://github.com/abeaumet/shaarli.git links && cd links && git
     submodule update --init && cd ..`

2. Then move the `links` folder in a place recognized by your HTTP server.

3. You're done! Test it through your web browser.
