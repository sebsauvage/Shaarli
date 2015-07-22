#Example patch   add new via field for links
Example patch to add a new field ("via") for links, an input field to set the "via" property from the "edit link" dialog, and display the "via" field in the link list display. **Untested, use at your own risk**

Thanks to @Knah-Tsaeb in https://github.com/sebsauvage/Shaarli/pull/158

```
From e0f363c18e8fe67990ed2bb1a08652e24e70bbcb Mon Sep 17 00:00:00 2001
From: Knah Tsaeb <knah-tsaeb@knah-tsaeb.org>
Date: Fri, 11 Oct 2013 15:18:37 +0200
Subject: [PATCH] Add a "via"/origin property for links, add new input in "edit link" dialog[](.html)
Thanks to:
* https://github.com/Knah-Tsaeb/Shaarli/commit/040eb18ec8cdabd5ea855e108f81f97fbf0478c4
* https://github.com/Knah-Tsaeb/Shaarli/commit/4123658eae44d7564d1128ce52ddd5689efee813
* https://github.com/Knah-Tsaeb/Shaarli/commit/f1a8ca9cc8fe49b119d51b2d8382cc1a34542f96

---
 index.php         | 43 ++++++++++++++++++++++++++++++++-----------
 tpl/editlink.html |  1 +
 tpl/linklist.html |  1 +
 3 files changed, 34 insertions(+), 11 deletions(-)

diff --git a/index.php b/index.php
index 6fae2f8..53f798e 100644
--- a/index.php
+++ b/index.php
@@ -436,6 +436,12 @@ if (isset($_POST['login']))[](.html)
 // ------------------------------------------------------------------------------------------
 // Misc utility functions:
 
+// Try to get just domain for @via
+function getJustDomain($url){
+    $parts = parse_url($url);   
+    return trim($parts['host']);[](.html)
+    }
+
 // Returns the server URL (including port and http/https), without path.
 // e.g. "http://myserver.com:8080"
 // You can append $_SERVER['SCRIPT_NAME'] to get the current script URL.[](.html)
@@ -799,7 +805,8 @@ class linkdb implements Iterator, Countable, ArrayAccess
             $found=   (strpos(strtolower($l['title']),$s)!==false)[](.html)
                    || (strpos(strtolower($l['description']),$s)!==false)[](.html)
                    || (strpos(strtolower($l['url']),$s)!==false)[](.html)
-                   || (strpos(strtolower($l['tags']),$s)!==false);[](.html)
+                   || (strpos(strtolower($l['tags']),$s)!==false)[](.html)
+                   || (!empty($l['via']) && (strpos(strtolower($l['via']),$s)!==false));[](.html)
             if ($found) $filtered[$l['linkdate'[ = $l;](-=-$l;.html)
         }
         krsort($filtered);
@@ -814,7 +821,7 @@ class linkdb implements Iterator, Countable, ArrayAccess
         $t = str_replace(',',' ',($casesensitive?$tags:strtolower($tags)));
         $searchtags=explode(' ',$t);
         $filtered=array();
-        foreach($this->links as $l)
+        foreach($this-> links as $l)
         {
             $linktags = explode(' ',($casesensitive?$l['tags']:strtolower($l['tags'])));[](.html)
             if (count(array_intersect($linktags,$searchtags)) == count($searchtags))
@@ -905,7 +912,7 @@ function showRSS()
     else $linksToDisplay = $LINKSDB;
     $nblinksToDisplay = 50;  // Number of links to display.
     if (!empty($_GET['nb']))  // In URL, you can specificy the number of links. Example: nb=200 or nb=all for all links.[](.html)
-    { 
+    {
         $nblinksToDisplay = $_GET['nb']=='all' ? count($linksToDisplay) : max($_GET['nb']+0,1) ;[](.html)
     }
 
@@ -944,7 +951,12 @@ function showRSS()
         // If user wants permalinks first, put the final link in description
         if ($usepermalinks===true) $descriptionlink = '(<a href="'.$absurl.'">Link</a>)';
         if (strlen($link['description'])>0) $descriptionlink = '<br>'.$descriptionlink;[](.html)
-        echo '<description><![CDATA['.nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description'])))).$descriptionlink.'[></description>'."\n</item>\n";](></description>'."\n</item>\n";.html)
+        if(!empty($link['via'])){[](.html)
+          $via = '<br>Origine => <a href="'.htmlspecialchars($link['via']).'">'.htmlspecialchars(getJustDomain($link['via'])).'</a>';[](.html)
+        } else {
+         $via = '';
+        }
+        echo '<description><![CDATA['.nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description'])))).$via.$descriptionlink.'[></description>'."\n</item>\n";](></description>'."\n</item>\n";.html)
         $i++;
     }
     echo '</channel></rss><!-- Cached version of '.htmlspecialchars(pageUrl()).' -->';
@@ -980,7 +992,7 @@ function showATOM()
     else $linksToDisplay = $LINKSDB;
     $nblinksToDisplay = 50;  // Number of links to display.
     if (!empty($_GET['nb']))  // In URL, you can specificy the number of links. Example: nb=200 or nb=all for all links.[](.html)
-    { 
+    {
         $nblinksToDisplay = $_GET['nb']=='all' ? count($linksToDisplay) : max($_GET['nb']+0,1) ;[](.html)
     }
 
@@ -1006,11 +1018,16 @@ function showATOM()
 
         // Add permalink in description
         $descriptionlink = htmlspecialchars('(<a href="'.$guid.'">Permalink</a>)');
+        if(isset($link['via']) && !empty($link['via'])){[](.html)
+          $via = htmlspecialchars('</br> Origine => <a href="'.$link['via'].'">'.getJustDomain($link['via']).'</a>');[](.html)
+        } else {
+          $via = '';
+        }
         // If user wants permalinks first, put the final link in description
         if ($usepermalinks===true) $descriptionlink = htmlspecialchars('(<a href="'.$absurl.'">Link</a>)');
         if (strlen($link['description'])>0) $descriptionlink = '&lt;br&gt;'.$descriptionlink;[](.html)
 
-        $entries.='<content type="html">'.htmlspecialchars(nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description']))))).$descriptionlink."</content>\n";[](.html)
+        $entries.='<content type="html">'.htmlspecialchars(nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description']))))).$descriptionlink.$via."</content>\n";[](.html)
         if ($link['tags']!='') // Adding tags to each ATOM entry (as mentioned in ATOM specification)[](.html)
         {
             foreach(explode(' ',$link['tags']) as $tag)[](.html)
@@ -1478,7 +1495,7 @@ function renderPage()
         if (!startsWith($url,'http:') && !startsWith($url,'https:') && !startsWith($url,'ftp:') && !startsWith($url,'magnet:') && !startsWith($url,'?'))
             $url = 'http://'.$url;
         $link = array('title'=>trim($_POST['lf_title']),'url'=>$url,'description'=>trim($_POST['lf_description']),'private'=>(isset($_POST['lf_private']) ? 1 : 0),[](.html)
-                      'linkdate'=>$linkdate,'tags'=>str_replace(',',' ',$tags));
+                      'linkdate'=>$linkdate,'tags'=>str_replace(',',' ',$tags), 'via'=>trim($_POST['lf_via']));[](.html)
         if ($link['title']=='') $link['title']=$link['url']; // If title is empty, use the URL as title.[](.html)
         $LINKSDB[$linkdate] = $link;[](.html)
         $LINKSDB->savedb(); // Save to disk.
@@ -1556,7 +1573,8 @@ function renderPage()
             $title = (empty($_GET['title']) ? '' : $_GET['title'] ); // Get title if it was provided in URL (by the bookmarklet).[](.html)
             $description = (empty($_GET['description']) ? '' : $_GET['description']); // Get description if it was provided in URL (by the bookmarklet). [Bronco added that][](.html)
             $tags = (empty($_GET['tags']) ? '' : $_GET['tags'] ); // Get tags if it was provided in URL[](.html)
-            $private = (!empty($_GET['private']) && $_GET['private'] === "1" ? 1 : 0); // Get private if it was provided in URL [](.html)
+            $via = (empty($_GET['via']) ? '' : $_GET['via'] );[](.html)
+            $private = (!empty($_GET['private']) && $_GET['private'] === "1" ? 1 : 0); // Get private if it was provided in URL[](.html)
             if (($url!='') && parse_url($url,PHP_URL_SCHEME)=='') $url = 'http://'.$url;
             // If this is an HTTP link, we try go get the page to extract the title (otherwise we will to straight to the edit form.)
             if (empty($title) && parse_url($url,PHP_URL_SCHEME)=='http')
@@ -1567,7 +1585,7 @@ function renderPage()
  					 {
                         // Look for charset in html header.
  						preg_match('#<meta .*charset=.*>#Usi', $data, $meta);
- 
+
  						// If found, extract encoding.
  						if (!empty($meta[0]))[](.html)
  						{
@@ -1577,7 +1595,7 @@ function renderPage()
 							$html_charset = (!empty($enc[1])) ? strtolower($enc[1]) : 'utf-8';[](.html)
  						}
  						else { $html_charset = 'utf-8'; }
- 
+
  						// Extract title
  						$title = html_extract_title($data);
  						if (!empty($title))
@@ -1592,7 +1610,7 @@ function renderPage()
                 $url='?'.smallHash($linkdate);
                 $title='Note: ';
             }
-            $link = array('linkdate'=>$linkdate,'title'=>$title,'url'=>$url,'description'=>$description,'tags'=>$tags,'private'=>$private);
+            $link = array('linkdate'=>$linkdate,'title'=>$title,'url'=>$url,'description'=>$description,'tags'=>$tags,'via' => $via,'private'=>$private);
         }
 
         $PAGE = new pageBuilder;
@@ -1842,6 +1860,9 @@ function buildLinkList($PAGE,$LINKSDB)
         $taglist = explode(' ',$link['tags']);[](.html)
         uasort($taglist, 'strcasecmp');
         $link['taglist']=$taglist;[](.html)
+        if(!empty($link['via'])){[](.html)
+          $link['via']=htmlspecialchars($link['via']);[](.html)
+        }
         $linkDisp[$keys[$i[ = $link;](-=-$link;.html)
         $i++;
     }
diff --git a/tpl/editlink.html b/tpl/editlink.html
index 4a2c30c..14d4f9c 100644
--- a/tpl/editlink.html
+++ b/tpl/editlink.html
@@ -16,6 +16,7 @@
 	        <i>Title</i><br><input type="text" name="lf_title" value="{$link.title|htmlspecialchars}" style="width:100%"><br>
 	        <i>Description</i><br><textarea name="lf_description" rows="4" cols="25" style="width:100%">{$link.description|htmlspecialchars}</textarea><br>
 	        <i>Tags</i><br><input type="text" id="lf_tags" name="lf_tags" value="{$link.tags|htmlspecialchars}" style="width:100%"><br>
+	        <i>Origine</i><br><input type="text" name="lf_via" value="{$link.via|htmlspecialchars}" style="width:100%"><br>
 	        {if condition="($link_is_new && $GLOBALS['privateLinkByDefault']==true) || $link.private == true"}[](.html)
             <input type="checkbox" checked="checked" name="lf_private" id="lf_private">
             &nbsp;<label for="lf_private"><i>Private</i></label><br>
diff --git a/tpl/linklist.html b/tpl/linklist.html
index ddc38cb..0a8475f 100644
--- a/tpl/linklist.html
+++ b/tpl/linklist.html
@@ -43,6 +43,7 @@
                 <span class="linktitle"><a href="{$redirector}{$value.url|htmlspecialchars}">{$value.title|htmlspecialchars}</a></span>
                 <br>
                 {if="$value.description"}<div class="linkdescription"{if condition="$search_type=='permalink'"} style="max-height:none !important;"{/if}>{$value.description}</div>{/if}
+                {if condition="isset($value.via) && !empty($value.via)"}<div><a href="{$value.via}">Origine => {$value.via|getJustDomain}</a></div>{/if}
                 {if="!$GLOBALS['config'['HIDE_TIMESTAMPS'] || isLoggedIn()"}]('HIDE_TIMESTAMPS']-||-isLoggedIn()"}.html)
                     <span class="linkdate" title="Permalink"><a href="?{$value.linkdate|smallHash}">{$value.localdate|htmlspecialchars} - permalink</a> - </span>
                 {else}
-- 
2.1.1
```
