# Easy Retweet #
**Contributors:** sudar  
**Tags:** posts, Twitter, tweet, Retweet  
**Requires at least:** 2.8  
**Donate Link:** http://sudarmuthu.com/if-you-wanna-thank-me  
**Tested up to:** 4.1  
**Stable tag:** 3.0.3  

Adds a Retweet button to your WordPress posts

## Description ##

Easy ReTweet is a WordPress Plugin, which let's you add retweet or Tweet this buttons for your WordPress posts, together with the retweet count.

### Usage

There are three ways you can add the retweet button. Automatic way, manual way and using shortcodes

#### Automatic way

Install the Plugin and choose the type and position of the button from the Plugin's settings page. You can also specifically enable/disable the button for each post or page from the write post/page screen.

#### Manual way

If you want more control over the way the button should be positioned, then you can manually call the button using the following code.

`if (function_exists('easy_retweet_button')) echo easy_retweet_button();`

#### Using shortcodes

You can also place the shortcode [easy-retweet] anywhere in your post. This shortcode will be replaced by the button when the post is rendered.

### Development

The development of the Plugin happens over at [github][6]. If you want to contribute to the Plugin, fork the [project at github][6] and send me a pull request.

If you are not familiar with either git or Github then refer to this [guide to see how fork and send pull request](http://sudarmuthu.com/blog/contributing-to-project-hosted-in-github).

If you are looking for ideas, then you can start with one of the following TODO items :)

### TODO

The following are the features that I am thinking of adding to the Plugin, when I get some free time. If you have any feature request or want to increase the priority of a particular feature, then let me know.

- Add Google Analytics tracking to shortcodes and template function
- Add tracking of tweet button clicks

### Support

- If you have found a bug/issue or have a feature request, then post them in [github issues][7]
- If you have a question about usage or need help to troubleshoot, then post in WordPress forums or leave a comment in [Plugins's home page][1]
- If you like the Plugin, then kindly leave a review/feedback at [WordPress repo page][8].
- If you find this Plugin useful or and wanted to say thank you, then there are ways to [make me happy](http://sudarmuthu.com/if-you-wanna-thank-me) :) and I would really appreciate if you can do one of those.
- Checkout other [WordPress Plugins][5] that I have written
- If anything else, then contact me in [twitter][3].

 [1]: http://sudarmuthu.com/wordpress/easy-retweet
 [3]: http://twitter.com/sudarmuthu
 [4]: http://sudarmuthu.com/blog
 [5]: http://sudarmuthu.com/wordpress
 [6]: https://github.com/sudar/easy-retweet
 [7]: https://github.com/sudar/easy-retweet/issues
 [8]: http://wordpress.org/extend/plugins/easy-retweet/

## Translation ##

The Plugin currently has translations for the following languages.

*   Belorussian (Thanks FatCow)
*   Spanish (Thanks Carlos Varela)
*   Brazilian Portuguese (Thanks Marcelo)
*   German (Thanks Jenny Beelens)
*   Bulgarian (Thanks Dimitar Kolevski)
*   Lithuanian (Thanks Nata)
*   French (Thanks Brian Flores)
*   Romanian (Thanks Alexander Ovsov)
*   Hindi (Thanks Love Chandel)
*   Irish (Thanks Vikas Arora)
*   Danish (Thanks Jorgen)

The pot file is available with the Plugin. If you are willing to do translation for the Plugin, use the pot file to create the .po files for your language and let me know. I will add it to the Plugin after giving credit to you.

## Credits ##

Easy ReTweet uses easy [retweet JavaScript library](http://ejohn.org/blog/retweet/) created by John Resig and is based on [Bit.ly JavaScript API](http://code.google.com/p/bitly-api/wiki/JavascriptClientApiDocumentation).

## Installation ##

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

## Screenshots ##

1. General Settings page

2. Bit.ly Settings page

3. Twitter button Settings page

4. Enable/Disable button in the write post/page page

## Readme Generator ##

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.
