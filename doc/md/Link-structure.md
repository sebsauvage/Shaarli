## Link structure

Every link available through the `LinkDB` object is represented as an array 
containing the following fields:

  * `id` (integer): Unique identifier.
  * `title` (string): Title of the link.
  * `url` (string): URL of the link. Used for displayable links (without redirector, url encoding, etc.).  
           Can be absolute or relative for Notes.
  * `real_url` (string): Real destination URL, can be redirected, encoded, etc.
  * `shorturl` (string): Permalink small hash.
  * `description` (string): Link text description.
  * `private` (boolean): whether the link is private or not.
  * `tags` (string): all link tags separated by a single space
  * `thumbnail` (string|boolean): relative path of the thumbnail cache file, or false if there isn't any.
  * `created` (DateTime): link creation date time.
  * `updated` (DateTime): last modification date time.
  