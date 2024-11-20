<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Moneybadger payment method integration
 *
 * @since 1.0.0
 */
final class WC_Gateway_Moneybadger_Blocks_Support extends AbstractPaymentMethodType {
	/**
	 * Name of the payment method.
	 *
	 * @var string
	 */
	protected $name = 'wc_moneybadger_payment_gateway';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_moneybadger_settings', [] );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		$payment_gateways_class   = WC()->payment_gateways();
		$payment_gateways         = $payment_gateways_class->payment_gateways();

		return $payment_gateways['wc_moneybadger_payment_gateway']->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$asset_path   = WC_GATEWAY_MONEYBADGER_PATH . '/build/index.asset.php';
		$version      = WC_GATEWAY_MONEYBADGER_VERSION;
		$dependencies = [];
		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = is_array( $asset ) && isset( $asset['version'] )
				? $asset['version']
				: $version;
			$dependencies = is_array( $asset ) && isset( $asset['dependencies'] )
				? $asset['dependencies']
				: $dependencies;
		}
		
		wp_register_script(
			'wc-moneybadger-blocks-integration',
			WC_GATEWAY_MONEYBADGER_URL . '/assets/js/moneybadger.js',
			$dependencies,
			$version,
			true
		);
		wp_set_script_translations(
			'wc-moneybadger-blocks-integration',
			'wc-moneybadger-gateway'
		);
		return [ 'wc-moneybadger-blocks-integration' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => $this->get_supported_features(),
			'logo_url'    => WC_GATEWAY_MONEYBADGER_URL . '/assets/images/moneybadger-logo.png',
		];
	}

	/**
	 * Returns an array of supported features.
	 *
	 * @return string[]
	 */
	public function get_supported_features() {
		$payment_gateways = WC()->payment_gateways->payment_gateways();
		return $payment_gateways['wc_moneybadger_payment_gateway']->supports;
	}
}
