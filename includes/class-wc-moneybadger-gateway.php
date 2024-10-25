<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * WC_Gateway_Moneybadger class.
 */
class WC_Gateway_Moneybadger extends WC_Payment_Gateway {

    // define settings

    /**
     * Whether the gateway is in test mode
     * @var bool
     */
    private $testmode;

    /**
     * The merchant code
     * @var string
     */
    private $merchant_code;


	/**
	 * WC_Logger
	 *
	 * @var WC_Logger $logger
	 */
	protected $logger;

    /**
     * The API key
     * @var string
     */
    private $api_key;

    /**
     * Get the base URL to use for API requests.
     *
     * @return string
     */
    public function get_base_url() {
        return 'https://api.' . ($this->testmode ? 'staging' : '') . '.cryptoqr.co.za/api/v2';
    }

    /**
     * Get the payments base URL to use for requests.
     *
     * @return string
     */
    public function get_payments_base_url() {
        return 'https://pay.' . ($this->testmode ? 'staging' : '') . '.cryptoqr.net';
    }

    public function __construct() {
        $this->id                 = 'wc_moneybadger_payment_gateway';
        $this->method_title       = 'MoneyBadger Payment Gateway';
        $this->method_description = 'Accept crypto payments from Bitcoin Lightning, VALR, Luno and Binance wallets.';
        $this->has_fields         = true;
        $this->supports           = array('products', 'iframe');  // Declare iframe support

        $this->init_form_fields();
        $this->init_settings();

        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->testmode     = 'yes' === $this->get_option('testmode');
        $this->merchant_code = $this->get_option('merchant_code');
        $this->api_key      = $this->get_option('api_key');

        // Save admin options
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array( $this, 'process_admin_options' )
        );
        // Receipt page creates POST to gateway or hosts iFrame
		add_action(
            'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' )
        );
        // Register the webhook endpoint
        add_action(
            'woocommerce_api_wc_gateway_moneybadger_webhook',
            array( $this, 'check_moneybadger_webhook' )
        );
        add_action(
            'woocommerce_api_wc_gateway_moneybadger_order_status',
            array( $this, 'check_if_order_is_paid' )
        );
    }

    /**
     * Initialize the admin fields for the plugin settings.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'wc-gateway-moneybadger'),
                'type'    => 'checkbox',
                'label'   => __('Enable the MoneyBadger Payment Gateway', 'wc-gateway-moneybadger'),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __('Title', 'wc-gateway-moneybadger'),
                'type'        => 'text',
                'description' => __(
                    'The payment method title displayed to customers at checkout.',
                    'wc-gateway-moneybadger')
                ,
                'default'     => __('MoneyBadger', 'wc-gateway-moneybadger'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'wc-gateway-moneybadger'),
                'type'        => 'textarea',
                'description' => __(
                    'The payment method description displayed to customers at checkout.',
                    'wc-gateway-moneybadger'
                ),
                'default'     => __('Pay securely with Bitcoin Lightning, VALR, Luno and Binance wallets via MoneyBadger.', 'wc-gateway-moneybadger'),
            ),
            'merchant_code' => array(
                'title'       => __('Merchant Code', 'wc-gateway-moneybadger'),
                'type'        => 'text',
                'description' => __('Enter your MoneyBadger merchant code.', 'wc-gateway-moneybadger'),
                'default'     => '',
            ),
            'api_key' => array(
                'title'       => __('API Key', 'wc-gateway-moneybadger'),
                'type'        => 'password',
                'description' => __('Enter your MoneyBadger API key.', 'wc-gateway-moneybadger'),
                'default'     => '',
            ),
            'testmode' => array(
                'title'       => __('Test mode', 'wc-gateway-moneybadger'),
                'type'        => 'checkbox',
                'label'       => __('Enable test mode', 'wc-gateway-moneybadger'),
                'default'     => 'yes',
            ),
        );
    }

    /**
     * Generate the MoneyBadger payment iframe URL.
     *
     * @param string $order_id  The order ID
     * @return string
     */
    public function get_moneybadger_iframe_url($order_id) {
        $amount = $this->get_order_total();
        $reference = $order_id . '-' . time();

        $webhook_params = http_build_query([
           "wc-api" => "wc_gateway_moneybadger_webhook",
           "order_id" => $order_id,
           "reference" => $reference,
        ]);

        return sprintf(
            "%s/?%s",
            $this->get_payments_base_url(),
            http_build_query(
                [
                    'amountCents' => $amount * 100,
                    'orderId' => $reference,
                    'merchantCode' => $this->merchant_code,
                    'statusWebhookUrl' => sprintf( "%s/?%s", home_url(), $webhook_params ),
                    'orderDescription' => 'Payment for order ' . $order_id,
                    'autoConfirm' => true,
                ])
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order( $order_id );
        $order->add_order_note(
            __( 'MoneyBadger payment initiated.', 'wc-gateway-moneybadger' )
        );
        $order->save();
        return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);
    }

    /**
	 * Generates the MoneyBadger iframe on the payment page.
	 *
	 * @param string $order_id  The order ID
     * @return void
	 */
	public function receipt_page( $order_id ) {
        $order = wc_get_order( $order_id );

        wp_enqueue_script(
			'moneybadger-payment-script',
            plugins_url( './../assets/js/moneybadger-checkout.js', __FILE__ ),
            array( 'jquery' )
        );

        $order_status_url_params = http_build_query([
           "wc-api" => "wc_gateway_moneybadger_order_status",
           "order_id" => $order_id,
        ]);
        $order_status_url = sprintf( "%s/?%s", home_url(), $order_status_url_params );

        wp_localize_script(
			'moneybadger-payment-script',
			'moneybadger_checkout_params',
			array(
                'order_status_url' => $order_status_url,
                'order_complete_url' => $this->get_return_url( $order ),
			)
		);

        echo '<iframe src="' . esc_url (
                $this->get_moneybadger_iframe_url( $order_id )
            ) . '" width="100%" height="600" frameborder="0"></iframe>';
    }

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
            if ( get_woocommerce_currency() !== 'ZAR' ) {
                return false;
            }
            if ( empty( $this->get_option('merchant_code') ) ) {
                return false;
            }
            if ( empty( $this->get_option('api_key') ) ) {
                return false;
            }
        }
		return parent::is_available();
	}

    /**
     * Check the MoneyBadger webhook
     */
    public function check_moneybadger_webhook() {
        $payload = wp_unslash( $_GET );

        if ( ! isset( $payload['reference'] ) ) {
            wp_send_json_error( array( 'error' => 'Unexpected payload.' ) );
        }

        $reference = $payload['reference'];
        // the reference is a concat of order id & timestamp
        $order_id = explode( '-', $reference )[0];
        $invoice = $this->get_invoice( $reference );

        if ( is_wp_error( $invoice ) ) {
            wp_send_json_error( array( 'error' => $invoice->get_error_message() ) );
        }

        $status = $invoice['status'];
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'error' => 'Order not found' ) );
        }

        if ( "AUTHORIZED" === $status ) {
            $this->confirm_payment( $reference );
            // fetch the status again after confirming
            $invoice = $this->get_invoice( $reference );
            if ( is_wp_error( $invoice ) ) {
                wp_send_json_error( array( 'error' => $invoice->get_error_message() ) );
            }
            $status = $invoice['status'];
        }

        if ( "CONFIRMED" === $status ) {
            if ( $order->is_paid() ) {
                // exit
                wp_send_json( array() );
            }
            $transaction_id = isset( $invoice['id'] ) ? $invoice['id'] : $reference;
            try {
                // mark the order as complete and empty the cart
                $order->payment_complete( $transaction_id );
                $order->add_order_note(
                    'Payment received successfully via MoneyBadger.',
                    'wc-gateway-moneybadger'
                );
                $order->save();
                wc_empty_cart();
            } catch (\Exception $e) {
                // fail with an error, so that the webhook can be re-sent again
                wp_send_json_error( array( 'error' => $e->getMessage() ) );
            }
        }

        $order->add_order_note(
            'MoneyBadger webhook received for the invoice, which has status: ' .
                $invoice['status'],
            'wc-gateway-moneybadger'
        );
        wp_send_json( array() );
    }

    /**
     * Retrieve a MoneyBadger invoice.
     *
     * @param string $invoiceID The invoice to retrieve.
     * @return mixed|WP_Error
     */
    public function get_invoice($invoiceID) {
        $api_url = $this->get_base_url() . '/invoices/' . $invoiceID;

        $args = array(
            'headers' => array(
                'X-API-Key' => $this->get_option( 'api_key' ),
            ),
        );

        $response = wp_remote_get( $api_url, $args );

        if ( is_wp_error( $response ) ) {
            $this->log( "MoneyBadger invoice lookup error:" );
            $this->log( $response );
            return new WP_Error(
                'moneybadger_api_invoice_lookup_error',
                'MoneyBadger invoice lookup failed',
                $response
            );
        }

        $body = wp_remote_retrieve_body( $response );
        $invoice = json_decode( $body, true );

        if ( null === $invoice ) {
            return new WP_Error(
                'moneybadger_api_unexpected_response',
                'MoneyBadger invoice lookup returned invalid JSON',
                $body
            );
        }

        if (
            ! isset( $invoice['id'] ) ||
            ! isset( $invoice['status'] )
        ) {
            return new WP_Error(
                'moneybadger_api_unexpected_response',
                'MoneyBadger invoice lookup returned invalid JSON',
                $body
            );
        }

        return $invoice;
    }

    /**
     * Confirm a MoneyBadger payment request.
     *
     * @param string $invoiceID The invoice to confirm.
     * @throws \Exception
     * @return void
     */
    public function confirm_payment($invoiceID) {
        $api_url = $this->get_base_url() . '/invoices/' . $invoiceID . '/confirm';
        $api_key = $this->get_option( 'api_key' );

        $args = array(
            'headers' => array(
                'X-API-Key' => $api_key,
            ),
        );

        $response = wp_remote_post( $api_url, $args );

        if ( is_wp_error( $response ) ) {
            $this->log( "MoneyBadger payment confirmation failed:" );
            $this->log($response);
            throw new \Exception( "MoneyBadger payment confirmation failed." );
        }
    }

    public function check_if_order_is_paid() {
        // get the order_id from the url
        $order_id = isset( $_GET['order_id']) ? intval( $_GET['order_id'] ) : 0;

        if (!$order_id) {
            wp_send_json( array( 'order_is_complete' => false ) );
            return;
        }

        // get the order
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wp_send_json( array( 'order_is_complete' => false ) );
            return;
        }

        // check if the order is paid
        $order_is_complete = $order->is_paid();

        // return json object with shape { order_is_complete: true/false }
        wp_send_json( array( 'order_is_complete' => $order_is_complete ) );
    }

    /**
	 * Write to the error log
	 *
	 * @param mixed $message Message/object to log
     * @return void
	 */
	public function log( $message ) {
		if ( 'yes' === $this->get_option( 'testmode' ) ) {
			if ( empty( $this->logger ) ) {
				$this->logger = new WC_Logger();
			}

            if ( ! is_string( $message ) ) {
                $message =  print_r( $message , true );
            }

			$this->logger->add( 'moneybadger', $message );
		}
	}

}
