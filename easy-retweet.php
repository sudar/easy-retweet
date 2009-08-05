<?php
/**
Plugin Name: Easy Retweet
Plugin URI: http://sudarmuthu.com/wordpress/easy-retweet
Description: Adds a Retweet button to your WordPress posts.
Author: Sudar
Version: 1.1.0
Author URI: http://sudarmuthu.com/
Text Domain: easy-retweet

=== RELEASE NOTES ===
2009-07-13 – v0.1 – Initial Release
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

Uses the script created by John Resig http://ejohn.org/blog/retweet/
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

        // Enqueue the script
        add_action('template_redirect', array(&$this, 'add_script'));

        // Register filters
        add_filter('the_content', array(&$this, 'append_retweet_button') , 99);

        // register short code
        add_shortcode('easy-retweet', array(&$this, 'shortcode_handler'));

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(&$this, 'add_action_links'));
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
        // Enqueue the script
        wp_enqueue_script("retweet", '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/js/retweet.js.php');
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
                        <th scope="row"><?php _e( 'Type', 'easy-retweet' ); ?></th>
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

                <p class="submit">
                    <input type="submit" name="easy-retweet-submit" class="button-primary" value="<?php _e('Save Changes', 'easy-retweet') ?>" />
                </p>
            </form>
        </div>
<?php
        // Display credits in Footer
        add_action( 'in_admin_footer', array(&$this, 'add_footer_links'));
    }

    /**
     * append the retweet_button
     */
    function append_retweet_button($content) {
        $options = get_option('retweet-style');

        if (is_single()
            || ($options['display-page'] == "1" && is_page())
            || ($options['display-archive'] == "1" && is_archive())
            || ($options['display-home'] == "1" && is_home())) {

            $button = easy_retweet_button(false);
            switch ($options['position']) {
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
                default:
                    // nothing to do
                break;
            }
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
    $title = get_the_title($post->ID);

    $options = get_option('retweet-style');
    $align = ($options['align'] == "vert")? "vert": "";

    $output = "<a href='$permalink' class='retweet $align' ";

    if ($options['linkattr'] != "") {
        $output .= ' ' . $options['linkattr'] . ' ';
    }

    $output .= ">$title</a>";
    
    if ($display) {
        echo $output;
    } else {
        return $output;
    }
}
?>