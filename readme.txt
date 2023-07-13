=== WordPress Error Monitoring by Bugsnag ===
Contributors: loopj
Tags: bugsnag, error, monitoring, exception, logging
Requires at least: 2.0
Tested up to: 6.3
Stable tag: 1.6.3
License: GPLv2 or later

Bugsnag is a WordPress plugin that automatically detects errors & crashes on your WordPress site, and notifies you by email, chat or issues system


== Description ==

Bugsnag is a WordPress plugin that automatically detects errors & crashes on your WordPress site, and notifies you by email, chat or ticket system.

All websites crash from time to time, including WordPress sites! The *WordPress Error Monitoring* plugin by Bugsnag automatically detects crashes, exceptions and other errors in your WordPress PHP code as well as any errors in plugins you are using.

Errors are sent to your [Bugsnag Dashboard](https://bugsnag.com) for you to view and debug, and we'll also notify you by email, chat, sms or create a ticket in your issue tracking system if you use one. We'll also show you exactly how many times each error occurred, and how many users were impacted by each crash.


== Installation ==

Bugsnag is available through the [WordPress Plugins Directory](http://wordpress.org/plugins/), so you can download and install automatically from the Plugins menu on your WordPress admin page.

To manually install Bugsnag:

1. Unzip `bugsnag-wordpress.zip`
1. Upload the `bugsnag` directory from the zip file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click the 'configure' link next to the Bugsnag plugin, and follow the instructions


== Screenshots ==

1. Bugsnag dashboard list of errors
2. Bugsnag dashboard error details


== Changelog ==

= 1.6.3 =
* Fix PHP 8.2 deprecation notice

= 1.6.2 =
* Fix broken url in notifier configuration & add docs link to test notification.
* Bump "tested up to" WordPress version

= 1.6.1 =
* Bump "tested up to" WordPress version

= 1.6.0 =
* Add support for setting the release stage using the new "wp_get_environment_type" function
* Update bugsnag-php to v2.10.1

= 1.5.0 =
* Clarify the "Test Bugsnag" form action
* Improve error message when methods are called with no api key set
* Add a constant to control setting error handlers

= 1.4.0 =
* Add WordPress version string to report payloads (device.runtimeVersions)
* Added ability to define API key as constant
* Removed form action to prevent deprecation warnings
* Fixed broken links on settings page bugsnag.com
* Made bugsnagWordpress global to make available for CLI scripts

= 1.3.1 =
* Remove deprecated screen_icon function from settings page

= 1.3.0 =
* Fix version constraints
* General fixes for WP 4.5 compatibility

= 1.2.1 =
* Add support for WordPress Multisite installations.

= 1.2.0 =
* Update bugsnag-php to allow cURL or fopen.

= 1.1.2 =
* Allow configuration of filter fields.
* Add a 'Test Bugsnag' button to the settings page.

= 1.1.1 =
* Identify as wordpress notifier instead of PHP.
* Allow configuration of error reporting levels.

= 1.0.1 =
* Fix bug where bugsnag-php library wasn't being included in zip file

= 1.0.0 =
* Initial release
