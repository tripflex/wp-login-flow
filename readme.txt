=== WP Login Flow ===
Contributors: tripflex
Donate link: https://www.gittip.com/tripflex
Tags: wp-login, wp-login.php, activation, activate, email, background, responsive, color, login, customize, permalink, rewrite, url, register, lost, forgot, password, template
Requires at least: 4.1
Tested up to: 4.2.2
Stable tag: 2.0.0
License: GPLv3

Complete wp-login.php customization, including rewrites, require email activation, email templates, custom colors, logo, link, responsiveness, border radius, and more!

== Description ==

= Features =

* Activate account via email with link to set password
* Custom permalinks/rewrites for Login, Register, Lost Password, and Activate
* Custom wp-login.php background, colors, images, links, etc.
* Custom outgoing name/email, and Activation/Reset Password email templates
* Activation status icons on user list table
* Supports creating new users from admin (checkbox to require activation)
* Works with any plugins/themes that use native WP user registation/login

Full rewrite support for *every* wp-login.php url (register, lost password, login, etc) using your own custom permalinks/rewrites.

Full activation by email support, user gets sent a customizable activation email with link to set password through core of WordPress.  You can also customize the lost password email as well.

Completely customize the default WordPress wp-login.php page, including background, logo, url, links, colors, border radius, and more!

WP Login Flow was intended to be completely bloat free, and integrate with the core of WordPress as much as possible.  Any themes, plugins, or other code that uses the native WordPress functions and hooks for registration, lost password, etc, should be supported.

Fully documented code

** Known issue with outdated limit login attempts plugin

[Read more about WP Login Flow](https://github.com/tripflex/wp-login-flow).

= Documentation =

Documentation will be maintained on the [GitHub Wiki here](https://github.com/tripflex/wp-login-flow/wiki).

= Contributing and reporting bugs =

You can contribute code and localizations to this plugin via GitHub: [https://github.com/tripflex/wp-login-flow](https://github.com/tripflex/wp-login-flow)

= Support =

If you spot a bug, you can of course log it on [Github](https://github.com/tripflex/wp-login-flow)

Or contact me at myles@smyl.es

== Installation ==

= Automatic installation =

Install through Wordpress, select activate.

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favourite FTP application.

* Download the plugin file to your computer and unzip it
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's `wp-content/plugins/` directory.
* Activate the plugin from the Plugins menu within the WordPress admin.

== Screenshots ==

1. Screenshot 1

== Changelog ==

= 2.0.0 =
* Complete refactor and rewrite of codebase
* Fully functional rewrites now
* Added lost password template
* Lost password Rewrite added
* Lost password URL now supports rewrites
* Fixed thank you notification
* Fixed activation page going to reset password rewrite
* Reset Password changed to Set Password on activation pages
* Added activation status column on user list table
* Support for new users from admin area (checkbox to require activation)
* Option to require existing users to activate before they can login again
* Many other enhancements and improvements

= 1.0.0 =
* Initial Creation
