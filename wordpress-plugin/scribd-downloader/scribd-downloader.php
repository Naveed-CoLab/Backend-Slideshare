<?php
/**
 * Plugin Name: Scribd Downloader Widget
 * Plugin URI: https://slidesdownloader.pro
 * Description: Display the Scribd Downloader widget on any WordPress page or post using shortcode [scribd-downloader].
 * Version: 1.0.0
 * Author: SlideShare Downloader Team
 * License: GPL-2.0+
 * Text Domain: scribd-downloader
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('SCRIBD_DL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCRIBD_DL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCRIBD_DL_RAILWAY_DEFAULT_URL', 'https://backend-slideshare-production-96f2.up.railway.app');
define('SCRIBD_DL_RAILWAY_DEFAULT_KEY', 'sdl-3f8b9c2e7a1d4f6b8e0c5a9d2f7b1e4c6a8d0f3b9e2c7a1d');

// Register admin settings
add_action('admin_menu', 'scribd_dl_add_admin_menu');
add_action('admin_init', 'scribd_dl_settings_init');

function scribd_dl_add_admin_menu() {
    add_options_page(
        'Scribd Downloader Settings',
        'Scribd Downloader',
        'manage_options',
        'scribd-downloader',
        'scribd_dl_options_page'
    );
}

function scribd_dl_settings_init() {
    register_setting('scribd_dl_plugin_page', 'scribd_dl_api_url');
    register_setting('scribd_dl_plugin_page', 'scribd_dl_api_key');
    register_setting('scribd_dl_plugin_page', 'scribd_dl_turnstile_enabled');
    register_setting('scribd_dl_plugin_page', 'scribd_dl_turnstile_site_key');
    register_setting('scribd_dl_plugin_page', 'scribd_dl_use_proxy');

    add_settings_section(
        'scribd_dl_plugin_page_section',
        __('Scribd Downloader Backend Settings', 'scribd-downloader'),
        'scribd_dl_settings_section_callback',
        'scribd_dl_plugin_page'
    );

    add_settings_field(
        'scribd_dl_api_url',
        __('Railway Backend URL', 'scribd-downloader'),
        'scribd_dl_api_url_render',
        'scribd_dl_plugin_page',
        'scribd_dl_plugin_page_section'
    );

    add_settings_field(
        'scribd_dl_api_key',
        __('Railway Backend API Key', 'scribd-downloader'),
        'scribd_dl_api_key_render',
        'scribd_dl_plugin_page',
        'scribd_dl_plugin_page_section'
    );

    add_settings_field(
        'scribd_dl_use_proxy',
        __('Proxy Requests via WordPress', 'scribd-downloader'),
        'scribd_dl_use_proxy_render',
        'scribd_dl_plugin_page',
        'scribd_dl_plugin_page_section'
    );

    add_settings_field(
        'scribd_dl_turnstile_enabled',
        __('Enable Cloudflare Turnstile CAPTCHA', 'scribd-downloader'),
        'scribd_dl_turnstile_enabled_render',
        'scribd_dl_plugin_page',
        'scribd_dl_plugin_page_section'
    );

    add_settings_field(
        'scribd_dl_turnstile_site_key',
        __('Cloudflare Turnstile Site Key', 'scribd-downloader'),
        'scribd_dl_turnstile_site_key_render',
        'scribd_dl_plugin_page',
        'scribd_dl_plugin_page_section'
    );
}

function scribd_dl_settings_section_callback() {
    echo __('Configure your Railway backend microservice URL and API keys.', 'scribd-downloader');
}

function scribd_dl_api_url_render() {
    $val = get_option('scribd_dl_api_url', SCRIBD_DL_RAILWAY_DEFAULT_URL);
    echo '<input type="url" name="scribd_dl_api_url" value="' . esc_attr($val) . '" style="width:80%; max-width:600px;" placeholder="' . SCRIBD_DL_RAILWAY_DEFAULT_URL . '">';
    echo '<p class="description">Your Railway backend URL (e.g. <code>https://backend-slideshare-production-96f2.up.railway.app</code>).</p>';
}

function scribd_dl_api_key_render() {
    $val = get_option('scribd_dl_api_key', SCRIBD_DL_RAILWAY_DEFAULT_KEY);
    echo '<input type="text" name="scribd_dl_api_key" value="' . esc_attr($val) . '" style="width:80%; max-width:600px;">';
    echo '<p class="description">Backend API key used for X-API-Key header authentication.</p>';
}

function scribd_dl_use_proxy_render() {
    $val = get_option('scribd_dl_use_proxy', '1');
    echo '<label><input type="checkbox" name="scribd_dl_use_proxy" value="1" ' . checked(1, $val, false) . '> Route requests through WordPress (Recommended - prevents CORS cross-domain issues)</label>';
}

function scribd_dl_turnstile_enabled_render() {
    $val = get_option('scribd_dl_turnstile_enabled', '0');
    echo '<label><input type="checkbox" name="scribd_dl_turnstile_enabled" value="1" ' . checked(1, $val, false) . '> Enable Turnstile CAPTCHA badge and verification</label>';
}

function scribd_dl_turnstile_site_key_render() {
    $val = get_option('scribd_dl_turnstile_site_key', '');
    echo '<input type="text" name="scribd_dl_turnstile_site_key" value="' . esc_attr($val) . '" style="width:80%; max-width:600px;" placeholder="Optional Cloudflare Turnstile Site Key">';
    echo '<p class="description">Leave blank if Turnstile CAPTCHA is not enabled.</p>';
}

function scribd_dl_options_page() {
    ?>
    <div class="wrap">
        <h2>Scribd Downloader Widget Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('scribd_dl_plugin_page');
            do_settings_sections('scribd_dl_plugin_page');
            submit_button();
            ?>
        </form>
        <hr>
        <h3>Usage Instructions</h3>
        <p>To display the Scribd Downloader widget anywhere on your site, add this shortcode to any Page, Post, or Block:</p>
        <code style="font-size:16px; padding: 6px 12px; background:#f0f0f0;">[scribd-downloader]</code>
    </div>
    <?php
}

// Enqueue styles and scripts
function scribd_dl_enqueue_assets() {
    wp_register_style('scribd-downloader-css', SCRIBD_DL_PLUGIN_URL . 'assets/css/scribd-downloader.css', array(), '1.0.0');
    wp_register_script('scribd-downloader-js', SCRIBD_DL_PLUGIN_URL . 'assets/js/scribd-downloader.js', array(), '1.0.0', true);

    $turnstile_enabled = get_option('scribd_dl_turnstile_enabled', '0');
    $turnstile_site_key = get_option('scribd_dl_turnstile_site_key', '');

    if ($turnstile_enabled == '1' && !empty($turnstile_site_key)) {
        wp_register_script('cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'scribd_dl_enqueue_assets');

// Shortcode handler: [scribd-downloader]
function scribd_dl_shortcode($atts) {
    wp_enqueue_style('scribd-downloader-css');
    wp_enqueue_script('scribd-downloader-js');

    $turnstile_enabled = get_option('scribd_dl_turnstile_enabled', '0');
    $turnstile_site_key = get_option('scribd_dl_turnstile_site_key', '');
    $api_url = get_option('scribd_dl_api_url', SCRIBD_DL_RAILWAY_DEFAULT_URL);
    $use_proxy = get_option('scribd_dl_use_proxy', '1');

    if ($turnstile_enabled == '1' && !empty($turnstile_site_key)) {
        wp_enqueue_script('cloudflare-turnstile');
    }

    $proxy_url = ($use_proxy == '1') ? admin_url('admin-ajax.php?action=scribd_downloader_proxy') : '';

    ob_start();
    ?>
    <div class="scribd-downloader-widget-container">
        <form class="scribd-downloader-form" data-api-url="<?php echo esc_url($api_url); ?>" data-proxy-url="<?php echo esc_url($proxy_url); ?>">
            <div class="scribd-downloader-input-wrap">
                <input class="scribd-downloader-input" name="scribd_url" type="url" placeholder="https://www.scribd.com/document/123456789/Title" required>
            </div>

            <div class="scribd-downloader-badges">
                <span class="scribd-downloader-badge">PDF</span>
                <span class="scribd-downloader-badge">Progress updates</span>
                <span class="scribd-downloader-badge">No Registration</span>
                <?php if ($turnstile_enabled == '1' && !empty($turnstile_site_key)): ?>
                    <span class="scribd-downloader-badge">Captcha Protected</span>
                <?php endif; ?>
            </div>

            <?php if ($turnstile_enabled == '1' && !empty($turnstile_site_key)): ?>
                <div class="scribd-downloader-turnstile">
                    <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($turnstile_site_key); ?>" data-callback="onScribdWidgetTurnstileSuccess" data-expired-callback="onScribdWidgetTurnstileExpired" data-error-callback="onScribdWidgetTurnstileExpired"></div>
                </div>
            <?php endif; ?>

            <button class="scribd-downloader-submit-btn" type="submit" <?php echo ($turnstile_enabled == '1' && !empty($turnstile_site_key)) ? 'disabled' : ''; ?>>
                Start download
            </button>
        </form>

        <div class="scribd-downloader-result"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('scribd-downloader', 'scribd_dl_shortcode');

// WordPress Proxy AJAX Handlers
add_action('wp_ajax_scribd_downloader_proxy', 'scribd_dl_handle_proxy');
add_action('wp_ajax_nopriv_scribd_downloader_proxy', 'scribd_dl_handle_proxy');

function scribd_dl_handle_proxy() {
    $api_url = rtrim(get_option('scribd_dl_api_url', SCRIBD_DL_RAILWAY_DEFAULT_URL), '/');
    $api_key = get_option('scribd_dl_api_key', SCRIBD_DL_RAILWAY_DEFAULT_KEY);
    $action = isset($_REQUEST['scribd_action']) ? sanitize_text_field($_REQUEST['scribd_action']) : '';

    $is_railway_backend = (strpos($api_url, 'scribd_api.php') === false);

    $headers = array('Content-Type' => 'application/json');
    if (!empty($api_key)) {
        $headers['X-API-Key'] = $api_key;
    }

    // 1. Download file action
    if (isset($_GET['action']) && $_GET['action'] === 'download') {
        $job_id = isset($_GET['job_id']) ? sanitize_text_field($_GET['job_id']) : '';
        if (empty($job_id)) {
            status_header(400);
            echo 'Invalid Job ID';
            wp_die();
        }

        $target = $is_railway_backend ? ($api_url . '/api/jobs/' . rawurlencode($job_id) . '/file') : ($api_url . '?action=download&job_id=' . urlencode($job_id));
        
        $response = wp_remote_get($target, array(
            'timeout' => 600,
            'headers' => $headers
        ));
        
        if (is_wp_error($response)) {
            status_header(500);
            echo 'Download failed: ' . esc_html($response->get_error_message());
            wp_die();
        }

        $resp_headers = wp_remote_retrieve_headers($response);
        $body = wp_remote_retrieve_body($response);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="scribd-document-' . sanitize_file_name($job_id) . '.pdf"');
        header('Content-Length: ' . strlen($body));
        header('Cache-Control: no-store');

        echo $body;
        wp_die();
    }

    // 2. Status poll action
    if ($action === 'status') {
        $job_id = isset($_GET['job_id']) ? sanitize_text_field($_GET['job_id']) : '';
        if (empty($job_id)) {
            wp_send_json_error(array('error' => 'Invalid Job ID'));
            wp_die();
        }

        $target = $is_railway_backend ? ($api_url . '/api/jobs/' . rawurlencode($job_id)) : ($api_url . '?action=status&job_id=' . urlencode($job_id));
        $response = wp_remote_get($target, array(
            'timeout' => 30,
            'headers' => $headers
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('error' => $response->get_error_message()));
        } else {
            $body = wp_remote_retrieve_body($response);
            header('Content-Type: application/json; charset=utf-8');
            echo $body;
        }
        wp_die();
    }

    // 3. Submit URL action
    if ($action === 'submit') {
        $scribd_url = isset($_POST['scribd_url']) ? esc_url_raw($_POST['scribd_url']) : '';
        if (empty($scribd_url) || strpos(strtolower($scribd_url), 'scribd.com') === false) {
            wp_send_json_error(array('error' => 'Please provide a valid Scribd document URL.'));
            wp_die();
        }

        if ($is_railway_backend) {
            $target = $api_url . '/api/jobs';
            $payload = wp_json_encode(array('url' => $scribd_url));

            $response = wp_remote_post($target, array(
                'timeout' => 120,
                'headers' => $headers,
                'body' => $payload
            ));
        } else {
            $target = $api_url;
            $body_args = array(
                'scribd_url' => $scribd_url,
                'action' => 'submit'
            );
            if (isset($_POST['cf-turnstile-response'])) {
                $body_args['cf-turnstile-response'] = sanitize_text_field($_POST['cf-turnstile-response']);
            }
            $response = wp_remote_post($target, array(
                'timeout' => 120,
                'body' => $body_args
            ));
        }

        if (is_wp_error($response)) {
            wp_send_json_error(array('error' => $response->get_error_message()));
        } else {
            $body = wp_remote_retrieve_body($response);
            header('Content-Type: application/json; charset=utf-8');
            echo $body;
        }
        wp_die();
    }

    wp_send_json_error(array('error' => 'Invalid action'));
    wp_die();
}
