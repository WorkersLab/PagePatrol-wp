<?php

class PagePatrol_Admin {
    private $api_client;

    public function __construct($api_client = null) {
        $this->api_client = $api_client;
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_pagepatrol_refresh_status', [$this, 'ajax_refresh_status']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'PagePatrol',
            'PagePatrol',
            'manage_options',
            'pagepatrol',
            [$this, 'render_admin_page'],
            'dashicons-visibility',
            30
        );
    }

    public function register_settings() {
        register_setting('pagepatrol_settings', 'pagepatrol_api_key', [
            'sanitize_callback' => [$this, 'sanitize_api_key']
        ]);
    }

    public function sanitize_api_key($key) {
        $key = sanitize_text_field($key);

        if (empty($key)) {
            return $key;
        }

        try {
            // Test the API key by making a request
            $test_client = new PagePatrol_API_Client($key);
            $response = $test_client->register_site();

            // If successful, store the platform ID
            update_option('pagepatrol_platform_id', $response['id']);

            return $key;
        } catch (Exception $e) {
            add_settings_error(
                'pagepatrol_settings',
                'invalid_api_key',
                'Invalid API key: ' . $e->getMessage()
            );
            return get_option('pagepatrol_api_key'); // Keep the old key
        }
    }

    public function ajax_refresh_status() {
        check_ajax_referer('pagepatrol_refresh', 'nonce');

        if (!$this->api_client) {
            wp_send_json_error(['message' => 'API client not initialized']);
            return;
        }

        try {
            $platform_id = get_option('pagepatrol_platform_id');
            if (!$platform_id) {
                throw new Exception('Platform ID not found');
            }

            $status = $this->api_client->get_status($platform_id);
            $logs = $this->api_client->get_logs($platform_id);

            wp_send_json_success([
                'status' => $status,
                'logs' => array_slice($logs, 0, 5)
            ]);
        } catch (Exception $e) {
            PagePatrol::handle_error($e, 'Status refresh failed');
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_pagepatrol' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'pagepatrol-admin',
            PAGEPATROL_PLUGIN_URL . 'assets/css/admin.css',
            [],
            PAGEPATROL_VERSION
        );

        wp_enqueue_script(
            'pagepatrol-admin',
            PAGEPATROL_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            PAGEPATROL_VERSION,
            true
        );

        wp_localize_script('pagepatrol-admin', 'pagepatrol', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pagepatrol_refresh'),
            'platform_id' => get_option('pagepatrol_platform_id')
        ]);
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check for settings errors
        settings_errors('pagepatrol_settings');

        // Include the admin page template
        require_once PAGEPATROL_PLUGIN_DIR . 'views/admin-page.php';
    }
}
