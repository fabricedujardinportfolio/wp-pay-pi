<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('wp_pay_pi_options');
        do_settings_sections('wp_pay_pi_options');
        ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="binance_api_key"><?php _e('Binance API Key', 'wp-pay-pi'); ?></label>
                </th>
                <td>
                    <input type="text" id="binance_api_key" name="wp_pay_pi_settings[binance_api_key]" 
                           value="<?php echo esc_attr(get_option('wp_pay_pi_settings')['binance_api_key'] ?? ''); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="binance_api_secret"><?php _e('Binance API Secret', 'wp-pay-pi'); ?></label>
                </th>
                <td>
                    <input type="password" id="binance_api_secret" name="wp_pay_pi_settings[binance_api_secret]" 
                           value="<?php echo esc_attr(get_option('wp_pay_pi_settings')['binance_api_secret'] ?? ''); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="pi_network_api_key"><?php _e('Pi Network API Key', 'wp-pay-pi'); ?></label>
                </th>
                <td>
                    <input type="text" id="pi_network_api_key" name="wp_pay_pi_settings[pi_network_api_key]" 
                           value="<?php echo esc_attr(get_option('wp_pay_pi_settings')['pi_network_api_key'] ?? ''); ?>" 
                           class="regular-text">
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>