<?php

class PagePatrol_API_Client {
    private $api_key;
    private $api_url;

    public function __construct($api_key) {
        $this->api_key = $api_key;
        $this->api_url = PAGEPATROL_API_URL;
    }

    public function register_site() {
        $site_url = get_site_url();
        $site_name = get_bloginfo('name');

        return $this->post('platforms', [
            'platform' => [
                'name' => $site_name,
                'uri' => $site_url,
                'frequency_in_minutes' => 5,
                'status_code' => 200,
                'check_ssl' => true
            ]
        ]);
    }

    public function get_status($platform_id) {
        return $this->get("platforms/{$platform_id}/status");
    }

    public function get_logs($platform_id) {
        return $this->get("platforms/{$platform_id}/logs");
    }

    private function request($method, $endpoint, $data = null) {
        $url = "{$this->api_url}/{$endpoint}";

        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];

        if ($data) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new Exception(wp_kses($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 400) {
            throw new Exception("API Error: " . wp_kses($body));
        }

        return json_decode($body, true);
    }

    private function get($endpoint) {
        return $this->request('GET', $endpoint);
    }

    private function post($endpoint, $data) {
        return $this->request('POST', $endpoint, $data);
    }
}
