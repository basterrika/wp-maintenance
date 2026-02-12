<?php
/**
 * Plugin Name: Maintenance
 * Description: Simple WordPress maintenance mode plugin.
 * Version: 1.0.0
 * Author: Mikel
 * Author URI: https://basterrika.com
 * Update URI: https://github.com/basterrika/wp-maintenance
 * Text Domain: wp-maintenance
 * Requires PHP: 8.4
 * Requires at least: 6.5
 * Tested up to: 6.9.1
 */

defined('ABSPATH') || exit;

define('WP_MAINTENANCE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_MAINTENANCE_PLUGIN_PATH', plugin_dir_path(__FILE__));

if (is_admin()) {
    require_once WP_MAINTENANCE_PLUGIN_PATH . 'settings.php';
}

add_action('template_redirect', 'wpm_load_maintenance_template');
function wpm_load_maintenance_template(): void {
    if (is_user_logged_in()) {
        return;
    }

    $maintenance_settings = get_option('maintenance_settings', []);
    if (!is_array($maintenance_settings)) {
        $maintenance_settings = [];
    }

    $maintenance_enabled = !empty($maintenance_settings['enabled']);
    if (!$maintenance_enabled) {
        return;
    }

    require_once WP_MAINTENANCE_PLUGIN_PATH . 'template.php';
    exit;
}
