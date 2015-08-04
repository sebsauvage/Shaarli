#Datastore hacks
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

### Changing the timestamp for a link
* Look for `<input type="hidden" name="lf_linkdate" value="{$link.linkdate}">` in `tpl/editlink.tpl` (line 14)
* Remove `type="hidden"` from this line
* A new date/time field becomes available in the edit/new link dialog.
* You can set the timestamp manually by entering it in the format `YYYMMDD_HHMMS`.
