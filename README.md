# MoneyBadger Gateway for WooCommerce

## Overview

The MoneyBadger Gateway for WooCommerce plugin allows merchants to accept cryptocurrency payments through various wallets, including Bitcoin Lightning, VALR, Luno, and Binance. This plugin integrates seamlessly with WooCommerce, providing a user-friendly interface for managing crypto transactions.

## Plugin Information

- **Plugin Name**: MoneyBadger Gateway for WooCommerce

- **Plugin URI**: [MoneyBadger Integration](https://moneybadger.eu/integrations/woocommerce)

- **Description**: Accept crypto payments from Bitcoin Lightning, VALR, Luno, and Binance wallets.

- **Author**: MoneyBadger

- **Author URI**: [MoneyBadger](https://moneybadger.eu/)

- **License**: GPLv3

- **License URI**: [GPLv3 License](https://www.gnu.org/licenses/gpl-3.0.html)

- **Version**: 1.0.0

- **Requires at least**: WordPress 6.2

- **Tested up to**: WordPress 6.5.2

- **WooCommerce tested up to**: WooCommerce 8.7.0

- **WooCommerce requires at least**: 7.2

- **Requires PHP**: 7.2 or later


## Constants


- **WC_GATEWAY_MONEYBADGER_VERSION**: The current version of the plugin.

- **WC_GATEWAY_MONEYBADGER_URL**: The URL of the plugin directory.


- **WC_GATEWAY_MONEYBADGER_PATH**: The file path of the plugin directory.


## Localization


The plugin supports localization. The text domain used is `wc-moneybadger-gateway`. The plugin text domain is loaded using the `load_plugin_textdomain` function.


## Initialization


The plugin initializes the MoneyBadger payment gateway when the `plugins_loaded` action is triggered. The `moneybadger_init` function checks if the `WC_Payment_Gateway` class exists and includes the main gateway class file.


### Functions


1. **moneybadger_init()**

   - Initializes the MoneyBadger payment gateway.
   - Requires the WC_Payment_Gateway class.
   - Adds the gateway to WooCommerce payment methods.

2. **moneybadger_wc_add_gateway_class($methods)**
   
   - Adds the MoneyBadger gateway class to the list of available payment gateways in WooCommerce.

3. **moneybadger_plugin_links($links)**
   
   - Adds action links to the plugin settings page for easy access to the settings and support.


4. **moneybadger_wc_blocks_support()**

   - Adds support for WooCommerce blocks if the WooCommerce Blocks plugin is active.


5. **moneybadger_wc_declare_feature_compatibility()**

   - Declares compatibility with WooCommerce features such as custom order tables and product block editor.

## Usage

1. **Installation**

   - Upload the `moneybadger-gateway` folder to the `/wp-content/plugins/` directory.

   - Activate the plugin through the 'Plugins' menu in WordPress.

2. **Configuration**

   - Go to WooCommerce > Settings > Payments.
   - Enable the MoneyBadger Payment Gateway.
   - Configure the settings, including Merchant Code and API Key.

3. **Payment Process**

   - Customers can select the MoneyBadger payment option during checkout.   
   - The plugin will handle the transaction and provide feedback to the customer.

## Code Structure

The main class for the MoneyBadger payment gateway is defined in `includes/class-wc-gateway-moneybadger.php`. This class handles the payment processing, settings, and integration with WooCommerce.

### Key Methods in `WC_Gateway_Moneybadger`

- **get_base_url()**: Returns the base URL for API requests. 

- **get_payments_base_url()**: Returns the payments base URL for requests.

- **init_form_fields()**: Initializes the admin fields for the plugin settings.

- **get_moneybadger_iframe_url($order)**: Generates the MoneyBadger payment iframe URL.

- **process_payment($order_id)**: Processes the payment for the given order.

- **check_moneybadger_webhook()**: Handles the webhook from MoneyBadger for payment confirmation.

- **check_moneybadger_webhook()**: Handles the webhook from MoneyBadger for payment confirmation.

- **get_invoice($invoiceID)**: Retrieves a MoneyBadger invoice.

- **confirm_payment($invoiceID)**: Confirms a MoneyBadger payment request.

## Support

For support, please visit the [MoneyBadger Support Page](https://www.moneybadger.co.za/contact).
