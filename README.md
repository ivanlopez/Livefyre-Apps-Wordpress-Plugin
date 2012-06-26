Livefyre-Wordpress-Comments
===========================

Livefyre's V3 platform implementation for WordPress, as a plugin.

To build a livefyre.zip that you can install with the "upload" feature in wp-admin:
./build.sh

== Changelog ==

== 3.50 ==
* Added a trim() filter to remove any trailing space or control characters from the site secret key - improves Livefyre Comments V3 compatibility for very old installations.
* Reorganized code and applied coding standards to PHP plugin architecture.