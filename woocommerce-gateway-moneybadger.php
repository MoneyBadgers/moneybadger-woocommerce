<?php
/**
 * @wordpress-plugin
 * @Plugin Name: MoneyBadger Gateway for WooCommerce
 * Plugin URI: https://moneybadger.eu/integrations/woo-commerce
 * Description: Accept crypto payments from Bitcoin Lightning, VALR, Luno and Binance wallets.
 * Author: MoneyBadger
 * Author URI: https://moneybadger.eu/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Version: 1.0.0
 * Requires at least: 6.2
 * Tested up to: 6.5.3
 * WC tested up to: 8.7.0
 * WC requires at least: 7.2
 * Requires PHP: 7.2 or later
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_GATEWAY_MONEYBADGER_VERSION', '1.0.0' );
define( 'WC_GATEWAY_MONEYBADGER_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_GATEWAY_MONEYBADGER_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

load_plugin_textdomain( 'wc-moneybadger-gateway', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

add_action('plugins_loaded', 'moneybadger_init', 0);

/**
 * Initialize the gateway.
 */
function moneybadger_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
    
    require_once( plugin_basename( 'includes/class-wc-gateway-moneybadger.php' ) );

    add_filter('woocommerce_payment_gateways', 'moneybadger_wc_add_gateway_class');
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'moneybadger_plugin_links' );
}

/**
 * Add the gateway to WooCommerce
 */
function moneybadger_wc_add_gateway_class($methods) {
  $methods[] = 'WC_Gateway_moneybadger';
  return $methods;
}

/**
 * Show action links on the plugin screen.
 *
 * @param mixed $links Plugin Action links.
 *
 * @return array
 */
function moneybadger_plugin_links( $links ) {
	$settings_url = add_query_arg(
		array(
			'page' => 'wc-settings',
			'tab' => 'checkout',
			'section' => 'wc_gateway_moneybadger',
		),
		admin_url( 'admin.php' )
	);

	$plugin_links = array(
		'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'wc-moneybadger-gateway' ) . '</a>',
		'<a href="https://moneybadger.eu/contact">' . esc_html__( 'Support', 'wc-moneybadger-gateway' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}

add_action( 'woocommerce_blocks_loaded', 'moneybadger_wc_blocks_support' );

function moneybadger_wc_blocks_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-moneybadger-wc-gateway-blocks-support.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_Gateway_Moneybadger_Blocks_Support );
			}
		);
	}
}

/**
 * Make it compatible with Woocommerce features.
 *
 * List of features:
 * - custom_order_tables
 * - product_block_editor
 *
 * @return void
 */
function moneybadger_wc_declare_feature_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__
		);
	}
}
add_action( 'before_woocommerce_init', 'moneybadger_wc_declare_feature_compatibility' );
