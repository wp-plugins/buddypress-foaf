=== Buddypress Friend of a Friend (FOAF) === 
Contributors: Florian Schießl
Donate link: http://ifs-net.de/donate.php
Tags: buddypress, foaf, social, buddy, friends
Requires at least: 3.0
Tested up to: 4.1.1
Stable Tag: trunk
License: GPLv2
License URI: http://www.opensource.org/licenses/GPL-2.0

This plugin includes a new block inside each user profile page and includes a "Friend of a Friend (FOAF)" display.

== Description ==

**Increase communication and networking at your buddypress based social network.**

This plugin includes a new block inside each user profile page and includes a "Friend of a Friend (FOAF)" display.
If you have buddypress friends enabled your users will have friends. Their friends also have friends and these friends again have friends.
So there are "social paths" inside your members friends lists. This Plugin visualizes the nearest path to the user whose profile is visited by another user.
The world is small and you'll see that most users know each other - because their friends are friends...

**Features:**

* New block inside buddypress profile page (automatically integrated) that tells you whose friend the visited user is
* Shortcode: Create a page using the shortcode [buddypressfoaf_show_potential_friends] that shows excerps of friends of your friends that are not yet your friends
* Widget: Show a random user (friend's friend or random user if you do not have friends)

Please take a look at the [screenshot section](https://wordpress.org/plugins/buddypress-foaf/screenshots/ "Screenshots") for some examples!

**More about me and my plugins**

Since the year 1999 I do administration, customizing and programming for several forums, communities and social networks. In the year 2013 I switched from another PHP framework to Wordpress.
Because not all plugins I'd like to have exist already I wrote some own plugins and I think I'll continue to do so.

If you have the scope at forums or social networks my other modules might also be interesting for you. [Just take a look at my Wordpress Profile to see all my Plugins.](http://wordpress.org/plugins/search.php?q=quan_flo "ifs-net / quan_flo Wordpress Plugins") Use them and if my work helps you to save time, earn money or just makes you happy feel free to donate - Thanks. The donation link can be found at the right sidebar next to this text.

== Installation ==

1. Upload the files to the `/wp-content/plugins/buddypress-foaf/` directory or install through WordPress directly.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Open a profile of a friend, a friend of a friend or a friend of a friend of a friend to see how it works :-)
1. Have fun!

Available Shortcodes:
* [buddypressfoaf_show_potential_friends]

== Frequently Asked Questions ==

= The admin user is not shown up in "you may know" widget or site =

Yes, the user with ID 1 (admin) is excluded

= You have a question? =

Use the support forum

== Screenshots ==

1. Buddypress profile integration: if you visit another profile you will see via which friends you now the visited person
1. A widget will introduce other members to you you might know about your friends
1. Via shortcode you can integrate a "do you know this friends of your friends" page into any place at your wordpress site!

== Changelog ==

= 2.4 =
* Admin user (user with ID 1) will not appear any more in "you might know" widget or site

= 2.3 =
* adding "add as friend" button below the users suggested as friends (thanks to maddogmcewan)

= 2.2 =
* little bug fixed: Own user could be shown as potential friend if a user does not have friends yet

= 2.1 =
* Some code corrections (debug messages, thanks to BackpackersUnion for reporting)

= 2.0 =
* Using bp_core_get_userlink() method from Buddypress now to create userlinks in widget and shortcode output

= 1.9 =
* Fixed minor bug: using user_nicename instead of user_login now.

= 1.8 =
* Avatars shown at profile pages are now always shown as thumbnails.

= 1.7 =
* Avatars that are shown in profile pages of visited user profiles are now clickable

= 1.6 =
* Code cleanup, fixed minor bugs

= 1.5 =
* Code cleanup, fixed minor bugs

= 1.4 =
* Fixed little bug in profile output

= 1.3 =
* Widget now only shows users that have been active in the last 6 months

= 1.2 =
* Fixed little bug for gettext language generation

= 1.1 =
* Introduce own page (via shortcode [buddypressfoaf_show_potential_friends] that displays the top ten of your friends' friends that have the most friends common with you. Also random friends of your friends are shown.
* Introduce a new Widget. A random user of your friends friends is shown. If you do not have friends or friends of your friends or if you are not registered at the site a random user is shown

= 1.0 = 
* First version.