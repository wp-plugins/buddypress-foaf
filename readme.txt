=== Buddypress Friend of a Friend (FOAF) === 
Contributors: Florian Schießl
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NX8D8HYLP9HYS
Tags: buddypress, foaf, social, buddy, friends
Requires at least: 3.0
Tested up to: 3.8.1
Stable Tag: trunk
License: GPLv2
License URI: http://www.opensource.org/licenses/GPL-2.0

This plugin includes a new block inside each user profile page and includes a "Friend of a Friend (FOAF)" display.

== Description ==

Increase communication and networking at your buddypress based social network.
This plugin includes a new block inside each user profile page and includes a "Friend of a Friend (FOAF)" display.
If you have buddypress friends enabled your users will have friends. Their friends also have friends and these friends again have friends.
So there are "social paths" inside your friend lists. This Plugin visualizes the nearest path to the user whose profile is visited by another user.
The world is small and you'll see that most users know each other - because their friends are friends...

Features:

* Block inside buddypress profile page (automatically integrated) that tells you whose friend the visited user is
* Shortcode: Create a page using the shortcode [buddypressfoaf_show_potential_friends] that shows excerps of friends of your friends that are not yet your friends
* Widget: Show a random user (friend's friend or random user if you do not have friends)

Just install, activate, it will automatically plug in into buddypress profile page

== Installation ==

1. Upload the files to the `/wp-content/plugins/buddypress-foaf/` directory or install through WordPress directly.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Open a profile of a friend, a friend of a friend or a friend of a friend of a friend to see how it works :-)
1. Have fun!

Available Shortcodes:
* [buddypressfoaf_show_potential_friends]

== Frequently Asked Questions ==

= You have a question? =

Use the support forum

== Changelog ==

= 1.2 =
* Fixed little bug for gettext language generation

= 1.1 =
* Introduce own page (via shortcode [buddypressfoaf_show_potential_friends] that displays the top ten of your friends' friends that have the most friends common with you. Also random friends of your friends are shown.
* Introduce a new Widget. A random user of your friends friends is shown. If you do not have friends or friends of your friends or if you are not registered at the site a random user is shown

= 1.0 = 
* First version.