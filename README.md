Livefyre-Wordpress-Comments
===========================

Livefyre's V3 platform implementation for WordPress, as a plugin.

To build a .zip that you can install with the "upload" feature in wp-admin:
zip -r livefyre.zip livefyre-comments/ -x "livefyre-comments/**/.*" -x "livefyre-comments/.*

== Changelog ==
== 3.50 ==
* Added a trim() filter to remove any trailing space or control characters from the site secret key - improves Livefyre Comments V3 compatibility for very old installations.
* Fix postback mechanism - the parent ID was being filtered out by a sanitizer. Added parent ID to the fields whitelist.