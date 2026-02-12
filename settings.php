<?php

defined('ABSPATH') || exit;

add_action('admin_menu', 'wpm_register_settings_page');
function wpm_register_settings_page(): void {
    add_menu_page(__('Maintenance', 'wp-maintenance'), __('Maintenance', 'wp-maintenance'), 'manage_options', 'maintenance', 'wpm_main_settings_page');
}

function wpm_main_settings_page(): void {
    $settings_updated = false;
    $maintenance_settings = get_option('maintenance_settings', []);

    if (!is_array($maintenance_settings)) {
        $maintenance_settings = [];
    }

    if (
        'POST' === ($_SERVER['REQUEST_METHOD'] ?? '') &&
        isset($_POST['maintenance_settings'], $_POST['wp_maintenance_nonce']) &&
        current_user_can('manage_options')
    ) {
        check_admin_referer('wp_maintenance_settings', 'wp_maintenance_nonce');

        $posted_settings = wp_unslash($_POST['maintenance_settings']);

        if (!is_array($posted_settings)) {
            $posted_settings = [];
        }

        $maintenance_settings = [
            'enabled' => !empty($posted_settings['enabled']) ? 1 : 0,
            'meta_title' => sanitize_text_field($posted_settings['meta_title'] ?? ''),
            'meta_description' => sanitize_text_field($posted_settings['meta_description'] ?? ''),
            'html' => wp_kses_post($posted_settings['html'] ?? ''),
            'css' => wp_strip_all_tags($posted_settings['css'] ?? ''),
            'js' => wp_strip_all_tags($posted_settings['js'] ?? ''),
            'google_analytics_id' => sanitize_text_field($posted_settings['google_analytics_id'] ?? ''),
        ];

        $settings_updated = update_option('maintenance_settings', $maintenance_settings);
    }

    $maintenance_enabled = !empty($maintenance_settings['enabled']);
    $blog_name = get_option('blogname');
    $blog_description = get_option('blogdescription');
    $meta_title = $maintenance_settings['meta_title'] ?? $blog_name;
    $meta_description = $maintenance_settings['meta_description'] ?? $blog_description;
    $saved_html = $maintenance_settings['html'] ?? null;
    $saved_css = $maintenance_settings['css'] ?? null;
    $saved_js = $maintenance_settings['js'] ?? '';
    $google_analytics_id = $maintenance_settings['google_analytics_id'] ?? '';

    $html = '
<div class="container">
    <h1>' . esc_html__('UNDER CONSTRUCTION', 'wp-maintenance') . '</h1>
    <p>' . esc_html__('Please check back later', 'wp-maintenance') . '</p>
</div>';

    $css = '
body {
    display: table;
    width: 100%;
    overflow: hidden;
}

.container {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    height: 100vh;
    width: 100%;
}';

    if ($settings_updated) {
        ?>

        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully.', 'wp-maintenance'); ?></p>
        </div>

        <?php
    }

    ?>

    <div class="wrap">
        <h2><?php esc_html_e('Maintenance mode', 'wp-maintenance') ?></h2>
        <form method="post">
            <?php wp_nonce_field('wp_maintenance_settings', 'wp_maintenance_nonce'); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="maintenance_enabled"><?php esc_html_e('Enable maintenance mode', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="maintenance_enabled" name="maintenance_settings[enabled]" value="1" <?php checked($maintenance_enabled); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="maintenance_title"><?php esc_html_e('Window tab text / Meta title', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <input type="text" id="maintenance_title" name="maintenance_settings[meta_title]" value="<?php echo esc_attr($meta_title); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="maintenance_description"><?php esc_html_e('Meta description', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <input type="text" id="maintenance_description" name="maintenance_settings[meta_description]" value="<?php echo esc_attr($meta_description); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="maintenance_html"><?php esc_html_e('HTML', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <textarea id="maintenance_html" name="maintenance_settings[html]"><?php echo esc_textarea(null !== $saved_html ? $saved_html : $html) ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="maintenance_css"><?php esc_html_e('CSS', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <textarea id="maintenance_css" name="maintenance_settings[css]"><?php echo esc_textarea(null !== $saved_css ? $saved_css : $css) ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="maintenance_js"><?php esc_html_e('JavaScript', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <textarea id="maintenance_js" name="maintenance_settings[js]"><?php echo esc_textarea($saved_js) ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="google_analytics_id"><?php esc_html_e('Google Analytics', 'wp-maintenance') ?></label>
                        </th>
                        <td>
                            <input type="text" id="google_analytics_id" name="maintenance_settings[google_analytics_id]" value="<?php echo esc_attr($google_analytics_id); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save', 'wp-maintenance') ?>">
            </p>
        </form>
    </div>

    <?php
}

add_action('admin_enqueue_scripts', 'wpm_maintenance_admin_enqueues');
function wpm_maintenance_admin_enqueues(string $hook): void {
    if ($hook !== 'toplevel_page_maintenance') {
        return;
    }

    $admin_object = [
        'htmlCodeEditor' => wp_enqueue_code_editor([
            'type' => 'text/html',
        ]),
        'cssCodeEditor' => wp_enqueue_code_editor([
            'type' => 'text/css',
        ]),
        'jsCodeEditor' => wp_enqueue_code_editor([
            'type' => 'javascript',
        ]),
    ];

    wp_enqueue_script('maintenance-admin', WP_MAINTENANCE_PLUGIN_URL . 'settings.js', ['wp-theme-plugin-editor'], '1.0.0', true);
    wp_localize_script('maintenance-admin', 'wp_maintenance', $admin_object);
}

add_action('admin_notices', 'wpm_maintenance_enabled_notice');
function wpm_maintenance_enabled_notice(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (
        'POST' === ($_SERVER['REQUEST_METHOD'] ?? '') &&
        isset($_POST['maintenance_settings'], $_POST['wp_maintenance_nonce']) &&
        wp_verify_nonce($_POST['wp_maintenance_nonce'], 'wp_maintenance_settings')
    ) {
        $posted_settings = wp_unslash($_POST['maintenance_settings']);
        if (!is_array($posted_settings)) {
            $posted_settings = [];
        }

        $maintenance_enabled = !empty($posted_settings['enabled']);
    }
    else {
        $maintenance_settings = get_option('maintenance_settings', []);
        if (!is_array($maintenance_settings)) {
            $maintenance_settings = [];
        }

        $maintenance_enabled = !empty($maintenance_settings['enabled']);
    }

    if (!$maintenance_enabled) {
        return;
    }

    ?>

    <div class="notice notice-warning">
        <p>
            <strong><?php esc_html_e('Maintenance mode is enabled.', 'wp-maintenance') ?></strong>
            <?php

            printf(
                /* translators: %s: URL to maintenance settings page */
                __('Remember to <a href="%s">deactivate</a> it once finished.', 'wp-maintenance'),
                esc_url(admin_url('admin.php?page=maintenance'))
            );

            ?>
        </p>
    </div>

    <?php
}
