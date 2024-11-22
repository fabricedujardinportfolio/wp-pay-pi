<?php
namespace WPPayPi;

class WPPayPi {
    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_filter('woocommerce_payment_gateways', [$this, 'add_gateway_class']);
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_gateway_class($gateways) {
        $gateways[] = 'WPPayPi\Gateway\PiGateway';
        return $gateways;
    }

    public function add_plugin_page() {
        add_menu_page(
            __('WP-Pay-Pi Settings', 'wp-pay-pi'),
            __('WP-Pay-Pi', 'wp-pay-pi'),
            'manage_options',
            'wp-pay-pi',
            [$this, 'create_admin_page'],
            'dashicons-money'
        );
    }

    public function register_settings() {
        register_setting('wp_pay_pi_options', 'wp_pay_pi_settings');
    }

    public function create_admin_page() {
        include WP_PAY_PI_PLUGIN_DIR . 'templates/admin-settings.php';
    }
}