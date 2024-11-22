<?php
/**
 * Plugin Name: WP-Pay-Pi
 * Description: Accept Pi Coin payments in WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wp-pay-pi
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WP_PAY_PI_VERSION', '1.0.0');
define('WP_PAY_PI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_PAY_PI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WPPayPi\\';
    $base_dir = WP_PAY_PI_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function wp_pay_pi_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . 
                 esc_html__('WP-Pay-Pi requires WooCommerce to be installed and active.', 'wp-pay-pi') . 
                 '</p></div>';
        });
        return;
    }

    // Load plugin
    require_once WP_PAY_PI_PLUGIN_DIR . 'includes/class-wp-pay-pi.php';
    \WPPayPi\WPPayPi::instance();
}
add_action('plugins_loaded', 'wp_pay_pi_init');