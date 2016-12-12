#Theming
## User CSS

- Shaarli's apparence can be modified by editing CSS rules in `inc/user.css`. This file allows to override rules defined in the main `inc/shaarli.css` (only add changed rules), or define a whole new theme.
- Do not edit `inc/shaarli.css`! Your changes would be overriden when updating Shaarli.
- Some themes are available at https://github.com/shaarli/shaarli-themes.

See also:
- [Download CSS styles from an OPML list](Download-CSS-styles-from-an-OPML-list.html)

## RainTPL template

_WARNING - This feature is currently being worked on and will be improved in the next releases. Experimental._

- Find the template you'd like to install (see the list of [available templates|Theming#community-themes--templates](available-templates|Theming#community-themes--templates.html))
- Find it's git clone URL or download the zip archive for the template.
- In your Shaarli `tpl/` directory, run `git clone https://url/of/my-template/` or unpack the zip archive.
    - There should now be a `my-template/` directory under the `tpl/` dir, containing directly all the template files.
- Edit `data/config.json.php` to have Shaarli use this template, in `"resource"` e.g.
```json
"raintpl_tpl": "tpl\/my-template\/",
```

## Community themes & templates
- [AkibaTech/Shaarli Superhero Theme](https://github.com/AkibaTech/Shaarli---SuperHero-Theme) - A template/theme for Shaarli[](.html)
- [alexisju/albinomouse-template](https://github.com/alexisju/albinomouse-template) - A full template for Shaarli[](.html)
- [ArthurHoaro/shaarli-launch](https://github.com/ArthurHoaro/shaarli-launch) - Customizable Shaarli theme.[](.html)
- [dhoko/ShaarliTemplate](https://github.com/dhoko/ShaarliTemplate) - A template/theme for Shaarli[](.html)
- [kalvn/shaarli-blocks](https://github.com/kalvn/shaarli-blocks) - A template/theme for Shaarli[](.html)
- [kalvn/Shaarli-Material](https://github.com/kalvn/Shaarli-Material) - A theme (template) based on Google's Material Design for Shaarli, the superfast delicious clone.[](.html)
- [ManufacturaInd/shaarli-2004licious-theme](https://github.com/ManufacturaInd/shaarli-2004licious-theme) - A template/theme as a humble homage to the early looks of the del.icio.us site.[](.html)
- [misterair/Limonade](https://github.com/misterair/limonade) - A fork of (legacy) Shaarli with a new template[](.html)
- [mrjovanovic/serious-theme-shaarli](https://github.com/mrjovanovic/serious-theme-shaarli) - A serious theme for SHaarli.[](.html)
- [vivienhaese/shaarlitheme](https://github.com/vivienhaese/shaarlitheme) - A Shaarli fork meant to be run in an openshift instance[](.html)

### Example installation: AlbinoMouse template
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
