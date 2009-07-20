<?php
/**
Plugin Name: Easy Retweet
Plugin URI: http://sudarmuthu.com/wordpress/easy-retweet
Description: Adds a Retweet button to your WordPress posts.
Author: Sudar
Version: 0.1
Author URI: http://sudarmuthu.com/

Uses the script created by John Resig http://ejohn.org/blog/retweet/
*/

class EasyRetweet {

    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        load_plugin_textdomain( 'easy-retweet', false, '/easy-retweet/languages' );

        // Register hooks
        add_action( 'admin_menu', array(&$this, 'register_settings_page') );
        add_action( 'admin_init', array(&$this, 'add_settings') );

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(&$this, 'add_action_links'));

        // Register filters
        add_filter('the_content', array(&$this, 'append_retweet_button') , 999);
        // Enqueue the script
        wp_enqueue_script("retweet", '/' . PLUGINDIR . '/easy-retweet/js/retweet.js');
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
     * hook to add action links
     * @param <type> $links
     * @return <type>
     */
    function add_action_links( $links ) {
        // Add a link to this plugin's settings page
        $settings_link = '<a href="options-general.php?page=easy-retweet">' . __("Settings") . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
     */
    function add_footer_links() {
        $plugin_data = get_plugin_data( __FILE__ );
        printf('%1$s ' . __("plugin") .' | ' . __("Version") . ' %2$s | '. __('by') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
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
                <?php $options['align'] = ($options['align'] == "")? "hori":$options['align'];?>
                <?php $options['position'] = ($options['position'] == "")? "after":$options['position'];?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Position', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[position]" value="before" <?php checked("before", $options['position']); ?> /> <?php _e("Before the content of your post");?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="after" <?php checked("after", $options['position']); ?> /> <?php _e("After the content of your post");?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="both" <?php checked("both", $options['position']); ?> /> <?php _e("Before AND After the content of your post");?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="manual" <?php checked("manual", $options['position']); ?> /> <?php _e("Manually call the retweet button");?></label></p>
                            <p><?php _e("You can manually call the <code>easy_retweet_button</code> function. E.g. <code>if (function_exists('easy_retweet_button')) echo easy_retweet_button();."); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Type', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[align]" value="hori" <?php checked("hori", $options['align']); ?> /> <?php _e("Horizontal button");?></label></p>
                            <p><label><input type="radio" name="retweet-style[align]" value="vert" <?php checked("vert", $options['align']); ?> /> <?php _e("Vertical button");?></label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Display', 'easy-retweet' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="retweet-style[display-page]" value="1" <?php checked("1", $options['display-page']); ?> /> <?php _e("Display the button on pages");?></label>
                        </td>
                    </tr>
<!--
                    <tr>
                        <th>&nbsp;</th>
                        <td>
                            <label><input type="radio" name="retweet-style[display-page]" value="1" <?php checked("1", $options['display-page']); ?> /> <?php _e("Display the button on your feed");?></label>
                        </td>
                    </tr>
-->
                </table>

                <p class="submit">
                    <input type="submit" name="easy-retweet-submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
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

        if (is_single() || ($options['display-page'] == "1" && is_page())) {
            switch ($options['position']) {
                case "before":
                    $content = easy_retweet_button(false) . $content;
                break;
                case "after":
                    $content = $content . easy_retweet_button(false);
                break;
                case "both":
                    $content = easy_retweet_button(false) . $content . easy_retweet_button(false);
                break;
                case "manual":
                default:
                    // nothing to do
                break;
            }
        }
        return $content;
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
    $align = ($option['align'] == "vert")? "vert": "";

    $output = "<a href='$permalink' class='retweet $align'>$title</a>";
    
    if ($display) {
        echo $output;
    } else {
        return $output;
    }
}
?>
