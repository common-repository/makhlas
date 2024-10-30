=== Makhlas ===
Contributors: farmad
Donate link: http://makhlas.com/
Version: 1.0.0
Tags: link, url, url shortener, url shortening, shorturl, short_url, shortlink, short permalink
Requires at least: 3.0.1
Tested up to: 3.4
Requires PHP: 5.2.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Creates a custom short URL when saving posts.

== Description ==
 
Makhlas is the easiest way to replace the internally generated WordPress shortlinks. 

Shortlinks are a great way to quickly share posts on social media like Twitter, Facebook and Whatsapp. Just finished writing an amazing post and want to share that post with your friend? It’s a lot easier to text message a shortlink than the entire address.

= Available Template Tags =

* `makhlas_the_short_url_link()` outputs an anchor (a) tag, ex: `<a href="http://ly.gy/sample" class="makhlas short-link" rel="nofollow">http://ly.gy/sample</a>`
* `makhlas_get_the_short_link` retrieves the above anchor for storage in a variable
* `makhlas_the_short_url()` outputs the short URL, ex: `http://ly.gy/sample`
* `makhlas_get_the_short_url()` retrieves the above URL for storage in a variable
* `makhlas_the_short_url_input()` outputs an form input (a) tag, ex: `<input class="makhlas short-url-input" type="text" value="http://ly.gy/sample"></input>`

== Installation ==

1. Upload the zip file to the /wp-content/plugins/ directory
1. Unzip
1. Activate the plugin through the ‘Plugins’ menu in WordPress
1. Visit the settings page under Makhlas to add API key