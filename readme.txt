=== Easy Retweet ===
Contributors: sudar 
Tags: posts, Twitter, tweet, Retweet
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 1.2.0
	
Adds a Retweet button to your WordPress posts

== Description ==

Easy ReTweet is a WordPress Plugin, which let’s you add retweet or Tweet this buttons for your WordPress posts, together with the retweet count.

**Usage**

There are three ways you can add the retweet button. Automatic way, manual way and using shortcodes

#### Automatic way

Install the Plugin and choose the type and position of the button from the Plugin’s settings page.

#### Manual way

If you want more control over the way the button should be positioned, then you can manually call the button using the following code.

if (function_exists('easy_retweet_button')) echo easy_retweet_button();

#### Using shortcodes

You can also place the shortcode [easy-retweet] anywhere in your post. This shortcode will be replaced by the button when the post is rendered.

More information available at the [Plugins home page][1].

 [1]: http://sudarmuthu.com/wordpress/easy-retweet
	

If you like this Plugin, please vote for it at <a href = "http://weblogtoolscollection.com/pluginblog/2009/07/22/easy-retweet-wordpress-plugin/">WordPress Plugin Competition blog</a>.


== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Screenshots ==
1. Settings page


== Changelog ==


###v0.1 (2009-07-13)

*   Initial Release

###v0.2 (2009-07-20)

*   Added option to add/remove button in archive pages.

###v0.3 (2009-07-21)

*   Added support for translation.

###v0.4 (2009-07-22)

*   Added option to add/remove button in home page.

###v0.5 (2009-07-24)

*   Added option to edit the text that is displayed in the button.

###v0.6 (2009-07-26)

*   Prevented the JavaScript file from getting included in admin pages.

###v0.7 (2009-07-27)

*   Added an option to add text that can be added as Prefix to the Twitter message used for retweet.

###v0.8 (2009-07-28)

*   Added support for shortcode to retweet button.

###v0.9 (2009-07-31)

*   Fixed an issue with generated JavaScript. Thanks Dougal (http://dougal.gunters.org/).

###v1.0 (2009-08-02)

*   Added option to enter your own Bit.ly username and api key.
*   Added option to sepcify your own attributes like rel or target to the retweet link.

###v1.1.0 (2009-08-05)

*   The shorturls generated using your API key, will be linked with your account.
*   Printing js using PHP, for better performance of JavaScript.

###v1.2.0 (2009-08-18)

*   Removed hard coded Plugin path to make it work even if the wp-content path is changed.

==Readme Generator== 

This Readme file was generated using <a href = 'http://sudarmuthu.com/projects/wp-readme.php'>wp-readme</a>, which generates readme files for WordPress Plugins.