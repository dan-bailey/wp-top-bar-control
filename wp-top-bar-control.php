<?php
/**
 * Plugin Name: WP Top Bar Control
 * Plugin URI: https://github.com/dan-bailey/wp-top-bar-control
 * Description: Customize WordPress admin bar colors and add theme-color meta tag
 * Version: 1.0.0
 * Author: Dan Bailey
 * Author URI: https://danbailey.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-top-bar-control
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Top_Bar_Control {

    private $option_name = 'wtbc_settings';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_head', array($this, 'add_theme_color_meta'));
        add_action('admin_head', array($this, 'add_admin_bar_styles'));
        add_action('wp_head', array($this, 'add_admin_bar_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_color_picker'));
    }

    /**
     * Add settings page to WordPress admin menu
     */
    public function add_settings_page() {
        add_options_page(
            'WP Top Bar Control',
            'Top Bar Control',
            'manage_options',
            'wp-top-bar-control',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting($this->option_name, $this->option_name, array($this, 'sanitize_settings'));
    }

    /**
     * Sanitize settings input
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        if (isset($input['background_color'])) {
            $sanitized['background_color'] = sanitize_hex_color($input['background_color']);
        }

        if (isset($input['foreground_color'])) {
            $sanitized['foreground_color'] = sanitize_hex_color($input['foreground_color']);
        }

        return $sanitized;
    }

    /**
     * Enqueue color picker scripts
     */
    public function enqueue_color_picker($hook) {
        if ($hook !== 'settings_page_wp-top-bar-control') {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                $(".wtbc-color-picker").wpColorPicker();
            });
        ');
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $options = get_option($this->option_name, array(
            'background_color' => '#23282d',
            'foreground_color' => '#ffffff'
        ));

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields($this->option_name);
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="background_color">Background Color</label>
                        </th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr($this->option_name); ?>[background_color]"
                                   id="background_color"
                                   class="wtbc-color-picker"
                                   value="<?php echo esc_attr($options['background_color']); ?>" />
                            <p class="description">This color will be applied to the admin bar background and theme-color meta tag.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="foreground_color">Foreground Color</label>
                        </th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr($this->option_name); ?>[foreground_color]"
                                   id="foreground_color"
                                   class="wtbc-color-picker"
                                   value="<?php echo esc_attr($options['foreground_color']); ?>" />
                            <p class="description">This color will be applied to the admin bar text.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Colors'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Add theme-color meta tag to site head
     */
    public function add_theme_color_meta() {
        $options = get_option($this->option_name, array(
            'background_color' => '#23282d'
        ));

        if (!empty($options['background_color'])) {
            echo '<meta name="theme-color" content="' . esc_attr($options['background_color']) . '">' . "\n";
        }
    }

    /**
     * Add custom styles for admin bar
     */
    public function add_admin_bar_styles() {
        if (!is_admin_bar_showing()) {
            return;
        }

        $options = get_option($this->option_name, array(
            'background_color' => '#23282d',
            'foreground_color' => '#ffffff'
        ));

        ?>
        <style type="text/css">
            #wpadminbar {
                background-color: <?php echo esc_attr($options['background_color']); ?> !important;
                background-image: none !important;
            }

            #wpadminbar .ab-item,
            #wpadminbar a.ab-item,
            #wpadminbar > #wp-toolbar span.ab-label,
            #wpadminbar > #wp-toolbar span.noticon,
            #wpadminbar .ab-icon:before,
            #wpadminbar .ab-label,
            #wpadminbar input[type="text"],
            #wpadminbar input[type="search"] {
                color: <?php echo esc_attr($options['foreground_color']); ?> !important;
            }

            #wpadminbar .menupop .ab-sub-wrapper,
            #wpadminbar .shortlink-input {
                background-color: <?php echo esc_attr($options['background_color']); ?> !important;
            }

            #wpadminbar .ab-submenu .ab-item {
                color: <?php echo esc_attr($options['foreground_color']); ?> !important;
            }
        </style>
        <?php
    }
}

// Initialize the plugin
new WP_Top_Bar_Control();
