<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Setup Brain\Monkey
Brain\Monkey\setUp();

// Mock WordPress functions
Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(dirname(__DIR__) . '/');
Brain\Monkey\Functions\when('plugin_dir_url')->justReturn('http://example.com/wp-content/plugins/pagepatrol/');
Brain\Monkey\Functions\when('plugin_basename')->justReturn('pagepatrol/pagepatrol.php');
Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
    'response' => ['code' => 200],
    'body' => json_encode(['id' => '123'])
]);
Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['id' => '123']));
Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
Brain\Monkey\Functions\when('is_wp_error')->justReturn(false);
Brain\Monkey\Functions\when('get_option')->justReturn('test_value');
Brain\Monkey\Functions\when('update_option')->justReturn(true);
Brain\Monkey\Functions\when('add_action')->justReturn(true);
Brain\Monkey\Functions\when('add_filter')->justReturn(true);
Brain\Monkey\Functions\when('sanitize_text_field')->justReturn("test_value");
Brain\Monkey\Functions\when('admin_url')->justReturn('http://example.com/wp-admin/');
Brain\Monkey\Functions\when('wp_create_nonce')->justReturn('test_nonce');
Brain\Monkey\Functions\when('get_site_url')->justReturn('http://example.com');
Brain\Monkey\Functions\when('get_bloginfo')->justReturn('Test Site');
Brain\Monkey\Functions\when('register_activation_hook')->justReturn(true);
Brain\Monkey\Functions\when('register_deactivation_hook')->justReturn(true);
Brain\Monkey\Functions\when('delete_transient')->justReturn(true);
Brain\Monkey\Functions\when('wp_kses')->returnArg();
Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
Brain\Monkey\Functions\when('add_menu_page')->justReturn(true);
Brain\Monkey\Functions\when('register_setting')->justReturn(true);
Brain\Monkey\Functions\when('add_settings_error')->justReturn(true);
Brain\Monkey\Functions\when('wp_enqueue_style')->justReturn(true);
Brain\Monkey\Functions\when('wp_enqueue_script')->justReturn(true);
Brain\Monkey\Functions\when('wp_localize_script')->justReturn(true);

// Now include the plugin files
require_once dirname(__DIR__) . '/includes/class-api-client.php';
require_once dirname(__DIR__) . '/includes/class-admin.php';
require_once dirname(__DIR__) . '/includes/class-admin-bar.php';

// Load the main plugin file last
require_once dirname(__DIR__) . '/pagepatrol.php';

// Cleanup function for Brain Monkey
register_shutdown_function(function () {
    Brain\Monkey\tearDown();
});
