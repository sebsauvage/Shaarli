#Backup, restore, import and export
## Backup and restore the datastore file

Backup the file `data/datastore.php` (by FTP or SSH). Restore by putting the file back in place.

Example command:
```bash
rsync -avzP my.server.com:/var/www/shaarli/data/datastore.php datastore-$(date +%Y-%m-%d_%H%M).php
```

## Export links as...
To export links as an HTML file, under _Tools > Export_, choose:
- _Export all_ to export both public and private links
- _Export public_ to export public links only
- _Export private_ to export private links only

Restore by using the `Import` feature.
* This can be done using the [shaarchiver](https://github.com/nodiscc/shaarchiver) tool.[](.html)

Example command: 
```bash
./export-bookmarks.py --url=https://my.server.com/shaarli --username=myusername --password=mysupersecretpassword --download-dir=./ --type=all
```

## Import links from...
### Diigo

If you export your bookmark from Diigo, make sure you use the Delicious export, not the Netscape export. (Their Netscape export is broken, and they don't seem to be interested in fixing it.)

### Mister Wong
See [this issue](https://github.com/sebsauvage/Shaarli/issues/146) for import tweaks.[](.html)

### SemanticScuttle

To correctly import the tags from a [SemanticScuttle](http://semanticscuttle.sourceforge.net/) HTML export, edit the HTML file before importing and replace all occurences of `tags=` (lowercase) to `TAGS=` (uppercase).[](.html)
