<?php

defined('ABSPATH') || exit;

status_header(503);
header('X-Robots-Tag: noindex, nofollow', true);
nocache_headers();
send_nosniff_header();

$maintenance_settings = (isset($maintenance_settings) && is_array($maintenance_settings)) ? $maintenance_settings : get_option('maintenance_settings', []);

if (!is_array($maintenance_settings)) {
    $maintenance_settings = [];
}

$meta_title = sanitize_text_field((string)($maintenance_settings['meta_title'] ?? ''));

if ($meta_title === '') {
    $meta_title = __('UNDER CONSTRUCTION', 'wp-maintenance');
}

$meta_description = sanitize_text_field((string)($maintenance_settings['meta_description'] ?? ''));
$html = wp_kses_post((string)($maintenance_settings['html'] ?? ''));

if (trim($html) === '') {
    $html = '<main class="wpm-container"><h1>' . esc_html__('UNDER CONSTRUCTION', 'wp-maintenance') . '</h1><p>' . esc_html__('Please check back later', 'wp-maintenance') . '</p></main>';
}

$css = wp_strip_all_tags(trim((string)($maintenance_settings['css'] ?? '')), false);
$css = str_ireplace('</style', '<\/style', $css);

$js = wp_strip_all_tags(trim((string)($maintenance_settings['js'] ?? '')), false);
$js = str_ireplace('</script', '<\/script', $js);

$google_analytics_id = sanitize_text_field((string)($maintenance_settings['google_analytics_id'] ?? ''));
$google_analytics_enabled = $google_analytics_id !== '';

$charset = get_bloginfo('charset');

if (!is_string($charset) || $charset === '') {
    $charset = 'UTF-8';
}

?>

<!doctype html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php echo esc_attr($charset); ?>">
        <title><?php echo esc_html($meta_title); ?></title>
        <?php

        if (!empty($meta_description)) {
            ?>

            <meta name="description" content="<?php echo esc_attr($meta_description); ?>">

            <?php
        }

        // Display site favicon meta tags.
        wp_site_icon();

        if (!empty($css)) {
            ?>

            <style><?php echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>

            <?php
        }

        if ($google_analytics_enabled) {
            ?>

            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($google_analytics_id); ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());
                gtag('config', '<?php echo esc_js($google_analytics_id); ?>');
            </script>

            <?php
        }

        ?>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noarchive">
    </head>
    <body>
        <?php

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if (!empty($js)) {
            ?>

            <script><?php echo $js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>

            <?php
        }

        ?>
    </body>
</html>
