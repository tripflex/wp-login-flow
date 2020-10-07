=== WP Login Flow ===
Contributors: tripflex
Donate link: https://www.patreon.com/smyles
Tags: wp-login, wp-login.php, login flow, wp login flow, activation, activate, email, background, responsive, color, login, customize, custom, permalink, rewrite, url, register, lost, forgot, password, template, reset, register, registration, password, password registration, admin bar, smyles, tripflex
Requires at least: 4.4
Tested up to: 5.5.1
Stable tag: 3.1.1
License: GPLv3

wp-login permalinks, auto login, register w/ pass, login/logout redirects, email as username, bg/logo/color customizations, hide admin bar, and more!

== Description ==

WP Login Flow is a complete solution to make `wp-login.php` not suck!  Below are all the features organized by what they relate to.  This plugin is completely open source, and has **NO ADS OR UPSELLS**

= Registration =
* Enhances Registration flow and wording for "Activation" (more below)
* Custom notices and wording to match "Activation" instead of "Reset Password"
* Allow users to register and set a password (includes password strength)
* Auto Login users after registration
* Hide Username field and use Email as Username
* Loading spinner after clicking Register
* Add unlimited custom text fields to Register form (saved to user meta)
* Notice shown to user when attempt to login with unactivated account

= Permalinks/URLs =
* Customize Login, Register, Activation, Lost Password, Reset Password, and Logged Out URLs
* Custom "Activation" URL permalink for Registration (instead of default Reset Password)
* Setting to auto disable using custom URLs if `.htaccess` or `web.config` does not exist

= Redirects =
* Custom Login/Logout default login redirect URL
* Custom Login/Logout redirects based on User Role
* Custom Login/Logout redirects based on specific User

= Page Customizations =
* Background, Font, Link, and Link Hover Colors (with Color Picker)
* Custom CSS with Code Editor
* Customize Logo URL, and Title
* Upload custom Logo
* Customize Login Box Font and Background Colors
* Custom Border Radius for Login Box
* Enable Responsive Width for Login Box

= Email =
* Customize Outgoing WordPress From Name and Email
* Customize New Account Activation Required Email (WYSIWYG Editor)
* Customize New Account Email (WYSIWYG Editor - when user sets own password)
* Customize Lost Password Email Template (WYSIWYG Editor)

= Notices =
* Customize Account Requires Activation Notice
* Customize Pending Activation Notice
* Customize Successful Activation Notice

= Other Features =
* Color scheme matches WordPress admin area color scheme
* Fully documented and clean code base
* Login Page Spinning Loader
* Hide frontend Admin Bar from non-admin Users
* Activation status icons on user list table
* Works with any plugins/themes that use native WP user registration/login

= Default Registration Enhancements =
By default, when a user registers on a WordPress site, they are sent a password reset email which is used for account "activation", but that also sends the user a URL that is for resetting a password, and even shows "Reset Password" on the page.  This plugin fixes these problems by allow you to customize the activation email sent, adding custom permalink for activations, updating wording to match "activation" instead of reset password, and more ... all for a better UX (User Experience).  See screenshots or video for examples of this.

WP Login Flow was intended to be completely bloat free, and integrate with the core of WordPress as much as possible.  Any themes, plugins, or other code that uses the native WordPress functions and hooks for registration, lost password, etc, should be supported.

= Features Coming Soon =
* Login Limiter based on Limit Login Attempts
* Bulk remove unactivated accounts
* [Your IDEA!](https://github.com/tripflex/wp-login-flow/issues/new)

= WP Login Flow History =
I originally created WP Login Flow back in 2014 to solve what I considered to be a huge issue .. and that was passwords being emailed to users on registration (email is never secure!).  My original implementation of this plugin added the exact feature that is now default in WordPress, by using the Reset Password handling to add "Activation" for new user registration.  After this was added to core I no longer had a need for this plugin, but in 2019 I decided to take the time to fully revamp the plugin, integrate with the latest versions of WordPress, and add additional features.


[Read more about WP Login Flow](https://github.com/tripflex/wp-login-flow).

= Documentation =

Documentation will be maintained on the [GitHub Wiki here](https://github.com/tripflex/wp-login-flow/wiki).

= Contributing and reporting bugs =

You can contribute code and localizations to this plugin via GitHub: [https://github.com/tripflex/wp-login-flow](https://github.com/tripflex/wp-login-flow)

= Support =

If you spot a bug, you can of course log it on [Github](https://github.com/tripflex/wp-login-flow)

== Installation ==

= Automatic installation =

Install through Wordpress, select activate.

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favourite FTP application.

* Download the plugin file to your computer and unzip it
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's `wp-content/plugins/` directory.
* Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

= Does this plugin work with newest WP version and also older versions? =
Yes!  It works with any version of WordPress 4.4 or newer!

== Screenshots ==

1. Custom Permalink Configuration
2. Permalink Examples
3. Registration Customization
4. Registration Form Examples
5. Activate Account Default vs WP Login Flow Comparison
6. New Account Set Password Default vs WP Login Flow Comparison
7. Custom Login/Logout Redirects
8. Page Customizations (bg, font, link, custom css)
9. Page Customizations Logo, Title, and URL
10. Page Login Box Customizations
11. Customize WordPress Outgoing Email From Name/Email
12. New User Email Template Customization
13. Notice Customizations (shown on wp-login page)
14. Plugin Settings

== Changelog ==

= 3.1.1 - September 7, 2020 =
* NEW setting to auto disable custom redirects/rewrites if htaccess or web.config do not exist
* Added Nginx notice that rewrites must be manually set
* Fix CodeMirror enqueued on all admin pages

= 3.1.0 - September 9, 2019 =
* NEW setting to auto disable custom redirects/rewrites if htaccess or web.config do not exist
* Added Nginx notice that rewrites must be manually set
* Fix CodeMirror enqueued on all admin pages

= 3.0.3 - July 19, 2019 =
* Fix login/register loader not being added when activation is not enabled
* Bump tested up to 5.2.2

= 3.0.2 - May 30, 2019 =
* Use `login_headertext` for 5.2.0+ instead of `login_headertitle`
* Bump tested up to 5.2.1

= 3.0.0 - March 21, 2019 =
* Added full support for WordPress 4.4+ and 5.0+
* Updated activation handling to work with latest WP
* Added Auto Login feature
* Added Email as Username feature
* Added Register with Password feature
* Added Password Strength meter on Register with Password
* Added Custom Registration fields
* Added Login/Logout Redirect feature
* Added Login/Logout Redirect by User Role
* Added hide admin bar feature
* Added more wp-login.php page customizations
* Added New User Account email template (when user sets pw)
* Added Register/Login Spin Loader
* Removed "Require Activation" to login (any account with valid un/pw can login)
* Disable password nag when user registers with password
* Custom CSS area now uses code editor with syntax highlighting
* Updated `wp_new_user_notification` to match latest and fix pw reset key
* Updated `wp_password_change_notification` to match latest versions
* Removed old outdated activation wording and references
* Many other minor bug fixes, and enhancements

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

== Upgrade Notice ==
= 3.0.0 =
Major refactoring of entire codebase and updated support for WordPress 4.4+ and 5.0+ -- please test your site after upgrading!
