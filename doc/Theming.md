#Theming
## Foreword
There are two ways of customizing how Shaarli looks:

1. by using a custom CSS to override Shaarli's CSS
2. by using a full theme that provides its own RainTPL templates, CSS and Javascript resources

## Custom CSS
Shaarli's appearance can be modified by adding CSS rules to:
- Shaarli < `v0.9.0`: `inc/user.css`
- Shaarli >= `v0.9.0`: `data/user.css`

This file allows overriding rules defined in the template CSS files (only add changed rules), or define a whole new theme.

**Note**: Do not edit `tpl/default/css/shaarli.css`! Your changes would be overridden when updating Shaarli.

See also [Download CSS styles from an OPML list](Download-CSS-styles-from-an-OPML-list.html)

## Themes
_WARNING - This feature is currently being worked on and will be improved in the next releases. Experimental._

Installation:
- find a theme you'd like to install
- copy or clone the theme folder under `tpl/<a_sweet_theme>`
- enable the theme:
    - Shaarli < `v0.9.0`: edit `data/config.json.php` and set the value of `raintpl_tpl` to the new theme name:
      `"raintpl_tpl": "tpl\/my-template\/"`
    - Shaarli >= `v0.9.0`: select the theme through the _Tools_ page

## Community CSS & themes
### Custom CSS
- [mrjovanovic/serious-theme-shaarli](https://github.com/mrjovanovic/serious-theme-shaarli) - A serious theme for Shaarli[](.html)
- [shaarli/shaarli-themes](https://github.com/shaarli/shaarli-themes)[](.html)

### Themes
- [AkibaTech/Shaarli Superhero Theme](https://github.com/AkibaTech/Shaarli---SuperHero-Theme) - A template/theme for Shaarli[](.html)
- [alexisju/albinomouse-template](https://github.com/alexisju/albinomouse-template) - A full template for Shaarli[](.html)
- [ArthurHoaro/shaarli-launch](https://github.com/ArthurHoaro/shaarli-launch) - Customizable Shaarli theme[](.html)
- [dhoko/ShaarliTemplate](https://github.com/dhoko/ShaarliTemplate) - A template/theme for Shaarli[](.html)
- [kalvn/shaarli-blocks](https://github.com/kalvn/shaarli-blocks) - A template/theme for Shaarli[](.html)
- [kalvn/Shaarli-Material](https://github.com/kalvn/Shaarli-Material) - A theme (template) based on Google's Material Design for Shaarli, the superfast delicious clone[](.html)
- [ManufacturaInd/shaarli-2004licious-theme](https://github.com/ManufacturaInd/shaarli-2004licious-theme) - A template/theme as a humble homage to the early looks of the del.icio.us site[](.html)

### Shaarli forks
- [misterair/Limonade](https://github.com/misterair/limonade) - A fork of (legacy) Shaarli with a new template[](.html)
- [vivienhaese/shaarlitheme](https://github.com/vivienhaese/shaarlitheme) - A Shaarli fork meant to be run in an openshift instance[](.html)

## Example installation: AlbinoMouse theme
With the following configuration:
- Apache 2 / PHP 5.6
- user sites are enabled, e.g. `/home/user/public_html/somedir` is served as `http://localhost/~user/somedir`
- `http` is the name of the Apache user

```bash
$ cd ~/public_html

# clone repositories
$ git clone https://github.com/shaarli/Shaarli.git shaarli
$ pushd shaarli/tpl
$ git clone https://github.com/alexisju/albinomouse-template.git
$ popd

# set access rights for Apache
$ chgrp -R http shaarli
$ chmod g+rwx shaarli shaarli/cache shaarli/data shaarli/pagecache shaarli/tmp
```

Get config written:
- go to the freshly installed site
- fill the install form
- log in to Shaarli

Edit Shaarli's [configuration|Shaarli configuration](configuration|Shaarli-configuration.html):
```bash
# the file should be owned by Apache, thus not writeable => sudo
$ sudo sed -i s=tpl=tpl/albinomouse-template=g shaarli/data/config.php
```
