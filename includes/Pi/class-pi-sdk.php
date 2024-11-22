<?php
namespace WPPayPi\Pi;

class PiSdk {
    private $api_key;
    private $sandbox_mode;

    public function __construct($api_key, $sandbox_mode = true) {
        $this->api_key = $api_key;
        $this->sandbox_mode = $sandbox_mode;
    }

    public function create_payment($amount, $memo, $metadata = []) {
        $api_url = $this->sandbox_mode 
            ? 'https://api.sandbox.pi-network.com/v1/payments' 
            : 'https://api.pi-network.com/v1/payments';

        $headers = [
            'Authorization' => 'Key ' . $this->api_key,
            'Content-Type' => 'application/json'
        ];

        $body = [
            'amount' => $amount,
            'memo' => $memo,
            'metadata' => $metadata,
            'webhook_url' => home_url('/wc-api/wp_pay_pi')
        ];

        $response = wp_remote_post($api_url, [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['payment_id'])) {
            throw new \Exception(__('Invalid response from Pi Network', 'wp-pay-pi'));
        }

        return $body;
    }

    public function verify_payment($payment_id) {
        $api_url = $this->sandbox_mode 
            ? "https://api.sandbox.pi-network.com/v1/payments/{$payment_id}" 
            : "https://api.pi-network.com/v1/payments/{$payment_id}";

        $response = wp_remote_get($api_url, [
            'headers' => [
                'Authorization' => 'Key ' . $this->api_key
            ]
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}