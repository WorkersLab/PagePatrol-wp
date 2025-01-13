<?php

class PagePatrol_Admin_Bar {
    private $api_client;
    private $platform_id;

    public function __construct() {
        $api_key = get_option('pagepatrol_api_key');
        $this->platform_id = get_option('pagepatrol_platform_id');

        if ($api_key && $this->platform_id) {
            $this->api_client = new PagePatrol_API_Client($api_key);
            add_action('admin_bar_menu', [$this, 'add_toolbar_items'], 100);
        }
    }

    public function add_toolbar_items($admin_bar) {
        $status = get_transient('pagepatrol_status');

        if (!$status) {
            try {
                $status = $this->api_client->get_status($this->platform_id);
                set_transient('pagepatrol_status', $status, 5 * MINUTE_IN_SECONDS);
            } catch (Exception $e) {
                $status = ['current_status' => 'unknown'];
            }
        }

        $admin_bar->add_node([
            'id' => 'pagepatrol',
            'title' => $this->get_status_icon($status['current_status']) . ' PagePatrol',
            'href' => admin_url('admin.php?page=pagepatrol'),
        ]);

        $admin_bar->add_node([
            'id' => 'pagepatrol-dashboard',
            'parent' => 'pagepatrol',
            'title' => 'View Dashboard',
            'href' => 'https://pagepatrol.net/dashboard',
            'meta' => ['target' => '_blank']
        ]);
    }

    private function get_status_icon($status) {
        $colors = [
            'up' => '#00b300',
            'down' => '#ff0000',
            'unknown' => '#999999'
        ];

        return sprintf(
            '<span class="pagepatrol-status-dot" style="background-color: %s"></span>',
            $colors[$status] ?? $colors['unknown']
        );
    }
}
