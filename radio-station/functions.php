<?php
/**
 * Radio Station theme.
 *
 * Shows and DJs are registered by the KITE Station companion plugin.
 * If that plugin is not active, the theme loads the copy shipped in companion/.
 *
 * @package Radio_Station
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('radio_station_register_post_types')) {
    $kite_station_plugin = WP_PLUGIN_DIR . '/kite-station/kite-station.php';

    if (is_readable($kite_station_plugin)) {
        require_once $kite_station_plugin;
    } else {
        require_once get_template_directory() . '/companion/kite-station/kite-station.php';
    }
}

/**
 * Warn in wp-admin when Shows and DJs are being loaded from the theme copy.
 */
function radio_station_plugin_admin_notice() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (is_plugin_active('kite-station/kite-station.php')) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('KITE Station is running from the theme folder. Install and activate the KITE Station plugin so shows and DJs remain if you switch themes. Zip the theme’s companion/kite-station folder and upload it under Plugins.', 'radio-station');
    echo '</p></div>';
}
add_action('admin_notices', 'radio_station_plugin_admin_notice');

require_once get_template_directory() . '/inc/station.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/setup.php';

/**
 * Theme supports, menus, and image sizes.
 *
 * Show and DJ pages render their own details, so the plugin's content
 * appenders are removed while this theme is active.
 */
function radio_station_setup() {
    load_theme_textdomain('radio-station', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 80,
            'width'       => 80,
            'flex-height' => true,
            'flex-width'  => true,
        )
    );

    register_nav_menus(
        array(
            'primary' => __('Primary', 'radio-station'),
        )
    );

    add_image_size('radio-card', 800, 600, true);
    add_image_size('radio-portrait', 800, 1067, true);
    add_image_size('radio-hero', 1600, 1000, true);

    remove_filter('the_content', 'radio_station_display_show_details');
    remove_filter('the_content', 'radio_station_display_dj_shows');
    remove_filter('the_content', 'radio_station_display_show_categories');
}
add_action('after_setup_theme', 'radio_station_setup');

/**
 * Front-end assets.
 */
function radio_station_assets() {
    wp_enqueue_style(
        'radio-station-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Figtree:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'radio-station-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array('radio-station-fonts'),
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'radio-station-theme',
        get_template_directory_uri() . '/assets/js/theme.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'radio_station_assets');

/**
 * Preconnect to the font host.
 *
 * @param array  $urls          Resource hints.
 * @param string $relation_type Hint type.
 * @return array
 */
function radio_station_resource_hints($urls, $relation_type) {
    if ($relation_type === 'preconnect') {
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
        $urls[] = 'https://fonts.googleapis.com';
    }

    return $urls;
}
add_filter('wp_resource_hints', 'radio_station_resource_hints', 10, 2);
