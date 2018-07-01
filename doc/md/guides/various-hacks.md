### Decode datastore content

To display the array representing the data saved in `data/datastore.php`, use the following snippet:

```php
$data = "tZNdb9MwFIb... <Commented content inside datastore.php>";
$out = unserialize(gzinflate(base64_decode($data)));
echo "<pre>"; // Pretty printing is love, pretty printing is life
print_r($out);
echo "</pre>";
exit;
```
This will output the internal representation of the datastore, "unobfuscated" (if this can really be considered obfuscation).

Alternatively, you can transform to JSON format (and pretty-print if you have `jq` installed):
```
php -r 'print(json_encode(unserialize(gzinflate(base64_decode(preg_replace("!.*/\* (.+) \*/.*!", "$1", file_get_contents("data/datastore.php")))))));' | jq .
```

### Changing the timestamp for a shaare

- Look for `<input type="hidden" name="lf_linkdate" value="{$link.linkdate}">` in `tpl/editlink.tpl` (line 14)
- Replace `type="hidden"` with `type="text"` from this line
- A new date/time field becomes available in the edit/new link dialog.
- You can set the timestamp manually by entering it in the format `YYYMMDD_HHMMS`.


### See also

- [Add a new custom field to shaares (example patch)](https://gist.github.com/nodiscc/8b0194921f059d7b9ad89a581ecd482c)
- [Download CSS styles for shaarlis listed in an opml file](https://gist.github.com/nodiscc/dede231c92cab22c3ad2cc24d5035012)
- [Copy an existing Shaarli installation over SSH, and serve it locally](https://gist.github.com/nodiscc/ed161c66e5b028b5299b0a3733d01c77)
- [Create multiple Shaarli instances, generate an HTML index of them](https://gist.github.com/nodiscc/52e711cda3bc47717c16065231cf6b20)
