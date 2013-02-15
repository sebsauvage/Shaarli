This project is a fork of [Shaarli](http://sebsauvage.net/wiki/doku.php?id=php:shaarli) by Sebsauvage.

It is hosted on [Github](https://github.com/abeaumet/shaarli). You can see the
result [on my website](http://shaarli.beaumet.fr).

Features:
* Rearrange menu (only when not logged in):
  * Delete "Login" button (`/?do=login` do the job... don't need a public link) ;
  * Delete "Picture wall" button ;
  * Delete "Daily" button ;
  * Reorder the remaining elements.
* Add a margin above each link description.
* Add Markdown parsing for link description.
* Enlarge description height while adding/editing a link.

Note:
* You can retrieve the deleted buttons or get the old order back by simply
  restoring the original `tpl/page.header.html` file. You can found it in
  `tpl/page.header.html.bak`.
