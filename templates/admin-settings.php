<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_MoneyBadger_Settings {
    public function __construct() {
        add_action('woocommerce_settings_general_options_after', array($this, 'settings_page'));
        add_action('woocommerce_update_options_payment_gateways_moneybadger', array($this, 'process_admin_options'));
    }

    public function settings_page() {
        ?>
        <h2><?php _e('MoneyBadger Settings', 'woocommerce-moneybadger'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="api_key"><?php _e('API Key', 'woocommerce-moneybadger'); ?></label>
                </th>
                <td class="forminp">
                    <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr(get_option('api_key')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="enabled"><?php _e('Enable/Disable', 'woocommerce-moneybadger'); ?></label>
                </th>
                <td class="forminp">
                    <input type="checkbox" id="enabled" name="enabled" <?php checked('yes', get_option('enabled')); ?> />
                </td>
            </tr>
        </table>
        <?php
    }

    public function process_admin_options() {
        // Save the settings to the database
        update_option('api_key', sanitize_text_field($_POST['api_key']));
        update_option('enabled', isset($_POST['enabled']) ? 'yes' : 'no');
    }
}

// Initialize the settings class
new WC_MoneyBadger_Settings();
