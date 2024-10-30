=== Countdown to force logout ===
Contributors: Katsushi Kawamori
Donate link: https://shop.riverforest-wp.info/donate/
Tags: countdown, logout, notify
Requires at least: 5.0
Requires PHP: 8.0
Tested up to: 6.6
Stable tag: 1.04
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Notify each logged-in user of the countdown to forced logout in the admin bar.

== Description ==

= Notifies =
* Notifies a countdown to the admin bar at 60 second intervals.
* When the countdown reaches 600 seconds or less, a notification is sent to the modal window. This number of seconds can be changed by the filter hook.

= Filter hooks =
~~~
/** ==================================================
 * Filter for countdown limit second for modal window view.
 *
 */
add_filter( 'countdown_to_force_logout_limit_sec', function(){ return 300; }, 10, 1 );
~~~

== Installation ==

1. Upload `countdown-to-force-logout` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. Admin bar view
2. Modal window view

== Changelog ==

= 1.04 =
Supported WordPress 6.4.
PHP 8.0 is now required.

= 1.03 =
Fixed an issue with the mobile display of the unread view in the admin bar.

= 1.02 =
Supported WordPress 6.1.

= 1.01 =
Simplified period display.
Add description.

= 1.00 =
Initial release.

== Upgrade Notice ==

= 1.00 =

