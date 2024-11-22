<?php
namespace WPPayPi\Gateway;

use WPPayPi\Pi\PiSdk;

class PiGateway extends \WC_Payment_Gateway {
    private $pi_sdk;

    public function __construct() {
        $this->id = 'wp_pay_pi';
        $this->method_title = __('Paiement en Pi Coin', 'wp-pay-pi');
        $this->method_description = __('Accepter les paiements en Pi Coin', 'wp-pay-pi');
        
        $this->supports = [
            'products',
            'refunds'
        ];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');

        $settings = get_option('wp_pay_pi_settings', []);
        $this->pi_sdk = new PiSdk(
            $settings['pi_network_api_key'] ?? '',
            $this->get_option('sandbox_mode', 'yes') === 'yes'
        );

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_wp_pay_pi', [$this, 'check_pi_response']);
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Activer/Désactiver', 'wp-pay-pi'),
                'type' => 'checkbox',
                'label' => __('Activer le paiement en Pi Coin', 'wp-pay-pi'),
                'default' => 'no'
            ],
            'title' => [
                'title' => __('Titre', 'wp-pay-pi'),
                'type' => 'text',
                'description' => __('Titre de la méthode de paiement affiché lors du paiement.', 'wp-pay-pi'),
                'default' => __('Payer en Pi Coin', 'wp-pay-pi'),
                'desc_tip' => true,
            ],
            'description' => [
                'title' => __('Description', 'wp-pay-pi'),
                'type' => 'textarea',
                'description' => __('Description de la méthode de paiement affichée lors du paiement.', 'wp-pay-pi'),
                'default' => __('Payez en toute sécurité avec Pi Coin.', 'wp-pay-pi'),
                'desc_tip' => true,
            ],
            'sandbox_mode' => [
                'title' => __('Mode Sandbox', 'wp-pay-pi'),
                'type' => 'checkbox',
                'label' => __('Activer le mode test', 'wp-pay-pi'),
                'default' => 'yes',
                'description' => __('Utilisez le mode sandbox pour tester les paiements.', 'wp-pay-pi'),
            ],
        ];
    }

    public function process_payment($order_id) {
        try {
            $order = wc_get_order($order_id);
            
            $payment = $this->pi_sdk->create_payment(
                $order->get_total(),
                sprintf(__('Commande %s sur %s', 'wp-pay-pi'), $order->get_order_number(), get_bloginfo('name')),
                ['order_id' => $order_id]
            );
            
            $order->update_meta_data('_pi_payment_id', $payment['payment_id']);
            $order->save();
            
            return [
                'result' => 'success',
                'redirect' => $payment['payment_url']
            ];
        } catch (\Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            return [
                'result' => 'failure'
            ];
        }
    }

    public function check_pi_response() {
        $payment_id = $_POST['payment_id'] ?? '';
        
        try {
            $payment_status = $this->pi_sdk->verify_payment($payment_id);
            $order_id = $payment_status['metadata']['order_id'] ?? 0;
            $order = wc_get_order($order_id);
            
            if (!$order) {
                throw new \Exception(__('Commande introuvable', 'wp-pay-pi'));
            }
            
            if ($payment_status['status'] === 'completed') {
                $order->payment_complete($payment_id);
                $order->add_order_note(__('Paiement Pi Coin reçu avec succès', 'wp-pay-pi'));
            } else if ($payment_status['status'] === 'failed') {
                $order->update_status('failed', __('Le paiement Pi Coin a échoué', 'wp-pay-pi'));
            }
            
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}