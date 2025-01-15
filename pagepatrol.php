<?php
/**
 * Plugin Name: PagePatrol
 * Description: Monitor your website's uptime and performance with PagePatrol
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: ibabar, PagePatrol
 * Author URI: https://fusion.pagepatrol.net
 * License: GPLv2 or later
 * Text Domain: pagepatrol
 */

if (!defined("ABSPATH")) {
    exit();
}

class PagePatrol {
    private $api_client;
    private $admin;
    private $admin_bar;
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->include_files();
        $this->init_components();
        $this->init_hooks();
    }

    private function define_constants() {
        define("PAGEPATROL_VERSION", "1.0.0");
        define("PAGEPATROL_PLUGIN_DIR", plugin_dir_path(__FILE__));
        define("PAGEPATROL_PLUGIN_URL", plugin_dir_url(__FILE__));
        define("PAGEPATROL_API_URL", "https://fusion-api.pagepatrol.net/v1");
    }

    private function include_files() {
        require_once PAGEPATROL_PLUGIN_DIR . "includes/class-api-client.php";
        require_once PAGEPATROL_PLUGIN_DIR . "includes/class-admin.php";
        require_once PAGEPATROL_PLUGIN_DIR . "includes/class-admin-bar.php";
    }

    private function init_components() {
        // Initialize API client if we have an API key
        $api_key = get_option("pagepatrol_api_key");
        if ($api_key) {
            try {
                $this->api_client = new PagePatrol_API_Client($api_key);
            } catch (Exception $e) {
                add_action("admin_notices", function () use ($e) {
                    echo '<div class="notice notice-error"><p>PagePatrol Error: ' .
                        esc_html($e->getMessage()) .
                        "</p></div>";
                });
            }
        }

        // Initialize admin and admin bar components
        $this->admin = new PagePatrol_Admin($this->api_client);
        $this->admin_bar = new PagePatrol_Admin_Bar($this->api_client);
    }

    private function init_hooks() {
        // Add settings link to plugins page
        add_filter("plugin_action_links_" . plugin_basename(__FILE__), [
            $this,
            "add_plugin_links",
        ]);

        // Handle plugin activation
        register_activation_hook(__FILE__, [$this, "activate"]);

        // Handle plugin deactivation
        register_deactivation_hook(__FILE__, [$this, "deactivate"]);
    }

    public function add_plugin_links($links) {
        $settings_link =
            '<a href="' .
            esc_url(admin_url("admin.php?page=pagepatrol")) .
            '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function activate() {
        // No special activation needed yet
    }

    public function deactivate() {
        // Clear cached data
        delete_transient("pagepatrol_status");

        // Note: We don't delete the API key or platform ID in case the plugin is reactivated
    }

    // Error handling helper
    public static function handle_error($e, $context = "") {
        $message = $context ? "$context: " : "";
        $message .= $e->getMessage();

        if (defined("WP_DEBUG") && WP_DEBUG) {
            // phpcs:disable WordPress.PHP.DevelopmentFunctions
            error_log("PagePatrol Error: $message");
            // phpcs:enable WordPress.PHP.DevelopmentFunctions
        }

        if (is_admin()) {
            add_action("admin_notices", function () use ($message) {
                echo '<div class="notice notice-error"><p>' .
                    esc_html($message) .
                    "</p></div>";
            });
        }
    }
}

// Initialize the plugin
function pagepatrol() {
    return PagePatrol::get_instance();
}

pagepatrol();
