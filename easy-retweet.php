<?php
/**
Plugin Name: Easy Retweet
Plugin URI: http://sudarmuthu.com/wordpress/easy-retweet
Description: Adds a Retweet button to your WordPress posts.
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
License: GPL
Author: Sudar
Version: 2.3
Author URI: http://sudarmuthu.com/
Text Domain: easy-retweet

=== RELEASE NOTES ===
2009-07-13 - v0.1 - Initial Release
2009-07-20 - v0.2 - Added option to add/remove button in archive page.
2009-07-21 - v0.3 - Added support for translation.
2009-07-22 - v0.4 - Added option to add/remove button in home page.
2009-07-24 - v0.5 - Added option to change the text which is displayed in the button.
2009-07-26 - v0.6 - Prevented the script file from loading in Admin pages.
2009-07-27 - v0.7 - Added an option to specify prefix for the Twitter message.
2009-07-28 - v0.8 - Added support for shortcode to retweet button.
2009-07-31 - v0.9 - Fixed an issue with generated JavaScript. Thanks Dougal (http://dougal.gunters.org/).
2009-08-02 - v1.0 - Added an option to specify bit.ly username, API Key and also other attributes for the link.
2009-08-05 - v1.1.0 - Generating the js file from php to fix issues wiht bit.ly key. The urls generated will appear in bit.ly account.
2009-08-18 - v1.2.0 - Removed hard coded Plugin path to make it work even if the wp-content path is changed.
2009-08-19 - v1.3.0 - Added the ability to enable/disable button on per page/post basics.
2009-10-15 - v1.4.0 - Added the ability to enable/disable button on per page/post basics, event if template function is used.
2010-01-02 - v1.5 - Ability to specify custom message for twitter instead of the post title. Also added Belorussian Translations (Thanks FatCow).
2010-03-27 - v1.6 - Added Spanish Translations (Thanks Carlos Varela).
2010-11-29 - v2.0 - Added support for official twitter button.
2010-12-05 - v2.1 - Fixed issue with the support for official twitter button.
2011-01-23 - v2.2 - Fixed issue with permalink for official twitter button.
2011-01-23 - v2.3 - Added Brazilian Portuguese translations.

Uses the script created by John Resig http://ejohn.org/blog/retweet/
*/

/*  Copyright 2010  Sudar Muthu  (email : sudar@sudarmuthu.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Easy Retweet Plugin Class
 */
class EasyRetweet {

    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        load_plugin_textdomain( 'easy-retweet', false, dirname(plugin_basename(__FILE__)) . '/languages' );

        // Register hooks
        add_action( 'admin_menu', array(&$this, 'register_settings_page') );
        add_action( 'admin_init', array(&$this, 'add_settings') );

        /* Use the admin_menu action to define the custom boxes */
        add_action('admin_menu', array(&$this, 'add_custom_box'));

        /* Use the save_post action to do something with the data entered */
        add_action('save_post', array(&$this, 'save_postdata'));

        // Enqueue the script
        add_action('template_redirect', array(&$this, 'add_script'));

        // Register filters
        add_filter('the_content', array(&$this, 'append_retweet_button') , 99);

        // register short code
        add_shortcode('easy-retweet', array(&$this, 'shortcode_handler'));

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(&$this, 'add_action_links'));

        // for outputing js code
        $this->deliver_js();
    }

    /**
     * Register the settings page
     */
    function register_settings_page() {
        add_options_page( __('Easy Retweet', 'easy-retweet'), __('Easy Retweet', 'easy-retweet'), 8, 'easy-retweet', array(&$this, 'settings_page') );
    }

    /**
     * add options
     */
    function add_settings() {
        // Register options
        register_setting( 'easy-retweet', 'retweet-style');
    }

    /**
     * Enqueue the Retweet script
     */
    function add_script() {
        // Enqueue the script only if the button type is bit.ly
        $options = get_option('retweet-style');

        if ($options['button-type'] == 'bit.ly') {
            wp_enqueue_script('retweet', get_option('home') . '/?retweetjs');
        }
    }

    /**
     * Deliver the js through PHP
     * Thanks to Sivel http://sivel.net/ for this code
     */
    function deliver_js() {
        if ( array_key_exists('retweetjs', $_GET) ) {
            $options = get_option('retweet-style');

            $options['username'] = ($options['username'] == "")? "retweetjs" : $options['username'];
            $options['apikey'] = ($options['apikey'] == "") ? "R_6287c92ecaf9efc6f39e4f33bdbf80b1" : $options['apikey'];
            $options['text'] = ($options['text'] == "")? "Retweet":$options['text'];

            header('Content-Type: text/javascript');
            print_retweet_js($options);
            
            // die after printing js
            die();
        }
    }

    /**
     * Adds the custom section in the Post and Page edit screens
     */
    function add_custom_box() {

        add_meta_box( 'retweet_enable_button', __( 'Easy Retweet Button', 'easy-retweet' ),
                    array(&$this, 'inner_custom_box'), 'post', 'side' );
        add_meta_box( 'retweet_enable_button', __( 'Easy Retweet Button', 'easy-retweet' ),
                    array(&$this, 'inner_custom_box'), 'page', 'side' );
    }

    /**
     * Prints the inner fields for the custom post/page section
     */
    function inner_custom_box() {
        global $post;
        $post_id = $post->ID;
        
        $option_value = '';
        
        if ($post_id > 0) {
            $enable_retweet = get_post_meta($post_id, 'enable_retweet_button', true);
            if ($enable_retweet != '') {
                $option_value = $enable_retweet;
            }

            $custom_retweet_text = get_post_meta($post_id, 'custom_retweet_text', true);

        }
        // Use nonce for verification
?>
        <input type="hidden" name="retweet_noncename" id="retweet_noncename" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) );?>" />
        <p>
        <label><input type="radio" name="retweet_button" value ="1" <?php checked('1', $option_value); ?> /> <?php _e('Enabled', 'easy-retweet'); ?></label>
        <label><input type="radio" name="retweet_button" value ="0"  <?php checked('0', $option_value); ?> /> <?php _e('Disabled', 'easy-retweet'); ?></label>
        </p>
        <p>
            <label><?php _e('Custom Retweet Text:', 'easy-retweet'); ?><input type ="text" name="custom_retweet_text" value ="<?php echo $custom_retweet_text;?>" /></label>
        </p>
        <p>
            <?php _e('If left blank, the post title will be used.', 'easy-retweet'); ?>
        </p>
<?php
    }

    /**
     * When the post is saved, saves our custom data
     * @param string $post_id
     * @return string return post id if nothing is saved
     */
    function save_postdata( $post_id ) {

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if ( !wp_verify_nonce( $_POST['retweet_noncename'], plugin_basename(__FILE__) )) {
            return $post_id;
        }

        if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ))
                return $post_id;
        } else {
            if ( !current_user_can( 'edit_post', $post_id ))
                return $post_id;
        }

        // OK, we're authenticated: we need to find and save the data

        if (isset($_POST['retweet_button'])) {
            $choice = $_POST['retweet_button'];
            $choice = ($choice == '1')? '1' : '0';
            update_post_meta($post_id, 'enable_retweet_button', $choice);
        }

        if (isset($_POST['custom_retweet_text'])) {
            $custom_retweet_text = esc_attr($_POST['custom_retweet_text']);
            update_post_meta($post_id, 'custom_retweet_text', $custom_retweet_text);
        }
    }

    /**
     * hook to add action links
     * @param <type> $links
     * @return <type>
     */
    function add_action_links( $links ) {
        // Add a link to this plugin's settings page
        $settings_link = '<a href="options-general.php?page=easy-retweet">' . __("Settings", 'easy-retweet') . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
     */
    function add_footer_links() {
        $plugin_data = get_plugin_data( __FILE__ );
        printf('%1$s ' . __("plugin", 'easy-retweet') .' | ' . __("Version", 'easy-retweet') . ' %2$s | '. __('by', 'easy-retweet') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
    }

    /**
     * Dipslay the Settings page
     */
    function settings_page() {
?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'Easy Retweet Settings', 'easy-retweet' ); ?></h2>

            <form id="smer_form" method="post" action="options.php">
                <?php settings_fields('easy-retweet'); ?>
                <?php $options = get_option('retweet-style'); ?>
                <?php $options['username'] = ($options['username'] == "")? "retweetjs" : $options['username'];?>
                <?php $options['align'] = ($options['align'] == "")? "hori":$options['align'];?>
                <?php $options['position'] = ($options['position'] == "")? "after":$options['position'];?>
                <?php $options['text'] = ($options['text'] == "")? "Retweet":$options['text'];?>
                
                <?php $options['button-type'] = ($options['button-type'] == "")? "twitter":$options['button-type'];?>
                <?php $options['t-count'] = ($options['t-count'] == "")? "horizontal":$options['t-count'];?>

                <h3><?php _e('General Settings', 'easy-retweet'); ?></h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Display', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="checkbox" name="retweet-style[display-page]" value="1" <?php checked("1", $options['display-page']); ?> /> <?php _e("Display the button on pages", 'easy-retweet');?></label></p>
                            <p><label><input type="checkbox" name="retweet-style[display-archive]" value="1" <?php checked("1", $options['display-archive']); ?> /> <?php _e("Display the button on archive pages", 'easy-retweet');?></label></p>
                            <p><label><input type="checkbox" name="retweet-style[display-home]" value="1" <?php checked("1", $options['display-home']); ?> /> <?php _e("Display the button in home page", 'easy-retweet');?></label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Position', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[position]" value="before" <?php checked("before", $options['position']); ?> /> <?php _e("Before the content of your post", 'easy-retweet');?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="after" <?php checked("after", $options['position']); ?> /> <?php _e("After the content of your post", 'easy-retweet');?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="both" <?php checked("both", $options['position']); ?> /> <?php _e("Before AND After the content of your post", 'easy-retweet');?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="manual" <?php checked("manual", $options['position']); ?> /> <?php _e("Manually call the retweet button", 'easy-retweet');?></label></p>
                            <p><?php _e("You can manually call the <code>easy_retweet_button</code> function. E.g. <code>if (function_exists('easy_retweet_button')) echo easy_retweet_button();.", 'easy-retweet'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Button type', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[button-type]" value="bit.ly" <?php checked("bit.ly", $options['button-type']); ?> /> <?php _e("Bit.ly hit count button", 'easy-retweet');?></label></p>
                            <p><label><input type="radio" name="retweet-style[button-type]" value="twitter" <?php checked("twitter", $options['button-type']); ?> /> <?php _e("Official Twitter button", 'easy-retweet');?></label></p>
                        </td>
                    </tr>

                </table>

                <div id="bitly-button">
                    <h3><?php _e('Bit.ly Button', 'easy-retweet'); ?></h3>
                    
                    <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Bit.ly Username', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[username]" value="<?php echo $options['username']; ?>" /></label></p>
                            <p><?php _e("A default account will be used if left blank.", 'easy-retweet');?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Bit.ly API Key', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[apikey]" value="<?php echo $options['apikey']; ?>" /></label></p>
                            <p><?php _e("You can get it from <a href = 'http://bit.ly/account/' target = '_blank'>http://bit.ly/account/</a>.", 'easy-retweet');?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Button style', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[align]" value="vert" <?php checked("vert", $options['align']); ?> /> <img src ="<?php echo plugin_dir_url(__FILE__); ?>images/vert.png" /> (<?php _e("Vertical button", 'easy-retweet');?>)</label></p>
                            <p><label><input type="radio" name="retweet-style[align]" value="hori" <?php checked("hori", $options['align']); ?> /> <img src ="<?php echo plugin_dir_url(__FILE__); ?>images/hori.png" /> (<?php _e("Horizontal button", 'easy-retweet');?>)</label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Text', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[text]" value="<?php echo $options['text']; ?>" /></label></p>
                            <p><?php _e("The text that you enter here will be displayed in the button.", 'easy-retweet');?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Message Prefix', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[prefix]" value="<?php echo $options['prefix']; ?>" /></label></p>
                            <p><?php _e("The text that you want to be added in front of each twitter message. eg: <code>RT: @sudarmuthu</code>", 'easy-retweet');?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Link Attributes', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[linkattr]" value="<?php echo $options['linkattr']; ?>" /></label></p>
                            <p><?php _e("eg: <code>rel='nofollow'</code> or <code>target = '_blank'</code>", 'easy-retweet');?></p>
                        </td>
                    </tr>

                </table>
                </div>

                <div id="twitter-button">
                    <h3><?php _e('Twitter Button', 'easy-retweet'); ?></h3>

                    <table class="form-table">

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Button Style', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[t-count]" value="vertical" <?php checked("vertical", $options['t-count']); ?> /> <img src ="<?php echo plugin_dir_url(__FILE__); ?>images/t-vert.png" /> (<?php _e("Vertical count", 'easy-retweet');?>)</label></p>
                            <p><label><input type="radio" name="retweet-style[t-count]" value="horizontal" <?php checked("horizontal", $options['t-count']); ?> /> <img src ="<?php echo plugin_dir_url(__FILE__); ?>images/t-hori.png" /> (<?php _e("Horizontal count", 'easy-retweet');?>)</label></p>
                            <p><label><input type="radio" name="retweet-style[t-count]" value="none" <?php checked("none", $options['t-count']); ?> /> <img src ="<?php echo plugin_dir_url(__FILE__); ?>images/t-no.png" /> (<?php _e("No count", 'easy-retweet');?>)</label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Recommended Twitter account', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[account1]" value="<?php echo $options['account1']; ?>" /></label></p>
                            <p><?php _e("Twitter account for users to follow after they share content from your website. This account could include your own, or that of a contributor or a partner.", 'easy-retweet');?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Additional styles', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[t-style]" value="<?php echo $options['t-style']; ?>" /></label></p>
                            <p><?php _e("eg: <code>float: left; margin-right: 10px;</code>.", 'easy-retweet');?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Language', 'easy-retweet' ); ?></th>
                        <td>
                            <select name="retweet-style[t-language]">
                                <option value="en" <?php selected("en", $options['t-language']); ?>><?php _e('English', 'easy-retweet' ); ?></option>
                                <option value="fr" <?php selected("fr", $options['t-language']); ?>><?php _e('French', 'easy-retweet' ); ?></option>
                                <option value="de" <?php selected("de", $options['t-language']); ?>><?php _e('German', 'easy-retweet' ); ?></option>
                                <option value="es" <?php selected("es", $options['t-language']); ?>><?php _e('Spanish', 'easy-retweet' ); ?></option>
                                <option value="ja" <?php selected("ja", $options['t-language']); ?>><?php _e('Japanese', 'easy-retweet' ); ?></option>
                            </select>
                            <p><?php _e("This is the language that the button will render in on your website.", 'easy-retweet');?></p>
                        </td>
                    </tr>

                </table>
                </div>

                <p class="submit">
                    <input type="submit" name="easy-retweet-submit" class="button-primary" value="<?php _e('Save Changes', 'easy-retweet'); ?>" />
                </p>
            </form>

            <h3><?php _e('Support', 'easy-retweet'); ?></h3>
            <p><?php _e('If you have any questions/comments/feedback about the Plugin then post a comment in the <a target="_blank" href = "http://sudarmuthu.com/wordpress/easy-retweet">Plugins homepage</a>.','easy-retweet'); ?></p>
            <p><?php _e('If you like the Plugin, then consider doing one of the following.', 'easy-retweet'); ?></p>
            <ul style="list-style:disc inside">
                <li><?php _e('Write a blog post about the Plugin.', 'easy-retweet'); ?></li>
                <li><a href="http://twitter.com/share" class="twitter-share-button" data-url="http://sudarmuthu.com/wordpress/easy-retweet" data-text="Easy Retweet WordPress Plugin" data-count="none" data-via="sudarmuthu">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><?php _e(' about it.', 'easy-retweet'); ?></li>
                <li><?php _e('Give a <a href = "http://wordpress.org/extend/plugins/easy-retweet/" target="_blank">good rating</a>.', 'easy-retweet'); ?></li>
                <li><?php _e('Say <a href = "http://sudarmuthu.com/if-you-wanna-thank-me" target="_blank">thank you</a>.', 'easy-retweet'); ?></li>
            </ul>
        </div>
<?php
        // Display credits in Footer
        add_action( 'in_admin_footer', array(&$this, 'add_footer_links'));
    }

    /**
     * Append the retweet_button
     * 
     * @global object $post Current post
     * @param string $content Post content
     * @return string modifiyed content
     */
    function append_retweet_button($content) {

        global $post;
        $options = get_option('retweet-style');

        $enable_retweet = get_post_meta($post->ID, 'enable_retweet_button', true);

        if ($enable_retweet != "") {
            // if option per post/page is set
            if ($enable_retweet == "1") {
                // Retweet button is enabled

                $content = $this->build_retweet_button($content, $options['position']);

            } elseif ($enable_retweet == "0") {
                // Retweet button is disabled
                // Do nothing
            }

        } else {
            //Option per post/page is not set
            if (is_single()
                || ($options['display-page'] == "1" && is_page())
                || ($options['display-archive'] == "1" && is_archive())
                || ($options['display-home'] == "1" && is_home())) {

                $content = $this->build_retweet_button($content, $options['position']);
            }
        }
        return $content;
    }

    /**
     * Helper function for append_retweet_button
     *
     * @param string $content The post content
     * @param string $position Position of the button
     * @return string Modifiyed content
     */
    function build_retweet_button($content, $position) {
        $button = easy_retweet_button(false);

        switch ($position) {
            case "before":
                $content = $button . $content;
            break;
            case "after":
                $content = $content . $button;
            break;
            case "both":
                $content = $button . $content . $button;
            break;
            case "manual":
            break;
            default:
                $content = $content . $button;
            break;
        }
        return $content;
    }

    /**
     * Short code handler
     * @param <type> $attr
     * @param <type> $content 
     */
    function shortcode_handler($attr, $content) {
        return easy_retweet_button(false);
    }

    // PHP4 compatibility
    function EasyRetweet() {
        $this->__construct();
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'EasyRetweet' ); function EasyRetweet() { global $EasyRetweet; $EasyRetweet = new EasyRetweet(); }

/**
 * Template function to add the retweet button
 */
function easy_retweet_button($display = true) {
    global $wp_query;
    $post = $wp_query->post;
    $permalink = get_permalink($post->ID);
    $custom_retweet_text = get_post_meta($post->ID, 'custom_retweet_text', true);

    if ($custom_retweet_text == '') {
        // if the custom text message is empty default to post title
        $custom_retweet_text = get_the_title($post->ID);
    }

    $enable_retweet = get_post_meta($post->ID, 'enable_retweet_button', true);

    $output = '';
    
    if ($enable_retweet == "" || $enable_retweet == "1") {
        // if option per post/page is set or
        // Retweet button is enabled

        $options = get_option('retweet-style');

        if ($options['button-type'] == 'bit.ly') {
            //Bit.ly Button
            $align = ($options['align'] == "vert")? "vert": "";

            $output = "<a href='$permalink' class='retweet $align' startCount = '0'";

            if ($options['linkattr'] != "") {
                $output .= ' ' . $options['linkattr'] . ' ';
            }

            $output .= ">$custom_retweet_text</a>";
        } else {
            //Twitter button

            $t_count  = $options['t-count'];
            $account1 = $options['account1'];
            $lang = $options['t-language'];
            $style = $options['t-style'];

            if ($lang != '' && $lang != 'en') {
                $lang = "data-lang='$lang'";
            }

            $output = <<<EOD
            <a href="http://twitter.com/share" class="twitter-share-button" data-count="$t_count" data-text="$custom_retweet_text" data-via="$account1" data-url="$permalink" $lang>Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
EOD;

            if ($style != '') {
                $output = "<div style = '$style'>$output</div>";
            }
        }
    }

    if ($display) {
        echo $output;
    } else {
        return $output;
    }
}

/**
 * Print Retweet js
 * @param array $options Plugin options
 */
function print_retweet_js($options) {
?>
/*
 * Easy Retweet Button
 * http://ejohn.org/blog/retweet/
 *   by John Resig (ejohn.org)
 *
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

(function(){

window.RetweetJS = {
	// Your Bit.ly Username
	bitly_user: "<?php echo $options['username'];?>",

	// Your Bit.ly API Key
	// Found here: http://bit.ly/account
	bitly_key: "<?php echo $options['apikey']; ?>",

	// The text to replace the links with
	link_text: (/windows/i.test( navigator.userAgent) ? "&#9658;" : "&#9851;") +
		"&nbsp;<?php echo $options['text'];?>",

	// What # to show (Use "clicks" for # of clicks or "none" for nothing)
	count_type: "clicks",

	// Tweet Prefix text
	// "RT @jeresig " would result in: "RT @jeresig Link Title http://bit.ly/asdf"
	prefix: "<?php echo $options['prefix']; ?> ",

	// Style information
	styling: "a.retweet { font: 12px Helvetica,Arial; color: #000; text-decoration: none; border: 0px; }" +
		"a.retweet span { color: #FFF; background: #94CC3D; margin-left: 2px; border: 1px solid #43A52A; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; padding: 3px; }" +
		"a.vert { display: block; text-align: center; font-size: 16px; float: left; margin: 4px; }" +
		"a.retweet strong.vert { display: block; margin-bottom: 4px; background: #F5F5F5; border: 1px solid #EEE; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; padding: 3px; }" +
		"a.retweet span.vert { display: block; font-size: 12px; margin-left: 0px; }"
};

//////////////// No Need to Configure Below Here ////////////////

var loadCount = 1;

// Asynchronously load the Bit.ly JavaScript API
// If it hasn't been loaded already
if ( typeof BitlyClient === "undefined" ) {
	var head = document.getElementsByTagName("head")[0] ||
		document.documentElement;
	var script = document.createElement("script");
	script.src = "http://bit.ly/javascript-api.js?version=latest&login=" +
		RetweetJS.bitly_user + "&apiKey=" + RetweetJS.bitly_key;
	script.charSet = "utf-8";
	head.appendChild( script );

	var check = setInterval(function(){
		if ( typeof BitlyCB !== "undefined" ) {
			clearInterval( check );
			head.removeChild( script );
			loaded();
		}
	}, 10);

	loadCount = 0;
}

if ( document.addEventListener ) {
	document.addEventListener("DOMContentLoaded", loaded, false);

} else if ( window.attachEvent ) {
	window.attachEvent("onload", loaded);
}

function loaded(){
	// Need to wait for doc ready and js ready
	if ( ++loadCount < 2 ) {
		return;
	}

	var elems = [], urlElem = {}, hashURL = {};

	BitlyCB.shortenResponse = function(data) {
		for ( var url in data.results ) {
			var hash = data.results[url].userHash;
			hashURL[hash] = url;

			var elems = urlElem[ url ];

			for ( var i = 0; i < elems.length; i++ ) {
				elems[i].href += hash;
			}

			if ( RetweetJS.count_type === "clicks" ) {
				BitlyClient.stats(hash, 'BitlyCB.statsResponse');
			}
		}
	};

	BitlyCB.statsResponse = function(data) {
		var clicks = data.results.clicks, hash = data.results.userHash;
		var url = hashURL[ hash ], elems = urlElem[ url ];

		if ( clicks > 0 ) {
			for ( var i = 0; i < elems.length; i++ ) {
				var strong = document.createElement("strong");
				strong.appendChild( document.createTextNode( clicks + parseInt(elems[i].attributes.startCount.value) + " " ) );
				elems[i].insertBefore(strong, elems[i].firstChild);

				if ( /(^|\s)vert(\s|$)/.test( elems[i].className ) ) {
					elems[i].firstChild.className = elems[i].lastChild.className = "vert";
				}
			}
		}

		hashURL[ hash ] = urlElem[ url ] = null;
	};

	if ( document.getElementsByClassName ) {
		elems = document.getElementsByClassName("retweet");
	} else {
		var tmp = document.getElementsByTagName("a");
		for ( var i = 0; i < tmp.length; i++ ) {
			if ( /(^|\s)retweet(\s|$)/.test( tmp[i].className ) ) {
				elems.push( tmp[i] );
			}
		}
	}

	if ( elems.length && RetweetJS.styling ) {
		var style = document.createElement("style");
		style.type = "text/css";

		try {
			style.appendChild( document.createTextNode( RetweetJS.styling ) );
		} catch (e) {
			if ( style.styleSheet ) {
				style.styleSheet.cssText = RetweetJS.styling;
			}
		}

		document.body.appendChild( style );
	}

	for ( var i = 0; i < elems.length; i++ ) {
		var elem = elems[i];

		if ( /(^|\s)self(\s|$)/.test( elem.className ) ) {
			elem.href = window.location;
			elem.title = document.title;
		}

		var origText = elem.title || elem.textContent || elem.innerText,
			href = elem.href;

		elem.innerHTML = "<span>" + RetweetJS.link_text + "</span>";
		elem.title = "";
		elem.href = "http://twitter.com/home?status=" +
			encodeURIComponent(RetweetJS.prefix + origText + " http://bit.ly/");

		if ( urlElem[ href ] ) {
			urlElem[ href ].push( elem );
		} else {
			urlElem[ href ] = [ elem ];
			BitlyClient.call('shorten', {'longUrl':href, 'history':'1'}, 'BitlyCB.shortenResponse');
		}
	}

}

})();
<?php
}
?>
