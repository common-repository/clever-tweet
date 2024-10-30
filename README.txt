=== Clever Tweet ===
Contributors: P. Prins
Tags: widget, twitter, tweets, jQuery
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 2.0.0
Plugin URI: http://www.cleverweb.nl/projects/clever-tweet/

Clever Tweet is a widget that will display tweets with a cool jQuery animation!

== Description ==

Clever Tweet will give you the following features: 

* Customizable amount of tweets to display
* Onmouseover stops animation
* Option to show retweets (RT)
* Option to show mentions (@username)
* Customizable jQuery animation interval
* Clickable hashtags
* Clickable urls
* Cache for faster loading and less requests to Twitter
* Integrates well with all different templates.


== Installation ==

1. Download the plugin archive and expand it (you've likely already done this).
2. Put the 'clever-tweet' directory into your wp-content/plugins/ directory.
3. Go to the Plugins page in your WordPress Administration area and click 'Activate' for Clever Tweet.
4. Go to the Settings > Clever Tweet page in your Administration area
5. Customize the Plugin options
6. Go to the Widget page in your WordPress Administration area and drag it to your sidebar.
7. Requires atleast PHP 5.2


== Frequently Asked Questions ==

= Whats up with the 'Tweet block height'? =

This is somewhat the most important thing of the whole widget. The script that will control the animation
of your tweets depends on the size of a (one) tweetblock. For example if you styled a list with blocks in your CSS
and gave it a height of 50 pixels with a 2 pixel padding your tweetblock height is: 54

50 pixels + 2 pixels padding top + 2 pixels padding bottom

The Clever Tweet widget will automatically build the right size of the sidebar Widget. See the plugin URL
for an example. 

== Changelog ==

= 1.0.0 =
* Initial release

= 1.0.1 =
* Fixed Typo

= 1.0.2 =
* Fixed little hastag bug

= 1.0.3 =
* Changed Plugin URI, it was pointing to a wrong page

= 1.0.4 =
* Added the actual link to the Twitter post with a css styled image.

= 1.0.5 =
* Rewritten handling of Twitter Feeds
* Created Administration settings page
* Added cache support for eliminating the Twitter Rate-Limit

= 1.0.6 =
* Fixed some theme related bug (Thanks to Mr. K. Stone)

= 1.0.7 =
* Actual Twitter posts were not clickable. Fixed this by adding a link to the specific Twitter post.

= 1.0.8 =
* Dropped SimpleXML object
* Added another way to load feeds if file_get_contents fail
* Fixed issue where Latin characters were not shown properly
* Minor bugfixes

= 2.0.0 =
* New cool feature: Load more tweets and show a few at once
* Added a timestamp / date to the tweets
* Above 2 features are being credited to B. Jurgens
* Bug fixes
* Overall stability of the inline elements