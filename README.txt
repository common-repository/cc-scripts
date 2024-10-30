=== CC-Scripts ===
Contributors: ClearcodeHQ, PiotrPress
Tags: JavaScript, scripts, head, body, footer, minify, minification, dependency, dependencies, output buffering, Clearcode, PiotrPress
Requires PHP: 7.0
Requires at least: 4.6
Tested up to: 4.9.4
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Add custom JavaScript code to all your WordPress pages at once via the Admin panel. 

== Description ==

The CC-Scripts plugin gives Admin users the ability to add custom JavaScript code to every page on WordPress website in the head/footer and/or directly after the opening body html tag.
Additionally, you can minify scripts using JSMin php library and/or displaying it selectively using dependencies.
The best part is that you can do this from the Admin panel, perfect for those who don't have the programming skills needed to manually add custom JavaScript code to template files.

== Installation ==

= From your WordPress Dashboard =

1. Go to 'Plugins > Add New'
2. Search for 'CC-Scripts'
3. Activate the plugin from the Plugin section in your WordPress Dashboard.

= From WordPress.org =

1. Download 'CC-Scripts'.
2. Upload the 'cc-scripts' directory to your '/wp-content/plugins/' directory using your favorite method (ftp, sftp, scp, etc...)
3. Activate the plugin from the Plugin section in your WordPress Dashboard.

= Once Activated =

Visit 'Settings > Scripts', add your scripts into head and/or body fields and chose the method of inclusion.

**Please note**

* wp_head action is the preferred location for firing scripts in the head section.
* wp_body action is the preferred location for firing scripts in the body section.
* wp_footer action is the preferred location for firing scripts in the footer section.
* Use output buffering if you don't have access to the source code of your theme and/or you don't know if the functions above are included in your theme.

= Multisite =

The plugin can be activated and used for just about any use case.

* Activate at site level to load the plugin on that site only.
* Activate at network level for full integration with all sites in your network (this is the most common type of multisite installation).

== Frequently Asked Questions ==

= How do I use the wp_body action? =

Paste the following code directly after the opening `<body>` tag in your theme:
`<?php do_action( 'wp_body' ); ?>`

= Which inclusion method should I chose? =

* wp_head action is the preferred location for firing scripts in the head section.
* wp_body action is the preferred location for firing scripts in the body section.
* wp_footer action is the preferred location for firing scripts in the footer section.
* Use output buffering if you don't have access to the source code of your theme and/or you don't know if the functions above are included in your theme.

= What are minimum requirements for the plugin? =

PHP interpreter version >= 5.3

== Screenshots ==

1. **WordPress General Settings** - Visit 'Settings > Scripts', add your scripts into the head and/or body fields and chose the method of inclusion.

== Changelog ==

= 1.2.0 =
*Release date: 28.02.2018*

* Changed function from `wp_body_open()` to `wp_body()`.

= 1.1.0 =
*Release date: 12.12.2016*

* Added support for footer scripts.
* Added support for minification.
* Added support for dependencies.

= 1.0.0 =
*Release date: 24.08.2016*

* First stable version of the plugin.