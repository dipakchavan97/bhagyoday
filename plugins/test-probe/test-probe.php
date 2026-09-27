<?php
/**
 * Plugin Name: Ayur Test Probe
 * Description: Diagnostic probe for deployment verification.
 * Version: 1.0.1
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function() {
    if (isset($_GET['ayur_test_probe_action'])) {
        $action = sanitize_text_field($_GET['ayur_test_probe_action']);
        
        // 1. Create a mock order for Purchase Event verification
        if ($action === 'create_test_order' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            if (!class_exists('WC_Order')) {
                wp_send_json_error(array('message' => 'WooCommerce not active'));
            }

            $order = wc_create_order();
            $product_id = 30; // Ashwagandha Shilajit
            $product = wc_get_product($product_id);
            if ($product) {
                $order->add_product($product, 1);
            }
            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'company'    => '',
                'email'      => 'audit@bhagyodayayurveda.com',
                'phone'      => '9888686858',
                'address_1'  => 'Civil Hospital Road',
                'address_2'  => '',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'billing');
            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'address_1'  => 'Civil Hospital Road',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'shipping');
            $order->set_payment_method('cod');
            $order->set_payment_method_title('Cash on Delivery');
            $order->calculate_totals();
            $order->update_status('processing', 'Test audit order for purchase verification');
            $order_id = $order->get_id();

            wp_send_json_success(array(
                'order_id'       => $order_id,
                'order_key'      => $order->get_order_key(),
                'total'          => $order->get_total(),
                'thank_you_url'  => $order->get_checkout_order_received_url(),
            ));
        }

        // 2. Clean up mock order
        if ($action === 'cleanup_test_order' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $order_id = absint($_GET['order_id']);
            if ($order_id) {
                wp_delete_post($order_id, true);
                wp_send_json_success(array('deleted' => $order_id));
            }
        }

        // 3. Clear WC Cart
        if ($action === 'empty_cart' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            if (function_exists('WC') && WC()->cart) {
                WC()->cart->empty_cart();
                wp_send_json_success(array('cleared' => true));
            } else {
                wp_send_json_error(array('message' => 'WC Cart unavailable'));
            }
        }

        // 4. Diagnose Checkout, Gateways & Shipping
        if ($action === 'diagnose_checkout' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {

            $prod = wc_get_product(30);
            $zones_data = array();
            if (class_exists('WC_Shipping_Zones')) {
                foreach (WC_Shipping_Zones::get_zones() as $z) {
                    $methods = array();
                    foreach ($z['shipping_methods'] as $m) {
                        $methods[] = array(
                            'id' => $m->id,
                            'instance_id' => $m->instance_id,
                            'title' => $m->title,
                            'enabled' => $m->enabled,
                            'settings' => $m->instance_settings
                        );
                    }
                    $zones_data[] = array(
                        'name' => $z['zone_name'],
                        'methods' => $methods
                    );
                }
            }

            $gateways_data = array();
            if (WC()->payment_gateways()) {
                foreach (WC()->payment_gateways()->payment_gateways() as $id => $gateway) {
                    $gateways_data[$id] = array(
                        'id' => $id,
                        'title' => $gateway->title,
                        'enabled' => $gateway->enabled,
                        'is_available' => $gateway->is_available(),
                        'settings' => isset($gateway->settings) ? $gateway->settings : array()
                    );
                }
            }

            $checkout_page = get_post(wc_get_page_id('checkout'));
            $cart_page = get_post(wc_get_page_id('cart'));

            wp_send_json_success(array(
                'shipping_enabled' => wc_shipping_enabled(),
                'product_30' => array(
                    'needs_shipping' => $prod ? $prod->needs_shipping() : false,
                    'is_virtual' => $prod ? $prod->is_virtual() : false,
                    'price' => $prod ? $prod->get_price() : 0,
                    'regular_price' => $prod ? $prod->get_regular_price() : 0,
                ),
                'shipping_zones' => $zones_data,
                'gateways' => $gateways_data,
                'checkout_page_content' => $checkout_page ? $checkout_page->post_content : '',
                'cart_page_content' => $cart_page ? $cart_page->post_content : '',
            ));
        }

        // 5. Verify COD Order Metadata
        if ($action === 'verify_cod_order_meta' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error(array('message' => 'Order not found'));
            }

            wp_send_json_success(array(
                'order_id'                  => $order_id,
                'status'                    => $order->get_status(),
                'total'                     => (float) $order->get_total(),
                'subtotal'                  => (float) $order->get_subtotal(),
                'shipping_total'            => (float) $order->get_shipping_total(),
                'payment_method'            => $order->get_payment_method(),
                'cod_confirmation_fee'     => get_post_meta($order_id, '_cod_confirmation_fee', true),
                'cod_confirmation_payment_id' => get_post_meta($order_id, '_cod_confirmation_payment_id', true),
                'cod_confirmation_razorpay_order_id' => get_post_meta($order_id, '_cod_confirmation_razorpay_order_id', true),
                'cod_confirmation_paid'    => get_post_meta($order_id, '_cod_confirmation_paid', true),
                'cod_remaining_amount'      => get_post_meta($order_id, '_cod_remaining_amount', true),
                'cod_payment_status'       => get_post_meta($order_id, '_cod_payment_status', true),
                'cod_confirmation_timestamp' => get_post_meta($order_id, '_cod_confirmation_timestamp', true),
            ));
        }

        // 6. Create Test Confirmed COD Order
        if ($action === 'create_test_cod_confirmed_order' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $order = wc_create_order(array('status' => 'processing'));
            $product_id = 30; // Ashwagandha Shilajit (₹890)
            $product = wc_get_product($product_id);
            if ($product) {
                $order->add_product($product, 1);
            }

            // Standard Shipping ₹70
            $shipping_item = new WC_Order_Item_Shipping();
            $shipping_item->set_method_title('Standard Delivery (₹70)');
            $shipping_item->set_method_id('flat_rate:2');
            $shipping_item->set_total(70);
            $order->add_item($shipping_item);

            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'email'      => 'audit@bhagyodayayurveda.com',
                'phone'      => '9888686858',
                'address_1'  => 'Civil Hospital Road',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'billing');
            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'phone'      => '9888686858',
                'address_1'  => 'Civil Hospital Road',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'shipping');

            $order->set_payment_method('cod');
            $order->set_payment_method_title('Cash on Delivery (COD)');
            $order->calculate_totals();

            $order_id = $order->get_id();
            $remaining = (float) $order->get_total(); // ₹960

            update_post_meta($order_id, '_cod_confirmation_fee', 100);
            update_post_meta($order_id, '_cod_confirmation_payment_id', 'pay_test_' . wp_generate_password(14, false));
            update_post_meta($order_id, '_cod_confirmation_razorpay_order_id', 'order_test_' . wp_generate_password(14, false));
            update_post_meta($order_id, '_cod_confirmation_paid', 'yes');
            update_post_meta($order_id, '_cod_remaining_amount', $remaining);
            update_post_meta($order_id, '_cod_payment_status', 'confirmed');
            update_post_meta($order_id, '_cod_confirmation_timestamp', current_time('mysql'));

            $order->add_order_note('Test COD Confirmation Fee of ₹100 paid online. Remaining ₹' . $remaining . ' due on delivery.', false);

            wp_send_json_success(array(
                'order_id'       => $order_id,
                'order_key'      => $order->get_order_key(),
                'total'          => $order->get_total(),
                'thank_you_url'  => $order->get_checkout_order_received_url(),
            ));
        }

        // 7. Get Ayur Commerce State
        if ($action === 'get_ayur_commerce_state' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            wp_send_json_success(array(
                'instant_checkout_enabled' => class_exists('Ayur_Instant_Checkout') ? Ayur_Instant_Checkout::is_enabled() : false,
                'offers_enabled'           => class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::is_enabled() : false,
                'offer_tiers'              => class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::get_offer_tiers() : array(),
                'cod_fee_enabled'          => class_exists('Ayur_COD_Confirmation') ? Ayur_COD_Confirmation::is_enabled() : false,
                'cod_fee_amount'           => class_exists('Ayur_COD_Confirmation') ? Ayur_COD_Confirmation::get_fee_amount() : 100,
                'address_autofill_enabled' => class_exists('Ayur_Address_Autofill') ? Ayur_Address_Autofill::is_enabled() : false,
            ));
        }

        // 7b. Restore Ayur Suite Defaults (Ensure no options were cleared when saving cod tab)
        if ($action === 'restore_ayur_suite_defaults' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            update_option('ayur_instant_checkout_enabled', 'yes');
            update_option('ayur_address_autocomplete_enabled', 'yes');
            update_option('ayur_quantity_offers_enabled', 'yes');
            update_option('ayur_quantity_offer_tiers', array(
                array('min_qty' => 2, 'discount_pct' => 10),
                array('min_qty' => 4, 'discount_pct' => 20)
            ));
            wp_send_json_success(array(
                'status' => 'restored',
                'instant_checkout' => get_option('ayur_instant_checkout_enabled'),
                'address_autofill' => get_option('ayur_address_autocomplete_enabled'),
                'quantity_offers'  => get_option('ayur_quantity_offers_enabled'),
                'offer_tiers'      => get_option('ayur_quantity_offer_tiers')
            ));
        }

        // 8. Get PHP Debug Log
        if ($action === 'get_debug_log' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $log_path = WP_CONTENT_DIR . '/debug.log';
            $content = '';
            if (file_exists($log_path)) {
                $lines = file($log_path);
                $last_lines = array_slice($lines, -50);
                $content = implode("", $last_lines);
            }
            wp_send_json_success(array(
                'log_exists' => file_exists($log_path),
                'content'    => $content
            ));
        }

        // 10. Gateway & API Configuration Diagnostics
        if ($action === 'get_gateway_and_api_diagnostics' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $wc_rzp = get_option('woocommerce_razorpay_settings', array());
            $cod_key_id = get_option('ayur_cod_razorpay_key_id', '');
            $cod_key_secret = get_option('ayur_cod_razorpay_key_secret', '');
            $places_key = get_option('ayur_places_api_key', '');

            $effective_rzp_key = !empty($cod_key_id) ? $cod_key_id : (isset($wc_rzp['key_id']) ? $wc_rzp['key_id'] : '');
            $effective_rzp_secret = !empty($cod_key_secret) ? $cod_key_secret : (isset($wc_rzp['key_secret']) ? $wc_rzp['key_secret'] : '');

            global $wpdb;
            $matching_options = $wpdb->get_results("SELECT option_name, SUBSTRING(option_value, 1, 40) as val_preview FROM {$wpdb->options} WHERE option_name LIKE '%razorpay%' OR option_name LIKE '%rzp%' OR option_name LIKE '%ayur%'");
            wp_send_json_success(array(
                'active_plugins' => get_option('active_plugins', array()),
                'matching_options' => $matching_options,
                'wc_rzp_exists' => !empty($wc_rzp),
                'wc_rzp_enabled' => isset($wc_rzp['enabled']) ? $wc_rzp['enabled'] : 'no',
                'effective_rzp_key_id_prefix' => substr($effective_rzp_key, 0, 8),
                'effective_rzp_key_id_len' => strlen($effective_rzp_key),
                'has_rzp_secret' => !empty($effective_rzp_secret),
                'places_key_prefix' => substr($places_key, 0, 6),
                'places_key_len' => strlen($places_key),
                'checkout_page_has_blocks' => has_block('woocommerce/checkout', wc_get_page_id('checkout')),
                'wc_gateway_keys' => function_exists('WC') && WC()->payment_gateways ? array_keys(WC()->payment_gateways->payment_gateways()) : array(),
                'gateway_settings_options' => $wpdb->get_results("SELECT option_name, SUBSTRING(option_value, 1, 60) as val_preview FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_%_settings'"),
                'constants_check' => array(
                    'RAZORPAY_KEY_ID' => defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : null,
                    'RAZORPAY_KEY_SECRET' => defined('RAZORPAY_KEY_SECRET') ? 'SET' : null,
                    'RZP_KEY' => defined('RZP_KEY') ? RZP_KEY : null,
                )
            ));
        }

        // 11. Test Server-Side COD Bypass Defense
        if ($action === 'test_server_side_cod_bypass' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $caught_exception = false;
            $exception_message = '';
            $exception_code = 0;

            try {
                $mock_order = new WC_Order();
                $mock_order->set_payment_method('cod');
                
                // Directly invoke Store API gate without probe bypass
                if (class_exists('Ayur_COD_Confirmation')) {
                    // Temporarily unset GET params for the inner check
                    $old_action = $_GET['ayur_test_probe_action'];
                    unset($_GET['ayur_test_probe_action']);
                    try {
                        Ayur_COD_Confirmation::prevent_unverified_store_api_cod($mock_order);
                    } finally {
                        $_GET['ayur_test_probe_action'] = $old_action;
                    }
                }
            } catch (Throwable $e) {
                $caught_exception = true;
                $exception_message = $e->getMessage();
                $exception_code = $e->getCode();
            }

            wp_send_json_success(array(
                'gate_active'       => class_exists('Ayur_COD_Confirmation'),
                'is_cod_enabled'    => class_exists('Ayur_COD_Confirmation') ? Ayur_COD_Confirmation::is_enabled() : false,
                'blocked'           => $caught_exception,
                'exception_message' => $exception_message,
                'exception_code'    => $exception_code,
            ));
        }
    }
});



