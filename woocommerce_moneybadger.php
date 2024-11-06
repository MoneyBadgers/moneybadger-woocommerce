<?php
/*
Plugin Name: MoneyBadger Crypto Payments
Plugin URI:  https://wordpress.org/plugins/moneybadger-woocommerce/
Description: Accept crypto payments from Bitcoin Lightning, VALR, Luno and Binance wallets.
Version: 1.0
Author: MoneyBadger
Author URI: https://www.moneybadger.co.za
License: GPL-3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires at least: 5.4
Requires PHP: 7.4
WC requires at least: 4.0
WC tested up to: 8.0
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'MONEYBADGER_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define(
    'MONEYBADGER_PLUGIN_URL',
    untrailingslashit(
        plugins_url( basename( plugin_dir_path( __FILE__ ) ) , basename( __FILE__ ) )
    )
);

// Check if WooCommerce is active
if (
    ! in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option ('active_plugins' ) )
    )
) {
    add_action( 'admin_notices', 'wc_moneybadger_missing_woocommerce_notice' );
    return;
}

// Display admin notice if WooCommerce is not active
function wc_moneybadger_missing_woocommerce_notice() {
    echo '<div class="error"><p><strong>WooCommerce MoneyBadger Gateway requires WooCommerce to be active.</strong></p></div>';
}

// Hook into WooCommerce to initialize the gateway
function woocommerce_moneybadger_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return; // Exit if WooCommerce is not installed

    // Load the gateway class
    include_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-moneybadger-gateway.php';

    // Add MoneyBadger to the list of available gateways
    add_filter( 'woocommerce_payment_gateways', 'add_moneybadger_gateway' );

    function add_moneybadger_gateway($methods) {
        $methods[] = 'WC_Gateway_Moneybadger';
        return $methods;
    }
}
add_action( 'plugins_loaded', 'woocommerce_moneybadger_init', 0);

// Add settings link on the plugin page
function moneybadger_gateway_settings_link($links) {
    $url = add_query_arg(
		array(
			'page'    => 'wc-settings',
			'tab'     => 'checkout',
			'section' => 'wc_moneybadger_payment_gateway',
		),
		admin_url( 'admin.php' )
	);
    $settings_link = '<a href="' . esc_url( $url ) . '">' .
                    esc_html__('Settings', 'wc-gateway-moneybadger') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'moneybadger_gateway_settings_link' );

// Register the payment method for WooCommerce Blocks
add_action(
    'woocommerce_blocks_payment_method_type_registration',
    function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-moneybadger-gateway-block.php';
        $payment_method_registry->register( new MoneyBadger_Blocks_Support() );
    }
);

/**
 * Inform WooCommerce about specialised feature support.
 *
 * @return void
 */
function woocommerce_moneybadger_compatibility_declarations() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__
		);
	}
}
add_action( 'before_woocommerce_init', 'woocommerce_moneybadger_compatibility_declarations' );
